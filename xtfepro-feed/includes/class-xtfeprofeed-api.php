<?php
/**
 * XT Facebook Events Pro Live Feed - Facebook API / Scraper Handler
 *
 * OPTIMIZED: Paginated Transient Chain + Non-blocking background fetch
 * - Page 1 loads live on first visit (2-4 sec), then cached
 * - Pages 2-N fetched in background via non-blocking wp_remote_post
 * - HQ images fetched in batches of 5 (not 1-by-1 with 2s gaps)
 * - Image DB table kept for persistence across cache clears
 * - No Action Scheduler dependency for background page fetching
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_API {

	/** @var XTFEPRO_Feed_API */
	private static $instance = null;

	/** Cache key prefix for paginated pages */
	const PAGE_CACHE_PREFIX = 'xtfeprofeed_p_';

	/** Lock prefix to prevent duplicate background fetches */
	const LOCK_PREFIX = 'xtfeprofeed_lock_';

	/** Legacy single-blob cache key prefix (for backward compat clear) */
	const LEGACY_CACHE_PREFIX = 'xtfeprofeed_';

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
		// Background AJAX handlers (non-blocking page fetch + HQ image batch)
		add_action( 'wp_ajax_xtfeprofeed_bg_fetch_page',        array( $this, 'ajax_bg_fetch_page' ) );
		add_action( 'wp_ajax_nopriv_xtfeprofeed_bg_fetch_page', array( $this, 'ajax_bg_fetch_page' ) );

		add_action( 'wp_ajax_xtfeprofeed_bg_fetch_images',        array( $this, 'ajax_bg_fetch_images' ) );
		add_action( 'wp_ajax_nopriv_xtfeprofeed_bg_fetch_images', array( $this, 'ajax_bg_fetch_images' ) );

		// Keep Action Scheduler hooks for backward compat (existing scheduled jobs)
		add_action( 'xtfeprofeed_background_sync',   array( $this, 'background_sync_page' ), 10, 2 );
		add_action( 'xtfeprofeed_fetch_hq_image',    array( $this, 'background_fetch_hq_image' ), 10, 2 );
	}

	// -------------------------------------------------------
	// Public: Get events for a feed (paginated transient chain)
	// -------------------------------------------------------

	/**
	 * Main entry point. Returns events for the requested page.
	 * Page 1: live scrape on first visit, cached after.
	 * Pages 2+: triggered async in background after page 1 loads.
	 *
	 * @param int  $feed_id
	 * @param bool $force          Force refresh (admin clear cache)
	 * @param int  $page_requested Which display page user is viewing
	 * @return array|WP_Error
	 */
	public function get_events( $feed_id, $force = false, $page_requested = 1 ) {
		$meta     = $this->get_feed_meta( $feed_id );
		$per_page = absint( $meta['per_page'] ?: 12 );
		$duration = absint( $meta['cache_duration'] ) * MINUTE_IN_SECONDS;

		// --- Cache HIT: check if we have enough events cached ---
		if ( ! $force ) {
			$all_cached = $this->get_all_cached_events( $feed_id );
			if ( ! empty( $all_cached ) ) {
				$required = $page_requested * $per_page;
				// If we have enough events OR no more pages are being fetched
				if ( count( $all_cached ) >= $required || ! $this->is_background_running( $feed_id ) ) {
					return $this->sort_events( $all_cached );
				}
			}
		}

		// --- Cache MISS or force: live fetch page 1 ---
		$response = $this->fetch_page( $meta, '' );

		if ( is_wp_error( $response ) ) {
			// Return stale data if available
			$stale = $this->get_all_cached_events( $feed_id );
			return ! empty( $stale ) ? $this->sort_events( $stale ) : $response;
		}

		$events = $response['events'] ?? array();

		// For groups, HTML scraping usually only returns 2-3 events.
		// Fetch one more page synchronously to ensure a decent initial load.
		if ( ($meta['source_type'] ?? '') === 'group_id' && ! empty( $response['has_more'] ) && ! empty( $response['cursor'] ) ) {
			$r2 = $this->fetch_page( $meta, $response['cursor'] );
			if ( ! is_wp_error( $r2 ) ) {
				$events = array_merge( $events, $r2['events'] ?? array() );
				$response['has_more'] = ! empty( $r2['has_more'] );
				$response['cursor']   = $r2['cursor'] ?? '';
			}
		}

		$events = $this->dedup( $events );
		$events = $this->sort_events( $events );

		// Save page 1 transient
		$this->save_page_cache( $feed_id, 1, $events, $duration );
		update_post_meta( $feed_id, '_xtfeprofeed_last_fetched', time() );

		// If more pages exist, trigger background fetch for all of them
		if ( ! empty( $response['has_more'] ) && ! empty( $response['cursor'] ) && in_array( $meta['source_type'] ?? 'page_id', array( 'page_id', 'group_id' ), true ) ) {
			$this->trigger_bg_page_fetch( $feed_id, 2, $response['cursor'], $duration );
		}

		return $events;
	}

	// -------------------------------------------------------
	// Paginated transient cache helpers
	// -------------------------------------------------------

	/**
	 * Get cache key for a specific scrape page (not display page).
	 * We store FB scrape pages (50 events each) as separate transients.
	 */
	private function page_cache_key( $feed_id, $scrape_page ) {
		return self::PAGE_CACHE_PREFIX . absint( $feed_id ) . '_' . absint( $scrape_page );
	}

	/**
	 * Save events for a scrape page into its own transient.
	 */
	private function save_page_cache( $feed_id, $scrape_page, $events, $duration ) {
		set_transient( $this->page_cache_key( $feed_id, $scrape_page ), $events, $duration );
	}

	/**
	 * Merge all cached scrape pages into one flat array.
	 */
	public function get_all_cached_events( $feed_id ) {
		$all    = array();
		$page   = 1;
		$limit  = 20; // Max FB scrape pages we support

		while ( $page <= $limit ) {
			$cached = get_transient( $this->page_cache_key( $feed_id, $page ) );
			if ( false === $cached ) {
				break; // No more pages cached
			}
			if ( is_array( $cached ) ) {
				$all = array_merge( $all, $cached );
			}
			$page++;
		}

		return $all;
	}

	/**
	 * Check if a background fetch is currently running for this feed.
	 */
	private function is_background_running( $feed_id ) {
		return (bool) get_transient( self::LOCK_PREFIX . 'running_' . absint( $feed_id ) );
	}

	// -------------------------------------------------------
	// Non-blocking background page fetch
	// -------------------------------------------------------

	/**
	 * Trigger a non-blocking background fetch for the next scrape page.
	 * Uses wp_remote_post with blocking=false — fires & forgets.
	 */
	private function trigger_bg_page_fetch( $feed_id, $scrape_page, $cursor, $duration ) {
		$lock_key = self::LOCK_PREFIX . 'running_' . absint( $feed_id );

		// Already running?
		if ( get_transient( $lock_key ) ) {
			return;
		}

		// Set a running lock (15 min max per feed)
		set_transient( $lock_key, $scrape_page, 15 * MINUTE_IN_SECONDS );
		update_post_meta( $feed_id, '_xtfeprofeed_next_cursor', $cursor );
		update_post_meta( $feed_id, '_xtfeprofeed_next_page', $scrape_page );

		wp_remote_post(
			admin_url( 'admin-ajax.php' ),
			array(
				'timeout'   => 0.01,   // Fire & forget
				'blocking'  => false,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				'body'      => array(
					'action'       => 'xtfeprofeed_bg_fetch_page',
					'feed_id'      => $feed_id,
					'scrape_page'  => $scrape_page,
					'cursor'       => $cursor,
					'duration'     => $duration,
					'nonce'        => wp_create_nonce( 'xtfeprofeed_bg_' . $feed_id ),
				),
			)
		);
	}

	/**
	 * AJAX handler: background page fetch (runs after user response sent).
	 * Loops through all remaining FB pages until done, saves each as its own transient.
	 */
	public function ajax_bg_fetch_page() {
		$feed_id    = absint( $_POST['feed_id']     ?? 0 );
		$scrape_page = absint( $_POST['scrape_page'] ?? 2 );
		$cursor     = sanitize_text_field( $_POST['cursor']   ?? '' );
		$duration   = absint( $_POST['duration']  ?? HOUR_IN_SECONDS );
		$nonce      = sanitize_text_field( $_POST['nonce']    ?? '' );

		if ( ! $feed_id || ! wp_verify_nonce( $nonce, 'xtfeprofeed_bg_' . $feed_id ) ) {
			wp_die();
		}

		ignore_user_abort( true );
		set_time_limit( 300 );

		$meta     = $this->get_feed_meta( $feed_id );
		$lock_key = self::LOCK_PREFIX . 'running_' . $feed_id;
		$max_pages = 20;

		while ( $scrape_page <= $max_pages && $cursor ) {
			// Check if this page already cached (prevent duplicate work)
			if ( false !== get_transient( $this->page_cache_key( $feed_id, $scrape_page ) ) ) {
				// Get next cursor from postmeta if there is one
				$cursor = get_post_meta( $feed_id, '_xtfeprofeed_next_cursor', true );
				if ( ! $cursor ) break;
				$scrape_page++;
				continue;
			}

			$response = $this->fetch_page( $meta, $cursor );

			if ( is_wp_error( $response ) ) {
				break;
			}

			$events = $this->dedup( $response['events'] ?? array() );
			$events = $this->sort_events( $events );

			$this->save_page_cache( $feed_id, $scrape_page, $events, $duration );

			// Trigger HQ image batch fetch for this page's events (non-blocking)
			if ( ! empty( $events ) ) {
				$this->trigger_bg_image_batch( $feed_id, $events );
			}

			$has_more = ! empty( $response['has_more'] ) && ! empty( $response['cursor'] );
			$cursor   = $has_more ? $response['cursor'] : '';

			if ( $has_more ) {
				update_post_meta( $feed_id, '_xtfeprofeed_next_cursor', $cursor );
				update_post_meta( $feed_id, '_xtfeprofeed_next_page', $scrape_page + 1 );
			} else {
				delete_post_meta( $feed_id, '_xtfeprofeed_next_cursor' );
				delete_post_meta( $feed_id, '_xtfeprofeed_next_page' );
				break;
			}

			$scrape_page++;
		}

		// Release the running lock
		delete_transient( $lock_key );

		wp_die();
	}

	// -------------------------------------------------------
	// Background sync (Action Scheduler — backward compat)
	// -------------------------------------------------------

	/**
	 * Kept for backward compat with any existing AS jobs.
	 * New installs use ajax_bg_fetch_page() instead.
	 */
	public function background_sync_page( $feed_id, $cursor ) {
		$saved_cursor = get_post_meta( $feed_id, '_xtfeprofeed_next_cursor', true );
		if ( $cursor !== $saved_cursor ) {
			return;
		}

		$meta        = $this->get_feed_meta( $feed_id );
		$scrape_page = absint( get_post_meta( $feed_id, '_xtfeprofeed_next_page', true ) ?: 2 );
		$duration    = absint( $meta['cache_duration'] ) * MINUTE_IN_SECONDS;
		$response    = $this->fetch_page( $meta, $cursor );

		if ( is_wp_error( $response ) ) {
			delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
			return;
		}

		$events = $this->dedup( array_merge(
			$this->get_all_cached_events( $feed_id ),
			$response['events'] ?? array()
		) );
		$events = $this->sort_events( $events );
		$this->save_page_cache( $feed_id, $scrape_page, $response['events'] ?? array(), $duration );

		if ( $response['has_more'] && $response['cursor'] ) {
			update_post_meta( $feed_id, '_xtfeprofeed_next_cursor', $response['cursor'] );
			update_post_meta( $feed_id, '_xtfeprofeed_next_page', $scrape_page + 1 );
			$this->schedule_background_sync( $feed_id, $response['cursor'], 5 );
		} else {
			delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
			delete_post_meta( $feed_id, '_xtfeprofeed_next_cursor' );
			delete_post_meta( $feed_id, '_xtfeprofeed_next_page' );
		}
	}

	// -------------------------------------------------------
	// HQ Image: non-blocking batch fetch
	// -------------------------------------------------------

	/**
	 * Trigger a non-blocking background HQ image fetch for a batch of events.
	 * Groups events in batches of 5 and fires separate async requests.
	 *
	 * @param int   $feed_id
	 * @param array $events  Normalized events
	 */
	public function trigger_bg_image_batch( $feed_id, $events ) {
		$db          = XTFEPRO_Feed_DB::instance();
		$pending_ids = array();

		foreach ( $events as $event ) {
			$event_id = $this->extract_base_event_id( $event );
			if ( empty( $event_id ) ) continue;
			if ( $db->get_image( $event_id ) ) continue; // Already have HQ image
			$pending_ids[] = $event_id;
		}

		if ( empty( $pending_ids ) ) return;

		// Remove duplicates
		$pending_ids = array_unique( $pending_ids );

		// Fire in batches of 5
		$batches = array_chunk( $pending_ids, 5 );

		foreach ( $batches as $batch ) {
			wp_remote_post(
				admin_url( 'admin-ajax.php' ),
				array(
					'timeout'   => 0.01,
					'blocking'  => false,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
					'body'      => array(
						'action'    => 'xtfeprofeed_bg_fetch_images',
						'feed_id'   => $feed_id,
						'event_ids' => implode( ',', $batch ),
						'nonce'     => wp_create_nonce( 'xtfeprofeed_img_' . $feed_id ),
					),
				)
			);
		}
	}

	/**
	 * AJAX: Background HQ image batch fetch.
	 * Fetches up to 5 event images, saves to DB, updates transient caches.
	 */
	public function ajax_bg_fetch_images() {
		$feed_id   = absint( $_POST['feed_id']   ?? 0 );
		$ids_raw   = sanitize_text_field( $_POST['event_ids'] ?? '' );
		$nonce     = sanitize_text_field( $_POST['nonce']     ?? '' );

		if ( ! $feed_id || ! wp_verify_nonce( $nonce, 'xtfeprofeed_img_' . $feed_id ) ) {
			wp_die();
		}

		ignore_user_abort( true );
		set_time_limit( 120 );

		$event_ids = array_filter( array_map( 'trim', explode( ',', $ids_raw ) ) );
		$db        = XTFEPRO_Feed_DB::instance();

		foreach ( $event_ids as $event_id ) {
			// Skip if already in DB
			if ( $db->get_image( $event_id ) ) continue;

			try {
				$data = $this->getEventById( $event_id );
				if ( ! empty( $data['cover_image'] ) ) {
					$db->save_image( $event_id, $data['cover_image'] );
					// Update all paginated transient caches for this feed
					$this->update_paginated_cache_image( $feed_id, $event_id, $data['cover_image'] );
				}
			} catch ( \Exception $e ) {
				// Silently skip — will retry on next cache clear
			}
		}

		wp_die();
	}

	/**
	 * Legacy Action Scheduler HQ image job (backward compat).
	 */
	public function background_fetch_hq_image( $event_id, $event_url = '' ) {
		$event_id = sanitize_text_field( $event_id );
		$db       = XTFEPRO_Feed_DB::instance();

		if ( $db->get_image( $event_id ) ) return;

		try {
			$data = $this->getEventById( $event_id );
			if ( ! empty( $data['cover_image'] ) ) {
				$db->save_image( $event_id, $data['cover_image'] );
				// Update all feed caches that contain this event
				$this->update_all_feeds_image( $event_id, $data['cover_image'] );
			}
		} catch ( \Exception $e ) {
			// Silently fail
		}
	}

	/**
	 * Update image in all paginated transients for ONE specific feed.
	 * Much faster than the old approach that scanned ALL transients.
	 */
	private function update_paginated_cache_image( $feed_id, $event_id, $image_url ) {
		$page  = 1;
		$limit = 20;

		while ( $page <= $limit ) {
			$key    = $this->page_cache_key( $feed_id, $page );
			$events = get_transient( $key );
			if ( false === $events ) break;

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
				$timeout   = get_option( '_transient_timeout_' . $key );
				$remaining = $timeout ? max( 60, $timeout - time() ) : HOUR_IN_SECONDS;
				set_transient( $key, $events, $remaining );
			}

			$page++;
		}

		// Also update legacy single-blob transient if exists
		$legacy_key = $this->cache_key( $feed_id );
		$legacy     = get_transient( $legacy_key );
		if ( is_array( $legacy ) ) {
			$updated = false;
			foreach ( $legacy as &$ev ) {
				$ev_id = $this->extract_base_event_id( $ev );
				if ( (string) $ev_id === (string) $event_id ) {
					$ev['image_url'] = $image_url;
					$updated = true;
				}
			}
			unset( $ev );
			if ( $updated ) {
				$timeout   = get_option( '_transient_timeout_' . $legacy_key );
				$remaining = $timeout ? max( 60, $timeout - time() ) : HOUR_IN_SECONDS;
				set_transient( $legacy_key, $legacy, $remaining );
			}
		}
	}

	/**
	 * Old method: scan ALL transients (used only by legacy AS jobs).
	 */
	private function update_all_feeds_image( $event_id, $image_url ) {
		global $wpdb;

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
				$timeout   = get_option( '_transient_timeout_' . $cache_key );
				$remaining = $timeout ? max( 60, $timeout - time() ) : HOUR_IN_SECONDS;
				set_transient( $cache_key, $events, $remaining );
			}
		}
	}

	// -------------------------------------------------------
	// HQ image schedule (legacy — still used for event_ids source)
	// -------------------------------------------------------

	/**
	 * Schedule HQ image fetches — now uses non-blocking batch instead of AS delay loop.
	 *
	 * @param array $events Normalized events.
	 */
	public function schedule_hq_image_fetches( $events ) {
		// Use non-blocking batch fetch (no feed_id available here, use per-event transient)
		$db          = XTFEPRO_Feed_DB::instance();
		$pending_ids = array();

		foreach ( $events as $event ) {
			$event_id = $this->extract_base_event_id( $event );
			if ( empty( $event_id ) ) continue;
			if ( $db->get_image( $event_id ) ) continue;
			$cache_key = 'xtfepro_event_details_' . $event_id;
			if ( false !== get_transient( $cache_key ) ) {
				// Already have details, just save to DB
				$details = get_transient( $cache_key );
				if ( ! empty( $details['cover_image'] ) ) {
					$db->save_image( $event_id, $details['cover_image'] );
					continue;
				}
			}
			$pending_ids[] = $event_id;
		}

		if ( empty( $pending_ids ) ) return;

		$pending_ids = array_unique( $pending_ids );
		$batches     = array_chunk( $pending_ids, 5 );

		foreach ( $batches as $batch ) {
			// Use a dummy feed_id 0 for non-feed-specific image fetches
			wp_remote_post(
				admin_url( 'admin-ajax.php' ),
				array(
					'timeout'   => 0.01,
					'blocking'  => false,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
					'body'      => array(
						'action'    => 'xtfeprofeed_bg_fetch_images',
						'feed_id'   => 0,
						'event_ids' => implode( ',', $batch ),
						'nonce'     => wp_create_nonce( 'xtfeprofeed_img_0' ),
					),
				)
			);
		}
	}

	// -------------------------------------------------------
	// Preview (admin builder)
	// -------------------------------------------------------

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
				( $meta['page_id']     ?? '' ) === ( $saved['page_id']     ?? '' ) &&
				( $meta['group_id']    ?? '' ) === ( $saved['group_id']    ?? '' ) &&
				( $meta['event_ids']   ?? '' ) === ( $saved['event_ids']   ?? '' ) &&
				( $meta['ical_url']    ?? '' ) === ( $saved['ical_url']    ?? '' ) &&
				( $meta['time_filter'] ?? '' ) === ( $saved['time_filter'] ?? '' ) &&
				( $meta['start_date']  ?? '' ) === ( $saved['start_date']  ?? '' ) &&
				( $meta['end_date']    ?? '' ) === ( $saved['end_date']    ?? '' ) &&
				( ! empty( $meta['hide_online'] ) ) === ( ! empty( $saved['hide_online'] ) )
			);

			if ( $is_same_source ) {
				$cached = $this->get_all_cached_events( $feed_id );
				if ( ! empty( $cached ) ) {
					return $this->sort_events( $cached );
				}
			}
		}

		$response = $this->fetch_page( $meta, '' );
		if ( is_wp_error( $response ) ) return $response;

		$events = $response['events'];
		// Fetch one more page for preview to give a better sample
		if ( $response['has_more'] && $response['cursor'] ) {
			$r2 = $this->fetch_page( $meta, $response['cursor'] );
			if ( ! is_wp_error( $r2 ) ) {
				$events = $this->dedup( array_merge( $events, $r2['events'] ) );
			}
		}

		return $this->sort_events( $this->dedup( $events ) );
	}

	// -------------------------------------------------------
	// Cache management
	// -------------------------------------------------------

	/**
	 * Clear all cache for a feed (paginated pages + legacy + postmeta).
	 */
	public function clear_cache( $feed_id ) {
		// Clear paginated page transients
		$page  = 1;
		$limit = 20;
		while ( $page <= $limit ) {
			$key    = $this->page_cache_key( $feed_id, $page );
			$exists = get_transient( $key );
			delete_transient( $key );
			if ( false === $exists ) break;
			$page++;
		}

		// Clear legacy single-blob transient
		delete_transient( $this->cache_key( $feed_id ) );

		// Clear locks and cursors
		delete_transient( self::LOCK_PREFIX . 'running_' . absint( $feed_id ) );
		delete_post_meta( $feed_id, '_xtfeprofeed_sync_status' );
		delete_post_meta( $feed_id, '_xtfeprofeed_next_cursor' );
		delete_post_meta( $feed_id, '_xtfeprofeed_next_page' );
	}

	/**
	 * Legacy single-blob cache key (kept for backward compat).
	 */
	public function cache_key( $feed_id ) {
		return self::LEGACY_CACHE_PREFIX . absint( $feed_id );
	}

	// -------------------------------------------------------
	// Core fetch dispatcher
	// -------------------------------------------------------

	private function fetch_page( $meta, $cursor = '' ) {
		$source_type = $meta['source_type'] ?? 'page_id';

		switch ( $source_type ) {
			case 'page_id':
				return apply_filters( 'xtfeprofeed_fetch_page_events', new WP_Error( 'xtfeprofeed_pro_only', __( 'Facebook Page ID source is only available in the Pro version.', 'xt-facebook-events-pro' ) ), $meta, $cursor, $this );

			case 'group_id':
				return apply_filters( 'xtfeprofeed_fetch_group_events', new WP_Error( 'xtfeprofeed_pro_only', __( 'Facebook Group source is only available in the Pro version.', 'xt-facebook-events-pro' ) ), $meta, $cursor, $this );

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
			$cache_key  = 'xtfepro_event_details_' . $event_id;
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
				$cache_key  = 'xtfepro_event_details_' . $event_id;
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
							'end_date'    => $parsed['end_local']   ?? '',
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
					'end_local'      => $parsed['end_local']   ?? '',
					'end_utc'        => $parsed['end_local']   ?? '',
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
	// iCal parser
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

		$lines         = explode( "\n", $body );
		$events        = array();
		$current_event = null;
		$in_event      = false;

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) ) continue;

			if ( 'BEGIN:VEVENT' === $line ) {
				$current_event = array();
				$in_event      = true;
				continue;
			}

			if ( 'END:VEVENT' === $line ) {
				if ( $current_event ) {
					$events[] = $current_event;
				}
				$in_event      = false;
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
							$current_event[ $parts[0] ] = $prop_parts[1];
						}
					}
					continue;
				}
				$key_parts = explode( ';', $parts[0], 2 );
				$key       = trim( $key_parts[0] );
				$val       = trim( $parts[1] );
				$current_event[ $key ] = $val;
			}
		}

		$normalized = array();
		foreach ( $events as $raw ) {
			$uid = $raw['UID'] ?? '';
			$id  = '';
			if ( preg_match( '/(\d+)/', $uid, $matches ) ) {
				$id = $matches[1];
			}

			$url = $raw['URL'] ?? '';
			if ( ! $url && $id ) {
				$url = 'https://www.facebook.com/events/' . $id . '/';
			}

			$name        = $raw['SUMMARY'] ?? '';
			$name        = str_replace( array( '\\,', '\\;', '\\\\', '\\N', '\\n' ), array( ',', ';', '\\', "\n", "\n" ), $name );
			$description = $raw['DESCRIPTION'] ?? '';
			$description = str_replace( array( '\\,', '\\;', '\\\\', '\\N', '\\n' ), array( ',', ';', '\\', "\n", "\n" ), $description );
			$start_raw   = $raw['DTSTART'] ?? '';
			$end_raw     = $raw['DTEND']   ?? '';
			$location    = $raw['LOCATION'] ?? '';
			$location    = str_replace( array( '\\,', '\\;', '\\\\', '\\N', '\\n' ), array( ',', ';', '\\', "\n", "\n" ), $location );

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
	// Normalization helpers
	// -------------------------------------------------------

	public function normalize_raw_fb_event( $raw ) {
		$is_online = ! empty( $raw['is_online'] );
		return array(
			'id'              => sanitize_text_field( $raw['id']   ?? '' ),
			'name'            => sanitize_text_field( $raw['name'] ?? '' ),
			'url'             => esc_url_raw( $raw['url'] ?? ( $raw['id'] ? 'https://www.facebook.com/events/' . $raw['id'] . '/' : '' ) ),
			'status'          => 'CONFIRMED',
			'start_local'     => sanitize_text_field( $raw['start_time'] ?? '' ),
			'start_utc'       => sanitize_text_field( $raw['start_time'] ?? '' ),
			'end_local'       => sanitize_text_field( $raw['end_time'] ?? $raw['start_time'] ?? '' ),
			'end_utc'         => sanitize_text_field( $raw['end_time'] ?? $raw['start_time'] ?? '' ),
			'timezone'        => '',
			'image_url'       => esc_url_raw( $raw['cover'] ?? '' ),
			'is_online'       => $is_online,
			'venue_name'      => sanitize_text_field( $raw['place']['name'] ?? ( $is_online ? 'Online Event' : '' ) ),
			'venue_address'   => '',
			'venue_city'      => sanitize_text_field( $raw['place']['city'] ?? '' ),
			'organizer_name'  => sanitize_text_field( $raw['organizer']     ?? '' ),
			'organizer_url'   => esc_url_raw( $raw['organizer_url'] ?? '' ),
			'category'        => '',
			'category_id'     => '',
			'tags'            => array(),
			'is_free'         => true,
			'is_sold_out'     => false,
			'min_price'       => 0,
			'currency'        => '',
			'currency_symbol' => '',
		);
	}

	public function normalize_event_details( $raw ) {
		return array(
			'id'              => sanitize_text_field( $raw['id']   ?? '' ),
			'name'            => sanitize_text_field( $raw['name'] ?? '' ),
			'url'             => esc_url_raw( $raw['id'] ? 'https://www.facebook.com/events/' . $raw['id'] . '/' : '' ),
			'status'          => 'CONFIRMED',
			'start_local'     => sanitize_text_field( $raw['start_date'] ?? '' ),
			'start_utc'       => sanitize_text_field( $raw['start_date'] ?? '' ),
			'end_local'       => sanitize_text_field( $raw['end_date'] ?? $raw['start_date'] ?? '' ),
			'end_utc'         => sanitize_text_field( $raw['end_date'] ?? $raw['start_date'] ?? '' ),
			'timezone'        => '',
			'image_url'       => esc_url_raw( $raw['cover_image'] ?? '' ),
			'is_online'       => empty( $raw['place']['name'] ) && empty( $raw['place']['address'] ),
			'venue_name'      => sanitize_text_field( $raw['place']['name']    ?? '' ),
			'venue_address'   => sanitize_text_field( $raw['place']['address'] ?? '' ),
			'venue_city'      => '',
			'organizer_name'  => sanitize_text_field( $raw['creator']['name'] ?? '' ),
			'organizer_url'   => ( ! empty( $raw['creator']['id'] ) ) ? esc_url_raw( 'https://www.facebook.com/' . $raw['creator']['id'] ) : '',
			'category'        => '',
			'category_id'     => '',
			'tags'            => array(),
			'is_free'         => true,
			'is_sold_out'     => false,
			'min_price'       => 0,
			'currency'        => '',
			'currency_symbol' => '',
		);
	}

	// -------------------------------------------------------
	// HQ image enrichment (used in preview + on-demand)
	// -------------------------------------------------------

	public function enrich_events_with_hq_images( $events, $is_preview = false ) {
		$db = XTFEPRO_Feed_DB::instance();

		foreach ( $events as &$event ) {
			$event_id = $this->extract_base_event_id( $event );
			if ( empty( $event_id ) ) continue;

			// 1. DB cache (persists across transient clears)
			$hq = $db->get_image( $event_id );
			if ( $hq ) {
				$event['image_url'] = $hq;
				continue;
			}

			// 2. Per-event transient cache
			$cache_key     = 'xtfepro_event_details_' . $event_id;
			$event_details = get_transient( $cache_key );

			if ( $event_details && ! empty( $event_details['cover_image'] ) ) {
				$db->save_image( $event_id, $event_details['cover_image'] );
				$event['image_url'] = $event_details['cover_image'];
				continue;
			}

			// Preview: skip live fetch, use blur placeholder
			if ( $is_preview ) continue;

			// Live: fetch now (only hits if DB + transient both miss)
			if ( false === $event_details ) {
				try {
					$event_details = $this->getEventById( $event_id );
					if ( ! empty( $event_details['name'] ) ) {
						set_transient( $cache_key, $event_details, DAY_IN_SECONDS );
					}
				} catch ( \Exception $e ) {
					$event_details = false;
				}
			}

			if ( $event_details && ! empty( $event_details['cover_image'] ) ) {
				$db->save_image( $event_id, $event_details['cover_image'] );
				$event['image_url'] = $event_details['cover_image'];
			}
		}
		unset( $event );

		return $events;
	}

	// -------------------------------------------------------
	// Facebook scraping / GraphQL
	// -------------------------------------------------------

	public function getEventById( string $eventId ): array {
		// Try GraphQL first (faster, less IP-ban risk)
		$graphql_url = 'https://www.facebook.com/api/graphql/';
		$payload     = [
			'variables' => json_encode( [ 'eventID' => $eventId, 'isCrawler' => false, 'scale' => 1 ] ),
			'doc_id'    => '33843234555263685',
		];

		$ch = curl_init( $graphql_url );
		curl_setopt_array( $ch, [
			CURLOPT_POST           => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS     => http_build_query( $payload ),
			CURLOPT_HTTPHEADER     => [ 'Content-Type: application/x-www-form-urlencoded', 'User-Agent: Mozilla/5.0' ],
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_TIMEOUT        => 15,
		] );
		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $httpCode === 200 && $response ) {
			$data  = json_decode( $response, true );
			$event = $data['data']['event'] ?? null;

			if ( $event && ! empty( $event['name'] ) ) {
				$start_ts    = $event['start_timestamp']         ?? null;
				$event_place = $event['event_place']             ?? null;
				$cover_media = $event['cover_media_renderer']    ?? [];
				$cover_image_url = $this->extract_cover_image( $cover_media );

				return [
					'id'          => $eventId,
					'name'        => $event['name'],
					'start_date'  => $start_ts ? date( 'Y-m-d H:i:s', $start_ts ) : null,
					'end_date'    => null,
					'description' => null,
					'cover_image' => $cover_image_url,
					'place'       => [
						'name'    => $event_place['name'] ?? null,
						'address' => null,
						'lat'     => null,
						'lng'     => null,
					],
					'creator'     => [ 'id' => null, 'name' => null ],
				];
			}
		}

		// Fallback: HTML scraping
		$url      = "https://www.facebook.com/events/{$eventId}/?locale=en_US";
		$response = wp_remote_get( $url, [
			'headers' => [
				'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'en-GB,en-US;q=0.9,en;q=0.8',
				'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',
			],
			'timeout' => 15,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$html = wp_remote_retrieve_body( $response );
		$dom  = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();

		$scripts    = ( new \DOMXPath( $dom ) )->query( '//script[@type="application/json"]' );
		$cover_data = null;
		$hosts_data = null;

		foreach ( $scripts as $script ) {
			$text = $script->textContent;
			if ( $cover_data === null && strpos( $text, 'cover_media_renderer' ) !== false ) {
				$decoded = json_decode( $text, true );
				if ( json_last_error() === JSON_ERROR_NONE ) $cover_data = $decoded;
			}
			if ( $hosts_data === null && strpos( $text, 'event_creator' ) !== false ) {
				$decoded = json_decode( $text, true );
				if ( json_last_error() === JSON_ERROR_NONE ) $hosts_data = $decoded;
			}
			if ( $cover_data !== null && $hosts_data !== null ) break;
		}

		$creator       = $this->findKey( $hosts_data ?? [], 'event_creator' );
		$event_place   = $this->findKey( $hosts_data ?? [], 'event_place' );
		$one_line_addr = $this->findKey( $hosts_data ?? [], 'one_line_address' );
		$description   = $this->findKey( $hosts_data ?? [], 'event_description' );
		$cover_media   = $this->findKey( $cover_data ?? [], 'cover_media_renderer' );
		$start_ts      = $this->findKey( $cover_data ?? [], 'start_timestamp' );
		$end_ts        = $this->findKey( $cover_data ?? [], 'end_timestamp' );

		return [
			'id'          => $eventId,
			'name'        => $this->findKey( $cover_data ?? [], 'name' ),
			'start_date'  => $start_ts ? date( 'Y-m-d H:i:s', $start_ts ) : null,
			'end_date'    => $end_ts   ? date( 'Y-m-d H:i:s', $end_ts )   : null,
			'description' => $description['text'] ?? null,
			'cover_image' => $this->extract_cover_image( $cover_media ?? [] ),
			'place'       => [
				'name'    => $event_place['name']              ?? null,
				'address' => $one_line_addr                    ?? null,
				'lat'     => $event_place['location']['latitude']  ?? null,
				'lng'     => $event_place['location']['longitude'] ?? null,
			],
			'creator'     => [
				'id'   => $creator['id']   ?? null,
				'name' => $creator['name'] ?? null,
			],
		];
	}

	/**
	 * Extract cover image URL from cover_media_renderer — tries photo, video thumbnail.
	 */
	private function extract_cover_image( array $cover_media ): ?string {
		// Way 1: Standard photo
		$cover_photo = $cover_media['cover_photo']['photo']['full_image'] ?? null;
		if ( ! empty( $cover_photo['uri'] ) ) return $cover_photo['uri'];

		// Way 2: Video preferred_thumbnail
		$preferred_thumbnail = $this->findKey( $cover_media, 'preferred_thumbnail' );
		if ( ! empty( $preferred_thumbnail ) ) {
			$img = $this->findKey( $preferred_thumbnail, 'image' );
			if ( ! empty( $img['uri'] ) ) return $img['uri'];
		}

		// Way 3: cover_video > preferred_thumbnail
		$cover_video = $this->findKey( $cover_media, 'cover_video' );
		if ( ! empty( $cover_video ) ) {
			$thumb = $this->findKey( $cover_video, 'preferred_thumbnail' );
			if ( ! empty( $thumb ) ) {
				$img = $this->findKey( $thumb, 'image' );
				if ( ! empty( $img['uri'] ) ) return $img['uri'];
			}
		}

		return null;
	}

	// -------------------------------------------------------
	// Filters, sorting, dedup
	// -------------------------------------------------------

	public function apply_local_filters( $events, $meta ) {
		// Remove events that have no title (usually private or restricted events)
		$events = array_filter( $events, fn( $e ) => ! empty( $e['name'] ) );

		if ( ! empty( $meta['hide_online'] ) ) {
			$events = array_filter( $events, fn( $e ) => ! $e['is_online'] );
		}

		if ( ! empty( $meta['time_filter'] ) && 'all' !== $meta['time_filter'] ) {
			$filter       = $meta['time_filter'];
			$now          = time();
			$today_end    = strtotime( 'tomorrow' ) - 1;
			$custom_start = ! empty( $meta['start_date'] ) ? strtotime( $meta['start_date'] ) : 0;
			$custom_end   = ! empty( $meta['end_date'] )   ? strtotime( $meta['end_date'] . ' 23:59:59' ) : 2147483647;
			if ( false === $custom_start ) $custom_start = 0;
			if ( false === $custom_end )   $custom_end   = 2147483647;

			$events = array_filter( $events, function( $e ) use ( $filter, $now, $today_end, $custom_start, $custom_end ) {
				$start_ts = strtotime( $e['start_local'] );
				$end_ts   = ! empty( $e['end_local'] ) ? strtotime( $e['end_local'] ) : $start_ts;
				if ( ! $end_ts ) return true;

				switch ( $filter ) {
					case 'today':           return ( $end_ts >= $now && $start_ts <= $today_end );
					case 'upcoming_week':   return ( $end_ts >= $now && $start_ts <= $now + 7  * DAY_IN_SECONDS );
					case 'upcoming_15_days':return ( $end_ts >= $now && $start_ts <= $now + 15 * DAY_IN_SECONDS );
					case 'upcoming_month':  return ( $end_ts >= $now && $start_ts <= $now + 30 * DAY_IN_SECONDS );
					case 'current_future':  return $end_ts >= $now;
					case 'custom':          return ( $end_ts >= $now && $start_ts <= $custom_end );
					default:                return $end_ts >= $now;
				}
			} );
		}

		return array_values( $events );
	}

	public function dedup( $events ) {
		$unique = array();
		$seen   = array();

		foreach ( $events as $event ) {
			$id = (string) ( $event['id'] ?? '' );

			if ( $id !== '' ) {
				if ( isset( $seen[ 'id:' . $id ] ) ) continue;
				$seen[ 'id:' . $id ] = true;
			}

			$name  = $event['name']        ?? '';
			$start = $event['start_local'] ?? '';
			if ( $name !== '' && $start !== '' ) {
				$day       = substr( $start, 0, 10 );
				$norm_name = preg_replace( '/[^\p{L}\p{N}]/u', '', mb_strtolower( $name, 'UTF-8' ) );
				$key       = 'name_date:' . $norm_name . '_' . $day;
				if ( isset( $seen[ $key ] ) ) continue;
				$seen[ $key ] = true;
			}

			if ( $id === '' && ( $name === '' || $start === '' ) ) {
				$raw_key = 'raw:' . md5( wp_json_encode( $event ) );
				if ( isset( $seen[ $raw_key ] ) ) continue;
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
		if ( array_key_exists( $key, $arr ) ) return $arr[ $key ];
		foreach ( $arr as $v ) {
			if ( is_array( $v ) ) {
				$result = $this->findKey( $v, $key );
				if ( $result !== null ) return $result;
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
			$parts  = explode( '/', $path );
			if ( 'pages' === $parts[0] && isset( $parts[2] ) ) {
				return sanitize_text_field( $parts[2] );
			}
			return sanitize_text_field( $parts[0] );
		}
		return sanitize_text_field( $page_url );
	}

	private function extract_base_event_id( $event ) {
		$event_id = (string) ( $event['id']  ?? '' );
		$url      = (string) ( $event['url'] ?? '' );
		if ( preg_match( '/events\/(\d+)/', $url, $matches ) ) {
			return $matches[1];
		}
		return $event_id;
	}

	// -------------------------------------------------------
	// Feed meta
	// -------------------------------------------------------

	public function get_feed_meta( $feed_id ) {
		$time_filter     = get_post_meta( $feed_id, '_xtfeprofeed_time_filter',    true ) ?: 'current_future';
		$register_label  = get_post_meta( $feed_id, '_xtfeprofeed_register_label', true ) ?: __( 'View Event', 'xt-facebook-events-pro' );

		$allowed_sources = apply_filters( 'xtfeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) );
		$source_type     = get_post_meta( $feed_id, '_xtfeprofeed_source_type', true );
		if ( empty( $source_type ) ) {
			$source_type = in_array( 'page_id', $allowed_sources, true ) ? 'page_id' : 'event_ids';
		}
		if ( ! in_array( $source_type, $allowed_sources, true ) ) {
			$source_type = 'event_ids';
		}

		$allowed_layouts = apply_filters( 'xtfeprofeed_allowed_layouts', array( 'card-grid', 'list' ) );
		$layout          = get_post_meta( $feed_id, '_xtfeprofeed_layout', true ) ?: 'card-grid';
		if ( ! in_array( $layout, $allowed_layouts, true ) ) {
			$layout = 'card-grid';
		}

		return array(
			'source_type'     => $source_type,
			'page_id'         => get_post_meta( $feed_id, '_xtfeprofeed_page_id',        true ),
			'group_id'        => get_post_meta( $feed_id, '_xtfeprofeed_group_id',       true ),
			'event_ids'       => get_post_meta( $feed_id, '_xtfeprofeed_event_ids',       true ),
			'ical_url'        => get_post_meta( $feed_id, '_xtfeprofeed_ical_url',        true ),
			'time_filter'     => $time_filter,
			'start_date'      => get_post_meta( $feed_id, '_xtfeprofeed_start_date',      true ),
			'end_date'        => get_post_meta( $feed_id, '_xtfeprofeed_end_date',        true ),
			'category_id'     => '',
			'tag_query'       => '',
			'tags_filter'     => '',
			'hide_online'     => get_post_meta( $feed_id, '_xtfeprofeed_hide_online',     true ),
			'layout'          => $layout,
			'columns'         => absint( get_post_meta( $feed_id, '_xtfeprofeed_columns', true ) ?: 3 ),
			'show_image'      => get_post_meta( $feed_id, '_xtfeprofeed_show_image',      true ) !== '0',
			'show_date'       => get_post_meta( $feed_id, '_xtfeprofeed_show_date',       true ) !== '0',
			'show_venue'      => get_post_meta( $feed_id, '_xtfeprofeed_show_venue',      true ) !== '0',
			'show_organizer'  => get_post_meta( $feed_id, '_xtfeprofeed_show_organizer',  true ),
			'show_price'      => get_post_meta( $feed_id, '_xtfeprofeed_show_price',      true ) !== '0',
			'show_category'   => false,
			'show_tags'       => false,
			'show_ticket_btn' => get_post_meta( $feed_id, '_xtfeprofeed_show_ticket_btn', true ) !== '0',
			'ticket_style'    => 'link',
			'free_label'      => __( 'Free', 'xt-facebook-events-pro' ),
			'paid_label'      => __( 'Paid', 'xt-facebook-events-pro' ),
			'register_label'  => $register_label,
			'pagination_type' => get_post_meta( $feed_id, '_xtfeprofeed_pagination_type', true ) ?: 'ajax',
			'per_page'        => absint( get_post_meta( $feed_id, '_xtfeprofeed_per_page', true ) ?: 12 ),
			'cache_duration'  => absint( get_post_meta( $feed_id, '_xtfeprofeed_cache_duration', true ) ?: 1440 ),
			'auto_refresh'    => get_post_meta( $feed_id, '_xtfeprofeed_auto_refresh',    true ),
			'custom_css'      => get_post_meta( $feed_id, '_xtfeprofeed_custom_css',      true ),
		);
	}
}
