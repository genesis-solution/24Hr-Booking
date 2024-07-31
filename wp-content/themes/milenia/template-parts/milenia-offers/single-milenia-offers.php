<?php
/**
* The template for displaying a single offer page.
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

$milenia_offer_price = $Milenia->getThemeOption('milenia-offer-price', null);
$milenia_offer_currency = $Milenia->getThemeOption('milenia-offer-currency', null);
$milenia_offer_start_date = $Milenia->getThemeOption('milenia-offer-start-date', null);
$milenia_offer_end_date = $Milenia->getThemeOption('milenia-offer-end-date', null);
$milenia_offer_tags_state = $Milenia->getThemeOption('milenia-offer-tags-state', 'show');
$milenia_offer_related_posts_state = $Milenia->getThemeOption('milenia-offer-related-posts-state', 'show');
$milenia_offer_share_buttons_state = $Milenia->getThemeOption('milenia-post-share-buttons-state', 'show');

if($milenia_offer_related_posts_state == 'show')
{
	$mielnia_offer_cats = get_the_terms(get_the_ID(), 'milenia-offers-categories');

	if(is_array($mielnia_offer_cats) && !empty($mielnia_offer_cats))
	{
		$milenia_offer_cats_final = array();
		foreach($mielnia_offer_cats as $milenia_cat) array_push($milenia_offer_cats_final, $milenia_cat->term_id);
	}
}
get_header(); ?>

<?php if(have_posts()) : ?>
	<?php while( have_posts() ) : the_post(); ?>
		<!--================ Offer Container ================-->
        <div class="milenia-single-entity-container">
            <!--================ Content Section ================-->
            <div class="milenia-section">
                <main <?php post_class('milenia-entity-single milenia-entity--post milenia-entity--offer'); ?>>
                    <!--================ Offer Header ================-->
					<header class="milenia-entity-header milenia-entity-header--single">
						<div class="row align-items-center milenia-columns-aligner--edges-lg">
							<div class="col-lg-9">
								<?php if(!empty(get_the_title())) : ?>
									<h1><?php the_title(); ?></h1>
								<?php endif; ?>

								<?php if(!post_password_required()) : ?>
									<div class="milenia-entity-meta">
										<?php if(milenia_has_post_terms(get_the_ID(), 'milenia-offers-categories')) : ?>
			        						<div><?php
												esc_html_e('In', 'milenia');
			        							echo milenia_get_post_terms(get_the_ID(), 'milenia-offers-categories');
			        						?></div>
			        					<?php endif; ?>

										<?php if(!empty($milenia_offer_start_date)) : ?>
											<div>
												<?php esc_html_e('Start date:', 'milenia'); ?>
												<time class="milenia-color--black" datetime="<?php echo esc_attr(mysql2date('c', $milenia_offer_start_date)); ?>"><?php echo esc_html(mysql2date('F j, Y g:i', $milenia_offer_start_date, true)); ?></time>
											</div>
										<?php endif; ?>

										<?php if(!empty($milenia_offer_end_date)) : ?>
											<div>
												<?php esc_html_e('End date:', 'milenia'); ?>
												<time class="milenia-color--black" datetime="<?php echo esc_attr(mysql2date('c', $milenia_offer_end_date)); ?>"><?php echo esc_html(mysql2date('F j, Y g:i', $milenia_offer_end_date, true)); ?></time>
											</div>
										<?php endif; ?>
			        				</div>
								<?php endif; ?>
							</div>

							<div class="col-lg-3">
								<?php if(!post_password_required() && !empty($milenia_offer_price) && !empty($milenia_offer_currency)) : ?>
									<div class="milenia-entity-meta">
										<strong class="milenia-entity-price"><?php printf('%s%s', esc_html($milenia_offer_currency), esc_html($milenia_offer_price)); ?></strong>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</header>
                    <!--================ End of Offer Header ================-->

					<!--================ Media ================-->
					<?php if(has_post_thumbnail() && !post_password_required()) : ?>
						<div class="milenia-entity-media">
							<?php the_post_thumbnail('entity-thumb-square'); ?>
						</div>
					<?php endif; ?>
					<!--================ End of Media ================-->

                    <?php if(!empty(get_the_content())) : ?>
                        <div class="milenia-entity-content milenia-form--fields-white">
                            <?php
                                the_content();
                                wp_link_pages(array(
                                    'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
                                    'after' => '</div>',
                                    'link_before' => '<span>',
                                    'link_after' => '</span>'
                                ));
                            ?>
                        </div>
                    <?php endif; ?>

					<?php if(post_password_required()) : ?>
						<footer class="milenia-entity-footer">
							<a href="<?php echo esc_url( get_post_type_archive_link( get_post_type() ) ); ?>" class="milenia-btn milenia-btn--scheme-primary" role="button"><?php esc_html_e('View all Offers', 'milenia'); ?></a>
						</footer>
					<?php else : ?>
	                    <?php if($milenia_offer_share_buttons_state == 'show' || $milenia_offer_tags_state == 'show') : ?>
	                        <footer class="milenia-entity-footer">
	                            <div class="row milenia-columns-aligner--edges-lg">
	                                <div class="col-lg-9">
	                                    <?php if($milenia_offer_tags_state == 'show' && milenia_has_post_terms(get_the_ID(), 'milenia-offers-tags')) : ?>
	                                        <?php esc_html_e('Tags:', 'milenia'); ?>
	                                        <?php echo milenia_get_post_terms(get_the_ID(), 'milenia-offers-tags'); ?>
	                                    <?php endif; ?>

	                                    <?php
	                                    /**
	                                     * Hook for the add some content after the main content of the post.
	                                     *
	                                     * @hooked
	                                     */
	                                    do_action('milenia_single_post_footer_left_col', get_post());
	                                    ?>
	                                </div>

	                                <div class="col-lg-3">
	                                    <?php
	                                    /**
	                                     * Hook for the add some content after the main content of the post.
	                                     *
	                                     * @hooked
	                                     */
	                                    do_action('milenia_single_post_footer_right_col', get_post());
	                                    ?>
	                                </div>
	                            </div>
	                        </footer>
	                    <?php endif; ?>
					<?php endif; ?>
                </main>
            </div>
            <!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->

			<?php if(!post_password_required()) : ?>
				<?php if($milenia_offer_share_buttons_state == 'show' && isset($milenia_offer_cats_final)) : ?>
					<!-- - - - - - - - - - - - - - Content Section - - - - - - - - - - - - - -->
					<section class="milenia-section">
						<h3><?php esc_html_e('Related Offers', 'milenia'); ?></h3>
						<?php echo do_shortcode(sprintf('[vc_milenia_offers milenia_offers_data_exc="%s" milenia_offers_data_categories="%s" milenia_offers_columns="milenia-grid--cols-3" milenia_offers_item_style="milenia-pricing-tables--style-2"]', get_the_ID(), implode(',', $milenia_offer_cats_final))); ?>
					</section>
					<!-- - - - - - - - - - - - - - End of Content Section - - - - - - - - - - - - - -->
				<?php endif; ?>

	            <?php if(comments_open()) : ?>
	                <?php comments_template(); ?>
	            <?php endif; ?>
			<?php endif; ?>

            <!-- - - - - - - - - - - - - - Posts Navgation - - - - - - - - - - - - - -->
            <nav class="milenia-section">
                <ul class="milenia-list--unstyled milenia-posts-navigation">
                    <?php previous_post_link('<li class="milenia-posts-navigation-prev"><span>%link</span></li>'); ?>
                    <?php next_post_link('<li class="milenia-posts-navigation-next"><span>%link</span></li>'); ?>
                </ul>
            </nav>
            <!-- - - - - - - - - - - - - - End of Posts Navigation - - - - - - - - - - - - - -->
        </div>
        <!-- - - - - - - - - - - - - - End of Single Post Container - - - - - - - - - - - - - -->
	<?php endwhile; ?>
    <!-- - - - - - - - - - - - - - End of Main Content - - - - - - - - - - - - - - - - -->
<?php else : ?>
	<?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>

<?php get_footer(); ?>
