<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     XT_Facebook_Events
 * @subpackage  XT_Facebook_Events/admin
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     XT_Facebook_Events
 * @subpackage  XT_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class XT_Facebook_Events_Admin {


	public $adminpage_url;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->adminpage_url = admin_url('admin.php?page=wpfb_events' );
		// register the widget
		add_action( 'widgets_init', function(){
			register_widget( 'XT_Facebook_Events_Widget' );
		});
		add_action( 'admin_menu', array( $this, 'add_menu_pages') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
		add_action( 'admin_notices', array( $this, 'display_notices') );
		add_action( 'xtfe_display_all_notice', array( $this, 'display_notices') );
		add_filter( 'admin_footer_text', array( $this, 'add_xt_facebook_events_credit' ) );
		add_filter( 'submenu_file', array( $this, 'get_selected_tab_submenu_xtfe' ) );
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages() {

		add_menu_page( esc_attr__( 'Facebook Events', 'xt-facebook-events' ), esc_attr__( 'Facebook Events', 'xt-facebook-events' ), 'manage_options', 'wpfb_events', array( $this, 'admin_page' ), 'dashicons-facebook', '70' );
		global $submenu;	
		$submenu['wpfb_events'][] = array( __( 'Facebook Events', 'xt-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=wpfb_events&tab=settings' ) );
		if ( post_type_exists( 'xtfepro_live_feed' ) ) {
			$submenu['wpfb_events'][] = array(
				'<span style="display:flex; justify-content:space-between; align-items:center; width:100%;">' 
					. __( 'Facebook Widget', 'xt-facebook-events' ) 
					. '<span style="background:#4CAF50; margin-left:6px; flex-shrink:0;height: 22px;border-radius: 3px;color: #FFF;font-size: 12px;line-height: 18px;font-weight: 600;display: inline-flex;padding: 0 4px;align-items: center;">NEW</span>'
				. '</span>',
				'manage_options',
				'edit.php?post_type=xtfepro_live_feed'
			);
		}
		$submenu['wpfb_events'][] = array( __( 'Shortcodes & Widgets', 'xt-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=wpfb_events&tab=shortandwid' ));
		if ( xtfe_is_pro() ) {
			$submenu['wpfb_events'][] = array( __( 'License', 'xt-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=wpfb_events&tab=license' ));
		}
		$submenu['wpfb_events'][] = array( __( 'Support & help', 'xt-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=wpfb_events&tab=support' ));
		if( !xtfe_is_pro() ){
        	$submenu['wpfb_events'][] = array( '<li style="background-color: #1da867;" class="current">' . __( 'Upgrade to Pro', 'xt-facebook-events' ) . '</li>', 'manage_options', esc_url( "https://xylusthemes.com/plugins/xt-facebook-events/") );
		}
	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_scripts( $hook ) {
		$js_dir  = XTFE_PLUGIN_URL . 'assets/js/';
		wp_enqueue_script( 'xt-facebook-events', $js_dir . 'xt-facebook-events-admin.js', array( 'jquery', 'wp-color-picker' ), XTFE_VERSION, true );		
	}

	/**
	 * Tab Submenu got selected.
	 *
	 * @since 1.6.7
	 * @return void
	 */
	public function get_selected_tab_submenu_xtfe( $submenu_file ){
		if( !empty( $_GET['page'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) == 'wpfb_events' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$allowed_tabs = array( 'settings', 'shortandwid', 'support', 'logs' );
			if ( xtfe_is_pro() ) {
				$allowed_tabs[] = 'license';
			}
			$tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if( in_array( $tab, $allowed_tabs ) ){
				$submenu_file = admin_url( 'admin.php?page=wpfb_events&tab='.$tab );
			}
		}

		global $post_type;
		if ( 'xtfepro_live_feed' === $post_type ) {
			$submenu_file = 'edit.php?post_type=xtfepro_live_feed';
		}

		return $submenu_file;
	}

	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_styles( $hook ) {

		global $pagenow;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if( 'wpfb_events' == $page || $pagenow == 'widgets.php' ){
		  	$css_dir = XTFE_PLUGIN_URL . 'assets/css/';
			wp_enqueue_style('xt-facebook-events', $css_dir . 'xt-facebook-events-admin.css', array(), XTFE_VERSION );
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
	 * Load Admin page.
	 *
	 * @since 1.0
	 * @return void
	 */
	function admin_page() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		
		if ( $tab == 'support' ) {
			$page_title = __( 'Support & Help', 'xt-facebook-events' );
		} elseif ( $tab == 'shortandwid' ) {
			$page_title = __( 'Shortcodes & Widgets', 'xt-facebook-events' );
		} elseif ( $tab == 'license' ) {
			$page_title = __( 'License', 'xt-facebook-events' );
		} elseif ( $tab == 'logs' ) {
			$page_title = __( 'API Logs', 'xt-facebook-events' );
		} else {
			$page_title = __( 'Settings', 'xt-facebook-events' );
		}

		$this->xtfe_render_common_header( $page_title );
		?>
		<div class="xtfe-container">
			<div class="xtfe-wrap">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<?php do_action( 'xtfe_display_all_notice' ); ?>
						<div class="delete_notice"></div>

						<div id="postbox-container-2" class="postbox-container">
							<div class="xtfe-app" style="margin-bottom: 20px;" >
								<div class="xtfe-tabs">
									<div class="tabs-scroller">
										<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
											<div class="var-tabs__tab-wrap var-tabs--layout-horizontal">
												<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $tab == 'settings' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
													<span class="tab-label"><?php esc_html_e( 'Settings', 'xt-facebook-events' ); ?></span>
												</a>
												<a href="<?php echo esc_url( add_query_arg( 'tab', 'shortandwid', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $tab == 'shortandwid' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
													<span class="tab-label"><?php esc_html_e( 'Shortcodes & Widgets', 'xt-facebook-events' ); ?></span>
												</a>
												<?php if ( xtfe_is_pro() ) { ?>
												<a href="<?php echo esc_url( add_query_arg( 'tab', 'license', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $tab == 'license' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
													<span class="tab-label"><?php esc_html_e( 'License', 'xt-facebook-events' ); ?></span>
												</a>
												<?php } ?>
												<a href="<?php echo esc_url( add_query_arg( 'tab', 'support', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $tab == 'support' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
													<span class="tab-label"><?php esc_html_e( 'Support & Help', 'xt-facebook-events' ); ?></span>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>

							<?php
							if ( $tab == 'settings' ) {
								require_once XTFE_PLUGIN_DIR . '/templates/xt-facebook-events-settings.php';
							} elseif ( $tab == 'support' ) {
								require_once XTFE_PLUGIN_DIR . '/templates/xt-facebook-events-support.php';
							} elseif ( $tab == 'shortandwid' ) {
								require_once XTFE_PLUGIN_DIR . '/templates/xt-facebook-events-shortcode.php';
							} elseif ( $tab == 'license' ) {
								do_action( 'xtfe_pro_render_license_tab' );
							} elseif ( $tab == 'logs' ) {
								do_action( 'xtfe_pro_render_logs_tab' );
							}
							?>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
		</div>
		<?php
		$this->xtfe_render_common_footer();
	}

	/**
	 * Display notices in admin.
	 *
	 * @since    1.0.0
	 */
	public function display_notices() {
		global $xtfe_errors, $xtfe_success_msg, $xtfe_warnings, $xtfe_info_msg;
		
		if ( ! empty( $xtfe_errors ) ) {
			foreach ( $xtfe_errors as $error ) :
			    ?>
			    <div class="notice notice-error xtfe-notice is-dismissible">
			        <p><?php echo esc_attr( $error ); ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $xtfe_success_msg ) ) {
			foreach ( $xtfe_success_msg as $success ) :
			    ?>
			    <div class="notice notice-success xtfe-notice is-dismissible">
			        <p><?php echo esc_attr( $success ); ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $xtfe_warnings ) ) {
			foreach ( $xtfe_warnings as $warning ) :
			    ?>
			    <div class="notice notice-warning xtfe-notice is-dismissible">
			        <p><?php echo esc_attr( $warning ); ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $xtfe_info_msg ) ) {
			foreach ( $xtfe_info_msg as $info ) :
			    ?>
			    <div class="notice notice-info xtfe-notice is-dismissible">
			        <p><?php echo esc_attr( $info ); ?></p>
			    </div>
			    <?php
			endforeach;
		}

	}

	/**
	 * Add Import Facebook Events rating text
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_xt_facebook_events_credit( $footer_text ){
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $page != '' && $page == 'wpfb_events' ) {
			$rate_url = 'https://wordpress.org/support/plugin/xt-facebook-events/reviews/?rate=5#new-post';

			$footer_text .= sprintf(
				esc_html__( ' Rate %1$sXT Facebook Events%2$s %3$s', 'xt-facebook-events' ),  // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
				'<strong>',
				'</strong>',
				'<a href="' . $rate_url . '" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}
		return $footer_text;
	}

	/**
     * Get Plugin array
     *
     * @since 1.1.0
     * @return array
     */
    public function xtfe_get_xyuls_themes_plugins(){
        return array(
            'wp-bulk-delete' => array( 'plugin_name' => esc_html__( 'WP Bulk Delete', 'xt-facebook-events' ), 'description' => 'Delete posts, pages, comments, users, taxonomy terms and meta fields in bulk with different powerful filters and conditions.' ),
            'wp-event-aggregator' => array( 'plugin_name' => esc_html__( 'WP Event Aggregator', 'xt-facebook-events' ), 'description' => 'WP Event Aggregator: Easy way to import Facebook Events, Eventbrite events, MeetUp events into your WordPress Event Calendar.' ),
            'import-facebook-events' => array( 'plugin_name' => esc_html__( 'Import Social Events', 'xt-facebook-events' ), 'description' => 'Import Facebook events into your WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-eventbrite-events' => array( 'plugin_name' => esc_html__( 'Import Eventbrite Events', 'xt-facebook-events' ), 'description' => 'Import Eventbrite Events into WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-meetup-events' => array( 'plugin_name' => esc_html__( 'Import Meetup Events', 'xt-facebook-events' ), 'description' => 'Import Meetup Events allows you to import Meetup (meetup.com) events into your WordPress site effortlessly.' ),
			'event-schema' => array( 'plugin_name' => esc_html__( 'Event Schema / Structured Data', 'xt-facebook-events' ), 'description' => 'Automatically Google Event Rich Snippet Schema Generator. This plug-in generates complete JSON-LD based schema (structured data for Rich Snippet) for events.' ),
            'xylus-events-calendar' => array( 'plugin_name' => esc_html__( 'Easy Events Calendar', 'xt-facebook-events' ), 'description' => 'Create and manage events with ease using Easy Events Calendar. Best event calendar for WordPress.' ),
            'wp-smart-import' => array( 'plugin_name' => esc_html__( 'WP Smart Import : Import any XML File to WordPress', 'xt-facebook-events' ), 'description' => 'The most powerful solution for importing any CSV files to WordPress. Create Posts and Pages any Custom Posttype with content from any CSV file.' ),
            'xt-feed-for-linkedin' => array( 'plugin_name' => esc_html__( 'XT Feed for LinkedIn', 'xt-facebook-events' ), 'description' => 'XT Feed for LinkedIn auto-shares WordPress posts to LinkedIn with one click, making content distribution easy and boosting your reach effortlessly.' ),
        );
    }

	/**
	 * Render Page header Section
	 *
	 * @since 1.1
	 * @return void
	 */
	public function xtfe_render_common_header( $page_title ){
		?>
		<div class="xtfe-header" >
			<div class="xtfe-container" >
				<div class="xtfe-header-content" >
					<span style="font-size:18px;"><?php esc_html_e('Dashboard','xt-facebook-events'); ?></span>
					<span class="spacer"></span>
					<span class="page-name"><?php echo esc_attr( $page_title ); ?></span>
					<div class="header-actions" >
						<span class="round">
							<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/facebookevents/' ); ?>" target="_blank">
								<svg viewBox="0 0 20 20" fill="#2c3e50" height="20px" xmlns="http://www.w3.org/2000/svg" class="xtfe-circle-question-mark">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
								</svg>
							</a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Page Footer Section
	 *
	 * @since 1.1
	 * @return void
	 */
	public function xtfe_render_common_footer(){
		?>
			<div id="xtfe-footer-links" >
				<div class="xtfe-footer">
					<div><?php esc_attr_e( 'Made with ♥ by the Xylus Themes','xt-facebook-events'); ?></div>
					<div class="xtfe-links" >
						<a href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>" target="_blank" ><?php esc_attr_e( 'Support','xt-facebook-events'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/facebookevents' ); ?>" target="_blank" ><?php esc_attr_e( 'Docs','xt-facebook-events'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" ><?php esc_attr_e( 'Free Plugins','xt-facebook-events'); ?></a>
					</div>
					<div class="xtfe-social-links">
						<a href="<?php echo esc_url( 'https://www.facebook.com/xylusinfo/' ); ?>" target="_blank" >
							<svg class="xtfe-facebook">
								<path fill="currentColor" d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.linkedin.com/company/xylus-consultancy-service-xcs-/' ); ?>" target="_blank" >
							<svg class="xtfe-linkedin">
								<path fill="currentColor" d="M14 1H1.97C1.44 1 1 1.47 1 2.03V14c0 .56.44 1 .97 1H14a1 1 0 0 0 1-1V2.03C15 1.47 14.53 1 14 1ZM5.22 13H3.16V6.34h2.06V13ZM4.19 5.4a1.2 1.2 0 0 1-1.22-1.18C2.97 3.56 3.5 3 4.19 3c.65 0 1.18.56 1.18 1.22 0 .66-.53 1.19-1.18 1.19ZM13 13h-2.1V9.75C10.9 9 10.9 8 9.85 8c-1.1 0-1.25.84-1.25 1.72V13H6.53V6.34H8.5v.91h.03a2.2 2.2 0 0 1 1.97-1.1c2.1 0 2.5 1.41 2.5 3.2V13Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://x.com/XylusThemes' ); ?>" target="_blank" >
							<svg class="xtfe-twitter" width="24" height="24" viewBox="0 0 24 24">
								<circle cx="12" cy="12" r="12" fill="currentColor"></circle>
								<g>
									<path d="M13.129 11.076L17.588 6H16.5315L12.658 10.4065L9.5665 6H6L10.676 12.664L6 17.9865H7.0565L11.1445 13.332L14.41 17.9865H17.9765L13.129 11.076ZM11.6815 12.7225L11.207 12.0585L7.4375 6.78H9.0605L12.1035 11.0415L12.576 11.7055L16.531 17.2445H14.908L11.6815 12.7225Z" fill="white"></path>
								</g>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.youtube.com/@xylussupport7784' ); ?>" target="_blank" >
							<svg class="xtfe-youtube">
								<path fill="currentColor" d="M16.63 3.9a2.12 2.12 0 0 0-1.5-1.52C13.8 2 8.53 2 8.53 2s-5.32 0-6.66.38c-.71.18-1.3.78-1.49 1.53C0 5.2 0 8.03 0 8.03s0 2.78.37 4.13c.19.75.78 1.3 1.5 1.5C3.2 14 8.51 14 8.51 14s5.28 0 6.62-.34c.71-.2 1.3-.75 1.49-1.5.37-1.35.37-4.13.37-4.13s0-2.81-.37-4.12Zm-9.85 6.66V5.5l4.4 2.53-4.4 2.53Z"></path>
							</svg>
						</a>
					</div>
				</div>
			</div>
		<?php   
	}
}
