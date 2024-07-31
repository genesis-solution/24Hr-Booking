<?php
/**
* The template file that responsible to display archive page of team members.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}
?>

<?php if(have_posts()) : ?>
    <!-- - - - - - - - - - - - - - Main Content - - - - - - - - - - - - - - - - -->
    <main class="milenia-section">
        <!-- - - - - - - - - - - - - - Team Members - - - - - - - - - - - - - -->
        <div class="milenia-grid milenia-grid--cols-4">
            <?php while(have_posts()) : the_post(); ?>
                <?php get_template_part('template-parts/milenia-team-members/milenia-team-members', 'post'); ?>
            <?php endwhile; ?>
        </div>
        <!-- - - - - - - - - - - - - - End of Team Members - - - - - - - - - - - - - -->
    </main>
    <!-- - - - - - - - - - - - - - End of Main Content - - - - - - - - - - - - - - - - -->

    <!-- - - - - - - - - - - - - - Pagination - - - - - - - - - - - - - - - - -->
    <footer class="text-center"><?php milenia_pagination(array('end_size' => 1), array('milenia-pagination--justified')); ?></footer>
    <!-- - - - - - - - - - - - - - End of Pagination - - - - - - - - - - - - - - - - -->
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
