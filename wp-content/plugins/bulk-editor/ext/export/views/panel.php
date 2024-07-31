<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');

global $WPBE;
?>

<div class="notice notice-warning">
    <p>
        <?php printf(esc_html__('Export will be applied to: %s', 'bulk-editor'), '<span class="wpbe_action_will_be_applied_to">' . sprintf(esc_html__('all the [%s] on the site', 'bulk-editor'), $WPBE->settings->current_post_type) . '</span>') ?>
    </p>
</div>

<div class="notice notice-info">
    <p>
        <?php printf(esc_html__('Note: you can change columns set and then set their order in the tab Settings, then save it as columns profile which in future will help you with exporting the [%s] data format quickly without necessary each time set columns order and their set!', 'bulk-editor'), $WPBE->settings->current_post_type) ?>    
    </p>
</div>
<br />

<div class="col-lg-6">
    <a href="javascript: wpbe_export_to_csv();void(0);" class="button button-primary button-large wpbe_export_posts_btn"><span class="icon-export"></span>&nbsp;<?php esc_html_e('Export to CSV', 'bulk-editor') ?></a>
    <a href="javascript: wpbe_export_to_xml();void(0);" class="button button-primary button-large wpbe_export_posts_btn"><?php _e('Export to XML', 'bulk-editor') ?></a>
<!-- &nbsp;<a href="javascript: wpbe_export_to_excel();void(0);" class="button button-primary button-large wpbe_export_posts_btn"><?php esc_html_e('Export to Excel', 'bulk-editor') ?></a><br /> -->
    <a href="<?php echo $download_link ?>" target="_blank" class="button button-primary button-large wpbe_export_posts_btn_down" style="display: none;"><span class="icon-download"></span>&nbsp;<?php esc_html_e('download', 'bulk-editor') ?>&nbsp;<span class="icon-download"></span></a>
    <a href="<?php echo $download_link_xml ?>" target="_blank" class="button button-primary button-large wpbe_export_posts_btn_down_xml" style="display: none; color: greenyellow;"><span class="icon-download"></span>&nbsp;<?php _e('download XML', 'bulk-editor') ?>&nbsp;<span class="icon-download"></span></a>
    <a href="javascript: wpbe_export_to_csv_cancel();void(0);" class="button button-primary button-large wpbe_export_posts_btn_cancel" style="display: none;"><span class="icon-cancel-circled-3"></span>&nbsp;<?php esc_html_e('cancel export', 'bulk-editor') ?></a>
</div>

<div class="col-lg-6 wpbe-text-align-right">

    <?php esc_html_e('Current export functionality is just for simple operations and data share, if you need something complex find and use especial plugin for Export WordPress data!', 'bulk-editor') ?>

</div>
<div class="clear"></div>
<br />

<ul>
    <li>
        <select id="wpbe_export_delimiter">
            <option value=",">,</option>
            <option value=";">;</option>
            <option value="|">|</option>
            <option value="^">^</option>
            <option value="~">~</option>
        </select>&nbsp;<?php echo WPBE_HELPER::draw_tooltip(esc_html__('Select CSV data delimiter.', 'bulk-editor')) ?>
    </li>
</ul>


<ul>
    <li>
        <div class="col-lg-12">

            <div class="wpbe_progress wpbe_progress_export" style="display: none;">
                <div class="wpbe_progress_in" id="wpbe_export_progress">0%</div>
            </div>

        </div>
        <div class="clear"></div>
    </li>

</ul>



<div class="clear"></div>
<br />
<a href="https://bulk-editor.pro/document/wordpress-posts-export/" target="_blank" class="button button-primary wpbe-info-btn"><span class="icon-book"></span>&nbsp;<?php esc_html_e('Documentation', 'bulk-editor') ?></a>
<br />
