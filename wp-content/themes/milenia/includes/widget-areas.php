<?php
/**
* This file is responsible to register widget areas in the theme.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if( !function_exists('milenia_register_widget_areas') ) {
    function milenia_register_widget_areas() {
        global $Milenia;

        /*
         * The blogroll page sidebar.
         */
        register_sidebar(array(
			'id' => 'widget-area-1',
			'name' => esc_html__('Blog Widget Area', 'milenia'),
			'description' => esc_html__('Here you can define widgets for the blog posts page.', 'milenia'),
            'before_widget' => '<div id="%1$s" class="milenia-widget milenia-grid-item %2$s"><div class="milenia-grid-item-inner"><div class="milenia-grid-item-content">',
            'after_widget' => '</div></div></div>',
            'before_title' => '<h2 class="milenia-widget-title">',
            'after_title' => '</h2>'
        ));

        /*
         * The blog post page sidebar.
         */
        register_sidebar(array(
			'id' => 'widget-area-2',
			'name' => esc_html__('Single Post Widget Area', 'milenia'),
			'description' => esc_html__('Here you can define widgets for the widget area on a single blog post page.', 'milenia'),
            'before_widget' => '<div id="%1$s" class="milenia-widget milenia-grid-item %2$s"><div class="milenia-grid-item-inner"><div class="milenia-grid-item-content">',
            'after_widget' => '</div></div></div>',
            'before_title' => '<h2 class="milenia-widget-title">',
            'after_title' => '</h2>'
        ));

        /*
         * Single Page Widget Area.
         */
        register_sidebar(array(
            'id' => 'widget-area-5',
            'name' => esc_html__('Single Page Widget Area', 'milenia'),
            'description' => esc_html__('Here you can define widgets for a single page widget area.', 'milenia'),
            'before_widget' => '<div id="%1$s" class="milenia-widget milenia-grid-item %2$s"><div class="milenia-grid-item-inner"><div class="milenia-grid-item-content">',
            'after_widget' => '</div></div></div>',
            'before_title' => '<h2 class="milenia-widget-title">',
            'after_title' => '</h2>'
        ));

        /*
         * Footer widget area #1.
         */
        register_sidebar(array(
            'id' => 'widget-area-3',
            'name' => esc_html__('Footer Widget Area #1', 'milenia'),
            'description' => esc_html__('Here you can define widgets for the footer widget area #1.', 'milenia'),
            'before_widget' => '<div id="%1$s" class="milenia-widget milenia-grid-item %2$s"><div class="milenia-grid-item-inner"><div class="milenia-grid-item-content">',
            'after_widget' => '</div></div></div>',
            'before_title' => '<h2 class="milenia-widget-title">',
            'after_title' => '</h2>'
        ));

        /*
         * Footer widget area #2.
         */
        register_sidebar(array(
            'id' => 'widget-area-4',
            'name' => esc_html__('Footer Widget Area #2', 'milenia'),
            'description' => esc_html__('Here you can define widgets for the footer widget area #2.', 'milenia'),
            'before_widget' => '<div id="%1$s" class="milenia-widget milenia-grid-item %2$s"><div class="milenia-grid-item-inner"><div class="milenia-grid-item-content">',
            'after_widget' => '</div></div></div>',
            'before_title' => '<h2 class="milenia-widget-title">',
            'after_title' => '</h2>'
        ));

        /* 
         * Register additional widget areas for giving a user ability to add additional footer/blog widget areas 
         * and switch between them using theme options.
         */
        if($Milenia->themeOptionsEnabled())
        {   
            $num = 1;
            $from = 6;
            $max = 25;

            for($i = $from; $i < $max; $i++, $num++)
            {
                register_sidebar(array(
                    'id' => 'widget-area-' . $i,
                    'name' => esc_html__('Additional Widget Area #' .$num, 'milenia'),
                    'description' => esc_html__('Here you can define widgets for the additional widget area. You can select that widget area in designated places of the theme options. For instance: you can add an additional widget area to the site footer.', 'milenia'),
                    'before_widget' => '<div id="%1$s" class="milenia-widget milenia-grid-item %2$s"><div class="milenia-grid-item-inner"><div class="milenia-grid-item-content">',
                    'after_widget' => '</div></div></div>',
                    'before_title' => '<h2 class="milenia-widget-title">',
                    'after_title' => '</h2>'
                ));
            }
        }
    }
}
add_action('widgets_init', 'milenia_register_widget_areas');
?>
