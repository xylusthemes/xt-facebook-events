<?php
/**
 * XT Facebook Events Pro Live Feed - AJAX Handlers
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_AJAX {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'wp_ajax_xtfeprofeed_clear_cache',   array( $this, 'clear_cache' ) );
		add_action( 'wp_ajax_xtfeprofeed_clear_hard_cache', array( $this, 'clear_hard_cache' ) );
		add_action( 'wp_ajax_xtfeprofeed_preview_feed',  array( $this, 'preview_feed' ) );
		add_action( 'wp_ajax_xtfeprofeed_live_preview',  array( $this, 'live_preview' ) );
		add_action( 'wp_ajax_nopriv_xtfeprofeed_load_page', array( $this, 'load_paginated_page' ) );
		add_action( 'wp_ajax_xtfeprofeed_load_page',        array( $this, 'load_paginated_page' ) );
	}

	// -------------------------------------------------------
	// Clear cache
	// -------------------------------------------------------

	public function clear_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'xt-facebook-events-pro' ) ) );
		}
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		$feed_id = absint( $_POST['feed_id'] ?? 0 );
		if ( ! wp_verify_nonce( $nonce, 'xtfeprofeed_clear_cache_' . $feed_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'xt-facebook-events-pro' ) ) );
		}
		if ( ! $feed_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'xt-facebook-events-pro' ) ) );
		}
		XTFEPRO_Feed_API::instance()->clear_cache( $feed_id );
		wp_send_json_success( array( 'message' => __( 'Cache cleared! Next page load will fetch fresh data from Facebook.', 'xt-facebook-events-pro' ) ) );
	}

	// -------------------------------------------------------
	// Clear hard cache (HQ Images)
	// -------------------------------------------------------

	public function clear_hard_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'xt-facebook-events-pro' ) ) );
		}
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		$feed_id = absint( $_POST['feed_id'] ?? 0 );
		if ( ! wp_verify_nonce( $nonce, 'xtfeprofeed_clear_hard_cache' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'xt-facebook-events-pro' ) ) );
		}
		if ( ! $feed_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'xt-facebook-events-pro' ) ) );
		}

		// Delete all cached images from DB
		XTFEPRO_Feed_DB::instance()->delete_all_images();

		// Clear feed cache
		XTFEPRO_Feed_API::instance()->clear_cache( $feed_id );

		wp_send_json_success( array( 'message' => __( 'Hard cache cleared! Re-fetching HQ images will start on next page load.', 'xt-facebook-events-pro' ) ) );
	}

	// -------------------------------------------------------
	// Preview feed (admin)
	// -------------------------------------------------------

	public function preview_feed() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'xt-facebook-events-pro' ) ) );
		}
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		$feed_id = absint( $_POST['feed_id'] ?? 0 );
		if ( ! wp_verify_nonce( $nonce, 'xtfeprofeed_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'xt-facebook-events-pro' ) ) );
		}
		$events = XTFEPRO_Feed_API::instance()->get_events( $feed_id, true );
		if ( is_wp_error( $events ) ) {
			wp_send_json_error( array( 'message' => $events->get_error_message() ) );
		}
		wp_send_json_success( array(
			'total'   => count( $events ),
			'message' => sprintf( __( '%d events fetched successfully.', 'xt-facebook-events-pro' ), count( $events ) ),
		) );
	}

	// -------------------------------------------------------
	// Public: paginated page load
	// -------------------------------------------------------

	public function load_paginated_page() {
		$feed_id  = absint( $_POST['feed_id'] ?? 0 );
		$page     = absint( $_POST['page'] ?? 1 );
		$per_page = absint( $_POST['per_page'] ?? 12 );

		if ( ! $feed_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'xt-facebook-events-pro' ) ) );
		}

		$feed_post = get_post( $feed_id );
		if ( ! $feed_post || XTFEPRO_FEED_CPT !== $feed_post->post_type || 'publish' !== $feed_post->post_status ) {
			wp_send_json_error( array( 'message' => __( 'Feed not found.', 'xt-facebook-events-pro' ) ) );
		}

		$events = XTFEPRO_Feed_API::instance()->get_events( $feed_id, false, $page );
		if ( is_wp_error( $events ) ) {
			wp_send_json_error( array( 'message' => $events->get_error_message() ) );
		}
		if ( empty( $events ) ) {
			wp_send_json_error( array( 'message' => __( 'No events found.', 'xt-facebook-events-pro' ) ) );
		}

		$meta         = XTFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );
		$total_events = count( $events );
		$total_pages  = ceil( $total_events / $per_page );
		$page         = max( 1, min( $page, $total_pages ) );
		$page_events  = array_slice( $events, ( $page - 1 ) * $per_page, $per_page );

		ob_start();
		foreach ( $page_events as $event ) {
			XTFEPRO_Feed_Shortcode::instance()->render_event_card( $event, $meta );
		}
		$events_html = ob_get_clean();

		ob_start();
		XTFEPRO_Feed_Shortcode::instance()->render_pagination( $page, $total_pages, $total_events, $per_page, $meta['pagination_type'] ?? 'ajax' );
		$pagination_html = ob_get_clean();

		wp_send_json_success( array(
			'events_html'     => $events_html,
			'pagination_html' => $pagination_html,
			'current_page'    => $page,
			'total_pages'     => $total_pages,
			'total_events'    => $total_events,
		) );
	}

	// -------------------------------------------------------
	// Live preview (admin builder)
	// -------------------------------------------------------

	public function live_preview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'xt-facebook-events-pro' ) ) );
		}

		$feed_id = absint( $_POST['feed_id'] ?? 0 );

		$time_filter = sanitize_text_field( $_POST['_xtfeprofeed_time_filter'] ?? 'all' );
		if ( 'past' === $time_filter ) {
			$time_filter = 'current_future';
		}

		$register_label = sanitize_text_field( $_POST['_xtfeprofeed_register_label'] ?? __( 'View Event', 'xt-facebook-events-pro' ) );

		$posted_meta = array(
			'source_type'     => sanitize_text_field( $_POST['_xtfeprofeed_source_type'] ?? 'page_id' ),
			'page_id'         => sanitize_text_field( $_POST['_xtfeprofeed_page_id'] ?? '' ),
			'event_ids'       => sanitize_text_field( $_POST['_xtfeprofeed_event_ids'] ?? '' ),
			'ical_url'        => sanitize_text_field( $_POST['_xtfeprofeed_ical_url'] ?? '' ),
			'time_filter'     => $time_filter,
			'cache_duration'  => (function() {
				$cache_val = $_POST['_xtfeprofeed_cache_duration'] ?? '1440';
				if ( 'custom' === $cache_val ) {
					$custom_hours = max( 1, absint( $_POST['_xtfeprofeed_cache_duration_custom'] ?? 5 ) );
					return $custom_hours * 60;
				}
				return absint( $cache_val ) ?: 1440;
			})(),
			'pagination_type' => sanitize_text_field( $_POST['_xtfeprofeed_pagination_type'] ?? 'ajax' ),
			'per_page'        => absint( $_POST['_xtfeprofeed_per_page'] ?? 12 ),
			'layout'          => sanitize_text_field( $_POST['_xtfeprofeed_layout'] ?? 'card-grid' ),
			'columns'         => absint( $_POST['_xtfeprofeed_columns'] ?? 3 ),
			'show_image'      => ! empty( $_POST['_xtfeprofeed_show_image'] ),
			'show_date'       => ! empty( $_POST['_xtfeprofeed_show_date'] ),
			'show_venue'      => ! empty( $_POST['_xtfeprofeed_show_venue'] ),
			'show_organizer'  => ! empty( $_POST['_xtfeprofeed_show_organizer'] ),
			'show_price'      => ! empty( $_POST['_xtfeprofeed_show_price'] ),
			'show_category'   => false,
			'show_tags'       => false,
			'show_ticket_btn' => ! empty( $_POST['_xtfeprofeed_show_ticket_btn'] ),
			'ticket_style'    => 'link',
			'free_label'      => __( 'Free', 'xt-facebook-events-pro' ),
			'paid_label'      => __( 'Paid', 'xt-facebook-events-pro' ),
			'register_label'  => $register_label,
			'hide_online'     => ! empty( $_POST['_xtfeprofeed_hide_online'] ),
			'start_date'      => sanitize_text_field( $_POST['_xtfeprofeed_start_date'] ?? '' ),
			'end_date'        => sanitize_text_field( $_POST['_xtfeprofeed_end_date'] ?? '' ),
			'category_id'     => '',
			'tag_query'       => '',
			'tags_filter'     => '',
			'is_preview'      => true,
			'feed_id'         => $feed_id,
		);

		// Fallback to saved meta if source field empty
		if ( 'page_id' === $posted_meta['source_type'] && empty( $posted_meta['page_id'] ) && $feed_id ) {
			$saved = XTFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );
			$posted_meta['page_id'] = $saved['page_id'] ?? '';
		} elseif ( 'event_ids' === $posted_meta['source_type'] && empty( $posted_meta['event_ids'] ) && $feed_id ) {
			$saved = XTFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );
			$posted_meta['event_ids'] = $saved['event_ids'] ?? '';
		} elseif ( 'ical_url' === $posted_meta['source_type'] && empty( $posted_meta['ical_url'] ) && $feed_id ) {
			$saved = XTFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );
			$posted_meta['ical_url'] = $saved['ical_url'] ?? '';
		}

		$is_full_preview = ! empty( $_POST['is_full_preview'] ) && 'true' === $_POST['is_full_preview'];

		$events = XTFEPRO_Feed_API::instance()->fetch_preview_events( $posted_meta );

		if ( is_wp_error( $events ) ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Could not load preview: %s. Please check your Source settings.', 'xt-facebook-events-pro' ), $events->get_error_message() ) ) );
		}

		if ( empty( $events ) ) {
			wp_send_json_error( array( 'message' => __( 'No events found. Please verify your Source Data.', 'xt-facebook-events-pro' ) ) );
		}

		$per_page       = absint( $posted_meta['per_page'] );
		$preview_limit  = $is_full_preview ? max( 1, $per_page ) : min( 5, max( 1, $per_page ) );
		$preview_events = array_slice( $events, 0, $preview_limit );

		ob_start();
		?>
		<div class="xtfeprofeed-feed-wrap xtfeprofeed-layout-<?php echo esc_attr( $posted_meta['layout'] ); ?> xtfeprofeed-cols-<?php echo esc_attr( $posted_meta['columns'] ); ?> xtfeprofeed-preview-sample">
			<div class="xtfeprofeed-events-grid">
				<?php foreach ( $preview_events as $event ) : ?>
					<?php XTFEPRO_Feed_Shortcode::instance()->render_event_card( $event, $posted_meta ); ?>
				<?php endforeach; ?>
			</div>
			<div style="margin-top: 15px; font-size: 11px; color: #777; text-align: center; font-style: italic;">
				<?php esc_html_e( 'Note: Low-quality/blurry images may display here in Live Preview for new events to keep the editor fast. High-quality HD images will load automatically on the front-end.', 'xt-facebook-events-pro' ); ?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
