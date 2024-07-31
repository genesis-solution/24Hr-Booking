<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}
?>
<?php wp_nonce_field( $this->action_gallery_builder, 'milenia_gallery_builder_nonce' ); ?>

<div class='padding-cont'>

	<div class='slider_option slider_type'>

		<div class='hideable-area'>

			<div class='settings_media'>

				<div class='append_block'>
					<ul class='sortable-img-items'>
						<?php if(isset($milenia_gallery_builder['sliders']) && isset($milenia_gallery_builder['sliders']['slides'])) :
							$milenia_items_template_data = array();
							$milenia_items_template_data['slides'] = $milenia_gallery_builder['sliders']['slides'];

							if(isset($args['item_fields']) && is_array($args['item_fields']) && count($args['item_fields'])) {
								$milenia_items_template_data['item_fields'] = $args['item_fields'];
							}
						?>
							<?php $this->view($this->path('views/milenia-gallery-builder-items.php'), $milenia_items_template_data); ?>
						<?php endif; ?>
					</ul>
				</div>

				<div class='clear'></div>

			</div><!--/ .settings_media-->

			<div class='settings_box'>

				<div class='settings_box_title'>
					<h2><?php esc_html_e('Media Settings', 'milenia-app-textdomain') ?></h2>
				</div>

				<div class='settings_box_content'>

					<?php if(is_array($args['supports']) && count($args['supports'])) : ?>

						<div class='available_media'>

							<?php if(in_array('image', $args['supports'])) : ?>

								<div class='button-item'>
									<button type="button" data-type="image" class="button button-secondary add_image_available_media"><?php esc_html_e('Add Image', 'milenia-app-textdomain') ?></button>
								</div>

							<?php endif; ?>

							<?php if(in_array('video', $args['supports'])) : ?>

								<div class='button-item'>
									<button data-type="video" class="button button-secondary add_image_available_media"><?php esc_html_e('Add Video', 'milenia-app-textdomain') ?></button>
								</div>

							<?php endif; ?>

							<div class='clear'></div>

						</div><!--/ .available_media-->

					<?php endif; ?>

					<?php if(is_array($args['fields']) && count($args['fields'])) : ?>
						<div class="options-container">
							<?php foreach($args['fields'] as $field) : $field['milenia_gallery_builder'] = $milenia_gallery_builder; ?>
								<?php if(!isset($field['type']) || !isset($field['name'])) continue; ?>
								<?php if(!is_file($this->path(sprintf('views/gallery-builder-fields/milenia-gallery-builder-field-%s.php', $field['type']))) || !is_readable($this->path(sprintf('views/gallery-builder-fields/milenia-gallery-builder-field-%s.php', $field['type'])))) continue; ?>

								<!-- - - - - - - - - - - - - - Field - - - - - - - - - - - - - -->
								<div class='option-col option-type option-type-<?php echo esc_attr($field['type']); ?>'>
									<?php if(isset($field['title'])) : ?>
										<h2><?php echo esc_html($field['title']); ?></h2>
									<?php endif; ?>

									<?php $this->view($this->path(sprintf('views/gallery-builder-fields/milenia-gallery-builder-field-%s.php', $field['type'])), $field); ?>

									<?php if(isset($field['description'])) : ?>
										<div class="option-description">
											<?php echo esc_html($field['description']); ?>
										</div>
									<?php endif; ?>
								</div>
								<!-- - - - - - - - - - - - - - End of Field - - - - - - - - - - - - - -->
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

				</div><!--/ .settings_box_content-->
			</div><!--/ .settings_box-->
		</div><!--/ .hideable-area-->
	</div><!--/ .slider_option-->
</div>
