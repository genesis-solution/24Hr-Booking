<?php
/**
 * Describes media area of the standard post.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$milenia_post_archive_style = get_query_var('milenia-post-archive-style', 'milenia-entities--style-9');
$milenia_post_archive_isotope_layout = get_query_var('milenia-post-archive-isotope-layout', 'grid');
$milenia_post_thumb_size = get_query_var('milenia-post-thumb-size', 'entity-thumb-standard');

if( has_post_thumbnail() && strpos($milenia_post_archive_style, 'milenia-entities--without-media') == false ) : ?>
	<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
	<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
		<div class="milenia-entity-media" data-bg-image-src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), $milenia_post_thumb_size)); ?>">
			<?php if($milenia_post_archive_isotope_layout == 'masonry' && empty(get_post_format())) : ?>
				<a class="milenia-ln--independent" href="<?php the_permalink(); ?>">
		            <?php the_post_thumbnail($milenia_post_thumb_size); ?>
		        </a>
			<?php endif; ?>
		</div>
	<?php else : ?>
	    <div class="milenia-entity-media">
	        <a class="milenia-ln--independent" href="<?php the_permalink(); ?>">
	            <?php the_post_thumbnail($milenia_post_thumb_size); ?>
	        </a>
	    </div>
	<?php endif; ?>
    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
<?php endif; ?>
