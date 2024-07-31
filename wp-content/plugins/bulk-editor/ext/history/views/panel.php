<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');

global $WPBE;
?>

<h4 class="wpbe-documentation"><a href="https://bulk-editor.pro/document/history/" target="_blank" class="button button-primary"><span class="icon-book"></span></a>&nbsp;<?php esc_html_e('History', 'bulk-editor') ?></h4>
<div class="wpbe_alert"><?php esc_html_e('Works for edit-operations and not work with delete-operations!', 'bulk-editor') ?></div><br />

<?php if ($WPBE->show_notes) : ?>
    <div class="notice notice-warning">
        <p class="wpbe_set_attention"><?php esc_html_e('In FREE version of the plugin it is possible to roll back 2 last operations.', 'bulk-editor') ?></p>
    </div>
<?php endif; ?>


<div class="col-lg-6">
    <label for="wpbe_history_pagination_number"><?php esc_html_e('Per page:', 'bulk-editor') ?></label>
    <select id="wpbe_history_pagination_number">
        <option value="10">10</option>
        <option value="20">20</option>
        <option value="50">50</option>
        <option value="-1"><?php esc_html_e('ALL', 'bulk-editor') ?></option>
    </select>
</div>
<div class="col-lg-6 wpbe-text-align-right">
    <a href="javascript: wpbe_history_clear();void(0);" class="button button-primary"><?php esc_html_e('Clear the History', 'bulk-editor') ?></a>
</div>
<div class="clear"></div>
<div class="col-lg-12 wpbe_history_pagination_cont">

    <div class="col-lg-12 wpbe_history_filters">
        <div class="col-lg-2">
            <select id="wpbe_history_show_types">
                <option value="0"><?php esc_html_e('all', 'bulk-editor') ?></option>
                <option value="1"><?php esc_html_e('solo operations', 'bulk-editor') ?></option>
                <option value="2"><?php esc_html_e('bulk operations', 'bulk-editor') ?></option>
            </select>
        </div>
        <div class="col-lg-2" >
            <?php
            $opt_auth = array();
            $users = get_users(array('fields' => array('ID', 'display_name')));
            $opt_auth[-1] = esc_html__('by Author', 'bulk-editor');
            foreach ($users as $user) {
                if (WPBE_HELPER::can_manage_data($user->ID)) {
                    $opt_auth[$user->ID] = $user->display_name;
                }
            }
            ?>
            <?php
            echo WPBE_HELPER::draw_select(array(
                'options' => $opt_auth,
                'field' => '',
                'post_id' => "author",
                'class' => 'wpbe_history_filter_author chosen-select',
                'name' => '',
                'field' => 'wpbe_history_filter'
            ));
            ?>
        </div>
        <div class="col-lg-2" >
            <input type="text" onmouseover="wpbe_init_calendar(this)" data-title="<?php esc_html_e('by date from', 'bulk-editor') ?>" data-val-id="wpbe_history_filter_date_from" value="" class="wpbe_calendar" placeholder="<?php esc_html_e('by date from', 'bulk-editor') ?>" />
            <input type="hidden" data-key="from" data-post-id="" id="wpbe_history_filter_date_from" value=""  />            
            <a href="#" class="wpbe_calendar_clear" data-val-id="wpbe_history_filter_date_from" ><?php esc_html_e('clear', 'bulk-editor') ?></a>
        </div>
        <div class="col-lg-2" >
            <input type="text" onmouseover="wpbe_init_calendar(this)" data-title="<?php esc_html_e('by date to', 'bulk-editor') ?>" data-val-id="wpbe_history_filter_date_to" value="" class="wpbe_calendar" placeholder="<?php esc_html_e('by date to', 'bulk-editor') ?>" />
            <input type="hidden" data-key="from" data-post-id="" id="wpbe_history_filter_date_to" value=""  />
            <a href="#" class="wpbe_calendar_clear" data-val-id="wpbe_history_filter_date_to"><?php esc_html_e('clear', 'bulk-editor') ?></a>
        </div>
        <div class="col-lg-2" >
            <input type="text" id="wpbe_history_filter_field" placeholder="<?php esc_html_e('by fields', 'bulk-editor') ?>" >
        </div>     
        <div class="col-lg-2" >
            <input type="button" id="wpbe_history_filter_submit" class="button button-primary" value="<?php esc_html_e('Filter', 'bulk-editor') ?>">&nbsp;<input type="button" class="button button-primary" id="wpbe_history_filter_reset" value="<?php esc_html_e('Reset', 'bulk-editor') ?>">
        </div>


    </div>
</div>    
<div class="clear"></div>


<div id="wpbe_history_list_container"></div>

<div id="wpbe_history_pagination_container">
    <a href="#"class="wpbe_history_pagination_prev"><span class="dashicons dashicons-arrow-left-alt"></span><?php esc_html_e('Prev', 'bulk-editor') ?></a>
    <span class="wpbe_history_pagination_current_count"></span><?php esc_html_e('of', 'bulk-editor') ?><span class="wpbe_history_pagination_count"></span>
    <a href="#" class="wpbe_history_pagination_next"><?php esc_html_e('Next', 'bulk-editor') ?><span class="dashicons dashicons-arrow-right-alt"></span></a>
</div>
<input type="hidden"  id="wpbe_history_panel_nonce" value="<?php echo  wp_create_nonce( 'wpbe_history_nonce' ); ?>"  />  

