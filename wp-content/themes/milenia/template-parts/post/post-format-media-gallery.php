<?php
/**
 * Describes media area of the gallery post.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia;
$milenia_gallery = $Milenia->getThemeOption('milenia-post-gallery', '', array('object_id' => get_the_ID()));
?>

<?php if(!empty($milenia_gallery)) : ?>
	<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
	<div class="milenia-entity-media">
	    <div class="owl-carousel milenia-simple-slideshow">
			<?php foreach($milenia_gallery as $attachment_id => $image) : ?>
				<?php echo wp_get_attachment_image($attachment_id, 'entity-thumb-standard', false, array(
					'class' => 'owl-carousel-img'
				)); ?>
			<?php endforeach; ?>
		</div>
	</div>
	<!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
<?php endif; ?>
