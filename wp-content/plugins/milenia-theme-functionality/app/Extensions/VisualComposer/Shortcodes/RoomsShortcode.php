<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class RoomsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
{

    /**
     * Contains the current query results.
     *
     * @var array|null $rooms
     * @access protected
     */
    protected $rooms;

    /**
     * Contains tabs markup.
     *
     * @var array $tabs_template
     * @access protected
     */
    protected $tabs_template = array();

    /**
     * Contains tabs nav markup.
     *
     * @var array $tabs_nav_template
     * @access protected
     */
    protected $tabs_nav_template = array();

    /**
     * Returns a parameters array of the shortcode.
     *
     * @access public
     * @return array
     */
    public function getParams()
    {
        return array(
            'name' => esc_html__('Rooms', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_rooms',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a list of room types.', 'milenia-app-textdomain'),
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
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-entities--style-15',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-entities--style-10',
                        esc_html__('Style 3', 'milenia-app-textdomain') => 'milenia-entities--style-11',
                        esc_html__('Style 4', 'milenia-app-textdomain') => 'milenia-entities--style-12',
                        esc_html__('Style 5', 'milenia-app-textdomain') => 'milenia-entities--style-13',
                        esc_html__('Style 6', 'milenia-app-textdomain') => 'milenia-entities--style-14'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'columns',
                    'value' => array(
                        esc_html__('1 Column', 'milenia-app-textdomain') => 'milenia-grid--cols-1',
                        esc_html__('2 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('3 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('4 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4'
                    ),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'style',
                        'value' => array('milenia-entities--style-10', 'milenia-entities--style-11', 'milenia-entities--style-12', 'milenia-entities--style-13', 'milenia-entities--style-14')
                    )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__('Content area background', 'milenia-app-textdomain'),
                    'param_name' => 'content_area_background',
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Show "View details" button', 'milenia-app-textdomain'),
                    'param_name' => 'show_button_details',
                    'admin_label' => false,
                    'value' => 'true'
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Show "Book Now" button', 'milenia-app-textdomain'),
                    'param_name' => 'show_button_book',
                    'admin_label' => false,
                    'value' => 'true',
                    'dependency' => array(
                        'element' => 'style',
                        'value' => array('milenia-entities--style-15', 'milenia-entities--style-14', 'milenia-entities--style-12', 'milenia-entities--style-11', 'milenia-entities--style-10')
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Show content', 'milenia-app-textdomain'),
                    'param_name' => 'show_content',
                    'admin_label' => false,
                    'value' => 'true',
                    'dependency' => array(
                        'element' => 'style',
                        'value' => array('milenia-entities--style-15', 'milenia-entities--style-14', 'milenia-entities--style-12', 'milenia-entities--style-11', 'milenia-entities--style-10')
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Carousel', 'milenia-app-textdomain'),
                    'param_name' => 'carousel',
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Show filter', 'milenia-app-textdomain'),
                    'param_name' => 'filter_state',
                    'admin_label' => false,
                    'description' => esc_html__('For carousel layout only.', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'style',
                        'value' => array('milenia-entities--style-15')
                    )
                ),
	            array(
		            'type' => 'checkbox',
		            'heading' => esc_html__('Show Gallery', 'milenia-app-textdomain'),
		            'description' => esc_html__('If not checked, will be show featured image', 'milenia-app-textdomain'),
		            'param_name' => 'gallery',
		            'admin_label' => false,
		            'value' => true,
		            'std' => true
	            ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Default title', 'milenia-app-textdomain'),
                    'param_name' => 'filter_default_title',
                    'value' => esc_html__('All', 'milenia-app-textdomain'),
                    'description' => esc_html__( 'Enter the name of the tab that is responsible for displaying all items.', 'milenia-app-textdomain' ),
                    'group' => esc_html__('Filter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Duration of panels animation', 'milenia-app-textdomain'),
                    'param_name' => 'tabs_panels_duration',
                    'value' => 600,
                    'admin_label' => false,
                    'description' => esc_html__('In milliseconds.', 'milenia-app-textdomain'),
                    'group' => esc_html__('Filter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Easing of panels animation', 'milenia-app-textdomain'),
                    'param_name' => 'tabs_panels_easing',
                    'value' => array(
                        'linear',
                        'swing',
                        'easeInQuad',
                        'easeOutQuad',
                        'easeInOutQuad',
                        'easeInCubic',
                        'easeOutCubic',
                        'easeInOutCubic',
                        'easeInQuart',
                        'easeOutQuart',
                        'easeInOutQuart',
                        'easeInQuint',
                        'easeOutQuint',
                        'easeInOutQuint',
                        'easeInSine',
                        'easeOutSine',
                        'easeInOutSine',
                        'easeInExpo',
                        'easeOutExpo',
                        'easeInOutExpo',
                        'easeInCirc',
                        'easeOutCirc',
                        'easeInOutCirc',
                        'easeInElastic',
                        'easeOutElastic',
                        'easeInOutElastic',
                        'easeInBack',
                        'easeOutBack',
                        'easeInOutBack',
                        'easeInBounce',
                        'easeOutBounce',
                        'easeInOutBounce'
                    ),
                    'description' => esc_html__('Timing function for the animation of panels.', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'group' => esc_html__('Filter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Active tab number', 'milenia-app-textdomain'),
                    'param_name' => 'filter_active_tab',
                    'value' => '1',
                    'admin_label' => false,
                    'group' => esc_html__('Filter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'filter_state',
                        'not_empty' => true
                    )
                ),

                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'total_items',
                    'value' => 6,
                    'description' => esc_html__( 'Enter total amount of projects.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Order by', 'milenia-app-textdomain' ),
                    'param_name' => 'order_by',
                    'value' => array(
                        esc_html__( 'Date', 'milenia-app-textdomain' ) => 'date',
                        esc_html__( 'Title', 'milenia-app-textdomain' ) => 'title',
                        esc_html__( 'ID', 'milenia-app-textdomain' ) => 'id',
                        esc_html__( 'Random', 'milenia-app-textdomain' ) => 'rand'
                    ),
                    'description' => esc_html__( 'Select a database table column by which projects will be ordered.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Sort order', 'milenia-app-textdomain' ),
                    'param_name' => 'sort_order',
                    'value' => array(
                        esc_html__( 'Descending', 'milenia-app-textdomain' ) => 'DESC',
                        esc_html__( 'Ascending', 'milenia-app-textdomain' ) => 'ASC'
                    ),
                    'description' => esc_html__( 'Select the sort order.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Offset', 'milenia-app-textdomain' ),
                    'param_name' => 'offset',
                    'description' => esc_html__( 'Number of grid elements to displace or pass over.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => 'get_terms',
                    "term" => 'mphb_room_type_category',
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'categories',
                    'description' => esc_html__( 'Select the categories from which the projects will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'mphb_room_type',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'inc',
                    'description' => esc_html__( 'Choose projects which will be included into the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'mphb_room_type',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'exc',
                    'description' => esc_html__( 'Choose projects which will be excluded from the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
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
        add_shortcode('vc_milenia_rooms', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $this->attributes = shortcode_atts(array(
			'milenia_widget_title' => '',
            'columns' => 'milenia-grid--cols-1',
            'style' => 'milenia-entities--style-15',

            'show_button_details' => 0,
            'show_button_book' => 0,
            'show_content' => 0,
            'carousel' => 0,
            'gallery' => 1,
            'content_area_background' => '',
            'filter_state' => 0,
            'filter_default_title' => esc_html__('All', 'milenia-app-textdomain'),
            'tabs_panels_duration' => 600,
            'tabs_panels_easing' => 'linear',
            'filter_active_tab' => 1,

            'total_items' => 6,
			'order_by' => 'date',
			'sort_order' => 'DESC',
			'offset' => 0,
			'categories' => '',
			'tags' => '',
			'inc' => '',
			'exc' => '',

			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_rooms');

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-rooms');
        $this->columns = $this->throughWhiteList($this->attributes['columns'], array(
            'milenia-grid--cols-1',
            'milenia-grid--cols-2',
            'milenia-grid--cols-3',
            'milenia-grid--cols-4'
        ), 'milenia-grid---cols-1');
        $this->style = $this->throughWhiteList($this->attributes['style'], array(
            'milenia-entities--style-15',
            'milenia-entities--style-14',
            'milenia-entities--style-13',
            'milenia-entities--style-12',
            'milenia-entities--style-11',
            'milenia-entities--style-10'
        ), 'milenia-entities--style-15');
        $this->rooms = $this->queryTheRooms();

        $template = 'vc-milenia-rooms/container.tpl';
        $this->element_classes = array('milenia-entities', $this->style);
		$container_classes = array();
        $this->grid_classes = array('milenia-grid', $this->columns);

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_rooms', $this->attributes ));

		if(!empty($this->attributes['milenia_extra_class_name']))
		{
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}

		if($this->attributes['css_animation'] == 'none')
		{
			array_push($container_classes, 'milenia-visible');
		}

        if(boolval($this->attributes['carousel']))
        {
            if($this->style == 'milenia-entities--style-15')
            {
                wp_enqueue_script('milenia-tabbed-grid');
                $this->grid_classes[] = 'milenia-grid--tabbed';
                $this->grid_classes[] = 'milenia-grid--tabbed-loading';
                $this->element_classes[] = 'milenia-entities--with-tabbed-grid';

                if(boolval($this->attributes['filter_state']))
                {
                    wp_enqueue_script('milenia-tabs');

                    $container_classes[] = 'milenia-tabs';
                    $container_classes[] = 'milenia-tabs--unstyled';
                    $this->makeTabs();

                    return $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-rooms/container-carousel-v1-tabbed.tpl'), array(
                        '${unique_id}' => esc_attr($this->unique_id),
            			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
                        '${aria_label}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('aria-label="%s"', esc_attr($this->attributes['milenia_widget_title'])) : '',
                        '${tabs}' => implode('', $this->tabs_template),
                        '${tabs_nav_items}' => implode('', $this->tabs_nav_template),
                        '${panels_animation_easing}' => esc_js($this->attributes['tabs_panels_easing']),
                        '${panels_animation_duration}' => esc_js($this->attributes['tabs_panels_duration']),
            			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
            			'${css_animation}' => esc_attr($this->attributes['css_animation'])
            		));
                }
                $template = 'vc-milenia-rooms/container-carousel-v1.tpl';
            }
            else
            {
                $this->grid_classes[] = 'owl-carousel';
                $this->grid_classes[] = 'owl-carousel--nav-edges';
                $this->grid_classes[] = 'milenia-grid--shortcode';
            }
        }

        $container_classes = array_merge($container_classes, $this->element_classes);

		return $this->prepareShortcodeTemplate(self::loadShortcodeTemplate($template), array(
            '${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${items}' => $this->getItems(),
            '${thumbnails}' => $this->getCarouselThumbnails(),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
            '${element_classes}' => esc_attr($this->sanitizeHtmlClasses($this->element_classes)),
			'${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($this->grid_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }

    /**
     *
     *
     *
     * @param
     * @access
     * @return array|null
     */
    protected function queryTheRooms()
    {
        $results = array(
            'all' => array(
                'name' => esc_html($this->attributes['filter_default_title']),
                'items' => array()
            )
        );

        if(function_exists('MPHB'))
        {
            // Sanitization of the attributes
    		$total_items = is_numeric($this->attributes['total_items']) ? intval($this->attributes['total_items']) : 6;
    		$offset = is_numeric($this->attributes['offset']) ? intval($this->attributes['offset']) : 0;
    		$order_by = $this->throughWhiteList($this->attributes['order_by'], array('date', 'title', 'id', 'rand'), 'date');
    		$order = $this->throughWhiteList($this->attributes['sort_order'], array('DESC', 'ASC'), 'DESC');

    		$query_args = array(
    			'post_status' => 'publish',
    			'orderby' => $order_by,
    			'order' => $order,
    			'posts_per_page' => $total_items,
    			'offset' => $offset
    		);

    		if(!empty($this->attributes['inc']) && $this->attributes['inc'] != 'none') {
    			$query_args[strrev('edulcni')] = str_replace(' ', '', $this->attributes['inc']);
    		}

    		if(!empty($this->attributes['exc']) && $this->attributes['exc'] != 'none') {
    			$query_args['exclude'] = str_replace(' ', '', $this->attributes['exc']);
    		}

    		if(!empty($this->attributes['categories']) && $this->attributes['categories'] != 'none') {
    			$query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'mphb_room_type_category',
                        'field' => 'term_id',
                        'terms' => explode(',', str_replace(' ', '', $this->attributes['categories'])),
                        'include_children' => true
                    )
                );
    		}

            $rooms = MPHB()->getRoomTypeRepository()->findAll($query_args);


            if(is_array($rooms) && !empty($rooms))
            {
                foreach($rooms as $room)
                {
                    array_push($results['all']['items'], $room);

                    if(is_array($room->getCategories()) && !empty($room->getCategories()))
                    {
                        foreach($room->getCategories() as $category)
                        {
                            if(!isset($results[$category->slug]))
                            {
                                $results[$category->slug] = array(
                                    'name' => $category->name,
                                    'items' => array()
                                );
                            }
                            array_push($results[$category->slug]['items'], $room);
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Returns items markup.
     *
     * @param string $category
     * @access protected
     * @return string|null
     */
    protected function getItems($category = 'all')
    {
        global $post;
        if(is_array($this->rooms) && !empty($this->rooms) && isset($this->rooms[$category]) && is_array($this->rooms[$category]) && isset($this->rooms[$category]['items']) && is_array($this->rooms[$category]['items']))
        {
            ob_start();

            $show_gallery = boolval($this->attributes['gallery']);

            foreach($this->rooms[$category]['items'] as $index => $room)
            {
                $post = get_post($room->getId());
                setup_postdata($post);

                require(sprintf('%s/%s/vc-milenia-rooms/item.php', MILENIA_FUNCTIONALITY_ROOT,  App::get('config')['visual-composer']['templates_path']));
            }

            wp_reset_postdata();
            return ob_get_clean();
        }

        return null;
    }

    /**
     * Returns carousel thumbnails markup.
     *
     * @param string $category
     * @access protected
     * @return string
     */
    protected function getCarouselThumbnails($category = 'all')
    {
        if(is_array($this->rooms) && !empty($this->rooms) && isset($this->rooms[$category]) && is_array($this->rooms[$category]) && isset($this->rooms[$category]['items']) && is_array($this->rooms[$category]['items']))
        {
            ob_start();
            foreach($this->rooms[$category]['items'] as $index => $room)
            {
                setup_postdata(get_post($room->getId()));
                require(sprintf('%s/%s/vc-milenia-rooms/item-carousel-v1.php', MILENIA_FUNCTIONALITY_ROOT,  App::get('config')['visual-composer']['templates_path']));
            }

            wp_reset_postdata();

            return ob_get_clean();
        }

        return null;
    }

    /**
     * Returns tabs markup.
     *
     * @access protected
     * @return string
     */
    protected function makeTabs()
    {
        if(is_array($this->rooms) && !empty($this->rooms))
        {
            $counter = 0;
            foreach($this->rooms as $slug => $cat)
            {
                $nav_item_classes = array();
                $nav_item_id = self::getShortcodeUniqueId('vc-milenia-rooms-tabs-nav-item');
                $item_id = self::getShortcodeUniqueId('vc-milenia-rooms-tabs-item');
                $item_id_rooms = self::getShortcodeUniqueId('vc-milenia-rooms');

                if($counter + 1 == intval($this->attributes['filter_active_tab']))
                {
                    $nav_item_classes[] = 'milenia-active';
                }

                array_push($this->tabs_nav_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-rooms/item-nav-carousel-v1-tabbed.tpl'), array(
                    '${item_classes}' => esc_attr($this->sanitizeHtmlClasses($nav_item_classes)),
                    '${item_id}' => esc_attr($nav_item_id),
                    '${index}' => esc_attr($counter),
                    '${item_aria_selected}' => 'false',
                    '${item_href}' => esc_attr($item_id),
                    '${item_text}' => esc_html($cat['name'])
        		)));

                array_push($this->tabs_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-rooms/item-carousel-v1-tabbed.tpl'), array(
                    '${item_id}' => esc_attr($item_id),
                    '${index}' => esc_attr($counter),
                    '${item_aria_labelledby}' => esc_attr($nav_item_id),
                    '${item_content}' => $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-rooms/container-carousel-v1.tpl'), array(
                        '${unique_id}' => esc_attr($item_id_rooms),
                        '${widget_title}' => '',
                        '${items}' => $this->getItems($slug),
                        '${thumbnails}' => $this->getCarouselThumbnails($slug),
            			'${container_classes}' => '',
                        '${element_classes}' => esc_attr($this->sanitizeHtmlClasses($this->element_classes)),
            			'${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($this->grid_classes)),
            			'${css_animation}' => 'none'
            		))
        		)));

                $counter++;
            }
        }
    }
}
?>
