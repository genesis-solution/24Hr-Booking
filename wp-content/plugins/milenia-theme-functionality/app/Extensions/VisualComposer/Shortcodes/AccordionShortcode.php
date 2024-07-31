<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class AccordionShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Accordion', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_accordion',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows collapsible panels.', 'milenia-app-textdomain'),
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
                    'param_name' => 'milenia_accordion_panels',
                    'description' => esc_html__('Here you can create accordion panels.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_accordion_panel_title',
                            'description' => esc_html__('Enter text used as a title of the panel.', 'milenia-app-textdomain'),
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textarea',
                            'heading' => esc_html__('Descrption', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_accordion_panel_description',
                            'description' => esc_html__('Enter text used as content of the panel. You can use the following HTML tags in this field', 'milenia-app-textdomain') . ':' . esc_attr( '<i></i>, <u></u>, <b></b>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <ul></ul>, <ol></ol>, <li></li>' )
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_accordion_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-panels--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-panels--style-2'
                    ),
                    'admin_label' => true,
                    'description' => esc_html__('Select display style of the accordion element.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Active panel number', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_accordion_active_panel_number',
                    'value' => '1',
                    'admin_label' => true,
                    'description' => esc_html__('Enter a number of opened panel by default (comma-separated).', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Allow toggle all panels', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_accordion_allow_toggle_all',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Duration of panels animation', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_accordion_panels_duration',
                    'value' => 600,
                    'admin_label' => true,
                    'description' => esc_html__('In milliseconds.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Easing of panels animation', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_accordion_panels_easing',
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
        add_shortcode('vc_milenia_accordion', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_accordion_panels'] = vc_param_group_parse_atts( $atts['milenia_accordion_panels'] );

		$this->attributes = shortcode_atts( array(
			'milenia_widget_title' => '',
			'milenia_accordion_panels' => array(),
			'milenia_accordion_style' => 'milenia-panels--style-1',
			'milenia_accordion_active_panel_number' => 1,
			'milenia_accordion_allow_toggle_all' => '',
			'milenia_accordion_panels_duration' => 600,
			'milenia_accordion_panels_easing' => 'linear',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_accordion' );


		wp_enqueue_script('milenia-accordion');

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-panels');
		$items_template = array();
		$container_classes = array();
		$element_classes = array($this->throughWhiteList($this->attributes['milenia_accordion_style'], array(
			'milenia-panels--style-1',
			'milenia-panels--style-2'
		), 'milenia-panels--style-1'));

		$duration = is_numeric($this->attributes['milenia_accordion_panels_duration']) ? intval($this->attributes['milenia_accordion_panels_duration']) : 600;
		$active_panel_index = $this->attributes['milenia_accordion_active_panel_number'];
		$easing = $this->throughWhiteList($this->attributes['milenia_accordion_panels_easing'], array(
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
		$is_toggle = $this->attributes['milenia_accordion_allow_toggle_all'] == 'true';
        $active_panel_index = explode(',', preg_replace('/\s/', '', $active_panel_index));
        if(empty($active_panel_index)) $active_panel_index = array(1);

		if($is_toggle)
		{
			array_push($element_classes, 'milenia-panels--toggles');
		}
		else {
            if(count($active_panel_index) > 1)
            {
                $active_panel_index = array_slice($active_panel_index, 0, 1);
            }

			array_push($element_classes, 'milenia-panels--accordion');
		}


		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		if( count($this->attributes['milenia_accordion_panels']) && count($this->attributes['milenia_accordion_panels'][0]) ) {
			foreach($this->attributes['milenia_accordion_panels'] as $index => $panel) {
				array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-panels-item.tpl'), array(
					'${panel_active_class}' => sanitize_html_class(in_array($index + 1, $active_panel_index) ? 'milenia-panels-active' : ''),
					'${panel_index}' => esc_attr(sprintf('%s-%d', $this->unique_id, $index)),
					'${panel_expanded_state}' => esc_attr(($index + 1 == $active_panel_index) ? 'true' : 'false'),
					'${panel_title}' => esc_html($panel['milenia_accordion_panel_title']),
					'${panel_description}' => wpautop( wp_kses( $panel['milenia_accordion_panel_description'], array(
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

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-panels-container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${items}' => implode("\r\n", $items_template),
			'${is_toggle}' => esc_js($is_toggle ? 1 : 0),
			'${panels_animation_easing}' => esc_js($easing),
			'${panels_animation_duration}' => esc_js($duration),
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${element_classes}' => $this->sanitizeHtmlClasses($element_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
