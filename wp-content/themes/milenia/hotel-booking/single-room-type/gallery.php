<?php
/**
 * Available variables
 * - array $galleryIds Array of gallery images ids.
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( mphb_tmpl_has_room_type_gallery() ) : ?>
	<?php do_action( 'milenia_mphb_render_single_room_type_before_gallery' ); ?>
	<?php
		$galleryIds = mphb_tmpl_get_room_type_gallery_ids();

		if(is_array($galleryIds) && !empty($galleryIds)) : ?>
			<div id="simple-slideshow-<?php echo get_the_ID(); ?>" class="owl-carousel milenia-simple-slideshow">
				<?php foreach($galleryIds as $gallery_image_id) : ?>
					<?php echo wp_get_attachment_image($gallery_image_id, 'entity-thumb-standard', false, array(
						'class' => 'owl-carousel-img'
					)); ?>
				<?php endforeach; ?>
			</div>
			<div data-sync="#simple-slideshow-<?php echo get_the_ID(); ?>" class="owl-carousel owl-carousel--nav-edges owl-carousel--nav-onhover owl-carousel--nav-small milenia-simple-slideshow-thumbs">
				<?php foreach($galleryIds as $gallery_image_id) : ?>
					<?php echo wp_get_attachment_image($gallery_image_id, 'medium', false, array(
						'class' => 'owl-carousel-img'
					)); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php do_action( 'milenia_mphb_render_single_room_type_after_gallery' ); ?>
<?php endif; ?>
