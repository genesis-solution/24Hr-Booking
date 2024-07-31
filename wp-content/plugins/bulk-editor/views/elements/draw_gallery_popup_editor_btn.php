<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $WPBE;

$title = '';
if ($post_id > 0) {
    $title = get_the_title($post_id);
}

if (is_string($images)) {
    $images = explode(',', $images);
}

if (is_array($images)) {
    if (count($images) === 1) {
        if (empty($images[0])) {
            $images = [];
        }
    }
}


$files_count = count($images);
$images_data = [];

if ($files_count > 0) {

    foreach ($images as $attachment_id) {
        $img = wp_get_attachment_image_src($attachment_id);
        if (isset($img[0])) {
            $images_data[] = array(
                'id' => $attachment_id,
                'url' => $img[0]
            );
        }
    }
}


//***

if (empty($images)) {
    ?>
    <div class="wpbe-button" onclick="wpbe_act_gallery_editor(this)" data-count="0" data-post_id="<?php echo $post_id ?>" id="popup_val_<?php echo $field_key ?>_<?php echo $post_id ?>" data-key="<?php echo $field_key ?>" data-terms_ids="" data-name="<?php echo sprintf(esc_html__('Product: %s', 'woocommerce-bulk-editor'), $title) ?>">
        <?php printf(esc_html__('Images (%s)', 'woocommerce-bulk-editor'), $files_count) ?>
    </div>
    <?php
} else {
    ?>
    <a href="javascript: void(0);" class="gallery_popup_editor_btn" data-images='<?php echo json_encode($images_data) ?>' onclick="wpbe_act_gallery_editor(this)" data-count="<?php echo $files_count ?>" data-post_id="<?php echo $post_id ?>" id="popup_val_<?php echo $field_key ?>_<?php echo $post_id ?>" data-key="<?php echo $field_key ?>" data-terms_ids="" data-name="<?php echo sprintf(esc_html__('Post: %s', 'bulk-editor'), $title) ?>">
        <?php
        foreach ($images_data as $c => $d) {
            if ($c > 2) {
                break;
            }
            ?><img src="<?php echo $d['url'] ?>" alt="" class="wpbe_btn_gal_block" /><?php
        }
        ?>
        <?php if ($files_count > 2): ?>
            <span class="wpbe_btn_gal_block"><?php echo $files_count ?></span>
        <?php endif; ?>
    </a>
    <?php
}

