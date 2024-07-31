<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class BlockquoteShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Blockquote', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_blockquote',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a blockquote.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textarea',
                    'heading' => esc_html__('Quote', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_blockquote_quote',
                    'admin_label' => false,
                    'description' => esc_html__('You can use the following HTML tags in this field', 'milenia-app-textdomain') . ':' . esc_attr( '<i></i>, <u></u>, <b></b>, <strong></strong>, <s></s>, <ul></ul>, <ol></ol>, <li></li>', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_blockquote_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-blockquote--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-blockquote--style-2'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Source', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_blockquote_source',
                    'description' => esc_html__('Author info.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'vc_link',
                    'heading' => esc_html__('Source link', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_blockquote_source_link',
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_blockquote_source',
                        'not_empty' => true
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
        add_shortcode('vc_milenia_blockquote', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $this->attributes = shortcode_atts( array(
			'milenia_blockquote_quote' => '',
			'milenia_blockquote_style' => 'milenia-blockquote--style-1',
			'milenia_blockquote_source' => '',
			'milenia_blockquote_source_link' => '',
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_blockquote' );

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-blockquote');
		$style = $this->throughWhiteList($this->attributes['milenia_blockquote_style'], array('milenia-blockquote--style-1', 'milenia-blockquote--style-2'), 'milenia-blockquote--style-1');

		$container_classes = array();
		$blockquote_source = $this->attributes['milenia_blockquote_source'];
		$blockquote_source_link = vc_build_link( $this->attributes['milenia_blockquote_source_link'] );
		$blockquote_element_classes = array($style);

		array_push($blockquote_element_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_blockquote', $this->attributes ));

		if(!empty($blockquote_source_link['url']))
		{
			$blockquote_source = sprintf(
				'<a href="%s" %s %s %s>%s</a>',
				esc_url($blockquote_source_link['url']),
				!empty($blockquote_source_link['title']) ? sprintf('title="%s"', esc_attr($blockquote_source_link['title'])) : '',
				!empty($blockquote_source_link['target']) ? sprintf('target="%s"', esc_attr($blockquote_source_link['target'])) : '',
				!empty($blockquote_source_link['rel']) ? sprintf('rel="%s"', esc_attr($blockquote_source_link['rel'])) : '',
				$blockquote_source
			);
		}


		if(!empty($this->attributes['milenia_extra_class_name']))
		{
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}

		if($this->attributes['css_animation'] == 'none')
		{
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-blockquote.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
			'${blockquote_text}' => wpautop( wp_kses( $this->attributes['milenia_blockquote_quote'], array(
				'i' => array(),
				'u' => array(),
				'b' => array(),
				'strong' => array(),
				's' => array(),
				'cite' => array(),
				'ul' => array(
					'type' => true
				),
				'ol' => array(
					'type' => true
				),
				'li' => array()
			) ) ),
			'${blockquote_classes}' => esc_attr($this->sanitizeHtmlClasses($blockquote_element_classes)),
			'${blockquote_source}' => wp_kses_post($blockquote_source),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
