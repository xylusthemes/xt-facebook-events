<?php
/**
 * Plugin Name:       XT Event Widget for Social Events
 * Plugin URI:        http://xylusthemes.com/plugins/xt-facebook-events/
 * Description:       Display Facebook Events into your WordPress site anywhere.
 * Version:           1.1.6
 * Author:            Xylus Themes
 * Author URI:        http://xylusthemes.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xt-facebook-events
 * Domain Path:       /languages
 *
 * @package     XT_Facebook_Events
 * @author      Dharmesh Patel <dspatel44@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'XT_Facebook_Events' ) ):

/**
* Main Facebook Events class
*/
class XT_Facebook_Events{
	
	/** Singleton *************************************************************/
	/**
	 * XT_Facebook_Events The one true XT_Facebook_Events.
	 */
	private static $instance;
	public $common, $facebook, $admin, $fb_authorize;

    /**
     * Main Facebook Events Instance.
     * 
     * Insure that only one instance of XT_Facebook_Events exists in memory at any one time.
     * Also prevents needing to define globals all over the place.
     *
     * @since 1.0.0
     * @static object $instance
     * @uses XT_Facebook_Events::setup_constants() Setup the constants needed.
     * @uses XT_Facebook_Events::includes() Include the required files.
     * @uses XT_Facebook_Events::laod_textdomain() load the language files.
     * @see run_import_facebook_events()
     * @return object| Facebook Events the one true Facebook Events.
     */
	public static function instance() {
		if( ! isset( self::$instance ) && ! (self::$instance instanceof XT_Facebook_Events ) ) {
			self::$instance = new XT_Facebook_Events;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
			add_action( 'plugins_loaded', array( self::$instance, 'load_authorize_class' ), 20 );
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'xtfe_enqueue_style' ) );
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'xtfe_enqueue_script' ) );

			self::$instance->includes();
			self::$instance->common = new XT_Facebook_Events_Common();
			self::$instance->facebook = new XT_Facebook_Events_Facebook();
			self::$instance->admin = new XT_Facebook_Events_Admin();
		}
		return self::$instance;	
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent XT_Facebook_Events from being loaded more than once.
	 *
	 * @since 1.0.0
	 * @see XT_Facebook_Events::instance()
	 * @see run_import_facebook_events()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent XT_Facebook_Events from being cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'xt-facebook-events' ), '1.1.6' ); }

	/**
	 * A dummy magic method to prevent XT_Facebook_Events from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'xt-facebook-events' ), '1.1.6' ); }


	/**
	 * Setup plugins constants.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if( ! defined( 'XTFE_VERSION' ) ){
			define( 'XTFE_VERSION', '1.1.6' );
		}

		// Plugin folder Path.
		if( ! defined( 'XTFE_PLUGIN_DIR' ) ){
			define( 'XTFE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL.
		if( ! defined( 'XTFE_PLUGIN_URL' ) ){
			define( 'XTFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin root file.
		if( ! defined( 'XTFE_PLUGIN_FILE' ) ){
			define( 'XTFE_PLUGIN_FILE', __FILE__ );
		}

		// Options
		if( ! defined( 'XTFE_OPTIONS' ) ){
			define( 'XTFE_OPTIONS', 'xtfe_options' );
		}

		// Pro plugin Buy now Link.
		if( ! defined( 'XTFE_PLUGIN_BUY_NOW_URL' ) ){
			define( 'XTFE_PLUGIN_BUY_NOW_URL', 'https://xylusthemes.com/plugins/xt-facebook-events/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin' );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		require_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-facebook.php';
		require_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-common.php';
		require_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-admin.php';		
		require_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-widget.php';
		require_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-deactivation.php';
		require_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-list-table.php';

		// Gutenberg Block
		require_once XTFE_PLUGIN_DIR . 'blocks/facebook-events/index.php';
	}

	/**
	 * Loads the plugin language files.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain(){

		load_plugin_textdomain(
			'xt-facebook-events',
			false,
			basename( dirname( __FILE__ ) ) . '/languages'
		);

	}

	/**
	 * Loads the facebook authorize class
	 *
	 * @access public
	 * @since 1.1
	 * @return void
	 */
	public function load_authorize_class(){
		if( !class_exists( 'XT_Facebook_Events_FB_Authorize', false ) ){
			include_once XTFE_PLUGIN_DIR . 'includes/class-xt-facebook-events-fb-authorize.php';
			global $xtfe_events;
			if( class_exists('XT_Facebook_Events_FB_Authorize', false ) && !empty( $xtfe_events ) ){
				$xtfe_events->fb_authorize = new XT_Facebook_Events_FB_Authorize();
			}
		}
	}

	/**
	 * enqueue style front-end
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function xtfe_enqueue_style() {

		$css_dir = XTFE_PLUGIN_URL . 'assets/css/';
		wp_enqueue_style('font-awesome', $css_dir . 'font-awesome.min.css', false, "" );
	 	wp_enqueue_style('xt-facebook-events-front', $css_dir . 'xt-facebook-events.css', false, "" );
		wp_enqueue_style('xt-facebook-events-front-grid2', $css_dir . 'grid_style2.css', false, "" );
	}

	/**
	 * enqueue script front-end
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function xtfe_enqueue_script() {
		
		// enqueue script here.
	}

}

endif; // End If class exists check.

/**
 * The main function for that returns XT_Facebook_Events
 *
 * The main function responsible for returning the one true XT_Facebook_Events
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $xtfe_events = run_import_facebook_events(); ?>
 *
 * @since 1.0.0
 * @return object|XT_Facebook_Events The one true XT_Facebook_Events Instance.
 */
function run_xt_facebook_events() {
	return XT_Facebook_Events::instance();
}

/**
 * Get Import events setting options
 *
 * @since 1.0
 * @return array
 */
function xtfe_get_options() {

	$xtfe_options = get_option( XTFE_OPTIONS , array() );
	return $xtfe_options;
}

// Get XT_Facebook_Events Running.
global $xtfe_events, $xtfe_errors, $xtfe_success_msg, $xtfe_warnings, $xtfe_info_msg;
$xtfe_events = run_xt_facebook_events();
$xtfe_errors = $xtfe_warnings = $xtfe_success_msg = $xtfe_info_msg = array();

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 */
function xtfe_activate_facebook_events() {
	
}
register_activation_hook( __FILE__, 'xtfe_activate_facebook_events' );
