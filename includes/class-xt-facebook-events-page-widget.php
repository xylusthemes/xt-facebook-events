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
 * @since 1.0.2
 *
 * @see WP_Widget
 */
class XT_Facebook_Events_Page_Widget extends WP_Widget {

	/**
	 * Defualt widget options
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public $default_options;

	/**
	 * Sets up a new Facebook Events widget instance.
	 *
	 * @since 1.0.2
	 * @access public
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'widget_XT_Facebook_Events_Page_Widget',
			'description' => __( 'Facebook Page Events Widget', 'xt-facebook-events' ),
		);
		$control_ops = array( 'width' => '', 'height' => '' );
		parent::__construct( 'xtfacebook_page_widget', __( 'Facebook Page Events Widget', 'xt-facebook-events' ), $widget_ops, $control_ops );

		$this->default_options = array(
				'title' 	 			=> '',
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
	}

	/**
	 * Outputs the content for the current widget instance.
	 *
	 * @since 1.0.2
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
				<?php $this->xt_render_facebook_page_widget( $args, $instance ); ?>
			</div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * @since 1.0.2
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['page_url'] = esc_url( $new_instance['page_url'] );
		$instance['width'] = $new_instance['width'] ? $new_instance['width'] : 340;
		$instance['height'] = $new_instance['height'] ? $new_instance['height'] : 500;		
		$instance['hide_cover'] = $new_instance['hide_cover'] ? 'true' : 'false';
		$instance['show_facepile'] = $new_instance['show_facepile'] ? 'true' : 'false';
		$instance['hide_cta'] = $new_instance['hide_cta'] ? 'true' : 'false';
		$instance['small_header'] = $new_instance['small_header'] ? 'true' : 'false';
		return $instance;
	}

	/**
	 * Outputs the Facebook Events widget settings form.
	 *
	 * @since 1.0.2
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->default_options );
		extract( $instance );
		$title = sanitize_text_field( $instance['title'] );
		$this->render_input_field( 'title', $title, __( 'Title:', 'xt-facebook-events' ), 'text');
		$this->render_input_field( 'page_url', $page_url, __( 'Facebook Page URL:', 'xt-facebook-events' ), 'text' );
		$this->render_input_field( 'width', $width, __( 'Width:', 'xt-facebook-events' ), 'number' );
		$this->render_input_field( 'height', $height, __( 'Height:', 'xt-facebook-events' ), 'number' );
		$this->render_input_field( 'small_header', $small_header, __( 'Use Small Header', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'show_facepile', $show_facepile, __( 'Show Friend\'s Faces', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'hide_cover', $hide_cover, __( 'Hide Cover Photo', 'xt-facebook-events' ), 'checkbox' );
		$this->render_input_field( 'hide_cta', $hide_cta, __( 'Hide call to action button (if available)', 'xt-facebook-events' ), 'checkbox' );
	}

	/**
	 * Generate and render HTML for input element.
	 *
	 * @since 1.0.2
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
					<input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="checkbox"<?php checked( 'true', $value ); ?> />&nbsp;<label for="<?php echo $id; ?>"><?php echo $title; ?></label>
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
	 * @since 1.0.2
	 * @access public
	 *
	 */
	public function xt_render_facebook_page_widget( $args, $instance ){
		$attr_array = array();
		if( isset( $instance['page_url'] ) && $instance['page_url'] != '' ){
			$attr_array[]= 'page_url ="'.esc_attr( $instance["page_url"] ).'"';
		}
		if( $instance['width'] ){
			$attr_array[]= 'width ="'.$instance["width"].'"';
		}
		if( $instance['height'] ){
			$attr_array[]= 'height ="'.$instance["height"].'"';
		}
		if( $instance['hide_cover'] ){
			$attr_array[]= 'hide_cover ="'.$instance["hide_cover"].'"';
		}
		if( $instance['show_facepile'] ){
			$attr_array[]= 'show_facepile ="'.$instance["show_facepile"].'"';
		}
		if( $instance['hide_cta'] ){
			$attr_array[]= 'hide_cta ="'.$instance["hide_cta"].'"';
		}
		if( $instance['small_header'] ){
			$attr_array[]= 'small_header ="'.$instance["small_header"].'"';
		}
		$attr_str = implode(' ', $attr_array );
		echo do_shortcode( '[fb_event_widget ' . $attr_str . ']' );
	}
}
