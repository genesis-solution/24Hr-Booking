<?php
if (!defined('ABSPATH')) {
    exit;
}

//as an example for new kind of data field
?>
<ul class="wpbe_fields_tmp">
    <?php if (!empty($posts)): ?>
        <?php
        foreach ($posts as $prod_id) :
            if (has_post_thumbnail($prod_id)) {
                $img_src = wp_get_attachment_image_src(get_post_thumbnail_id($prod_id), 'thumbnail');
                $img_src = $img_src[0];
            } else {
                $img_src = WPBE_ASSETS_LINK . 'images/not-found.jpg';
            }
            ?>
            <li class="wpbe_options_li">
                <a href="#" class="help_tip wpbe_drag_and_drope" title="<?php esc_html_e('drag and drop', 'bulk-editor') ?>"><img src="<?php echo WPBE_ASSETS_LINK ?>images/move.png" alt="<?php esc_html_e('move', 'bulk-editor') ?>" /></a>
                <img src="<?php echo $img_src ?>" alt="" class="wpbe_gal_img_block" />&nbsp;
                <a href="<?php echo get_post_permalink($prod_id) ?>" target="_blank"><label><?php echo get_post_field('post_title', $prod_id) ?> (#<?php echo $prod_id ?>)</label></a>
                <a href="#" class="wpbe_prod_delete"><img src="<?php echo WPBE_ASSETS_LINK . 'images/delete2.png' ?>" alt="" /></a>
                <input type="hidden" name="wpbe_prod_ids[]" value="<?php echo intval($prod_id); ?>" />
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
