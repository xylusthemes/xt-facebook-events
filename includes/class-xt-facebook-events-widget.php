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

/**
 * Core class used to implement a Facebook Events widget
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class XT_Facebook_Events_Widget extends WP_Widget {

	/**
	 * Defualt widget options
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public $default_options;

	/**
	 * widget style options
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public $display_styles;
	
	/**
	 * Sets up a new Facebook Events widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'widget_XT_Facebook_Events_Widget',
			'description' => __( 'Display Facebook Events.' ),
		);
		$control_ops = array( 'width' => '', 'height' => '' );
		parent::__construct( 'xtfacebook_widget', __( 'Facebook Events', 'xt-facebook-events' ), $widget_ops, $control_ops );

		$this->default_options = array(
				'title' 	 	=> '',
				'page_id' 	 	=> '',
				'max_events' 	=> 10,
				'display_style' => 'style1',
				'new_window' 	=> 1,
				'display_event_image' 	 => 1,
				'display_event_location' => 1,
				'display_event_enddate'  => 0,
				'display_event_desc'	 => 0,
			);

		$this->display_styles = array(
			'style1' => __( 'Style 1', 'xt-facebook-events' ),
			'style2' => __( 'Style 2 (coming soon)','xt-facebook-events' ),
			);
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current widget instance.
	 */
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __('Facebook Events', 'xt-facebook-events') : $instance['title'], $instance, $this->id_base );
		
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
			<div class="facebookevents_widget">
				<?php $this->xt_render_facebook_events( $args, $instance ); ?>
			</div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		global $xtfe_events;
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['page_id'] = sanitize_text_field( $new_instance['page_id'] );
		$instance['max_events'] = sanitize_text_field( $new_instance['max_events'] );
		$instance['display_style'] = sanitize_text_field( $new_instance['display_style'] );
		$instance['new_window'] = $new_instance['new_window'] ? 1 : 0;
		$instance['display_event_image'] = $new_instance['display_event_image'] ? 1 : 0;
		$instance['display_event_location'] = $new_instance['display_event_location'] ? 1 : 0;
		$instance['display_event_enddate'] = $new_instance['display_event_enddate'] ? 1 : 0;
		$instance['display_event_desc'] = $new_instance['display_event_desc'] ? 1 : 0;
		// purge transient.
		$xtfe_events->facebook->xtfe_purge_transient();
		return $instance;
	}

	/**
	 * Outputs the Facebook Events widget settings form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->default_options );
		extract( $instance );
		$title = sanitize_text_field( $instance['title'] );
		$this->render_input_field( 'title', $title, __( 'Title:', 'xt-facebook-events' ), 'text');
		$this->render_input_field( 'page_id', $page_id, __( 'Facebook Page ID:', 'xt-facebook-events' ), 'text' );
		$this->render_input_field( 'max_events', $max_events, __( 'Max. Events:', 'xt-facebook-events' ), 'number' );
		$this->render_input_field( 'display_style', $display_style, __( 'Select Event listing style', 'xt-facebook-events' ), 'select', '', $this->display_styles );
		$this->render_input_field( 'new_window', $new_window, __( 'Open Events in new window', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'display_event_image', $display_event_image, __( 'Display Event Image', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'display_event_location', $display_event_location, __( 'Display Event Location', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'display_event_enddate', $display_event_enddate, __( 'Display Event Enddate', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'display_event_desc', $display_event_desc, __( 'Display Event Description', 'xt-facebook-events' ), 'checkbox' );
		
	}

	/**
	 * Generate and render HTML for input element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function render_input_field( $name, $value, $title, $type = 'text', $description = '', $options = array() ){
		$name = $this->get_field_name( $name );
		$id = $this->get_field_name( $name );

		switch ( $type ) {
			case 'text':
				?>
				<p>
					<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
					<input class="widefat" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="text" value="<?php echo esc_attr($value); ?>" />
				</p>
				<?php
				break;

			case 'number':
				?>
				<p>
					<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
					<input class="widefat" id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="number" min="0" value="<?php echo esc_attr($value); ?>" />
				</p>
				<?php
				break;


			case 'checkbox':
				?>
				<p>
					<input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="checkbox"<?php checked( $value ); ?> />&nbsp;<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
				</p>
				<?php
				break;

			case 'select':
				?>
				<p>
					<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
					<select class="widefat" id="<?php echo $id; ?>" name="<?php echo $name; ?>">
					<?php 
					if( !empty( $options) ){
						foreach ($options as $key => $option) {
							echo '<option value="' . $key . '" ' . selected( $value, $key ) . '>' . $option . '</option>';
						}
					}
					?>
					</select>
				</p>
				<?php
				break;

			default:
				break;
		}
	}

	/**
	 * Outputs Facebook Events
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function xt_render_facebook_events( $args, $instance ){
		$attr_array = array();
		if( isset( $instance['max_events'] ) && $instance['max_events'] != '' ){
			$attr_array[]= 'max_events ="'.esc_attr( $instance["max_events"] ).'"';
		}
		if( isset( $instance['page_id'] ) && $instance['page_id'] != '' ){
			$attr_array[]= 'page_id ="'.esc_attr( $instance["page_id"] ).'"';
		}
		if( isset( $instance['new_window'] ) && $instance['new_window'] != '' ){
			$attr_array[]= 'new_window ="1"';
		}
		if(isset( $instance['display_style'] ) && $instance['display_style'] != '' ){
			$attr_array[]= 'style = '. esc_attr( $instance['display_style'] );
		}
		if( $instance['display_event_image'] ){
			$attr_array[]= 'display_event_image ="1"';
		}
		if( $instance['display_event_location'] ){
			$attr_array[]= 'display_event_location ="1"';
		}
		if( $instance['display_event_enddate'] ){
			$attr_array[]= 'display_event_enddate ="1"';
		}
		if( $instance['display_event_desc'] ){
			$attr_array[]= 'display_event_desc ="1"';
		}
		$attr_str = implode(' ', $attr_array );
		echo do_shortcode( '[wpfb_events type="widget" ' . $attr_str . ']' );
	}
}
