<?php
namespace Milenia\Core\Extensions\VisualComposer;

use Milenia\Core\App;

class VisualComposerExtension extends VisualComposerExtensionAbstract
{
    /**
     * Contains uri of to the Visual Composer work directory.
     *
     * @access protected
     * @var string
     */
    protected $work_directory_uri = MILENIA_FUNCTIONALITY_CORE . 'Extensions/VisualComposer';

    /**
     * Contains all custom shortcodes.
     *
     * @access protected
     * @var array:VisualComposerShortcodeInterface
     */
    protected $shortcodes = array();

    /**
     * Constructor of the class.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Describes an action of the vc_before_init hook.
     *
     * @access protected
     */
    public function beforeInitComposer()
    {
        $this->setAsTheme()->integrateVCShortcodes();
    }

    /**
	 * Registers a shortcode in the visual composer.
	 *
	 * @param VisualComposerShortcodeInterface $shortcode
     * @abstract
	 * @access public
	 * @return VisualComposerExtensionAbstract
	 */
	public function addShortcode(VisualComposerShortcodeInterface $shortcode)
    {
        $this->shortcodes[] = $shortcode;

        return $this;
    }

    /**
     * Sets the Visual Composer as a part of the theme.
     *
     * @access protected
     * @return MileniaVC
     */
    protected function setAsTheme()
    {
        vc_set_as_theme();
        vc_set_shortcodes_templates_dir(MILENIA_FUNCTIONALITY_ROOT . App::get('config')['visual-composer']['vc_templates_path']);

        return $this;
    }

    /**
     * Integrates custom shortcodes into Visual Composer.
     *
     * @access protected
     * @return MileniaVC
     */
    protected function integrateVCShortcodes()
    {
        if(!empty($this->shortcodes))
        {
            foreach ($this->shortcodes as $shortcode)
            {
                vc_map($shortcode->getParams());
                $shortcode->register();
            }
        }

        return $this;

        /* Integration of the 'Social Icons' shortcode
        /* ---------------------------------------------------------------------- */
        vc_map( array(
            'name' => esc_html__('Social Icons', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_social_icons',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a set of social icons.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'param_group',
                    'heading' => esc_html__('Icons', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_icons',
                    'params' => array(
                        array(
                            'type' => 'iconpicker',
                            'heading' => esc_html__('Icon', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon',
                            'settings' => array(
                                'emptyIcon' => true,
                                'type' => 'fontawesome',
                                'iconsPerPage' => 200,
                            ),
                            'description' => esc_html__('Select icon from library.', 'milenia-app-textdomain'),
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__('Icon link settings', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_icon_link_settings',
                            'admin_label' => false
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Alignment', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_icons_alignment',
                    'value' => array(
                        esc_html__('Left', 'milenia-app-textdomain') => 'apo-align-left',
                        esc_html__('Center', 'milenia-app-textdomain') => 'apo-align-center',
                        esc_html__('Right', 'milenia-app-textdomain') => 'apo-align-right'
                    ),
                    'description' => esc_html__('Select the links alignment.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_icons_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'apo-style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'apo-style-2'
                    ),
                    'admin_label' => false,
                ),
                vc_map_add_css_animation(),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Extra class name', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_extra_class_name',
                    'admin_label' => false,
                    'description' => esc_html__('Style particular content element differently - add a class name and refer to it in custom CSS.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => esc_html__('Css', 'milenia-app-textdomain'),
                    'param_name' => 'css',
                    'group' => esc_html__('Design options', 'milenia-app-textdomain')
                )
            )
        ) );
    }
}
?>
