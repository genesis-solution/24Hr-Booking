<?php
/**
 * Milenia functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package WordPress
 * @since Milenia 1.0
 */
 // Prevent the direct loading of the file
 if ( ! defined( 'ABSPATH' ) ) {
 	die( esc_html__('You cannot access this file directly', 'milenia') );
 }

 /*  Main Constants
 /* ---------------------------------------------------------------------- */
 define('MILENIA_TEMPLATE_DIRECTORY', get_template_directory());
 define('MILENIA_TEMPLATE_DIRECTORY_URI', get_template_directory_uri());
 define('MILENIA_STYLESHEET_DIRECTORY', get_stylesheet_directory());

/* Including of the required theme files
/* ---------------------------------------------------------------------- */
require_once(MILENIA_TEMPLATE_DIRECTORY . '/includes/functions-core.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/includes/hooks.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/includes/widget-areas.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/includes/config-contact-form-7/MileniaContactForm7Config.php');

require_once(MILENIA_TEMPLATE_DIRECTORY . '/admin/domain/interfaces/ConfigurableInterface.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/admin/domain/interfaces/LayoutInterface.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/admin/domain/MileniaConfigurator.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/admin/domain/MileniaLayout.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/admin/domain/MileniaAdmin.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/admin/milenia-plugins-bundle.php');

require_once(MILENIA_TEMPLATE_DIRECTORY . '/includes/domain/Milenia.php');
require_once(MILENIA_TEMPLATE_DIRECTORY . '/includes/domain/MileniaHelper.php');

/*  Theme support & Theme setup
/* ---------------------------------------------------------------------- */
if ( ! function_exists( 'milenia_setup' ) ) {
	function milenia_setup() {

        global $Milenia, $MileniaLayout, $content_width;

		$content_width = apply_filters( 'milenia_content_width', 1350 );

		// Load theme textdomain
		load_theme_textdomain( 'milenia', MILENIA_TEMPLATE_DIRECTORY  . '/lang' );
		load_child_theme_textdomain( 'milenia', MILENIA_STYLESHEET_DIRECTORY . '/lang' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption'
		) );

		// Post Thumbnails Support
		add_theme_support('post-thumbnails');

		// Add default posts and comments RSS feed links to head
		add_theme_support('automatic-feed-links');

		add_theme_support('title-tag');

        // Add custom image sizes
        add_image_size('entity-thumb-standard', $content_width, 900, true);
        add_image_size('entity-thumb-square', $content_width, $content_width, true);

        add_image_size('entity-thumb-size-rectangle', 660, 440, true);
        add_image_size('entity-thumb-size-square', 660, 660, true);
        add_image_size('entity-thumb-size-vertical-rectangle', 660, 1150, true);

        // Add post formats
        add_theme_support( 'post-formats', array( 'quote', 'video', 'link', 'gallery', 'audio' ) );

		// Register navigation menus
		register_nav_menu('primary', esc_html__('Primary Menu', 'milenia'));
		register_nav_menu('header', esc_html__('Header Sub Menu', 'milenia'));
        register_nav_menu('footer', esc_html__('Footer Menu', 'milenia'));
        register_nav_menu('hidden-sidebar-nav', esc_html__('Navigation sidebar (bottom)', 'milenia'));

		add_theme_support( 'custom-header', apply_filters( 'milenia_custom_header_args', array(
			'width' => 1350,
		) ) );

		// This theme uses its own gallery styles.
		add_filter( 'use_default_gallery_style', '__return_false' );

        // Initialization theme core objects
        $MileniaConfigurator = new MileniaConfigurator();
		$Milenia = new Milenia( new MileniaAdmin( $MileniaConfigurator ), new MileniaHelper() );
        $MileniaLayout = new MileniaLayout( $MileniaConfigurator );

		require_once('admin/MileniaBase.php');
		require_once('admin/license.php');
	}
}
add_action( 'after_setup_theme', 'milenia_setup', 100 );


/*  Including editor's stylesheet
/* ---------------------------------------------------------------------- */
if (!function_exists( 'milenia_theme_add_editor_styles'))
{
    function milenia_theme_add_editor_styles()
    {
        add_editor_style('custom-editor-style.css');
    }
}
add_action('init', 'milenia_theme_add_editor_styles');


/*  Demo content
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_import_files'))
{
    function milenia_import_files() {
        return array(
            array(
                'import_file_name' => 'Demo Import 1',
                'local_import_file' => trailingslashit( get_template_directory() ) . 'demo/content.xml',
                'local_import_widget_file' => trailingslashit( get_template_directory() ) . 'demo/widgets.wie',
                'local_import_redux' => array(
                    array(
                        'file_path'   => trailingslashit( get_template_directory() ) . 'demo/redux.json',
                        'option_name' => 'milenia_settings',
                    ),
                ),
                'import_preview_image_url' => trailingslashit( get_template_directory_uri() ) . 'demo/screen-image.png',
                'import_notice' => esc_html__( 'After you import this demo, you will have to setup the slider separately.', 'milenia' )
            )
        );
    }
}
add_filter('pt-ocdi/import_files', 'milenia_import_files');

if (!function_exists('milenia_after_import'))
{
    function milenia_after_import($selected_import)
    {
        $primary_menu = get_term_by('name', 'Primary Navigation', 'nav_menu');
        $account_menu = get_term_by('name', 'Account Navigation', 'nav_menu');
        $footer_menu = get_term_by('name', 'Footer Navigation', 'nav_menu');
        $hidden_sidebar_menu = get_term_by('name', 'Hidden Sidebar Navigation', 'nav_menu');

        set_theme_mod('nav_menu_locations', array(
            'primary' => $primary_menu->term_id,
            'header' => $account_menu->term_id,
            'footer' => $footer_menu->term_id,
            'hidden-sidebar-nav' => $hidden_sidebar_menu->term_id
        ));

        $page = get_page_by_title( 'Home - Luxury Hotel (Main)');

        if(isset($page->ID))
        {
            update_option( 'page_on_front', $page->ID );
            update_option( 'show_on_front', 'page' );
        }

        if(class_exists('RevSlider'))
        {
            $slider_array = array(
                get_template_directory() . '/demo/home_1_slider.zip',
                get_template_directory() . '/demo/home-2-slider.zip',
                get_template_directory() . '/demo/home-3-slider.zip',
                get_template_directory() . '/demo/home-4-slider.zip',
                get_template_directory() . '/demo/home-5-slider.zip',
                get_template_directory() . '/demo/home-1-slider-with-header.zip'
            );

            $slider = new RevSlider();

            foreach($slider_array as $filepath)
            {
                $slider->importSliderFromPost(true,true,$filepath);
            }
        }
    }
}
add_action( 'pt-ocdi/after_import', 'milenia_after_import' );

// Disables the block editor from managing widgets in the Gutenberg plugin.
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );

// Disables the block editor from managing widgets.
add_filter( 'use_widgets_block_editor', '__return_false' );

class Milenia_Includes {

	/**
	 * The one, true instance of the Suround object.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $instance = null;

	/**
	 * Access the single instance of this class.
	 *
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Milenia_Includes();
		}

		return self::$instance;
	}

	public function get( $integration ) {
		return $this->integrations->get( $integration );
	}

	/**
	 * The class constructor
	 */
	private function __construct() {
		$this->includes();
		$this->setup();
	}

	private function includes() {
		$this->files = array(
			'includes/integrations/class-integrations.php',
			'includes/integrations/class-integration.php',
		);

		foreach ( $this->files as $file ) {
			require_once( get_theme_file_path( $file ));
		}
	}

	private function setup() {

		$this->integrations = new Milenia_Integrations();
	}

}

function milenia_run() {
	return Milenia_Includes::get_instance();
}
milenia_run();

?>
