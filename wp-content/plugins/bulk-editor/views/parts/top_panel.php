<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');
?>
<div class="wpbe_top_panel_container">

    <div class="wpbe_top_panel">
        <div class="wpbe_top_panel_wrapper">

            <div id="tabs_f" class="wpbe-tabs wpbe-tabs-style-shape">

                <nav>
                    <ul><?php do_action('wpbe_ext_top_panel_tabs'); //including extensions scripts    ?></ul>
                </nav>

                <div class="content-wrap"><?php do_action('wpbe_ext_top_panel_tabs_content'); //including extensions scripts    ?></div>

            </div>

            <a href="#" class="button button-large button-primary wpbe_top_panel_btn2" title="<?php esc_html_e('Close the panel', 'bulk-editor') ?>"></a>
        </div>
    </div>

    <div class="wpbe_top_panel_slide"><a href="#" class="wpbe_top_panel_btn"><?php esc_html_e('Show: Filters/Bulk Edit/Export', 'bulk-editor') ?></a></div>

</div>
