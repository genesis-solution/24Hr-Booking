<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

/* Initialization of the portfolio project gallery builder
/* ---------------------------------------------------------------------- */
$MileniaPortfolioProjectGallery = new MileniaGalleryBuilder('milenia-portfolio', array(
    'builder_title' => esc_html__('Project Images', 'milenia-app-textdomain'),
    'paths' => array(
        'root' => MILENIA_FUNCTIONALITY_ROOT,
        'url'  => MILENIA_FUNCTIONALITY_URL
    ),
    'supports' => array('image')
));

/* Initialization of the gallery builder for the "Galleries" post type
/* ---------------------------------------------------------------------- */
$MileniaGalleryBuilder = new MileniaGalleryBuilder('milenia-galleries', array(
    'builder_title' => esc_html__('Gallery Builder', 'milenia-app-textdomain'),
    'paths' => array(
        'root' => MILENIA_FUNCTIONALITY_ROOT,
        'url'  => MILENIA_FUNCTIONALITY_URL
    ),
    'fields' => array(
        array(
            'title' => esc_html__('[Single page] Layout', 'milenia-app-textdomain'),
            'name' => 'single-page-layout',
            'type' => 'select',
            'options' => array(
                'grid' => esc_html__('Grid', 'milenia-app-textdomain'),
                'masonry' => esc_html__('Masonry', 'milenia-app-textdomain')
            )
        ),
		array(
            'title' => esc_html__('[Single page] Columns', 'milenia-app-textdomain'),
            'name' => 'single-page-columns',
            'type' => 'select',
            'options' => array(
                'milenia-grid--cols-4' => esc_html__('4 Columns', 'milenia-app-textdomain'),
                'milenia-grid--cols-3' => esc_html__('3 Columns', 'milenia-app-textdomain'),
                'milenia-grid--cols-2' => esc_html__('2 Columns', 'milenia-app-textdomain'),
                'milenia-grid--cols-1' => esc_html__('1 Columns', 'milenia-app-textdomain'),
            )
		),
		array(
            'title' => esc_html__('[Single page] Items per page', 'milenia-app-textdomain'),
            'name' => 'single-page-items-per-page',
            'type' => 'text',
			'value' => 12
		)
    ),
    'item_fields' => array(
        array(
            'type' => 'text',
            'name' => 'image-title',
            'title' => esc_html__('Title', 'milenia-app-textdomain'),
            'description' => esc_html__('Enter the image title.', 'milenia-app-textdomain'),
			'full-width-column' => true
        ),
		array(
            'type' => 'text',
            'name' => 'image-external-link',
            'title' => esc_html__('External link', 'milenia-app-textdomain'),
			'full-width-column' => true
        ),
		array(
            'type' => 'checkbox',
            'name' => 'image-external-link-target',
            'title' => esc_html__('Open link in a new tab', 'milenia-app-textdomain'),
			'full-width-column' => true,
			'value' => 'target'
        ),
		array(
            'type' => 'checkbox',
            'name' => 'image-external-link-nofollow',
            'title' => esc_html__('Add nofollow option', 'milenia-app-textdomain'),
			'full-width-column' => true,
			'value' => 'nofollow'
        )
    ),
    'supports' => array('image')
));
?>
