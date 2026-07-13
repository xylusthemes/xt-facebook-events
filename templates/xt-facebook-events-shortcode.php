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

            <div class="xtfe-settings-wrapper">
                <div class="xtfe-shortcode-grid">
                    
                    <!-- Widget View Shortcode -->
                    <div class="xtfe-sc-card">
                        <div class="xtfe-sc-mockup">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                        </div>
                        <div class="xtfe-sc-title"><?php esc_html_e( 'Widget View', 'xt-facebook-events' ); ?></div>
                        <div class="xtfe-sc-subtitle"><?php esc_html_e( 'Hover to view shortcode', 'xt-facebook-events' ); ?></div>
                        
                        <div class="xtfe-sc-overlay">
                            <label style="font-weight: 700; color: #0f172a; margin-bottom: 12px; font-size: 14px;"><?php esc_html_e( 'Widget Shortcode', 'xt-facebook-events' ); ?></label>
                            <div style="display: flex; gap: 8px; width: 100%;">
                                <input type="text" readonly value='[wpfb_events type="widget" page_id="YOUR_PAGE_ID" max_events="10"]' style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 12px; font-weight: 600; color: #334155; padding: 10px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[wpfb_events type="widget" page_id="YOUR_PAGE_ID" max_events="10"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid View Shortcode -->
                    <div class="xtfe-sc-card">
                        <div class="xtfe-sc-pro-badge">PRO</div>
                        <div class="xtfe-sc-mockup">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </div>
                        <div class="xtfe-sc-title"><?php esc_html_e( 'Grid View', 'xt-facebook-events' ); ?></div>
                        <div class="xtfe-sc-subtitle"><?php esc_html_e( 'Hover to view shortcode', 'xt-facebook-events' ); ?></div>
                        
                        <div class="xtfe-sc-overlay">
                            <label style="font-weight: 700; color: #0f172a; margin-bottom: 12px; font-size: 14px;"><?php esc_html_e( 'Grid Shortcode', 'xt-facebook-events' ); ?></label>
                            <div style="display: flex; gap: 8px; width: 100%;">
                                <input type="text" readonly value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10"]' style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 12px; font-weight: 600; color: #334155; padding: 10px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                        </div>
                    </div>

                    <!-- New Grid Layouts -->
                    <div class="xtfe-sc-card">
                        <div class="xtfe-sc-pro-badge">PRO</div>
                        <div class="xtfe-sc-mockup">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        </div>
                        <div class="xtfe-sc-title"><?php esc_html_e( 'New Grid Layouts', 'xt-facebook-events' ); ?></div>
                        <div class="xtfe-sc-subtitle"><?php esc_html_e( 'Hover to view shortcode', 'xt-facebook-events' ); ?></div>
                        
                        <div class="xtfe-sc-overlay">
                            <label style="font-weight: 700; color: #0f172a; margin-bottom: 12px; font-size: 14px;"><?php esc_html_e( 'Style 2 Shortcode', 'xt-facebook-events' ); ?></label>
                            <div style="display: flex; gap: 8px; width: 100%;">
                                <input type="text" readonly value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10" layout="style2"]' style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 12px; font-weight: 600; color: #334155; padding: 10px; border-radius: 6px; flex-grow: 1;" />
                                <button class="xtfe-btn-copy-shortcode xtfe_button" data-value='[wpfb_events page_id="YOUR_PAGE_ID" col="3" max_events="10" layout="style2"]' style="border: none; margin: 0; padding: 0 16px; font-weight: 600; cursor: pointer; height: 38px;">Copy</button>
                            </div>
                        </div>
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
                            __( '<a class="xtfe_widget_link" href="%s" style="font-weight: 600; color: #005ae0;">Click Here</a> to go to the Widget section, or read our <a target="_blank" class="xtfe_widget_link" href="%s" style="font-weight: 600; color: #005ae0;">detailed comprehensive documentation</a> on how to use widgets effectively.', 'xt-facebook-events' ),
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