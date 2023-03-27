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
				<div class="xtfe-support-features-card">
					<div class="xtfe-support-features-img">
						<img class="xtfe-support-features-icon" src="<?php echo XTFE_PLUGIN_URL.'assets/images/document.svg'; ?>" alt="<?php esc_attr_e( 'Looking for Something?', 'xt-facebook-events' ); ?>">
					</div>
					<div class="xtfe-support-features-text">
						<h3 class="xtfe-support-features-title"><?php esc_attr_e( 'Looking for Something?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'We have documentation of how to Display Facebook Events.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://docs.xylusthemes.com/docs/facebookevents/"><?php esc_attr_e( 'Plugin Documentation', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-features-card">
					<div class="xtfe-support-features-img">
						<img class="xtfe-support-features-icon" src="<?php echo XTFE_PLUGIN_URL.'assets/images/call-center.svg'; ?>" alt="<?php esc_attr_e( 'Need Any Assistance?', 'xt-facebook-events' ); ?>">
					</div>
					<div class="xtfe-support-features-text">
						<h3 class="xtfe-support-features-title"><?php esc_attr_e( 'Need Any Assistance?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to Help you out.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/"><?php esc_attr_e( 'Contact Support', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-features-card">
					<div class="xtfe-support-features-img">
						<img class="xtfe-support-features-icon"  src="<?php echo XTFE_PLUGIN_URL.'assets/images/bug.svg'; ?>" alt="<?php esc_attr_e( 'Found Any Bugs?', 'xt-facebook-events' ); ?>" />
					</div>
					<div class="xtfe-support-features-text">
						<h3 class="xtfe-support-features-title"><?php esc_attr_e( 'Found Any Bugs?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Report any Bug that you Discovered, Get Instant Solutions.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/xt-facebook-events"><?php esc_attr_e( 'Report to GitHub', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-features-card">
					<div class="xtfe-support-features-img">
						<img class="xtfe-support-features-icon" src="<?php echo XTFE_PLUGIN_URL.'assets/images/tools.svg'; ?>" alt="<?php esc_attr_e( 'Require Customization?', 'xt-facebook-events' ); ?>" />
					</div>
					<div class="xtfe-support-features-text">
						<h3 class="xtfe-support-features-title"><?php esc_attr_e( 'Require Customization?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'We would Love to hear your Integration and Customization Ideas.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/what-we-do/"><?php esc_attr_e( 'Connect Our Service', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="xtfe-support-features-card">
					<div class="xtfe-support-features-img">
						<img class="xtfe-support-features-icon" src="<?php echo XTFE_PLUGIN_URL.'assets/images/like.svg'; ?>" alt="<?php esc_attr_e( 'Like The Plugin?', 'xt-facebook-events' ); ?>" />
					</div>
					<div class="xtfe-support-features-text">
						<h3 class="xtfe-support-features-title"><?php esc_attr_e( 'Like The Plugin?', 'xt-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Your Review is very important to us as it helps us to grow more.', 'xt-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/xt-facebook-events/reviews/?rate=5#new-post"><?php esc_attr_e( 'Review Us on WP.org', 'xt-facebook-events' ); ?></a>
					</div>
				</div>
			</div>
        </div>

        <?php 
        $plugins = array();
        $plugin_list = $xtfe_events->admin->get_xyuls_themes_plugins();
        if( !empty( $plugin_list ) ){
            foreach ($plugin_list as $key => $value) {
                $plugins[] = $xtfe_events->admin->get_wporg_plugin( $key );
            }
        }
        ?>
        <h3 class="setting_bar"><?php _e( 'Plugins you should try', 'xt-facebook-events' ); ?></h3>
		<div id="xtfe-addons-list">
			<?php
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $plugin ) {
				
					$plugin_activation = is_plugin_active( $plugin->slug.'/'. $plugin->slug.'.php' );
					$plugin_not_active = ABSPATH . 'wp-content/plugins/'.$plugin->slug.'/';
					$buy_now = "<a class='xtfe-status-download button-primary' target='_blank' href='".$plugin->homepage."'>Buy Now</a>";                  
				?>
					<div class="xtfe-addon-container">
						<div class="xtfe-addon-item">
							<div class="xtfe-details xtfe-clear" style="height: 165px;">
								<img src="<?php if( isset( $plugin->icons['2x'] ) ){ echo $plugin->icons['2x']; }else{ echo 'https://secure.gravatar.com/avatar/4363de1418924e303221153dd70484ab?s=96&d=monsterid&r=g'; } ?>">
								<h5 class="xtfe-addon-name"><?php echo $plugin->name; ?></h5>
								<p class="xtfe-addon-desc"><?php if( isset( $plugin->short_description ) ){ echo $plugin->short_description; }else{ echo ''; } ?></p>
							</div>
							<div class="actions xtfe-clear">
								<div class="xtfe-status">
									<strong>
									<?php _e( 'Active Installs: ', 'xt-facebook-events' ); ?><span class="xtfe-status-label xtfe-status-download"><?php echo $plugin->active_installs; ?>+</span></strong>
								</div>
								<div class="xtfe-action-button">
									
									<?php add_thickbox(); ?>
									<?php if( $plugin_activation == true ){ ?>
										<a class="xtfe-status-download button-secondary" disabled ><?php _e( 'Actived', 'xt-facebook-events' ); ?> </a>
										<?php echo $buy_now; ?>
									<?php }elseif( is_dir( $plugin_not_active ) && $plugin_activation == false ){ ?>
										<a class="xtfe-status-download button-secondary"  href="<?php echo admin_url( 'plugins.php' ); ?>" ><?php _e( 'Activate', 'xt-facebook-events' ); ?></a>
										<?php echo $buy_now; ?>
									<?php }else{ ?>
										<a class="xtfe-status-download button button-secondary thickbox" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin->slug . '&TB_iframe=true&width=772&height=600' ); ?>" >
									<?php _e( 'Install Plugin', 'xt-facebook-events' ); ?></a>
									<?php echo $buy_now; } ?>
								</div>
							</div>
						</div>
					</div>
				<?php
				}
			}
			?>
			</div>
		<div style="clear: both;">
    </div>

</div>