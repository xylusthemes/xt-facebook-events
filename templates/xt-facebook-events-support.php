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
        <div class="" style="margin-top: 20px;">
            <h3 class="setting_bar"><?php _e( 'Plugins you should try','xt-facebook-events' ); ?></h3>
            <?php 
            if( !empty( $plugins ) ){
                foreach ($plugins as $plugin ) {
                    ?>
                    <div class="plugin_box">
                        <?php if( $plugin->banners['low'] != '' ){ ?>
                            <img src="<?php echo $plugin->banners['low']; ?>" class="plugin_img" title="<?php echo $plugin->name; ?>">
                        <?php } ?>                    
                        <div class="plugin_content">
                            <h3><?php echo $plugin->name; ?></h3>

                            <?php wp_star_rating( array(
                            'rating' => $plugin->rating,
                            'type'   => 'percent',
                            'number' => $plugin->num_ratings,
                            ) );?>

                            <?php if( $plugin->version != '' ){ ?>
                                <p><strong><?php _e( 'Version:','xt-facebook-events' ); ?> </strong><?php echo $plugin->version; ?></p>
                            <?php } ?>

                            <?php if( $plugin->requires != '' ){ ?>
                                <p><strong><?php _e( 'Requires:','xt-facebook-events' ); ?> </strong> <?php _e( 'WordPress ','xt-facebook-events' ); echo $plugin->requires; ?>+</p>
                            <?php } ?>

                            <?php if( $plugin->active_installs != '' ){ ?>
                                <p><strong><?php _e( 'Active Installs:','xt-facebook-events' ); ?> </strong><?php echo $plugin->active_installs; ?>+</p>
                            <?php } ?>

                            <?php //print_r( $plugin ); ?>
                            <a class="button button-secondary" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin='. $plugin->slug.'&TB_iframe=1&width=772&height=600'); ?>" target="_blank">
                                <?php _e( 'Install Now','xt-facebook-events' ); ?>
                            </a>
                            <a class="button button-primary" href="<?php echo $plugin->homepage . '?utm_source=crosssell&utm_medium=web&utm_content=supportpage&utm_campaign=freeplugin'; ?>" target="_blank">
                                <?php _e( 'Buy Now','xt-facebook-events' ); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
            <div style="clear: both;">
        </div>
    </div>

</div>