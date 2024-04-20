<?php
/**
 * Class for Facebook Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    XT_Facebook_Events
 * @subpackage XT_Facebook_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class XT_Facebook_Events_Facebook {

	/*
	*	Facebook app ID
	*/
	public $fb_app_id;

	/*
	*	Facebook app Secret
	*/
	public $fb_app_secret;

	/*
	*	Facebook Graph URL
	*/
	public $fb_graph_url;

	/*
	*	Facebook Access Token
	*/
	private $fb_access_token;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $xtfe_events;
		
		$options = xtfe_get_options();
		$xtfe_user_token_options = get_option( 'xtfe_user_token_options', array() );
		$this->fb_app_id = isset( $options['facebook_app_id'] ) ? $options['facebook_app_id'] : '';
		$this->fb_app_secret = isset( $options['facebook_app_secret'] ) ? $options['facebook_app_secret'] : '';
		$this->fb_graph_url = 'https://graph.facebook.com/v19.0/';
		$this->fb_access_token = isset( $xtfe_user_token_options['access_token'] ) ? $xtfe_user_token_options['access_token'] : '';
		add_shortcode( 'wpfb_events', array( $this, 'render_facebook_events' ) );
		add_action( 'admin_post_xtfe_clear_cache', array( $this, 'xtfe_clear_events_cache' ) );
	}

	/**
	 * render shortcode for [wpfb_events]
	 *
	 * @since  1.0.0
	 * @param  array $atts shortcode attributes
	 * @return string Generate HTML
	 */
	public function render_facebook_events( $atts = array() ){

		if( isset( $atts['type'] ) && $atts['type'] == 'widget' ){
			 $event_args = array(
				'page_id' 	 	=> '',
				'max_events' 	=> 10,
				'type' 		 	=> 'widget',
				'style'			=> 'style1',
				'new_window' 	=> 0,
				'display_event_image' 	 => 0,
				'display_event_location' => 0,
				'display_event_enddate'  => 0,
				'display_event_desc'	 => 0,
			);
			$atts = wp_parse_args( (array) $atts, $event_args );
		}else{
			$event_args = array(
				'page_id' 	 => '',
				'max_events' => 10,
				'new_window' => 0,
				'type'       => 'page',
			);
			$atts = wp_parse_args( (array) $atts, $event_args );
		}

		ob_start();
		if( $this->fb_access_token == '' ){
			_e( 'Please insert Facebook app ID and app Secret. Or Connect the Facebook app with a click to "Log in With Facebook".', 'xt-facebook-events');
			return ob_get_clean();
		}
		if( !isset( $atts['page_id'] ) || $atts['page_id'] == '' ){
			_e( 'Please insert Facebook page ID for display events.', 'xt-facebook-events');
			return ob_get_clean();
		}

		$xtfe_transient_key = 'xtfe_';
		$xtfe_transient_key .= md5(json_encode($atts));
		$facebook_events = get_transient( $xtfe_transient_key );
		if ( false === $facebook_events ) {
			$facebook_events = $this->get_events_for_facebook_page( $atts );

			// Save the Facebook Events.
			set_transient( $xtfe_transient_key, $facebook_events, 900 );
			$this->xtfe_update_transient_keys( $xtfe_transient_key );
		}

		if( !empty( $facebook_events ) ){
			$this->render_facebook_event_listing( $facebook_events, $atts );
		}else{
			echo apply_filters( 'xtfe_no_events_found_message', __( "No Events are found.", 'xt-facebook-events' ) );
		}
		return ob_get_clean();
	}

	/**
	 * Update transient key to option.
	 *
	 * @param string $new_transient_key
	 * @return void
	 */
	function xtfe_update_transient_keys( $new_transient_key ) {
		// Get the current list of transients.
		$transient_keys = get_option( 'xtfe_transient_keys', array() );

		// Append our new one.
		$transient_keys[]= $new_transient_key;

		// Save it to the DB.
		update_option( 'xtfe_transient_keys', $transient_keys );
	}

	/**
	 * Purge all transients stored by plugin
	 *
	 * @return void
	 */
	public function xtfe_purge_transient(){
		global $wpdb;
		// Get our list of transient keys from the DB.
		$transient_keys = get_option( 'xtfe_transient_keys', array() );

		// For each key, delete that transient.
		foreach( $transient_keys as $t ) {
			delete_transient( $t );
		}

		// Reset our DB value.
		update_option( 'xtfe_transient_keys', array() );

		// Manually Delete incase of missing in keys.
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '%_transient_xtfe_%'");
	}

	/**
	 * Render Facebook Events Listing
	 *
	 * @since 1.0.0
	 */
	public function render_facebook_event_listing( $facebook_events, $event_args ){

		if( empty( $facebook_events ) ){ return false; }

		$xtfe_options = get_option( XTFE_OPTIONS, array() );
		$accent_color = isset( $xtfe_options['accent_color'] ) ? $xtfe_options['accent_color'] : '#039ED7';

		$new_window = false;
		if( isset( $event_args['new_window'] ) && $event_args['new_window'] === true ){
			$event_args['new_window'] == '1';
		}
		if( isset( $event_args['new_window'] ) && $event_args['new_window'] == '1' ){
			$new_window = true;
		}
		$shortcode_type = isset( $event_args['type'] ) ? esc_attr( $event_args['type'] ) : 'page';
		if( 'widget' == $shortcode_type ){
			$style = isset( $event_args['style'] ) ? esc_attr( $event_args['style'] ) : 'style1';
			$is_display_image    = $event_args['display_event_image'];
			$is_display_location = $event_args['display_event_location'];
			$is_display_enddate  = $event_args['display_event_enddate'];
			$is_display_desc     = $event_args['display_event_desc'];

			echo '<div class="xtfacebook_events_widget">';	
		} else {
			if( xtfe_is_pro() ) {
				do_action( 'xtfe_render_wpfb_events_shortcode', $facebook_events, $event_args);
			} else {
				?>
				<div class="components-placeholder editor-media-placeholder wp-block-image">
					<?php do_action( 'xtfe_render_pro_notice' ); ?>
				</div>
				<?php
			}
		}

		foreach ($facebook_events as $facebook_event ) {
			$event_id 	= isset( $facebook_event->id ) ? $facebook_event->id : '';
			if( $event_id == '' ){ continue; }
			$event_link = esc_url( 'https://www.facebook.com/events/' . $event_id . '/' );
			$name = isset( $facebook_event->name ) ? $facebook_event->name : '';
			$description = isset( $facebook_event->description ) ? $facebook_event->description : '';
			$short_description = substr( $description, 0, 100 ) . '...';
			$start_time  = isset( $facebook_event->start_time ) ? $facebook_event->start_time : date('Y-m-d');
			$end_time    = isset( $facebook_event->end_time ) ? $facebook_event->end_time : $start_time;

			$timezone = isset( $facebook_event->timezone ) ? $facebook_event->timezone : '';
			if( $timezone != '' ){
				$start_date = new DateTime( $start_time, new DateTimeZone($timezone));
				$end_date   = new DateTime( $end_time, new DateTimeZone($timezone));
			}else{
				$start_date = new DateTime( $start_time );
				$end_date   = new DateTime( $end_time );
			}

			$cover_url   = isset( $facebook_event->cover->source ) ? $facebook_event->cover->source : '';
			if(empty($cover_url) ){
				$image_date = date_i18n('F+d', strtotime($start_date->format('Y-m-d h:i a')) );
				$cover_url = "http://placehold.it/420x150?text=".$image_date;
			}
			$picture_url = isset( $facebook_event->picture->data->url ) ? $facebook_event->picture->data->url : '';
			if( empty($picture_url ) ){
				$picture_url = $cover_url;
			}
			$location = isset( $facebook_event->place->name ) ? $facebook_event->place->name : '';

			$event_date = date_i18n( 'F j (h:i a)', strtotime($start_date->format('Y-m-d h:i a')) );

			if( 'widget' == $shortcode_type ){
				if( $is_display_enddate ){
					if( $start_date->format('Y-m-d h:i a') != $end_date->format('Y-m-d h:i a') ){
						if( $start_date->format('Y-m-d') == $end_date->format('Y-m-d') ){
							$event_date = date_i18n( 'F j', strtotime($start_date->format('Y-m-d h:i a')) ).' ('. date_i18n( 'h:i a', strtotime($start_date->format('Y-m-d h:i a'))) . ' - '. date_i18n( 'h:i a', strtotime($end_date->format('Y-m-d h:i a'))) .')';
						} else {
							$event_date = date_i18n( 'F j (h:i a)', strtotime($start_date->format('Y-m-d h:i a')) ) . ' - ' . date_i18n( 'F j (h:i a)', strtotime($end_date->format('Y-m-d h:i a')) );
						}
					}
				}

				if( file_exists( XTFE_PLUGIN_DIR . '/templates/event-widget-' . $style . '.php' ) ){
					include XTFE_PLUGIN_DIR . '/templates/event-widget-' . $style . '.php';				
				} else {
					include XTFE_PLUGIN_DIR . '/templates/event-widget-style1.php';
				}
			}
		}
		if( 'widget' == $shortcode_type ){
			echo '</div>';
		}
		?>
		<div style="clear: both"></div>
		<style type="text/css">
			.xtfe_event .event_date{
			    background-color: <?php echo $accent_color;?>;
			}
			.xtfe_event .event_desc .event_title, .xtfe_event .event_desc .event_name a{
			    color: <?php echo $accent_color;?>;
			}
		</style>
		<?php
	}

	/**
	 * get access token
	 *
	 * @since 1.0.0
	 */
	public function get_access_token(){

		if( $this->fb_access_token != '' ){
			return $this->fb_access_token;
		}

		$xtfe_user_token_options = get_option( 'xtfe_user_token_options', array() );
		$is_direct_auth         = isset( $xtfe_user_token_options['direct_auth'] ) ? ( 1 === $xtfe_user_token_options['direct_auth'] ) : false;

		// Skip debug token check if direct auth is enabled.
		if ( $is_direct_auth ) {
			$access_token     = isset( $xtfe_user_token_options['access_token'] ) ? $xtfe_user_token_options['access_token'] : '';
			$is_authenticated = isset( $xtfe_user_token_options['authorize_status'] ) ? ( 1 === $xtfe_user_token_options['authorize_status'] ) : false;
			if ( ! empty( $access_token ) && $is_authenticated ) {
				$this->fb_access_token = apply_filters( 'xtfe_facebook_access_token', $access_token );
				return $this->fb_access_token;
			}
		}

		$args = array(
			'grant_type' => 'client_credentials', 
			'client_id'  => $this->fb_app_id,
			'client_secret' => $this->fb_app_secret
			);
		$access_token_url = add_query_arg( $args, $this->fb_graph_url . 'oauth/access_token' );
		$access_token_response = wp_remote_get( $access_token_url );
		$access_token_response_body = wp_remote_retrieve_body( $access_token_response );
		$access_token_data = json_decode( $access_token_response_body );
		$access_token = ! empty( $access_token_data->access_token ) ? $access_token_data->access_token : null;
		$xtfe_user_token_options = get_option( 'xtfe_user_token_options', array() );
		if( !empty( $xtfe_user_token_options ) && $access_token != '' ){
			$authorize_status =	isset( $xtfe_user_token_options['authorize_status'] ) ? $xtfe_user_token_options['authorize_status'] : 0;
			$user_access_token = isset( $xtfe_user_token_options['access_token'] ) ? $xtfe_user_token_options['access_token'] : '';
			if( 1 == $authorize_status && $user_access_token != '' ){
				$args = array(
					'input_token' => $user_access_token,
					'access_token'  => $access_token,
					);
				$access_token_url = add_query_arg( $args, $this->fb_graph_url . 'debug_token' );
				$access_token_response = wp_remote_get( $access_token_url );
				$access_token_response_body = wp_remote_retrieve_body( $access_token_response );
				$access_token_data = json_decode( $access_token_response_body );
				if( !isset( $access_token_data->error ) && $access_token_data->data->is_valid == 1 ){
					$access_token = $user_access_token;
				}else{
					$xtfe_user_token_options['authorize_status'] = 0;
					update_option( 'xtfe_user_token_options', $xtfe_user_token_options );
				}
			}
		}
		$this->fb_access_token = apply_filters( 'xtfe_facebook_access_token', $access_token );
		return $this->fb_access_token;
	}
	
	/**
	 * Generate Facebook api URL for grab Event.
	 *
	 * @since 1.0.0
	 */
	public function generate_facebook_api_url( $path = '', $query_args = array(), $access_token = '' ) {
		$query_args = array_merge( $query_args, array( 'access_token' => $this->get_access_token() ) );
		if( !empty( $access_token ) ){
			$query_args['access_token'] = $access_token;
		}
		$url = add_query_arg( $query_args, $this->fb_graph_url . $path );
		return $url;
	}

	/**
	 * Get organizer Name based on Organiser ID.
	 *
	 * @since    1.0.0
	 * @param array $organizer_id Organizer event.
	 * @return array
	 */
	public function get_organizer_name_by_id( $organizer_id, $full_data = false ) {
		if( !$organizer_id || $organizer_id == '' ){
			return;
		}
		$organizer_raw_data = $this->get_facebook_response_data( $organizer_id, array() );
		if( isset( $organizer_raw_data->error->message ) ){
			return false;
		}

		if( ! isset( $organizer_raw_data->name ) ){
			return false;
		}
		if( $full_data ){
			return $organizer_raw_data;
		}

		$oraganizer_name = isset( $organizer_raw_data->name ) ? $organizer_raw_data->name : '';
		return $oraganizer_name;
	}

	/**
	 * get a facebook object.
	 *
	 * @since 1.0.0
	 */
	public function get_facebook_response_data( $event_id, $args = array() ) {
		$url = $this->generate_facebook_api_url( $event_id, $args );
		$event_data = $this->get_json_response_from_url( $url );
		return $event_data;
	}

	/**
	 * get a facebook event object
	 *
	 * @since 1.0.0
	 */
	public function get_facebook_event_by_event_id( $event_id ) {
		return $this->get_facebook_response_data(
			$event_id,
			array(
				'fields' => implode(
					',',
					array(
						'id',
						'name',
						'description',
						'start_time',
						'end_time',
						'updated_time',
						'cover',
						'ticket_uri',
						'timezone',
						'owner',
						'place',
					)
				),
			)
		);
	}

	/**
	 * Get body data from url and return decoded data.
	 *
	 * @since 1.0.0
	 */
	public function get_json_response_from_url( $url ) {
		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ) );
		return $response;
	}

	/**
	 * get all events for facebook page or organizer
	 *
	 * @since 1.0.0
	 * @return array the events
	 */
	public function get_events_for_facebook_page( $facebook_args ) {

		$facebook_page_id = isset( $facebook_args['page_id'] ) ? $facebook_args['page_id'] : '';
		if( $facebook_page_id == '' ){ return array(); }
		$max_events = isset( $facebook_args['max_events'] ) ? $facebook_args['max_events'] : 10;

		$fields = array(
			'id',
			'name',
			'description',
			'start_time',
			'end_time',
			'event_times',
			'cover',
			'ticket_uri',
			'timezone',
			'place',
		);
		$include_owner = apply_filters( 'xtfe_import_owner', false );
		if( $include_owner ){
			$fields[] = 'owner';
		}

		$args = array(
			'limit'       => 999,
			'fields'      => implode(
				',',
				$fields
			)
		);
		if ( $facebook_page_id === 'me' ){
			$args['since'] = current_time('timestamp');
		}else{
			$args['time_filter'] = 'upcoming';
		}

		$page_token = false;
		$user_fb_pages = get_option('xtfe_user_token_options', array() );
		if( !empty( $user_fb_pages ) ){
			$page_data = $this->get_organizer_name_by_id( $facebook_page_id, true );
			if( isset( $page_data->id ) && isset( $user_fb_pages['access_token'] ) ){
				if( isset( $user_fb_pages['access_token'] ) && $user_fb_pages['access_token'] !== ''){
					$page_token = $user_fb_pages['access_token'];
				}
			}
		}

		$url = $this->generate_facebook_api_url( $facebook_page_id . '/events', $args );
		if( $page_token && !empty( $page_token ) ){
			$url = $this->generate_facebook_api_url( $facebook_page_id . '/events', $args, $page_token );
		}

		$response = $this->get_json_response_from_url( $url );
		$response_data = !empty( $response->data ) ? (array) $response->data : array();

		if ( empty( $response_data ) || empty( $response_data[0] ) ) {	
			return false;
		}
		$response_data = array_reverse( $response_data );
		$events_data = array_slice( $response_data, 0, $max_events );
		$events = array();
		foreach( $events_data as $key => $event ){
			if(!empty($event->event_times)){
				$has_upcoming_times = false;
				foreach ( $event->event_times as $event_time ) {
					if ( isset( $event_time->start_time ) && isset( $event_time->end_time ) && strtotime( $event_time->end_time) >= current_time('timestamp') ) {
						$has_upcoming_times  = true;
						$temp_event = clone $event;
						$temp_event->id         = $event_time->id;
						$temp_event->start_time = $event_time->start_time;
						$temp_event->end_time   = $event_time->end_time;
						$events[strtotime($temp_event->start_time)] = $temp_event;
					}
				}
				if(!$has_upcoming_times){
					$events[strtotime($event->start_time)] = $event;
				}
			}else{
				$events[strtotime($event->start_time)] = $event;
			}
		}
		ksort($events);
		return array_slice( $events, 0, $max_events );
	}

	function xtfe_clear_events_cache(){
		if ( ! empty($_POST) && wp_verify_nonce($_POST['xtfe_clear_cache_nonce'], 'xtfe_clear_cache_action' ) ) {
			$this->xtfe_purge_transient();
			$redirect_url = admin_url('admin.php?page=wpfb_events&xtcleared=1');
			wp_redirect($redirect_url);
			exit();
		} else {
			die( __('You have not access to doing this operations.', 'xt-facebook-events' ) );
		}
	}

}
