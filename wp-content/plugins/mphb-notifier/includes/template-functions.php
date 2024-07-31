<?php

/**
 * @param array $options
 * @param mixed $selected
 * @return string
 *
 * @since 1.0
 */
function mphb_notifier_tmpl_render_select_options($options, $selected)
{
    $output = '';

    foreach ($options as $value => $label) {
        $output .= '<option value="' . esc_attr($value) . '"' . selected($selected, $value, false) . '>';
            $output .= esc_html($label);
        $output .= '</option>';
    }

    return $output;
}
