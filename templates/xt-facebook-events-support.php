<?php
// If this file is called directly, abort.
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
        	<h3><?php esc_attr_e( 'Getting Support', 'xt-facebook-events' ); ?></h3>
            <p><?php _e( 'Thanks you for using Import Facebook Events, We are sincerely appreciate your support and we’re excited to see you using our plugins.','xt-facebook-events' ); ?> </p>
            <p><?php _e( 'Our support team is always around to help you.','xt-facebook-events' ); ?></p>
                
            <p><strong><?php _e( 'Looking for free support?','xt-facebook-events' ); ?></strong></p>
            <a class="button button-secondary" href="<?php echo $open_source_support_url; ?>" target="_blank" >
                <?php _e( 'Open-source forum on WordPress.org','xt-facebook-events' ); ?>
            </a>

            <p><strong><?php _e( 'Looking for more immediate support?','xt-facebook-events' ); ?></strong></p>
            <p><?php _e( 'We offer premium support on our website with the purchase of our premium plugins.','xt-facebook-events' ); ?>
            </p>
            
            <a class="button button-primary" href="<?php echo $support_url; ?>" target="_blank" >
                <?php _e( 'Contact us directly (Premium Support)','xt-facebook-events' ); ?>
            </a>

            <p><strong><?php _e( 'Enjoying Import Facebook Events or have feedback?','xt-facebook-events' ); ?></strong></p>
            <a class="button button-secondary" href="<?php echo $review_url; ?>" target="_blank" ><?php _e( 'Leave us a review','xt-facebook-events' ); ?></a> 
            <a class="button button-secondary" href="<?php echo $twitter_url; ?>" target="_blank" ><?php _e( 'Follow us on Twitter','xt-facebook-events' ); ?></a> 
            <a class="button button-secondary" href="<?php echo $facebook_url; ?>" target="_blank" ><?php _e( 'Like us on Facebook','xt-facebook-events' ); ?></a>
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
								<img src="<?php echo $plugin->icons['2x']; ?>">
								<h5 class="xtfe-addon-name"><?php echo $plugin->name; ?></h5>
								<p class="xtfe-addon-desc"><?php echo $plugin->short_description; ?></p>
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