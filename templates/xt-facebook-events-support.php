<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $xtfe_events;
?>
<div class="form-table">
    <!-- Help & Resources -->
    <div class="xtfe-card mt-2" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0; margin-bottom: 24px;">
        <div class="header" style="background-color: #f8fafc; border-bottom-color: #e2e8f0;">
            <div class="text">
                <div class="header-icon" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230f172a%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><circle cx=%2212%22 cy=%2212%22 r=%2210%22></circle><path d=%22M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3%22></path><line x1=%2212%22 y1=%2217%22 x2=%2212.01%22 y2=%2217%22></line></svg>');"></div>
                <div class="header-title">
                    <span style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php esc_html_e( 'Help & Resources', 'xt-facebook-events' ); ?></span>
                </div>
            </div>
        </div>
        <div class="content" style="padding: 28px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">

                <!-- Resource 1 -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='var(--xtfe-primary-color, #005AE0)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)';">
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px;">
                        <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/document.svg' ); ?>" style="width: 24px; height: 24px;" alt="">
                        </div>
                        <div>
                            <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_attr_e( 'Looking for Something?', 'xt-facebook-events' ); ?></h4>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_attr_e( 'We have documentation of how to Display Facebook Events.', 'xt-facebook-events' ); ?></p>
                        </div>
                    </div>
                    <a target="_blank" href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/facebookevents/' ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Plugin Documentation', 'xt-facebook-events' ); ?></a>
                </div>

                <!-- Resource 2 -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='var(--xtfe-primary-color, #005AE0)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)';">
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px;">
                        <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/call-center.svg' ); ?>" style="width: 24px; height: 24px;" alt="">
                        </div>
                        <div>
                            <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_attr_e( 'Need Any Assistance?', 'xt-facebook-events' ); ?></h4>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_attr_e( 'Our EXPERT Support Team is always ready to help you out.', 'xt-facebook-events' ); ?></p>
                        </div>
                    </div>
                    <a target="_blank" href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Contact Support', 'xt-facebook-events' ); ?></a>
                </div>

                <!-- Resource 3 -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='var(--xtfe-primary-color, #005AE0)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)';">
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px;">
                        <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/bug.svg' ); ?>" style="width: 24px; height: 24px;" alt="">
                        </div>
                        <div>
                            <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_attr_e( 'Found Any Bugs?', 'xt-facebook-events' ); ?></h4>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_attr_e( 'Report any Bug that you Discovered, and get Instant Solutions.', 'xt-facebook-events' ); ?></p>
                        </div>
                    </div>
                    <a target="_blank" href="<?php echo esc_url( 'https://github.com/xylusthemes/xt-facebook-events' ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Report to GitHub', 'xt-facebook-events' ); ?></a>
                </div>

                <!-- Resource 4 -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='var(--xtfe-primary-color, #005AE0)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)';">
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px;">
                        <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/tools.svg' ); ?>" style="width: 24px; height: 24px;" alt="">
                        </div>
                        <div>
                            <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_attr_e( 'Require Customization?', 'xt-facebook-events' ); ?></h4>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_attr_e( 'We would love to hear your Integration and Customization Ideas.', 'xt-facebook-events' ); ?></p>
                        </div>
                    </div>
                    <a target="_blank" href="<?php echo esc_url( 'https://xylusthemes.com/what-we-do/' ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Connect Our Service', 'xt-facebook-events' ); ?></a>
                </div>

                <!-- Resource 5 -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='var(--xtfe-primary-color, #005AE0)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.05)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)';">
                    <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px;">
                        <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <img src="<?php echo esc_url( XTFE_PLUGIN_URL.'assets/images/like.svg' ); ?>" style="width: 24px; height: 24px;" alt="">
                        </div>
                        <div>
                            <h4 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php esc_attr_e( 'Like The Plugin?', 'xt-facebook-events' ); ?></h4>
                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php esc_attr_e( 'Your Review is very important to us as it helps us to grow more.', 'xt-facebook-events' ); ?></p>
                        </div>
                    </div>
                    <a target="_blank" href="<?php echo esc_url( 'https://wordpress.org/support/plugin/xt-facebook-events/reviews/?rate=5#new-post' ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Review Us on WP.org', 'xt-facebook-events' ); ?></a>
                </div>

            </div>
        </div>
    </div>

    <!-- Recommended Plugins -->
    <?php 
    $xtfe_plugin_list = array();
    if ( isset( $xtfe_events->admin ) && method_exists( $xtfe_events->admin, 'xtfe_get_xyuls_themes_plugins' ) ) {
        $xtfe_plugin_list = $xtfe_events->admin->xtfe_get_xyuls_themes_plugins();
    }
    if ( ! empty( $xtfe_plugin_list ) ) :
    ?>
    <div class="xtfe-card mt-2" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0; margin-bottom: 24px;">
        <div class="header" style="background-color: #f8fafc; border-bottom-color: #e2e8f0;">
            <div class="text">
                <div class="header-icon" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230f172a%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><path d=%22M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z%22></path><line x1=%227%22 y1=%227%22 x2=%227.01%22 y2=%227%22></line></svg>');"></div>
                <div class="header-title">
                    <span style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php esc_html_e( 'Plugins you should try', 'xt-facebook-events' ); ?></span>
                </div>
            </div>
        </div>
        <div class="content" style="padding: 28px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">

                <?php 
                foreach ( $xtfe_plugin_list as $xtfe_key => $xtfe_plugin ) {
                    $xtfe_plugin_slug = ucwords( str_replace( '-', ' ', $xtfe_key ) );
                    $xtfe_plugin_name =  $xtfe_plugin['plugin_name'];
                    $xtfe_plugin_description =  $xtfe_plugin['description'];
                    if ( $xtfe_key == 'wp-event-aggregator' ) {
                        $xtfe_plugin_icon = 'https://ps.w.org/'.$xtfe_key.'/assets/icon-256x256.jpg';
                    } elseif ( $xtfe_key == 'xt-feed-for-linkedin' ) {
                        $xtfe_plugin_icon = 'https://ps.w.org/'.$xtfe_key.'/assets/icon-256x256.gif';
                    } else {
                        $xtfe_plugin_icon = 'https://ps.w.org/'.$xtfe_key.'/assets/icon-256x256.png';
                    }

                    // Check if the plugin is installed
                    $xtfe_plugin_installed = false;
                    $xtfe_plugin_active = false;
                    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                    $xtfe_all_plugins = get_plugins();
                    $xtfe_plugin_path = $xtfe_key . '/' . $xtfe_key . '.php';

                    if ( isset( $xtfe_all_plugins[$xtfe_plugin_path] ) ) {
                        $xtfe_plugin_installed = true;
                        $xtfe_plugin_active = is_plugin_active( $xtfe_plugin_path );
                    }

                    // Determine the status text
                    $xtfe_status_text = 'Not Installed';
                    if ( $xtfe_plugin_installed ) {
                        $xtfe_status_text = $xtfe_plugin_active ? 'Active' : 'Installed (Inactive)';
                    }
                    ?>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='var(--xtfe-primary-color, #005AE0)';" onmouseout="this.style.borderColor='#e2e8f0';">
                        <div>
                            <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                                <img alt="<?php echo esc_attr( $xtfe_plugin_slug ); ?>" src="<?php echo esc_url( $xtfe_plugin_icon ); ?>" style="width: 54px; height: 54px; border-radius: 8px; flex-shrink: 0; border: 1px solid #e2e8f0;" />
                                <div>
                                    <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #0f172a;"><?php echo esc_html( $xtfe_plugin_slug ); ?></h4>
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px;">
                                        <span style="color: #64748b;"><?php esc_html_e( 'Status:', 'xt-facebook-events' ); ?></span>
                                        <span style="font-weight: 600; <?php 
                                            if ( $xtfe_status_text == 'Active' ) {
                                                echo 'color:#10b981;';
                                            } elseif ( $xtfe_status_text == 'Installed (Inactive)' ) {
                                                echo 'color:var(--xtfe-primary-color, #005AE0);';
                                            } else {
                                                echo 'color:#f59e0b;';
                                            }
                                        ?>">
                                            <?php echo esc_html( $xtfe_status_text ); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <p style="margin: 0 0 20px 0; font-size: 13px; color: #64748b; line-height: 1.5;"><?php echo esc_html( $xtfe_plugin_description ); ?></p>
                        </div>
                        
                        <div>
                            <?php if ( !$xtfe_plugin_installed ): ?>
                                <a target="_blank" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box; width: 100%;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Install Free Plugin', 'xt-facebook-events' ); ?></a>
                            <?php elseif ( !$xtfe_plugin_active ): ?>
                                <a href="<?php echo esc_url( admin_url( 'plugins.php?s='. $xtfe_plugin_name ) ); ?>" style="background-color: var(--xtfe-primary-color, #005AE0); border: none; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: background-color 0.2s; height: 38px; box-sizing: border-box; width: 100%;" onmouseover="this.style.backgroundColor='var(--xtfe-button-color, #0049b3)'" onmouseout="this.style.backgroundColor='var(--xtfe-primary-color, #005AE0)'"><?php esc_attr_e( 'Activate Plugin', 'xt-facebook-events' ); ?></a>
                            <?php else: ?>
                                <button disabled style="background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 0 18px; border-radius: 6px; font-weight: 600; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 13px; cursor: default; height: 38px; box-sizing: border-box; width: 100%;"><?php esc_attr_e( 'Active & Ready', 'xt-facebook-events' ); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>
    </div>
    <?php endif; ?>
</div>