<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}
?>

<select name='<?php echo esc_attr(sprintf('milenia_gallery_builder[sliders][%s]', $name)); ?>' class='strip_custom_select'>
    <?php if(isset($options) && is_array($options) && count($options)) : ?>

        <?php foreach ($options as $opt_name => $option) : ?>
            <option <?php echo ((isset($milenia_gallery_builder['sliders'][$name]) && $milenia_gallery_builder['sliders'][$name] == $opt_name) ? 'selected="selected"' : '') ?> value="<?php echo esc_attr($opt_name) ?>"><?php echo esc_html($option); ?></option>
        <?php endforeach; ?>

    <?php endif; ?>
</select>
