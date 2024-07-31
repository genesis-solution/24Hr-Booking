<?php
/**
 * Template for a blank page layout.
 *
 * @since 7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	wp_head();
	?>
</head>
<body <?php body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
}

while ( have_posts() ) :
	the_post();
	?>
	<div class="wpb-content--blank">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		</article>
	</div>
	<?php

endwhile;
wp_footer();
?>
</body>
</html>
