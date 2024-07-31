<?php
/**
* The template for displaying a single team member page.
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

$position = $Milenia->getThemeOption('milenia-team-member-position');

get_header(); ?>

<?php if(have_posts()) : ?>
	<?php while( have_posts() ) : the_post();
		$whatsapp = get_post_meta(get_the_ID(), 'milenia-team-member-whatsapp', true);
		$facebook = get_post_meta(get_the_ID(), 'milenia-team-member-facebook', true);
		$tripadvisor = get_post_meta(get_the_ID(), 'milenia-team-member-tripadvisor', true);
		$youtube = get_post_meta(get_the_ID(), 'milenia-team-member-youtube', true);
		$twitter = get_post_meta(get_the_ID(), 'milenia-team-member-twitter', true);
		$google_plus = get_post_meta(get_the_ID(), 'milenia-team-member-google-plus', true);
		$instagram = get_post_meta(get_the_ID(), 'milenia-team-member-instagram', true);
		$milenia_team_member_has_social_profiles = (!empty($facebook) || !empty($tripadvisor) || !empty($youtube) || !empty($twitter) || !empty($google_plus) || !empty($instagram));
	?>
		<div class="row">
	        <!-- - - - - - - - - - - - - - Main Content - - - - - - - - - - - - - - - - -->
			<main class="col-xl-10 offset-xl-1">
		        <!-- - - - - - - - - - - - - - Single Entity - - - - - - - - - - - - - - - - -->
		        <article <?php post_class('milenia-entity milenia-entity--team-member-single') ?>>
					<div class="row">
						<!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
						<?php if( has_post_thumbnail() && !post_password_required() ) : ?>
							<div class="col-md-4">
								<div class="milenia-entity-media milenia-team-member-photo">
									<?php the_post_thumbnail('milenia-team-member-thumb'); ?>
								</div>
							</div>
						<?php endif; ?>
						<!-- - - - - - - - - - - - - - End of Entity Media - - - - - - - - - - - - - - - - -->

						<!-- - - - - - - - - - - - - - Entity Content - - - - - - - - - - - - - - - - -->
						<div class="col">
							<?php if(!empty(get_the_content())) : ?>
				                <div class="milenia-entity-content">
									<?php if(!empty($position)) : ?>
										<em class="milenia-team-member-position"><?php echo esc_html($position); ?></em>
									<?php endif; ?>
				                    <?php
				                        the_content();
				                        wp_link_pages( array(
				                            'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
				                            'after' => '</div>',
				                            'link_before' => '<span>',
				                            'link_after' => '</span>'
				                        ) );
				                    ?>
				                </div>
							<?php endif; ?>

							<?php if($milenia_team_member_has_social_profiles) : ?>
								<ul class="milenia-social-icons milenia-list--unstyled">
									<?php if(!empty($facebook)) : ?>
										<li>
											<a href="<?php echo esc_url($facebook); ?>">
												<i class="fab fa-facebook-f"></i>
											</a>
										</li>
									<?php endif; ?>
									<?php if(!empty($tripadvisor)) : ?>
										<li>
											<a href="<?php echo esc_url($tripadvisor); ?>">
												<i class="fab fa-tripadvisor"></i>
											</a>
										</li>
									<?php endif; ?>
									<?php if(!empty($youtube)): ?>
										<li>
											<a href="<?php echo esc_url($youtube); ?>">
												<i class="fab fa-youtube"></i>
											</a>
										</li>
									<?php endif; ?>
									<?php if(!empty($twitter)) : ?>
										<li>
											<a href="<?php echo esc_url($twitter); ?>">
												<i class="fab fa-twitter"></i>
											</a>
										</li>
									<?php endif; ?>
									<?php if(!empty($google_plus)) : ?>
										<li>
											<a href="<?php echo esc_url($google_plus); ?>">
												<i class="fab fa-google"></i>
											</a>
										</li>
									<?php endif; ?>
									<?php if(!empty($instagram)) : ?>
										<li>
											<a href="<?php echo esc_url($instagram); ?>">
												<i class="fab fa-instagram"></i>
											</a>
										</li>
									<?php endif; ?>
									<?php if(!empty($whatsapp)) : ?>
										<li>
											<a href="<?php echo esc_url($whatsapp); ?>">
												<i class="fab fa-whatsapp"></i>
											</a>
										</li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>

							<a href="<?php echo esc_url( get_post_type_archive_link( get_post_type() ) ); ?>" class="milenia-btn" role="button"><?php esc_html_e('View all team members', 'milenia'); ?></a>
						</div>
						<!-- - - - - - - - - - - - - - End of Entity Content - - - - - - - - - - - - - - - - -->
					</div>
		        </article>
		        <!-- - - - - - - - - - - - - - End of Single Entity - - - - - - - - - - - - - - - - -->

	            <?php if(comments_open()) : ?>
					<?php comments_template(); ?>
	            <?php endif; ?>
		    </main>
		</div>
	<?php endwhile; ?>
    <!-- - - - - - - - - - - - - - End of Main Content - - - - - - - - - - - - - - - - -->
<?php else : ?>
	<?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>

<?php get_footer(); ?>
