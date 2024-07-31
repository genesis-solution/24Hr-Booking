<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeContainerBase;

class FlexibleGridColumnShortcode extends VisualComposerExtensionShortcodeContainerBase implements VisualComposerShortcodeInterface
{
    /**
     * Contains dynamically generated css code of the shortcode.
     *
     * @var string $inline_css
     * @access protected
     */
    protected $inline_css = '';

    /**
     * Contains css classes of the container element.
     *
     * @var array
     * @access protected
     */
    protected $container_classes = array();

    /**
     * Contains css classes of the colorizer element.
     *
     * @var array
     * @access protected
     */
    protected $colorizer_classes = array();

    /**
     * Contains attributes array of the colorizer element.
     *
     * @var array
     * @access protected
     */
    protected $colorizer_attributes = array(
        'data-bg-image-src' => '',
        'data-bg-image-opacity' => '',
        'data-bg-color' => ''
    );

    /**
     * Returns a parameters array of the shortcode.
     *
     * @access public
     * @return array
     */
    public function getParams()
    {
        return array(
            'name' => esc_html__('Flexible grid column', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_flexible_grid_column',
            'as_child' => array('only' => 'vc_milenia_flexible_grid'),
            'content_element' => true,
            'is_container' => true,
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a flexible grid column.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Color scheme', 'milenia-app-textdomain' ),
                    'param_name' => 'color_scheme',
                    'value' => array(
                        esc_html__( 'Custom', 'milenia-app-textdomain' ) => 'custom',
                        esc_html__( '[Current page scheme] Primary', 'milenia-app-textdomain' ) => 'primary',
                        esc_html__( '[Current page scheme] Secondary', 'milenia-app-textdomain' ) => 'secondary',
                        esc_html__( '[Blue scheme] Primary', 'milenia-app-textdomain' ) => 'blue|primary',
                        esc_html__( '[Lightbrown scheme] Primary', 'milenia-app-textdomain' ) => 'lightbrown|primary',
                        esc_html__( '[Gray scheme] Primary', 'milenia-app-textdomain' ) => 'gray|primary',
                        esc_html__( '[Green scheme] Primary', 'milenia-app-textdomain' ) => 'green|primary',
                        esc_html__( '[Green scheme] Secondary', 'milenia-app-textdomain' ) => 'green|secondary',
                        esc_html__( 'Light', 'milenia-app-textdomain' ) => 'light',
                        esc_html__( 'Dark', 'milenia-app-textdomain' ) => 'dark'
                    ),
                    'description' => esc_html__( 'Select color scheme of the column.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
                ),

                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Text color', 'milenia-app-textdomain'),
                    'param_name' => 'text_color',
                    'value' => '',
                    'description' => esc_html__('Select text color.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Links color', 'milenia-app-textdomain'),
                    'param_name' => 'links_color',
                    'value' => '',
                    'description' => esc_html__('Select links color.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Background color', 'milenia-app-textdomain'),
                    'param_name' => 'background_color',
                    'value' => '',
                    'description' => esc_html__('Select background color.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'color_scheme',
                        'value' => array('custom')
                    )
                ),
                array(
                    'type' => 'attach_image',
                    'heading' => esc_html__('Background image', 'milenia-app-textdomain'),
                    'param_name' => 'background_image',
                    'value' => '',
                    'description' => esc_html__('Select background image.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'color_scheme',
                        'value' => array('custom')
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Parallax', 'milenia-app-textdomain'),
                    'param_name' => 'background_parallax',
                    'value' => array(
                        esc_html__('Disabled', 'milenia-app-textdomain') => 'disabled',
                        esc_html__('Enabled', 'milenia-app-textdomain') => 'enabled'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'background_image',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Image transparency', 'milenia-app-textdomain'),
                    'param_name' => 'background_transparency',
                    'value' => array(
                        '1' => '1',
                        '0.9' => '0.9',
                        '0.8' => '0.8',
                        '0.7' => '0.7',
                        '0.6' => '0.6',
                        '0.5' => '0.5',
                        '0.4' => '0.4',
                        '0.3' => '0.3',
                        '0.2' => '0.2',
                        '0.1' => '0.1'
                    ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'background_image',
                        'not_empty' => true
                    )
                ),

                // Geometry tab
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[Default] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-1'
                    ),
                    'description' => esc_html__( 'Select default column size.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[xxxl] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size_xxxl',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxxl-1'
                    ),
                    'description' => esc_html__( 'Screen sizes greater than 1600px.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[xxl] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size_xxl',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xxl-1'
                    ),
                    'description' => esc_html__( 'Screen sizes greater than 1380px.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[xl] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size_xl',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-xl-1'
                    ),
                    'description' => esc_html__( 'Screen sizes greater than 1200px.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[lg] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size_lg',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-lg-1'
                    ),
                    'description' => esc_html__( 'Screen sizes greater than 992px.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[md] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size_md',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-md-1'
                    ),
                    'description' => esc_html__( 'Screen sizes greater than 768px.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('[sm] Column size', 'milenia-app-textdomain'),
                    'param_name' => 'column_size_sm',
                    'value' => array(
                        esc_html__('12 columns 1/1', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-12',
                        esc_html__('11 columns 11/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-11',
                        esc_html__('10 columns 5/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-10',
                        esc_html__('9 columns 3/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-9',
                        esc_html__('8 columns 2/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-8',
                        esc_html__('7 columns 7/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-7',
                        esc_html__('6 columns 1/2', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-6',
                        esc_html__('5 columns 5/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-5',
                        esc_html__('4 columns 1/3', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-4',
                        esc_html__('3 columns 1/4', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-3',
                        esc_html__('2 columns 1/6', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-2',
                        esc_html__('1 columns 1/12', 'milenia-app-textdomain') => 'milenia-flexible-grid-col-sm-1'
                    ),
                    'description' => esc_html__( 'Screen sizes greater than 576px.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),

                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Default] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top',
                    'value' => '90px',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' ),
                    'vc_single_param_edit_holder_class' => 'vc_col-xs-4'
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxxl] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top_xxxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxl] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top_xxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xl] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top_xl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[lg] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top_lg',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[md] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top_md',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[sm] Padding top', 'milenia-app-textdomain'),
                    'param_name' => 'padding_top_sm',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),

                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Default] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right',
                    'value' => '30px',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxxl] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right_xxxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxl] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right_xxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xl] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right_xl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[lg] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right_lg',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[md] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right_md',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[sm] Padding right', 'milenia-app-textdomain'),
                    'param_name' => 'padding_right_sm',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),


                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Default] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom',
                    'value' => '90px',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxxl] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom_xxxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxl] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom_xxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xl] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom_xl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[lg] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom_lg',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[md] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom_md',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[sm] Padding bottom', 'milenia-app-textdomain'),
                    'param_name' => 'padding_bottom_sm',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),

                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Default] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left',
                    'value' => '30px',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxxl] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left_xxxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xxl] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left_xxl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[xl] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left_xl',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[lg] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left_lg',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[md] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left_md',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[sm] Padding left', 'milenia-app-textdomain'),
                    'param_name' => 'padding_left_sm',
                    'value' => '',
                    'description' => esc_html__( 'All available css units.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Geometry', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Horizontal text alignment', 'milenia-app-textdomain'),
                    'param_name' => 'text_alignment_horizontal',
                    'value' => array(
                        esc_html__('Left', 'milenia-app-textdomain') => 'text-left',
                        esc_html__('Center', 'milenia-app-textdomain') => 'text-center',
                        esc_html__('Right', 'milenia-app-textdomain') => 'text-right'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Vertical text alignment', 'milenia-app-textdomain'),
                    'param_name' => 'text_alignment_vertical',
                    'value' => array(
                        esc_html__('Top', 'milenia-app-textdomain') => 'milenia-aligner--valign-top',
                        esc_html__('Middle', 'milenia-app-textdomain') => 'milenia-aligner--valign-middle',
                        esc_html__('Bottom', 'milenia-app-textdomain') => 'milenia-aligner--valign-bottom'
                    ),
                    'admin_label' => false
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
        add_shortcode('vc_milenia_flexible_grid_column', array($this, 'content'));
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
            'text_color' => '',
            'headings_color' => '',
            'links_color' => '',
            'background_color' => '',
            'background_image' => '',
            'background_parallax' => 'disabled',
            'background_transparency' => '1',
            'color_scheme' => 'custom',

            'padding_top' => '90px',
            'padding_top_xxxl' => '',
            'padding_top_xxl' => '',
            'padding_top_xl' => '',
            'padding_top_lg' => '',
            'padding_top_md' => '',
            'padding_top_sm' => '',

            'padding_right' => '30px',
            'padding_right_xxxl' => '',
            'padding_right_xxl' => '',
            'padding_right_xl' => '',
            'padding_right_lg' => '',
            'padding_right_md' => '',
            'padding_right_sm' => '',

            'padding_left' => '30px',
            'padding_left_xxxl' => '',
            'padding_left_xxl' => '',
            'padding_left_xl' => '',
            'padding_left_lg' => '',
            'padding_left_md' => '',
            'padding_left_sm' => '',

            'padding_bottom' => '90px',
            'padding_bottom_xxxl' => '',
            'padding_bottom_xxl' => '',
            'padding_bottom_xl' => '',
            'padding_bottom_lg' => '',
            'padding_bottom_md' => '',
            'padding_bottom_sm' => '',

            'column_size' => 'milenia-flexible-grid-col-12',
            'column_size_xxxl' => 'milenia-flexible-grid-col-12',
            'column_size_xxl' => 'milenia-flexible-grid-col-12',
            'column_size_xl' => 'milenia-flexible-grid-col-12',
            'column_size_lg' => 'milenia-flexible-grid-col-12',
            'column_size_md' => 'milenia-flexible-grid-col-12',
            'column_size_sm' => 'milenia-flexible-grid-col-12',

            'text_alignment_horizontal' => 'text-left',
            'text_alignment_vertical' => 'milenia-aligner--valign-top',
            'css_animation' => 'none',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_flexible_grid_column' );

        $this->reset();

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-flexible-grid-col');
        $text_alignment_horizontal = $this->throughWhiteList($this->attributes['text_alignment_horizontal'], array(
            'text-left',
            'text-center',
            'text-right'
        ), 'text-left');
        $text_alignment_vertical = $this->throughWhiteList($this->attributes['text_alignment_vertical'], array(
            'milenia-aligner--valign-top',
            'milenia-aligner--valign-middle',
            'milenia-aligner--valign-bottom'
        ), 'milenia-aligner--valign-top');

        $column_size = $this->throughWhiteList($this->attributes['column_size'], array(
            'milenia-flexible-grid-col-12',
            'milenia-flexible-grid-col-11',
            'milenia-flexible-grid-col-10',
            'milenia-flexible-grid-col-9',
            'milenia-flexible-grid-col-8',
            'milenia-flexible-grid-col-7',
            'milenia-flexible-grid-col-6',
            'milenia-flexible-grid-col-5',
            'milenia-flexible-grid-col-4',
            'milenia-flexible-grid-col-3',
            'milenia-flexible-grid-col-2',
            'milenia-flexible-grid-col-1',
        ), 'milenia-flexible-grid-col-12');
        $column_size_xxxl = $this->throughWhiteList($this->attributes['column_size_xxxl'], array(
            'milenia-flexible-grid-col-xxxl-12',
            'milenia-flexible-grid-col-xxxl-11',
            'milenia-flexible-grid-col-xxxl-10',
            'milenia-flexible-grid-col-xxxl-9',
            'milenia-flexible-grid-col-xxxl-8',
            'milenia-flexible-grid-col-xxxl-7',
            'milenia-flexible-grid-col-xxxl-6',
            'milenia-flexible-grid-col-xxxl-5',
            'milenia-flexible-grid-col-xxxl-4',
            'milenia-flexible-grid-col-xxxl-3',
            'milenia-flexible-grid-col-xxxl-2',
            'milenia-flexible-grid-col-xxxl-1',
        ), 'milenia-flexible-grid-col-xxxl-12');
        $column_size_xxl = $this->throughWhiteList($this->attributes['column_size_xxl'], array(
            'milenia-flexible-grid-col-xxl-12',
            'milenia-flexible-grid-col-xxl-11',
            'milenia-flexible-grid-col-xxl-10',
            'milenia-flexible-grid-col-xxl-9',
            'milenia-flexible-grid-col-xxl-8',
            'milenia-flexible-grid-col-xxl-7',
            'milenia-flexible-grid-col-xxl-6',
            'milenia-flexible-grid-col-xxl-5',
            'milenia-flexible-grid-col-xxl-4',
            'milenia-flexible-grid-col-xxl-3',
            'milenia-flexible-grid-col-xxl-2',
            'milenia-flexible-grid-col-xxl-1',
        ), 'milenia-flexible-grid-col-xxl-12');
        $column_size_xl = $this->throughWhiteList($this->attributes['column_size_xl'], array(
            'milenia-flexible-grid-col-xl-12',
            'milenia-flexible-grid-col-xl-11',
            'milenia-flexible-grid-col-xl-10',
            'milenia-flexible-grid-col-xl-9',
            'milenia-flexible-grid-col-xl-8',
            'milenia-flexible-grid-col-xl-7',
            'milenia-flexible-grid-col-xl-6',
            'milenia-flexible-grid-col-xl-5',
            'milenia-flexible-grid-col-xl-4',
            'milenia-flexible-grid-col-xl-3',
            'milenia-flexible-grid-col-xl-2',
            'milenia-flexible-grid-col-xl-1',
        ), 'milenia-flexible-grid-col-xl-12');
        $column_size_lg = $this->throughWhiteList($this->attributes['column_size_lg'], array(
            'milenia-flexible-grid-col-lg-12',
            'milenia-flexible-grid-col-lg-11',
            'milenia-flexible-grid-col-lg-10',
            'milenia-flexible-grid-col-lg-9',
            'milenia-flexible-grid-col-lg-8',
            'milenia-flexible-grid-col-lg-7',
            'milenia-flexible-grid-col-lg-6',
            'milenia-flexible-grid-col-lg-5',
            'milenia-flexible-grid-col-lg-4',
            'milenia-flexible-grid-col-lg-3',
            'milenia-flexible-grid-col-lg-2',
            'milenia-flexible-grid-col-lg-1',
        ), 'milenia-flexible-grid-col-lg-12');
        $column_size_md = $this->throughWhiteList($this->attributes['column_size_md'], array(
            'milenia-flexible-grid-col-md-12',
            'milenia-flexible-grid-col-md-11',
            'milenia-flexible-grid-col-md-10',
            'milenia-flexible-grid-col-md-9',
            'milenia-flexible-grid-col-md-8',
            'milenia-flexible-grid-col-md-7',
            'milenia-flexible-grid-col-md-6',
            'milenia-flexible-grid-col-md-5',
            'milenia-flexible-grid-col-md-4',
            'milenia-flexible-grid-col-md-3',
            'milenia-flexible-grid-col-md-2',
            'milenia-flexible-grid-col-md-1',
        ), 'milenia-flexible-grid-col-md-12');
        $column_size_sm = $this->throughWhiteList($this->attributes['column_size_sm'], array(
            'milenia-flexible-grid-col-sm-12',
            'milenia-flexible-grid-col-sm-11',
            'milenia-flexible-grid-col-sm-10',
            'milenia-flexible-grid-col-sm-9',
            'milenia-flexible-grid-col-sm-8',
            'milenia-flexible-grid-col-sm-7',
            'milenia-flexible-grid-col-sm-6',
            'milenia-flexible-grid-col-sm-5',
            'milenia-flexible-grid-col-sm-4',
            'milenia-flexible-grid-col-sm-3',
            'milenia-flexible-grid-col-sm-2',
            'milenia-flexible-grid-col-sm-1',
        ), 'milenia-flexible-grid-col-sm-12');
        $this->color_scheme = $this->throughWhiteList($this->attributes['color_scheme'], array(
            'primary',
            'secondary',
            'blue|primary',
            'lightbrown|primary',
            'gray|primary',
            'green|primary',
            'green|secondary',
            'light',
            'dark',
            'custom'
        ), 'custom');

        $this->container_classes = array($text_alignment_vertical, $text_alignment_horizontal, $column_size, $column_size_xxxl, $column_size_xxl, $column_size_xl, $column_size_lg, $column_size_md, $column_size_sm);
        $this->appearance();
        $this->geometry();

        wp_enqueue_script('appearjs');

        if(!empty($this->attributes['milenia_extra_class_name']))
        {
            array_push($this->container_classes, $this->attributes['milenia_extra_class_name']);
        }
        if($this->attributes['css_animation'] == 'none')
        {
            array_push($this->container_classes, 'milenia-visible');
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-flexible-grid/item.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${container_classes}' => esc_attr($this->sanitizeHtmlClasses($this->container_classes)),
            '${content}' => do_shortcode($content),
            '${css_animation}' => esc_attr($this->attributes['css_animation']),
            '${colorizer_classes}' => esc_attr($this->sanitizeHtmlClasses($this->colorizer_classes)),
            '${data_bg_color_attribute}' => $this->colorizer_attributes['data-bg-color'],
            '${data_bg_image_attribute}' => $this->colorizer_attributes['data-bg-image-src'],
            '${data_bg_image_opacity_attribute}' => $this->colorizer_attributes['data-bg-image-opacity'],
            '${data_row_css}' => esc_attr($this->inline_css)
        ));
	}

    /**
     * Resets properties.
     *
     * @access protected
     * @return void
     */
    protected function reset()
    {
        $this->container_classes = array();
        $this->colorizer_classes = array();
        $this->colorizer_attributes = array(
            'data-bg-image-src' => '',
            'data-bg-image-opacity' => '',
            'data-bg-color' => ''
        );
        $this->inline_css = '';

    }

    /**
     * Applies necessary stylesheets.
     *
     * @access protected
     * @return void
     */
    protected function appearance()
    {
        if(!empty($this->attributes['text_color']))
        {
            $this->inline_css .= $this->makeCSS(
                sprintf('#%s', $this->unique_id),
                array(
                    'color' => $this->attributes['text_color']
                )
            );
        }

        if(!empty($this->attributes['links_color']))
        {
            $this->inline_css .= $this->makeCSS(
                sprintf('#%s a', $this->unique_id),
                array(
                    'color' => $this->attributes['links_color'],
                    'background-image' => sprintf('linear-gradient(to bottom, %1$s %2$s, %1$s %2$s)', $this->attributes['links_color'], '100%')
                )
            );
        }

        if($this->color_scheme != 'custom')
        {
            $color_scheme = explode('|', $this->color_scheme);

            if(count($color_scheme) > 1)
            {

                $this->container_classes[] = sprintf('milenia-body--scheme-%s', $color_scheme[0]);
                $this->colorizer_classes[] = sprintf('milenia-colorizer--scheme-%s', $color_scheme[1]);
            }
            else
            {
                $this->colorizer_classes[] = sprintf('milenia-colorizer--scheme-%s', $color_scheme[0]);
            }
        }
        else
        {
            $this->colorizer_classes[] = 'milenia-colorizer-functionality';

            if(!empty($this->attributes['background_color']))
            {
                $this->colorizer_attributes['data-bg-color'] = sprintf('data-bg-color="%s"', $this->attributes['background_color']);
            }
        }

        if(!empty($this->attributes['background_image']))
        {
            $this->colorizer_attributes['data-bg-image-src'] = sprintf('data-bg-image-src="%s"', wp_get_attachment_image_url(intval($this->attributes['background_image']), 'full'));
        }

        if(!empty($this->attributes['background_transparency']))
        {
            $this->colorizer_attributes['data-bg-image-opacity'] = sprintf('data-bg-image-opacity="%s"', $this->attributes['background_transparency']);
        }

        if($this->attributes['background_parallax'] == 'enabled')
        {
            $this->colorizer_classes[] = 'milenia-colorizer--parallax';
        }
    }

    /**
     * Applies necessary geometry styles.
     *
     * @access protected
     * @return void
     */
    protected function geometry()
    {
        $default = array();
        $xxxl = array();
        $xxl = array();
        $xl = array();
        $lg = array();
        $md = array();
        $sm = array();


        if(!empty($this->attributes['padding_top']))
        {
            $default['padding-top'] = $this->attributes['padding_top'];
        }
        if(!empty($this->attributes['padding_right']))
        {
            $default['padding-right'] = $this->attributes['padding_right'];
        }
        if(!empty($this->attributes['padding_bottom']))
        {
            $default['padding-bottom'] = $this->attributes['padding_bottom'];
        }
        if(!empty($this->attributes['padding_left']))
        {
            $default['padding-left'] = $this->attributes['padding_left'];
        }


        if(!empty($this->attributes['padding_top_xxxl']))
        {
            $xxxl['padding-top'] = $this->attributes['padding_top_xxxl'];
        }
        if(!empty($this->attributes['padding_right_xxxl']))
        {
            $xxxl['padding-right'] = $this->attributes['padding_right_xxxl'];
        }
        if(!empty($this->attributes['padding_bottom_xxxl']))
        {
            $xxxl['padding-bottom'] = $this->attributes['padding_bottom_xxxl'];
        }
        if(!empty($this->attributes['padding_left_xxxl']))
        {
            $xxxl['padding-left'] = $this->attributes['padding_left_xxxl'];
        }

        if(!empty($this->attributes['padding_top_xxl']))
        {
            $xxl['padding-top'] = $this->attributes['padding_top_xxl'];
        }
        if(!empty($this->attributes['padding_right_xxl']))
        {
            $xxl['padding-right'] = $this->attributes['padding_right_xxl'];
        }
        if(!empty($this->attributes['padding_bottom_xxl']))
        {
            $xxl['padding-bottom'] = $this->attributes['padding_bottom_xxl'];
        }
        if(!empty($this->attributes['padding_left_xxl']))
        {
            $xxl['padding-left'] = $this->attributes['padding_left_xxl'];
        }

        if(!empty($this->attributes['padding_top_xl']))
        {
            $xl['padding-top'] = $this->attributes['padding_top_xl'];
        }
        if(!empty($this->attributes['padding_right_xl']))
        {
            $xl['padding-right'] = $this->attributes['padding_right_xl'];
        }
        if(!empty($this->attributes['padding_bottom_xl']))
        {
            $xl['padding-bottom'] = $this->attributes['padding_bottom_xl'];
        }
        if(!empty($this->attributes['padding_left_xl']))
        {
            $xl['padding-left'] = $this->attributes['padding_left_xl'];
        }

        if(!empty($this->attributes['padding_top_lg']))
        {
            $lg['padding-top'] = $this->attributes['padding_top_lg'];
        }
        if(!empty($this->attributes['padding_right_lg']))
        {
            $lg['padding-right'] = $this->attributes['padding_right_lg'];
        }
        if(!empty($this->attributes['padding_bottom_lg']))
        {
            $lg['padding-bottom'] = $this->attributes['padding_bottom_lg'];
        }
        if(!empty($this->attributes['padding_left_lg']))
        {
            $lg['padding-left'] = $this->attributes['padding_left_lg'];
        }

        if(!empty($this->attributes['padding_top_md']))
        {
            $md['padding-top'] = $this->attributes['padding_top_md'];
        }
        if(!empty($this->attributes['padding_right_md']))
        {
            $md['padding-right'] = $this->attributes['padding_right_md'];
        }
        if(!empty($this->attributes['padding_bottom_md']))
        {
            $md['padding-bottom'] = $this->attributes['padding_bottom_md'];
        }
        if(!empty($this->attributes['padding_left_md']))
        {
            $md['padding-left'] = $this->attributes['padding_left_md'];
        }

        if(!empty($this->attributes['padding_top_sm']))
        {
            $sm['padding-top'] = $this->attributes['padding_top_sm'];
        }
        if(!empty($this->attributes['padding_right_sm']))
        {
            $sm['padding-right'] = $this->attributes['padding_right_sm'];
        }
        if(!empty($this->attributes['padding_bottom_sm']))
        {
            $sm['padding-bottom'] = $this->attributes['padding_bottom_sm'];
        }
        if(!empty($this->attributes['padding_left_sm']))
        {
            $sm['padding-left'] = $this->attributes['padding_left_sm'];
        }

        if(!empty($default))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $default);
        }
        if(!empty($xxxl))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $xxxl, '@media all and (min-width: 1600px)');
        }
        if(!empty($xxl))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $xxl, '@media all and (min-width: 1360px)');
        }
        if(!empty($xl))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $xl, '@media all and (min-width: 1200px)');
        }
        if(!empty($lg))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $lg, '@media all and (min-width: 992px)');
        }
        if(!empty($md))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $md, '@media all and (min-width: 768px)');
        }
        if(!empty($sm))
        {
            $this->inline_css .= $this->makeCSS(sprintf('#%s', $this->unique_id), $sm, '@media all and (min-width: 576px)');
        }
    }
}
?>
