<?php
/**
 * The template file that is responsible to describe a team member element markup.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia-theme-functionality') );
}

$facebook = get_post_meta(get_the_ID(), 'milenia-team-member-facebook', true);
$tripadvisor = get_post_meta(get_the_ID(), 'milenia-team-member-tripadvisor', true);
$youtube = get_post_meta(get_the_ID(), 'milenia-team-member-youtube', true);
$twitter = get_post_meta(get_the_ID(), 'milenia-team-member-twitter', true);
$google_plus = get_post_meta(get_the_ID(), 'milenia-team-member-google-plus', true);
$instagram = get_post_meta(get_the_ID(), 'milenia-team-member-instagram', true);
$milenia_team_member_has_social_profiles = (!empty($facebook) || !empty($tripadvisor) || !empty($youtube) || !empty($twitter) || !empty($google_plus) || !empty($instagram));
?>
<!-- - - - - - - - - - - - - - Team Member - - - - - - - - - - - - - - - - -->
<div class="milenia-grid-item">
	<figure <?php post_class('milenia-team-member'); ?>>
		<?php if(has_post_thumbnail()) : ?>
			<a href="<?php the_permalink(); ?>" class="milenia-team-member-photo milenia-ln--independent">
				<?php the_post_thumbnail('milenia-team-member-thumb'); ?>
			</a>
		<?php endif; ?>

		<figcaption class="milenia-team-member-info">
			<h2 class="milenia-team-member-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<?php if(!empty(get_post_meta(get_the_ID(), 'milenia-team-member-position', true))) : ?>
				<em class="milenia-team-member-position milenia-font--like-body"><?php echo esc_html(get_post_meta(get_the_ID(), 'milenia-team-member-position', true)); ?></em>
			<?php endif; ?>

			<?php if($milenia_team_member_has_social_profiles && $this->attributes['milenia_team_members_social_icons'] == 'true') : ?>
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
				</ul>
			<?php endif; ?>
		</figcaption>
	</figure>
</div>
<!-- - - - - - - - - - - - - - End of Team Member - - - - - - - - - - - - - - - - -->
