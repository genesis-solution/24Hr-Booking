<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$btn_text = esc_html__('Content[empty]', 'bulk-editor');
if($val){
	$btn_text = esc_html__('Content', 'bulk-editor');
	if (WPBE_HELPER::get_show_text_editor()) {
		$btn_text = wp_trim_words($val , 15);
	}	
}

$use_wp_editor = 0;
global $WPBE;
if ($field_key == 'post_content' && $WPBE->settings->use_wp_editor) {
	if (use_block_editor_for_post_type($post['post_type'])){
		$use_wp_editor = 1;
	}	
}

?>

<div class="wpbe-button text-editor-standart" data-post_edit_link ="<?php echo esc_attr(get_edit_post_link($post['ID']));?>" data-wp_editor="<?php echo $use_wp_editor; ?>" data-text-title="<?php echo WPBE_HELPER::get_show_text_editor() ?>" onclick="wpbe_act_popupeditor(this, <?php echo intval($post['post_parent']) ?>)" data-post_id="<?php esc_html_e($post['ID']) ?>" id="popup_val_<?php echo $field_key ?>_<?php echo $post['ID'] ?>" data-key="<?php esc_html_e($field_key) ?>" data-terms_ids="" data-name="<?php esc_html_e(sprintf(esc_html__('Post: %s', 'bulk-editor'), $post['post_title'])) ?>">
    <?php echo $btn_text ?>
</div>
