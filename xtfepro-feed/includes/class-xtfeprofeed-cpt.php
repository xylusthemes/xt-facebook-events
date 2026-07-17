<?php
/**
 * XT Facebook Events Pro Live Feed - Custom Post Type
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_CPT {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function register_cpt() {
		$labels = array(
			'name'               => __( 'Facebook Widget', 'xt-facebook-events-pro' ),
			'singular_name'      => __( 'Facebook Widget', 'xt-facebook-events-pro' ),
			'add_new'            => __( 'Add New Widget', 'xt-facebook-events-pro' ),
			'add_new_item'       => __( 'Add New Facebook Widget', 'xt-facebook-events-pro' ),
			'edit_item'          => __( 'Edit Facebook Widget', 'xt-facebook-events-pro' ),
			'new_item'           => __( 'New Facebook Widget', 'xt-facebook-events-pro' ),
			'view_item'          => __( 'View Facebook Widget', 'xt-facebook-events-pro' ),
			'search_items'       => __( 'Search Facebook Widgets', 'xt-facebook-events-pro' ),
			'not_found'          => __( 'No widgets found.', 'xt-facebook-events-pro' ),
			'not_found_in_trash' => __( 'No widgets found in Trash.', 'xt-facebook-events-pro' ),
			'menu_name'          => __( 'Facebook Widget', 'xt-facebook-events-pro' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'capability_type'   => 'post',
			'hierarchical'      => false,
			'supports'          => array( 'title' ),
			'has_archive'       => false,
			'rewrite'           => false,
			'query_var'         => false,
			'menu_icon'         => 'dashicons-facebook',
		);

		register_post_type( XTFEPRO_FEED_CPT, $args );
	}

	public function init_admin_hooks() {
		add_filter( 'manage_' . XTFEPRO_FEED_CPT . '_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_' . XTFEPRO_FEED_CPT . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_row_actions' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_notices', array( $this, 'show_cache_cleared_notice' ) );
		add_action( 'admin_post_xtfeprofeed_clear_cache', array( $this, 'handle_clear_cache_row_action' ) );
		add_action( 'before_delete_post', array( $this, 'clear_cache_on_delete' ), 10, 2 );
		add_action( 'wp_trash_post', array( $this, 'clear_cache_on_trash' ) );
		add_action( 'untrash_post', array( $this, 'clear_cache_on_untrash' ) );

		add_action( 'in_admin_header', array( $this, 'render_admin_header' ) );
		add_action( 'in_admin_footer', array( $this, 'render_admin_footer' ) );
	}

	public function render_admin_header() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . XTFEPRO_FEED_CPT !== $screen->id ) return;

		$admin_instance = XT_Facebook_Events::instance()->admin;
		if ( $admin_instance ) {
			$admin_instance->xtfe_render_common_header( __( 'Facebook Widget', 'xt-facebook-events-pro' ) );
		}

		echo '<style>
			.xtfe-container { max-width: 1600px !important; margin: 0 auto; }
			#wpbody-content > .wrap {
				max-width: 1600px !important;
				margin: 24px auto !important;
				padding: 24px !important;
				border-radius: 12px !important;
				border: 1px solid #e2e8f0 !important;
				background: #fff !important;
				box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
				box-sizing: border-box;
			}
			#wpbody-content .wrap > h1, .wp-heading-inline { display: none !important; }
			#wpbody-content .wrap > .page-title-action {
				display: inline-block !important;
				background: #005ae0;
				color: #fff;
				border-radius: 6px;
				padding: 0px 15px;
				line-height: 32px;
				text-decoration: none;
				font-weight: 600;
				font-size: 13px;
				border: none;
				margin-bottom: 20px;
				box-shadow: 0 1px 2px rgba(0,0,0,0.05);
				transition: background 0.2s ease;
			}
			#wpbody-content .wrap > .page-title-action:hover {
				background: #0046b5;
				color: #fff;
			}
			.wp-list-table { border: 1px solid #e2e8f0 !important; box-shadow: none !important; border-radius: 8px; overflow: hidden; margin-top: 16px !important; }
			.wp-list-table thead, .wp-list-table tfoot { background-color: #f8fafc; }
			.wp-list-table th { color: #475569 !important; font-weight: 600 !important; border-bottom: 1px solid #e2e8f0 !important; }
			.wp-list-table td { color: #334155 !important; border-bottom: 1px solid #e2e8f0 !important; vertical-align: middle; }
			.wp-list-table tbody tr:hover { background-color: #f8fafc !important; }
			.tablenav .actions select { border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 8px; min-height: 32px; color: #475569; width: 10rem; }
			.tablenav .button, .search-box .button { border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #334155; font-weight: 600; padding: 0 12px; min-height: 32px; transition: all 0.2s; }
			.tablenav .button:hover, .search-box .button:hover { background: #f8fafc; border-color: #94a3b8; }
			.search-box input[type="search"] { border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; min-height: 32px; }
			.subsubsub a { color: #64748b; font-weight: 500; }
			.subsubsub a.current { color: #005ae0; font-weight: 700; }

			/* Hide Screen Options */
			#screen-meta, #screen-meta-links { display: none !important; }

			/* Hide all default WordPress notices outside our container */
			#wpbody-content > .notice,
			#wpbody-content > .updated,
			#wpbody-content > .error,
			#wpbody-content > .update-nag {
				display: none !important;
			}
		</style>';
	}

	public function render_admin_footer() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . XTFEPRO_FEED_CPT !== $screen->id ) return;

		$admin_instance = XT_Facebook_Events::instance()->admin;
		if ( $admin_instance ) {
			$admin_instance->xtfe_render_common_footer();
		}
	}

	public function add_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['xtfeprofeed_shortcode']    = __( 'Shortcode', 'xt-facebook-events-pro' );
				$new['xtfeprofeed_source']       = __( 'Source', 'xt-facebook-events-pro' );
				$new['xtfeprofeed_cache_status'] = __( 'Cache Status', 'xt-facebook-events-pro' );
				$new['xtfeprofeed_last_fetched'] = __( 'Last Fetched', 'xt-facebook-events-pro' );
			}
		}
		return $new;
	}

	public function render_column( $column, $post_id ) {
		switch ( $column ) {

			case 'xtfeprofeed_shortcode':
				$shortcode = '[xtfepro_live_feed id="' . $post_id . '"]';
				echo '<code class="xtfeprofeed-copy-shortcode" data-shortcode="' . esc_attr( $shortcode ) . '" style="cursor:pointer;" title="' . esc_attr__( 'Click to copy', 'xt-facebook-events-pro' ) . '">';
				echo esc_html( $shortcode );
				echo '</code> <span class="xtfeprofeed-copied" style="display:none;color:green;">&#10003;</span>';
				break;

			case 'xtfeprofeed_source':
				$source_type = get_post_meta( $post_id, '_xtfeprofeed_source_type', true );
				$map = array(
					'page_id'   => __( 'Page ID/Slug', 'xt-facebook-events-pro' ),
					'group_id'  => __( 'Group URL/ID', 'xt-facebook-events-pro' ),
					'event_ids' => __( 'Event IDs', 'xt-facebook-events-pro' ),
					'ical_url'  => __( 'iCal URL', 'xt-facebook-events-pro' ),
				);
				echo esc_html( $map[ $source_type ] ?? '—' );
				break;

			case 'xtfeprofeed_cache_status':
				$cache_key   = 'xtfeprofeed_p_' . $post_id . '_all';
				$cached      = get_transient( $cache_key );
				$timeout_key = '_transient_timeout_' . $cache_key;
				$expires_at  = get_option( $timeout_key );
				if ( false !== $cached && $expires_at ) {
					$remaining = $expires_at - time();
					if ( $remaining > 0 ) {
						printf( '<span style="color:green;">&#9679; ' . esc_html__( 'Cached (%s left)', 'xt-facebook-events-pro' ) . '</span>', human_time_diff( time(), $expires_at ) );
					} else {
						echo '<span style="color:orange;">&#9679; ' . esc_html__( 'Expired', 'xt-facebook-events-pro' ) . '</span>';
					}
				} else {
					echo '<span style="color:#aaa;">&#9679; ' . esc_html__( 'Not cached', 'xt-facebook-events-pro' ) . '</span>';
				}
				break;

			case 'xtfeprofeed_last_fetched':
				$last = get_post_meta( $post_id, '_xtfeprofeed_last_fetched', true );
				echo $last ? esc_html( human_time_diff( $last, time() ) . ' ago' ) : '—';
				break;
		}
	}

	public function add_row_actions( $actions, $post ) {
		if ( XTFEPRO_FEED_CPT !== $post->post_type ) return $actions;
		$nonce = wp_create_nonce( 'xtfeprofeed_clear_cache_' . $post->ID );
		$url   = add_query_arg(
			array( 'action' => 'xtfeprofeed_clear_cache', 'feed_id' => $post->ID, '_wpnonce' => $nonce ),
			admin_url( 'admin-post.php' )
		);
		$actions['xtfeprofeed_clear_cache'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Clear Cache', 'xt-facebook-events-pro' ) . '</a>';
		return $actions;
	}

	public function handle_clear_cache_row_action() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Permission denied.' );
		$feed_id = absint( $_GET['feed_id'] ?? 0 );
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'xtfeprofeed_clear_cache_' . $feed_id ) ) {
			wp_die( 'Security check failed.' );
		}
		XTFEPRO_Feed_API::instance()->clear_cache( $feed_id );
		wp_redirect( admin_url( 'edit.php?post_type=' . XTFEPRO_FEED_CPT . '&xtfeprofeed_cache_cleared=1' ) );
		exit;
	}

	public function show_cache_cleared_notice() {
		if ( ! empty( $_GET['xtfeprofeed_cache_cleared'] ) && get_current_screen()->post_type === XTFEPRO_FEED_CPT ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Feed cache cleared.', 'xt-facebook-events-pro' ) . '</p></div>';
		}
	}

	public function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || XTFEPRO_FEED_CPT !== $screen->post_type ) return;
		wp_enqueue_style( 'xtfeprofeed-admin', XTFEPRO_FEED_URL . 'assets/feed-admin.css', array(), XTFEPRO_FEED_VERSION );
		wp_enqueue_style( 'xtfeprofeed-public', XTFEPRO_FEED_URL . 'assets/feed-public.css', array(), XTFEPRO_FEED_VERSION );
		
		// Enqueue the global plugin CSS for header/footer layout
		wp_enqueue_style( 'xt-facebook-events-admin', plugin_dir_url( __FILE__ ) . '../../assets/css/xt-facebook-events-admin.css', array(), XTFEPRO_FEED_VERSION, 'all' );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_script( 'xtfeprofeed-admin', XTFEPRO_FEED_URL . 'assets/feed-admin.js', array( 'jquery', 'jquery-ui-datepicker' ), XTFEPRO_FEED_VERSION, true );
		wp_localize_script( 'xtfeprofeed-admin', 'xtfeproFeedAdmin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'xtfeprofeed_admin_nonce' ),
			'i18n'     => array(
				'copied'        => __( 'Copied!', 'xt-facebook-events-pro' ),
				'cache_cleared' => __( 'Cache cleared!', 'xt-facebook-events-pro' ),
				'cache_error'   => __( 'Failed. Try again.', 'xt-facebook-events-pro' ),
				'clearing'      => __( 'Clearing...', 'xt-facebook-events-pro' ),
				'hard_cleared'  => __( 'Hard cache cleared! Re-fetching HQ images will start.', 'xt-facebook-events-pro' ),
			),
		) );
	}

	public function clear_cache_on_delete( $post_id, $post ) {
		if ( XTFEPRO_FEED_CPT !== $post->post_type ) return;
		XTFEPRO_Feed_API::instance()->clear_cache( $post_id );
	}

	public function clear_cache_on_trash( $post_id ) {
		if ( get_post_type( $post_id ) !== XTFEPRO_FEED_CPT ) return;
		XTFEPRO_Feed_API::instance()->clear_cache( $post_id );
	}

	public function clear_cache_on_untrash( $post_id ) {
		if ( get_post_type( $post_id ) !== XTFEPRO_FEED_CPT ) return;
		XTFEPRO_Feed_API::instance()->clear_cache( $post_id );
	}
}
