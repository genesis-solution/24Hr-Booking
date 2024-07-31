<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}
?>

<input type="number" class="widefat milenia-styled-input" min="1" name="<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][%s]', $name)); ?>"
    <?php if(isset($milenia_gallery_builder['sliders'][$name])) : ?>
        value="<?php echo esc_attr($milenia_gallery_builder['sliders'][$name]); ?>"
    <?php elseif(isset($value) && !empty($value)) : ?>
        value="<?php echo esc_attr($value); ?>"
    <?php endif; ?>
>
