<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}
?>

<textarea rows="<?php echo esc_attr(isset($field['rows']) ? $field['rows'] : 3); ?>"
          maxlength="<?php echo esc_attr(isset($field['max']) ? $field['max'] : 240); ?>"
          class="widefat"
          name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s]', $slide_id, $field['name'])); ?>"><?php echo esc_html(isset($slide[$field['name']]) ? $slide[$field['name']] : '') ?></textarea>
