<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
$xtfe_options = get_option( XTFE_OPTIONS, array() );
?>
<div class="xtfe_container">
    <div class="xtfe_row">
    	
    	<form method="post" id="xtfe_setting_form">                

            <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'xt-facebook-events' ); ?></h3>
            <p><?php _e( 'You need a Facebook App ID and App Secret to display events from Facebook.','xt-facebook-events' ); ?> </p>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App ID','xt-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_id" name="xtfe[facebook_app_id]" type="text" value="<?php if ( isset( $xtfe_options['facebook_app_id'] ) ) { echo $xtfe_options['facebook_app_id']; } ?>" />
                            <span class="xtei_small">
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
                            <input class="facebook_app_secret" name="xtfe[facebook_app_secret]" type="text" value="<?php if ( isset( $xtfe_options['facebook_app_secret'] ) ) { echo $xtfe_options['facebook_app_secret']; } ?>" />
                            <span class="xtei_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'xt-facebook-events'),
                                    __('from here', 'xt-facebook-events')
                                 );
                                ?>
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
    </div>
</div>
