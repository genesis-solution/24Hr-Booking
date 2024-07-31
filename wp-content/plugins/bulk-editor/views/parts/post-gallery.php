<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<br />

<ul class="wpbe_fields_tmp">

    <?php if (!empty($images)): ?>
        <?php
        foreach ($images as $attachment_id) :
            $img = wp_get_attachment_image_src($attachment_id);
            ?>
            <li>
                <img src="<?php echo $img[0] ?>" alt="" class="wpbe_gal_img_block" data-attachment-id="<?php echo $attachment_id ?>" />
                <a href="#" class="wpbe_gall_file_delete" title="<?php esc_html_e('Detach image of the post', 'bulk-editor') ?>"><span class="icon-trash button"></span></a>
                <input type="hidden" name="wpbe_gallery_images[]" value="<?php echo intval($attachment_id); ?>" />
            </li>
        <?php endforeach; ?>
    <?php endif; ?>

</ul>


