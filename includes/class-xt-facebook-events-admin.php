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
		add_filter( 'admin_footer_text', array( $this, 'add_xt_facebook_events_credit' ) );
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages() {

		add_menu_page( __( 'Facebook Events', 'xt-facebook-events' ), __( 'Facebook Events', 'xt-facebook-events' ), 'manage_options', 'wpfb_events', array( $this, 'admin_page' ), 'dashicons-facebook', '70' );
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
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if( 'wpfb_events' == $page || $pagenow == 'widgets.php' ){
		  	$css_dir = XTFE_PLUGIN_URL . 'assets/css/';
			wp_enqueue_style('xt-facebook-events', $css_dir . 'xt-facebook-events-admin.css', false, "" );
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
		
		?>
		<div class="wrap">
		    <h2><?php esc_html_e( 'Facebook Events', 'xt-facebook-events' ); ?></h2>
		    <?php
		    // Set Default Tab to Import.
		    $tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
		    ?>
		    <div id="poststuff">
		        <div id="post-body" class="metabox-holder columns-2">

		            <div id="postbox-container-1" class="postbox-container">
						<?php
						if( !xtfe_is_pro() ){
							require_once XTFE_PLUGIN_DIR . '/templates/admin-sidebar.php';
						}
						?>
		            </div>
		            <div id="postbox-container-2" class="postbox-container">

		                <h1 class="nav-tab-wrapper">
		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'settings' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Settings', 'xt-facebook-events' ); ?>
		                    </a>
							<a href="<?php echo esc_url( add_query_arg( 'tab', 'shortandwid', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'shortandwid' ) { echo 'nav-tab-active'; } ?>" >
								<?php esc_html_e( 'Shortcodes & Widgets', 'xt-facebook-events' ); ?>
							</a>
		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'support', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'support' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Support & Help', 'xt-facebook-events' ); ?>
		                    </a>
		                </h1>

		                <div class="xt-facebook-events-page">

		                	<?php
		                	if ( $tab == 'settings' ) {

		                		require_once XTFE_PLUGIN_DIR . '/templates/xt-facebook-events-settings.php';

		                	}elseif ( $tab == 'support' ) {

		                		require_once XTFE_PLUGIN_DIR . '/templates/xt-facebook-events-support.php';

							}elseif ( $tab == 'shortandwid' ) {

								require_once XTFE_PLUGIN_DIR . '/templates/xt-facebook-events-shortcode.php';

		                	}
			                ?>
		                	<div style="clear: both"></div>
		                </div>

		        </div>
		        
		    </div>
		</div>
		<?php
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
			    <div class="notice notice-error is-dismissible">
			        <p><?php echo $error; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $xtfe_success_msg ) ) {
			foreach ( $xtfe_success_msg as $success ) :
			    ?>
			    <div class="notice notice-success is-dismissible">
			        <p><?php echo $success; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $xtfe_warnings ) ) {
			foreach ( $xtfe_warnings as $warning ) :
			    ?>
			    <div class="notice notice-warning is-dismissible">
			        <p><?php echo $warning; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $xtfe_info_msg ) ) {
			foreach ( $xtfe_info_msg as $info ) :
			    ?>
			    <div class="notice notice-info is-dismissible">
			        <p><?php echo $info; ?></p>
			    </div>
			    <?php
			endforeach;
		}

	}

	/**
	 * Add Import Facebook Events ratting text
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_xt_facebook_events_credit( $footer_text ){
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $page != '' && $page == 'wpfb_events' ) {
			$rate_url = 'https://wordpress.org/support/plugin/xt-facebook-events/reviews/?rate=5#new-post';

			$footer_text .= sprintf(
				esc_html__( ' Rate %1$sXT Facebook Events%2$s %3$s', 'xt-facebook-events' ),
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
	 * @since 1.0.0
	 * @return array
	 */
	public function get_xyuls_themes_plugins(){
		return array(
			'wp-event-aggregator' => esc_html__( 'WP Event Aggregator', 'xt-facebook-events' ),
			'import-facebook-events' => esc_html__( 'Import Facebook Events', 'xt-facebook-events' ),
			'import-eventbrite-events' => esc_html__( 'Import Eventbrite Events', 'xt-facebook-events' ),
			'import-meetup-events' => esc_html__( 'Import Meetup Events', 'xt-facebook-events' ),
			'wp-bulk-delete' => esc_html__( 'WP Bulk Delete', 'xt-facebook-events' ),
			'event-schema' => esc_html__( 'Event Schema / Structured Data', 'xt-facebook-events' ),
		);
	}

	/**
	 * Get Plugin Details.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_wporg_plugin( $slug ){

		if( $slug == '' ){
			return false;
		}

		$transient_name = 'support_plugin_box'.$slug;
		$plugin_data = get_transient( $transient_name );
		if( false === $plugin_data ){
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}

			$plugin_data = plugins_api( 'plugin_information', array(
				'slug' => $slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners' => true,
					'active_installs'   => true,
					'short_description' => true,
					'icons'			    => true,
				),
			) );

			if ( ! is_wp_error( $plugin_data ) ) {
				set_transient( $transient_name, $plugin_data, 24 * HOUR_IN_SECONDS );
			} else {
				// If there was a bug on the Current Request just leave
				return false;
			}			
		}
		return $plugin_data;
	}
}
