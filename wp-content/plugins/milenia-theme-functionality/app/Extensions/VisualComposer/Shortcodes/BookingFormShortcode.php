<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class BookingFormShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Booking Form', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_booking_form',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a booking form.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true,
                    'dependency' => array(
                        'element' => 'style',
                        'value' => array('milenia-booking-form-wrapper--v2', 'milenia-booking-form-wrapper--v4')
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-booking-form-wrapper--v1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-booking-form-wrapper--v2',
                        esc_html__('Style 3', 'milenia-app-textdomain') => 'milenia-booking-form-wrapper--v3',
                        esc_html__('Style 4', 'milenia-app-textdomain') => 'milenia-booking-form-wrapper--v4'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Color scheme', 'milenia-app-textdomain'),
                    'param_name' => 'scheme',
                    'value' => array(
                        esc_html__('Dark', 'milenia-app-textdomain') => 'milenia-booking-form-wrapper--dark',
                        esc_html__('Light', 'milenia-app-textdomain') => 'milenia-booking-form-wrapper--light'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Adults', 'milenia-app-textdomain'),
                    'param_name' => 'adults',
                    'description' => esc_html__('The number of adults presetted in the search form. (1...30)', 'milenia-app-textdomain'),
                    'value' => '1',
                    'admin_label' => false,
                    'group' => esc_html__('Booking Form', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Children', 'milenia-app-textdomain'),
                    'param_name' => 'children',
                    'description' => esc_html__('The number of children presetted in the search form. (0...10)', 'milenia-app-textdomain'),
                    'value' => '3',
                    'admin_label' => false,
                    'group' => esc_html__('Booking Form', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Check-in date', 'milenia-app-textdomain'),
                    'param_name' => 'check_in_date',
                    'description' => esc_html__('Check-in date presetted in the search form. Date in format d/m/Y.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Booking Form', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Check-out date', 'milenia-app-textdomain'),
                    'param_name' => 'check_out_date',
                    'description' => esc_html__('Check-out date presetted in the search form. Date in format d/m/Y.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Booking Form', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Attributes', 'milenia-app-textdomain'),
                    'param_name' => 'attributes',
                    'description' => esc_html__('Custom attributes (comma-separated).', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Booking Form', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_booking_form', array($this, 'content'));
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
            'adults'		 => MPHB()->settings()->main()->getMinAdults(),
            'children'		 => MPHB()->settings()->main()->getMinChildren(),
            'check_in_date' => '',
            'check_out_date' => '',
            'attributes' => '',

            'style' => 'milenia-booking-form-wrapper--v1',
            'scheme' => 'milenia-booking-form-wrapper--dark',

			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_booking_form' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-booking-form');


		// Sanitization of the attributes
		$this->style = $this->throughWhiteList($this->attributes['style'], array('milenia-booking-form-wrapper--v1', 'milenia-booking-form-wrapper--v2', 'milenia-booking-form-wrapper--v3', 'milenia-booking-form-wrapper--v4'), 'milenia-booking-form-wrapper--v1');
		$this->scheme = $this->throughWhiteList($this->attributes['scheme'], array( 'milenia-booking-form-wrapper--dark', 'milenia-booking-form-wrapper--light' ), 'milenia-booking-form-wrapper--dark');
        $container_classes = array();

        if($this->style == 'milenia-booking-form-wrapper--v4' && $this->scheme == 'milenia-booking-form-wrapper--light')
        {
            $container_classes[] = 'milenia-form--fields-darken';
        }

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_booking_form', $this->attributes ));

		if(!empty($this->attributes['milenia_extra_class_name']))
		{
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
		{
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-booking-form.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => wp_kses($this->attributes['milenia_widget_title'], array(
                'br' => array(),
                'small' => array()
            )),
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation']),
            '${form}' => do_shortcode(sprintf(
                '[mphb_availability_search adults="%d" children="%d" check_in_date="%s" check_out_date="%s" class="%s" attributes="%s"][/mphb_availability_search]',
                $this->attributes['adults'],
                $this->attributes['children'],
                $this->attributes['check_in_date'],
                $this->attributes['check_out_date'],
                $this->style . ' ' . $this->scheme,
                trim($this->attributes['attributes'])
            ))
		));
    }
}
?>
