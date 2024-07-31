<?php
/**
* The template file that responsible to display archive page of offers.
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
    <!--================ Main Content ================-->
    <main class="milenia-section">
        <!--================ Offers ================-->
        <div class="milenia-pricing-tables">
            <div class="milenia-grid milenia-grid--isotope milenia-grid--cols-3" data-isotope-layout="masonry">
                <?php while(have_posts()) : the_post(); ?>
                    <div class="milenia-grid-sizer"></div>
                    <?php get_template_part('template-parts/milenia-offers/milenia-offers', 'post'); ?>
                <?php endwhile; ?>
            </div>
        </div>
        <!--================ End of Offers ================-->
    </main>
    <!--================ End of Main Content ================-->

    <!--================ Pagination ================-->
    <footer class="text-center"><?php milenia_pagination(array('end_size' => 1), array('milenia-pagination--justified')); ?></footer>
    <!--================ End of Pagination ================-->
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
