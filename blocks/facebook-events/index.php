<?php
/**
 * Facebook Events Block Initializer
 *
 * @since   1.6
 * @package    XT_Facebook_Events
 * @subpackage XT_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Gutenberg Block
 *
 * @return void
 */
function xtfe_register_gutenberg_block() {
	global $xtfe_events;
	if ( function_exists( 'register_block_type' ) ) {
		// Register block editor script.
		$js_dir = XTFE_PLUGIN_URL . 'assets/js/blocks/';
		wp_register_script(
			'xtfe-facebook-events-block',
			$js_dir . 'gutenberg.blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			XTFE_VERSION
		);

		// Register block editor style.
		$css_dir = XTFE_PLUGIN_URL . 'assets/css/';
		wp_register_style(
			'xtfe-facebook-events-block-style',
			$css_dir . 'xt-facebook-events.css',
			array(),
			XTFE_VERSION
		);
		wp_register_style(
			'xtfe-facebook-events-block-style2',
			$css_dir . 'grid_style2.css',
			array(),
			XTFE_VERSION
		);

		// Register our block.
		register_block_type( 'xtfe-block/facebook-events', array(
			'attributes' => array(
				'col'  => array(
					'type'    => 'number',
					'default' => 3
				),
				'max_events' => array(
					'type'    => 'number',
					'default' => 12
				),
				'page_id'    => array(
					'type' => 'string'
				),
				'new_window' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'type' => array(
					'type'    => 'string',
					'default' => 'page'
				),
				'layout'        => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'editor_script'   => 'xtfe-facebook-events-block', // The script name we gave in the wp_register_script() call.
			'editor_style'    => 'xtfe-facebook-events-block-style', // The script name we gave in the wp_register_style() call.
			'style'           => 'xtfe-facebook-events-block-style2', 
			'render_callback' => array( $xtfe_events->facebook, 'render_facebook_events' ),
		) );
	}
}

add_action( 'init', 'xtfe_register_gutenberg_block' );
