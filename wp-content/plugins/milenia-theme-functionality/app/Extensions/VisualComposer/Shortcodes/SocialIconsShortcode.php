<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class SocialIconsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Social Icons', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_social_icons',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows social network icons.', 'milenia-app-textdomain'),
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
                    'heading' => esc_html__('Size', 'milenia-app-textdomain'),
                    'param_name' => 'size',
                    'value' => array(
                        esc_html__('Default', 'milenia-app-textdomain') => 'milenia-social-icons--default',
                        esc_html__('Huge', 'milenia-app-textdomain') => 'milenia-social-icons--huge'
                    ),
                    'description' => esc_html__('Select a type of the alert box.', 'milenia-app-textdomain'),
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
        add_shortcode('vc_milenia_social_icons', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        global $Milenia;

        if(!isset($Milenia)) return;

        $this->attributes = shortcode_atts( array(
			'milenia_widget_title' => '',
			'size' => 'milenia-social-icons--default',
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_social_icons' );
        $size = $this->throughWhiteList($this->attributes['size'], array(
			'milenia-social-icons--default',
			'milenia-social-icons--huge'
		), 'milenia-social-icons--default');
        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-social-icons');
        $container_classes = array('milenia-list--unstyled', 'milenia-social-icons', $size);
        $items_template = array();

        $facebook_profile = $Milenia->getThemeOption('milenia-social-links-facebook', '#');
        $google_plus_profile = $Milenia->getThemeOption('milenia-social-links-google-plus', '#');
        $twitter_profile = $Milenia->getThemeOption('milenia-social-links-twitter', '#');
        $tripadvisor_profile = $Milenia->getThemeOption('milenia-social-links-tripadvisor', '#');
        $instagram_profile = $Milenia->getThemeOption('milenia-social-links-instagram', '#');
        $youtube_profile = $Milenia->getThemeOption('milenia-social-links-youtube', '#');
        $flickr_profile = $Milenia->getThemeOption('milenia-social-links-flickr', '#');
        $booking_profile = $Milenia->getThemeOption('milenia-social-links-booking', '#');
        $airbnb_profile = $Milenia->getThemeOption('milenia-social-links-airbnb', '#');

        if(!empty($facebook_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-facebook-f"></i></a></li>', esc_url($facebook_profile));
        }
        if(!empty($google_plus_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-google-plus-g"></i></a></li>', esc_url($google_plus_profile));
        }
        if(!empty($twitter_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-twitter"></i></a></li>', esc_url($twitter_profile));
        }
        if(!empty($tripadvisor_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-tripadvisor"></i></a></li>', esc_url($tripadvisor_profile));
        }
        if(!empty($instagram_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-instagram"></i></a></li>', esc_url($instagram_profile));
        }
        if(!empty($youtube_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-youtube"></i></a></li>', esc_url($youtube_profile));
        }

        if(!empty($flickr_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-flickr"></i></a></li>', esc_url($flickr_profile));
        }

        if(!empty($booking_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="milenia-font-icon-1-icon-booking-icon"></i></a></li>', esc_url($booking_profile));
        }

        if(!empty($airbnb_profile))
        {
            $items_template[] = sprintf('<li><a href="%s"><i class="fab fa-airbnb"></i></a></li>', esc_url($airbnb_profile));
        }

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}

		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_social_icons', $this->attributes ));

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-social-icons-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
            '${items}' => implode('', $items_template),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
