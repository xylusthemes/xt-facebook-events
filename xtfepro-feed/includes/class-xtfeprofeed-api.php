<?php
/**
 * XT Facebook Events Pro Live Feed - Facebook API / Scraper Handler
 *
 * Source types: page_id (Facebook page slug/ID) | event_ids (specific event IDs) | ical_url (Facebook iCal feed URL).
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_API {

	/** @var XTFEPRO_Feed_API */
	private static $instance = null;

	private array $baseHeaders = [
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8',
		'Accept-Encoding: gzip, deflate, br',
		'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
		'Sec-Fetch-Dest: document',
		'Sec-Fetch-Mode: navigate',
		'Sec-Fetch-Site: none',
		'Upgrade-Insecure-Requests: 1',
	];

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'xtfeprofeed_background_sync', array( $this, 'background_sync_page' ), 10, 2 );
		add_action( 'xtfeprofeed_fetch_hq_image', array( $this, 'background_fetch_hq_image' ), 10, 2 );
	}

	// -------------------------------------------------------
	// Public: Get events for a feed (cache-first)
	// -------------------------------------------------------

	/**
	 * @param int  $feed_id
	 * @param bool $force
	 * @param int  $page_requested
	 * @return array|WP_Error
	 */
	public function get_events( $feed_id, $force = false, $page_requested = 1 ) {
		$cache_key = $this->cache_key( $feed_id );
		$meta      = $this->get_feed_meta( $feed_id );
		$per_page  = absint( $meta['per_page'] ?: 12 );

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( ! empty( $cached ) && is_array( $cached ) ) {
				$required_count = $page_requested * $per_page;
				if ( count( $cached ) >= $required_count ) {
					return $this->sort_events( $cached );
				}
			}
		}

		$events     = array();
		$cursor     = '';
		$has_more   = true;
		$page_count = 0;
		$max_pages  = 15; // Safeguard to prevent timeouts or infinite loops

		while ( $has_more && $page_count < $max_pages ) {
			$response = $this->fetch_page( $meta, $cursor );

			if ( is_wp_error( $response ) ) {
				if ( ! empty( $events ) ) {
					break;
				}
				$stale = get_option( '_transient_' . $cache_key );
				return is_array( $stale ) ? $this->sort_events( $stale ) : $response;
			}

			if ( ! empty( $response['events'] ) ) {
				$events = array_merge( $events, $response['events'] );
			}

			$has_more   = ! empty( $response['has_more'] ) && ! empty( $response['cursor'] );
			$cursor     = $response['cursor'] ?? '';
			$page_count++;

			// If it's not a paginated source type, break out of loop.
			if ( 'page_id' !== ( $meta['source_type'] ?? 'page_id' ) ) {
				break;
			}
		}

		$events   = $this->dedup( $events );
		$events   = $this->sort_events( $events );
		$duration = absint( $meta['cache_duration'] ) * MINUTE_IN_SECONDS;
		set_transient( $cache_key, $events, $duration );
		update_post_meta( $feed_id, '_xtfeprofeed_last_fetched', time() );

		// Clean up background sync postmeta
		delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
		delete_post_meta( $feed_id, '_xtfeprofeed_next_cursor' );

		return $events;
	}

	/**
	 * Background sync: fetch next cursor page and append to cache.
	 */
	public function background_sync_page( $feed_id, $cursor ) {
		$saved_cursor = get_post_meta( $feed_id, '_xtfeprofeed_next_cursor', true );
		if ( $cursor !== $saved_cursor ) {
			return; // Already fetched by another process
		}

		$cache_key = $this->cache_key( $feed_id );
		$meta      = $this->get_feed_meta( $feed_id );
		$response  = $this->fetch_page( $meta, $cursor );

		if ( is_wp_error( $response ) ) {
			delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
			return;
		}

		$cached = get_transient( $cache_key );
		if ( ! is_array( $cached ) ) $cached = array();

		$events   = $this->sort_events( $this->dedup( array_merge( $cached, $response['events'] ) ) );
		$duration = absint( $meta['cache_duration'] ) * MINUTE_IN_SECONDS;
		set_transient( $cache_key, $events, $duration );

		if ( $response['has_more'] && $response['cursor'] ) {
			update_post_meta( $feed_id, '_xtfeprofeed_next_cursor', $response['cursor'] );
			$this->schedule_background_sync( $feed_id, $response['cursor'], 5 );
		} else {
			delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
			delete_post_meta( $feed_id, '_xtfeprofeed_next_cursor' );
		}
	}

	/**
	 * For admin preview: fetch events using posted meta (bypasses cache).
	 */
	public function fetch_preview_events( $meta ) {
		$meta['is_preview'] = true;
		$feed_id = absint( $meta['feed_id'] ?? 0 );
		if ( $feed_id ) {
			$saved = $this->get_feed_meta( $feed_id );
			$is_same_source = (
				( $meta['source_type'] ?? '' ) === ( $saved['source_type'] ?? '' ) &&
				( $meta['page_id'] ?? '' ) === ( $saved['page_id'] ?? '' ) &&
				( $meta['event_ids'] ?? '' ) === ( $saved['event_ids'] ?? '' ) &&
				( $meta['ical_url'] ?? '' ) === ( $saved['ical_url'] ?? '' ) &&
				( $meta['time_filter'] ?? '' ) === ( $saved['time_filter'] ?? '' ) &&
				( $meta['start_date'] ?? '' ) === ( $saved['start_date'] ?? '' ) &&
				( $meta['end_date'] ?? '' ) === ( $saved['end_date'] ?? '' ) &&
				( ! empty( $meta['hide_online'] ) ) === ( ! empty( $saved['hide_online'] ) )
			);

			if ( $is_same_source ) {
				$cached = get_transient( $this->cache_key( $feed_id ) );
				if ( ! empty( $cached ) && is_array( $cached ) ) {
					return $this->sort_events( $cached );
				}
			}
		}

		$response = $this->fetch_page( $meta, '' );
		if ( is_wp_error( $response ) ) return $response;
		$events = $response['events'];
		if ( $response['has_more'] && $response['cursor'] ) {
			$r2 = $this->fetch_page( $meta, $response['cursor'] );
			if ( ! is_wp_error( $r2 ) ) {
				$events = $this->dedup( array_merge( $events, $r2['events'] ) );
			}
		}
		$events   = $this->dedup( $events );
		return $this->sort_events( $events );
	}

	public function clear_cache( $feed_id ) {
		delete_transient( $this->cache_key( $feed_id ) );
		delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
		delete_post_meta( $feed_id, '_xtfeprofeed_next_cursor' );
	}

	public function cache_key( $feed_id ) {
		return 'xtfeprofeed_' . absint( $feed_id );
	}

	// -------------------------------------------------------
	// Core fetch dispatcher
	// -------------------------------------------------------

	private function fetch_page( $meta, $cursor = '' ) {
		$source_type = $meta['source_type'] ?? 'page_id';

		switch ( $source_type ) {
			case 'page_id':
				return apply_filters( 'xtfeprofeed_fetch_page_events', new WP_Error( 'xtfeprofeed_pro_only', __( 'Facebook Page ID source is only available in the Pro version.', 'xt-facebook-events-pro' ) ), $meta, $cursor, $this );

			case 'event_ids':
				return $this->fetch_by_ids( $meta );

			case 'ical_url':
				return $this->fetch_by_ical( $meta );

			default:
				return new WP_Error( 'xtfeprofeed_invalid_source', __( 'Invalid feed source type.', 'xt-facebook-events-pro' ) );
		}
	}

	// -------------------------------------------------------
	// Source: Specific Event IDs
	// -------------------------------------------------------

	private function fetch_by_ids( $meta ) {
		$ids_raw = sanitize_text_field( $meta['event_ids'] ?? '' );
		$ids     = array_filter( array_map( 'trim', explode( ',', $ids_raw ) ) );

		if ( empty( $ids ) ) {
			return new WP_Error( 'xtfeprofeed_no_ids', __( 'At least one Event ID is required.', 'xt-facebook-events-pro' ) );
		}

		$events = array();
		$errors = array();

		foreach ( $ids as $event_id ) {
			$cache_key = 'xtfepro_event_details_' . $event_id;
			$event_data = get_transient( $cache_key );

			if ( false === $event_data ) {
				try {
					$event_data = $this->getEventById( $event_id );
					if ( ! empty( $event_data['name'] ) ) {
						set_transient( $cache_key, $event_data, DAY_IN_SECONDS );
					}
				} catch ( \Exception $e ) {
					$errors[] = $e->getMessage();
					continue;
				}
			}

			if ( ! empty( $event_data['name'] ) ) {
				$events[] = $this->normalize_event_details( $event_data );
			} else {
				$errors[] = sprintf( __( 'Event ID %s did not return any data.', 'xt-facebook-events-pro' ), $event_id );
			}
		}

		if ( empty( $events ) && ! empty( $errors ) ) {
			return new WP_Error( 'xtfeprofeed_event_error', implode( ' | ', array_unique( $errors ) ) );
		}

		$events = $this->apply_local_filters( $events, $meta );

		return array(
			'events'   => $events,
			'has_more' => false,
			'cursor'   => '',
		);
	}

	// -------------------------------------------------------
	// Source: iCal URL
	// -------------------------------------------------------

	private function fetch_by_ical( $meta ) {
		$ical_url = trim( $meta['ical_url'] ?? '' );
		if ( ! $ical_url ) {
			return new WP_Error( 'xtfeprofeed_no_ical', __( 'iCal URL is required.', 'xt-facebook-events-pro' ) );
		}

		$parsed_events = $this->parse_ical_feed( $ical_url );
		if ( is_wp_error( $parsed_events ) ) {
			return $parsed_events;
		}

		$events = array();
		foreach ( $parsed_events as $parsed ) {
			$event_id = $parsed['id'];
			if ( ! $event_id ) {
				if ( preg_match( '/(\d+)/', $parsed['url'], $matches ) ) {
					$event_id = $matches[1];
				}
			}

			$event_data = false;
			if ( $event_id ) {
				$cache_key = 'xtfepro_event_details_' . $event_id;
				$event_data = get_transient( $cache_key );

				if ( false === $event_data ) {
					try {
						$event_data = $this->getEventById( $event_id );
						if ( ! empty( $event_data['name'] ) ) {
							set_transient( $cache_key, $event_data, DAY_IN_SECONDS );
						}
					} catch ( \Exception $e ) {
						$event_data = array(
							'id'          => $event_id,
							'name'        => $parsed['name'],
							'url'         => $parsed['url'],
							'description' => $parsed['description'],
							'start_date'  => $parsed['start_local'] ?? '',
							'end_date'    => $parsed['end_local'] ?? '',
							'place'       => array(
								'name'    => $parsed['location'],
								'address' => '',
							),
						);
					}
				}
			}

			if ( $event_data ) {
				$events[] = $this->normalize_event_details( $event_data );
			} else {
				$events[] = array(
					'id'             => $event_id ?: md5( $parsed['url'] ),
					'name'           => $parsed['name'],
					'url'            => $parsed['url'],
					'status'         => 'CONFIRMED',
					'start_local'    => $parsed['start_local'] ?? '',
					'start_utc'      => $parsed['start_local'] ?? '',
					'end_local'      => $parsed['end_local'] ?? '',
					'end_utc'        => $parsed['end_local'] ?? '',
					'timezone'       => '',
					'image_url'      => '',
					'is_online'      => false,
					'placeholder'    => true,
					'venue_name'     => $parsed['location'],
					'venue_address'  => '',
					'venue_city'     => '',
					'organizer_name' => '',
					'organizer_url'  => '',
					'category'       => '',
					'category_id'    => '',
					'tags'           => array(),
					'is_free'        => true,
					'is_sold_out'    => false,
					'min_price'      => 0,
					'currency'       => '',
					'currency_symbol'=> '',
				);
			}
		}

		$events = $this->apply_local_filters( $events, $meta );

		return array(
			'events'   => $events,
			'has_more' => false,
			'cursor'   => '',
		);
	}

	// -------------------------------------------------------
	// Parser for Facebook iCal Feed
	// -------------------------------------------------------

	private function parse_ical_feed( $ical_url ) {
		$response = wp_remote_get( $ical_url, array(
			'timeout'    => 30,
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return new WP_Error( 'xtfeprofeed_ical_http_error', sprintf( __( 'HTTP error code: %d', 'xt-facebook-events-pro' ), $code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return array();
		}

		$body = str_replace( array( "\r\n", "\r" ), "\n", $body );
		$body = preg_replace( "/\n[ \t]/", "", $body );

		$lines = explode( "\n", $body );
		$events = array();
		$current_event = null;
		$in_event = false;

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) {
				continue;
			}

			if ( 'BEGIN:VEVENT' === $line ) {
				$current_event = array();
				$in_event = true;
				continue;
			}

			if ( 'END:VEVENT' === $line ) {
				if ( $current_event ) {
					$events[] = $current_event;
				}
				$in_event = false;
				$current_event = null;
				continue;
			}

			if ( $in_event ) {
				$parts = explode( ':', $line, 2 );
				if ( count( $parts ) < 2 ) {
					$parts = explode( ';', $line, 2 );
					if ( count( $parts ) >= 2 ) {
						$prop_parts = explode( ':', $parts[1], 2 );
						if ( count( $prop_parts ) >= 2 ) {
							$key = $parts[0];
							$val = $prop_parts[1];
							$current_event[ $key ] = $val;
						}
					}
					continue;
				}
				$key_parts = explode( ';', $parts[0], 2 );
				$key = trim( $key_parts[0] );
				$val = trim( $parts[1] );
				$current_event[ $key ] = $val;
			}
		}

		$normalized = array();
		foreach ( $events as $raw ) {
			$uid = $raw['UID'] ?? '';
			$id = '';
			if ( preg_match( '/(\d+)/', $uid, $matches ) ) {
				$id = $matches[1];
			}

			$url = $raw['URL'] ?? '';
			if ( ! $url && $id ) {
				$url = 'https://www.facebook.com/events/' . $id . '/';
			}

			$name = $raw['SUMMARY'] ?? '';
			$name = str_replace( array( '\\,', '\\;', '\\\\', '\\N', '\\n' ), array( ',', ';', '\\', "\n", "\n" ), $name );

			$description = $raw['DESCRIPTION'] ?? '';
			$description = str_replace( array( '\\,', '\\;', '\\\\', '\\N', '\\n' ), array( ',', ';', '\\', "\n", "\n" ), $description );

			$start_raw = $raw['DTSTART'] ?? '';
			$end_raw   = $raw['DTEND'] ?? '';

			$location = $raw['LOCATION'] ?? '';
			$location = str_replace( array( '\\,', '\\;', '\\\\', '\\N', '\\n' ), array( ',', ';', '\\', "\n", "\n" ), $location );

			$normalized[] = array(
				'id'          => $id,
				'name'        => $name,
				'url'         => $url,
				'start_local' => $this->parse_ical_date( $start_raw ),
				'end_local'   => $this->parse_ical_date( $end_raw ),
				'location'    => $location,
				'description' => $description,
			);
		}

		return $normalized;
	}

	private function parse_ical_date( $date_str ) {
		$date_str = trim( $date_str );
		if ( ! $date_str ) return '';

		$date_str = str_replace( 'Z', '', $date_str );

		if ( preg_match( '/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})$/', $date_str, $m ) ) {
			return "{$m[1]}-{$m[2]}-{$m[3]} {$m[4]}:{$m[5]}:{$m[6]}";
		}

		if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $date_str, $m ) ) {
			return "{$m[1]}-{$m[2]}-{$m[3]} 00:00:00";
		}

		return $date_str;
	}

	// -------------------------------------------------------
	// Normalization Helpers
	// -------------------------------------------------------

	public function normalize_raw_fb_event( $raw ) {
		$is_online = ! empty( $raw['is_online'] );
		return array(
			'id'             => sanitize_text_field( $raw['id'] ?? '' ),
			'name'           => sanitize_text_field( $raw['name'] ?? '' ),
			'url'            => esc_url_raw( $raw['url'] ?? ( $raw['id'] ? 'https://www.facebook.com/events/' . $raw['id'] . '/' : '' ) ),
			'status'         => 'CONFIRMED',

			// Date / time
			'start_local'    => sanitize_text_field( $raw['start_time'] ?? '' ),
			'start_utc'      => sanitize_text_field( $raw['start_time'] ?? '' ),
			'end_local'      => sanitize_text_field( $raw['end_time'] ?? $raw['start_time'] ?? '' ),
			'end_utc'        => sanitize_text_field( $raw['end_time'] ?? $raw['start_time'] ?? '' ),
			'timezone'       => '',

			// Image
			'image_url'      => esc_url_raw( $raw['cover'] ?? '' ),

			// Venue
			'is_online'      => $is_online,
			'venue_name'     => sanitize_text_field( $raw['place']['name'] ?? ( $is_online ? 'Online Event' : '' ) ),
			'venue_address'  => '',
			'venue_city'     => sanitize_text_field( $raw['place']['city'] ?? '' ),

			// Organizer
			'organizer_name' => sanitize_text_field( $raw['organizer'] ?? '' ),
			'organizer_url'  => esc_url_raw( $raw['organizer_url'] ?? '' ),

			// Category
			'category'       => '',
			'category_id'    => '',
			'tags'           => array(),

			// Ticket / Price
			'is_free'        => true,
			'is_sold_out'    => false,
			'min_price'      => 0,
			'currency'       => '',
			'currency_symbol'=> '',
		);
	}

	public function normalize_event_details( $raw ) {
		return array(
			'id'             => sanitize_text_field( $raw['id'] ?? '' ),
			'name'           => sanitize_text_field( $raw['name'] ?? '' ),
			'url'            => esc_url_raw( $raw['id'] ? 'https://www.facebook.com/events/' . $raw['id'] . '/' : '' ),
			'status'         => 'CONFIRMED',

			// Date / time
			'start_local'    => sanitize_text_field( $raw['start_date'] ?? '' ),
			'start_utc'      => sanitize_text_field( $raw['start_date'] ?? '' ),
			'end_local'      => sanitize_text_field( $raw['end_date'] ?? $raw['start_date'] ?? '' ),
			'end_utc'        => sanitize_text_field( $raw['end_date'] ?? $raw['start_date'] ?? '' ),
			'timezone'       => '',

			// Image
			'image_url'      => esc_url_raw( $raw['cover_image'] ?? '' ),

			// Venue
			'is_online'      => empty( $raw['place']['name'] ) && empty( $raw['place']['address'] ),
			'venue_name'     => sanitize_text_field( $raw['place']['name'] ?? '' ),
			'venue_address'  => sanitize_text_field( $raw['place']['address'] ?? '' ),
			'venue_city'     => '',

			// Organizer
			'organizer_name' => sanitize_text_field( $raw['creator']['name'] ?? '' ),
			'organizer_url'  => ( ! empty( $raw['creator']['id'] ) ) ? esc_url_raw( 'https://www.facebook.com/' . $raw['creator']['id'] ) : '',

			// Category
			'category'       => '',
			'category_id'    => '',
			'tags'           => array(),

			// Ticket / Price
			'is_free'        => true,
			'is_sold_out'    => false,
			'min_price'      => 0,
			'currency'       => '',
			'currency_symbol'=> '',
		);
	}

	// -------------------------------------------------------
	// Facebook Scraping Logic (from reference file)
	// -------------------------------------------------------

	public function getEventById( string $eventId ): array {
		// First try: public GraphQL API to avoid page scraping IP blocks
		$graphql_url = 'https://www.facebook.com/api/graphql/';
		$payload     = [
			'variables' => json_encode( [ 'eventID' => $eventId, 'isCrawler' => false, 'scale' => 1 ] ),
			'doc_id'    => '33843234555263685'
		];

		$ch = curl_init( $graphql_url );
		curl_setopt_array( $ch, [
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS     => http_build_query( $payload ),
			CURLOPT_HTTPHEADER     => [ 'Content-Type: application/x-www-form-urlencoded', 'User-Agent: Mozilla/5.0' ],
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_TIMEOUT        => 15,
		]);
		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $httpCode === 200 && $response ) {
			$data = json_decode( $response, true );
			$event = $data['data']['event'] ?? null;

			if ( $event && ! empty( $event['name'] ) ) {
				$start_ts    = $event['start_timestamp'] ?? null;
				$event_place = $event['event_place'] ?? null;
				$cover_media = $event['cover_media_renderer'] ?? [];

				// --- Two-way image extraction ---
				$cover_image_url = null;

				// Way 1: Standard photo cover
				$cover_photo = $cover_media['cover_photo']['photo']['full_image'] ?? null;
				if ( ! empty( $cover_photo['uri'] ) ) {
					$cover_image_url = $cover_photo['uri'];
				}

				// Way 2: Video cover — preferred_thumbnail
				if ( empty( $cover_image_url ) ) {
					$preferred_thumbnail = $this->findKey( $cover_media, 'preferred_thumbnail' );
					if ( ! empty( $preferred_thumbnail ) ) {
						$thumb_image = $this->findKey( $preferred_thumbnail, 'image' );
						if ( ! empty( $thumb_image['uri'] ) ) {
							$cover_image_url = $thumb_image['uri'];
						}
					}
				}

				// Way 2b: Video cover — cover_video > preferred_thumbnail
				if ( empty( $cover_image_url ) ) {
					$cover_video = $this->findKey( $cover_media, 'cover_video' );
					if ( ! empty( $cover_video ) ) {
						$thumb = $this->findKey( $cover_video, 'preferred_thumbnail' );
						if ( ! empty( $thumb ) ) {
							$img = $this->findKey( $thumb, 'image' );
							if ( ! empty( $img['uri'] ) ) {
								$cover_image_url = $img['uri'];
							}
						}
					}
				}

				return [
					'id'          => $eventId,
					'name'        => $event['name'],
					'start_date'  => $start_ts ? date( 'Y-m-d H:i:s', $start_ts ) : null,
					'end_date'    => null,
					'description' => null, // Not returned by this doc_id
					'cover_image' => $cover_image_url,
					'place'       => [
						'name'    => $event_place['name'] ?? null,
						'address' => null,
						'lat'     => null,
						'lng'     => null,
					],
					'creator'     => [
						'id'   => null,
						'name' => null,
					]
				];
			}
		}

		// Fallback: Scraping page HTML (original way if API fails)
		$url  = "https://www.facebook.com/events/{$eventId}/?locale=en_US";
		$response = wp_remote_get( $url, [
			'headers' => [
				'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8',
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
			],
			'timeout' => 15,
		] );
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}
		$html = wp_remote_retrieve_body( $response );

		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		$scripts    = (new \DOMXPath( $dom ))->query( '//script[@type="application/json"]' );
		$cover_data = null;
		$hosts_data = null;

		foreach ( $scripts as $script ) {
			$text = $script->textContent;

			if ( $cover_data === null && strpos( $text, 'cover_media_renderer' ) !== false ) {
				$decoded = json_decode( $text, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$cover_data = $decoded;
				}
			}

			if ( $hosts_data === null && strpos( $text, 'event_creator' ) !== false ) {
				$decoded = json_decode( $text, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$hosts_data = $decoded;
				}
			}

			if ( $cover_data !== null && $hosts_data !== null ) {
				break;
			}
		}

		$creator       = $this->findKey( $hosts_data ?? [], 'event_creator' );
		$hosts         = $this->findKey( $hosts_data ?? [], 'event_hosts_that_can_view_guestlist' );
		$cover_media   = $this->findKey( $cover_data ?? [], 'cover_media_renderer' );
		$start_ts      = $this->findKey( $cover_data ?? [], 'start_timestamp' );
		$end_ts        = $this->findKey( $cover_data ?? [], 'end_timestamp' );
		$event_place   = $this->findKey( $hosts_data ?? [], 'event_place' );
		$one_line_addr = $this->findKey( $hosts_data ?? [], 'one_line_address' );
		$description   = $this->findKey( $hosts_data ?? [], 'event_description' );

		// --- Two-way image extraction (HTML scraping fallback) ---
		$cover_image_url = null;

		// Way 1: Standard photo cover
		$cover_photo = $this->findKey( $cover_media ?? [], 'full_image' );
		if ( ! empty( $cover_photo['uri'] ) ) {
			$cover_image_url = $cover_photo['uri'];
		}

		// Way 2: Video cover — preferred_thumbnail
		if ( empty( $cover_image_url ) ) {
			$preferred_thumbnail = $this->findKey( $cover_media ?? [], 'preferred_thumbnail' );
			if ( ! empty( $preferred_thumbnail ) ) {
				$thumb_image = $this->findKey( $preferred_thumbnail, 'image' );
				if ( ! empty( $thumb_image['uri'] ) ) {
					$cover_image_url = $thumb_image['uri'];
				}
			}
		}

		// Way 2b: Video cover — cover_video > preferred_thumbnail
		if ( empty( $cover_image_url ) ) {
			$cover_video = $this->findKey( $cover_media ?? [], 'cover_video' );
			if ( ! empty( $cover_video ) ) {
				$thumb = $this->findKey( $cover_video, 'preferred_thumbnail' );
				if ( ! empty( $thumb ) ) {
					$img = $this->findKey( $thumb, 'image' );
					if ( ! empty( $img['uri'] ) ) {
						$cover_image_url = $img['uri'];
					}
				}
			}
		}

		return [
			'id'          => $eventId,
			'name'        => $this->findKey( $cover_data ?? [], 'name' ),
			'start_date'  => $start_ts ? date( 'Y-m-d H:i:s', $start_ts ) : null,
			'end_date'    => $end_ts ? date( 'Y-m-d H:i:s', $end_ts ) : null,
			'description' => $description['text'] ?? null,
			'cover_image' => $cover_image_url,
			'place'       => [
				'name'    => $event_place['name'] ?? null,
				'address' => $one_line_addr ?? null,
				'lat'     => $event_place['location']['latitude'] ?? null,
				'lng'     => $event_place['location']['longitude'] ?? null,
			],
			'creator'     => [
				'id'   => $creator['id'] ?? null,
				'name' => $creator['name'] ?? null,
			]
		];
	}

	// -------------------------------------------------------
	// HQ Image fetching and caching (custom DB table)
	// -------------------------------------------------------

	public function enrich_events_with_hq_images( $events, $is_preview = false ) {
		$db = XTFEPRO_Feed_DB::instance();

		foreach ( $events as &$event ) {
			$event_id = $this->extract_base_event_id( $event );
			if ( empty( $event_id ) ) continue;

			// 1. Try custom DB cache
			$hq_image = $db->get_image( $event_id );
			if ( $hq_image ) {
				$event['image_url'] = $hq_image;
				continue;
			}

			// 2. Try the transient cache used by getEventById flow
			$cache_key = 'xtfepro_event_details_' . $event_id;
			$event_details = get_transient( $cache_key );

			if ( $event_details && ! empty( $event_details['cover_image'] ) ) {
				$hq_image = $event_details['cover_image'];
				$db->save_image( $event_id, $hq_image );
				$event['image_url'] = $hq_image;
				continue;
			}

			// If it's preview, do NOT fetch synchronously from the API (avoid speed delay).
			// Just use the scraped blurry image!
			if ( $is_preview ) {
				continue;
			}

			if ( false === $event_details ) {
				try {
					$event_details = $this->getEventById( $event_id );
					if ( ! empty( $event_details['name'] ) ) {
						set_transient( $cache_key, $event_details, DAY_IN_SECONDS );
					}
				} catch ( \Exception $e ) {
					// Fallback to whatever scraped image we have
					$event_details = false;
				}
			}

			if ( $event_details && ! empty( $event_details['cover_image'] ) ) {
				$hq_image = $event_details['cover_image'];
				$db->save_image( $event_id, $hq_image );
				$event['image_url'] = $hq_image;
			}
		}
		unset( $event );

		return $events;
	}

	/**
	 * Schedule background HQ image fetches for events missing HQ images in DB.
	 *
	 * Uses Action Scheduler if available, otherwise falls back to wp_schedule_single_event.
	 *
	 * @param array $events Normalized events.
	 */
	public function schedule_hq_image_fetches( $events ) {
		$db = XTFEPRO_Feed_DB::instance();
		$scheduled = array();
		$delay     = 1;

		foreach ( $events as $event ) {
			$event_id = $this->extract_base_event_id( $event );
			if ( empty( $event_id ) || in_array( $event_id, $scheduled, true ) ) {
				continue;
			}

			// Skip if HQ image already exists in DB
			if ( $db->get_image( $event_id ) ) {
				$scheduled[] = $event_id;
				continue;
			}

			$args = array( 'event_id' => $event_id, 'event_url' => $event['url'] ?? '' );

			if ( function_exists( 'as_schedule_single_action' ) ) {
				$group = 'xtfeprofeed_hq_images';
				// Don't schedule if already pending
				if ( function_exists( 'as_has_scheduled_action' ) && as_has_scheduled_action( 'xtfeprofeed_fetch_hq_image', $args, $group ) ) {
					$scheduled[] = $event_id;
					continue;
				}
				as_schedule_single_action( time() + $delay, 'xtfeprofeed_fetch_hq_image', $args, $group );
			} else {
				wp_schedule_single_event( time() + $delay, 'xtfeprofeed_fetch_hq_image', $args );
			}

			$scheduled[] = $event_id;
			$delay += 2; // 2-second gap between API calls
		}
	}

	/**
	 * Background job: Fetch HQ image for a single event via the detail API.
	 *
	 * Called by Action Scheduler or WP-Cron.
	 *
	 * @param string $event_id  Facebook event ID.
	 * @param string $event_url Event URL (for reference).
	 */
	public function background_fetch_hq_image( $event_id, $event_url = '' ) {
		$event_id = sanitize_text_field( $event_id );
		$db       = XTFEPRO_Feed_DB::instance();

		// Already have it
		if ( $db->get_image( $event_id ) ) {
			return;
		}

		// Try fetching via the event detail API
		try {
			$event_data = $this->getEventById( $event_id );
			$hq_image   = $event_data['cover_image'] ?? '';

			if ( ! empty( $hq_image ) ) {
				$db->save_image( $event_id, $hq_image );

				// Update any active transient caches that contain this event
				$this->update_transient_image( $event_id, $hq_image );
			}
		} catch ( \Exception $e ) {
			// Silently fail — will retry on next cache clear
		}
	}

	/**
	 * Update a specific event's image in ALL active feed transient caches.
	 *
	 * @param string $event_id  Facebook event ID.
	 * @param string $image_url New HQ image URL.
	 */
	private function update_transient_image( $event_id, $image_url ) {
		global $wpdb;

		// Find all feed transients
		$transient_keys = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_xtfeprofeed_%'
			 AND option_name NOT LIKE '_transient_timeout_%'"
		);

		foreach ( $transient_keys as $option_name ) {
			$cache_key = str_replace( '_transient_', '', $option_name );
			$events    = get_transient( $cache_key );
			if ( ! is_array( $events ) ) continue;

			$updated = false;
			foreach ( $events as &$ev ) {
				$ev_id = $this->extract_base_event_id( $ev );
				if ( (string) $ev_id === (string) $event_id ) {
					$ev['image_url'] = $image_url;
					$updated = true;
				}
			}
			unset( $ev );

			if ( $updated ) {
				// Preserve original TTL
				$timeout = get_option( '_transient_timeout_' . $cache_key );
				$remaining = $timeout ? max( 60, $timeout - time() ) : HOUR_IN_SECONDS;
				set_transient( $cache_key, $events, $remaining );
			}
		}
	}

	/**
	 * Extract the base event ID from a normalized event array.
	 * Handles both numeric IDs and URL-based extraction.
	 *
	 * @param array $event Normalized event.
	 * @return string Base event ID.
	 */
	private function extract_base_event_id( $event ) {
		$event_id = (string) ( $event['id'] ?? '' );
		$url      = (string) ( $event['url'] ?? '' );

		// Try to extract numeric ID from URL for recurring events
		if ( preg_match( '/events\/(\d+)/', $url, $matches ) ) {
			return $matches[1];
		}

		return $event_id;
	}

	// -------------------------------------------------------
	// Local filters and sorting
	// -------------------------------------------------------


	public function apply_local_filters( $events, $meta ) {
		if ( ! empty( $meta['hide_online'] ) ) {
			$events = array_filter( $events, function( $e ) {
				return ! $e['is_online'];
			} );
		}

		if ( ! empty( $meta['time_filter'] ) && 'all' !== $meta['time_filter'] ) {
			$filter      = $meta['time_filter'];
			$now         = time();
			$today_start = strtotime( 'today' );
			$today_end   = strtotime( 'tomorrow' ) - 1;
			$custom_start = ! empty( $meta['start_date'] ) ? strtotime( $meta['start_date'] ) : 0;
			$custom_end   = ! empty( $meta['end_date'] ) ? strtotime( $meta['end_date'] . ' 23:59:59' ) : 2147483647;

			if ( false === $custom_start ) {
				$custom_start = 0;
			}
			if ( false === $custom_end ) {
				$custom_end = 2147483647;
			}

			$events = array_filter( $events, function( $e ) use ( $filter, $now, $today_start, $today_end, $custom_start, $custom_end ) {
				$start_ts = strtotime( $e['start_local'] );
				$end_ts   = ! empty( $e['end_local'] ) ? strtotime( $e['end_local'] ) : $start_ts;
				if ( ! $end_ts ) return true;

				switch ( $filter ) {
					case 'today':
						return ( $end_ts >= $now && $start_ts <= $today_end );
					case 'upcoming_week':
						return ( $end_ts >= $now && $start_ts <= $now + 7 * DAY_IN_SECONDS );
					case 'upcoming_15_days':
						return ( $end_ts >= $now && $start_ts <= $now + 15 * DAY_IN_SECONDS );
					case 'upcoming_month':
						return ( $end_ts >= $now && $start_ts <= $now + 30 * DAY_IN_SECONDS );
					case 'current_future':
						return $end_ts >= $now;
					case 'custom':
						return ( $end_ts >= $now && $start_ts <= $custom_end );
					default:
						return $end_ts >= $now; // default mein bhi expired hide karo
				}
			} );
		}

		return array_values( $events );
	}

	public function dedup( $events ) {
		$unique = array();
		$seen   = array();
		foreach ( $events as $event ) {
			$id  = (string) ( $event['id'] ?? '' );
			
			// 1. Check by ID
			if ( $id !== '' ) {
				if ( isset( $seen[ 'id:' . $id ] ) ) {
					continue;
				}
				$seen[ 'id:' . $id ] = true;
			}
			
			// 2. Check by Name + Date
			$name = $event['name'] ?? '';
			$start = $event['start_local'] ?? '';
			if ( $name !== '' && $start !== '' ) {
				$day = substr( $start, 0, 10 ); // YYYY-MM-DD
				$norm_name = preg_replace( '/[^\p{L}\p{N}]/u', '', mb_strtolower( $name, 'UTF-8' ) );
				$name_date_key = 'name_date:' . $norm_name . '_' . $day;
				if ( isset( $seen[ $name_date_key ] ) ) {
					continue;
				}
				$seen[ $name_date_key ] = true;
			}
			
			// 3. Fallback check for raw hash
			if ( $id === '' && ( $name === '' || $start === '' ) ) {
				$raw_key = 'raw:' . md5( wp_json_encode( $event ) );
				if ( isset( $seen[ $raw_key ] ) ) {
					continue;
				}
				$seen[ $raw_key ] = true;
			}
			
			$unique[] = $event;
		}
		return $unique;
	}

	private function sort_events( $events ) {
		usort( $events, function( $a, $b ) {
			$at = $a['start_local'] ? strtotime( $a['start_local'] ) : 0;
			$bt = $b['start_local'] ? strtotime( $b['start_local'] ) : 0;
			if ( $at === $bt ) return strcmp( (string) ( $a['id'] ?? '' ), (string) ( $b['id'] ?? '' ) );
			if ( 0 === $at ) return 1;
			if ( 0 === $bt ) return -1;
			return $at <=> $bt;
		} );
		return array_values( $events );
	}

	private function findKey( array $arr, string $key ) {
		if ( array_key_exists( $key, $arr ) ) {
			return $arr[ $key ];
		}
		foreach ( $arr as $v ) {
			if ( is_array( $v ) ) {
				$result = $this->findKey( $v, $key );
				if ( $result !== null ) {
					return $result;
				}
			}
		}
		return null;
	}

	private function schedule_background_sync( $feed_id, $cursor, $delay = 5 ) {
		$args = array( 'feed_id' => $feed_id, 'cursor' => $cursor );
		if ( function_exists( 'as_enqueue_async_action' ) ) {
			as_enqueue_async_action( 'xtfeprofeed_background_sync', $args, 'xtfeprofeed', false, time() + $delay );
		} else {
			wp_schedule_single_event( time() + $delay, 'xtfeprofeed_background_sync', $args );
		}
	}

	public function extract_page_id( $page_url ) {
		$page_url = trim( $page_url );
		if ( ! $page_url ) return '';
		if ( strpos( $page_url, 'facebook.com' ) !== false ) {
			$parsed = wp_parse_url( $page_url );
			$path   = trim( $parsed['path'] ?? '', '/' );
			$parts = explode( '/', $path );
			// E.g. facebook.com/pages/name/123 or facebook.com/name
			if ( 'pages' === $parts[0] && isset( $parts[2] ) ) {
				return sanitize_text_field( $parts[2] );
			}
			return sanitize_text_field( $parts[0] );
		}
		return sanitize_text_field( $page_url );
	}

	// -------------------------------------------------------
	// Public: Get feed meta
	// -------------------------------------------------------

	public function get_feed_meta( $feed_id ) {
		$time_filter = get_post_meta( $feed_id, '_xtfeprofeed_time_filter', true ) ?: 'current_future';
		$register_label = get_post_meta( $feed_id, '_xtfeprofeed_register_label', true ) ?: __( 'View Event', 'xt-facebook-events-pro' );

		$allowed_sources = apply_filters( 'xtfeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) );
		$source_type = get_post_meta( $feed_id, '_xtfeprofeed_source_type', true );
		if ( empty( $source_type ) ) {
			$source_type = in_array( 'page_id', $allowed_sources, true ) ? 'page_id' : 'event_ids';
		}
		if ( ! in_array( $source_type, $allowed_sources, true ) ) {
			$source_type = 'event_ids';
		}

		$allowed_layouts = apply_filters( 'xtfeprofeed_allowed_layouts', array( 'card-grid', 'list' ) );
		$layout = get_post_meta( $feed_id, '_xtfeprofeed_layout', true ) ?: 'card-grid';
		if ( ! in_array( $layout, $allowed_layouts, true ) ) {
			$layout = 'card-grid';
		}

		return array(
			// Source
			'source_type'     => $source_type,
			'page_id'         => get_post_meta( $feed_id, '_xtfeprofeed_page_id', true ),
			'event_ids'       => get_post_meta( $feed_id, '_xtfeprofeed_event_ids', true ),
			'ical_url'        => get_post_meta( $feed_id, '_xtfeprofeed_ical_url', true ),

			// Filters
			'time_filter'     => $time_filter,
			'start_date'      => get_post_meta( $feed_id, '_xtfeprofeed_start_date', true ),
			'end_date'        => get_post_meta( $feed_id, '_xtfeprofeed_end_date', true ),
			'category_id'     => '',
			'tag_query'       => '',
			'tags_filter'     => '',
			'hide_online'     => get_post_meta( $feed_id, '_xtfeprofeed_hide_online', true ),

			// Display
			'layout'          => $layout,
			'columns'         => absint( get_post_meta( $feed_id, '_xtfeprofeed_columns', true ) ?: 3 ),
			'show_image'      => get_post_meta( $feed_id, '_xtfeprofeed_show_image', true ) !== '0',
			'show_date'       => get_post_meta( $feed_id, '_xtfeprofeed_show_date', true ) !== '0',
			'show_venue'      => get_post_meta( $feed_id, '_xtfeprofeed_show_venue', true ) !== '0',
			'show_organizer'  => get_post_meta( $feed_id, '_xtfeprofeed_show_organizer', true ),
			'show_price'      => get_post_meta( $feed_id, '_xtfeprofeed_show_price', true ) !== '0',
			'show_category'   => false,
			'show_tags'       => false,
			'show_ticket_btn' => get_post_meta( $feed_id, '_xtfeprofeed_show_ticket_btn', true ) !== '0',

			// Tickets / Buttons
			'ticket_style'    => 'link',
			'free_label'      => __( 'Free', 'xt-facebook-events-pro' ),
			'paid_label'      => __( 'Paid', 'xt-facebook-events-pro' ),
			'register_label'  => $register_label,

			// Pagination
			'pagination_type' => get_post_meta( $feed_id, '_xtfeprofeed_pagination_type', true ) ?: 'ajax',
			'per_page'        => absint( get_post_meta( $feed_id, '_xtfeprofeed_per_page', true ) ?: 12 ),

			// Cache
			'cache_duration'  => absint( get_post_meta( $feed_id, '_xtfeprofeed_cache_duration', true ) ?: 1440 ),
			'auto_refresh'    => get_post_meta( $feed_id, '_xtfeprofeed_auto_refresh', true ),
			'custom_css'      => get_post_meta( $feed_id, '_xtfeprofeed_custom_css', true ),
		);
	}
}
