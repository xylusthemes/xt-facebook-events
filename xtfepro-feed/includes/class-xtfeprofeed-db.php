<?php
/**
 * XTFEPRO Feed DB - Custom database table for HQ event images.
 *
 * Stores high-quality images fetched via the event detail API separately
 * from the transient event cache, so images persist across cache clears.
 *
 * Auto-cleanup: Images older than 7 days are purged weekly via WP-Cron.
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_DB {

	/** @var XTFEPRO_Feed_DB */
	private static $instance = null;

	/** @var string DB table name (with prefix) */
	private $table_images;

	/** @var string DB version option key */
	private $db_version_key = 'xtfeprofeed_db_version';

	/** @var string Current schema version */
	private $db_version = '1.0';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		$this->table_images = $wpdb->prefix . 'xtfeprofeed_images';
	}

	// -------------------------------------------------------
	// Table creation
	// -------------------------------------------------------

	/**
	 * Create/update the images table. Called on plugin activation and admin_init.
	 */
	public function maybe_create_table() {
		$installed_version = get_option( $this->db_version_key, '0' );
		if ( version_compare( $installed_version, $this->db_version, '>=' ) ) {
			return;
		}

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_images} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id VARCHAR(100) NOT NULL,
			image_url TEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY event_id (event_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( $this->db_version_key, $this->db_version );
	}

	// -------------------------------------------------------
	// Image CRUD
	// -------------------------------------------------------

	/**
	 * Get a cached HQ image URL for an event.
	 *
	 * @param string $event_id Facebook event ID.
	 * @return string|false Image URL or false if not cached.
	 */
	public function get_image( $event_id ) {
		global $wpdb;
		$url = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT image_url FROM {$this->table_images} WHERE event_id = %s LIMIT 1",
				(string) $event_id
			)
		);
		return $url ?: false;
	}

	/**
	 * Save (upsert) a HQ image URL for an event.
	 *
	 * @param string $event_id  Facebook event ID.
	 * @param string $image_url Full image URL.
	 * @return bool True on success.
	 */
	public function save_image( $event_id, $image_url ) {
		global $wpdb;

		// REPLACE INTO = upsert (event_id has UNIQUE key)
		$result = $wpdb->replace(
			$this->table_images,
			array(
				'event_id'   => (string) $event_id,
				'image_url'  => $image_url,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Delete a single event image.
	 *
	 * @param string $event_id Facebook event ID.
	 */
	public function delete_image( $event_id ) {
		global $wpdb;
		$wpdb->delete(
			$this->table_images,
			array( 'event_id' => (string) $event_id ),
			array( '%s' )
		);
	}

	/**
	 * Delete ALL cached images (hard cache clear).
	 *
	 * @return int Number of rows deleted.
	 */
	public function delete_all_images() {
		global $wpdb;
		return (int) $wpdb->query( "TRUNCATE TABLE {$this->table_images}" );
	}

	/**
	 * Get total count of cached images.
	 *
	 * @return int
	 */
	public function get_image_count() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_images}" );
	}

	// -------------------------------------------------------
	// Weekly cleanup (auto-delete images older than 7 days)
	// -------------------------------------------------------

	/**
	 * Schedule the weekly cleanup cron if not already scheduled.
	 */
	public function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'xtfeprofeed_weekly_image_cleanup' ) ) {
			wp_schedule_event( time(), 'weekly', 'xtfeprofeed_weekly_image_cleanup' );
		}
	}

	/**
	 * Unschedule cleanup on deactivation.
	 */
	public static function unschedule_cleanup() {
		$timestamp = wp_next_scheduled( 'xtfeprofeed_weekly_image_cleanup' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'xtfeprofeed_weekly_image_cleanup' );
		}
	}

	/**
	 * Run the weekly cleanup — delete images older than 7 days.
	 */
	public function run_weekly_cleanup() {
		global $wpdb;
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( 7 * DAY_IN_SECONDS ) );
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table_images} WHERE created_at < %s",
				$cutoff
			)
		);
	}

	/**
	 * Get the table name (for debugging / status display).
	 *
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_images;
	}
}
