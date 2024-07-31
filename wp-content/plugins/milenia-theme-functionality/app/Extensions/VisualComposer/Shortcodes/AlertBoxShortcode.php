<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class AlertBoxShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Alert Box', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_alert_box',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows an alert box.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textarea',
                    'heading' => esc_html__('Text', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_alert_box_text',
                    'description' => esc_html__('Enter the alert box content. You can use the following HTML tags in this field', 'milenia-app-textdomain') . ': ' . esc_attr( '<i></i>, <u></u>, <b></b>, <em></em>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <cite></cite>, <ul></ul>, <li></li>' ),
                    'admin_label' => true
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Type', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_alert_box_type',
                    'value' => array(
                        esc_html__('Info', 'milenia-app-textdomain') => 'milenia-alert-box--info',
                        esc_html__('Warning', 'milenia-app-textdomain') => 'milenia-alert-box--warning',
                        esc_html__('Error', 'milenia-app-textdomain') => 'milenia-alert-box--error',
                        esc_html__('Success', 'milenia-app-textdomain') => 'milenia-alert-box--success'
                    ),
                    'description' => esc_html__('Select a type of the alert box.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Duration of closing the alert.', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_alert_closing_duration',
                    'value' => 500,
                    'admin_label' => true,
                    'description' => esc_html__('In milliseconds.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Easing of closing the alert.', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_alert_closing_easing',
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
        add_shortcode('vc_milenia_alert_box', array($this, 'content'));
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
			'milenia_alert_box_text' => '',
			'milenia_alert_box_type' => 'milenia-alert-box--info',
			'milenia_alert_closing_duration' => 500,
			'milenia_alert_closing_easing' => 'linear',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_alert_box' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-alert-box');

		wp_enqueue_script('milenia-alert-box');

		$alert_box_type = $this->throughWhiteList($this->attributes['milenia_alert_box_type'], array('milenia-alert-box--info', 'milenia-alert-box--warning', 'milenia-alert-box--error', 'milenia-alert-box--success'), 'milenia-alert-box--info');
		$container_classes = array($alert_box_type);
		$duration = is_numeric($this->attributes['milenia_alert_closing_duration']) ? intval($this->attributes['milenia_alert_closing_duration']) : 500;
		$easing = $this->throughWhiteList($this->attributes['milenia_alert_closing_easing'], array(
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

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-alert-box.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
			'${alert_box_text}' => wpautop( wp_kses( $this->attributes['milenia_alert_box_text'], array(
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
			) ) ),
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation']),
			'${alert_close_btn_text}' => esc_html__('Close', 'milenia-app-textdomain'),
			'${duration}' => esc_js($duration),
			'${easing}' => esc_js($easing)
		));
    }
}
?>
