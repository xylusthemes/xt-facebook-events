<?php
/**
 * XT Facebook Events Pro Live Feed – Modern Builder UI Shell
 *
 * @package XT_Facebook_Events_Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class XTFEPRO_Feed_Builder_UI {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'edit_form_after_title', array( $this, 'render_builder_shell' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_builder_assets' ) );
		add_action( 'admin_body_class', array( $this, 'add_body_class' ) );
	}

	public function add_body_class( $classes ) {
		$screen = get_current_screen();
		if ( $screen && XTFEPRO_FEED_CPT === $screen->post_type && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			$classes .= ' xtfepro-builder-active';
		}
		return $classes;
	}

	public function enqueue_builder_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || XTFEPRO_FEED_CPT !== $screen->post_type ) return;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;

		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( 'imagesloaded' );

		wp_enqueue_style(
			'xtfeprofeed-builder-ui',
			XTFEPRO_FEED_URL . 'assets/builder-ui.css',
			array( 'dashicons' ),
			XTFEPRO_FEED_VERSION
		);
		wp_enqueue_script(
			'xtfeprofeed-builder-ui',
			XTFEPRO_FEED_URL . 'assets/builder-ui.js',
			array( 'jquery' ),
			XTFEPRO_FEED_VERSION,
			true
		);
		wp_localize_script( 'xtfeprofeed-builder-ui', 'xtfeproBuilderUI', array(
			'backUrl'   => admin_url( 'edit.php?post_type=' . XTFEPRO_FEED_CPT ),
			'isNewPost' => ( 'post-new.php' === $hook ),
			'i18n'      => array(
				'back'            => __( 'Back to Facebook Widgets', 'xt-facebook-events-pro' ),
				'save'            => __( 'Save Widget', 'xt-facebook-events-pro' ),
				'saving'          => __( 'Saving…', 'xt-facebook-events-pro' ),
				'next'            => __( 'Continue', 'xt-facebook-events-pro' ),
				'prev'            => __( 'Previous', 'xt-facebook-events-pro' ),
				'step_of'         => __( 'Step %1$s of %2$s', 'xt-facebook-events-pro' ),
				'titlePlh'        => __( 'Enter widget name…', 'xt-facebook-events-pro' ),
				'shortcode_label' => __( 'Your Shortcode', 'xt-facebook-events-pro' ),
				'copied'          => __( 'Copied!', 'xt-facebook-events-pro' ),
				'reqTitle'        => __( 'Widget name is required.', 'xt-facebook-events-pro' ),
				'reqPageId'       => __( 'Facebook Page ID or Slug is required.', 'xt-facebook-events-pro' ),
				'reqGroupId'      => __( 'Facebook Group URL or ID is required.', 'xt-facebook-events-pro' ),
				'reqEventIds'     => __( 'At least one Event ID is required.', 'xt-facebook-events-pro' ),
				'reqIcalUrl'      => __( 'iCal URL is required.', 'xt-facebook-events-pro' ),
			),
		) );
	}

	public function render_builder_shell( $post ) {
		if ( XTFEPRO_FEED_CPT !== $post->post_type ) return;

		$steps = array(
			array( 'id' => 'source',   'label' => __( 'Source', 'xt-facebook-events-pro' ),   'icon' => 'dashicons-admin-site',    'desc' => __( 'Choose where to pull Facebook events from', 'xt-facebook-events-pro' ) ),
			array( 'id' => 'display',  'label' => __( 'Display', 'xt-facebook-events-pro' ),  'icon' => 'dashicons-layout',        'desc' => __( 'Customize layout and visible fields', 'xt-facebook-events-pro' ) ),
			array( 'id' => 'tickets',  'label' => __( 'Buttons', 'xt-facebook-events-pro' ),  'icon' => 'dashicons-tickets-alt',   'desc' => __( 'Configure event links and button labels', 'xt-facebook-events-pro' ) ),
			array( 'id' => 'filters',  'label' => __( 'Filters', 'xt-facebook-events-pro' ),  'icon' => 'dashicons-filter',        'desc' => __( 'Narrow down which events to show', 'xt-facebook-events-pro' ) ),
			array( 'id' => 'settings', 'label' => __( 'Settings', 'xt-facebook-events-pro' ), 'icon' => 'dashicons-admin-generic', 'desc' => __( 'Cache settings and custom CSS', 'xt-facebook-events-pro' ) ),
		);

		$total_steps = count( $steps );
		$shortcode   = ( $post->ID && 'auto-draft' !== $post->post_status )
			? '[xtfepro_live_feed id="' . $post->ID . '"]'
			: '';
		?>

		<div id="xtfepro-builder" class="xtfepro-builder">

			<!-- Global Warning Notice for Restricted Pages -->
			<div id="xtfepro-builder-global-warning" style="display: none; background: #fff8e5; border-left: 4px solid #f0b849; padding: 12px 15px; margin-bottom: 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<p style="margin: 0; font-size: 13px; color: #3c434a;">
					<strong><?php esc_html_e( 'Warning:', 'xt-facebook-events-pro' ); ?></strong>
					<span class="xtfepro-warning-text"></span>
				</p>
			</div>

			<!-- Top Bar -->
			<div class="xtfepro-builder__topbar">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . XTFEPRO_FEED_CPT ) ); ?>" class="xtfepro-builder__back">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
					<?php esc_html_e( 'Back to Facebook Widgets', 'xt-facebook-events-pro' ); ?>
				</a>

				<?php if ( $shortcode ) : ?>
				<div class="xtfepro-builder__shortcode-bar">
					<span class="xtfepro-builder__shortcode-label">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
						<?php esc_html_e( 'Shortcode:', 'xt-facebook-events-pro' ); ?>
					</span>
					<code class="xtfepro-builder__shortcode-code" id="xtfepro-builder-shortcode"><?php echo esc_html( $shortcode ); ?></code>
					<button type="button" class="xtfepro-builder__shortcode-copy" id="xtfepro-builder-copy-sc" title="<?php esc_attr_e( 'Copy shortcode', 'xt-facebook-events-pro' ); ?>">
						<span class="xtfepro-copy-icon-wrap" style="display:flex;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></span>
					</button>
				</div>
				<?php endif; ?>
			</div>

			<!-- Progress Stepper -->
			<div class="xtfepro-builder__stepper">
				<?php foreach ( $steps as $i => $step ) : ?>
				<div class="xtfepro-builder__step-indicator <?php echo 0 === $i ? 'is-active' : ''; ?>" data-step="<?php echo esc_attr( $i ); ?>">
					<div class="xtfepro-builder__step-circle">
						<span class="xtfepro-builder__step-number"><?php echo esc_html( $i + 1 ); ?></span>
						<span class="xtfepro-builder__step-check dashicons dashicons-yes"></span>
					</div>
					<span class="xtfepro-builder__step-label"><?php echo esc_html( $step['label'] ); ?></span>
					<?php if ( $i < $total_steps - 1 ) : ?>
					<div class="xtfepro-builder__step-line"></div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- Workspace -->
			<div class="xtfepro-builder__workspace">
				<div class="xtfepro-builder__panels">
					<?php foreach ( $steps as $i => $step ) : ?>
					<div class="xtfepro-builder__panel <?php echo 0 === $i ? 'is-active' : ''; ?>"
					     id="xtfepro-panel-<?php echo esc_attr( $step['id'] ); ?>"
					     data-step="<?php echo esc_attr( $i ); ?>">
						<div class="xtfepro-builder__panel-header">
							<span class="dashicons <?php echo esc_attr( $step['icon'] ); ?> xtfepro-builder__panel-icon"></span>
							<h2 class="xtfepro-builder__panel-title"><?php echo esc_html( $step['label'] ); ?></h2>
							<span class="xtfepro-builder__panel-hint" title="<?php echo esc_attr( $step['desc'] ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</div>
						<?php if ( 0 === $i ) : ?>
						<div class="xtfepro-builder__title-slot" id="xtfepro-builder-title-slot"></div>
						<?php endif; ?>
						<div class="xtfepro-builder__panel-body" id="xtfepro-panel-body-<?php echo esc_attr( $step['id'] ); ?>">
							<!-- JS moves metabox fields here -->
						</div>
					</div>
					<?php endforeach; ?>
				</div>

				<!-- Live Preview Sidebar -->
				<div class="xtfepro-builder__preview-sidebar">
					<div class="xtfepro-builder__preview-card">
						<div class="xtfepro-builder__preview-card-header">
							<div class="xtfepro-builder__preview-title-wrap" style="display:flex;align-items:center;gap:10px;">
								<h3><?php esc_html_e( 'Live Preview', 'xt-facebook-events-pro' ); ?></h3>
								<span class="xtfeprofeed-preview-loading" style="display:none;"><?php esc_html_e( 'Updating...', 'xt-facebook-events-pro' ); ?></span>
							</div>
							<button type="button" class="xtfepro-builder__full-preview-btn" id="xtfepro-builder-toggle-full-preview" style="display:inline-flex;align-items:center;gap:6px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:#334155;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.02);transition:all 0.2s ease;">
								<span class="xtfepro-preview-icon-wrap" style="display:flex;align-items:center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg></span>
								<span class="btn-text"><?php esc_html_e( 'Full Preview', 'xt-facebook-events-pro' ); ?></span>
							</button>
						</div>
						<div class="xtfepro-builder__preview-card-body">
							<div id="xtfepro-builder-preview-container">
								<div class="xtfepro-builder__preview-placeholder">
									<p><?php esc_html_e( 'Interactive preview shows up here', 'xt-facebook-events-pro' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer Nav -->
			<div class="xtfepro-builder__footer">
				<div class="xtfepro-builder__footer-inner">
					<button type="button" class="xtfepro-builder__btn xtfepro-builder__btn--prev" id="xtfepro-builder-prev" style="visibility:hidden;">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<?php esc_html_e( 'Previous', 'xt-facebook-events-pro' ); ?>
					</button>
					<div class="xtfepro-builder__step-counter" id="xtfepro-builder-counter">
						<?php printf(
							esc_html__( 'Step %1$s of %2$s', 'xt-facebook-events-pro' ),
							'<strong>1</strong>',
							'<strong>' . esc_html( $total_steps ) . '</strong>'
						); ?>
					</div>
					<div class="xtfepro-builder__footer-actions">
						<button type="button" class="xtfepro-builder__btn xtfepro-builder__btn--next" id="xtfepro-builder-next">
							<?php esc_html_e( 'Continue', 'xt-facebook-events-pro' ); ?>
							<span class="dashicons dashicons-arrow-right-alt2"></span>
						</button>
						<button type="button" class="xtfepro-builder__btn xtfepro-builder__btn--save" id="xtfepro-builder-save">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Save Widget', 'xt-facebook-events-pro' ); ?>
						</button>
					</div>
				</div>
			</div>

		</div><!-- #xtfepro-builder -->
		<?php
	}
}
