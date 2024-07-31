<?php
/**
* The main template file that is responsible to display the site footer.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia, $MileniaLayout, $milenia_settings;
$MileniaHelper = $Milenia->helper();
$milenia_footer = $MileniaLayout->getFooter();
?>
            	</div>
            </div>
			<?php if($MileniaLayout->hasFooter()) : ?>
				<!--================ Footer ================-->
				<footer id="milenia-footer" class="milenia-footer">
					<?php foreach($milenia_footer as $milenia_fs_id => $milenia_fs_data) : ?>
						<?php
							if($milenia_fs_id == 'footer-copyright-section') continue;
							$milenia_fs_classes = array('milenia-footer-row');
							$milenia_fs_grid_classes = array('milenia-grid', $milenia_fs_data['columns'], $milenia_fs_data['responsive-breakpoint']);
							$milenia_fs_inner_classes = array('milenia-footer-row--inner');
                            $milenia_widget_settings = $milenia_fs_data['widgets-settings'];
							$milenia_fs_inner_style = '';

							if(is_array($milenia_fs_data['color-settings-states']) && in_array('background', $milenia_fs_data['color-settings-states']))
							{
								array_push($milenia_fs_classes, sprintf('milenia-colorizer--scheme-%s', $milenia_fs_data['bg']));
							}

							if(intval($milenia_fs_data['padding-x']) != 1)
							{
								array_push($milenia_fs_inner_classes, 'milenia-footer-row-inner--no-offsets');
							}

							if(isset($milenia_fs_data['padding-y']) && is_array($milenia_fs_data['padding-y']))
							{
								if(intval($milenia_fs_data['padding-y']['padding-top']) < 30 && intval($milenia_fs_data['padding-y']['padding-bottom']) < 30) {
									$milenia_fs_classes[] = 'milenia-footer-row--thin';
								}

								if(isset($milenia_fs_data['padding-y']['padding-top']))
								{
									$milenia_fs_inner_style .= sprintf('padding-top: %s;', $milenia_fs_data['padding-y']['padding-top']);
								}
								if(isset($milenia_fs_data['padding-y']['padding-bottom']))
								{
									$milenia_fs_inner_style .= sprintf('padding-bottom: %s;', $milenia_fs_data['padding-y']['padding-bottom']);
								}
							}

							if(intval($milenia_fs_data['border-top']) == 1)
							{
								array_push($milenia_fs_classes, sprintf('milenia-colorizer--scheme-%s', $milenia_fs_data['bg']));
							}

							if(intval($milenia_fs_data['widgets-border']) == 1)
							{
								array_push($milenia_fs_classes, 'milenia-footer-row--widget-border');
							}

							if(intval($milenia_fs_data['uppercased-titles']) == 1)
							{
								array_push($milenia_fs_classes, 'milenia-footer-row--uppercased-titles');
							}

							if(intval($milenia_fs_data['large-offset']) == 1)
							{
								array_push($milenia_fs_classes, 'milenia-footer-row--titles-large-offset');
							}
						?>
						<?php if(is_active_sidebar($milenia_fs_data['src'])) : ?>
							<!--================ Footer row ================-->
							<div id="milenia-<?php echo esc_attr($milenia_fs_id); ?>" class="<?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_fs_classes)); ?>"
								<?php if($milenia_fs_data['bg'] == 'custom' && is_array($milenia_fs_data['color-settings-states']) && in_array('background', $milenia_fs_data['color-settings-states'])) : ?>
									data-bg-color="<?php echo esc_attr($milenia_fs_data['bg-custom']); ?>"
								<?php endif; ?>>

								<?php if(intval($milenia_fs_data['full-width']) != 1) : ?>
									<div class="container">
								<?php endif; ?>
										<div class="<?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_fs_inner_classes)); ?>" style="<?php echo esc_attr($milenia_fs_inner_style); ?>">
											<div class="<?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_fs_grid_classes)); ?>">
												<?php dynamic_sidebar($milenia_fs_data['src']); ?>
											</div>
										</div>
								<?php if(intval($milenia_fs_data['full-width']) != 1) : ?>
									</div>
								<?php endif; ?>
							</div>
							<!--================ End of Footer row ================-->
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if(isset($milenia_footer['footer-copyright-section'])) : 
						$milenia_copyright = $milenia_footer['footer-copyright-section'];
						$milenia_copyright_classes = array('milenia-footer-row', 'milenia-footer-row--thin', 'milenia-colorizer--scheme-' . $milenia_copyright['bg']);
					?>
						<!--================ Footer row ================-->
						<div class="<?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_copyright_classes)); ?>"
							<?php if($milenia_copyright['bg'] == 'custom') : ?>
								data-bg-color="<?php echo esc_attr($milenia_copyright['bg-custom']); ?>"
								style="color: <?php echo esc_attr($milenia_copyright['text-color']); ?>"
							<?php endif; ?>>
							<?php if(intval($milenia_copyright['full-width']) != 1) : ?>
								<div class="container">
							<?php endif; ?>
								<div class="milenia-footer-row--inner" style="border-color: <?php echo esc_attr($milenia_copyright['border-top-color']); ?>">
									<div class="milenia-grid milenia-grid--cols-1">
										<?php if(isset($milenia_copyright['text']) && !empty($milenia_copyright['text'])) : ?>
											<div class="milenia-grid-item milenia-widget text-center"><?php echo esc_html($milenia_copyright['text']) ?></div>
										<?php endif; ?>
									</div>
								</div>
							<?php if(intval($milenia_copyright['full-width']) != 1) : ?>
								</div>
							<?php endif; ?>
						</div>
						<!--================ End of Footer row ================-->
					<?php endif; ?>
				</footer>
				<!--================ End of Footer ================-->
			<?php endif; ?>
        </div>
        <!--================ End of Page Wrapper ================-->
        <?php
			/**
			 * Hook for the append some content to the body.
			 *
			 * @hooked
			 */
			do_action( 'milenia_body_append' );

			wp_footer();
		?>
    </body>
</html>