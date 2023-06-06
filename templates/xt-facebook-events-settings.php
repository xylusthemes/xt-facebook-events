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
?>
<div class="xtfe_container">
    <div class="xtfe_row">
        <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'xt-facebook-events' ); ?></h3>
        <?php
        $site_url = get_home_url();
        if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) {
            ?>
            <div class="widefat xtfe_settings_error">
                <?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', __( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your facebook account.",'xt-facebook-events' ), __( 'Click here','xt-facebook-events' ), __( 'for more information.','xt-facebook-events' ) ); ?>
            </div>
        <?php
        } ?>
        <?php
        if ( ! $is_key_saved ) {
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Facebook Authorization', 'xt-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							if ( ! empty( $xtfe_fb_authorize_user ) && isset( $xtfe_fb_authorize_user['name'] ) ) {
								$name  = $xtfe_fb_authorize_user['name'];
								$avtar = $xtfe_fb_authorize_user['avtar'];
								?>
								<div class="xtfe_connection_wrapper">
									<div class="image_wrap">
										<img src="<?php echo esc_url( $avtar ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
									</div>
									<div class="name_wrap">
										<?php printf( __( 'Connected as: %s', 'xt-facebook-events' ), '<strong>' . esc_attr( $name ) . '</strong>' ); ?>
										<br/>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'xtfe_deauthorize_action', admin_url( 'admin-post.php' ) ), 'xtfe_deauthorize_action', 'xtfe_deauthorize_nonce' ) ); ?>">
											<?php esc_html_e( 'Remove Connection', 'xt-facebook-events' ); ?>
										</a>
									</div>
								</div>
								<?php
							} else {
								$button_value = esc_attr__( 'Log in	With Facebook', 'xt-facebook-events' );
								$redirect_url = wp_nonce_url( add_query_arg( 'action', 'xtfe_fb_login_action', admin_url( 'admin-post.php' ) ), 'xtfe_fb_login_action', 'xtfe_fb_login_nonce' );
								$fb_login_url = add_query_arg(
									array(
										'redirect' => rawurlencode( $redirect_url ),
									),
									'https://connect.xylusthemes.com/login/facebook'
								);
								?>
								<a href="<?php echo esc_url( $fb_login_url ); ?>" class="button button-primary"><?php echo esc_attr( $button_value ); ?></a>
								<span class="xtfe_small">
									<?php esc_attr_e( 'Please authorize your Facebook account for XT Event Widget for Social Events from your Facebook page.', 'xt-facebook-events' ); ?>
								</span>
								<?php
							}
							?>
						</td>
					</tr>

					<?php if ( ! $is_key_saved && ! $is_direct_auth ) { ?>
					<tr>
						<th scope="row" style="text-align: center" colspan="2">
							<?php esc_html_e( ' - OR -', 'xt-facebook-events' ); ?>
						</th>
					</tr>
					<?php } ?>

				</tbody>
			</table>
		<?php } ?>
        <?php if ( ! $is_direct_auth ) { ?>
            <div class="widefat xtfe_settings_notice">
                <?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','xt-facebook-events' ), __( 'You have to create a Facebook application before filling the following details.','xt-facebook-events' ), __( 'Click here','xt-facebook-events' ),  __( 'to create new Facebook application.','xt-facebook-events' ) ); ?>
                <br/>
                <?php _e( 'For detailed step by step instructions ', 'xt-facebook-events' ); ?>
                <strong><a href="http://docs.xylusthemes.com/docs/xt-facebook-events/creating-facebook-application/" target="_blank"><?php _e( 'Click here', 'xt-facebook-events' ); ?></a></strong>.
                <br/>
                <?php _e( '<strong>Set the site url as : </strong>', 'xt-facebook-events' ); ?>
                <span style="color: green;"><?php echo get_site_url(); ?></span>
                <br/>
                <?php _e( '<strong>Set Valid OAuth redirect URI : </strong>', 'xt-facebook-events' ); ?>
                <span style="color: green;"><?php echo admin_url( 'admin-post.php?action=xtfe_facebook_authorize_callback' ); ?></span>
            </div>
        <?php } ?>
		<?php
		if ( $is_key_saved ) {
            ?>
            <h3 class="setting_bar"><?php esc_attr_e( 'Authorize your Facebook Account', 'xt-facebook-events' ); ?></h3>
            <div class="fb_authorize">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <?php _e( 'Facebook Authorization','xt-facebook-events' ); ?> :
                            </th>
                            <td>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                    <input type="hidden" name="action" value="xtfe_facebook_authorize_action"/>
                                    <?php wp_nonce_field('xtfe_facebook_authorize_action', 'xtfe_facebook_authorize_nonce'); ?>
                                    <?php
                                    $button_value = __('Authorize', 'xt-facebook-events');
                                    if( isset( $xtfe_user_token_options['authorize_status'] ) && $xtfe_user_token_options['authorize_status'] == 1 && isset(  $xtfe_user_token_options['access_token'] ) &&  $xtfe_user_token_options['access_token'] != '' ){
                                        $button_value = __('Reauthorize', 'xt-facebook-events');
                                    }
                                    ?>
                                    <input type="submit" class="button" name="xtfe_facebook_authorize" value="<?php echo $button_value; ?>" />
                                    <?php
                                    if( !empty( $xtfe_fb_authorize_user ) && isset( $xtfe_fb_authorize_user['name'] ) && $xtfe_events->common->has_authorized_user_token() ){
                                        $fbauthname = sanitize_text_field( $xtfe_fb_authorize_user['name'] );
                                        if( $fbauthname != '' ){
                                           printf( __(' ( Authorized as: %s )', 'xt-facebook-events'), '<b>'.$fbauthname.'</b>' );
                                        }
                                    }
                                    ?>
                                </form>

                                <span class="xtfe_small">
                                    <?php _e( 'Please authorize your facebook account for import facebook events. Please authorize with account which you have used for create an facebook app.','xt-facebook-events' ); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>

    	<form method="post" id="xtfe_setting_form">
            <table class="form-table">
                <tbody>
                    <?php if ( ! $is_direct_auth ) { ?>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App ID','xt-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_id" name="xtfe[facebook_app_id]" type="text" value="<?php echo $facebook_app_id; ?>" />
                            <span class="xtfe_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'xt-facebook-events'),
                                    __('from here', 'xt-facebook-events')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App secret','xt-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_secret" name="xtfe[facebook_app_secret]" type="text" value="<?php echo $facebook_app_secret; ?>" />
                            <span class="xtfe_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'xt-facebook-events'),
                                    __('from here', 'xt-facebook-events')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>
                    <?php } ?>

                    <tr>
						<th scope="row">
							<?php esc_attr_e( 'Accent Color', 'xt-facebook-events' ); ?> :
						</th>
						<td>
						<?php
						$accent_color = isset( $xtfe_options['accent_color'] ) ? $xtfe_options['accent_color'] : '#039ED7';
						?>
						<input class="xtfe_color_field" type="text" name="xtfe[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>"/>
						<span class="xtfe_small">
							<?php esc_attr_e( 'Choose accent color for front-end event grid and event widget.', 'xt-facebook-events' ); ?>
						</span>
						</td>
					</tr>
                </tbody>
            </table>
            <br/>
            <div class="xtfe_element">
                <input type="hidden" name="xtfe_action" value="xtfe_save_settings" />
                <?php wp_nonce_field( 'xtfe_setting_form_nonce_action', 'xtfe_setting_form_nonce' ); ?>
                <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'xt-facebook-events' ); ?>" />
            </div>
        </form>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <?php _e( 'Clear Facebook events Cache','xt-facebook-events' ); ?>: 
                    </th>
                    <td>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="xtfe_clear_cache"/>
                            <?php wp_nonce_field('xtfe_clear_cache_action', 'xtfe_clear_cache_nonce'); ?>
                            <?php
                            $button_value = __('Clear Cache', 'xt-facebook-events');
                            ?>
                            <input type="submit" class="button" name="xtfe_clear_cache" value="<?php echo $button_value; ?>" />
                        </form>
                        <span class="xtfe_small">
                            <?php _e('Please clear cache if latest events from facebook are not reflects on website.', 'xt-facebook-events'); ?>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>
