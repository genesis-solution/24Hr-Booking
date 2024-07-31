<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class GoogleMapShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Google Map', 'milenia-app-textdomain'),
    		'base' => 'vc_milenia_google_map',
    		'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
    		'description' => esc_html__('Creates a google map.', 'milenia-app-textdomain'),
    		'params' => array(
    			array(
    				'type' => 'param_group',
    				'heading' => esc_html__( 'Locations', 'milenia-app-textdomain' ),
    				'param_name' => 'locations',
    				'params' => array(
    					array(
    						'type' => 'textfield',
    						'heading' => esc_html__('Latitude (required)', 'milenia-app-textdomain'),
    						'param_name' => 'lat',
    						'admin_label' => false
    					),
    					array(
    						'type' => 'textfield',
    						'heading' => esc_html__('Longitude (required)', 'milenia-app-textdomain'),
    						'param_name' => 'lon',
    						'admin_label' => false
    					),
    					array(
    						'type' => 'attach_image',
    						'heading' => esc_html__('Marker (required)', 'milenia-app-textdomain'),
    						'param_name' => 'icon',
                            'admin_label' => false
    					),
    					array(
    						'type' => 'textfield',
    						'heading' => esc_html__('Title', 'milenia-app-textdomain'),
    						'param_name' => 'title',
    						'description' => esc_html__('Enter a title that will be displayed as the tooltip.', 'milenia-app-textdomain'),
    						'admin_label' => true
    					)
    				)
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[Default] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width',
    				'value' => '100%',
    				'admin_label' => false,
    				'description' => esc_html__( 'Enter default map width.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[Default] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height',
    				'value' => '340px',
    				'admin_label' => false,
    				'description' => esc_html__( 'Enter default map height.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[xxxl] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width_xxxl',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 1600px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[xxl] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width_xxl',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 1360px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[xl] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width_xl',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 1200px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[lg] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width_lg',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 992px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[md] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width_md',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 768px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
                array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[sm] Width', 'milenia-app-textdomain' ),
    				'param_name' => 'width_sm',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 576px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[xxxl] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height_xxxl',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 1600px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[xxl] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height_xxl',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 1360px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[xl] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height_xl',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 1200px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[lg] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height_lg',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 992px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[md] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height_md',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 768px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( '[sm] Height', 'milenia-app-textdomain' ),
    				'param_name' => 'height_sm',
    				'value' => '',
    				'admin_label' => false,
    				'description' => esc_html__( 'Screen sizes greater than 576px.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
    			),



    			array(
    				'type' => 'textfield',
    				'heading' => esc_html__( 'Zoom', 'milenia-app-textdomain' ),
    				'param_name' => 'zoom',
    				'value' => 16,
    				'description' => esc_html__( 'Initial zoom value.', 'milenia-app-textdomain' ),
    				'group' => esc_html__( 'Map Options', 'milenia-app-textdomain' )
    			),
    			array(
    				'type' => 'checkbox',
    				'heading' => esc_html__( 'Scrollwheel', 'milenia-app-textdomain' ),
    				'param_name' => 'scrollwheel',
    				'value' => false,
    				'group' => esc_html__( 'Map Options', 'milenia-app-textdomain' )
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
    				'param_name' => 'extra_class_name',
    				'admin_label' => true,
    				'description' => esc_html__( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'milenia-app-textdomain' )
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
        add_shortcode('vc_milenia_google_map', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['locations'] = vc_param_group_parse_atts( $atts['locations'] );

		$this->attributes = shortcode_atts( array(
            'locations' => array(),
    		'width' => '100%',
    		'width_xxxl' => '',
    		'width_xxl' => '',
    		'width_xl' => '',
    		'width_lg' => '',
    		'width_md' => '',
    		'width_sm' => '',

    		'height' => '340px',
    		'height_xxxl' => '',
    		'height_xxl' => '',
    		'height_xl' => '',
    		'height_lg' => '',
    		'height_md' => '',
    		'height_sm' => '',

    		'zoom' => 16,
    		'scrollwheel' => false,
            'css' => '',
    		'css_animation' => '',
    		'extra_class_name' => ''
		), $atts, 'vc_milenia_google_map' );

		wp_enqueue_script('maplace');

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-panels');
		$container_classes = array('milenia-gmap');
        $inline_css = '';

        $map_options = array(
            'zoom' => intval($this->attributes['zoom']),
            'scrollwheel' => boolval($this->attributes['scrollwheel'])
        );

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_google_map', $this->attributes ));

    	if($this->attributes['locations'])
        {
    		// Filtering of location data
    		foreach( $this->attributes['locations'] as $index => &$location ) {
    			if( !isset( $location['lat'] ) || !isset( $location['lon'] ) || !isset( $location['icon'] ) ) {
    				array_splice($this->attributes['locations'], $index, 1);
    				continue;
    			}
    			if(isset($location['icon']))
                {
    				$location['icon'] = wp_get_attachment_image_url( $location['icon'], 'full' );
    			}
    			if(isset($location['title']))
                {
    				$location['title'] = esc_html( $location['title'] );
    			}
    		}
    	}


        $default = array();
        $xxxl = array();
        $xxl = array();
        $xl = array();
        $lg = array();
        $md = array();
        $sm = array();


        if(!empty($this->attributes['width']))
        {
            $default['width'] = $this->attributes['width'];
        }
        if(!empty($this->attributes['height']))
        {
            $default['height'] = $this->attributes['height'];
        }

        if(!empty($this->attributes['width_xxxl']))
        {
            $xxxl['width'] = $this->attributes['width_xxxl'];
        }
        if(!empty($this->attributes['height_xxxl']))
        {
            $xxxl['height'] = $this->attributes['height_xxxl'];
        }

        if(!empty($this->attributes['width_xxl']))
        {
            $xxl['width'] = $this->attributes['width_xxl'];
        }
        if(!empty($this->attributes['height_xxl']))
        {
            $xxl['height'] = $this->attributes['height_xxl'];
        }

        if(!empty($this->attributes['width_xl']))
        {
            $xl['width'] = $this->attributes['width_xl'];
        }
        if(!empty($this->attributes['height_xl']))
        {
            $xl['height'] = $this->attributes['height_xl'];
        }

        if(!empty($this->attributes['width_lg']))
        {
            $lg['width'] = $this->attributes['width_lg'];
        }
        if(!empty($this->attributes['height_lg']))
        {
            $lg['height'] = $this->attributes['height_lg'];
        }

        if(!empty($this->attributes['width_md']))
        {
            $md['width'] = $this->attributes['width_md'];
        }
        if(!empty($this->attributes['height_md']))
        {
            $md['height'] = $this->attributes['height_md'];
        }

        if(!empty($this->attributes['width_sm']))
        {
            $sm['width'] = $this->attributes['width_sm'];
        }
        if(!empty($this->attributes['height_sm']))
        {
            $sm['height'] = $this->attributes['height_sm'];
        }


        if(!empty($default))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $default);
        }
        if(!empty($xxxl))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $xxxl, '@media all and (min-width: 1600px)');
        }
        if(!empty($xxl))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $xxl, '@media all and (min-width: 1360px)');
        }
        if(!empty($xl))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $xl, '@media all and (min-width: 1200px)');
        }
        if(!empty($lg))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $lg, '@media all and (min-width: 992px)');
        }
        if(!empty($md))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $md, '@media all and (min-width: 768px)');
        }
        if(!empty($sm))
        {
            $inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $sm, '@media all and (min-width: 576px)');
        }

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-google-map-container.tpl'), array(
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${unique_id}' => esc_attr($this->unique_id),
    		'${locations}' => !empty($this->attributes['locations']) ? wp_json_encode( $this->attributes['locations'] ) : '',
    		'${width}' => esc_attr($this->attributes['width']),
    		'${height}' => esc_attr($this->attributes['height']),
    		'${map_options}' => wp_json_encode( $map_options ),
    		'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
            '${css_animation}' => esc_attr($this->attributes['css_animation']),
            '${data_row_css}' => esc_attr($inline_css)
		));
    }
}
?>
