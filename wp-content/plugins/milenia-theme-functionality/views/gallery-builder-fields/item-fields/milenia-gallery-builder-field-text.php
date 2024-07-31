<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}


?>

<input type="text"
       class="widefat"
       name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][slides][%d][%s]', $slide_id, $field['name'])); ?>"
       value="<?php echo esc_attr(isset($slide[$field['name']]) ? $slide[$field['name']] : '');  ?>">
