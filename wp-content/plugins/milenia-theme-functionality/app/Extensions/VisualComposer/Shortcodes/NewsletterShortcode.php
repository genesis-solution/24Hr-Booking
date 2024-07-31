<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class NewsletterShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Newsletter', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_newsletter',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows a subscribe form.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Form id', 'milenia-app-textdomain'),
                    'description' => esc_html__('Enter a MailPoet form id.', 'milenia-app-textdomain'),
                    'param_name' => 'form_id',
                    'value' => '',
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Button color', 'milenia-app-textdomain'),
                    'param_name' => 'button_color',
                    'value' => array(
                        esc_html__('[Current scheme] Primary', 'milenia-app-textdomain') => 'milenia-newsletter--btn-primary',
                        esc_html__('Dark', 'milenia-app-textdomain') => 'milenia-newsletter--btn-dark'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Inline', 'milenia-app-textdomain'),
                    'param_name' => 'inline',
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Unbordered', 'milenia-app-textdomain'),
                    'param_name' => 'unbordered',
                    'admin_label' => false
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
        add_shortcode('vc_milenia_newsletter', array($this, 'content'));
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
            'form_id' => '',
			'css' => '',
            'inline' => 0,
            'unbordered' => 0,
            'button_color' => 'milenia-singlefield-form--btn-primary',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_newsletter' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-newsletter');
        $button_color = $this->throughWhiteList($this->attributes['button_color'], array(
			'milenia-newsletter--btn-primary',
			'milenia-newsletter--btn-dark'
		), 'milenia-newsletter--btn-primary');
        $container_classes = array('milenia-newsletter', $button_color);

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_newsletter', $this->attributes ));

        if((bool) $this->attributes['inline'])
        {
            array_push($container_classes, 'milenia-newsletter--inline');
        }

        if((bool) $this->attributes['unbordered'])
        {
            array_push($container_classes, 'milenia-form--unbordered');
        }

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-newsletter-container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
            '${content}' => $this->getWidgetContent(),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3 class="milenia-singlefield-form-title">%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }

    public function getWidgetContent()
    {
        ob_start();
        the_widget( 'WYSIJA_NL_Widget', array(
            'title' => '',
            'id_form' => intval($this->attributes['form_id']),
            'form' => intval($this->attributes['form_id'])
        ), array(
            'before_widget' => '<div class="milenia-widget">',
            'after_widget' => '</div>',
            'before_title' => '',
            'after_title' => ''
        ) );

        return ob_get_clean();
    }
}
?>
