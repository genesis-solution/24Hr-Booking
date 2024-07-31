<?php

if (!defined('ABSPATH')) {
    exit;
}

add_filter('mphb_create_trigger_date_field', '_mphb_notifier_create_trigger_date_field', 10, 4);
add_filter('mphb_get_template_part', '_mphb_notifier_get_template_path', 10, 2);

/**
 * Callback for filter "mphb_create_trigger_date_field".
 *
 * @param \MPHB\Admin\Fields\InputField|null $instance
 * @param string $name
 * @param array $args
 * @param mixed $value
 * @return \MPHB\Notifier\Admin\TriggerDateField
 *
 * @since 1.0
 */
function _mphb_notifier_create_trigger_date_field($instance, $name, $args, $value)
{
    return new \MPHB\Notifier\Admin\Fields\TriggerDateField($name, $args, $value);
}

/**
 * Callback for filter "mphb_get_template_part".
 *
 * @param string $template
 * @param string $slug
 * @return string
 *
 * @since 1.0
 */
function _mphb_notifier_get_template_path($template, $slug)
{
    if (empty($template) && $slug == 'emails/notification-default') {
        $template = \MPHB\Notifier\PLUGIN_DIR . 'templates/emails/notification-default.php';
    }

    return $template;
}
