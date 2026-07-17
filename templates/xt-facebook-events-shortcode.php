<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="xtfe-shortcode-container">
    <div class="xtfe-card mt-2" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0; margin-bottom: 24px;">
        <div class="header" style="background-color: #f8fafc; border-bottom-color: #e2e8f0;">
            <div class="text">
                <div class="header-icon" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230f172a%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><rect x=%223%22 y=%224%22 width=%2218%22 height=%2218%22 rx=%222%22 ry=%222%22></rect><line x1=%2216%22 y1=%222%22 x2=%2216%22 y2=%226%22></line><line x1=%228%22 y1=%222%22 x2=%228%22 y2=%226%22></line><line x1=%223%22 y1=%2210%22 x2=%2221%22 y2=%2210%22></line></svg>');"></div>
                <div class="header-title">
                    <span style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php esc_html_e( 'Available Shortcodes', 'xt-facebook-events' ); ?></span>
                </div>
            </div>
        </div>
        <div class="content" style="padding: 28px;">

            <div style="display: flex; gap: 40px; flex-wrap: wrap;">
                <div class="xtfe-settings-wrapper" style="flex: 1;min-width: 300px;gap: 45px;">
                    
                    <!-- Widget View Shortcode -->
                    <div class="xtfe-setting-row" onmouseover="changePrimaryPreview('widget');">
                        <div class="xtfe-inner-section-1">
                            <label><?php esc_html_e( 'Widget View', 'xt-facebook-events' ); ?></label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <div style="display: flex; gap: 12px; align-items: center; width: 100%;">
                                <input type="text" readonly value='[wpfb_events type="widget" page_id="YOUR_PAGE_ID" max_events="10"]' class="xtfe-shortcode-input" style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 13px; font-weight: 600; color: #334155; padding: 10px 14px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[wpfb_events type="widget" page_id="YOUR_PAGE_ID" max_events="10"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                            <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Display events in a compact widget layout.', 'xt-facebook-events' ); ?></div>
                        </div>
                    </div>

                    <!-- Grid View Shortcode -->
                    <div class="xtfe-setting-row" onmouseover="changePrimaryPreview('grid');">
                        <div class="xtfe-inner-section-1">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <?php esc_html_e( 'Grid View', 'xt-facebook-events' );
                                    if( !xtfe_is_pro() ){
                                        ?>
                                            <span style="background: #005ae0; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">PRO</span>
                                        <?php
                                    }
                                ?>
                            </label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <div style="display: flex; gap: 12px; align-items: center; width: 100%;">
                                <input type="text" readonly value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10"]' class="xtfe-shortcode-input" style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 13px; font-weight: 600; color: #334155; padding: 10px 14px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                            <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Display events in a clean grid layout.', 'xt-facebook-events' ); ?></div>
                        </div>
                    </div>

                    <!-- Style 2 Layouts -->
                    <div class="xtfe-setting-row" onmouseover="changePrimaryPreview('style2');">
                        <div class="xtfe-inner-section-1">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <?php esc_html_e( 'New Grid Layouts (Style 2)', 'xt-facebook-events' ); 
                                    if( !xtfe_is_pro() ){
                                        ?>
                                            <span style="background: #005ae0; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">PRO</span>
                                        <?php
                                    }
                                ?>
                            </label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <div style="display: flex; gap: 12px; align-items: center; width: 100%;">
                                <input type="text" readonly value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10" layout="style2"]' class="xtfe-shortcode-input" style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 13px; font-weight: 600; color: #334155; padding: 10px 14px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10" layout="style2"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                            <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Display events with a modern inner-card layout.', 'xt-facebook-events' ); ?></div>
                        </div>
                    </div>

                    <!-- Live Feed Shortcode -->
                    <div class="xtfe-setting-row" onmouseover="changePrimaryPreview('live_feed');">
                        <div class="xtfe-inner-section-1">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <?php esc_html_e( 'Live Feed (New)', 'xt-facebook-events' ); ?>
                                <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">NEW</span>
                            </label>
                        </div>
                        <div class="xtfe-inner-section-2">
                            <div style="display: flex; gap: 12px; align-items: center; width: 100%;">
                                <input type="text" readonly value='[xtfepro_live_feed id="YOUR_FEED_ID"]' class="xtfe-shortcode-input" style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 13px; font-weight: 600; color: #334155; padding: 10px 14px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[xtfepro_live_feed id="YOUR_FEED_ID"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                            <div class="xtfe_small" style="margin-top: 6px;"><?php esc_html_e( 'Display the new no-auth live widget feed.', 'xt-facebook-events' ); ?></div>
                        </div>
                    </div>

                </div>

                <!-- Preview Column -->
                <div style="flex: 0 0 350px;">
                    <div style="position: sticky; top: 40px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; padding: 20px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
                        <h3 id="preview-primary-title" style="margin-top: 0; font-size: 13px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px;">Widget View Preview</h3>
                        <div id="preview-primary-content" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px;">
                            <!-- Widget Layout Mockup (Default view) -->
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div style="display: flex; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 10px; gap: 12px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 6px;"></div>
                                    <div style="flex: 1;">
                                        <div style="width: 70%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                                        <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                                    </div>
                                </div>
                                <div style="display: flex; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 10px; gap: 12px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 6px;"></div>
                                    <div style="flex: 1;">
                                        <div style="width: 85%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                                        <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                                    </div>
                                </div>
                                <div style="display: flex; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 10px; gap: 12px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 6px;"></div>
                                    <div style="flex: 1;">
                                        <div style="width: 60%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                                        <div style="width: 35%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p style="font-size: 11px; color: #94a3b8; text-align: center; margin-top: 16px; font-style: italic;">Hover over any row to visualize the layout.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="xtfe-card mt-2" style="border-radius: 8px; overflow: hidden; border-color: #e2e8f0;">
        <div class="header" style="background-color: #f8fafc; border-bottom-color: #e2e8f0;">
            <div class="text">
                <div class="header-icon" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%230f172a%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><path d=%22M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z%22></path><polyline points=%223.27 6.96 12 12.01 20.73 6.96%22></polyline><line x1=%2212%22 y1=%2222.08%22 x2=%2212%22 y2=%2212%22></line></svg>');"></div>
                <div class="header-title">
                    <span style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php esc_html_e( 'Widgets Documentation & Usage', 'xt-facebook-events' ); ?></span>
                </div>
            </div>
        </div>
        <div class="content" style="padding: 28px;">
            <div class="xtfe-settings-wrapper">
                <div class="xtfe_widget_info" style="font-size: 14px; line-height: 1.6; color: #334155;">
                    <?php 
                        $xtfe_widget_section = sprintf(
                            /* translators: 1: Widget page link, 2: Documentation link */
                            __( '<a class="xtfe_widget_link" href="%1$s" style="font-weight: 600; color: #005ae0;">Click Here</a> to go to the Widget section, or read our <a target="_blank" class="xtfe_widget_link" href="%2$s" style="font-weight: 600; color: #005ae0;">detailed comprehensive documentation</a> on how to use widgets effectively.', 'xt-facebook-events' ),
                            esc_url( admin_url( 'widgets.php' ) ),
                            esc_url( 'https://docs.xylusthemes.com/docs/facebookevents/display-using-widget/' )
                        );
                        echo wp_kses_post( $xtfe_widget_section );
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changePrimaryPreview(layout) {
    let title = 'Widget View Preview';
    let html = '';

    if (layout === 'grid') {
        title = 'Grid View Preview';
        html = `<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
            <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                <div style="height: 70px; background: #e2e8f0;"></div>
                <div style="padding: 10px;">
                    <div style="width: 80%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                <div style="height: 70px; background: #e2e8f0;"></div>
                <div style="padding: 10px;">
                    <div style="width: 70%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 60%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                <div style="height: 70px; background: #e2e8f0;"></div>
                <div style="padding: 10px;">
                    <div style="width: 90%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; border: 1px solid #f1f5f9;">
                <div style="height: 70px; background: #e2e8f0;"></div>
                <div style="padding: 10px;">
                    <div style="width: 75%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 55%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
        </div>`;
    } else if (layout === 'style2') {
        title = 'Style 2 Layout Preview';
        html = `<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
            <div style="background: #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; position: relative; height: 110px;">
                <div style="position: absolute; bottom: 8px; left: 8px; right: 8px; background: #fff; border-radius: 6px; padding: 8px;">
                    <div style="width: 80%; height: 6px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="width: 50%; height: 4px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="background: #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; position: relative; height: 110px;">
                <div style="position: absolute; bottom: 8px; left: 8px; right: 8px; background: #fff; border-radius: 6px; padding: 8px;">
                    <div style="width: 70%; height: 6px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="width: 60%; height: 4px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="background: #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; position: relative; height: 110px;">
                <div style="position: absolute; bottom: 8px; left: 8px; right: 8px; background: #fff; border-radius: 6px; padding: 8px;">
                    <div style="width: 90%; height: 6px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="width: 40%; height: 4px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="background: #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow:hidden; position: relative; height: 110px;">
                <div style="position: absolute; bottom: 8px; left: 8px; right: 8px; background: #fff; border-radius: 6px; padding: 8px;">
                    <div style="width: 75%; height: 6px; background: #94a3b8; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="width: 55%; height: 4px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
        </div>`;
    } else if (layout === 'widget') {
        title = 'Widget View Preview';
        html = `<div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 10px; gap: 12px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 6px;"></div>
                <div style="flex: 1;">
                    <div style="width: 70%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 40%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="display: flex; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 10px; gap: 12px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 6px;"></div>
                <div style="flex: 1;">
                    <div style="width: 85%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 50%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
            <div style="display: flex; background: #fff; border-radius: 8px; border: 1px solid #f1f5f9; padding: 10px; gap: 12px; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 6px;"></div>
                <div style="flex: 1;">
                    <div style="width: 60%; height: 8px; background: #94a3b8; border-radius: 4px; margin-bottom: 8px;"></div>
                    <div style="width: 35%; height: 6px; background: #cbd5e1; border-radius: 4px;"></div>
                </div>
            </div>
        </div>`;
    } else if (layout === 'live_feed') {
        title = 'Live Feed Preview';
        html = `<div style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                <div style="width: 40%; height: 12px; background: #cbd5e1; border-radius: 6px;"></div>
                <div style="width: 20%; height: 12px; background: #94a3b8; border-radius: 6px;"></div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                <div style="background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow:hidden; border: 1px solid #e2e8f0;">
                    <div style="height: 80px; background: #cbd5e1; position: relative;">
                        <div style="position: absolute; top: 8px; right: 8px; width: 24px; height: 12px; background: #10b981; border-radius: 4px;"></div>
                    </div>
                    <div style="padding: 12px;">
                        <div style="width: 80%; height: 8px; background: #0f172a; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 50%; height: 6px; background: #64748b; border-radius: 4px;"></div>
                    </div>
                </div>
                <div style="background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow:hidden; border: 1px solid #e2e8f0;">
                    <div style="height: 80px; background: #cbd5e1;"></div>
                    <div style="padding: 12px;">
                        <div style="width: 70%; height: 8px; background: #0f172a; border-radius: 4px; margin-bottom: 8px;"></div>
                        <div style="width: 60%; height: 6px; background: #64748b; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        </div>`;
    }
    
    document.getElementById('preview-primary-title').innerText = title;
    document.getElementById('preview-primary-content').innerHTML = html;
}
</script>