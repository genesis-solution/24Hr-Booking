<?php
/**
* The gallery layout for the single project page.
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

$milenia_project_additional_info = $Milenia->getThemeOption('milenia-project-meta', array());
$milenia_project_release_date = $Milenia->getThemeOption('milenia-project-date', '');
$milenia_project_author_id = $Milenia->getThemeOption('milenia-project-author', null);

$milenia_project_gallery = get_post_meta(get_the_ID(), 'milenia_gallery_builder', true);
?>

<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
<main class="milenia-section milenia-section--py-small milenia-entity-single milenia-entity--project">
    <div class="row">
        <!-- - - - - - - - - - - - - - Project Images - - - - - - - - - - - - - -->
        <div class="col-lg-8">
            <?php if(isset($milenia_project_gallery['sliders']) && isset($milenia_project_gallery['sliders']['slides']) && is_array($milenia_project_gallery['sliders']['slides']) && count($milenia_project_gallery['sliders']['slides'])) : ?>
                <div class="milenia-gallery">
                    <div class="milenia-grid milenia-grid--cols-2">
                        <?php $milenia_loop_counter = 0; foreach($milenia_project_gallery['sliders']['slides'] as $index => $image) : ?>
                            <?php if($milenia_loop_counter == 0 || $milenia_loop_counter % 3 == 0) : ?>
                                <div class="milenia-grid-item milenia-grid-item--2x">
                            <?php else : ?>
                                <div class="milenia-grid-item">
                            <?php endif; ?>
                                <?php echo wp_get_attachment_image($image['attach_id'], 'entity-thumb-standard'); ?>
                            </div>
                        <?php $milenia_loop_counter++; endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- - - - - - - - - - - - - - End of Project Images - - - - - - - - - - - - - -->

        <!-- - - - - - - - - - - - - - Project Description - - - - - - - - - - - - - -->
        <div class="col-lg-4">
            <section class="milenia-section milenia-section--py-small">
                <div class="milenia-entity-content">
                    <h6 class="milenia-fw-bold"><?php esc_html_e('Description', 'milenia'); ?></h6>
                    <?php the_content(); ?>
                </div>
            </section>

            <section class="milenia-section milenia-section--py-small">
                <h6 class="milenia-fw-bold"><?php esc_html_e('Details', 'milenia'); ?></h6>

                <ul class="milenia-details-list milenia-details-list--colors-reversed milenia-list--unstyled">
    				<?php if(is_array($milenia_project_additional_info) && !empty($milenia_project_additional_info)) : ?>
    					<?php foreach($milenia_project_additional_info as $info_item) : ?>
    		                <li>
    		                    <span><?php echo esc_html($info_item[0]); ?></span>
    							<?php echo esc_html($info_item[1]); ?>
    		                </li>
    					<?php endforeach; ?>
    				<?php endif; ?>

    				<?php if(!empty($milenia_project_release_date)) : ?>
    					<li>
    						<span><?php esc_html_e('Date', 'milenia'); ?>:</span>
    						<time datetime="<?php echo esc_attr($milenia_project_release_date); ?>"><?php echo mysql2date('F j, Y', $milenia_project_release_date, true); ?></time>
    					</li>
    				<?php endif; ?>

    				<?php if(milenia_has_post_terms(get_the_ID(), 'milenia-portfolio-categories')) : ?>
    	                <li>
    	                    <span><?php esc_html_e('Categories', 'milenia'); ?>:</span>
    	                    <?php echo milenia_get_post_terms(get_the_ID(), 'milenia-portfolio-categories'); ?>
    	                </li>
    				<?php endif; ?>

    				<?php if(!empty($milenia_project_author_id)) : ?>
    	                <li>
    	                    <span><?php esc_html_e('Author', 'milenia'); ?>:</span>
    	                    <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID', $milenia_project_author_id))); ?>"><?php echo get_the_author_meta('display_name', $milenia_project_author_id); ?></a>
    	                </li>
    				<?php endif; ?>

    				<?php if(milenia_has_post_terms(get_the_ID(), 'milenia-portfolio-tags')) : ?>
    	                <li>
    	                    <span><?php esc_html_e('Tags', 'milenia'); ?>:</span>
    	                    <?php echo milenia_get_post_terms(get_the_ID(), 'milenia-portfolio-tags'); ?>
    	                </li>
    				<?php endif; ?>
                </ul>
            </section>

            <?php
			/**
			 * Hook for the add some content after the main content of the post.
			 *
			 * @hooked
			 */
			do_action('milenia_single_post_after_content', get_post(), true, false);
			?>
        </div>
        <!-- - - - - - - - - - - - - - End of Project Descrption - - - - - - - - - - - - - -->
    </div>
</main>
<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
