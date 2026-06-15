<?php
/**
 * XT Facebook Events Pro Live Feed - Admin Meta Boxes
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_Admin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . XTFEPRO_FEED_CPT, array( $this, 'save_meta' ) );
		add_action( 'admin_post_xtfeprofeed_clear_cache', array( $this, 'handle_clear_cache_row_action' ) );
		add_action( 'load-edit.php', array( $this, 'maybe_redirect_empty_feed_list' ) );
	}

	public function maybe_redirect_empty_feed_list() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . XTFEPRO_FEED_CPT !== $screen->id ) return;
		$has_visited = get_option( 'xtfeprofeed_has_visited_list', false );
		if ( ! $has_visited ) {
			update_option( 'xtfeprofeed_has_visited_list', true );
			wp_safe_redirect( admin_url( 'post-new.php?post_type=' . XTFEPRO_FEED_CPT ) );
			exit;
		}
	}

	// -------------------------------------------------------
	// Meta boxes
	// -------------------------------------------------------

	public function add_meta_boxes() {
		add_meta_box(
			'xtfeprofeed_shortcode_box',
			__( 'Your Shortcode', 'xt-facebook-events-pro' ),
			array( $this, 'render_shortcode_box' ),
			XTFEPRO_FEED_CPT, 'normal', 'high'
		);
		add_meta_box(
			'xtfeprofeed_settings',
			__( 'Feed Settings', 'xt-facebook-events-pro' ),
			array( $this, 'render_meta_box' ),
			XTFEPRO_FEED_CPT, 'normal', 'default'
		);
	}

	public function render_shortcode_box( $post ) {
		if ( ! $post->ID || 'auto-draft' === $post->post_status ) {
			echo '<p>' . esc_html__( 'Save the feed first to get your shortcode.', 'xt-facebook-events-pro' ) . '</p>';
			return;
		}
		$shortcode = '[xtfepro_live_feed id="' . $post->ID . '"]';
		?>
		<p style="margin-bottom:6px;font-size:12px;color:#666;">
			<?php esc_html_e( 'Paste this shortcode into any page or post:', 'xt-facebook-events-pro' ); ?>
		</p>
		<div style="display:flex;gap:6px;align-items:center;">
			<input type="text" readonly
				value="<?php echo esc_attr( $shortcode ); ?>"
				id="xtfeprofeed-shortcode-input"
				style="flex:1;font-family:monospace;font-size:13px;"
				onclick="this.select();"
			/>
			<button type="button" class="button" id="xtfeprofeed-copy-shortcode-btn">
				<?php esc_html_e( 'Copy', 'xt-facebook-events-pro' ); ?>
			</button>
		</div>
		<p style="margin-top:8px;font-size:12px;color:#666;">
			<?php esc_html_e( 'Override options inline:', 'xt-facebook-events-pro' ); ?><br>
			<code style="font-size:11px;">[xtfepro_live_feed id="<?php echo esc_html( $post->ID ); ?>" columns="2" per_page="6"]</code>
		</p>
		<?php
	}

	// -------------------------------------------------------
	// Main tabbed meta box
	// -------------------------------------------------------

	public function render_meta_box( $post ) {
		wp_nonce_field( 'xtfeprofeed_save_meta', 'xtfeprofeed_nonce' );
		$meta = XTFEPRO_Feed_API::instance()->get_feed_meta( $post->ID );
		?>
		<div class="xtfeprofeed-builder-layout">
		<div class="xtfeprofeed-builder-left">
		<div class="xtfeprofeed-tabs">
			<ul class="xtfeprofeed-tabs-nav">
				<li><a href="#xtfeprofeed-tab-source" class="active"><?php esc_html_e( 'Source', 'xt-facebook-events-pro' ); ?></a></li>
				<li><a href="#xtfeprofeed-tab-display"><?php esc_html_e( 'Display', 'xt-facebook-events-pro' ); ?></a></li>
				<li><a href="#xtfeprofeed-tab-tickets"><?php esc_html_e( 'Buttons', 'xt-facebook-events-pro' ); ?></a></li>
				<li><a href="#xtfeprofeed-tab-filters"><?php esc_html_e( 'Filters', 'xt-facebook-events-pro' ); ?></a></li>
				<li><a href="#xtfeprofeed-tab-settings"><?php esc_html_e( 'Settings', 'xt-facebook-events-pro' ); ?></a></li>
			</ul>

			<?php // ===== TAB 1: SOURCE ===== ?>
			<div id="xtfeprofeed-tab-source" class="xtfeprofeed-tab-content active">
				<table class="form-table xtfeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Source Type', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php
							$sources = array(
								'page_id'   => __( 'Facebook Page ID/Slug', 'xt-facebook-events-pro' ),
								'event_ids' => __( 'Specific Event IDs', 'xt-facebook-events-pro' ),
								'ical_url'  => __( 'iCal URL', 'xt-facebook-events-pro' ),
							);
							foreach ( $sources as $val => $label ) :
								$disabled = '';
								$label_suffix = '';
								$allowed_sources = apply_filters( 'xtfeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) );
								if ( ! in_array( $val, $allowed_sources, true ) ) {
									$disabled = 'disabled';
									$label_suffix = ' <span class="xtfe-pro-badge" style="color:red;font-size:10px;font-weight:bold;text-transform:uppercase;">(' . __( 'Pro Only', 'xt-facebook-events' ) . ')</span>';
								}
							?>
							<label style="margin-right:16px; <?php echo $disabled ? 'opacity:0.6; cursor:not-allowed;' : ''; ?>">
								<input type="radio"
									name="_xtfeprofeed_source_type"
									value="<?php echo esc_attr( $val ); ?>"
									<?php checked( $meta['source_type'], $val ); ?>
									<?php echo $disabled; ?>
									class="xtfeprofeed-source-type-radio"
								/>
								<?php echo esc_html( $label ) . $label_suffix; ?>
							</label>
							<?php endforeach; ?>
						</td>
					</tr>

					<tr class="xtfeprofeed-source-row xtfeprofeed-source-page_id" <?php echo 'page_id' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="xtfeprofeed_page_id"><?php esc_html_e( 'Facebook Page ID / Username', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<input type="text" id="xtfeprofeed_page_id" name="_xtfeprofeed_page_id"
								value="<?php echo esc_attr( $meta['page_id'] ); ?>"
								class="large-text" placeholder="e.g. Fashionmantraexhibitions or page numeric ID" <?php disabled( ! in_array( 'page_id', apply_filters( 'xtfeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) ), true ) ); ?> />
							<p class="description">
								<?php esc_html_e( 'Enter the username slug or ID of the Facebook page.', 'xt-facebook-events-pro' ); ?>
								<?php if ( ! in_array( 'page_id', apply_filters( 'xtfeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) ), true ) ) : ?>
									<br><span style="color:red;font-weight:bold;"><?php printf( __( 'Available in %s version.', 'xt-facebook-events' ), '<a href="https://xylusthemes.com/plugins/xt-facebook-events/" target="_blank">Pro</a>' ); ?></span>
								<?php endif; ?>
							</p>
						</td>
					</tr>

					<tr class="xtfeprofeed-source-row xtfeprofeed-source-event_ids" <?php echo 'event_ids' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="xtfeprofeed_event_ids"><?php esc_html_e( 'Event IDs', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<input type="text" id="xtfeprofeed_event_ids" name="_xtfeprofeed_event_ids"
								value="<?php echo esc_attr( $meta['event_ids'] ); ?>"
								class="large-text" placeholder="e.g. 3124795954373363, 1531644045138212" />
							<p class="description"><?php esc_html_e( 'Comma-separated Facebook Event IDs.', 'xt-facebook-events-pro' ); ?></p>
						</td>
					</tr>

					<tr class="xtfeprofeed-source-row xtfeprofeed-source-ical_url" <?php echo 'ical_url' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="xtfeprofeed_ical_url"><?php esc_html_e( 'Facebook iCal URL', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<input type="text" id="xtfeprofeed_ical_url" name="_xtfeprofeed_ical_url"
								value="<?php echo esc_attr( $meta['ical_url'] ); ?>"
								class="large-text" placeholder="e.g. https://www.facebook.com/events/ical/upcoming/?uid=...&key=..." />
							<p class="description"><?php esc_html_e( 'Enter your Facebook iCal export URL.', 'xt-facebook-events-pro' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<?php // ===== TAB 2: DISPLAY ===== ?>
			<div id="xtfeprofeed-tab-display" class="xtfeprofeed-tab-content">
				<table class="form-table xtfeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Layout', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<div class="xtfeprofeed-layout-picker">
								<?php
								$layouts = array(
									'card-grid'    => array( 'label' => __( 'Card Grid', 'xt-facebook-events-pro' ), 'icon' => '⊞' ),
									'list'         => array( 'label' => __( 'List', 'xt-facebook-events-pro' ), 'icon' => '☰' ),
									'masonry'      => array( 'label' => __( 'Masonry', 'xt-facebook-events-pro' ), 'icon' => '⊟' ),
									'minimal-grid' => array( 'label' => __( 'Minimal Grid', 'xt-facebook-events-pro' ), 'icon' => '◫' ),
									'compact-list' => array( 'label' => __( 'Compact List', 'xt-facebook-events-pro' ), 'icon' => '☶' ),
									'timeline'     => array( 'label' => __( 'Timeline', 'xt-facebook-events-pro' ), 'icon' => '↧' ),
									'ticket-list'  => array( 'label' => __( 'Ticket', 'xt-facebook-events-pro' ), 'icon' => '🎟' ),
								);
								foreach ( $layouts as $val => $data ) :
									$allowed_layouts = apply_filters( 'xtfeprofeed_allowed_layouts', array( 'card-grid', 'list' ) );
									$is_allowed = in_array( $val, $allowed_layouts, true );
									$class = $meta['layout'] === $val ? 'active' : '';
									if ( ! $is_allowed ) {
										$class .= ' xtfeprofeed-layout-pro-only';
									}
								?>
								<label class="xtfeprofeed-layout-option <?php echo esc_attr( $class ); ?>" style="<?php echo ! $is_allowed ? 'opacity:0.6; cursor:not-allowed; position:relative;' : ''; ?>">
									<input type="radio" name="_xtfeprofeed_layout" value="<?php echo esc_attr( $val ); ?>" <?php checked( $meta['layout'], $val ); ?> <?php disabled( ! $is_allowed ); ?> />
									<span class="xtfeprofeed-layout-icon"><?php echo esc_html( $data['icon'] ); ?></span>
									<span class="xtfeprofeed-layout-label"><?php echo esc_html( $data['label'] ); ?></span>
									<?php if ( ! $is_allowed ) : ?>
										<span class="xtfe-pro-badge" style="position:absolute; top:2px; right:2px; background:red; color:#fff; font-size:8px; font-weight:bold; padding:1px 3px; border-radius:3px; line-height:1;"><?php esc_html_e( 'PRO', 'xt-facebook-events' ); ?></span>
									<?php endif; ?>
								</label>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Columns', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php $this->render_radio_row( '_xtfeprofeed_columns', $meta['columns'], array( 1, 2, 3, 4 ) ); ?>
							<p class="description"><?php esc_html_e( 'Number of columns on desktop. Mobile is always 1 column.', 'xt-facebook-events-pro' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Show Fields', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php
							$toggles = array(
								'_xtfeprofeed_show_image'      => array( 'label' => __( 'Event Cover Image', 'xt-facebook-events-pro' ), 'default' => true ),
								'_xtfeprofeed_show_date'       => array( 'label' => __( 'Date & Time', 'xt-facebook-events-pro' ), 'default' => true ),
								'_xtfeprofeed_show_venue'      => array( 'label' => __( 'Venue / Location', 'xt-facebook-events-pro' ), 'default' => true ),
								'_xtfeprofeed_show_organizer'  => array( 'label' => __( 'Organizer Name', 'xt-facebook-events-pro' ), 'default' => false ),
								'_xtfeprofeed_show_price'      => array( 'label' => __( 'Free / Paid Badge', 'xt-facebook-events-pro' ), 'default' => true ),
								'_xtfeprofeed_show_ticket_btn' => array( 'label' => __( '"View Event" Button', 'xt-facebook-events-pro' ), 'default' => true ),
							);
							$meta_keys = array(
								'_xtfeprofeed_show_image'      => $meta['show_image'],
								'_xtfeprofeed_show_date'       => $meta['show_date'],
								'_xtfeprofeed_show_venue'      => $meta['show_venue'],
								'_xtfeprofeed_show_organizer'  => $meta['show_organizer'],
								'_xtfeprofeed_show_price'      => $meta['show_price'],
								'_xtfeprofeed_show_ticket_btn' => $meta['show_ticket_btn'],
							);
							foreach ( $toggles as $key => $info ) :
								$checked = $meta_keys[ $key ] ?? $info['default'];
							?>
							<label style="display:block;margin-bottom:6px;">
								<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $checked, true ); echo checked( $checked, '1', false ); ?> />
								<?php echo esc_html( $info['label'] ); ?>
							</label>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>
			</div>

			<?php // ===== TAB 3: TICKETS ===== ?>
			<div id="xtfeprofeed-tab-tickets" class="xtfeprofeed-tab-content">
				<table class="form-table xtfeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Button Type', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<p class="description"><?php esc_html_e( 'Buttons link directly to the Facebook event page.', 'xt-facebook-events-pro' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="xtfeprofeed_register_label"><?php esc_html_e( 'Button Label', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<input type="text" id="xtfeprofeed_register_label" name="_xtfeprofeed_register_label"
								value="<?php echo esc_attr( $meta['register_label'] ); ?>"
								class="regular-text" />
						</td>
					</tr>
				</table>
			</div>
			</div>

			<?php // ===== TAB 4: FILTERS ===== ?>
			<div id="xtfeprofeed-tab-filters" class="xtfeprofeed-tab-content">
				<table class="form-table xtfeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Time Filter', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php $this->render_select( '_xtfeprofeed_time_filter', $meta['time_filter'], array(
								'today'            => __( 'Today', 'xt-facebook-events-pro' ),
								'upcoming_week'    => __( 'Upcoming Week', 'xt-facebook-events-pro' ),
								'upcoming_15_days' => __( 'Upcoming 15 Days', 'xt-facebook-events-pro' ),
								'upcoming_month'   => __( 'Upcoming Month', 'xt-facebook-events-pro' ),
								'current_future'   => __( 'All Upcoming / Current', 'xt-facebook-events-pro' ),
								'custom'           => __( 'Custom Date Range', 'xt-facebook-events-pro' ),
								'all'              => __( 'All (No Filter)', 'xt-facebook-events-pro' ),
							) ); ?>
						</td>
					</tr>
					<tr class="xtfeprofeed-time-row xtfeprofeed-time-custom" <?php echo 'custom' !== $meta['time_filter'] ? 'style="display:none"' : ''; ?>>
						<th><label for="xtfeprofeed_start_date"><?php esc_html_e( 'Start Date', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<input type="text" id="xtfeprofeed_start_date" name="_xtfeprofeed_start_date"
								value="<?php echo esc_attr( $meta['start_date'] ); ?>"
								class="regular-text xtfeprofeed-datepicker" placeholder="YYYY-MM-DD" />
						</td>
					</tr>
					<tr class="xtfeprofeed-time-row xtfeprofeed-time-custom" <?php echo 'custom' !== $meta['time_filter'] ? 'style="display:none"' : ''; ?>>
						<th><label for="xtfeprofeed_end_date"><?php esc_html_e( 'End Date', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<input type="text" id="xtfeprofeed_end_date" name="_xtfeprofeed_end_date"
								value="<?php echo esc_attr( $meta['end_date'] ); ?>"
								class="regular-text xtfeprofeed-datepicker" placeholder="YYYY-MM-DD" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Events Per Page', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php $this->render_radio_row( '_xtfeprofeed_per_page', $meta['per_page'], array( 6, 9, 10, 12, 20, 30, 40, 50 ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Pagination Type', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php $this->render_select( '_xtfeprofeed_pagination_type', $meta['pagination_type'], array(
								'ajax'            => __( 'Numbered Pagination (AJAX)', 'xt-facebook-events-pro' ),
								'load_more'       => __( 'Load More Button', 'xt-facebook-events-pro' ),
								'infinite_scroll' => __( 'Infinite Scroll', 'xt-facebook-events-pro' ),
								'none'            => __( 'No Pagination (Show All)', 'xt-facebook-events-pro' ),
							) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Online Events', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="_xtfeprofeed_hide_online" value="1" <?php checked( $meta['hide_online'], '1' ); ?> />
								<?php esc_html_e( 'Hide online-only events', 'xt-facebook-events-pro' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>

			<?php // ===== TAB 5: SETTINGS ===== ?>
			<div id="xtfeprofeed-tab-settings" class="xtfeprofeed-tab-content">
				<table class="form-table xtfeprofeed-form-table">
					<tr>
					<th><?php esc_html_e( 'Cache Duration', 'xt-facebook-events-pro' ); ?></th>
					<td>
						<?php
						$presets      = array( 60 => __( '1 Hour', 'xt-facebook-events-pro' ), 360 => __( '6 Hours', 'xt-facebook-events-pro' ), 720 => __( '12 Hours', 'xt-facebook-events-pro' ), 1440 => __( '24 Hours', 'xt-facebook-events-pro' ) );
						$current_val  = absint( $meta['cache_duration'] ?: 1440 );
						$is_preset    = array_key_exists( $current_val, $presets );
						$is_custom    = ! $is_preset;
						$custom_hours = $is_custom ? round( $current_val / 60 ) : 5;
						?>
						<?php foreach ( $presets as $val => $label ) : ?>
						<label style="margin-right:12px;">
							<input type="radio" name="_xtfeprofeed_cache_duration" value="<?php echo esc_attr( $val ); ?>"
								class="xtfeprofeed-cache-preset"
								<?php checked( $is_preset && $current_val === $val ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
						<?php endforeach; ?>
						<label style="margin-right:12px;">
							<input type="radio" name="_xtfeprofeed_cache_duration" value="custom"
								class="xtfeprofeed-cache-preset"
								<?php checked( $is_custom ); ?> />
							<?php esc_html_e( 'Custom', 'xt-facebook-events-pro' ); ?>
						</label>
						<span class="xtfeprofeed-cache-custom-wrap" <?php echo ! $is_custom ? 'style="display:none"' : ''; ?>>
							<input type="number" name="_xtfeprofeed_cache_duration_custom" id="xtfeprofeed_cache_custom"
								value="<?php echo esc_attr( $custom_hours ); ?>"
								min="1" step="1" class="small-text" placeholder="e.g. 5" />
							<span><?php esc_html_e( 'hours', 'xt-facebook-events-pro' ); ?></span>
						</span>
						<p class="description"><?php esc_html_e( 'Events are fetched from Facebook once per this interval.', 'xt-facebook-events-pro' ); ?></p>
					</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Auto-Refresh Cache', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php $has_as = function_exists( 'as_schedule_recurring_action' ); ?>
							<label>
								<input type="checkbox" name="_xtfeprofeed_auto_refresh" value="1"
									<?php checked( $meta['auto_refresh'], '1' ); ?>
									<?php disabled( ! $has_as ); ?> />
								<?php esc_html_e( 'Automatically refresh cache in background', 'xt-facebook-events-pro' ); ?>
								<strong><?php esc_html_e( '(uses Action Scheduler)', 'xt-facebook-events-pro' ); ?></strong>
							</label>
							<?php if ( ! $has_as ) : ?>
							<p class="description" style="color:#d63638;">
								<?php esc_html_e( 'Action Scheduler is not available. Please install it to use this feature.', 'xt-facebook-events-pro' ); ?>
							</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Manual Cache Clear', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php if ( $post->ID && 'auto-draft' !== $post->post_status ) : ?>
							<button type="button" class="button button-secondary"
								id="xtfeprofeed-clear-cache-btn"
								data-feed-id="<?php echo esc_attr( $post->ID ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'xtfeprofeed_clear_cache_' . $post->ID ) ); ?>">
								<?php esc_html_e( 'Clear Cache Now', 'xt-facebook-events-pro' ); ?>
							</button>
							<span id="xtfeprofeed-clear-cache-msg" style="margin-left:10px;display:none;"></span>
							<?php
							$cache_key   = 'xtfeprofeed_' . $post->ID;
							$cached      = get_transient( $cache_key );
							$timeout_key = '_transient_timeout_' . $cache_key;
							$expires_at  = get_option( $timeout_key );
							if ( false !== $cached && $expires_at ) {
								$remaining = $expires_at - time();
								echo '<p class="description" style="margin-top:8px;">';
								if ( $remaining > 0 ) {
									echo '<span style="color:green;">&#9679; ' . sprintf( esc_html__( 'Cache active — expires in %d minutes.', 'xt-facebook-events-pro' ), ceil( $remaining / 60 ) ) . '</span>';
								} else {
									echo '<span style="color:orange;">&#9679; ' . esc_html__( 'Cache expired — will refresh on next page load.', 'xt-facebook-events-pro' ) . '</span>';
								}
								echo '</p>';
							} else {
								echo '<p class="description" style="margin-top:8px;"><span style="color:#aaa;">&#9679; ' . esc_html__( 'No cache — will fetch on first page load.', 'xt-facebook-events-pro' ) . '</span></p>';
							}
							?>
							<?php else : ?>
							<p class="description"><?php esc_html_e( 'Save the feed first to manage cache.', 'xt-facebook-events-pro' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Hard Cache (HQ Images)', 'xt-facebook-events-pro' ); ?></th>
						<td>
							<?php if ( $post->ID && 'auto-draft' !== $post->post_status ) : ?>
							<button type="button" class="button button-secondary"
								id="xtfeprofeed-clear-hard-cache-btn"
								data-feed-id="<?php echo esc_attr( $post->ID ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'xtfeprofeed_clear_hard_cache' ) ); ?>"
								style="color:#d63638;border-color:#d63638;">
								<?php esc_html_e( '🗑 Clear Hard Cache (Images)', 'xt-facebook-events-pro' ); ?>
							</button>
							<span id="xtfeprofeed-clear-hard-cache-msg" style="margin-left:10px;display:none;"></span>
							<?php
							$image_count = XTFEPRO_Feed_DB::instance()->get_image_count();
							echo '<p class="description" style="margin-top:8px;">';
							if ( $image_count > 0 ) {
								echo '<span style="color:#2271b1;">&#9679; ' . sprintf(
									esc_html__( '%d HQ images cached. Auto-cleans weekly.', 'xt-facebook-events-pro' ),
									$image_count
								) . '</span>';
							} else {
								echo '<span style="color:#aaa;">&#9679; ' . esc_html__( 'No HQ images cached yet.', 'xt-facebook-events-pro' ) . '</span>';
							}
							echo '</p>';
							?>
							<p class="description" style="margin-top:4px;font-style:italic;color:#666;">
								<?php esc_html_e( 'Clears all HQ event images. They will be re-fetched automatically on next page load.', 'xt-facebook-events-pro' ); ?>
							</p>
							<?php else : ?>
							<p class="description"><?php esc_html_e( 'Save the feed first.', 'xt-facebook-events-pro' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><label for="xtfeprofeed_custom_css"><?php esc_html_e( 'Custom CSS', 'xt-facebook-events-pro' ); ?></label></th>
						<td>
							<textarea id="xtfeprofeed_custom_css" name="_xtfeprofeed_custom_css"
								rows="8" class="large-text code"
								placeholder="/* Add custom CSS for this feed only */"><?php echo esc_textarea( $meta['custom_css'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Scoped to #xtfeprofeed-feed-' . $post->ID . '. Will not affect other feeds.', 'xt-facebook-events-pro' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

		</div><!-- .xtfeprofeed-tabs -->
		</div><!-- .xtfeprofeed-builder-left -->

		<div class="xtfeprofeed-builder-right">
			<div class="xtfeprofeed-preview-panel">
				<div class="xtfeprofeed-preview-header">
					<h3><?php esc_html_e( 'Live Preview', 'xt-facebook-events-pro' ); ?></h3>
					<span class="xtfeprofeed-preview-loading" style="display:none;"><?php esc_html_e( 'Updating...', 'xt-facebook-events-pro' ); ?></span>
				</div>
				<div class="xtfeprofeed-preview-body">
					<div id="xtfeprofeed-preview-container">
						<div class="xtfeprofeed-preview-placeholder">
							<p><?php esc_html_e( 'Select a source and display options to see a live preview.', 'xt-facebook-events-pro' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		</div><!-- .xtfeprofeed-builder-layout -->
		<?php
	}

	// -------------------------------------------------------
	// Save meta
	// -------------------------------------------------------

	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['xtfeprofeed_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['xtfeprofeed_nonce'] ) ), 'xtfeprofeed_save_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$text_fields = array(
			'_xtfeprofeed_source_type', '_xtfeprofeed_page_id', '_xtfeprofeed_event_ids', '_xtfeprofeed_ical_url',
			'_xtfeprofeed_time_filter', '_xtfeprofeed_start_date', '_xtfeprofeed_end_date',
			'_xtfeprofeed_layout', '_xtfeprofeed_pagination_type',
			'_xtfeprofeed_register_label',
		);

		$int_fields = array( '_xtfeprofeed_per_page', '_xtfeprofeed_columns' );

		$checkbox_fields = array(
			'_xtfeprofeed_show_image', '_xtfeprofeed_show_date', '_xtfeprofeed_show_venue',
			'_xtfeprofeed_show_organizer', '_xtfeprofeed_show_price',
			'_xtfeprofeed_show_ticket_btn', '_xtfeprofeed_hide_online', '_xtfeprofeed_auto_refresh',
		);

		foreach ( $text_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				if ( '_xtfeprofeed_time_filter' === $field && 'past' === $value ) {
					$value = 'current_future';
				}
				if ( '_xtfeprofeed_source_type' === $field ) {
					$allowed_sources = apply_filters( 'xtfeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) );
					if ( ! in_array( $value, $allowed_sources, true ) ) {
						$value = 'event_ids';
					}
				}
				if ( '_xtfeprofeed_layout' === $field ) {
					$allowed_layouts = apply_filters( 'xtfeprofeed_allowed_layouts', array( 'card-grid', 'list' ) );
					if ( ! in_array( $value, $allowed_layouts, true ) ) {
						$value = 'card-grid';
					}
				}
				update_post_meta( $post_id, $field, $value );
			}
		}
		foreach ( $int_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, absint( $_POST[ $field ] ) );
			}
		}
		foreach ( $checkbox_fields as $field ) {
			update_post_meta( $post_id, $field, isset( $_POST[ $field ] ) ? '1' : '0' );
		}

		if ( isset( $_POST['_xtfeprofeed_custom_css'] ) ) {
			update_post_meta( $post_id, '_xtfeprofeed_custom_css', wp_strip_all_tags( wp_unslash( $_POST['_xtfeprofeed_custom_css'] ) ) );
		}

		if ( isset( $_POST['_xtfeprofeed_cache_duration'] ) ) {
			$cache_val = sanitize_text_field( wp_unslash( $_POST['_xtfeprofeed_cache_duration'] ) );
			if ( 'custom' === $cache_val && isset( $_POST['_xtfeprofeed_cache_duration_custom'] ) ) {
				$custom_hours = max( 1, absint( $_POST['_xtfeprofeed_cache_duration_custom'] ) );
				$cache_val    = $custom_hours * 60;
			} else {
				$cache_val    = absint( $cache_val ) ?: 1440;
			}
			update_post_meta( $post_id, '_xtfeprofeed_cache_duration', $cache_val );
		}

		XTFEPRO_Feed_API::instance()->clear_cache( $post_id );
		do_action( 'xtfeprofeed_settings_saved', $post_id );
	}

	public function handle_clear_cache_row_action() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'xt-facebook-events-pro' ) );
		$feed_id = absint( $_GET['feed_id'] ?? 0 );
		$nonce   = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'xtfeprofeed_clear_cache_' . $feed_id ) ) wp_die( esc_html__( 'Security check failed.', 'xt-facebook-events-pro' ) );
		XTFEPRO_Feed_API::instance()->clear_cache( $feed_id );
		wp_redirect( admin_url( 'edit.php?post_type=' . XTFEPRO_FEED_CPT . '&xtfeprofeed_cache_cleared=1' ) );
		exit;
	}

	// -------------------------------------------------------
	// Render helpers
	// -------------------------------------------------------

	private function render_select( $name, $current, $options ) {
		echo '<select name="' . esc_attr( $name ) . '">';
		foreach ( $options as $val => $label ) {
			echo '<option value="' . esc_attr( $val ) . '" ' . selected( $current, $val, false ) . '>';
			echo esc_html( $label );
			echo '</option>';
		}
		echo '</select>';
	}

	private function render_radio_row( $name, $current, $values, $suffix = '' ) {
		foreach ( $values as $val ) {
			echo '<label style="margin-right:12px;">';
			echo '<input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" ' . checked( (string) $current, (string) $val, false ) . ' /> ';
			echo esc_html( $val );
			if ( $suffix ) echo ' ' . esc_html( $suffix );
			echo '</label>';
		}
	}
}
