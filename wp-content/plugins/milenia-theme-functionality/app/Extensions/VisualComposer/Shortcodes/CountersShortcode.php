<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class CountersShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Counters', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_counters',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a sequence of counters.', 'milenia-app-textdomain'),
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
                    'heading' => esc_html__('Counters', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_counters',
                    'description' => esc_html__('Here you can create counters.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_counter_title',
                            'description' => esc_html__('Enter text used as a title of the counter.', 'milenia-app-textdomain'),
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Value', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_counter_value',
                            'description' => esc_html__('Enter value of the counter.', 'milenia-app-textdomain'),
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'colorpicker',
                            'heading' => esc_html__('Value color', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_counter_value_color',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'colorpicker',
                            'heading' => esc_html__('Text color', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_counter_text_color',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'iconpicker',
                            'heading' => esc_html__('Icon', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_counter_icon',
                            'description' => esc_html__('Choose an icon.', 'milenia-app-textdomain'),
                            'admin_label' => false,
                            'settings' => array(
                                'emptyIcon' => true, // default true, display an "EMPTY" icon?
                                'type' => 'milenia_icons',
                                'iconsPerPage' => 200, // default 100, how many icons per/page to display
                            ),
                            'dependency' => array(
                                'element' => 'icon_type',
                                'value' => 'milenia_icons',
                            ),
                            'description' => __( 'Select icon from library.', 'milenia-app-textdomain' )
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_counters_style',
                    'value' => array(
                        esc_html__('Horizontal', 'milenia-app-textdomain') => 'milenia-counters--horizontal',
                        esc_html__('Vertical', 'milenia-app-textdomain') => 'milenia-counters--vertical'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_counters_columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
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
        add_shortcode('vc_milenia_counters', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_counters'] = vc_param_group_parse_atts( $atts['milenia_counters'] );

        $this->attributes = shortcode_atts( array(
            'milenia_widget_title' => '',
            'milenia_counters' => array(),
            'milenia_counter_value_color' => '',
            'milenia_counter_text_color' => '',
            'milenia_counters_columns' => 'milenia-grid--cols-4',
            'milenia_counters_style' => 'milenia-counters--horizontal',
            'css_animation' => 'none',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_counters' );

        wp_enqueue_script('milenia-counters');

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-counters');

        $style = $this->throughWhiteList($this->attributes['milenia_counters_style'], array('milenia-counters--horizontal', 'milenia-counters--vertical'), 'milenia-counters--horizontal');
        $columns = $this->throughWhiteList($this->attributes['milenia_counters_columns'], array('milenia-grid--cols-4', 'milenia-grid--cols-3', 'milenia-grid--cols-2', 'milenia-grid--cols-1'), 'milenia-grid--cols-4');
        $container_classes = array();
        $element_classes = array($style);
        $grid_element_classes = array($columns);
        $items_template = array();

        if(!empty($this->attributes['milenia_extra_class_name']))
        {
            array_push($container_classes, $this->attributes['milenia_extra_class_name']);
        }
        if($this->attributes['css_animation'] == 'none')
        {
            array_push($container_classes, 'milenia-visible');
        }

        if(count($this->attributes['milenia_counters']) && count($this->attributes['milenia_counters'][0])) {
            foreach($this->attributes['milenia_counters'] as $index => $counter) {
                $counter_title = (empty($counter['milenia_counter_title']) || !isset($counter['milenia_counter_title'])) ? '&nbsp;' : $counter['milenia_counter_title'];
                $counter_icon = !empty($counter['milenia_counter_icon']) ? sprintf('<div class="milenia-counter-icon %s"></div>', $counter['milenia_counter_icon']) : '';

                array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-counters-item.tpl'), array(
                    '${counter_icon}' => wp_kses($counter_icon, array(
                        'div' => array(
                            'class' => true
                        )
                    )),
                    '${counter_id}' => esc_attr($this->unique_id . $index),
                    '${counter_value_style}' => isset($counter['milenia_counter_value_color']) && !empty($counter['milenia_counter_value_color']) ? sprintf(' style="color: %s;"', esc_attr($counter['milenia_counter_value_color'])) : '',
                    '${counter_style}' => isset($counter['milenia_counter_text_color']) && !empty($counter['milenia_counter_text_color']) ? sprintf(' style="color: %s;"', esc_attr($counter['milenia_counter_text_color'])) : '',
                    '${counter_title}' => esc_html($counter_title),
                    '${counter_value}' => esc_html($counter['milenia_counter_value'])
                )));
            }
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-counters-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${items}' => implode("\r\n", $items_template),
            '${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
            '${element_classes}' => $this->sanitizeHtmlClasses($element_classes),
            '${grid_element_classes}' => $this->sanitizeHtmlClasses($grid_element_classes),
            '${css_animation}' => esc_attr($this->attributes['css_animation'])
        ));
	}
}
?>
