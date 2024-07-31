<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

add_action('init', 'milenia_theme_functionality_register_metaboxes', 10);

if(!function_exists('milenia_theme_functionality_register_metaboxes')) {
	function milenia_theme_functionality_register_metaboxes() {
		global $MileniaFunctionality;
		global $wp_registered_sidebars;

		// Registration of a single accommodation options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Single Page Options', 'milenia-app-textdomain'),
			    'post_types' => array('mphb_room_type'),
			    'fields' => array(
			        array(
			            'id'    => 'accomodation-single-layout-type',
			            'name'  => esc_html__('[Single Page] Layout', 'milenia-app-textdomain'),
			            'type'  => 'image_select',
						'class' => 'milenia-image-select',
			            'options' => array(
							'milenia-left-sidebar' => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-left.jpg',
							'milenia-full-width'    => MILENIA_FUNCTIONALITY_URL . 'assets/images/page-layout-fullwidth.png',
							'milenia-right-sidebar' => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-right.jpg'
			            ),
						'std' => 'milenia-right-sidebar'
			        ),
					array(
			            'id'    => 'accomodation-single-sidebar',
			            'name'  => esc_html__('[Single Page] Sidebar', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('accomodation-single-layout-type', 'in', array('milenia-right-sidebar', 'milenia-left-sidebar')),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-2'
			        ),
					array(
						'id'    => 'accomodation-floor-plan',
			            'name'  => esc_html__('[Single Page] Floor plan', 'milenia-app-textdomain'),
			            'type'  => 'image_upload',
						'max_file_uploads' => 20,
						'max_status' => true
					),
					array(
			            'id'    => 'accomodation-skin',
			            'name'  => esc_html__('Color scheme', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'brown' => esc_html__('Brown', 'milenia-app-textdomain'),
							'gray' => esc_html__('Gray', 'milenia-app-textdomain'),
							'blue' => esc_html__('Blue', 'milenia-app-textdomain'),
							'lightbrown' => esc_html__('Lightbrown', 'milenia-app-textdomain'),
							'green' => esc_html__('Green', 'milenia-app-textdomain')
						),
						'std'   => 'milenia-body--scheme-brown',
						'visible' => array('milenia-page-theme-skin-custom-state', '=', false)
			        ),
					array(
			            'id'    => 'milenia-page-theme-skin-custom-state',
			            'name'  => esc_html__('Custom color scheme', 'milenia-app-textdomain'),
						'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std'   => false
			        ),
					array(
						'id' => 'milenia-page-theme-skin-custom-primary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Primary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
						'id' => 'milenia-page-theme-skin-custom-secondary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Secondary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
			            'id'    => 'accommodation-rate-note',
			            'name'  => esc_html__('Rates note', 'milenia-app-textdomain'),
			            'type'  => 'text',
						'desc'  => esc_html__('Will be placed under the rates tables.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'accommodation-banners-state',
			            'name'  => esc_html__('Banners', 'milenia-app-textdomain'),
						'visible' => array('accomodation-single-layout-type', 'in', array('milenia-full-width')),
						'desc'  => esc_html__('Show banners', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 1
			        ),
					array(
			            'id'    => 'accommodation-other-rooms-state',
			            'name'  => esc_html__('Other rooms', 'milenia-app-textdomain'),
						'visible' => array('accomodation-single-layout-type', 'in', array('milenia-full-width')),
						'desc'  => esc_html__('Show other rooms', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 1
			        ),
					array(
						'id' => 'accomodation-divider-1',
						'type' => 'divider',
						'visible' => array('accommodation-banners-state', '=', true),
					),
					array(
			            'id'    => 'accomodation-banner-1-image',
			            'name'  => esc_html__('[Banner 1] Image', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'image_advanced',
						'max_file_uploads' => 1
			        ),
					array(
			            'id'    => 'accomodation-banner-1-title',
			            'name'  => esc_html__('[Banner 1] Title', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-1-content',
			            'name'  => esc_html__('[Banner 1] Content', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'textarea'
			        ),
					array(
			            'id'    => 'accomodation-banner-1-link-text',
			            'name'  => esc_html__('[Banner 1] Link text', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-1-link-url',
			            'name'  => esc_html__('[Banner 1] Link url', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-1-link-nofollow',
			            'name'  => esc_html__('[Banner 1] Nofollow', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'desc'  => esc_html__('Nofollow', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 0
			        ),
					array(
			            'id'    => 'accomodation-banner-1-link-target-blank',
			            'name'  => esc_html__('[Banner 1] Target', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'desc'  => esc_html__('Open link in a new window', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 0
			        ),
					array(
						'id' => 'accomodation-divider-2',
						'type' => 'divider',
						'visible' => array('accommodation-banners-state', '=', true),
					),
					array(
			            'id'    => 'accomodation-banner-2-image',
			            'name'  => esc_html__('[Banner 2] Image', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'image_advanced',
						'max_file_uploads' => 1
			        ),
					array(
			            'id'    => 'accomodation-banner-2-title',
			            'name'  => esc_html__('[Banner 2] Title', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-2-content',
			            'name'  => esc_html__('[Banner 2] Content', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'textarea'
			        ),
					array(
			            'id'    => 'accomodation-banner-2-link-text',
			            'name'  => esc_html__('[Banner 2] Link text', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-2-link-url',
			            'name'  => esc_html__('[Banner 2] Link url', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-2-link-nofollow',
			            'name'  => esc_html__('[Banner 2] Nofollow', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'desc'  => esc_html__('Nofollow', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 0
			        ),
					array(
			            'id'    => 'accomodation-banner-2-link-target-blank',
			            'name'  => esc_html__('[Banner 2] Target', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'desc'  => esc_html__('Open link in a new window', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 0
			        ),
					array(
						'id' => 'accomodation-divider-3',
						'type' => 'divider',
						'visible' => array('accommodation-banners-state', '=', true),
					),
					array(
			            'id'    => 'accomodation-banner-3-image',
			            'name'  => esc_html__('[Banner 3] Image', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'image_advanced',
						'max_file_uploads' => 1
			        ),
					array(
			            'id'    => 'accomodation-banner-3-title',
			            'name'  => esc_html__('[Banner 3] Title', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-3-content',
			            'name'  => esc_html__('[Banner 3] Content', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'textarea'
			        ),
					array(
			            'id'    => 'accomodation-banner-3-link-text',
			            'name'  => esc_html__('[Banner 3] Link text', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-3-link-url',
			            'name'  => esc_html__('[Banner 3] Link url', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'accomodation-banner-3-link-nofollow',
			            'name'  => esc_html__('[Banner 3] Nofollow', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'desc'  => esc_html__('Nofollow', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 0
			        ),
					array(
			            'id'    => 'accomodation-banner-3-link-target-blank',
			            'name'  => esc_html__('[Banner 3] Target', 'milenia-app-textdomain'),
						'visible' => array('accommodation-banners-state', '=', true),
						'desc'  => esc_html__('Open link in a new window', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 0
			        ),
			    )
			)
		));

		// Registration of the single post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Post Options', 'milenia-app-textdomain'),
			    'post_types' => array('post'),
			    'fields' => array(
					array(
			            'id'    => 'post-single-layout-state-individual',
			            'name'  => esc_html__('Post Settings', 'milenia-app-textdomain'),
			            'desc'  => esc_html__('Inherit value from the theme options.', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 1
			        ),
			        array(
			            'id'    => 'post-single-layout-individual',
			            'name'  => esc_html__('[Single Page] Select Layout', 'milenia-app-textdomain'),
			            'type'  => 'image_select',
						'visible' => array('post-single-layout-state-individual', '=', 0),
						'class' => 'milenia-image-select',
			            'options' => array(
			                'milenia-left-sidebar'  => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-left.jpg',
			                'milenia-has-not-sidebar'    => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-full.jpg',
			                'milenia-right-sidebar' => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-right.jpg'
			            ),
						'std' => 'milenia-has-not-sidebar'
			        ),
					array(
			            'id'    => 'post-single-sidebar-individual',
			            'name'  => esc_html__('[Single Page] Select Sidebar', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('post-single-layout-individual', 'in', array('milenia-left-sidebar', 'milenia-right-sidebar')),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars))
			        ),
					array(
			            'id'    => 'milenia-post-skin',
			            'name'  => esc_html__('Color scheme', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'brown' => esc_html__('Brown', 'milenia-app-textdomain'),
							'gray' => esc_html__('Gray', 'milenia-app-textdomain'),
							'blue' => esc_html__('Blue', 'milenia-app-textdomain'),
							'lightbrown' => esc_html__('Lightbrown', 'milenia-app-textdomain'),
							'green' => esc_html__('Green', 'milenia-app-textdomain')
						),
						'std'   => 'milenia-body--scheme-brown',
						'visible' => array(
							array('milenia-page-theme-skin-custom-state', '=', false),
							array('post-single-layout-state-individual', '=', 0)
						)
			        ),
					array(
			            'id'    => 'milenia-page-theme-skin-custom-state',
			            'name'  => esc_html__('Custom color scheme', 'milenia-app-textdomain'),
						'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std'   => false,
						'visible' => array('post-single-layout-state-individual', '=', 0)
			        ),
					array(
						'id' => 'milenia-page-theme-skin-custom-primary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Primary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
						'id' => 'milenia-page-theme-skin-custom-secondary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Secondary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
			            'id'    => 'milenia-post-share-buttons-state',
			            'name'  => esc_html__('[Single Page] Share buttons', 'milenia-app-textdomain'),
						'type'  => 'select',
						'visible' => array('post-single-layout-state-individual', '=', 0),
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        ),
					array(
			            'id'    => 'milenia-post-tags-state',
			            'name'  => esc_html__('[Single Page] Tags', 'milenia-app-textdomain'),
						'type'  => 'select',
						'visible' => array('post-single-layout-state-individual', '=', 0),
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        ),
					array(
			            'id'    => 'milenia-post-related-posts-state',
			            'name'  => esc_html__('[Single Page] Related Posts', 'milenia-app-textdomain'),
						'type'  => 'select',
						'visible' => array('post-single-layout-state-individual', '=', 0),
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        )
			    )
			)
		));

		// Registration of the single post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Event Options', 'milenia-app-textdomain'),
			    'post_types' => array('tribe_events'),
			    'fields' => array(
					array(
						'id'    => 'milenia-post-share-buttons-state',
						'name'  => esc_html__('[Single Page] Share buttons', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
					)
			    )
			)
		));

		// An offer settings
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Offer settings', 'milenia-app-textdomain'),
				'id' => 'milenia-offer-settings',
			    'post_types' => array('milenia-offers'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-offer-price',
						'name'  => esc_html__('Price', 'milenia-app-textdomain'),
						'type'  => 'number',
						'min'   => 0,
						'std' => 0,
						'step' => 0.01
			        ),
					array(
			            'id'    => 'milenia-offer-currency',
						'name'  => esc_html__('Currency', 'milenia-app-textdomain'),
						'type'  => 'text',
						'std'   => '$'
			        ),
					array(
						'name'  => esc_html__('Start date', 'milenia-app-textdomain'),
						'id' => 'milenia-offer-start-date',
						'type' => 'datetime',
						'js_options' => array(
							'stepMinute'      => 5,
							'showTimepicker'  => true,
							'controlType'     => 'select',
							'showButtonPanel' => false,
							'oneLine'         => true
						),
						'inline'     => false,
						'timestamp'  => false
					),
					array(
						'name'  => esc_html__('End date', 'milenia-app-textdomain'),
						'id' => 'milenia-offer-end-date',
						'type' => 'datetime',
						'js_options' => array(
							'stepMinute'      => 5,
							'showTimepicker'  => true,
							'controlType'     => 'select',
							'showButtonPanel' => false,
							'oneLine'         => true
						),
						'inline'     => false,
						'timestamp'  => false
					),
					array(
			            'id'    => 'milenia-post-share-buttons-state',
			            'name'  => esc_html__('Share buttons', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        ),
					array(
			            'id'    => 'milenia-offer-tags-state',
			            'name'  => esc_html__('Tags', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        ),
					array(
			            'id'    => 'milenia-offer-related-posts-state',
			            'name'  => esc_html__('Related Offers', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        )
			    )
			)
		));

		// Registration of the video post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Video Post Options', 'milenia-app-textdomain'),
				'id' => 'milenia-post-options-video',
			    'post_types' => array('post'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-video-selfhosted-state',
						'name'  => esc_html__('Video source', 'milenia-app-textdomain'),
						'desc'  => esc_html__('Self hosted video', 'milenia-app-textdomain'),
						'type'  => 'checkbox',
						'std' => 0
			        ),
					array(
			            'id'    => 'milenia-video-src-selfhosted',
						'name'  => esc_html__('Self hosted video', 'milenia-app-textdomain'),
						'visible' => array('milenia-video-selfhosted-state', '=', 1),
						'type'  => 'video',
						'max_file_uploads' => 1
			        ),
					array(
			            'id'    => 'milenia-video-src-outer',
						'name'  => esc_html__('Outer resource video url', 'milenia-app-textdomain'),
						'visible' => array('milenia-video-selfhosted-state', '=', 0),
						'type'  => 'text',
						'max_file_uploads' => 1
			        )
			    )
			)
		));

		// Registration of the audio post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Audio Post Options', 'milenia-app-textdomain'),
				'id' => 'milenia-post-options-audio',
			    'post_types' => array('post'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-audio-soundcloud',
						'name'  => esc_html__('SoundCloud code', 'milenia-app-textdomain'),
						'type'  => 'text'
			        )
			    )
			)
		));

		// Registration of the quote post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Quote Post Options', 'milenia-app-textdomain'),
				'id' => 'milenia-post-options-quote',
			    'post_types' => array('post'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-post-quote',
						'name'  => esc_html__('Quote', 'milenia-app-textdomain'),
						'type'  => 'textarea',
						'rows' => 4
			        ),
					array(
			            'id'    => 'milenia-post-quote-author',
						'name'  => esc_html__('Source', 'milenia-app-textdomain'),
						'desc'  => esc_html__('Author info', 'milenia-app-textdomain'),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-post-quote-author-link',
						'name'  => esc_html__('Source link', 'milenia-app-textdomain'),
						'visible' => array('milenia-post-quote-author', '!=', ''),
						'type'  => 'url'
			        ),
					array(
			            'id'    => 'milenia-post-quote-author-link-target',
						'name'  => esc_html__('Target', 'milenia-app-textdomain'),
						'desc'  => esc_html__('Open link in a new window', 'milenia-app-textdomain'),
						'visible' => array('milenia-post-quote-author-link', '!=', ''),
						'type'  => 'checkbox',
						'std' 	=> 1
			        ),
					array(
			            'id'    => 'milenia-post-quote-author-link-nofollow',
						'name'  => esc_html__('Nofollow', 'milenia-app-textdomain'),
						'desc'  => esc_html__('Add "nofollow" option to the link', 'milenia-app-textdomain'),
						'visible' => array('milenia-post-quote-author-link', '!=', ''),
						'type'  => 'checkbox',
						'std' 	=> 0
			        )
			    )
			)
		));

		// Registration of the link post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Link Post Options', 'milenia-app-textdomain'),
				'id' => 'milenia-post-options-link',
			    'post_types' => array('post'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-post-link-text',
						'name'  => esc_html__('Link text', 'milenia-app-textdomain'),
						'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-post-link-url',
						'name'  => esc_html__('Link URL', 'milenia-app-textdomain'),
						'type'  => 'url'
			        ),
					array(
			            'id'    => 'milenia-post-link-target',
						'name'  => esc_html__('Target', 'milenia-app-textdomain'),
						'desc'  => esc_html__('Open link in a new window', 'milenia-app-textdomain'),
						'type'  => 'checkbox',
						'std' 	=> 1
			        ),
					array(
			            'id'    => 'milenia-post-link-nofollow',
						'name'  => esc_html__('Nofollow', 'milenia-app-textdomain'),
						'desc'  => esc_html__('Add "nofollow" option to the link', 'milenia-app-textdomain'),
						'type'  => 'checkbox',
						'std' 	=> 0
			        )
			    )
			)
		));

		// Registration of the gallery post options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Gallery Post Options', 'milenia-app-textdomain'),
				'id' => 'milenia-post-options-gallery',
			    'post_types' => array('post'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-post-gallery',
						'name'  => esc_html__('Slideshow images', 'milenia-app-textdomain'),
						'type'  => 'image_advanced'
			        )
			    )
			)
		));

		// [Page Options] Header
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Header Settings', 'milenia-app-textdomain'),
			    'post_types' => array('page', 'post', 'milenia-portfolio', 'tribe_events', 'mphb_room_type'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-page-header-state',
						'name'  => esc_html__('Header settings', 'milenia-app-textdomain'),
			            'desc'  => esc_html__('Inherit settings from the theme options.', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'class' => ' ',
						'std' => 1
			        ),
					array(
						'id' => 'milenia-page-header-type',
						'type' => 'image_select',
						'class' => 'milenia-image-select-column',
						'options' => array(
							'milenia-header-layout-v1' => MILENIA_FUNCTIONALITY_URL . 'assets/images/header-layout-v2.png',
							'milenia-header-layout-v2' => MILENIA_FUNCTIONALITY_URL . 'assets/images/header-layout-v5.png',
							'milenia-header-layout-v3' => MILENIA_FUNCTIONALITY_URL . 'assets/images/header-layout-v4.png',
							'milenia-header-layout-v4' => MILENIA_FUNCTIONALITY_URL . 'assets/images/header-layout-v1.png',
							'milenia-header-layout-v5' => MILENIA_FUNCTIONALITY_URL . 'assets/images/header-layout-v3.png'
						),
			            'name'  => esc_html__('Layout', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => 'milenia-header-layout-v1'
					),
					array(
						'id' => 'milenia-page-header-logo',
						'type' => 'single_image',
						'name' => esc_html__('Logo', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => ''
					),
				    array(
						'id' => 'milenia-page-header-logo-hidpi',
						'type' => 'single_image',
						'name' => esc_html__('Logo HiDPI', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => ''
					),
					array(
						'id' => 'milenia-page-header-transparent',
						'type' => 'switch',
						'name' => esc_html__('Fixed transparent header', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-type', 'in', array('milenia-header-layout-v2', 'milenia-header-layout-v4')),
						'desc' => esc_html__('Please note that in case this option is enabled the header will be displayed as a fixed element (above the content).', 'milenia-app-textdomain'),
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std' => false
					),
					array(
						'id' => 'milenia-page-header-color-scheme',
						'type' => 'button_group',
						'multiple' => false,
						'name' => esc_html__('Color scheme', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-type', 'in', array('milenia-header-layout-v1', 'milenia-header-layout-v3', 'milenia-header-layout-v5')),
						'options' => array(
							'milenia-header--light' => esc_html__('Light', 'milenia-app-textdomain'),
							'milenia-header--dark' => esc_html__('Dark', 'milenia-app-textdomain')
						),
						'std' => 'milenia-header--light'
					),
					array(
						'id' => 'milenia-page-header-container',
						'type' => 'button_group',
						'multiple' => false,
						'visible' => array('milenia-page-header-type', 'in', array('milenia-header-layout-v2', 'milenia-header-layout-v4', 'milenia-header-layout-v5')),
						'name' => esc_html__('Content width', 'milenia-app-textdomain'),
						'options' => array(
							'container' => esc_html__('Container', 'milenia-app-textdomain'),
							'container-fluid' => esc_html__('Full width', 'milenia-app-textdomain')
						),
						'std' => 'container-fluid'
					),
					array(
						'id' => 'milenia-page-header-transparentable-color-scheme',
						'type' => 'button_group',
						'multiple' => false,
						'name' => esc_html__('Color scheme', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-transparent', '!=', true),
						'options' => array(
							'milenia-header--light' => esc_html__('Light', 'milenia-app-textdomain'),
							'milenia-header--dark' => esc_html__('Dark', 'milenia-app-textdomain')
						),
						'std' => 'milenia-header--light'
					),
					array(
						'id' => 'milenia-page-header-navigation-section',
						'type' => 'switch',
						'name' => esc_html__('Navigation section', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-type', '=', 'milenia-header-layout-v5'),
						'on_label' => esc_html__('Show', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Hide', 'milenia-app-textdomain'),
						'std' => true
					),
					array(
						'id' => 'milenia-page-header-sticky',
						'type' => 'switch',
						'name' => esc_html__('Sticky', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-type', '!=', 'milenia-header-layout-v5'),
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std' => true
					),
				    array(
					    'id'    => 'milenia-page-header-sticky-responsive-breakpoint',
					    'name'  => esc_html__('Sticky Responsive breakpoint', 'milenia-app-textdomain'),
					    'type'  => 'button_group',
					    'multiple' => true,
					    'inline' => true,
					    'options' => array(
						    'milenia-header-section--sticky-sm' => esc_html__('sm', 'milenia'),
						    'milenia-header-section--sticky-md' => esc_html__('md', 'milenia'),
						    'milenia-header-section--sticky-lg' => esc_html__('lg', 'milenia'),
						    'milenia-header-section--sticky-xl' => esc_html__('xl', 'milenia'),
						    'milenia-header-section--sticky-xxl' => esc_html__('xxl', 'milenia'),
						    'milenia-header-section--sticky-xxxl' => esc_html__('xxxl', 'milenia'),
					    ),
					    'visible' => array('milenia-page-header-sticky', '=', true),
					    'std' => 'milenia-header-section--sticky-xl'
				    ),
					array(
						'id' => 'milenia-page-header-layout-v5-sticky',
						'type' => 'switch',
						'name' => esc_html__('Sticky', 'milenia-app-textdomain'),
						'visible' => array(
							array('milenia-page-header-type', '=', 'milenia-header-layout-v5'),
							array('milenia-page-header-navigation-section', '=', true)
						),
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std' => true
					),
				    array(
					    'id'    => 'milenia-page-header-layout-v5-sticky-responsive-breakpoint',
					    'name'  => esc_html__('Sticky Responsive breakpoint', 'milenia-app-textdomain'),
					    'type'  => 'button_group',
					    'multiple' => true,
					    'inline' => true,
					    'options' => array(
						    'milenia-header-section--sticky-sm' => esc_html__('sm', 'milenia'),
						    'milenia-header-section--sticky-md' => esc_html__('md', 'milenia'),
						    'milenia-header-section--sticky-lg' => esc_html__('lg', 'milenia'),
						    'milenia-header-section--sticky-xl' => esc_html__('xl', 'milenia'),
						    'milenia-header-section--sticky-xxl' => esc_html__('xxl', 'milenia'),
						    'milenia-header-section--sticky-xxxl' => esc_html__('xxxl', 'milenia'),
					    ),
					    'visible' => array('milenia-page-header-layout-v5-sticky', '=', true),
					    'std' => 'milenia-header-section--sticky-md'
				    ),
					array(
						'id' => 'milenia-page-header-top-bar',
						'type' => 'switch',
						'visible' => array('milenia-page-header-type', 'in', array('milenia-header-layout-v1', 'milenia-header-layout-v2')),
						'name' => esc_html__('Top bar', 'milenia-app-textdomain'),
						'on_label' => esc_html__('Show', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Hide', 'milenia-app-textdomain'),
						'std' => true
					),
					array(
						'id' => 'milenia-page-header-top-bar-left-column-elements',
						'type' => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-header-top-bar', '=', true),
						'name' => esc_html__("[Top bar] Left column elements", 'milenia-app-textdomain'),
						'options' => array(
							'info' => esc_html__('General info (phone, email)', 'milenia-app-textdomain'),
							'subnav' => esc_html__('Sub navigation', 'milenia-app-textdomain')
						),
						'std' => array()
					),
					array(
						'id' => 'milenia-page-header-top-bar-right-column-elements',
						'type' => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-header-top-bar', '=', true),
						'name' => esc_html__("[Top bar] Right column elements", 'milenia-app-textdomain'),
						'options' => array(
							'info' => esc_html__('General info (phone, email)', 'milenia-app-textdomain'),
							'subnav' => esc_html__('Sub navigation', 'milenia-app-textdomain')
						),
						'std' => array()
					),
					array(
						'id' => 'milenia-page-header-left-column-elements',
						'type' => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-header-type', 'in', array('milenia-header-layout-v5', 'milenia-header-layout-v4', 'milenia-header-layout-v3')),
						'name' => esc_html__("Left column elements", 'milenia-app-textdomain'),
						'options' => array(
							'search' => esc_html__('Search button', 'milenia-app-textdomain'),
							'languages' => esc_html__('Language', 'milenia-app-textdomain'),
							'action-btn' => esc_html__('Action button', 'milenia-app-textdomain'),
							'weather' => esc_html__('Weather indicator', 'milenia-app-textdomain'),
							'hidden-sidebar-btn' => esc_html__('Hidden sidebar button', 'milenia-app-textdomain'),
							'menu-btn' => esc_html__('Vertical menu button', 'milenia-app-textdomain')
						),
						'std' => array()
					),
					array(
						'id' => 'milenia-page-header-right-column-elements',
						'type' => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-header-type', 'in', array('milenia-header-layout-v1', 'milenia-header-layout-v2', 'milenia-header-layout-v5', 'milenia-header-layout-v4')),
						'name' => esc_html__("Right column elements", 'milenia-app-textdomain'),
						'options' => array(
							'search' => esc_html__('Search button', 'milenia-app-textdomain'),
							'languages' => esc_html__('Language', 'milenia-app-textdomain'),
							'action-btn' => esc_html__('Action button', 'milenia-app-textdomain'),
							'weather' => esc_html__('Weather indicator', 'milenia-app-textdomain'),
							'hidden-sidebar-btn' => esc_html__('Hidden sidebar button', 'milenia-app-textdomain'),
							'menu-btn' => esc_html__('Vertical menu button', 'milenia-app-textdomain')
						),
						'default' => array()
					),
					array(
						'id' => 'milenia-page-header-items-caption',
						'type' => 'divider',
						'visible' => array('milenia-page-header-state', '=', 0)
					),
					array(
						'id' => 'milenia-page-header-action-btn-text',
						'type' => 'text',
						'name' => esc_html__("[Action Button] Text", 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => esc_html__('Book Now', 'milenia-app-textdomain')
					),
					array(
						'id' => 'milenia-page-header-action-btn-url',
						'type' => 'text',
						'name' => esc_html__("[Action Button] URL", 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => '#'
					),
					array(
						'id' => 'milenia-page-header-action-btn-target',
						'type' => 'checkbox',
						'name' => esc_html__("[Action Button] Open in a new tab", 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => 0
					),
					array(
						'id' => 'milenia-page-header-action-btn-nofollow',
						'type' => 'checkbox',
						'name' => esc_html__("[Action Button] Nofollow option", 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'std' => 0
					),
					array(
						'id' => 'milenia-page-header-vertical-menu-logo',
						'type' => 'single_image',
						'name' => esc_html__("[Vertical Navigation] Logo", 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0)
					),
					array(
						'id' => 'milenia-page-header-hidden-sidebar-widget-area',
						'type' => 'select',
						'name' => esc_html__('[Hidden Sidebar] Widget Area', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-header-state', '=', 0),
						'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-1'
					)
				)
			)
		));

		// [Page Options] Breadcrumb Settings
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Breadcrumb Settings', 'milenia-app-textdomain'),
			    'post_types' => array('page', 'post', 'milenia-portfolio', 'tribe_events', 'mphb_room_type'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-page-breadcrumb-settings-state',
						'name'  => esc_html__('Breadcrumb settings', 'milenia-app-textdomain'),
			            'desc'  => esc_html__('Inherit settings from the theme options.', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' => 1
			        ),
					array(
			            'id'    => 'milenia-page-breadcrumb-state',
			            'name'  => esc_html__('Breadcrumb section', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'visible' => array('milenia-page-breadcrumb-settings-state', '=', 0),
						'on_label' => esc_html__('Show', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Hide', 'milenia-app-textdomain'),
						'std' => true
			        ),
					array(
			            'id'    => 'milenia-page-breadcrumb',
			            'name'  => '',
			            'type'  => 'breadcrumb',
						'visible' => array('milenia-page-breadcrumb-state', '=', true)
			        )
				)
			)
		));

		// [Page Options] General
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Page Settings', 'milenia-app-textdomain'),
			    'post_types' => array('page'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-page-settings-inherit-individual',
			            'name'  => esc_html__('Page settings', 'milenia-app-textdomain'),
			            'desc'  => esc_html__('Inherit settings from the theme options.', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 1
			        ),
					array(
			            'id'    => 'milenia-page-type-individual',
						'name'  => esc_html__('Page type', 'milenia-app-textdomain'),
						'type'  => 'image_select',
						'visible' => array('milenia-page-settings-inherit-individual', '=', 0),
						'class' => 'milenia-image-select',
						'options' => array(
							'milenia-default' => MILENIA_FUNCTIONALITY_URL . 'assets/images/page-layout-default.png',
							'milenia-blogroll' => MILENIA_FUNCTIONALITY_URL . 'assets/images/page-layout-blogroll.png',
							'milenia-portfolio' => MILENIA_FUNCTIONALITY_URL . 'assets/images/page-layout-portfolio.jpg',
							'milenia-gallery' => MILENIA_FUNCTIONALITY_URL . 'assets/images/page-layout-gallery.jpg'
						),
						'std' => 'milenia-default'
			        ),

					// blogroll page

					array(
			            'id'    => 'milenia-page-blogroll-item-style',
						'name'  => esc_html__('Display style', 'milenia-app-textdomain'),
						'type'  => 'image_select',
						'visible' => array('milenia-page-type-individual', '=', 'milenia-blogroll'),
						'class' => 'milenia-image-select-column',
						'options' => array(
							'milenia-entities--style-4' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v1.png',
							'milenia-entities--style-6 milenia-entities--without-media' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v2.png',
							'milenia-entities--style-6' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v3.png',
							'milenia-entities--style-7' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v4.png',
							'milenia-entities--style-8' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v5.png',
							'milenia-entities--style-4 milenia-entities--list' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v6.png',
							'milenia-entities--style-7 milenia-entities--list' => MILENIA_FUNCTIONALITY_URL . 'assets/images/post-archive-layout-v7.png'
						),
						'std' => 'milenia-entities--style-4'
			        ),
					array(
			            'id'    => 'milenia-page-blogroll-categories',
						'name'  => esc_html__('Categories', 'milenia-app-textdomain'),
						'type'  => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-type-individual', '=', 'milenia-blogroll'),
						'options' => milenia_get_terms_as_array('category')
			        ),
					array(
			            'id'    => 'milenia-page-blogroll-tags',
						'name'  => esc_html__('Tags', 'milenia-app-textdomain'),
						'type'  => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-type-individual', '=', 'milenia-blogroll'),
						'options' => milenia_get_terms_as_array('post_tag')
			        ),
					array(
			            'id'    => 'milenia-page-blogroll-in',
						'name'  => esc_html__('Include', 'milenia-app-textdomain'),
						'type'  => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-type-individual', '=', 'milenia-blogroll'),
						'options' => milenia_get_posts_as_array('post')
			        ),
					array(
			            'id'    => 'milenia-page-blogroll-out',
						'name'  => esc_html__('Exclude', 'milenia-app-textdomain'),
						'type'  => 'select_advanced',
						'multiple' => true,
						'visible' => array('milenia-page-type-individual', '=', 'milenia-blogroll'),
						'options' => milenia_get_posts_as_array('post')
			        ),
					// End blogroll
					array(
						'id'    => 'milenia-page-layout-individual',
						'name'  => esc_html__('Page layout', 'milenia-app-textdomain'),
						'type'  => 'image_select',
						'visible' => array('milenia-page-settings-inherit-individual', '!=', '1'),
						'class' => 'milenia-image-select',
						'options' => array(
							'milenia-left-sidebar'  => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-left.jpg',
							'milenia-has-not-sidebar'    => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-full.jpg',
							'milenia-right-sidebar' => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-right.jpg',
							'milenia-full-width' => MILENIA_FUNCTIONALITY_URL . 'assets/images/page-layout-fullwidth.png'
						),
						'std' => 'milenia-has-not-sidebar'
					),
					array(
						'id'    => 'milenia-page-sidebar-individual',
						'name'  => esc_html__('Select sidebar', 'milenia-app-textdomain'),
						'type'  => 'select_advanced',
						'visible' => array('milenia-page-layout-individual', 'in', array('milenia-left-sidebar', 'milenia-right-sidebar')),
						'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-1'
					),
					array(
			            'id'    => 'milenia-page-items-layout',
						'name'  => esc_html__('Content layout', 'milenia-app-textdomain'),
						'type'  => 'image_select',
						'visible' => array('milenia-page-type-individual', '!=', 'milenia-default'),
						'class' => 'milenia-image-select',
						'options' => array(
							'grid' => MILENIA_FUNCTIONALITY_URL . 'assets/images/portfolio-gallery-grid.png',
							'masonry' => MILENIA_FUNCTIONALITY_URL . 'assets/images/portfolio-gallery-masonry.png'
						),
						'std' => 'grid'
			        ),

					array(
						'type' => 'divider',
						'visible' => array('milenia-page-type-individual', '!=', 'milenia-default')
					),
					array(
			            'id'    => 'milenia-page-items-per-page',
			            'name'  => esc_html__('Items per page', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '!=', 'milenia-default'),
			            'type'  => 'number',
						'min' => 0,
						'max' => 32,
						'step' => 1,
						'std'   => 9
			        ),
					array(
			            'id'    => 'milenia-page-order-by',
			            'name'  => esc_html__('Order by', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', 'in', array('milenia-portfolio', 'milenia-blogroll')),
			            'type'  => 'select',
						'options' => array(
							'date' => esc_html__('Date', 'milenia-app-textdomain'),
							'title' => esc_html__('Title', 'milenia-app-textdomain'),
							'rand' => esc_html__('Random', 'milenia-app-textdomain')
						),
						'std'   => 'date'
			        ),
					array(
			            'id'    => 'milenia-page-sort-order',
			            'name'  => esc_html__('Sort order', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '!=', 'milenia-default'),
			            'type'  => 'select',
						'options' => array(
							'desc' => esc_html__('DESC', 'milenia-app-textdomain'),
							'asc' => esc_html__('ASC', 'milenia-app-textdomain')
						),
						'std'   => 'desc'
			        ),

					// portfolio
					array(
			            'id'    => 'milenia-page-portfolio-categories',
			            'name'  => esc_html__('From categories', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '=', 'milenia-portfolio'),
			            'type'  => 'select_advanced',
						'multiple' => true,
						'options' => milenia_get_terms_as_array('milenia-portfolio-categories')
			        ),
					array(
			            'id'    => 'milenia-page-portfolio-out',
			            'name'  => esc_html__('Exclude items', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '=', 'milenia-portfolio'),
			            'type'  => 'select_advanced',
						'multiple' => true,
						'options' => milenia_get_posts_as_array('milenia-portfolio')
			        ),
					array(
			            'id'    => 'milenia-page-portfolio-in',
			            'name'  => esc_html__('Include items', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '=', 'milenia-portfolio'),
			            'type'  => 'select_advanced',
						'multiple' => true,
						'options' => milenia_get_posts_as_array('milenia-portfolio')
			        ),
					array(
			            'id'    => 'milenia-page-portfolio-item-style',
			            'name'  => esc_html__('Item style', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', 'in', array('milenia-portfolio')),
			            'type'  => 'select',
						'options' => array(
							'milenia-entities--style-18' => esc_html__('Style 1', 'milenia-app-textdomain'),
							'milenia-entities--style-17' => esc_html__('Style 2', 'milenia-app-textdomain')
						),
						'std'   => 'milenia-entities--style-18'
			        ),
					array(
			            'id'    => 'milenia-page-portfolio-categories-state',
			            'name'  => esc_html__('Item categories', 'milenia-app-textdomain'),
						'desc' => esc_html__('Show categories', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', 'in', array('milenia-portfolio')),
			            'type'  => 'checkbox',
						'std'   => 1
			        ),
					array(
						'type' => 'divider',
						'visible' => array('milenia-page-type-individual', '!=',  'milenia-default'),
					),

					// gallery
					array(
			            'id'    => 'milenia-page-gallery-categories',
			            'name'  => esc_html__('From categories', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '=', 'milenia-gallery'),
			            'type'  => 'select_advanced',
						'multiple' => true,
						'options' => milenia_get_terms_as_array('milenia-gallery-categories')
			        ),
					array(
			            'id'    => 'milenia-page-gallery-out',
			            'name'  => esc_html__('Exclude items', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '=', 'milenia-gallery'),
			            'type'  => 'select_advanced',
						'multiple' => true,
						'options' => milenia_get_posts_as_array('milenia-galleries')
			        ),
					array(
			            'id'    => 'milenia-page-gallery-in',
			            'name'  => esc_html__('Include items', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', '=', 'milenia-gallery'),
			            'type'  => 'select_advanced',
						'multiple' => true,
						'options' => milenia_get_posts_as_array('milenia-galleries')
			        ),
					// end of gallery

					// common
					array(
			            'id'    => 'milenia-page-filter-state',
			            'name'  => esc_html__('Filter', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', 'in', array('milenia-portfolio', 'milenia-gallery')),
			            'type'  => 'checkbox',
						'desc' => esc_html__('Show filter', 'milenia-app-textdomain'),
						'std'   => 1
			        ),
					array(
			            'id'    => 'milenia-page-filter-all-tab-text',
						'name'  => esc_html__('Filter "All" tab', 'milenia-app-textdomain'),
						'type'  => 'text',
						'placeholder' => esc_html__('All', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-filter-state', '=', 1),
						'size' => 30,
			            'desc'  => esc_html__('Enter the name of the tab that is responsible for displaying all items.', 'milenia-app-textdomain'),
						'std' 	=> esc_html__('All', 'milenia-app-textdomain')
			        ),
					array(
			            'id'    => 'milenia-page-blogroll-columns-individual',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
						'visible' => array(
							array('milenia-page-type-individual', 'in', array('milenia-blogroll')),
							array('milenia-page-blogroll-item-style', 'not in', array('milenia-entities--style-4 milenia-entities--list', 'milenia-entities--style-7 milenia-entities--list'))
						),
			            'type'  => 'select',
						'options' => array(
							'milenia-grid--cols-4' => esc_html__('4 Columns', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Columns', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Columns', 'milenia-app-textdomain'),
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain')
						),
						'desc' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected conditionals.', 'milenia-app-textdomain'),
						'std'   => 'milenia-grid--cols-3'
			        ),
					array(
			            'id'    => 'milenia-page-columns-individual',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-type-individual', 'in', array('milenia-portfolio', 'milenia-gallery')),
			            'type'  => 'select',
						'options' => array(
							'milenia-grid--cols-4' => esc_html__('4 Columns', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Columns', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Columns', 'milenia-app-textdomain'),
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain')
						),
						'desc' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected conditionals.', 'milenia-app-textdomain'),
						'std'   => 'milenia-grid--cols-3'
			        ),
					array(
			            'id'    => 'milenia-page-top-padding-individual',
			            'name'  => esc_html__('Page top padding', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-settings-inherit-individual', '=', 0),
			            'type'  => 'number',
						'max'   => 140,
						'min'	=> 0,
						'std'   => 95
			        ),
					array(
			            'id'    => 'milenia-page-bottom-padding-individual',
			            'name'  => esc_html__('Page bottom padding', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-settings-inherit-individual', '=', 0),
			            'type'  => 'number',
						'max'   => 140,
						'min'	=> 0,
						'std'   => 95
			        ),
					array(
			            'id'    => 'milenia-page-theme-skin-custom-state',
			            'name'  => esc_html__('Custom color scheme', 'milenia-app-textdomain'),
						'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std'   => false,
						'visible' => array('milenia-page-settings-inherit-individual', '=', 0)
			        ),
					array(
			            'id'    => 'milenia-page-skin',
			            'name'  => esc_html__('Color scheme', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'brown' => esc_html__('Brown', 'milenia-app-textdomain'),
							'gray' => esc_html__('Gray', 'milenia-app-textdomain'),
							'blue' => esc_html__('Blue', 'milenia-app-textdomain'),
							'lightbrown' => esc_html__('Lightbrown', 'milenia-app-textdomain'),
							'green' => esc_html__('Green', 'milenia-app-textdomain')
						),
						'std'   => 'milenia-body--scheme-brown',
						'visible' => array('milenia-page-theme-skin-custom-state', '=', false)
			        ),
					array(
						'id' => 'milenia-page-theme-skin-custom-primary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Primary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
						'id' => 'milenia-page-theme-skin-custom-secondary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Secondary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
			            'id'    => 'milenia-page-border-layout',
			            'name'  => esc_html__('Offset around the page', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-settings-inherit-individual', '=', 0),
						'std' => false
			        )
			    )
			)
		));

		// [Page Options] Footer
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Footer Settings', 'milenia-app-textdomain'),
			    'post_types' => array('page', 'post', 'milenia-portfolio', 'tribe_events', 'mphb_room_type'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-page-footer-state-individual',
			            'name'  => esc_html__('Footer settings', 'milenia-app-textdomain'),
			            'desc'  => esc_html__('Inherit footer options from the theme options page.', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'std' 	=> 1
			        ),
					array(
			            'id'    => 'milenia-page-footer-sections',
						'type'  => 'button_group',
						'multiple' => true,
						'options' => array(
							'footer-section-1' => esc_html__('Section #1', 'milenia-app-textdomain'),
							'footer-section-2' => esc_html__('Section #2', 'milenia-app-textdomain'),
							'footer-section-3' => esc_html__('Section #3', 'milenia-app-textdomain'),
							'footer-section-4' => esc_html__('Section #4', 'milenia-app-textdomain'),
							'footer-section-5' => esc_html__('Section #5', 'milenia-app-textdomain'),
						),
						'inline' => true,
			            'name'  => esc_html__('Using sections', 'milenia-app-textdomain'),
			            'desc'  => esc_html__('Choose sections the footer will be contain which.', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-state-individual', '=', 0),
						'std' 	=> array('footer-section-1'),
			        ),

					// Footer Section #1

					array(
						'type' => 'divider',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1')
					),
					array(
						'type' => 'custom_html',
						'std'  => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Footer section #1', 'milenia-app-textdomain')),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
					),
					array(
			            'id'    => 'milenia-page-footer-section-1-src',
			            'name'  => esc_html__('Widget area', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-2'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-1-padding-y-top',
			            'name'  => esc_html__('Padding top', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-1-padding-y-bottom',
			            'name'  => esc_html__('Padding bottom', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-1-color-settings-states',
			            'name'  => esc_html__('Appearance', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => true,
						'inline' => true,
						'options' => array(
							'background' => esc_html__('Background color', 'milenia-app-textdomain'),
							'text-color' => esc_html__('Text color', 'milenia-app-textdomain'),
							'links-color' => esc_html__('Links color', 'milenia-app-textdomain'),
							'headings-color' => esc_html__('Headings color', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
			            'desc'  => esc_html__("The section has default appearance but if you don't like it you can change. Choose a property you want to change.", 'milenia-app-textdomain'),
						'std' => array('background')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-1-bg',
			            'name'  => esc_html__('Background', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'dark' => esc_html__('Dark', 'milenia-app-textdomain'),
							'light-default' => esc_html__('Light', 'milenia-app-textdomain'),
							'primary' => esc_html__('[Current scheme] Primary', 'milenia-app-textdomain'),
							'secondary' => esc_html__('[Current scheme] Secondary', 'milenia-app-textdomain'),
							'custom' => esc_html__('Custom', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-section-1-color-settings-states', 'contains', 'background'),
						'std' => 'dark'
			        ),
					array(
						'id' => 'milenia-page-footer-section-1-bg-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Background', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-1-bg', 'contains', 'custom'),
						'alpha_channel' => true,
						'std' => '#1c1c1c'
					),
					array(
						'id' => 'milenia-page-footer-section-1-text-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Text color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-1-color-settings-states', 'contains', 'text-color'),
						'alpha_channel' => true,
						'std' => '#858585'
					),
					array(
						'id' => 'milenia-page-footer-section-1-links-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Links color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-1-color-settings-states', 'contains', 'links-color'),
						'alpha_channel' => true,
						'std' => '#ae745a'
					),
					array(
						'id' => 'milenia-page-footer-section-1-headings-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Headings color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-1-color-settings-states', 'contains', 'headings-color'),
						'alpha_channel' => true,
						'std' => '#ffffff'
					),
					array(
			            'id'    => 'milenia-page-footer-section-1-columns',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'std' => 'milenia-grid--cols-4'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-1-full-width',
			            'name'  => esc_html__('Full width', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-1-uppercased-titles',
						'name' => esc_html__('Uppercased titles', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'std' => false
					),
					array(
						'id' => 'milenia-page-footer-section-1-large-offset',
						'name' => esc_html__('Large widget title offset', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'std' => false
					),
					array(
			            'id'    => 'milenia-page-footer-section-1-padding-x',
			            'name'  => esc_html__('Padding (x axis)', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-1-full-width', '=', false),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-1-border-top-color',
						'type' => 'color',
						'name' => esc_html__('Border top color (if exists)', 'milenia-app-textdomain'),
						'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-1-widgets-border',
			            'name'  => esc_html__('Widgets border', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-1-widgets-border-color',
						'type' => 'color',
						'name' => esc_html__('Widgets border color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-1-widgets-border', '=', true),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-1-widgets',
			            'name'  => esc_html__('Widgets settings', 'milenia-app-textdomain'),
			            'type'  => 'widgetsettings',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-1-responsive-breakpoint',
			            'name'  => esc_html__('Responsive breakpoint', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia-app-textdomain'),
							'milenia-grid--responsive-md' => esc_html__('md', 'milenia-app-textdomain'),
							'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia-app-textdomain'),
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-1'),
						'std' => 'milenia-grid--responsive-sm'
			        ),

					// Footer Section #2

					array(
						'type' => 'divider',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2')
					),
					array(
						'type' => 'custom_html',
						'std'  => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Footer section #2', 'milenia-app-textdomain')),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
					),
					array(
			            'id'    => 'milenia-page-footer-section-2-src',
			            'name'  => esc_html__('Widget area', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-3'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-2-padding-y-top',
			            'name'  => esc_html__('Padding top', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-2-padding-y-bottom',
			            'name'  => esc_html__('Padding bottom', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-2-color-settings-states',
			            'name'  => esc_html__('Appearance', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => true,
						'inline' => true,
						'options' => array(
							'background' => esc_html__('Background color', 'milenia-app-textdomain'),
							'text-color' => esc_html__('Text color', 'milenia-app-textdomain'),
							'links-color' => esc_html__('Links color', 'milenia-app-textdomain'),
							'headings-color' => esc_html__('Headings color', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
			            'desc'  => esc_html__("The section has default appearance but if you don't like it you can change. Choose a property you want to change.", 'milenia-app-textdomain'),
						'std' => array('background')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-2-bg',
			            'name'  => esc_html__('Background', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'dark' => esc_html__('Dark', 'milenia-app-textdomain'),
							'light-default' => esc_html__('Light', 'milenia-app-textdomain'),
							'primary' => esc_html__('[Current scheme] Primary', 'milenia-app-textdomain'),
							'secondary' => esc_html__('[Current scheme] Secondary', 'milenia-app-textdomain'),
							'custom' => esc_html__('Custom', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-section-2-color-settings-states', 'contains', 'background'),
						'std' => 'dark'
			        ),
					array(
						'id' => 'milenia-page-footer-section-2-bg-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Background', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-2-bg', 'contains', 'custom'),
						'alpha_channel' => true,
						'std' => '#1c1c1c'
					),
					array(
						'id' => 'milenia-page-footer-section-2-text-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Text color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-2-color-settings-states', 'contains', 'text-color'),
						'alpha_channel' => true,
						'std' => '#858585'
					),
					array(
						'id' => 'milenia-page-footer-section-2-links-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Links color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-2-color-settings-states', 'contains', 'links-color'),
						'alpha_channel' => true,
						'std' => '#ae745a'
					),
					array(
						'id' => 'milenia-page-footer-section-2-headings-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Headings color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-2-color-settings-states', 'contains', 'headings-color'),
						'alpha_channel' => true,
						'std' => '#ffffff'
					),
					array(
			            'id'    => 'milenia-page-footer-section-2-columns',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'std' => 'milenia-grid--cols-4'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-2-full-width',
			            'name'  => esc_html__('Full width', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-2-uppercased-titles',
						'name' => esc_html__('Uppercased titles', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'std' => false
					),
					array(
						'id' => 'milenia-page-footer-section-2-large-offset',
						'name' => esc_html__('Large widget title offset', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'std' => false
					),
					array(
			            'id'    => 'milenia-page-footer-section-2-padding-x',
			            'name'  => esc_html__('Padding (x axis)', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-2-full-width', '=', false),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-2-border-top-color',
						'type' => 'color',
						'name' => esc_html__('Border top color (if exists)', 'milenia-app-textdomain'),
						'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-2-widgets-border',
			            'name'  => esc_html__('Widgets border', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-2-widgets-border-color',
						'type' => 'color',
						'name' => esc_html__('Widgets border color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-2-widgets-border', '=', true),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-2-widgets',
			            'name'  => esc_html__('Widgets settings', 'milenia-app-textdomain'),
			            'type'  => 'widgetsettings',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-2-responsive-breakpoint',
			            'name'  => esc_html__('Responsive breakpoint', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia-app-textdomain'),
							'milenia-grid--responsive-md' => esc_html__('md', 'milenia-app-textdomain'),
							'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia-app-textdomain'),
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-2'),
						'std' => 'milenia-grid--responsive-sm'
			        ),

					// Footer Section #3

					array(
						'type' => 'divider',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3')
					),
					array(
						'type' => 'custom_html',
						'std'  => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Footer section #3', 'milenia-app-textdomain')),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
					),
					array(
			            'id'    => 'milenia-page-footer-section-3-src',
			            'name'  => esc_html__('Widget area', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-4'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-3-padding-y-top',
			            'name'  => esc_html__('Padding top', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-3-padding-y-bottom',
			            'name'  => esc_html__('Padding bottom', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-3-color-settings-states',
			            'name'  => esc_html__('Appearance', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => true,
						'inline' => true,
						'options' => array(
							'background' => esc_html__('Background color', 'milenia-app-textdomain'),
							'text-color' => esc_html__('Text color', 'milenia-app-textdomain'),
							'links-color' => esc_html__('Links color', 'milenia-app-textdomain'),
							'headings-color' => esc_html__('Headings color', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
			            'desc'  => esc_html__("The section has default appearance but if you don't like it you can change. Choose a property you want to change.", 'milenia-app-textdomain'),
						'std' => array('background')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-3-bg',
			            'name'  => esc_html__('Background', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'dark' => esc_html__('Dark', 'milenia-app-textdomain'),
							'light-default' => esc_html__('Light', 'milenia-app-textdomain'),
							'primary' => esc_html__('[Current scheme] Primary', 'milenia-app-textdomain'),
							'secondary' => esc_html__('[Current scheme] Secondary', 'milenia-app-textdomain'),
							'custom' => esc_html__('Custom', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-section-3-color-settings-states', 'contains', 'background'),
						'std' => 'dark'
			        ),
					array(
						'id' => 'milenia-page-footer-section-3-bg-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Background', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-3-bg', 'contains', 'custom'),
						'alpha_channel' => true,
						'std' => '#1c1c1c'
					),
					array(
						'id' => 'milenia-page-footer-section-3-text-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Text color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-3-color-settings-states', 'contains', 'text-color'),
						'alpha_channel' => true,
						'std' => '#858585'
					),
					array(
						'id' => 'milenia-page-footer-section-3-links-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Links color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-3-color-settings-states', 'contains', 'links-color'),
						'alpha_channel' => true,
						'std' => '#ae745a'
					),
					array(
						'id' => 'milenia-page-footer-section-3-headings-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Headings color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-3-color-settings-states', 'contains', 'headings-color'),
						'alpha_channel' => true,
						'std' => '#ffffff'
					),
					array(
			            'id'    => 'milenia-page-footer-section-3-columns',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'std' => 'milenia-grid--cols-4'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-3-full-width',
			            'name'  => esc_html__('Full width', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-3-uppercased-titles',
						'name' => esc_html__('Uppercased titles', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'std' => false
					),
					array(
						'id' => 'milenia-page-footer-section-3-large-offset',
						'name' => esc_html__('Large widget title offset', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'std' => false
					),
					array(
			            'id'    => 'milenia-page-footer-section-3-padding-x',
			            'name'  => esc_html__('Padding (x axis)', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-3-full-width', '=', false),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-3-border-top-color',
						'type' => 'color',
						'name' => esc_html__('Border top color (if exists)', 'milenia-app-textdomain'),
						'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-3-widgets-border',
			            'name'  => esc_html__('Widgets border', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-3-widgets-border-color',
						'type' => 'color',
						'name' => esc_html__('Widgets border color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-3-widgets-border', '=', true),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-3-widgets',
			            'name'  => esc_html__('Widgets settings', 'milenia-app-textdomain'),
			            'type'  => 'widgetsettings',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-3-responsive-breakpoint',
			            'name'  => esc_html__('Responsive breakpoint', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia-app-textdomain'),
							'milenia-grid--responsive-md' => esc_html__('md', 'milenia-app-textdomain'),
							'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia-app-textdomain'),
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-3'),
						'std' => 'milenia-grid--responsive-sm'
			        ),

					// Footer Section #4

					array(
						'type' => 'divider',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4')
					),
					array(
						'type' => 'custom_html',
						'std'  => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Footer section #4', 'milenia-app-textdomain')),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
					),
					array(
			            'id'    => 'milenia-page-footer-section-4-src',
			            'name'  => esc_html__('Widget area', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-5'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-4-padding-y-top',
			            'name'  => esc_html__('Padding top', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-4-padding-y-bottom',
			            'name'  => esc_html__('Padding bottom', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-4-color-settings-states',
			            'name'  => esc_html__('Appearance', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => true,
						'inline' => true,
						'options' => array(
							'background' => esc_html__('Background color', 'milenia-app-textdomain'),
							'text-color' => esc_html__('Text color', 'milenia-app-textdomain'),
							'links-color' => esc_html__('Links color', 'milenia-app-textdomain'),
							'headings-color' => esc_html__('Headings color', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
			            'desc'  => esc_html__("The section has default appearance but if you don't like it you can change. Choose a property you want to change.", 'milenia-app-textdomain'),
						'std' => array('background')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-4-bg',
			            'name'  => esc_html__('Background', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'dark' => esc_html__('Dark', 'milenia-app-textdomain'),
							'light-default' => esc_html__('Light', 'milenia-app-textdomain'),
							'primary' => esc_html__('[Current scheme] Primary', 'milenia-app-textdomain'),
							'secondary' => esc_html__('[Current scheme] Secondary', 'milenia-app-textdomain'),
							'custom' => esc_html__('Custom', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-section-4-color-settings-states', 'contains', 'background'),
						'std' => 'dark'
			        ),
					array(
						'id' => 'milenia-page-footer-section-4-bg-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Background', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-4-bg', 'contains', 'custom'),
						'alpha_channel' => true,
						'std' => '#1c1c1c'
					),
					array(
						'id' => 'milenia-page-footer-section-4-text-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Text color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-4-color-settings-states', 'contains', 'text-color'),
						'alpha_channel' => true,
						'std' => '#858585'
					),
					array(
						'id' => 'milenia-page-footer-section-4-links-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Links color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-4-color-settings-states', 'contains', 'links-color'),
						'alpha_channel' => true,
						'std' => '#ae745a'
					),
					array(
						'id' => 'milenia-page-footer-section-4-headings-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Headings color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-4-color-settings-states', 'contains', 'headings-color'),
						'alpha_channel' => true,
						'std' => '#ffffff'
					),
					array(
			            'id'    => 'milenia-page-footer-section-4-columns',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'std' => 'milenia-grid--cols-4'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-4-full-width',
			            'name'  => esc_html__('Full width', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-4-uppercased-titles',
						'name' => esc_html__('Uppercased titles', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'std' => false
					),
					array(
						'id' => 'milenia-page-footer-section-4-large-offset',
						'name' => esc_html__('Large widget title offset', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'std' => false
					),
					array(
			            'id'    => 'milenia-page-footer-section-4-padding-x',
			            'name'  => esc_html__('Padding (x axis)', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-4-full-width', '=', false),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-4-border-top-color',
						'type' => 'color',
						'name' => esc_html__('Border top color (if exists)', 'milenia-app-textdomain'),
						'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-4-widgets-border',
			            'name'  => esc_html__('Widgets border', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-4-widgets-border-color',
						'type' => 'color',
						'name' => esc_html__('Widgets border color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-4-widgets-border', '=', true),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-4-widgets',
			            'name'  => esc_html__('Widgets settings', 'milenia-app-textdomain'),
			            'type'  => 'widgetsettings',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-4-responsive-breakpoint',
			            'name'  => esc_html__('Responsive breakpoint', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia-app-textdomain'),
							'milenia-grid--responsive-md' => esc_html__('md', 'milenia-app-textdomain'),
							'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia-app-textdomain'),
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-4'),
						'std' => 'milenia-grid--responsive-sm'
			        ),

					// Footer Section #5

					array(
						'type' => 'divider',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5')
					),
					array(
						'type' => 'custom_html',
						'std'  => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Footer section #5', 'milenia-app-textdomain')),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
					),
					array(
			            'id'    => 'milenia-page-footer-section-5-src',
			            'name'  => esc_html__('Widget area', 'milenia-app-textdomain'),
			            'type'  => 'select_advanced',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
			            'options' => array_combine(array_map('milenia_get_item_id', $wp_registered_sidebars), array_map('milenia_get_item_name', $wp_registered_sidebars)),
						'std' => 'widget-area-6'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-5-padding-y-top',
			            'name'  => esc_html__('Padding top', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-5-padding-y-bottom',
			            'name'  => esc_html__('Padding bottom', 'milenia-app-textdomain'),
			            'type'  => 'number',
						'min' => 0,
						'std' => 90,
						'max' => 200,
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
			            'desc'  => esc_html__('In pixels.', 'milenia-app-textdomain'),
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-5-color-settings-states',
			            'name'  => esc_html__('Appearance', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => true,
						'inline' => true,
						'options' => array(
							'background' => esc_html__('Background color', 'milenia-app-textdomain'),
							'text-color' => esc_html__('Text color', 'milenia-app-textdomain'),
							'links-color' => esc_html__('Links color', 'milenia-app-textdomain'),
							'headings-color' => esc_html__('Headings color', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
			            'desc'  => esc_html__("The section has default appearance but if you don't like it you can change. Choose a property you want to change.", 'milenia-app-textdomain'),
						'std' => array('background')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-5-bg',
			            'name'  => esc_html__('Background', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'dark' => esc_html__('Dark', 'milenia-app-textdomain'),
							'light-default' => esc_html__('Light', 'milenia-app-textdomain'),
							'primary' => esc_html__('[Current scheme] Primary', 'milenia-app-textdomain'),
							'secondary' => esc_html__('[Current scheme] Secondary', 'milenia-app-textdomain'),
							'custom' => esc_html__('Custom', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-section-5-color-settings-states', 'contains', 'background'),
						'std' => array('dark')
			        ),
					array(
						'id' => 'milenia-page-footer-section-5-bg-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Background', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-5-bg', 'contains', 'custom'),
						'alpha_channel' => true,
						'std' => '#1c1c1c'
					),
					array(
						'id' => 'milenia-page-footer-section-5-text-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Text color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-5-color-settings-states', 'contains', 'text-color'),
						'alpha_channel' => true,
						'std' => '#858585'
					),
					array(
						'id' => 'milenia-page-footer-section-5-links-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Links color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-5-color-settings-states', 'contains', 'links-color'),
						'alpha_channel' => true,
						'std' => '#ae745a'
					),
					array(
						'id' => 'milenia-page-footer-section-5-headings-color-custom',
						'type' => 'color',
						'name' => esc_html__('[Custom] Headings color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-5-color-settings-states', 'contains', 'headings-color'),
						'alpha_channel' => true,
						'std' => '#ffffff'
					),
					array(
			            'id'    => 'milenia-page-footer-section-5-columns',
			            'name'  => esc_html__('Columns', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia-app-textdomain'),
							'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia-app-textdomain')
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'std' => 'milenia-grid--cols-4'
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-5-full-width',
			            'name'  => esc_html__('Full width', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-5-uppercased-titles',
						'name' => esc_html__('Uppercased titles', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'std' => false
					),
					array(
						'id' => 'milenia-page-footer-section-5-large-offset',
						'name' => esc_html__('Large widget title offset', 'milenia'),
						'type' => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'std' => false
					),
					array(
			            'id'    => 'milenia-page-footer-section-5-padding-x',
			            'name'  => esc_html__('Padding (x axis)', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-5-full-width', '=', false),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-5-border-top-color',
						'type' => 'color',
						'name' => esc_html__('Border top color (if exists)', 'milenia-app-textdomain'),
						'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-5-widgets-border',
			            'name'  => esc_html__('Widgets border', 'milenia-app-textdomain'),
			            'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'std' => true
			        ),
					array(
						'id' => 'milenia-page-footer-section-5-widgets-border-color',
						'type' => 'color',
						'name' => esc_html__('Widgets border color', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-footer-section-5-widgets-border', '=', true),
						'alpha_channel' => true,
						'std' => '#eaeaea'
					),
					array(
			            'id'    => 'milenia-page-footer-section-5-widgets',
			            'name'  => esc_html__('Widgets settings', 'milenia-app-textdomain'),
			            'type'  => 'widgetsettings',
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5')
			        ),
					array(
			            'id'    => 'milenia-page-footer-section-5-responsive-breakpoint',
			            'name'  => esc_html__('Responsive breakpoint', 'milenia-app-textdomain'),
			            'type'  => 'button_group',
						'multiple' => false,
						'inline' => true,
						'options' => array(
							'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia-app-textdomain'),
							'milenia-grid--responsive-md' => esc_html__('md', 'milenia-app-textdomain'),
							'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia-app-textdomain'),
							'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia-app-textdomain'),
						),
						'visible' => array('milenia-page-footer-sections', 'contains', 'footer-section-5'),
						'std' => 'milenia-grid--responsive-sm'
			        )
				)
			)
		));

		// Registration of the team member options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('Team Member Info', 'milenia-app-textdomain'),
			    'post_types' => array('milenia-team-members'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-team-member-position',
			            'name'  => esc_html__('Position', 'milenia-app-textdomain'),
			            'type'  => 'text',
						'placeholder' => esc_html__("Enter the team member's position", 'milenia-app-textdomain')
			        ),
					array(
			            'id'    => 'milenia-team-member-facebook',
			            'name'  => esc_html__('Facebook', 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-team-member-tripadvisor',
			            'name'  => esc_html__('TripAdvisor', 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-team-member-youtube',
			            'name'  => esc_html__('YouTube', 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-team-member-twitter',
			            'name'  => esc_html__('Twitter', 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-team-member-google-plus',
			            'name'  => esc_html__('Google Plus', 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-team-member-instagram',
			            'name'  => esc_html__('Instagram', 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
				    array(
					    'id'    => 'milenia-team-member-whatsapp',
					    'name'  => esc_html__('WhatsApp', 'milenia-app-textdomain'),
					    'type'  => 'text'
				    ),
			    )
			)
		));

		// Registration of the testimonial options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('[Milenia] Testimonial Options', 'milenia-app-textdomain'),
			    'post_types' => array('milenia-testimonials'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-testimonial-author-location',
			            'name'  => esc_html__("Author's location", 'milenia-app-textdomain'),
			            'type'  => 'text'
			        ),
					array(
			            'id'    => 'milenia-testimonial-author-assessment',
			            'name'  => esc_html__("Assessment", 'milenia-app-textdomain'),
			            'type'  => 'number',
						'step' => 1,
						'max' => 5,
						'min' => 1,
						'std' => ''
			        ),
					array(
			            'id'    => 'milenia-testimonial-service-logo',
			            'name'  => esc_html__("Service logo", 'milenia-app-textdomain'),
			            'type'  => 'image_upload',
						'max_file_uploads' => 1
			        ),
					array(
			            'id'    => 'milenia-testimonial-service-link-url',
			            'name'  => esc_html__("Service link", 'milenia-app-textdomain'),
			            'type'  => 'url'
			        ),
					array(
			            'id'    => 'milenia-testimonial-service-link-nofollow',
			            'name'  => esc_html__("Link nofollow option", 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'desc' => esc_html__('Yes', 'milenia-app-textdomain')
			        ),
					array(
			            'id'    => 'milenia-testimonial-service-link-target-blank',
			            'name'  => esc_html__("Open link in a new window", 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'desc' => esc_html__('Yes', 'milenia-app-textdomain')
			        )
			    )
			)
		));

		// Registration of the portfolio project options
		$MileniaFunctionality->registerMetaBoxes(array(
			array(
			    'title' => esc_html__('Project Info', 'milenia-app-textdomain'),
			    'post_types' => array('milenia-portfolio'),
			    'fields' => array(
					array(
			            'id'    => 'milenia-project-layout',
			            'name'  => esc_html__('[Single Page] Layout', 'milenia-app-textdomain'),
			            'type'  => 'image_select',
						'class' => 'milenia-image-select',
			            'options' => array(
			                'slideshow'  => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-left.jpg',
			                'gallery'    => MILENIA_FUNCTIONALITY_URL . 'assets/images/layout-full.jpg'
			            ),
						'std' => 'slideshow'
			        ),
					array(
			            'id'    => 'milenia-project-skin',
			            'name'  => esc_html__('Color scheme', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'brown' => esc_html__('Brown', 'milenia-app-textdomain'),
							'gray' => esc_html__('Gray', 'milenia-app-textdomain'),
							'blue' => esc_html__('Blue', 'milenia-app-textdomain'),
							'lightbrown' => esc_html__('Lightbrown', 'milenia-app-textdomain'),
							'green' => esc_html__('Green', 'milenia-app-textdomain')
						),
						'std'   => 'milenia-body--scheme-brown',
						'visible' => array('milenia-page-theme-skin-custom-state', '=', false)
			        ),
					array(
			            'id'    => 'milenia-page-theme-skin-custom-state',
			            'name'  => esc_html__('Custom color scheme', 'milenia-app-textdomain'),
						'type'  => 'switch',
						'style' => 'rounded',
						'on_label' => esc_html__('On', 'milenia-app-textdomain'),
						'off_label' => esc_html__('Off', 'milenia-app-textdomain'),
						'std'   => false
			        ),
					array(
						'id' => 'milenia-page-theme-skin-custom-primary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Primary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
						'id' => 'milenia-page-theme-skin-custom-secondary',
						'type' => 'color',
						'name' => esc_html__('[Custom] Secondary', 'milenia-app-textdomain'),
						'visible' => array('milenia-page-theme-skin-custom-state', '=', true)
					),
					array(
			            'id'    => 'milenia-post-share-buttons-state',
			            'name'  => esc_html__('[Single Page] Share buttons', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        ),
					array(
			            'id'    => 'milenia-project-related-state',
			            'name'  => esc_html__('[Single Page] Related Projects', 'milenia-app-textdomain'),
						'type'  => 'select',
						'options' => array(
							'show' => esc_html__('Show', 'milenia-app-textdomain'),
							'hide' => esc_html__('Hide', 'milenia-app-textdomain')
						),
						'std'   => 'show'
			        ),
					array(
						'type' => 'divider'
					),
					array(
			            'id'    => 'milenia-project-external-link-state',
			            'name'  => esc_html__('Use external link for this project', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'desc' => esc_html__('Yes', 'milenia-app-textdomain')
			        ),
					array(
			            'id'    => 'milenia-project-external-link',
			            'name'  => esc_html__('External link', 'milenia-app-textdomain'),
			            'type'  => 'url',
						'placeholder' => esc_html__('Enter an external link', 'milenia-app-textdomain'),
						'visible' => array('milenia-project-external-link-state', '=', 1)
			        ),
					array(
			            'id'    => 'milenia-project-external-link-target',
			            'name'  => esc_html__('Open link in a new tab', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'placeholder' => esc_html__('Yes', 'milenia-app-textdomain'),
						'desc' => esc_html__('Yes', 'milenia-app-textdomain'),
						'visible' => array('milenia-project-external-link-state', '=', 1)
			        ),
					array(
			            'id'    => 'milenia-project-external-link-nofollow',
			            'name'  => esc_html__('Add nofollow option', 'milenia-app-textdomain'),
			            'type'  => 'checkbox',
						'placeholder' => esc_html__('Yes', 'milenia-app-textdomain'),
						'desc' => esc_html__('Yes', 'milenia-app-textdomain'),
						'visible' => array('milenia-project-external-link-state', '=', 1)
			        ),
					array(
						'type' => 'divider'
					),
					array(
						'id'    => 'milenia-project-date',
			            'name'  => esc_html__('Project release date', 'milenia-app-textdomain'),
			            'type'  => 'date'
					),
					array(
						'id'    => 'milenia-project-author',
			            'name'  => esc_html__('Author', 'milenia-app-textdomain'),
			            'type'  => 'user',
						'field_type' => 'select_advanced',
						'placeholder' => esc_html__('Select an author', 'milenia-app-textdomain')
					),
					array(
			            'id'    => 'milenia-project-meta',
			            'name'  => esc_html__('Additional meta information', 'milenia-app-textdomain'),
			            'type'  => 'key_value',
						'placeholder' => esc_html__('Enter additional information about the project in key-value format.', 'milenia-app-textdomain')
			        )
			    )
			)
		));
	}
}


// helpers
if( !function_exists('milenia_get_item_id') ) {
	function milenia_get_item_id($item) {
		return isset($item['id']) ? $item['id'] : null;
	}
}

if( !function_exists('milenia_get_item_name') ) {
	function milenia_get_item_name($item) {
		return isset($item['name']) ? $item['name'] : null;
	}
}
?>
