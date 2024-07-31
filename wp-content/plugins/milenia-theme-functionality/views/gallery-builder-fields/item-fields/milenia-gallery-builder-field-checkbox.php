<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

?>

<input type="checkbox"
       class="checkbox"
       name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s]', $slide_id, $field['name'])); ?>"
       <?php if(isset($slide[$field['name']])) : ?>checked<?php endif; ?>
       value="<?php echo esc_attr(isset($field['value']) ? $field['value'] : ''); ?>"> <?php _e('Yes', 'milenia-app-textdomain'); ?>
