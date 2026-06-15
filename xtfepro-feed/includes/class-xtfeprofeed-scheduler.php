<?php
/**
 * XT Facebook Events Pro Live Feed - Action Scheduler Integration
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_Scheduler {

	private static $instance = null;

	const ACTION_HOOK = 'xtfeprofeed_refresh_cache';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( self::ACTION_HOOK, array( $this, 'run_refresh' ) );
		add_action( 'xtfeprofeed_settings_saved', array( $this, 'handle_settings_saved' ) );
		add_action( 'before_delete_post', array( $this, 'handle_feed_deleted' ) );
	}

	public function run_refresh( $feed_id ) {
		$feed_id = absint( $feed_id );
		if ( ! $feed_id ) return;
		$auto = get_post_meta( $feed_id, '_xtfeprofeed_auto_refresh', true );
		if ( ! $auto ) {
			$this->unschedule( $feed_id );
			return;
		}
		XTFEPRO_Feed_API::instance()->get_events( $feed_id, true );
	}

	public function handle_settings_saved( $feed_id ) {
		$auto     = get_post_meta( $feed_id, '_xtfeprofeed_auto_refresh', true );
		$duration = absint( get_post_meta( $feed_id, '_xtfeprofeed_cache_duration', true ) ?: 1440 );
		if ( $auto ) {
			$this->schedule( $feed_id, $duration );
		} else {
			$this->unschedule( $feed_id );
		}
	}

	public function handle_feed_deleted( $post_id ) {
		if ( get_post_type( $post_id ) === XTFEPRO_FEED_CPT ) {
			$this->unschedule( $post_id );
			XTFEPRO_Feed_API::instance()->clear_cache( $post_id );
		}
	}

	private function schedule( $feed_id, $duration_minutes ) {
		$this->unschedule( $feed_id );
		$interval_seconds = $duration_minutes * MINUTE_IN_SECONDS;
		as_schedule_recurring_action(
			time() + $interval_seconds,
			$interval_seconds,
			self::ACTION_HOOK,
			array( 'feed_id' => $feed_id ),
			'xtfeprofeed'
		);
	}

	private function unschedule( $feed_id ) {
		as_unschedule_all_actions(
			self::ACTION_HOOK,
			array( 'feed_id' => $feed_id ),
			'xtfeprofeed'
		);
	}

	public static function get_next_run( $feed_id ) {
		return as_next_scheduled_action(
			self::ACTION_HOOK,
			array( 'feed_id' => absint( $feed_id ) ),
			'xtfeprofeed'
		) ?: false;
	}
}
