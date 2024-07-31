<?php
/**
* The template file for displaying a single page with default page type.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia, $MileniaLayout;
$MileniaHelper = $Milenia->helper();
?>

<?php if(have_posts()) : ?>
	<?php while (have_posts()) : the_post() ?>
		<div class="milenia-entity-content">
			<?php the_content(); ?>
		</div>
	<?php endwhile; ?>

	<?php if(!is_singular('tribe_events')) : ?>
		<!-- - - - - - - - - - - - - - Pagination - - - - - - - - - - - - - - - - -->
		<?php wp_link_pages( array(
			'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
			'after' => '</div>',
			'link_before' => '<span>',
			'link_after' => '</span>'
		) ); ?>
		<!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - - - - -->
	<?php endif; ?>
<?php else :
	get_template_part('template-parts/content', 'none');
endif;?>
<?php if(comments_open() && !is_singular('tribe_events')) : ?>
	<hr/>
	<?php comments_template(); ?>
<?php endif; ?>
