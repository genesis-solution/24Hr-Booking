<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class SectionHeadingShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Section Heading', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_section_heading',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates section headings.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Main heading', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Subheading', 'milenia-app-textdomain'),
                    'param_name' => 'subheading',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textarea',
                    'heading' => esc_html__('Description', 'milenia-app-textdomain'),
                    'param_name' => 'description',
                    'value' => '',
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Text alignment', 'milenia-app-textdomain'),
                    'param_name' => 'text_alignment',
                    'value' => array(
                        esc_html__('Left', 'milenia-app-textdomain') => 'text-left',
                        esc_html__('Center', 'milenia-app-textdomain') => 'text-center',
                        esc_html__('Right', 'milenia-app-textdomain') => 'text-right'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Color', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_color',
                    'value' => '',
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_font',
                    'value' => array(
                        esc_html__('First accented font', 'milenia-app-textdomain') => 'milenia-font--first-accented',
                        esc_html__('Second accented font', 'milenia-app-textdomain') => 'milenia-font--second-accented',
                        esc_html__('Body font', 'milenia-app-textdomain') => 'milenia-font--like-body'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Font size', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_font_size',
                    'value' => '52px',
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Line height', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_line_height',
                    'value' => '58px',
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font weight', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_font_weight',
                    'value' => array(
                        '400' => '400',
                        '900' => '900',
                        '800' => '800',
                        '700' => '700',
                        '600' => '600',
                        '500' => '500',
                        '300' => '300',
                        '200' => '200',
                        '100' => '100',
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font style', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_font_style',
                    'value' => array(
                        esc_html__('Normal', 'milenia-app-textdomain') => 'normal',
                        esc_html__('Italic', 'milenia-app-textdomain') => 'italic'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Text transform', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_text_transform',
                    'value' => array(
                        esc_html__('None', 'milenia-app-textdomain') => 'none',
                        esc_html__('Uppercase', 'milenia-app-textdomain') => 'uppercase',
                        esc_html__('Lowercase', 'milenia-app-textdomain') => 'lowercase',
                        esc_html__('Capitalize', 'milenia-app-textdomain') => 'capitalize'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Letter spacing', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_letter_spacing',
                    'value' => '',
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Margin bottom', 'milenia-app-textdomain'),
                    'param_name' => 'main_heading_margin_bottom',
                    'value' => '37px',
                    'admin_label' => false,
                    'group' => esc_html__('Main Heading Settings', 'milenia-app-textdomain')
                ),

                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Color', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_color',
                    'value' => '',
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_font',
                    'value' => array(
                        esc_html__('Body font', 'milenia-app-textdomain') => 'milenia-font--like-body',
                        esc_html__('First accented font', 'milenia-app-textdomain') => 'milenia-font--first-accented',
                        esc_html__('Second accented font', 'milenia-app-textdomain') => 'milenia-font--second-accented'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Font size', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_font_size',
                    'value' => '14px',
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Line height', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_line_height',
                    'value' => '20px',
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font weight', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_font_weight',
                    'value' => array(
                        '400' => '400',
                        '900' => '900',
                        '800' => '800',
                        '700' => '700',
                        '600' => '600',
                        '500' => '500',
                        '300' => '300',
                        '200' => '200',
                        '100' => '100',
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font style', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_font_style',
                    'value' => array(
                        esc_html__('Normal', 'milenia-app-textdomain') => 'normal',
                        esc_html__('Italic', 'milenia-app-textdomain') => 'italic'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Text transform', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_text_transform',
                    'value' => array(
                        esc_html__('Uppercase', 'milenia-app-textdomain') => 'uppercase',
                        esc_html__('None', 'milenia-app-textdomain') => 'none',
                        esc_html__('Lowercase', 'milenia-app-textdomain') => 'lowercase',
                        esc_html__('Capitalize', 'milenia-app-textdomain') => 'capitalize'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Letter spacing', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_letter_spacing',
                    'value' => '',
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Margin bottom', 'milenia-app-textdomain'),
                    'param_name' => 'subheading_margin_bottom',
                    'value' => '10px',
                    'admin_label' => false,
                    'group' => esc_html__('Subheading Settings', 'milenia-app-textdomain')
                ),

                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Color', 'milenia-app-textdomain'),
                    'param_name' => 'description_color',
                    'value' => '',
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font', 'milenia-app-textdomain'),
                    'param_name' => 'description_font',
                    'value' => array(
                        esc_html__('Body font', 'milenia-app-textdomain') => 'milenia-font--like-body',
                        esc_html__('First accented font', 'milenia-app-textdomain') => 'milenia-font--first-accented',
                        esc_html__('Second accented font', 'milenia-app-textdomain') => 'milenia-font--second-accented'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Font size', 'milenia-app-textdomain'),
                    'param_name' => 'description_font_size',
                    'value' => '18px',
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Line height', 'milenia-app-textdomain'),
                    'param_name' => 'description_line_height',
                    'value' => '30px',
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font weight', 'milenia-app-textdomain'),
                    'param_name' => 'description_font_weight',
                    'value' => array(
                        '400' => '400',
                        '900' => '900',
                        '800' => '800',
                        '700' => '700',
                        '600' => '600',
                        '500' => '500',
                        '300' => '300',
                        '200' => '200',
                        '100' => '100',
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Font style', 'milenia-app-textdomain'),
                    'param_name' => 'description_font_style',
                    'value' => array(
                        esc_html__('Normal', 'milenia-app-textdomain') => 'normal',
                        esc_html__('Italic', 'milenia-app-textdomain') => 'italic'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Text transform', 'milenia-app-textdomain'),
                    'param_name' => 'description_text_transform',
                    'value' => array(
                        esc_html__('None', 'milenia-app-textdomain') => 'none',
                        esc_html__('Uppercase', 'milenia-app-textdomain') => 'uppercase',
                        esc_html__('Lowercase', 'milenia-app-textdomain') => 'lowercase',
                        esc_html__('Capitalize', 'milenia-app-textdomain') => 'capitalize'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Letter spacing', 'milenia-app-textdomain'),
                    'param_name' => 'description_letter_spacing',
                    'value' => '',
                    'admin_label' => false,
                    'group' => esc_html__('Description Settings', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_section_heading', array($this, 'content'));
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
			'main_heading' => '',
            'main_heading_color' => '',
            'main_heading_font' => 'milenia-font--first-accented',
            'main_heading_font_size' => '52px',
            'main_heading_line_height' => '58px',
            'main_heading_font_style' => 'normal',
            'main_heading_font_weight' => '400',
            'main_heading_letter_spacing' => '',
            'main_heading_text_transform' => 'none',
            'main_heading_margin_bottom' => '37px',

            'subheading' => '',
            'subheading_color' => '',
            'subheading_font' => 'milenia-font--like-body',
            'subheading_font_size' => '14px',
            'subheading_line_height' => '20px',
            'subheading_font_style' => 'normal',
            'subheading_font_weight' => '400',
            'subheading_letter_spacing' => '4.2px',
            'subheading_text_transform' => 'uppercase',
            'subheading_margin_bottom' => '10px',

            'description' => '',
            'description_color' => '',
            'description_font' => 'milenia-font--like-body',
            'description_font_size' => '18px',
            'description_line_height' => '30px',
            'description_font_style' => 'normal',
            'description_font_weight' => '400',
            'description_letter_spacing' => '',
            'description_text_transform' => 'none',

            'text_alignment' => 'text-left',
            'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_section_heading' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-section-heading');
        $text_alignment = $this->throughWhiteList($this->attributes['text_alignment'], array('text-left', 'text-center', 'text-right'), 'text-left');

		$container_classes = array('milenia-section-heading', $text_alignment);
        $main_heading = '';
        $main_heading_style = '';
        $main_heading_classes = array('milenia-section-title');
        $subheading = '';
        $subheading_style = '';
        $subheading_classes = array('milenia-section-subtitle');
        $description = '';
        $description_style = '';
        $description_classes = array('milenia-section-description');

        if(!empty($this->attributes['main_heading']))
        {
            $main_heading_font = $this->throughWhiteList($this->attributes['main_heading_font'], array('milenia-font--first-accented', 'milenia-font--like-body', 'milenia-font--second-accented'), 'milenia-font--first-accented');
            $main_heading_text_transform = $this->throughWhiteList($this->attributes['main_heading_text_transform'], array('uppercase', 'lowercase', 'capitalize', 'none'), 'none');
            $main_heading_font_style = $this->throughWhiteList($this->attributes['main_heading_font_style'], array('italic', 'normal'), 'normal');
            $main_heading_font_weight = $this->throughWhiteList($this->attributes['main_heading_font_weight'], array('900', '800', '700', '600', '500', '400', '300', '200', '100'), '400');

            array_push($main_heading_classes, $main_heading_font);

            if(!empty($this->attributes['main_heading_color']))
            {
                $main_heading_style .= sprintf('color: %s;', $this->attributes['main_heading_color']);
            }
            if(!empty($this->attributes['main_heading_font_size']))
            {
                $main_heading_style .= sprintf('font-size: %s;', $this->attributes['main_heading_font_size']);
            }
            if(!empty($this->attributes['main_heading_line_height']))
            {
                $main_heading_style .= sprintf('line-height: %s;', $this->attributes['main_heading_line_height']);
            }
            if(!empty($this->attributes['main_heading_letter_spacing']))
            {
                $main_heading_style .= sprintf('letter-spacing: %s;', $this->attributes['main_heading_letter_spacing']);
            }
            if(!empty($this->attributes['main_heading_margin_bottom']))
            {
                $main_heading_style .= sprintf('margin-bottom: %s;', $this->attributes['main_heading_margin_bottom']);
            }

            $main_heading_style .= sprintf('text-transform: %s;', $main_heading_text_transform);
            $main_heading_style .= sprintf('font-style: %s;', $main_heading_font_style);
            $main_heading_style .= sprintf('font-weight: %s;', $main_heading_font_weight);

            $main_heading = $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-section-heading/main.tpl'), array(
                '${text}' => esc_html($this->attributes['main_heading']),
                '${style}' => esc_attr($main_heading_style),
                '${heading_classes}' => esc_attr($this->sanitizeHtmlClasses($main_heading_classes))
    		));
        }

        if(!empty($this->attributes['subheading']))
        {
            $subheading_font = $this->throughWhiteList($this->attributes['subheading_font'], array('milenia-font--first-accented', 'milenia-font--like-body', 'milenia-font--second-accented'), 'milenia-font--like-body');
            $subheading_text_transform = $this->throughWhiteList($this->attributes['subheading_text_transform'], array('uppercase', 'lowercase', 'capitalize', 'none'), 'uppercase');
            $subheading_font_style = $this->throughWhiteList($this->attributes['subheading_font_style'], array('italic', 'normal'), 'normal');
            $subheading_font_weight = $this->throughWhiteList($this->attributes['subheading_font_weight'], array('900', '800', '700', '600', '500', '400', '300', '200', '100'), '400');

            array_push($subheading_classes, $subheading_font);

            if(!empty($this->attributes['subheading_color']))
            {
                $subheading_style .= sprintf('color: %s;', $this->attributes['subheading_color']);
            }
            if(!empty($this->attributes['subheading_font_size']))
            {
                $subheading_style .= sprintf('font-size: %s;', $this->attributes['subheading_font_size']);
            }
            if(!empty($this->attributes['subheading_line_height']))
            {
                $subheading_style .= sprintf('line-height: %s;', $this->attributes['subheading_line_height']);
            }
            if(!empty($this->attributes['subheading_letter_spacing']))
            {
                $subheading_style .= sprintf('letter-spacing: %s;', $this->attributes['subheading_letter_spacing']);
            }
            if(!empty($this->attributes['subheading_margin_bottom']))
            {
                $subheading_style .= sprintf('margin-bottom: %s;', $this->attributes['subheading_margin_bottom']);
            }

            $subheading_style .= sprintf('text-transform: %s;', $subheading_text_transform);
            $subheading_style .= sprintf('font-style: %s;', $subheading_font_style);
            $subheading_style .= sprintf('font-weight: %s;', $subheading_font_weight);

            $subheading = $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-section-heading/subheading.tpl'), array(
                '${text}' => esc_html($this->attributes['subheading']),
                '${style}' => esc_attr($subheading_style),
                '${heading_classes}' => esc_attr($this->sanitizeHtmlClasses($subheading_classes))
    		));
        }

        if(!empty($this->attributes['description']))
        {
            $description_font = $this->throughWhiteList($this->attributes['description_font'], array('milenia-font--first-accented', 'milenia-font--like-body', 'milenia-font--second-accented'), 'milenia-font--like-body');
            $description_text_transform = $this->throughWhiteList($this->attributes['description_text_transform'], array('uppercase', 'lowercase', 'capitalize', 'none'), 'uppercase');
            $description_font_style = $this->throughWhiteList($this->attributes['description_font_style'], array('italic', 'normal'), 'normal');
            $description_font_weight = $this->throughWhiteList($this->attributes['description_font_weight'], array('900', '800', '700', '600', '500', '400', '300', '200', '100'), '400');

            array_push($description_classes, $description_font);

            if(!empty($this->attributes['description_color']))
            {
                $description_style .= sprintf('color: %s;', $this->attributes['description_color']);
            }
            if(!empty($this->attributes['description_font_size']))
            {
                $description_style .= sprintf('font-size: %s;', $this->attributes['description_font_size']);
            }
            if(!empty($this->attributes['description_line_height']))
            {
                $description_style .= sprintf('line-height: %s;', $this->attributes['description_line_height']);
            }
            if(!empty($this->attributes['description_letter_spacing']))
            {
                $description_style .= sprintf('letter-spacing: %s;', $this->attributes['description_letter_spacing']);
            }

            $description_style .= sprintf('text-transform: %s;', $description_text_transform);
            $description_style .= sprintf('font-style: %s;', $description_font_style);
            $description_style .= sprintf('font-weight: %s;', $description_font_weight);

            $description = $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-section-heading/description.tpl'), array(
                '${text}' => esc_html($this->attributes['description']),
                '${style}' => esc_attr($description_style),
                '${description_classes}' => esc_attr($this->sanitizeHtmlClasses($description_classes))
    		));
        }

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_section_heading', $this->attributes ));

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-section-heading/container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
			'${main_heading}' => $main_heading,
            '${subheading}' => $subheading,
            '${description}' => $description,
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
	}
}
?>
