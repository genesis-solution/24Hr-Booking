<?php
/**
 * The template file that is responsible to display a gallery item.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$milenia_gallery_item = unserialize(get_query_var('milenia-gallery-item', null));
$milenia_gallery_container_id = get_query_var('milenia-gallery-container-id', null);
if(is_null($milenia_gallery_item)) return;

$columns = $this->throughWhiteList($this->attributes['milenia_gallery_columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4' ), 'milenia-grid--cols-3');
$image_size = $this->get_image_size($columns);
?>

<!-- - - - - - - - - - - - - - Gallery Item - - - - - - - - - - - - - -->
<figure class="<?php echo esc_attr(implode(' ', get_post_class('milenia-grid-item', $milenia_gallery_item['parent_gallery_id']))); ?>">
    <?php if(isset($milenia_gallery_item['image-external-link']) && !empty($milenia_gallery_item['image-external-link'])) : ?>
        <a href="<?php echo esc_url($milenia_gallery_item['image-external-link']); ?>" class="milenia-ln--independent milenia-gallery-item-link"
            <?php if(isset($milenia_gallery_item['image-external-link-target'])) : ?>
                target="_blank"
            <?php endif; ?>
            <?php if(isset($milenia_gallery_item['image-external-link-nofollow'])) : ?>
                rel="nofollow"
            <?php endif; ?>
            <?php if(isset($milenia_gallery_item['image-title']) && !empty($milenia_gallery_item['image-title'])) : ?>
                title="<?php echo esc_attr($milenia_gallery_item['image-title']); ?>"
            <?php endif; ?>>
    <?php else : ?>
        <a href="<?php echo esc_url(wp_get_attachment_image_url($milenia_gallery_item['attach_id'], 'full')); ?>" class="milenia-ln--independent milenia-gallery-item-link"
		   data-caption="<?php echo esc_attr(wp_get_attachment_caption($milenia_gallery_item['attach_id'])); ?>"
		   data-fancybox="<?php echo esc_attr($milenia_gallery_container_id); ?>"
           <?php if(isset($milenia_gallery_item['image-title']) && !empty($milenia_gallery_item['image-title'])) : ?>
               data-caption="<?php echo esc_attr($milenia_gallery_item['image-title']); ?>"
               title="<?php echo esc_attr($milenia_gallery_item['image-title']); ?>"
           <?php endif; ?>>
    <?php endif ?>
        <?php echo wp_get_attachment_image($milenia_gallery_item['attach_id'], $image_size); ?>
    </a>
    <?php if(isset($milenia_gallery_item['image-title']) && !empty($milenia_gallery_item['image-title'])) : ?>
        <figcaption class="milenia-gallery-item-caption"><?php echo wp_kses_post($milenia_gallery_item['image-title']); ?></figcaption>
    <?php endif; ?>
</figure>
<!-- - - - - - - - - - - - - - End of Gallery Item - - - - - - - - - - - - - -->
