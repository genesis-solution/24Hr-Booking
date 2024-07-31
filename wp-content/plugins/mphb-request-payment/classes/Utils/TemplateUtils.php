<?php

namespace MPHB\Addons\RequestPayment\Utils;

class TemplateUtils
{
    public static function addCustomTemplatesPath()
    {
        add_filter('mphb_get_template_part', array(__CLASS__, 'onFilterTemplatePaths'), 10, 3);
    }

    /**
     * @param string $template Path to template file.
     * @param string $slug Template slug/name.
     * @param array $atts
     * @return string
     */
    public static function onFilterTemplatePaths($template, $slug, $atts)
    {
        // Get default template from plugin, but don't replace theme templates
        if (empty($template) && file_exists( MPHBRP()->pathTo("templates/{$slug}.php") )) {
            $template = MPHBRP()->pathTo("templates/{$slug}.php");
        }

        return $template;
    }
}
