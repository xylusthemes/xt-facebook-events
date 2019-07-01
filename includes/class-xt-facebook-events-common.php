<?php
/**
 * Common functions class for Facebook Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Facebook_Events
 * @subpackage XT_Facebook_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class XT_Facebook_Events_Common {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_init', array( $this, 'handle_import_settings_submit' ), 99 );
		add_action( 'xtfe_render_pro_notice', array( $this, 'render_pro_notice' ) );
	}

	/**
	 * Settings save.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_settings_submit() {
		global $xtfe_errors, $xtfe_success_msg;
		if ( isset( $_POST['xtfe_action'] ) && $_POST['xtfe_action'] == 'xtfe_save_settings' &&  check_admin_referer( 'xtfe_setting_form_nonce_action', 'xtfe_setting_form_nonce' ) ) {
				
			$xtfe_options = array();
			$xtfe_options = isset( $_POST['xtfe'] ) ? $_POST['xtfe'] : array();
			
			update_option( XTFE_OPTIONS, $xtfe_options );
			$xtfe_success_msg[] = __( 'Import settings has been saved successfully.', 'xt-facebook-events' );
		}
	}

	/**
	 * Check for user have Authorized user Token
	 *
	 * @since    1.2
	 * @return /boolean
	 */
	public function has_authorized_user_token() {
		$xtfe_user_token_options = get_option( 'xtfe_user_token_options', array() );
		if( !empty( $xtfe_user_token_options ) ){
			$authorize_status =	isset( $xtfe_user_token_options['authorize_status'] ) ? $xtfe_user_token_options['authorize_status'] : 0;
			$access_token = isset( $xtfe_user_token_options['access_token'] ) ? $xtfe_user_token_options['access_token'] : '';
			if( 1 == $authorize_status && $access_token != '' ){
				return true;
			}
		}
		return false;
	}

	/**
	 * Setup Success Messages.
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages() {
		global $xtfe_success_msg, $xtfe_errors;
		if ( isset( $_GET['xtauthorize'] ) && trim( $_GET['xtauthorize'] ) != '' ) {
			if( trim( $_GET['xtauthorize'] ) == '1' ){
				$xtfe_success_msg[] = esc_html__( 'Authorized Successfully.', 'xt-facebook-events' );
			} elseif( trim( $_GET['xtauthorize'] ) == '2' ){
				$xtfe_errors[] = esc_html__( 'Please insert Facebook App ID and Secret.', 'xt-facebook-events' );
			} elseif( trim( $_GET['xtauthorize'] ) == '0' ){
				$xtfe_errors[] = esc_html__( 'Something went wrong during authorization. Please try again.', 'xt-facebook-events' );
			}
		} elseif( isset( $_GET['xtcleared'] ) &&  trim( $_GET['xtcleared'] ) == '1' ){
			$xtfe_success_msg[] = esc_html__( 'Facebook Events Cache has been cleared successfully.', 'xt-facebook-events' );
		}
	}

	/**
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.1.0
	 */
	public function render_pro_notice(){
		if( !xtfe_is_pro() ){
			?>
			<span class="xtfe_small">
				<?php printf( '<span style="color: red">%s</span> <a href="' . XTFE_PLUGIN_BUY_NOW_URL . '" target="_blank" >%s</a>', __( 'Available in Pro version.', 'xt-facebook-events' ), __( 'Upgrade to PRO', 'xt-facebook-events' ) ); ?>
			</span>
			<?php
		}
	}
}

/**
 * Check if Pro addon is enabled or not.
 *
 * @since 1.0.0
 */
function xtfe_is_pro(){
	if( !function_exists( 'is_plugin_active' ) ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if ( is_plugin_active( 'xt-facebook-events-pro/xt-facebook-events-pro.php' ) ) {
		return true;
	}
	if( class_exists('XT_Facebook_Events_Pro', false) ){
		return true;
	}
	return false;
}