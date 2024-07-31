<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class KeyValueListShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Key-value list', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_key_value_list',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a key-value list.', 'milenia-app-textdomain'),
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
                    'heading' => esc_html__('Items', 'milenia-app-textdomain'),
                    'param_name' => 'items',
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Key', 'milenia-app-textdomain'),
                            'param_name' => 'key',
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Value', 'milenia-app-textdomain'),
                            'param_name' => 'value',
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__('Link settings', 'milenia-app-textdomain'),
                            'param_name' => 'link_settings',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'dropdown',
                            'heading' => esc_html__('Uppercased link', 'milenia-app-textdomain'),
                            'param_name' => 'link_uppercased',
                            'value' => array(
                                esc_html__('No', 'milenia-app-textdomain') => 'no',
                                esc_html__('Yes', 'milenia-app-textdomain') => 'yes'
                            ),
                            'admin_label' => false
                        )
                    )
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => esc_html__('Css', 'milenia-app-textdomain'),
                    'param_name' => 'css',
                    'group' => esc_html__('Design options', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_key_value_list', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['items'] = vc_param_group_parse_atts( $atts['items'] );

		$this->attributes = shortcode_atts( array(
			'milenia_widget_title' => '',
			'items' => array(),
            'link_uppercased' => 'yes',
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_key_value_list' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-panels');
        $container_classes = array('milenia-details-list', 'milenia-list--unstyled');
        $items_template = array();

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_key_value_list', $this->attributes ));

        if( count($this->attributes['items']) && count($this->attributes['items'][0]) ) {
			foreach($this->attributes['items'] as $index => $item) {

                if(isset($item['link_settings']))
                {
                    $item_link = vc_build_link($item['link_settings']);
                }

                if(isset($item_link) && !empty($item_link['url']))
                {
                    array_push($items_template, $this->prepareShortcodeTemplate(
                        self::loadShortcodeTemplate(
                            'vc-milenia-key-value-list/item-link.tpl'
                        ),
                        array(
                            '${key}' => isset($item['key']) ? sprintf('<span class="milenia-tc--dark">%s</span>', esc_html($item['key'])) : '',
                            '${value}' => isset($item['value']) ? esc_html($item['value']) : '',
                            '${url}' => esc_url($item_link['url']),
                            '${link_classes}' => $item['link_uppercased'] == 'yes' ? 'class="milenia-uppercased-link"' : '',
                            '${target}' => isset($item_link['target']) && !empty($item_link['target']) ? 'target="'.esc_attr($item_link['target']).'"' : '',
                            '${title}' => isset($item_link['title']) && !empty($item_link['title']) ? 'title="'.esc_attr($item_link['title']).'"' : '',
                            '${rel}' => isset($item_link['rel']) && !empty($item_link['rel']) ? 'rel="'.esc_attr($item_link['rel']).'"' : ''
                        )
                    ));
                }
                else
                {
                    array_push($items_template, $this->prepareShortcodeTemplate(
                        self::loadShortcodeTemplate(
                            'vc-milenia-key-value-list/item.tpl'
                        ),
                        array(
                            '${key}' => isset($item['key']) ? sprintf('<span class="milenia-tc--dark">%s</span>', esc_html($item['key'])) : '',
                            '${value}' => isset($item['value']) ? esc_html($item['value']) : ''
                        )
                    ));
                }
			}
		}


		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-key-value-list/container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h6 class="milenia-fw-bold">%s</h6>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${items}' => implode("\r\n", $items_template),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
