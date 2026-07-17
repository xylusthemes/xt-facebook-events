<?php
/**
 * XT Facebook Events Pro - Live Feed Module Entry Point
 *
 * Load this file from xt-facebook-events-pro.php:
 *   require_once XTFEPRO_PLUGIN_DIR . 'xtfepro-feed/feed-init.php';
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------
// Constants
// ---------------------------------------------------------
if ( ! defined( 'XTFEPRO_DIR' ) ) {
	define( 'XTFEPRO_DIR', plugin_dir_path( __FILE__ ) . '../' );
}
if ( ! defined( 'XTFEPRO_URL' ) ) {
	define( 'XTFEPRO_URL', plugin_dir_url( __FILE__ ) . '../' );
}

define( 'XTFEPRO_FEED_DIR', plugin_dir_path( __FILE__ ) );
define( 'XTFEPRO_FEED_URL', plugin_dir_url( __FILE__ ) );
define( 'XTFEPRO_FEED_VERSION', '1.0.0' );
define( 'XTFEPRO_FEED_CPT', 'xtfepro_live_feed' );

// ---------------------------------------------------------
// Autoload all feed classes
// ---------------------------------------------------------
$xtfeprofeed_classes = array(
	'XTFEPRO_Feed_DB'         => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-db.php',
	'XTFEPRO_Feed_CPT'        => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-cpt.php',
	'XTFEPRO_Feed_API'        => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-api.php',
	'XTFEPRO_Feed_Admin'      => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-admin.php',
	'XTFEPRO_Feed_Shortcode'  => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-shortcode.php',
	'XTFEPRO_Feed_Scheduler'  => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-scheduler.php',
	'XTFEPRO_Feed_AJAX'       => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-ajax.php',
	'XTFEPRO_Feed_Builder_UI' => XTFEPRO_FEED_DIR . 'includes/class-xtfeprofeed-builder-ui.php',
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
foreach ( $xtfeprofeed_classes as $class => $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

// ---------------------------------------------------------
// Boot all modules
// ---------------------------------------------------------
add_action( 'init', array( XTFEPRO_Feed_CPT::instance(), 'register_cpt' ) );
add_action( 'init', array( XTFEPRO_Feed_Shortcode::instance(), 'init' ) );
add_action( 'init', array( XTFEPRO_Feed_AJAX::instance(), 'init' ) );
XTFEPRO_Feed_API::instance();

// Initialize DB table for HQ image cache
add_action( 'admin_init', array( XTFEPRO_Feed_DB::instance(), 'maybe_create_table' ) );

// Weekly image cleanup cron
add_action( 'init', array( XTFEPRO_Feed_DB::instance(), 'schedule_cleanup' ) );
add_action( 'xtfeprofeed_weekly_image_cleanup', array( XTFEPRO_Feed_DB::instance(), 'run_weekly_cleanup' ) );

if ( is_admin() ) {
	add_action( 'init', array( XTFEPRO_Feed_CPT::instance(), 'init_admin_hooks' ) );
	add_action( 'init', array( XTFEPRO_Feed_Admin::instance(), 'init' ) );
	add_action( 'init', array( XTFEPRO_Feed_Builder_UI::instance(), 'init' ) );
}

add_action( 'init', function () {
	if ( function_exists( 'as_schedule_recurring_action' ) ) {
		XTFEPRO_Feed_Scheduler::instance()->init();
	}
}, 20 );
