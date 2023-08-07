<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shortcode_table = new XT_Facebook_Shortcode_List_Table();
$shortcode_table->prepare_items();

?>
<div class="xtfe_container">
    <div class="xtfe_row">
        <h3 class="setting_bar"><?php esc_attr_e( 'Shortcodes', 'xt-facebook-events' ); ?></h3>
        <?php $shortcode_table->display(); ?>
    </div>
</div>

<div class="xtfe_container">
    <div class="xtfe_row">
        <h3 class="setting_bar xtfe_mt_30" ><?php esc_attr_e( 'Widgets', 'xt-facebook-events' ); ?></h3>
        <div class="xyfe_widget_info">
            <?php 
                $xtfe_widget_section = sprintf(
                    '<h3 class="xtfe_setting_bar"><a class="xtfe_widget_link" href="%s">%s</a> to go to the Widget section, <a target="_blank" class="xtfe_widget_link" href="%s">%s</a> you can find detailed comprehensive documentation on how to use widgets effectively.</h3>',
                    esc_url( admin_url( 'widgets.php' ) ),
                    esc_html__( 'Click Here', 'xt-facebook-events' ),
                    esc_url( 'https://docs.xylusthemes.com/docs/facebookevents/display-using-widget/' ),
                    esc_html__( 'Here', 'xt-facebook-events' )
                );
                echo $xtfe_widget_section;
            ?>
        </div>
    </div>
</div>