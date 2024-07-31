<?php
/**
* The template file that responsible to display archive page of room types.
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
    <!--================ Room Types ================-->
    <div class="milenia-entities milenia-entities--style-15">
        <div class="milenia-grid">
            <?php while(have_posts()) : the_post(); ?>
                <?php
                    if(function_exists('MPHB'))
                    {
                        $room = MPHB()->getRoomTypeRepository()->findById(get_the_ID());
                        setup_postdata(get_post($room->getId()));
                        set_query_var('milenia-archive-room-type', serialize($room));
                        get_template_part('template-parts/milenia-rooms/milenia-rooms', 'post');
                    }
                ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
    <!--================ End of Room Types ================-->

    <!--================ Pagination ================-->
    <footer class="text-center"><?php milenia_pagination(array('end_size' => 1), array('milenia-pagination--justified')); ?></footer>
    <!--================ End of Pagination ================-->
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>
