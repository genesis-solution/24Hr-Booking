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

$milenia_banners = $Milenia->getThemeOption('accommodation-banners-state', '0');
$milenia_other_rooms = $Milenia->getThemeOption('accommodation-other-rooms-state', '0');
$milenia_single_rooms_sections = $Milenia->getThemeOption('milenia-single-room-sections')['enabled'];

while ( have_posts() ) : the_post();
    if ( post_password_required() ) : ?>
        <div class="milenia-section">
            <?php echo get_the_password_form(); ?>
        </div>
        <?php
        return;
    endif;
?>
<div <?php post_class('milenia-entity-single milenia-entity--room milenia-entity--room-fullwidth'); ?>>
    <?php
	/**
     * @hooked \Milenia\App\ServiceProvider\MPHBServiceProvider::renderRevSliderGallery - 10
     * @hooked \Milenia\App\ServiceProvider\MPHBServiceProvider::renderBreadcrumbs - 20
     */
	do_action( 'milenia_mphb_render_single_room_type_fullwidth_before_content' );

	$sections = milenia_mphb_render_sections();
 	?>

	<?php foreach ( $milenia_single_rooms_sections as $key => $title ): ?>

			<?php
			$section = $sections[$key] ? $sections[$key] : '';
			if (!$section) continue;
			?>

			<?php
			$section_classes = array('milenia-section');

			if ( isset($section['classes']) && !empty($section['classes']) ) {
				foreach($section['classes'] as $cls) {
					$section_classes[] = $cls;
				}
			}
			?>

			<section class="<?php echo implode(' ', $section_classes) ?>">

				<div class="row">

					<div class="col-lg-3">
						<h4><?php echo sprintf('%s', esc_html__($title, 'milenia')); ?></h4>
					</div>

					<?php if ( 'description' == $key ): ?>

						<div class="col-lg-6">
							<?php do_action($section['action'][0]); ?>
						</div>

						<div class="col-lg-3">
							<?php do_action($section['action'][1], true); ?>
						</div>

					<?php else: ?>

						<div class="col-lg-9">
							<?php do_action($section['action'], $section['arg'][0], $section['arg'][1]); ?>
						</div>

					<?php endif; ?>

				</div><!--/ .row-->

			</section>

	<?php endforeach; ?>

	<?php if($milenia_other_rooms == '1') : ?>
		<!--================ Rates section ================-->
		<section class="milenia-section">
			<h3><?php esc_html_e('Other rooms', 'milenia'); ?></h3>

			<?php echo do_shortcode('[vc_milenia_rooms columns="milenia-grid--cols-3" style="milenia-entities--style-14" show_button_details="0" show_button_book="0" show_content="0" total_items="3" exc="'.get_the_ID().'"]'); ?>
		</section>
		<!--================ End of Rates section ================-->
	<?php endif; ?>

	<?php if($milenia_banners == '1') : ?>
		<?php
			$milenia_banner_1_image = $Milenia->getThemeOption('accomodation-banner-1-image', null);
			$milenia_banner_1_title = $Milenia->getThemeOption('accomodation-banner-1-title', null);
			$milenia_banner_1_content = $Milenia->getThemeOption('accomodation-banner-1-content', null);
			$milenia_banner_1_link_text = $Milenia->getThemeOption('accomodation-banner-1-link-text', null);
			$milenia_banner_1_link_url = $Milenia->getThemeOption('accomodation-banner-1-link-url', null);
			$milenia_banner_1_link_nofollow = $Milenia->getThemeOption('accomodation-banner-1-link-nofollow', null);
			$milenia_banner_1_link_target_blank = $Milenia->getThemeOption('accomodation-banner-1-link-target-blank', null);

			if(is_array($milenia_banner_1_image) && !empty($milenia_banner_1_image)) {
				$milenia_banner_1_image = array_shift($milenia_banner_1_image);
			}
			$milenia_banner_2_image = $Milenia->getThemeOption('accomodation-banner-2-image', null);
			$milenia_banner_2_title = $Milenia->getThemeOption('accomodation-banner-2-title', null);
			$milenia_banner_2_content = $Milenia->getThemeOption('accomodation-banner-2-content', null);
			$milenia_banner_2_link_text = $Milenia->getThemeOption('accomodation-banner-2-link-text', null);
			$milenia_banner_2_link_url = $Milenia->getThemeOption('accomodation-banner-2-link-url', null);
			$milenia_banner_2_link_nofollow = $Milenia->getThemeOption('accomodation-banner-2-link-nofollow', null);
			$milenia_banner_2_link_target_blank = $Milenia->getThemeOption('accomodation-banner-2-link-target-blank', null);

			if(is_array($milenia_banner_2_image) && !empty($milenia_banner_2_image)) {
				$milenia_banner_2_image = array_shift($milenia_banner_2_image);
			}
			$milenia_banner_3_image = $Milenia->getThemeOption('accomodation-banner-3-image', null);
			$milenia_banner_3_title = $Milenia->getThemeOption('accomodation-banner-3-title', null);
			$milenia_banner_3_content = $Milenia->getThemeOption('accomodation-banner-3-content', null);
			$milenia_banner_3_link_text = $Milenia->getThemeOption('accomodation-banner-3-link-text', null);
			$milenia_banner_3_link_url = $Milenia->getThemeOption('accomodation-banner-3-link-url', null);
			$milenia_banner_3_link_nofollow = $Milenia->getThemeOption('accomodation-banner-3-link-nofollow', null);
			$milenia_banner_3_link_target_blank = $Milenia->getThemeOption('accomodation-banner-3-link-target-blank', null);

			if(is_array($milenia_banner_3_image) && !empty($milenia_banner_3_image)) {
				$milenia_banner_3_image = array_shift($milenia_banner_3_image);
			}
		?>

		<div class="milenia-section milenia-section--stretched-content-no-px milenia-section--no-py">
			<!--================ Entities (Style 2) ================-->
			<div class="milenia-entities milenia-entities--style-2">
				<div class="milenia-grid milenia-grid--cols-3 milenia-grid--no-gutters">
					<div class="milenia-grid-item">
						<!--================ Entity ================-->
						<article class="milenia-entity milenia-entity--scheme-primary">
							<?php if(is_array($milenia_banner_1_image)) : ?>
								<div data-bg-image-src="<?php echo esc_attr($milenia_banner_1_image['full_url']); ?>" class="milenia-entity-media milenia-mb-0"></div>
							<?php endif; ?>

							<div class="milenia-entity-content milenia-aligner">
								<div class="milenia-aligner-outer">
									<div class="milenia-aligner-inner">
										<?php if(!empty($milenia_banner_1_title)) : ?>
											<header class="milenia-entity-header">
												<h2 class="milenia-entity-title"><a href="<?php echo esc_url($milenia_banner_1_link_url); ?>" class="milenia-color--unchangeable"><?php echo esc_html($milenia_banner_1_title); ?></a></h2>
											</header>
										<?php endif; ?>
										<?php if(!empty($milenia_banner_1_content)) : ?>
											<div class="milenia-entity-body">
												<p><?php echo esc_html($milenia_banner_1_content); ?></p>
											</div>
										<?php endif; ?>

										<?php if(!empty($milenia_banner_1_link_url)) : ?>
											<footer class="milenia-entity-footer">
												<a href="<?php echo esc_url($milenia_banner_1_link_url); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-inherit"><?php echo esc_html($milenia_banner_1_link_text); ?></a>
											</footer>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</article>
						<!--================ End of Entity ================-->
					</div>
					<div class="milenia-grid-item">
						<!--================ Entity ================-->
						<article class="milenia-entity milenia-entity--scheme-light">
							<?php if(is_array($milenia_banner_2_image)) : ?>
								<div data-bg-image-src="<?php echo esc_attr($milenia_banner_2_image['full_url']); ?>" class="milenia-entity-media milenia-mb-0"></div>
							<?php endif; ?>

							<div class="milenia-entity-content milenia-aligner">
								<div class="milenia-aligner-outer">
									<div class="milenia-aligner-inner">
										<?php if(!empty($milenia_banner_2_title)) : ?>
											<header class="milenia-entity-header">
												<h2 class="milenia-entity-title"><a href="<?php echo esc_url($milenia_banner_2_link_url); ?>" class="milenia-color--unchangeable"><?php echo esc_html($milenia_banner_2_title); ?></a></h2>
											</header>
										<?php endif; ?>
										<?php if(!empty($milenia_banner_2_content)) : ?>
											<div class="milenia-entity-body">
												<p><?php echo esc_html($milenia_banner_2_content); ?></p>
											</div>
										<?php endif; ?>

										<?php if(!empty($milenia_banner_2_link_url)) : ?>
											<footer class="milenia-entity-footer">
												<a href="<?php echo esc_url($milenia_banner_2_link_url); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-primary"><?php echo esc_html($milenia_banner_2_link_text); ?></a>
											</footer>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</article>
						<!--================ End of Entity ================-->
					</div>
					<div class="milenia-grid-item">
						<!--================ Entity ================-->
						<article class="milenia-entity milenia-entity--scheme-dark">
							<?php if(is_array($milenia_banner_3_image)) : ?>
								<div data-bg-image-src="<?php echo esc_attr($milenia_banner_3_image['full_url']); ?>" class="milenia-entity-media milenia-mb-0"></div>
							<?php endif; ?>

							<div class="milenia-entity-content milenia-aligner">
								<div class="milenia-aligner-outer">
									<div class="milenia-aligner-inner">
										<?php if(!empty($milenia_banner_3_title)) : ?>
											<header class="milenia-entity-header">
												<h2 class="milenia-entity-title"><a href="<?php echo esc_url($milenia_banner_3_link_url); ?>" class="milenia-color--unchangeable"><?php echo esc_html($milenia_banner_3_title); ?></a></h2>
											</header>
										<?php endif; ?>
										<?php if(!empty($milenia_banner_3_content)) : ?>
											<div class="milenia-entity-body">
												<p><?php echo esc_html($milenia_banner_3_content); ?></p>
											</div>
										<?php endif; ?>

										<?php if(!empty($milenia_banner_3_link_url)) : ?>
											<footer class="milenia-entity-footer">
												<a href="<?php echo esc_url($milenia_banner_3_link_url); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-inherit"><?php echo esc_html($milenia_banner_3_link_text); ?></a>
											</footer>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</article>
						<!--================ End of Entity ================-->
					</div>
				</div>
			</div>
			<!--================ End of Entities (Style 2) ================-->
		</div>
	<?php endif; ?>

    <?php do_action( 'milenia_mphb_render_single_room_type_fullwidth_after_content' ); ?>
</div>
<?php endwhile; ?>
