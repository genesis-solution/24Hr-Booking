<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $WPBE;

$title = '';
$meta_data = array();
if ($post_id > 0) {
    $post = $WPBE->posts->get_post($post_id);
    $title = $post['post_title'];

    $meta_data = $WPBE->posts->get_post_field($post_id, $field_key);
}

$meta_data = json_encode($meta_data, JSON_HEX_QUOT | JSON_HEX_TAG);

if (empty($btn_title)) {
    $btn_title = esc_html__('Array', 'bulk-editor');
}
?>

<div class="wpbe-button" onclick="wpbe_act_meta_popup_editor(this)" id="meta_popup_<?php echo $field_key ?>_<?php echo $post_id ?>" data-count="0" data-post_id="<?php esc_html_e($post_id) ?>" data-key="<?php esc_html_e($field_key) ?>" data-terms_ids="" data-name="<?php echo sprintf(esc_html__('Post: %s', 'bulk-editor'), $title) ?>">
    <div style="display: none;" class="meta_popup_btn_data"><?php echo $meta_data ?></div>
    <?php echo $btn_title ?>
</div>




