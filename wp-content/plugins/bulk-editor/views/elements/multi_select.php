<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//$val is terms ids here
?>

<div class="wpbe_multi_select_cell">
    <div class="wpbe_multi_select_cell_list"><?php echo WPBE_HELPER::draw_attribute_list_btn($active_fields[$field_key]['select_options'], $val, $field_key, $post) ?></div>
    <div class="wpbe_multi_select_cell_dropdown" style="display: none;">
        <?php
        echo WPBE_HELPER::draw_select(array(
            'field' => $field_key,
            'post_id' => $post_id,
            'class' => 'wpbe_data_select chosen-select',
            //'options' => $this->settings->active_fields[$field_key]['select_options'],
            'options' => array(),
            'selected' => $val,
                //'onmouseover' => 'wpbe_multi_select_onmouseover(this)',
                //'onchange' => 'wpbe_act_select(this)'
                ), true);
        ?>
        <br /><br /> 
        <div class="wpbe-float-left">
            <a href="#" class="page-title-action wpbe_multi_select_cell_select"><?php esc_html_e('Select all', 'bulk-editor') ?></a>
            <a href="#" class="page-title-action wpbe_multi_select_cell_deselect"><?php esc_html_e('Deselect all', 'bulk-editor') ?></a>
        </div>

        <br /><br />         
        <div class="wpbe-float-right">
            <a href="#" class="page-title-action wpbe_multi_select_cell_cancel"><?php esc_html_e('cancel', 'bulk-editor') ?></a>
        </div>


        <div class="wpbe-float-left">
            <a href="#" class="page-title-action wpbe_multi_select_cell_save"><?php esc_html_e('save', 'bulk-editor') ?></a>
        </div>


        <div class="wpbe-float-left">
            <a href="#" class="page-title-action wpbe_multi_select_cell_new" data-tax-key="<?php esc_html_e($field_key) ?>"><?php esc_html_e('new', 'bulk-editor') ?></a>
        </div>


        <div class="clear"></div>


    </div>
</div>

