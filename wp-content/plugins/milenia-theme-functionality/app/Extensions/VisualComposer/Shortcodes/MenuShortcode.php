<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class MenuShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Menu', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_menu',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a menu.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'param_group',
                    'heading' => esc_html__('Items', 'milenia-app-textdomain'),
                    'param_name' => 'items',
                    'description' => esc_html__('Here you can create items.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'title',
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Value', 'milenia-app-textdomain'),
                            'param_name' => 'value',
                            'admin_label' => true
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
        add_shortcode('vc_milenia_menu', array($this, 'content'));
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
            'items' => array(),
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_blockquote' );

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-menu');
		$container_classes = array();
        $items_template = array();

		array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_menu', $this->attributes ));

        if(count($this->attributes['items']) && count($this->attributes['items'][0]))
        {
			foreach($this->attributes['items'] as $index => $item)
            {
				array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-menu/item.tpl'), array(
					'${title}' => isset($item['title']) && !empty($item['title']) ? esc_html($item['title']) : '',
					'${value}' => isset($item['value']) && !empty($item['value']) ? esc_html($item['value']) : ''
				)));
			}
		}

		if(!empty($this->attributes['milenia_extra_class_name']))
		{
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}

		if($this->attributes['css_animation'] == 'none')
		{
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-menu/container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${items}' => implode('', $items_template),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
