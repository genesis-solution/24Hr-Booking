<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class TabsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
{
    /**
     * Returns a parameters array of the shortcode.
     *
     * @access public
     * @return array
     */
    public function getParams()
    {
        return array(
            'name' => esc_html__('Tabs', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_tabs',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates tab panels.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'param_group',
                    'heading' => esc_html__('Panels', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_panels',
                    'description' => esc_html__('Here you can create tab panels.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_tab_panel_title',
                            'description' => esc_html__('Enter text used as a title of the tab panel.', 'milenia-app-textdomain'),
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textarea',
                            'heading' => esc_html__('Descrption', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_tab_panel_description',
                            'description' => esc_html__('Enter text used as content of the tab panel. You can use the following HTML tags in this field', 'milenia-app-textdomain') . ':' . esc_attr( '<i></i>, <u></u>, <b></b>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <ul></ul>, <ol></ol>, <li></li>' )
                        )
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Active panel number', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_active_panel_number',
                    'value' => '1',
                    'admin_label' => true,
                    'description' => esc_html__('Enter number of opened panel by default.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Tabs type', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_type',
                    'admin_label' => true,
                    'value' => array(
                        esc_html__('Horizontal', 'milenia-app-textdomain') => 'horizontal',
                        esc_html__('Vertical', 'milenia-app-textdomain') => 'vertical'
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Vertical tabs breakpoint', 'milenia-app-textdomain'),
                    'description' => esc_html__('The viewport width value in which the vertical tabs transforms to horizontal.', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_vertical_breakpoint',
                    'admin_label' => true,
                    'value' => array(
                        '992px' => 'lg',
                        '1600px' => 'xxxl',
                        '1380px' => 'xxl',
                        '1200px' => 'xl',
                        '576px' => 'sm',
                        '0px' => 'none'
                    ),
                    'dependency' => array(
                        'element' => 'milenia_tabs_type',
                        'value' => 'vertical'
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Duration of panels animation', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_panels_duration',
                    'value' => 600,
                    'admin_label' => true,
                    'description' => esc_html__('In milliseconds.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Display style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-tabs--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-tabs--style-2'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Easing of panels animation', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_tabs_panels_easing',
                    'value' => array(
                        'linear',
                        'swing',
                        'easeInQuad',
                        'easeOutQuad',
                        'easeInOutQuad',
                        'easeInCubic',
                        'easeOutCubic',
                        'easeInOutCubic',
                        'easeInQuart',
                        'easeOutQuart',
                        'easeInOutQuart',
                        'easeInQuint',
                        'easeOutQuint',
                        'easeInOutQuint',
                        'easeInSine',
                        'easeOutSine',
                        'easeInOutSine',
                        'easeInExpo',
                        'easeOutExpo',
                        'easeInOutExpo',
                        'easeInCirc',
                        'easeOutCirc',
                        'easeInOutCirc',
                        'easeInElastic',
                        'easeOutElastic',
                        'easeInOutElastic',
                        'easeInBack',
                        'easeOutBack',
                        'easeInOutBack',
                        'easeInBounce',
                        'easeOutBounce',
                        'easeInOutBounce'
                    ),
                    'description' => esc_html__('Timing function for the animation of panels.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                vc_map_add_css_animation(),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Extra class name', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_extra_class_name',
                    'admin_label' => true,
                    'description' => esc_html__('Style particular content element differently - add a class name and refer to it in custom CSS.', 'milenia-app-textdomain')
                )
            )
        );
    }

    /**
     * Appends the shortcode into the Visual Composer.
     *
     * @access public
     * @return void
     */
    public function register()
    {
        add_shortcode('vc_milenia_tabs', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_tabs_panels'] = vc_param_group_parse_atts( $atts['milenia_tabs_panels'] );

        $this->attributes = shortcode_atts( array(
            'milenia_widget_title' => '',
            'milenia_tabs_panels' => array(),
            'milenia_tabs_style' => 'milenia-tabs--style-1',
            'milenia_tabs_active_panel_number' => 1,
            'milenia_tabs_type' => 'horizontal',
            'milenia_tabs_vertical_breakpoint' => 'lg',
            'milenia_tabs_panels_duration' => 500,
            'milenia_tabs_panels_easing' => 'linear',
            'css_animation' => 'none',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_tabs' );

        wp_enqueue_script('milenia-tabs');

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-tabs');
        $style = $this->throughWhiteList($this->attributes['milenia_tabs_style'], array('milenia-tabs--style-1', 'milenia-tabs--style-2'), 'milenia-tabs--style-1');
        $type = $this->throughWhiteList($this->attributes['milenia_tabs_type'], array('horizontal', 'vertical'), 'horizontal');
        $vertical_breakpoint = $this->throughWhiteList($this->attributes['milenia_tabs_vertical_breakpoint'], array('xxxl', 'xxl', 'xl', 'lg', 'md', 'sm', 'none'), 'lg');
        $duration = is_numeric($this->attributes['milenia_tabs_panels_duration']) ? intval($this->attributes['milenia_tabs_panels_duration']) : 600;
        $active_panel_index = is_numeric($this->attributes['milenia_tabs_active_panel_number']) ? intval($this->attributes['milenia_tabs_active_panel_number']) : 1;
        $easing = $this->throughWhiteList($this->attributes['milenia_tabs_panels_easing'], array(
            'linear',
            'swing',
            'easeInQuad',
            'easeOutQuad',
            'easeInOutQuad',
            'easeInCubic',
            'easeOutCubic',
            'easeInOutCubic',
            'easeInQuart',
            'easeOutQuart',
            'easeInOutQuart',
            'easeInQuint',
            'easeOutQuint',
            'easeInOutQuint',
            'easeInSine',
            'easeOutSine',
            'easeInOutSine',
            'easeInExpo',
            'easeOutExpo',
            'easeInOutExpo',
            'easeInCirc',
            'easeOutCirc',
            'easeInOutCirc',
            'easeInElastic',
            'easeOutElastic',
            'easeInOutElastic',
            'easeInBack',
            'easeOutBack',
            'easeInOutBack',
            'easeInBounce',
            'easeOutBounce',
            'easeInOutBounce'
        ), 'linear' );

        $items_template = array();
        $nav_items_template = array();
        $container_classes = array();
        $tabs_element_classes = array($style);

        if(!empty($this->attributes['milenia_extra_class_name'])) {
            array_push($container_classes, $this->attributes['milenia_extra_class_name']);
        }
        if($this->attributes['css_animation'] == 'none')
        {
            array_push($container_classes, 'milenia-visible');
        }

        if($type == 'vertical')
        {
            if($vertical_breakpoint == 'none')
            {
                array_push($tabs_element_classes, 'milenia-tabs--tour-sections');
            }
            else
            {
                array_push($tabs_element_classes, sprintf('milenia-tabs--tour-sections-%s', $vertical_breakpoint));
            }
        }

        if( count($this->attributes['milenia_tabs_panels']) && count($this->attributes['milenia_tabs_panels'][0]) ) {
            foreach($this->attributes['milenia_tabs_panels'] as $index => $panel) {
                array_push($nav_items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-tabs-nav-item.tpl'), array(
                    '${panel_index}' => esc_attr(sprintf('%s-%d', $this->unique_id, $index)),
                    '${panel_title}' => esc_html($panel['milenia_tab_panel_title']),
                    '${panel_active_class}' => sanitize_html_class(($index + 1 == $active_panel_index) ? 'milenia-active' : ''),
                    '${panel_selected}' => esc_attr(($index + 1 == $active_panel_index) ? 'true' : 'false')
                )));

                array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-tabs-item.tpl'), array(
                    '${panel_index}' => esc_attr(sprintf('%s-%d', $this->unique_id, $index)),
                    '${panel_description}' => wpautop( wp_kses( $panel['milenia_tab_panel_description'], array(
                        'i' => array(),
                        'u' => array(),
                        'b' => array(),
                        'strong' => array(),
                        'p' => array(),
                        's' => array(),
                        'q' => array(),
                        'blockquote' => array(),
                        'cite' => array(),
                        'ul' => array(
                            'type' => true
                        ),
                        'ol' => array(
                            'type' => true
                        ),
                        'li' => array()
                    ) ) )
                )));
            }
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-tabs-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${items}' => implode("\r\n", $items_template),
            '${nav_items}' => implode("\r\n", $nav_items_template),
            '${panels_animation_easing}' => esc_js($easing),
            '${panels_animation_duration}' => esc_js($duration),
            '${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
            '${tabs_classes}' => $this->sanitizeHtmlClasses($tabs_element_classes),
            '${css_animation}' => esc_attr($this->attributes['css_animation'])
        ));
    }
}
?>
