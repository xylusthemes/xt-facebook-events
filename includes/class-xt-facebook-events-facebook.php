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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $xtfe_events;
		
		$options = xtfe_get_options();
		$this->fb_app_id = isset( $options['facebook_app_id'] ) ? $options['facebook_app_id'] : '';
		$this->fb_app_secret = isset( $options['facebook_app_secret'] ) ? $options['facebook_app_secret'] : '';
		$this->fb_graph_url = 'https://graph.facebook.com/v2.8/';
		add_shortcode( 'wpfb_events', array( $this, 'render_facebook_events' ) );
		add_shortcode( 'fb_event_widget', array( $this, 'render_facebook_page_widget' ) );
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
				'type' 		 => 'page',
			);
			$atts = wp_parse_args( (array) $atts, $event_args );
		}

		ob_start();
		if( $this->fb_app_id == '' || $this->fb_app_secret == '' ){
			_e( 'Please insert Facebook app ID and app Secret.', 'xt-facebook-events');
		}
		if( !isset( $atts['page_id'] ) || $atts['page_id'] == '' ){
			_e( 'Please insert Facebook page ID for display events.', 'xt-facebook-events');
		}
		$facebook_events = $this->get_events_for_facebook_page( $atts );
		if( !empty( $facebook_events ) ){
			$this->render_facebook_event_listing( $facebook_events, $atts );
		}
		return ob_get_clean();
	}

	/**
	 * render shortcode for [fb_event_widget]
	 *
	 * @since  1.0.0
	 * @param  array $atts shortcode attributes
	 * @return string Generate HTML
	 */
	public function render_facebook_page_widget( $atts = array() ){

		$event_args = array(
			'page_url' 	 			=> '',
			'tabs' 					=> 'events',
			'width' 				=> 340,
			'height'				=> 500,
			'hide_cover' 			=> 'false',
			'show_facepile' 	 	=> 'false',
			'hide_cta' 				=> 'false',
			'small_header'  		=> 'false',
			'adapt_container_width'	=> 'true',
		);
		$atts = wp_parse_args( (array) $atts, $event_args );

		ob_start();
		if( $this->fb_app_id == '' ){
			_e( 'Please insert Facebook app ID.', 'xt-facebook-events');
			return ob_get_clean();
		}
		if( !isset( $atts['page_url'] ) || $atts['page_url'] == '' ){
			_e( 'Please insert Facebook page URL for display page widget.', 'xt-facebook-events');
			return ob_get_clean();
		}

		?>
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0&appId=<?php echo $this->fb_app_id; ?>';
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>

		<div class="fb-page"
		data-href="<?php echo $atts['page_url']; ?>"
		data-tabs="<?php echo $atts['tabs']; ?>"
		data-width="<?php echo $atts['width']; ?>"
		data-height="<?php echo $atts['height']; ?>"
		data-small-header="<?php echo $atts['small_header']; ?>"
		data-adapt-container-width="<?php echo $atts['adapt_container_width']; ?>"
		data-hide-cover="<?php echo $atts['hide_cover']; ?>"
		data-show-facepile="<?php echo $atts['show_facepile']; ?>"
		data-hide-cta="<?php echo $atts['hide_cta']; ?>"></div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Facebook Events Listing
	 *
	 * @since 1.0.0
	 */
	public function render_facebook_event_listing( $facebook_events, $event_args ){

		if( empty( $facebook_events ) ){ return false; }

		$new_window = false;
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
			$css_class = 'col-xtfe-md-4';
			if( isset( $event_args['col'] ) && $event_args['col'] != '' && is_numeric( $event_args['col'] ) ){
			$col = $event_args['col'];
			switch ( $col ) {
				case '1':
					$css_class = 'col-xtfe-md-12';
					break;

				case '2':
					$css_class = 'col-xtfe-md-6';
					break;

				case '3':
					$css_class = 'col-xtfe-md-4';
					break;

				case '4':
					$css_class = 'col-xtfe-md-3';
					break;
				
				default:
					$css_class = 'col-xtfe-md-4';
					break;
			}
			$cover_image = '';
		}
			echo '<div class="xtfacebook_events xtfe_containter"><div class="row_grid">';
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
			$picture_url = isset( $facebook_event->picture->data->url ) ? $facebook_event->picture->data->url : '';
			$organiser_name = isset( $facebook_event->picture->data->url ) ? $facebook_event->picture->data->url : '';
			$location = isset( $facebook_event->place->name ) ? $facebook_event->place->name : '';

			$event_date = $start_date->format('F j (h:i a)');

			if( 'widget' == $shortcode_type ){
				if( $is_display_enddate ){
					if( $start_date->format('Y-m-d h:i a') != $end_date->format('Y-m-d h:i a') ){
						if( $start_date->format('Y-m-d') == $end_date->format('Y-m-d') ){
							$event_date = $start_date->format('F j') .' ('. $start_date->format('h:i a') . ' - '. $end_date->format('h:i a') .')';
						} else {
							$event_date = $start_date->format('F j (h:i a)') . ' - ' . $end_date->format('F j (h:i a)' );
						}
					}
				}

				if( file_exists( XTFE_PLUGIN_DIR . '/templates/event-widget-' . $style . '.php' ) ){
					include XTFE_PLUGIN_DIR . '/templates/event-widget-' . $style . '.php';				
				} else {
					include XTFE_PLUGIN_DIR . '/templates/event-widget-style1.php';
				}
			} else {
				include XTFE_PLUGIN_DIR . '/templates/event-shortcode-style1.php';
			}

		}
		if( 'widget' == $shortcode_type ){
			echo '</div>';
		} else {
			echo '</div></div>';	
		}		
		echo '<div style="clear: both"></div>';
	}

	/**
	 * get access token
	 *
	 * @since 1.0.0
	 */
	public function get_access_token(){
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
		
		return $access_token;
	}
	
	/**
	 * Generate Facebook api URL for grab Event.
	 *
	 * @since 1.0.0
	 */
	public function generate_facebook_api_url( $path = '', $query_args = array() ) {
		$query_args = array_merge( $query_args, array( 'access_token' => $this->get_access_token() ) );
		
		$url = add_query_arg( $query_args, $this->fb_graph_url . $path );

		return $url;
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

		$args = array(
			'limit' => 999,
			'since' => date( 'Y-m-d' ),
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
						'picture',
						'ticket_uri',
						'timezone',
						'owner',
						'place',
					)
				),
		);

		$url = $this->generate_facebook_api_url( $facebook_page_id . '/events', $args );

		$response = $this->get_json_response_from_url( $url );
		$response_data = !empty( $response->data ) ? (array) $response->data : array();

		if ( empty( $response_data ) || empty( $response_data[0] ) ) {	
			return false;
		}
		$response_data = array_reverse( $response_data );
		$events_data = $sliced_array = array_slice( $response_data, 0, $max_events );

		return $events_data;
	}

}