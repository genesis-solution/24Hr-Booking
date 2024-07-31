<?php
/**
* The template file that responsible to display a certain accommodation type
* with right sidebar.
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

while ( have_posts() ) : the_post(); ?>

    <?php if(!post_password_required()) : ?>
        <header class="milenia-page-header milenia-entity-single milenia-entity--room">
            <div class="row align-items-center milenia-columns-aligner--edges-lg">
                <div class="col-lg-9">
                    <?php
                    /**
                     * @hooked \MPHB\Views\SingleRoomTypeView::renderTitle - 10
                     */
                    do_action( 'milenia_mphb_render_single_room_type_title' );
                    ?>
                </div>

                <div class="col-lg-3">
                    <div class="milenia-entity-meta">
                        <div>
                            <?php
                            /**
                             * @hooked \MPHB\Views\SingleRoomTypeView::renderPrice - 10
                             */
                            do_action( 'milenia_mphb_render_single_room_type_price' );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    <?php endif; ?>


	<div class="row">
		<!-- - - - - - - - - - - - - - Main Content Column - - - - - - - - - - - - - - - - -->
		<div class="<?php echo esc_attr($MileniaLayout->getMainLayoutClasses('main')); ?>">
            <?php
            if ( post_password_required() ) {
        		echo get_the_password_form();
        	}
            else {
                do_action( 'milenia_mphb_render_single_room_type_wrapper_start' );
                ?>

                <div <?php post_class('milenia-entity-single milenia-entity--room'); ?>>
            		<?php do_action( 'milenia_mphb_render_single_room_type_before_content' ); ?>

                    <?php
                    /**
            		 * @hooked \MPHB\Views\SingleRoomTypeView::renderGallery - 10
            		 * @hooked \Milenia\App\ServiceProvider\MPHBServiceProvider::renderRoomTypeContentTabs - 20
            		 */
                    do_action('milenia_mphb_render_single_room_type_layout_sidebar_content', get_the_ID());
                    ?>

            		<?php do_action( 'milenia_mphb_render_single_room_type_after_content' ); ?>
            	</div>

                <?php
                do_action( 'milenia_mphb_render_single_room_type_wrapper_end' );
            } ?>
		</div>
		<!-- - - - - - - - - - - - - - End of Main Content Column - - - - - - - - - - - - - - - - -->

		<?php if($MileniaLayout->hasSidebar()) : ?>
			<!-- - - - - - - - - - - - - - Sidebar - - - - - - - - - - - - - - - - -->
			<aside class="milenia-sidebar <?php echo esc_attr($MileniaLayout->getMainLayoutClasses('side')); ?>" id="milenia-sidebar">
				<?php get_sidebar(); ?>
			</aside>
			<!-- - - - - - - - - - - - - - End of Sidebar - - - - - - - - - - - - - - - - -->
		<?php endif; ?>
	</div>
<?php endwhile; ?>
