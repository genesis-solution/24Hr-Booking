<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class AwardsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Awards', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_awards',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates an awards list.', 'milenia-app-textdomain'),
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
                    'param_name' => 'milenia_awards_items',
                    'params' => array(
                        array(
                            'type' => 'attach_image',
                            'heading' => esc_html__('Award', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_award_image',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__("Award's link settings", 'milenia-app-textdomain'),
                            'param_name' => 'milenia_award_link',
                            'admin_label' => false,
                            'description' => esc_html__('Enter text used as a description. You can use the following HTML tags in this field', 'milenia-app-textdomain') . ':' . esc_attr( '<i></i>, <u></u>, <b></b>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <ul></ul>, <ol></ol>, <li></li>' )
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_awards_columns',
                    'value' => array(
                        esc_html__('6 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-6',
                        esc_html__('5 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-5',
                        esc_html__('4 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 Column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
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
        add_shortcode('vc_milenia_awards', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_awards_items'] = vc_param_group_parse_atts( $atts['milenia_awards_items'] );

        $this->attributes = shortcode_atts( array(
            'milenia_widget_title' => '',
            'milenia_awards_items' => array(),
            'milenia_awards_columns' => 'milenia-grid--cols-6',
            'css_animation' => 'none',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_awards' );

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-awards');
        $columns = $this->throughWhiteList($this->attributes['milenia_awards_columns'], array('milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4', 'milenia-grid--cols-5', 'milenia-grid--cols-6'), 'milenia-grid--cols-6');

        $container_classes = array();
        $grid_classes = array($columns);

        if(!empty($this->attributes['milenia_extra_class_name'])) {
            array_push($container_classes, $this->attributes['milenia_extra_class_name']);
        }
        if($this->attributes['css_animation'] == 'none') {
            array_push($container_classes, 'milenia-visible');
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-awards-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${items}' => $this->getItems(),
            '${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($grid_classes)),
            '${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
            '${css_animation}' => esc_attr($this->attributes['css_animation'])
        ));
	}

    /**
	 * Returns html markup of the timeline items.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getItems()
	{
		$items_template = array();

		if(count($this->attributes['milenia_awards_items']) && count($this->attributes['milenia_awards_items'][0])) {
			foreach($this->attributes['milenia_awards_items'] as $index => $item) {
				$award_link = vc_build_link( $item['milenia_award_link'] );

				array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-awards-item.tpl'), array(
					'${award_image}' => isset($item['milenia_award_image']) && is_numeric($item['milenia_award_image']) ? wp_get_attachment_image($item['milenia_award_image']) : '',
					'${award_url}' => isset($award_link['url']) && !empty($award_link['url']) ? esc_attr($award_link['url']) : '#',
					'${award_attr_rel}' => isset($award_link['rel']) && !empty($award_link['rel']) ? 'rel="'.esc_attr($award_link['rel']).'"' : '',
					'${award_attr_title}' => isset($award_link['title']) && !empty($award_link['title']) ? 'title="'.esc_attr($award_link['title']).'"' : '',
					'${award_attr_target}' => isset($award_link['target']) && !empty($award_link['target']) ? 'target="'.esc_attr($award_link['target']).'"' : ''
				)));
			}
		}

		return implode("\t\r\n", $items_template);
	}
}
?>
