<?php
/**
 * class for Facebook User Authorization
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Facebook_Events
 * @subpackage XT_Facebook_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class XT_Facebook_Events_FB_Authorize {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_post_xtfe_facebook_authorize_action', array( $this, 'xtfe_facebook_authorize_user' ) );
		add_action( 'admin_post_xtfe_facebook_authorize_callback', array( $this, 'xtfe_facebook_authorize_user_callback' ) );
		add_action( 'admin_post_xtfe_deauthorize_action', array( $this, 'xtfe_deauthorize_user' ) );
		add_action( 'admin_post_xtfe_fb_login_action', array( $this, 'xtfe_fb_login_action' ) );
	}

	/*
	* Authorize facebook user to get access token
	*/
	function xtfe_facebook_authorize_user() {
		if ( ! empty($_POST) && wp_verify_nonce($_POST['xtfe_facebook_authorize_nonce'], 'xtfe_facebook_authorize_action' ) ) {

			$xtfe_options = get_option( XTFE_OPTIONS , array() );
			$app_id = isset( $xtfe_options['facebook_app_id'] ) ? $xtfe_options['facebook_app_id'] : '';
			$app_secret = isset( $xtfe_options['facebook_app_secret'] ) ? $xtfe_options['facebook_app_secret'] : '';
			$redirect_url = admin_url( 'admin-post.php?action=xtfe_facebook_authorize_callback' );
			$api_version = 'v19.0';
			$param_url = urlencode($redirect_url);
			$xtfe_session_state = md5(uniqid(rand(), TRUE));
			setcookie("xtfe_session_state", $xtfe_session_state, "0", "/");

			if( $app_id != '' && $app_secret != '' ){

				$dialog_url = "https://www.facebook.com/" . $api_version . "/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . $param_url . "&state=" . $xtfe_session_state . "&scope=pages_show_list,pages_manage_metadata,pages_read_engagement,pages_read_user_content,page_events";
				header("Location: " . $dialog_url);

			}else{
				die( __( 'Please insert Facebook App ID and Secret.', 'xt-facebook-events' ) );
			}

		} else {
			die( __('You have not access to doing this operations.', 'xt-facebook-events' ) );
		}
	}

	/*
	* Authorize facebook user on callback to get access token
	*/
	function xtfe_facebook_authorize_user_callback() {
		global $xtfe_success_msg;
		if ( isset( $_COOKIE['xtfe_session_state'] ) && isset($_REQUEST['state']) && ( $_COOKIE['xtfe_session_state'] === $_REQUEST['state'] ) ) {

			$code = sanitize_text_field($_GET['code']);
			$xtfe_options = get_option( XTFE_OPTIONS , array() );
			$app_id = isset( $xtfe_options['facebook_app_id'] ) ? $xtfe_options['facebook_app_id'] : '';
			$app_secret = isset( $xtfe_options['facebook_app_secret'] ) ? $xtfe_options['facebook_app_secret'] : '';
			$redirect_url = admin_url('admin-post.php?action=xtfe_facebook_authorize_callback');
			$api_version = 'v19.0';
			$param_url = urlencode($redirect_url);

			if( $app_id != '' && $app_secret != '' ){

				$token_url = "https://graph.facebook.com/" . $api_version . "/oauth/access_token?" . "client_id=" . $app_id . "&redirect_uri=" . $param_url . "&client_secret=" . $app_secret . "&code=" . $code;

				$access_token = "";
				$xtfe_user_token_options = $xtfe_fb_authorize_user = array();
				$response = wp_remote_get( $token_url );
				$body = wp_remote_retrieve_body( $response );
				$body_response = json_decode( $body );
				if ($body != '' && isset( $body_response->access_token ) ) {

					$access_token = $body_response->access_token;
					$xtfe_user_token_options['authorize_status'] = 1;
					$xtfe_user_token_options['access_token'] = sanitize_text_field($access_token);
					update_option('xtfe_user_token_options', $xtfe_user_token_options);

					$profile_call= wp_remote_get("https://graph.facebook.com/".$api_version."/me?fields=id,name,picture&access_token=$access_token");
					$profile = wp_remote_retrieve_body( $profile_call );
					$profile = json_decode( $profile );
					if( isset( $profile->id ) && isset( $profile->name ) ){
						$xtfe_fb_authorize_user['ID'] = sanitize_text_field( $profile->id );
						$xtfe_fb_authorize_user['name'] = sanitize_text_field( $profile->name );
						if( isset( $profile->picture->data->url ) ){
							$xtfe_fb_authorize_user['avtar'] = esc_url_raw( $profile->picture->data->url );
						}
					}
					update_option('xtfe_fb_authorize_user', $xtfe_fb_authorize_user );

					$args = array( 'timeout' => 15 );
					$accounts_call= wp_remote_get("https://graph.facebook.com/".$api_version."/me/accounts?access_token=$access_token&limit=100&offset=0", $args );
					$accounts = wp_remote_retrieve_body( $accounts_call );
					$accounts = json_decode( $accounts );
					$accounts = isset( $accounts->data ) ? $accounts->data : array();
					if( !empty( $accounts ) ){
						$pages = array();
						foreach ($accounts as $account) {
							$pages[$account->id] = array(
								'id' => $account->id,
								'name' => $account->name,
								'access_token' => $account->access_token
							);
						}
						update_option('xtfe_fb_user_pages', $pages );
					}

					$redirect_url = admin_url('admin.php?page=wpfb_events&xtauthorize=1');
					wp_redirect($redirect_url);
					exit();
				}else{
					$redirect_url = admin_url('admin.php?page=wpfb_events&xtauthorize=0');
					wp_redirect($redirect_url);
					exit();
				}
			} else {
				$redirect_url = admin_url('admin.php?page=wpfb_events&xtauthorize=2');
				wp_redirect($redirect_url);
				exit();
				die( __( 'Please insert Facebook App ID and Secret.', 'xt-facebook-events' ) );
			}
		} else {
			die( __('You have not access to doing this operations.', 'xt-facebook-events' ) );
		}
	}

	/**
	 * Authorize facebook user using https://connect.xylusthemes.com/.
	 *
	 * @return void
	 */
	public function xtfe_fb_login_action() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ! empty( $_GET ) && isset( $_GET['xtfe_fb_login_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['xtfe_fb_login_nonce'] ) ), 'xtfe_fb_login_action' ) ) { // input var okay.
			// phpcs:ignore WordPress.Security.NonceVerification
			$access_token = isset( $_GET['access_token'] ) ? sanitize_text_field( wp_unslash( $_GET['access_token'] ) ) : '';

			if ( ! empty( $access_token ) ) {
				$xtfe_user_token_options = array();
				$xtfe_fb_authorize_user  = array();

				$xtfe_user_token_options['authorize_status'] = 1;
				$xtfe_user_token_options['direct_auth']      = 1;
				$xtfe_user_token_options['access_token']     = sanitize_text_field( $access_token );
				$token_transient_key = 'xtfe_facebook_access_token';
				delete_transient( $token_transient_key );
				update_option( 'xtfe_user_token_options', $xtfe_user_token_options );

				$profile_call = wp_remote_get( 'https://graph.facebook.com/' . $this->api_version . "/me?fields=id,name,picture&access_token=$access_token" );
				$profile      = wp_remote_retrieve_body( $profile_call );
				$profile      = json_decode( $profile );
				if ( isset( $profile->id ) && isset( $profile->name ) ) {
					$xtfe_fb_authorize_user['ID']   = sanitize_text_field( $profile->id );
					$xtfe_fb_authorize_user['name'] = sanitize_text_field( $profile->name );
					if ( isset( $profile->picture->data->url ) ) {
						$xtfe_fb_authorize_user['avtar'] = esc_url_raw( $profile->picture->data->url );
					}
				}
				update_option( 'xtfe_fb_authorize_user', $xtfe_fb_authorize_user );

				$args          = array( 'timeout' => 15 );
				$accounts_call = wp_remote_get( 'https://graph.facebook.com/' . $this->api_version . "/me/accounts?access_token=$access_token&limit=100&offset=0", $args );
				$accounts      = wp_remote_retrieve_body( $accounts_call );
				$accounts      = json_decode( $accounts );
				$accounts      = isset( $accounts->data ) ? $accounts->data : array();
				if ( ! empty( $accounts ) ) {
					$pages = array();
					foreach ( $accounts as $account ) {
						$pages[ $account->id ] = array(
							'id'           => $account->id,
							'name'         => $account->name,
							'access_token' => $account->access_token,
						);
					}
					update_option( 'xtfe_fb_user_pages', $pages );
				}

				$redirect_url = admin_url( 'admin.php?page=wpfb_events&authorize=1' );
				wp_safe_redirect( $redirect_url );
				exit();
			} else {
				$redirect_url = admin_url( 'admin.php?page=wpfb_events&authorize=0' );
				wp_safe_redirect( $redirect_url );
				exit();
			}
		} else {
			die( esc_attr__( 'You have not access to doing this operations.', 'xt-facebook-events' ) );
		}
	}

	/**
	 * Authorize facebook user to get access token.
	 *
	 * @return void
	 */
	public function xtfe_deauthorize_user() {
		if ( ! empty( $_GET ) && isset( $_GET['xtfe_deauthorize_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['xtfe_deauthorize_nonce'] ) ), 'xtfe_deauthorize_action' ) ) { // input var okay.
			delete_transient( 'xtfe_facebook_access_token' );
			delete_option( 'xtfe_user_token_options' );
			delete_option( 'xtfe_fb_authorize_user' );
			delete_option( 'xtfe_fb_user_pages' );

			$redirect_url = admin_url( 'admin.php?page=wpfb_events&deauthorize=1' );
			wp_safe_redirect( $redirect_url );
		} else {
			die( esc_attr__( 'You have not access to doing this operations.', 'xt-facebook-events' ) );
		}
	}
}
