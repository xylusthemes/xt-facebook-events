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
		add_action( 'admin_init', array( $this, 'handle_import_settings_submit' ), 99 );
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
			
			$is_update = update_option( XTFE_OPTIONS, $xtfe_options );
			if( $is_update ){
				$xtfe_success_msg[] = __( 'Import settings has been saved successfully.', 'xt-facebook-events' );
			}else{
				$xtfe_errors[] = __( 'Something went wrong! please try again.', 'xt-facebook-events' );
			}
		}
	}
}
