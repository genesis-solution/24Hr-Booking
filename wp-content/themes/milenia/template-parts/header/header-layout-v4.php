<?php
/**
* The template file that describes header v4.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia, $MileniaWeatherForecaster;

$MileniaHelper = $Milenia->helper();
$milenia_header_classes = array();
$milenia_main_section_classes = array();
$milenia_right_col_classes = array();
$milenia_left_col_classes = array();

$milenia_header_transparent = $Milenia->getThemeOption('milenia-header-transparent', '0', array(
	'overriden_by' => 'milenia-page-header-transparent',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_color_scheme = $Milenia->getThemeOption('milenia-header-transparentable-color-scheme', 'milenia-header--light', array(
	'overriden_by' => 'milenia-page-header-transparentable-color-scheme',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_content_width = $Milenia->getThemeOption('milenia-header-container', 'container-fluid', array(
	'overriden_by' => 'milenia-page-header-container',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_sticky = $Milenia->getThemeOption('milenia-header-sticky', '1', array(
	'overriden_by' => 'milenia-page-header-sticky',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_sticky_responsive_breakpoint = $Milenia->getThemeOption('milenia-header-sticky-responsive-breakpoint', array('milenia-header-section--sticky-xl'), array(
	'overriden_by' => 'milenia-page-header-sticky-responsive-breakpoint',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_right_col_elements = $Milenia->getThemeOption('milenia-header-right-column-elements', array('search', 'languages'), array(
	'overriden_by' => 'milenia-page-header-right-column-elements',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_left_col_elements = $Milenia->getThemeOption('milenia-header-left-column-elements', array('weather', 'hidden-sidebar-btn'), array(
	'overriden_by' => 'milenia-page-header-left-column-elements',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_action_btn_text = $Milenia->getThemeOption('milenia-header-action-btn-text', '', array(
	'overriden_by' => 'milenia-page-header-action-btn-text',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_action_btn_url = $Milenia->getThemeOption('milenia-header-action-btn-url', '', array(
	'overriden_by' => 'milenia-page-header-action-btn-url',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_action_btn_target = $Milenia->getThemeOption('milenia-header-action-btn-target', '', array(
	'overriden_by' => 'milenia-page-header-action-btn-target',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

$milenia_header_action_btn_nofollow = $Milenia->getThemeOption('milenia-header-action-btn-nofollow', '', array(
	'overriden_by' => 'milenia-page-header-action-btn-nofollow',
	'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
));

if($milenia_header_sticky == '1') {
	for ( $i = 0; $i < count($milenia_header_sticky_responsive_breakpoint); $i++ ) {
		array_push($milenia_main_section_classes, $milenia_header_sticky_responsive_breakpoint[$i]);
	}
}

if($milenia_header_content_width == 'container') {
    array_push($milenia_left_col_classes, 'milenia-header-col--padding-no-left-md');
    array_push($milenia_right_col_classes, 'milenia-header-col--padding-no-right-md');
}

if($milenia_header_transparent == '0') {
    array_push($milenia_header_classes, $milenia_header_color_scheme);
}
else {
    array_push($milenia_header_classes, 'milenia-header--transparent');
    array_push($milenia_header_classes, 'milenia-header--transparent-single');
}
?>
<!--================ Header ================-->
<header id="milenia-header" class="milenia-header <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_header_classes)); ?>">
    <!--================ Section ================-->
    <div class="milenia-header-section-md milenia-header-section--has-navigation <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_main_section_classes)); ?>">
		<div class="<?php echo esc_attr($milenia_header_content_width); ?>">
	        <!--================ Column ================-->
	        <div class="milenia-header-col milenia-header-col-md-4 milenia-aligner milenia-aligner--valign-middle milenia-header-col--padding-default milenia-header-col--padding-small-md milenia-header-col--content-align-left-md <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_left_col_classes)); ?>">
	            <div class="milenia-aligner-outer">
	                <div class="milenia-aligner-inner">
	                    <div class="milenia-header-items">
							<?php if(!empty($milenia_header_left_col_elements)) : ?>
								<?php foreach($milenia_header_left_col_elements as $element) : ?>
									<?php switch($element) {
											case 'search' : ?>
												<!--================ Search ================-->
												<div>
													<button type="button" data-arctic-modal="#search-modal" class="milenia-icon-btn">
														<span class="icon icon-magnifier"></span>
													</button>
													<!--================ Search Modal ================-->
													<div class="milenia-d-none">
														<div id="search-modal" class="milenia-modal milenia-modal--search">
															<button type="button" class="milenia-icon-btn arcticmodal-close">
																<span class="icon icon-cross"></span>
															</button>
															<h3><?php esc_html_e('Find Everything', 'milenia'); ?></h3>
															<?php get_search_form(); ?>
														</div>
													</div>
													<!--================ End of Search Modal ================-->
												</div>
												<!--================ End of Search ================-->
											<?php break;
											case 'menu-btn': ?>
												<div>
													<button type="button" data-sidebar-hidden="#milenia-sidebar-vertical-navigation" aria-expanded="false" aria-controls="milenia-sidebar-vertical-navigation" aria-haspopup="true" class="milenia-header-menu-btn milenia-sidebar-hidden-btn"><span class="icon icon-menu"></span><?php esc_html_e('Menu', 'milenia'); ?></button>
												</div>
											<?php break;
											case 'languages' : ?>
												<div>
													<?php milenia_language_switcher(); ?>
												</div>
											<?php break;
											case 'action-btn' : ?>
												<div>
													<a href="<?php echo esc_url($milenia_header_action_btn_url); ?>"
													   class="milenia-btn--scheme-primary milenia-btn milenia-btn--big"
													   target="<?php echo esc_attr($milenia_header_action_btn_target == '1' ? '_blank' : '_self'); ?>"
													   <?php if($milenia_header_action_btn_nofollow) : ?>rel="nofollow"<?php endif; ?>
													><?php echo esc_html($milenia_header_action_btn_text); ?></a>
												</div>
											<?php break;
											case 'weather' : ?>
												<?php if(isset($MileniaWeatherForecaster)) : ?>
													<div class="milenia-weather-indicator">
														<div class="milenia-weather-indicator-celsius">
															<span class="icon <?php echo esc_attr($MileniaWeatherForecaster->getIconClass()); ?>"></span><sup><?php echo esc_html($MileniaWeatherForecaster->getCelsiusValue()); ?>&#176;C/</sup><span class="milenia-weather-indicator-btn">&#176;F</span>
														</div>
														<div class="milenia-weather-indicator-fahrenheit">
															<span class="icon <?php echo esc_attr($MileniaWeatherForecaster->getIconClass()); ?>"></span><sup><?php echo esc_html($MileniaWeatherForecaster->getFahrenheitValue()); ?>&#176;F/</sup><span class="milenia-weather-indicator-btn">&#176;C</span>
														</div>
													</div>
												<?php endif; ?>
											<?php break;
											case 'hidden-sidebar-btn' : ?>
												<div>
													<button type="button" data-sidebar-hidden="#milenia-sidebar-hidden" aria-expanded="false" aria-controls="milenia-sidebar-hidden" aria-haspopup="true" class="milenia-header-menu-btn milenia-sidebar-hidden-btn"><span class="icon icon-menu"></span></button>
												</div>
											<?php break;
										} ?>
								<?php endforeach; ?>
							<?php endif; ?>
	                    </div>
	                </div>
	            </div>
	        </div>
	        <!--================ End of Column ================-->

	        <!--================ Column ================-->
	        <div class="milenia-header-col milenia-header-col-md-4 milenia-aligner milenia-aligner--valign-middle milenia-header-col--padding-default milenia-header-col--padding-small-md">
	            <div class="milenia-aligner-outer">
	                <div class="milenia-aligner-inner">
	                    <div class="milenia-header-items">
							<div>
			                    <?php milenia_logo(); ?>
							</div>
	                    </div>
	                </div>
	            </div>
	        </div>
	        <!--================ End of Column ================-->

	        <!--================ Column ================-->
	        <div class="milenia-header-col milenia-header-col-md-4 milenia-aligner milenia-aligner--valign-middle milenia-header-col--content-align-right-md milenia-header-col--padding-default milenia-header-col--padding-small-md <?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_right_col_classes)); ?>">
	            <div class="milenia-aligner-outer">
	                <div class="milenia-aligner-inner">
	                    <div class="milenia-header-items">
							<?php if(!empty($milenia_header_right_col_elements)) : ?>
								<?php foreach($milenia_header_right_col_elements as $element) : ?>
									<?php switch($element) {
											case 'search' : ?>
												<!--================ Search ================-->
												<div>
													<button type="button" data-arctic-modal="#search-modal" class="milenia-icon-btn">
														<span class="icon icon-magnifier"></span>
													</button>
													<!--================ Search Modal ================-->
													<div class="milenia-d-none">
														<div id="search-modal" class="milenia-modal milenia-modal--search">
															<button type="button" class="milenia-icon-btn arcticmodal-close">
																<span class="icon icon-cross"></span>
															</button>
															<h3><?php esc_html_e('Find Everything', 'milenia'); ?></h3>
															<?php get_search_form(); ?>
														</div>
													</div>
													<!--================ End of Search Modal ================-->
												</div>
												<!--================ End of Search ================-->
											<?php break;
											case 'menu-btn': ?>
												<div>
													<button type="button" data-sidebar-hidden="#milenia-sidebar-vertical-navigation" aria-expanded="false" aria-controls="milenia-sidebar-vertical-navigation" aria-haspopup="true" class="milenia-header-menu-btn milenia-sidebar-hidden-btn"><span class="icon icon-menu"></span><?php esc_html_e('Menu', 'milenia'); ?></button>
												</div>
											<?php break;
											case 'languages' : ?>
												<div>
													<?php milenia_language_switcher(); ?>
												</div>
											<?php break;
											case 'action-btn' : ?>
												<div>
													<a href="<?php echo esc_url($milenia_header_action_btn_url); ?>"
													   class="milenia-btn--scheme-primary milenia-btn milenia-btn--big"
													   target="<?php echo esc_attr($milenia_header_action_btn_target == '1' ? '_blank' : '_self'); ?>"
													   <?php if($milenia_header_action_btn_nofollow) : ?>rel="nofollow"<?php endif; ?>
													><?php echo esc_html($milenia_header_action_btn_text); ?></a>
												</div>
											<?php break;
											case 'weather' : ?>
												<?php if(isset($MileniaWeatherForecaster)) : ?>
													<div class="milenia-weather-indicator">
														<div class="milenia-weather-indicator-celsius">
															<span class="icon <?php echo esc_attr($MileniaWeatherForecaster->getIconClass()); ?>"></span><sup><?php echo esc_html($MileniaWeatherForecaster->getCelsiusValue()); ?>&#176;C/</sup><span class="milenia-weather-indicator-btn">&#176;F</span>
														</div>
														<div class="milenia-weather-indicator-fahrenheit">
															<span class="icon <?php echo esc_attr($MileniaWeatherForecaster->getIconClass()); ?>"></span><sup><?php echo esc_html($MileniaWeatherForecaster->getFahrenheitValue()); ?>&#176;F/</sup><span class="milenia-weather-indicator-btn">&#176;C</span>
														</div>
													</div>
												<?php endif; ?>
											<?php break;
											case 'hidden-sidebar-btn' : ?>
												<div>
													<button type="button" data-sidebar-hidden="#milenia-sidebar-hidden" aria-expanded="false" aria-controls="milenia-sidebar-hidden" aria-haspopup="true" class="milenia-header-menu-btn milenia-sidebar-hidden-btn"><span class="icon icon-menu"></span></button>
												</div>
											<?php break;
										} ?>
								<?php endforeach; ?>
							<?php endif; ?>
	                    </div>
	                </div>
	            </div>
	        </div>
	        <!--================ End of Column ================-->
		</div>
    </div>
    <!--================ End of Section ================-->
</header>
<!--================ End of Header ================-->
