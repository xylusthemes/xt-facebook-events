<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $xtfe_events;
$xtfe_options = get_option( XTFE_OPTIONS, array() );
$facebook_app_id = isset($xtfe_options['facebook_app_id']) ? $xtfe_options['facebook_app_id'] : '';
$facebook_app_secret = isset($xtfe_options['facebook_app_secret']) ? $xtfe_options['facebook_app_secret'] : '';
$xtfe_user_token_options = get_option( 'xtfe_user_token_options', array() );
$xtfe_fb_authorize_user = get_option( 'xtfe_fb_authorize_user', array() );
$is_direct_auth         = isset( $xtfe_user_token_options['direct_auth'] ) ? ( 1 === $xtfe_user_token_options['direct_auth'] ) : false;
$is_authenticated       = isset( $xtfe_user_token_options['authorize_status'] ) ? ( 1 === $xtfe_user_token_options['authorize_status'] ) : false;
$is_key_saved           = ( ! empty( $facebook_app_id ) && ! empty( $facebook_app_secret ) );
$is_connected           = ( ! empty( $xtfe_fb_authorize_user ) && isset( $xtfe_fb_authorize_user['name'] ) && ( ! isset( $xtfe_user_token_options['authorize_status'] ) || 1 === $xtfe_user_token_options['authorize_status'] ) );
?>
<div class="xtfe-settings-container">
    <?php
    $site_url = get_home_url();
    if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) {
        ?>
        <div class="notice notice-error xtfe-notice">
            <p><?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', esc_html__( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required to authorize your facebook account.",'xt-facebook-events' ), esc_html__( 'Click here','xt-facebook-events' ), esc_html__( 'for more information.','xt-facebook-events' ) ); ?></p>
        </div>
    <?php
    }

    if ( isset( $_GET['authorize'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( '1' === $_GET['authorize'] ) {
            echo '<div class="notice notice-success is-dismissible xtfe-notice"><p><strong>' . esc_html__( 'Facebook account securely connected!', 'xt-facebook-events' ) . '</strong></p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible xtfe-notice"><p><strong>' . esc_html__( 'Failed to connect Facebook account. Please try again.', 'xt-facebook-events' ) . '</strong></p></div>';
        }
    }

    if ( isset( $_GET['xtauthorize'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( '1' === $_GET['xtauthorize'] ) {
            echo '<div class="notice notice-success is-dismissible xtfe-notice"><p><strong>' . esc_html__( 'Facebook App securely authorized!', 'xt-facebook-events' ) . '</strong></p></div>';
        } elseif ( '2' === $_GET['xtauthorize'] ) {
            echo '<div class="notice notice-error is-dismissible xtfe-notice"><p><strong>' . esc_html__( 'Please insert a valid Facebook App ID and Secret.', 'xt-facebook-events' ) . '</strong></p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible xtfe-notice"><p><strong>' . esc_html__( 'Failed to authorize Facebook App. Please check your credentials.', 'xt-facebook-events' ) . '</strong></p></div>';
        }
    }

    if ( isset( $_GET['deauthorize'] ) && '1' === $_GET['deauthorize'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        echo '<div class="notice notice-success is-dismissible xtfe-notice"><p><strong>' . esc_html__( 'Facebook account successfully disconnected.', 'xt-facebook-events' ) . '</strong></p></div>';
    }
    ?>



    <!-- Authorization Card -->
    <div class="xtfe-card mt-2" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0; margin-bottom: 24px;">
        <div class="header" style="background-color: #f8fafc; border-bottom-color: #e2e8f0;">
            <div class="text">
                <div class="header-icon" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230f172a%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><path d=%22M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z%22></path></svg>');"></div>
                <div class="header-title">
                    <span style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php esc_html_e( 'Facebook Authorization & Settings', 'xt-facebook-events' ); ?></span>
                </div>
            </div>
        </div>
        <div class="content" style="padding: 28px;gap:0;">
            <!-- New Widget Promo -->
            <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="padding-right: 20px;">
                    <h3 style="color: #3730a3; margin: 0 0 8px 0; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <?php esc_html_e( 'Try Our New Facebook Widget', 'xt-facebook-events' ); ?>
                        <span style="background: #4f46e5; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">New</span>
                    </h3>
                    <p style="margin: 0; color: #4338ca; font-size: 14px; line-height: 1.5;">
                        <?php esc_html_e( 'No need Auth, Key, Token! Use Public Page ID, Public Group ID, iCal URL and Event IDs directly.', 'xt-facebook-events' ); ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=xtfepro_live_feed' ) ); ?>" style="display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 14px; transition: background 0.2s; white-space: nowrap; border: 1px solid #4338ca;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                        <?php esc_html_e( 'Create Widget', 'xt-facebook-events' ); ?> &rarr;
                    </a>
                </div>
            </div>

            <div class="xtfe-auth-toggle-trigger <?php echo $is_connected ? 'is-open' : ''; ?>" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 20px; font-weight: 600; font-size: 15px; color: #0f172a; cursor: pointer; display: flex; justify-content: space-between; align-items: center; <?php echo $is_connected ? '' : 'margin-bottom: 24px;'; ?>">
                <span><?php esc_html_e( 'Facebook Authorization', 'xt-facebook-events' ); ?></span>
                <svg class="xtfe-auth-toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s; <?php echo $is_connected ? 'transform: rotate(180deg);' : ''; ?>"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="xtfe-auth-toggle-target <?php echo $is_connected ? 'is-open' : ''; ?>" style="<?php echo $is_connected ? 'display: block;' : 'display: none;'; ?>">
                <?php if ( ! $is_direct_auth ) { ?>
                    <div class="notice notice-info xtfe-notice" >
                        <p>
                            <?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s', esc_html__( 'Note : ','xt-facebook-events' ), esc_html__( 'You have to create a Facebook application before filling the following details.','xt-facebook-events' ), esc_html__( 'Click here','xt-facebook-events' ), esc_html__( 'to create a new Facebook application.','xt-facebook-events' ) ); ?>
                            <br/>
                            <?php esc_html_e( 'For detailed step by step instructions ', 'xt-facebook-events' ); ?>
                            <strong><a href="https://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php esc_html_e( 'Click here', 'xt-facebook-events' ); ?></a></strong>.
                            <br/>
                            <strong><?php esc_html_e( 'Set the site url as : ', 'xt-facebook-events' ); ?></strong>
                            <span style="color: green; font-weight: 600;"><?php echo esc_url( get_site_url() ); ?></span>
                            <br/>
                            <strong><?php esc_html_e( 'Set Valid OAuth redirect URI : ', 'xt-facebook-events' ); ?></strong>
                            <span style="color: green; font-weight: 600;"><?php echo esc_url( admin_url( 'admin-post.php?action=xtfe_facebook_authorize_callback' ) ); ?></span>
                        </p>
                    </div>
                <?php } ?>

                <div class="xtfe-settings-wrapper" style="margin-top: 20px;<?php if ( $is_connected ) { echo 'margin-bottom: 0px;'; }else{ echo 'margin-bottom: 20px;'; } ?>">
                <?php if ( $is_connected ) { 
                    $name  = $xtfe_fb_authorize_user['name'];
                    $avtar = $xtfe_fb_authorize_user['avtar'];
                    ?>
                    <div class="xtfe-setting-row">
                        <div class="xtfe-inner-section-1">
                            <label><?php esc_html_e( 'Account Status', 'xt-facebook-events' ); ?></label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <div class="xtfe_connection_wrapper" style="background-color: #ecfdf5; border: 1px solid #34d399; border-radius: 8px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; width: 100%; box-sizing: border-box;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <div class="image_wrap" style="position: relative; width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                        <img src="<?php echo esc_url( $avtar ); ?>" alt="<?php echo esc_attr( $name ); ?>" style="border-radius: 50%; object-fit: cover; border: 2px solid #10b981; box-sizing: border-box; display: block; margin: 0; padding: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" />
                                        <div style="position: absolute; bottom: 0; right: -4px; width: 14px; height: 14px; background-color: #10b981; border: 2px solid #ecfdf5; border-radius: 50%; display: flex; justify-content: center; align-items: center; box-sizing: content-box;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        </div>
                                    </div>
                                    <div class="name_wrap">
                                        <div style="font-weight: 700; color: #065f46; font-size: 15px; margin-bottom: 4px;">
                                            <?php echo esc_html( $name ); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #047857; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                            <?php esc_html_e( 'Account Securely Connected', 'xt-facebook-events' ); ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'xtfe_deauthorize_action', admin_url( 'admin-post.php' ) ), 'xtfe_deauthorize_action', 'xtfe_deauthorize_nonce' ) ); ?>" style="background-color: #ef4444; color: #ffffff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.2s; border: 1px solid #dc2626;" onmouseover="this.style.backgroundColor='#dc2626'; this.style.boxShadow='0 2px 4px rgba(220, 38, 38, 0.2)';" onmouseout="this.style.backgroundColor='#ef4444'; this.style.boxShadow='none';">
                                    <?php esc_html_e( 'Remove Connection', 'xt-facebook-events' ); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } else { 
                    $button_value = esc_html__( 'Log in With Facebook', 'xt-facebook-events' );
                    $redirect_url = wp_nonce_url( add_query_arg( 'action', 'xtfe_fb_login_action', admin_url( 'admin-post.php' ) ), 'xtfe_fb_login_action', 'xtfe_fb_login_nonce' );
                    $fb_login_url = add_query_arg(
                        array(
                            'redirect' => rawurlencode( $redirect_url ),
                        ),
                        'https://connect.xylusthemes.com/login/facebook'
                    );
                    ?>
                    <div class="xtfe-setting-row">
                        <div class="xtfe-inner-section-1">
                            <label><?php esc_html_e( 'Direct Login', 'xt-facebook-events' ); ?></label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <a href="<?php echo esc_url( $fb_login_url ); ?>" class="xtfe_button" style="display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; height: auto;"><?php echo esc_html( $button_value ); ?></a>
                            <div class="xtfe_small" style="margin-top: 6px;">
                                <?php esc_html_e( 'Please authorize your Facebook account to import Facebook events.', 'xt-facebook-events' ); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ( $is_key_saved ) { ?>
                    <div class="xtfe-setting-row">
                        <div class="xtfe-inner-section-1">
                            <label><?php esc_html_e( 'App Reauthorization', 'xt-facebook-events' ); ?></label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display: inline-block;">
                                <input type="hidden" name="action" value="xtfe_facebook_authorize_action"/>
                                <?php wp_nonce_field('xtfe_facebook_authorize_action', 'xtfe_facebook_authorize_nonce'); ?>
                                <?php
                                $button_value = esc_html__('Authorize', 'xt-facebook-events');
                                if( isset( $xtfe_user_token_options['authorize_status'] ) && $xtfe_user_token_options['authorize_status'] == 1 && isset(  $xtfe_user_token_options['access_token'] ) &&  $xtfe_user_token_options['access_token'] != '' ){
                                    $button_value = esc_html__('Reauthorize App', 'xt-facebook-events');
                                }
                                ?>
                                <input type="submit" class="xtfe_button" name="xtfe_facebook_authorize" value="<?php echo esc_attr( $button_value ); ?>" />
                            </form>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
            
            <form method="post" id="xtfe_setting_form" style="display: contents;padding-bottom: 0;">
                <div class="xtfe-auth-toggle-target <?php echo $is_connected ? 'is-open' : ''; ?>" style="<?php echo $is_connected ? 'display: block;padding-bottom: 0;' : 'display: none;'; ?>">
                    <?php if ( ! $is_connected ) { ?>
                        <hr style="border: 0; border-top: 1px solid #E8E8EB; margin: 0 0 24px 0;">
                    <?php } ?>
                    <div class="xtfe-settings-wrapper" style="margin-top: 20px;">
                        <?php if ( ! $is_direct_auth ) { ?>
                            <!-- App ID -->
                            <div class="xtfe-setting-row">
                                <div class="xtfe-inner-section-1">
                                    <label for="facebook_app_id"><?php esc_html_e( 'Facebook App ID', 'xt-facebook-events' ); ?></label>
                                </div>
                                <div class="xtfe-inner-section-2">
                                    <input id="facebook_app_id" class="facebook_app_id" name="xtfe[facebook_app_id]" type="text" value="<?php echo esc_attr( $facebook_app_id ); ?>" />
                                    <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Enter your Facebook Application ID.', 'xt-facebook-events' ); ?></div>
                                </div>
                            </div>

                            <!-- App Secret -->
                            <div class="xtfe-setting-row">
                                <div class="xtfe-inner-section-1">
                                    <label for="facebook_app_secret"><?php esc_html_e( 'Facebook App Secret', 'xt-facebook-events' ); ?></label>
                                </div>
                                <div class="xtfe-inner-section-2">
                                    <input id="facebook_app_secret" class="facebook_app_secret" name="xtfe[facebook_app_secret]" type="text" value="<?php echo esc_attr( $facebook_app_secret ); ?>" />
                                    <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Enter your Facebook Application Secret.', 'xt-facebook-events' ); ?></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="xtfe-settings-wrapper xtfe_accent_color_container" >
                    <!-- Accent Color -->
                    <div class="xtfe-setting-row">
                        <div class="xtfe-inner-section-1">
                            <label><?php esc_html_e( 'Accent Color', 'xt-facebook-events' ); ?></label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <?php
                            $accent_color = isset( $xtfe_options['accent_color'] ) ? $xtfe_options['accent_color'] : '#039ED7';
                            ?>
                            <div class="xtfe-color-picker-wrap" style="display: flex; align-items: center; gap: 10px;">
                                <input type="color" class="xtfe-color-input" id="xtfe_accent_color" name="xtfe[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>" style="width: 44px; height: 34px; padding: 0; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer; background: none;">
                                <span class="xtfe-color-val" style="font-family: SFMono-Regular, Consolas, monospace; font-size: 12.5px; color: #475569; background: #f1f5f9; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-weight: 600;"><?php echo esc_html( strtoupper( $accent_color ) ); ?></span>
                            </div>
                            <script>
                            jQuery(document).ready(function($) {
                                $('#xtfe_accent_color').on('input', function() {
                                    $(this).siblings('.xtfe-color-val').text($(this).val().toUpperCase());
                                });
                            });
                            </script>
                            <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Choose accent color for front-end event grid and event widget.', 'xt-facebook-events' ); ?></div>
                        </div>
                    </div>
                </div>

                <div class="xtfe-setting-row" style="margin-top: 24px;">
                    <div class="xtfe-inner-section-2">
                        <input type="hidden" name="xtfe_action" value="xtfe_save_settings" />
                        <?php wp_nonce_field( 'xtfe_setting_form_nonce_action', 'xtfe_setting_form_nonce' ); ?>
                        <input type="submit" class="xtfe_button" value="<?php esc_attr_e( 'Save Settings', 'xt-facebook-events' ); ?>" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($){
        $('.xtfe-auth-toggle-trigger').on('click', function(){
            var $trigger = $(this);
            var $targets = $('.xtfe-auth-toggle-target');
            
            $targets.slideToggle(300);
            $trigger.toggleClass('is-open');
            $targets.toggleClass('is-open');
            
            var icon = $trigger.find('.xtfe-auth-toggle-icon');
            var $accentContainer = $('.xtfe_accent_color_container');
            if ($trigger.hasClass('is-open')) {
                icon.css('transform', 'rotate(180deg)');
                $accentContainer.css('margin-top', '');
            } else {
                icon.css('transform', 'none');
                $accentContainer.css('margin-top', '25px');
            }
        });
    });
    </script>
    <style>
        .xtfe-auth-toggle-trigger {
            transition: all 0.2s ease;
        }
        .xtfe-auth-toggle-trigger.is-open {
            margin-bottom: 0 !important;
            border-bottom-color: transparent !important;
            border-radius: 6px 6px 0 0 !important;
        }
        .xtfe-auth-toggle-target {
            border-left: 1px solid transparent;
            border-right: 1px solid transparent;
        }
        .xtfe-auth-toggle-target.is-open {
            border-left-color: #e2e8f0;
            border-right-color: #e2e8f0;
            padding: 0 24px;
        }
        #xtfe_setting_form .xtfe-auth-toggle-target.is-open {
            border-bottom: 1px solid #e2e8f0;
            border-radius: 0 0 6px 6px;
            padding-bottom: 24px;
            margin-bottom: 20px;
        }
    </style>

    <!-- Cache Card -->
    <div class="xtfe-card mt-2" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0; margin-bottom: 24px;">
        <div class="header" style="background-color: #f8fafc; border-bottom-color: #e2e8f0;">
            <div class="text">
                <div class="header-icon" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230f172a%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%2221 8 21 21 3 21 3 8%22></polyline><rect x=%221%22 y=%223%22 width=%2222%22 height=%225%22></rect><line x1=%2210%22 y1=%2212%22 x2=%2214%22 y2=%2212%22></line></svg>');"></div>
                <div class="header-title">
                    <span style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php esc_html_e( 'Clear Cache', 'xt-facebook-events' ); ?></span>
                </div>
            </div>
        </div>
        <div class="content" style="padding: 28px;">
            <div class="xtfe-settings-wrapper">
                <div class="xtfe-setting-row">
                    <div class="xtfe-inner-section-1">
                        <label><?php esc_html_e( 'Clear Events Cache', 'xt-facebook-events' ); ?></label>
                    </div>
                    <div class="xtfe-inner-section-2">
                        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                            <input type="hidden" name="action" value="xtfe_clear_cache"/>
                            <?php wp_nonce_field('xtfe_clear_cache_action', 'xtfe_clear_cache_nonce'); ?>
                            <input type="submit" class="xtfe_button" name="xtfe_clear_cache" value="<?php esc_attr_e('Clear Cache', 'xt-facebook-events'); ?>" />
                        </form>
                        <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Clear cache if latest events from Facebook are not reflected on your site.', 'xt-facebook-events' ); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
