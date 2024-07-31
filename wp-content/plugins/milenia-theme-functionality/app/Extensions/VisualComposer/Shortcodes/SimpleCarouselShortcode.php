<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class SimpleCarouselShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Simple Carousel', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_simple_carousel',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a simple carousel.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'attach_images',
                    'heading' => esc_html__('Images', 'milenia-app-textdomain'),
                    'param_name' => 'images'
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Items', 'milenia-app-textdomain'),
                    'param_name' => 'options_items',
                    'value' => '1',
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Arrows', 'milenia-app-textdomain'),
                    'param_name' => 'options_navigation_arrows',
                    'value' => true,
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Dots', 'milenia-app-textdomain'),
                    'param_name' => 'options_navigation_dots',
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Margin', 'milenia-app-textdomain'),
                    'param_name' => 'options_margin',
                    'value' => '1',
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Loop', 'milenia-app-textdomain'),
                    'param_name' => 'options_loop',
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Autoplay', 'milenia-app-textdomain'),
                    'param_name' => 'options_autoplay',
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Autoplay timeout', 'milenia-app-textdomain'),
                    'param_name' => 'options_autoplay_timeout',
                    'value' => '5000',
                    'description' => esc_html__('In milliseconds.', 'milenia-app-textdomain'),
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'options_autoplay',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Autoplay hover pause', 'milenia-app-textdomain'),
                    'param_name' => 'options_autoplay_hover_pause',
                    'value' => 'true',
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'options_autoplay',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Speed', 'milenia-app-textdomain'),
                    'param_name' => 'options_speed',
                    'value' => '500',
                    'description' => esc_html__('In milliseconds.', 'milenia-app-textdomain'),
                    'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
                ),
	            array(
		            'type' => 'checkbox',
		            'heading' => esc_html__('Auto Height', 'milenia-app-textdomain'),
		            'param_name' => 'options_auto_height',
		            'value' => 'false',
		            'group' => esc_html__('Carousel Options', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_simple_carousel', array($this, 'content'));
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
			'images' => array(),
            'options_items' => 1,
            'options_navigation_arrows' => true,
			'options_navigation_dots' => false,
			'options_auto_height' => false,
			'options_margin' => 1,
			'options_loop' => false,
			'options_autoplay' => false,
			'options_autoplay_timeout' => 5000,
			'options_autoplay_hover_pause' => false,
			'options_speed' => 500,
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_simple_carousel' );

        wp_enqueue_style('owl-carousel');
        wp_enqueue_script('owl-carousel');

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-simple-carousel');
        $container_classes = array('owl-carousel', 'owl-carousel--vadaptive', 'owl-carousel--nav-edges', 'owl-carousel--nav-huge', 'owl-carousel--nav-inside', 'owl-carousel--nav-hover-white', 'milenia-simple-slideshow--shortcode');
        $items_template = array();
        $carousel_options = array(
            'items' => intval($this->attributes['options_items']),
            'margin' => intval($this->attributes['options_margin']),
            'nav' => boolval($this->attributes['options_navigation_arrows']),
            'dots' => boolval($this->attributes['options_navigation_dots']),
            'autoHeight' => boolval($this->attributes['options_auto_height']),
            'loop' => boolval($this->attributes['options_loop']),
            'autoplay' => boolval($this->attributes['options_autoplay']),
            'autoplayHoverPause' => boolval($this->attributes['options_autoplay_hover_pause']),
            'autoplayTimeout' => intval($this->attributes['options_autoplay_timeout']),
            'smartSpeed' => intval($this->attributes['options_speed']),
            'fluidSpeed' => intval($this->attributes['options_speed']),
            'autoplaySpeed' => intval($this->attributes['options_speed']),
            'navSpeed' => intval($this->attributes['options_speed']),
            'dotsSpeed' => intval($this->attributes['options_speed']),
            'dragEndSpeed' => intval($this->attributes['options_speed']),
        );

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_simple_carousel', $this->attributes ));

        foreach(explode(',', $this->attributes['images']) as $image_id)
        {
            $items_template[] = wp_get_attachment_image($image_id, 'full', false, array(
                'class' => 'owl-carousel-img'
            ));
        }

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-simple-carousel-container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h6 class="milenia-fw-bold">%s</h6>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${carousel_options}' => wp_json_encode($carousel_options),
			'${items}' => implode("\r\n", $items_template),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
