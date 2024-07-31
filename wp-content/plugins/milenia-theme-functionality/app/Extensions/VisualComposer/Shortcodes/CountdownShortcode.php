<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class CountdownShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Countdown', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_countdown',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a countdown element.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('End date', 'milenia-app-textdomain'),
                    'param_name' => 'datetime',
                    'description' => esc_html__('Paste a valid datetime string. (For example: 2089-12-20 08:30:45)', 'milenia-app-textdomain'),
                    'admin_label' => true
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
        add_shortcode('vc_milenia_countdown', array($this, 'content'));
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
            'datetime' => '',
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_countdown' );

        if(empty($this->attributes['datetime'])) return '';

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-panels');
        $container_classes = array();

        wp_enqueue_script('countdown');
        $EndDate = new \DateTime($this->attributes['datetime']);

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_countdown', $this->attributes ));

        if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-countdown-container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
            '${year}' => esc_attr($EndDate->format('Y')),
            '${month}' => esc_attr($EndDate->format('m')),
            '${day}' => esc_attr($EndDate->format('d')),
            '${hours}' => esc_attr($EndDate->format('H')),
            '${minutes}' => esc_attr($EndDate->format('i')),
            '${seconds}' => esc_attr($EndDate->format('s')),
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
