<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class ButtonShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Button', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_button',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows a button.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Text', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_text',
                    'value' => esc_html__('Text on the button', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'vc_link',
                    'heading' => esc_html__("Link's settings", 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_link'
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Predefined color scheme', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_predefined_color_scheme',
                    'value' => array(
                        esc_html__('Gray', 'milenia-app-textdomain') => 'gray',
                        esc_html__('None', 'milenia-app-textdomain') => 'none',
                        esc_html__('Dark', 'milenia-app-textdomain') => 'dark',
                        esc_html__('Light', 'milenia-app-textdomain') => 'light',
                        esc_html__('[Brown scheme] Primary', 'milenia-app-textdomain') => 'brown-primary',
                        esc_html__('[Brown scheme] Secondary', 'milenia-app-textdomain') => 'brown-secondary',
                        esc_html__('[Blue scheme] Primary', 'milenia-app-textdomain') => 'blue-primary',
                        esc_html__('[Lightbrown scheme] Primary', 'milenia-app-textdomain') => 'lightbrown-primary',
                        esc_html__('[Gray scheme] Primary', 'milenia-app-textdomain') => 'gray-primary',
                        esc_html__('[Green scheme] Primary', 'milenia-app-textdomain') => 'green-primary',
                        esc_html__('[Green scheme] Secondary', 'milenia-app-textdomain') => 'green-secondary'
                    ),
                    'description' => esc_html__('Choose one of the predefined color schemes.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Inverted button colors', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_button_inverted_colors',
                    'admin_label' => true,
                    'description' => esc_html__('Invert colors of the selected color scheme.', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'milenia_button_predefined_color_scheme',
                        'value' => array('gray', 'dark' ,'light', 'brown-primary', 'brown-secondary', 'blue-primary', 'lightbrown-primary', 'gray-primary', 'green-primary', 'green-secondary')
                    )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Color', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_color',
                    'value' => '#ffffff',
                    'description' => esc_html__('Select text color of the button.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_button_predefined_color_scheme',
                        'value' => 'none'
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Size', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_size',
                    'value' => array(
                        esc_html__('Medium', 'milenia-app-textdomain') => 'milenia-btn--medium',
                        esc_html__('Small', 'milenia-app-textdomain') => 'milenia-btn--link',
                        esc_html__('Big', 'milenia-app-textdomain') => 'milenia-btn--big',
                        esc_html__('Huge', 'milenia-app-textdomain') => 'milenia-btn--huge',
                        esc_html__('XXL', 'milenia-app-textdomain') => 'milenia-btn--xxl'
                    ),
                    'description' => esc_html__('Select size of the button.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Alignment', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_alignment',
                    'value' => array(
                        esc_html__('Left', 'milenia-app-textdomain') => 'text-left',
                        esc_html__('[xl] Left', 'milenia-app-textdomain') => 'text-xl-left',
                        esc_html__('[lg] Left', 'milenia-app-textdomain') => 'text-lg-left',
                        esc_html__('[md] Left', 'milenia-app-textdomain') => 'text-md-left',
                        esc_html__('[sm] Left', 'milenia-app-textdomain') => 'text-sm-left',
                        esc_html__('Center', 'milenia-app-textdomain') => 'text-center',
                        esc_html__('[xl] Center', 'milenia-app-textdomain') => 'text-xl-center',
                        esc_html__('[lg] Center', 'milenia-app-textdomain') => 'text-lg-center',
                        esc_html__('[md] Center', 'milenia-app-textdomain') => 'text-md-center',
                        esc_html__('[sm] Center', 'milenia-app-textdomain') => 'text-sm-center',
                        esc_html__('Right', 'milenia-app-textdomain') => 'text-right',
                        esc_html__('[xl] Right', 'milenia-app-textdomain') => 'text-xl-right',
                        esc_html__('[lg] Right', 'milenia-app-textdomain') => 'text-lg-right',
                        esc_html__('[md] Right', 'milenia-app-textdomain') => 'text-md-right',
                        esc_html__('[sm] Right', 'milenia-app-textdomain') => 'text-sm-right'
                    ),
                    'description' => esc_html__('Select the button alignment.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_font',
                    'value' => array(
                        esc_html__('Body font', 'milenia-app-textdomain') => 'body_font',
                        esc_html__('First accented font', 'milenia-app-textdomain') => 'first_accented_font',
                        esc_html__('Second accented font', 'milenia-app-textdomain') => 'second_accented_font'
                    ),
                    'description' => esc_html__('Select one of the fonts that have been configured on the theme options page.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Font size', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_font_size',
                    'description' => esc_html__('Any available CSS units.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Line height', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_line_height',
                    'description' => esc_html__('Any available CSS units.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_font_style',
                    'value' => array(
                        esc_html__('Normal', 'milenia-app-textdomain') => 'normal',
                        esc_html__('Italic', 'milenia-app-textdomain') => 'italic'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Letter spacing', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_letter_spacing',
                    'admin_label' => false,
                    'description' => esc_html__('Any available CSS units.', 'milenia-app-textdomain'),
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font weight', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_font_weight',
                    'value' => array(
                        400 => 400,
                        200 => 200,
                        300 => 300,
                        500 => 500,
                        600 => 600,
                        700 => 700,
                        800 => 800,
                        900 => 900
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Text transform', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_button_text_transform',
                    'value' => array(
                        esc_html__('Uppercase', 'milenia-app-textdomain') => 'uppercase',
                        esc_html__('None', 'milenia-app-textdomain') => 'none',
                        esc_html__('Lowercase', 'milenia-app-textdomain') => 'lowercase',
                        esc_html__('Capitalize', 'milenia-app-textdomain') => 'capitalize'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Font settings', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_button', array($this, 'content'));
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
			'milenia_button_text' => '',
			'milenia_button_link' => '',
			'milenia_button_predefined_color_scheme' => 'gray',
			'milenia_button_inverted_colors' => false,
			'milenia_button_color' => '#858585',
			'milenia_button_size' => 'milenia-btn--medium',
			'milenia_button_alignment' => 'text-left',
			'milenia_button_font' => 'body_font',
			'milenia_button_font_size' => '',
			'milenia_button_line_height' => '',
			'milenia_button_font_style' => 'normal',
			'milenia_button_letter_spacing' => '1.8px',
			'milenia_button_font_weight' => 400,
			'milenia_button_text_transform' => 'uppercase',
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_button' );

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-button');
		$button_color_scheme = $this->throughWhiteList($this->attributes['milenia_button_predefined_color_scheme'], array(
			'gray',
			'none',
			'dark',
			'light',
			'brown-primary',
			'brown-secondary',
			'blue-primary',
			'lightbrown-primary',
			'gray-primary',
			'green-primary',
			'green-secondary'
		), 'gray');

		$button_link = vc_build_link( $this->attributes['milenia_button_link'] );
		$button_size = $this->throughWhiteList($this->attributes['milenia_button_size'], array(
			'milenia-btn--medium',
			'milenia-btn--link',
			'milenia-btn--big',
			'milenia-btn--huge',
			'milenia-btn--xxl'
		), 'milenia-btn--medium');
		$button_alignment = $this->throughWhiteList($this->attributes['milenia_button_alignment'], array(
			'text-left','text-center','text-right', 'text-xl-left', 'text-lg-left', 'text-md-left', 'text-sm-left', 'text-xl-center', 'text-lg-center', 'text-md-center', 'text-sm-center', 'text-xl-right', 'text-lg-right', 'text-md-right', 'text-sm-right'
		), 'text-left');
		$button_style_map = 'font-style: %s; letter-spacing: %s; font-weight: %s; text-transform: %s;';
		$container_classes = array($button_alignment);
		$button_classes = array($button_size);

        if($button_size == 'milenia-btn--xxl')
        {
            $button_classes[] = 'milenia-btn--huge';
        }

		$button_style_map = sprintf($button_style_map, $this->attributes['milenia_button_font_style'], $this->attributes['milenia_button_letter_spacing'], $this->attributes['milenia_button_font_weight'], $this->attributes['milenia_button_text_transform']);

		switch($button_color_scheme)
		{
			case 'none' :
				$button_style_map .= sprintf('color: %s;', $this->attributes['milenia_button_color']);
				if($button_size == 'milenia-btn--link')
				{
					$button_style_map .= sprintf(
						'background-image: -webkit-gradient(linear, left top, left bottom, color-stop(%2$s, %1$s), to(%1$s)); background-image: linear-gradient(to bottom, %1$s %2$s, %1$s %2$s)',
						$this->attributes['milenia_button_color'],
						'100%'
					);
				}
			break;
			case 'dark' :
				array_push($button_classes, 'milenia-btn--scheme-dark');
			break;
			case 'light' :
				array_push($button_classes, 'milenia-btn--scheme-light');
			break;
			case 'brown-primary' :
				array_push($container_classes, 'milenia-body--scheme-brown');
				array_push($button_classes, 'milenia-btn--scheme-primary');
			break;
			case 'brown-secondary' :
				array_push($container_classes, 'milenia-body--scheme-brown');
				array_push($button_classes, 'milenia-btn--scheme-secondary');
			break;
			case 'blue-primary' :
				array_push($container_classes, 'milenia-body--scheme-blue');
				array_push($button_classes, 'milenia-btn--scheme-primary');
			break;
			case 'lightbrown-primary' :
				array_push($container_classes, 'milenia-body--scheme-lightbrown');
				array_push($button_classes, 'milenia-btn--scheme-primary');
			break;
			case 'gray-primary' :
				array_push($container_classes, 'milenia-body--scheme-gray');
				array_push($button_classes, 'milenia-btn--scheme-primary');
			break;
			case 'green-primary' :
				array_push($container_classes, 'milenia-body--scheme-green');
				array_push($button_classes, 'milenia-btn--scheme-primary');
			break;
			case 'green-secondary' :
				array_push($container_classes, 'milenia-body--scheme-green');
				array_push($button_classes, 'milenia-btn--scheme-secondary');
			break;
		}

		if($this->attributes['milenia_button_inverted_colors'] == 'true') {
			array_push($button_classes, 'milenia-btn--reverse');
		}

		if(!empty($this->attributes['milenia_button_font_size'])) {
			$button_style_map .= sprintf('font-size: %s;', esc_attr($this->attributes['milenia_button_font_size']));
		}

		if(!empty($this->attributes['milenia_button_line_height'])) {
			$button_style_map .= sprintf('line-height: %s;', esc_attr($this->attributes['milenia_button_line_height']));
		}

		array_push($button_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_button', $this->attributes ));

		switch($this->attributes['milenia_button_font'])
		{
			case 'body_font':
				array_push($button_classes, 'milenia-font--like-body');
			break;
			case 'first_accented_font':
				array_push($button_classes, 'milenia-font--first-accented');
			break;
			case 'second_accented_font':
				array_push($button_classes, 'milenia-font--second-accented');
			break;
		}

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}

		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-button.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
			'${button_text}' => esc_html($this->attributes['milenia_button_text']),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${button_classes}' => esc_attr($this->sanitizeHtmlClasses($button_classes)),
			'${button_style}' => $button_style_map,
			'${button_url}' => !empty($button_link['url']) ? esc_url($button_link['url']) : '#',
			'${button_title}' => esc_attr($button_link['title']),
			'${button_target}' => !empty($button_link['target']) ? esc_attr($button_link['target']) : '_self',
			'${button_rel}' => !empty($button_link['rel']) ? esc_attr($button_link['rel']) : '',
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
