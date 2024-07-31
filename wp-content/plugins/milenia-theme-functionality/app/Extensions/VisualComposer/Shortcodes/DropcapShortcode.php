<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class DropcapShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Dropcap', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_dropcap',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows paragraph with drop cap.', 'milenia-app-textdomain'),
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
                    'param_name' => 'milenia_dropcap_style',
                    'value' => array(
                        esc_html__('Unfilled', 'milenia-app-textdomain') => 'milenia-dropcap--unfilled',
                        esc_html__('Filled', 'milenia-app-textdomain') => 'milenia-dropcap--filled'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'textarea_html',
                    'heading' => esc_html__('Content', 'milenia-app-textdomain'),
                    'param_name' => 'content',
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
        add_shortcode('vc_milenia_dropcap', array($this, 'content'));
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
            'milenia_widget_title' => '',
            'milenia_dropcap_style' => 'milenia-dropcap--unfilled',
            'css_animation' => 'none',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_dropcap' );

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-dropcap');

        $dropcap_style = $this->throughWhiteList($this->attributes['milenia_dropcap_style'], array('milenia-dropcap--unfilled', 'milenia-dropcap--filled'), 'milenia-dropcap--unfilled');

        $container_classes = array($dropcap_style);

        if(!empty($this->attributes['milenia_extra_class_name']))
        {
            array_push($container_classes, $this->attributes['milenia_extra_class_name']);
        }

        if($this->attributes['css_animation'] == 'none')
        {
            array_push($container_classes, 'milenia-visible');
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-dropcap.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${content}' => wpautop($content),
            '${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
            '${css_animation}' => esc_attr($this->attributes['css_animation'])
        ));
	}
}
?>
