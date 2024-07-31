<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class IconBoxesShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Icon Boxes', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_icon_boxes',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a sequence of icon boxes.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_icon_boxes_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-icon-boxes--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-icon-boxes--style-2',
                        esc_html__('Style 3', 'milenia-app-textdomain') => 'milenia-icon-boxes--style-3'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'param_group',
                    'heading' => esc_html__('Icon boxes', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_icon_boxes',
                    'description' => esc_html__('Here you can create icon boxes.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'iconpicker',
                            'heading' => esc_html__('Icon', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_box_icon',
                            'settings' => array(
                                'emptyIcon' => true,
                                'type' => 'milenia_icons',
                                'iconsPerPage' => 200,
                            ),
                            'description' => esc_html__('Select icon from library.', 'milenia-app-textdomain'),
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_box_title',
                            'description' => esc_html__('Enter text used as a title of the counter.', 'milenia-app-textdomain'),
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'colorpicker',
                            'heading' => esc_html__('Title color', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_box_title_color',
                            'value' => '#1c1c1c',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'colorpicker',
                            'heading' => esc_html__('Text color', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_box_text_color',
                            'value' => '#858585',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'textarea',
                            'heading' => esc_html__('Content', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_box_content',
                            'description' => esc_html__('Enter the content of the icon box. You can use the following HTML tags in this field', 'milenia-app-textdomain') . ': ' . esc_attr( '<i></i>, <u></u>, <b></b>, <em></em>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <cite></cite>, <ul></ul>, <li></li>' ),
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__("Link settings", 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_box_link'
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_icon_boxes_columns',
                    'value' => array(
                        esc_html__('5 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-5',
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'admin_label' => false
                ),
                vc_map_add_css_animation(),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Extra class name', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_extra_class_name',
                    'admin_label' => false,
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
        add_shortcode('vc_milenia_icon_boxes', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_icon_boxes'] = vc_param_group_parse_atts( $atts['milenia_icon_boxes'] );

		$this->attributes = shortcode_atts( array(
			'milenia_widget_title' => '',
			'milenia_icon_boxes' => array(),
			'milenia_icon_boxes_columns' => 'milenia-grid--cols-5',
			'milenia_icon_boxes_style' => 'milenia-icon-boxes--style-1',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_icon_boxes' );

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-info-boxes');
		$columns = $this->throughWhiteList($this->attributes['milenia_icon_boxes_columns'], array('milenia-grid--cols-5', 'milenia-grid--cols-4', 'milenia-grid--cols-3', 'milenia-grid--cols-2', 'milenia-grid--cols-1'), 'milenia-grid--cols-5');
		$style = $this->throughWhiteList($this->attributes['milenia_icon_boxes_style'], array('milenia-icon-boxes--style-1', 'milenia-icon-boxes--style-2', 'milenia-icon-boxes--style-3'), 'milenia-icon-boxes--style-1');

		$container_classes = array($style);
		$grid_classes = array($columns);
		$items_template = array();

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		if(count($this->attributes['milenia_icon_boxes']) && count($this->attributes['milenia_icon_boxes'][0])) {
			foreach($this->attributes['milenia_icon_boxes'] as $index => $icon_box) {
                if(isset($icon_box['milenia_icon_box_link']))
                {
                    $icon_box_link = vc_build_link( $icon_box['milenia_icon_box_link'] );
                }

                if(isset($icon_box_link))
                {
    				$icon_box_full_item_link = sprintf(
    					'<a href="%s" class="milenia-ln--independent milenia-icon-box-link" %s %s %s></a>',
    					esc_url($icon_box_link['url']),
    					isset($icon_box_link['title']) && !empty($icon_box_link['title']) ? 'title="'.esc_attr($icon_box_link['title']).'"' : '',
    					isset($icon_box_link['rel']) && !empty($icon_box_link['rel']) ? 'rel="'.esc_attr($icon_box_link['rel']).'"' : '',
    					isset($icon_box_link['target']) && !empty($icon_box_link['target']) ? 'target="'.esc_attr($icon_box_link['target']).'"' : ''
    				);
                }

				if($style != 'milenia-icon-boxes--style-1') {
					if(isset($icon_box_link['url']) && !empty($icon_box_link['url'])) {
						$icon_box_title = sprintf(
							'<a href="%s" %s %s %s>%s</a>',
							esc_url($icon_box_link['url']),
							isset($icon_box_link['title']) && !empty($icon_box_link['title']) ? 'title="'.esc_attr($icon_box_link['title']).'"' : '',
							isset($icon_box_link['rel']) && !empty($icon_box_link['rel']) ? 'rel="'.esc_attr($icon_box_link['rel']).'"' : '',
							isset($icon_box_link['target']) && !empty($icon_box_link['target']) ? 'target="'.esc_attr($icon_box_link['target']).'"' : '',
							$icon_box['milenia_icon_box_title']
						);
					}
					else {
						$icon_box_title = $icon_box['milenia_icon_box_title'];
					}
				}
				else {
					$icon_box_title = $icon_box['milenia_icon_box_title'];
				}

                if(!isset($icon_box['milenia_icon_box_content']))
                {
                    $icon_box['milenia_icon_box_content'] = '';
                }

				array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-icon-boxes-item.tpl'), array(
                    '${icon_box_icon}' => (empty($icon_box['milenia_icon_box_icon']) || !isset($icon_box['milenia_icon_box_icon'])) ? '' : sprintf('<span class="milenia-icon-box-icon %s"></span>', $icon_box['milenia_icon_box_icon']),
                    '${icon_box_title}' => wp_kses($icon_box_title, array(
						'a' => array(
							'href' => true,
							'title' => true,
							'class' => true,
							'rel' => true,
							'target' => true
						)
					)),
                    '${icon_box_style}' => isset($icon_box['milenia_icon_box_text_color']) && $style != 'milenia-icon-boxes--style-1' ? sprintf(' style="color: %s;"', esc_attr($icon_box['milenia_icon_box_text_color'])) : '',
                    '${icon_box_title_style}' => isset($icon_box['milenia_icon_box_title_color']) && $style != 'milenia-icon-boxes--style-1' ? sprintf(' style="color: %s;"', esc_attr($icon_box['milenia_icon_box_title_color'])) : '',
                    '${icon_box_content}' => wpautop( wp_kses( $icon_box['milenia_icon_box_content'], array(
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
					))),
					'${icon_box_full_item_link}' => (isset($icon_box_link['url']) && !empty($icon_box_link['url']) && $style == 'milenia-icon-boxes--style-1') ? $icon_box_full_item_link : ''
				)));
			}
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-icon-boxes-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${items}' => implode("\r\n", $items_template),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($grid_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
