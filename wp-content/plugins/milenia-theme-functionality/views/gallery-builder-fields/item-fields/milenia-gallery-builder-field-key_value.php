<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

$field_name = $field['name'];
$current_index = 0;

if(isset($slide[$field_name]) && is_array($slide[$field_name]) && count($slide[$field_name])) :
?>
	<?php foreach($slide[$field_name] as $current_index => $meta) : $current_index = $current_index; ?>
		<div class="popup-modal-key-value-row">
			<div class="popup-modal-key-value-col">
				<label for="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 0)); ?>"><?php esc_html_e('Key', 'milenia-app-textdomain'); ?></label>
				<input id="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 0)); ?>"
					   type="text"
				       class="widefat"
				       name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s][%d][]', $slide_id, $field_name, $current_index)); ?>"
				       value="<?php echo esc_attr(isset($slide[$field_name]) && isset($slide[$field_name][$current_index]) && isset($slide[$field_name][$current_index][0]) ? $slide[$field_name][$current_index][0] : '');  ?>">
			</div>

			<div class="popup-modal-key-value-col">
				<label for="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 1)); ?>"><?php esc_html_e('Value', 'milenia-app-textdomain'); ?></label>
				<input id="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 1)); ?>"
					   type="text"
				       class="widefat"
				       name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s][%d][]', $slide_id, $field_name, $current_index)); ?>"
				       value="<?php echo esc_attr(isset($slide[$field_name]) && isset($slide[$field_name][$current_index]) && isset($slide[$field_name][$current_index][1]) ? $slide[$field_name][$current_index][1] : '');  ?>">
			</div>

			<div class="popup-modal-key-value-col popup-modal-key-value-col-button">
				<button type="button" data-action="remove" class="button button-primary popup-modal-manage-key-value-row"><i class="dashicons dashicons-trash"></i></button>
			</div>
		</div>
	<?php endforeach; ?>
<?php else : ?>

	<div class="popup-modal-key-value-row">
		<div class="popup-modal-key-value-col">
			<label for="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 0)); ?>"><?php esc_html_e('Key', 'milenia-app-textdomain'); ?></label>
			<input id="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 0)); ?>"
				   type="text"
			       class="widefat"
			       name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s][%d][]', $slide_id, $field_name, $current_index)); ?>"
			       value="<?php echo esc_attr(isset($slide[$field_name]) && isset($slide[$field_name][$current_index]) && isset($slide[$field_name][$current_index][0]) ? $slide[$field_name][$current_index][0] : '');  ?>">
		</div>

		<div class="popup-modal-key-value-col">
			<label for="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 1)); ?>"><?php esc_html_e('Value', 'milenia-app-textdomain'); ?></label>
			<input id="<?php echo esc_attr(sprintf('%s-%d-%d', $field_name, $current_index, 1)); ?>"
				   type="text"
			       class="widefat"
			       name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s][%d][]', $slide_id, $field_name, $current_index)); ?>"
			       value="<?php echo esc_attr(isset($slide[$field_name]) && isset($slide[$field_name][$current_index]) && isset($slide[$field_name][$current_index][1]) ? $slide[$field_name][$current_index][1] : '');  ?>">
		</div>

		<div class="popup-modal-key-value-col popup-modal-key-value-col-button">
			<button type="button" data-action="remove" class="button button-primary popup-modal-manage-key-value-row"><i class="dashicons dashicons-trash"></i></button>
		</div>
	</div>

<?php endif; ?>
<button type="button"
		data-action="add"
		class="button button-primary popup-modal-manage-key-value-row"
		data-current-index="<?php echo esc_attr($current_index); ?>"
		data-id-placeholder="<?php echo esc_attr(sprintf('%s-@globalindex@-@index@', $field_name)); ?>"
		data-name-placeholder="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s][@index@][]', $slide_id, $field_name)); ?>">
		<i class="dashicons dashicons-plus"></i> <?php esc_html_e('Add More', 'milenia-app-textdomain'); ?>
</button>
