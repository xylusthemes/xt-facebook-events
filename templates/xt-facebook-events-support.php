<?php
// If this file is called directly, abort.
// Icon Credit: Icon made by Freepik and Vectors Market from www.flaticon.com
if ( ! defined( 'ABSPATH' ) ) exit;
global $xtfe_events;
$open_source_support_url = 'https://wordpress.org/support/plugin/xt-facebook-events/';
$support_url = 'https://xylusthemes.com/support/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin';

$review_url = 'https://wordpress.org/support/plugin/xt-facebook-events/reviews/?rate=5#new-post';
$facebook_url = 'https://www.facebook.com/xylusinfo/';
$twitter_url = 'https://twitter.com/XylusThemes/';
?>
<div class="xtfe_container">
    <div class="xtfe_row">
        <div class="wpea-column support_well">
            <h3 class="setting_bar"><?php esc_attr_e( 'Getting Support', 'xt-facebook-events' ); ?></h3>
            <div class="xtfe-support-features">
				<div class="xtfe-support-featurxtfe-card">
					<div class="xtfe-support-featurxtfe-img">
						<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="xtfe-support-featurxtfe-icon" src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/document.svg' ); ?>" alt="<?php esc_attr_e( 'Looking for Something?', 'xt-facebook-events' ); ?>">
					</div>
					<div class="xtfe-support-featurxtfe-text">
						<h3 class="xtfe-support-featurxtfe-title"><?php esc_attr_e( 'Looking for Something?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'We have documentation of how to Display Facebook Events.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://docs.xylusthemes.com/docs/facebookevents/"><?php esc_attr_e( 'Plugin Documentation', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-featurxtfe-card">
					<div class="xtfe-support-featurxtfe-img">
						<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="xtfe-support-featurxtfe-icon" src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/call-center.svg' ); ?>" alt="<?php esc_attr_e( 'Need Any Assistance?', 'xt-facebook-events' ); ?>">
					</div>
					<div class="xtfe-support-featurxtfe-text">
						<h3 class="xtfe-support-featurxtfe-title"><?php esc_attr_e( 'Need Any Assistance?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to Help you out.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/"><?php esc_attr_e( 'Contact Support', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-featurxtfe-card">
					<div class="xtfe-support-featurxtfe-img">
						<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="xtfe-support-featurxtfe-icon"  src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/bug.svg' ); ?>" alt="<?php esc_attr_e( 'Found Any Bugs?', 'xt-facebook-events' ); ?>" />
					</div>
					<div class="xtfe-support-featurxtfe-text">
						<h3 class="xtfe-support-featurxtfe-title"><?php esc_attr_e( 'Found Any Bugs?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Report any Bug that you Discovered, Get Instant Solutions.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/xt-facebook-events"><?php esc_attr_e( 'Report to GitHub', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-featurxtfe-card">
					<div class="xtfe-support-featurxtfe-img">
						<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="xtfe-support-featurxtfe-icon" src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/tools.svg' ); ?>" alt="<?php esc_attr_e( 'Require Customization?', 'xt-facebook-events' ); ?>" />
					</div>
					<div class="xtfe-support-featurxtfe-text">
						<h3 class="xtfe-support-featurxtfe-title"><?php esc_attr_e( 'Require Customization?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'We would Love to hear your Integration and Customization Ideas.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/what-we-do/"><?php esc_attr_e( 'Connect Our Service', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-featurxtfe-card">
					<div class="xtfe-support-featurxtfe-img">
						<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="xtfe-support-featurxtfe-icon" src="<?php echo esc_url( 	XTFE_PLUGIN_URL.'assets/images/like.svg' ); ?>" alt="<?php esc_attr_e( 'Like The Plugin?', 'xt-facebook-events' ); ?>" />
					</div>
					<div class="xtfe-support-featurxtfe-text">
						<h3 class="xtfe-support-featurxtfe-title"><?php esc_attr_e( 'Like The Plugin?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Your Review is very important to us as it helps us to grow more.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/xt-facebook-events/reviews/?rate=5#new-post"><?php esc_attr_e( 'Review Us on WP.org', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
			</div>
        </div>

        <?php 
			$plugin_list = array();
			$plugin_list = $xtfe_events->admin->xtfe_get_xyuls_themes_plugins();
		?>
		<div class="" style="margin-top: 20px;">
			<h3 class="setting_bar"><?php esc_html_e( 'Plugins you should try','xt-facebook-events' ); ?></h3>
			<div class="xtfe-about-us-plugins">
				<div class="xtfe-support-features2">
				
					<?php 
						if( !empty( $plugin_list ) ){
							foreach ( $plugin_list as $key => $plugin ) {

								$plugin_slug = ucwords( str_replace( '-', ' ', $key ) );
								$plugin_name =  $plugin['plugin_name'];
								$plugin_description =  $plugin['description'];
								if( $key == 'wp-event-aggregator' ){
									$plugin_icon = 'https://ps.w.org/'.$key.'/assets/icon-256x256.jpg';
								} else {
									$plugin_icon = 'https://ps.w.org/'.$key.'/assets/icon-256x256.png';
								}

								// Check if the plugin is installed
								$plugin_installed = false;
								$plugin_active = false;
								include_once(ABSPATH . 'wp-admin/includes/plugin.php');
								$all_plugins = get_plugins();
								$plugin_path = $key . '/' . $key . '.php';

								if ( isset( $all_plugins[$plugin_path] ) ) {
									$plugin_installed = true;
									$plugin_active = is_plugin_active( $plugin_path );
								}

								// Determine the status text
								$status_text = 'Not Installed';
								if ( $plugin_installed ) {
									$status_text = $plugin_active ? 'Active' : 'Installed (Inactive)';
								}
								
								?>
								<div class="xtfe-support-featurxtfe-card2 xtfe-plugin">
									<div class="xtfe-plugin-main">
										<div>
											<img alt="<?php esc_attr( $plugin_slug . ' Image' ); ?>" src="<?php echo esc_url( $plugin_icon ); ?>">
										</div>
										<div>
											<div class="xtfe-main-name"><?php echo esc_attr( $plugin_slug ); ?></div>
											<div><?php echo esc_attr( $plugin_description ); ?></div>
										</div>
									</div>
									<div class="xtfe-plugin-footer">
										<div class="xtfe-footer-status">
											<div class="xtfe-footer-status-label"><?php esc_html_e( 'Status : ', 'xt-facebook-events' ); ?></div>
											<div class="xtfe-footer-status xtfe-footer-status-<?php echo esc_attr( strtolower( str_replace(' ', '-', $status_text ) ) ); ?>">
												<span <?php echo ( $status_text == 'Active' ) ? 'style="color:green;"' : ''; ?>>
													<?php echo esc_attr( $status_text ); ?>
												</span>
											</div>
										</div>
										<div class="xtfe-footer-action">
											<?php if ( !$plugin_installed ): ?>
												<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" type="button" class="button button-primary"><?php esc_attr_e( 'Install Free Plugin', 'xt-facebook-events' ); ?></a>
											<?php elseif ( !$plugin_active ): ?>
												<?php 
													$activate_nonce = wp_create_nonce('activate_plugin_' . $plugin_slug); 
													$activation_url = add_query_arg(array( 'action' => 'activate_plugin', 'plugin_slug' => $plugin_slug, 'nonce' => $activate_nonce, ), admin_url('admin.php?page=delete_all_actions&tab=by_support_help'));
												?>
												<a href="<?php echo esc_url( admin_url( 'plugins.php?s='. $plugin_name ) ); ?>" class="button button-primary"><?php esc_attr_e( 'Activate Plugin', 'xt-facebook-events' ); ?></a>
											<?php endif; ?>
										</div>
									</div>
								</div>
								<?php
							}
						}
					?>
				</div>
			</div>
			<div style="clear: both;">
		</div>
</div>