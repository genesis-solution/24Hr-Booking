<?php
/**
* The template for displaying a single post.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

// load header.php
get_header();

if(have_posts()) : ?>
	<?php while(have_posts()) : the_post(); ?>
        <!-- - - - - - - - - - - - - - Single Post Container - - - - - - - - - - - - - -->
        <div class="milenia-single-entity-container">
            <!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
            <div class="milenia-section">
                <main <?php post_class('milenia-entity-single milenia-entity--post'); ?>>
                    <!-- - - - - - - - - - - - - - Post Header - - - - - - - - - - - - - -->
                    <header class="milenia-entity-header milenia-entity-header--single text-center">
                        <?php if(!empty(wp_get_attachment_caption(get_the_ID()))) : ?>
        					<h1 class="milenia-entity-title"><?php echo esc_html(wp_get_attachment_caption(get_the_ID())); ?></h1>
        				<?php endif; ?>

        				<div class="milenia-entity-meta">
        					<div>
        						<time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php the_date('F j, Y'); ?></time>
        					</div>
        					<div>
        						<?php esc_html_e('by', 'milenia'); ?>&nbsp;<a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a>
        					</div>
                            <?php if(is_user_logged_in()) : ?>
								<div><?php edit_post_link(__('Edit', 'milenia'), null, null, get_the_ID(), 'milenia-entity-edit-link'); ?></div>
							<?php endif; ?>
        				</div>
                    </header>
                    <!-- - - - - - - - - - - - - - End of Post Header - - - - - - - - - - - - - -->
                    <?php if(wp_attachment_is_image(get_the_ID())) : ?>
                        <div class="milenia-entity-media">
                	        <?php echo wp_get_attachment_image(get_the_ID(), 'entity-thumb-standard'); ?>
                	    </div>
                    <?php endif; ?>

                    <div class="milenia-entity-content">
                        <?php the_content(); ?>
                    </div>
                </main>
            </div>
            <!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
        </div>
        <!-- - - - - - - - - - - - - - End of Single Post Container - - - - - - - - - - - - - -->
    <?php endwhile; ?>
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif;

// load footer.php
get_footer();
?>
