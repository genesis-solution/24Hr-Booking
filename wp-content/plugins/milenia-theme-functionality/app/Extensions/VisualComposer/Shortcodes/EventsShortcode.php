<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class EventsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
{
    /**
	 * Contains all actual categories for the current set of loaded posts.
	 *
	 * @access protected
	 * @var array
	 */
	protected $categories = array();

    /**
     * Returns a parameters array of the shortcode.
     *
     * @access public
     * @return array
     */
    public function getParams()
    {
        return array(
            'name' => esc_html__('Events', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_events',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('A list of events.', 'milenia-app-textdomain'),
            'params' => array(
				array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Layout', 'milenia-app-textdomain'),
                    'param_name' => 'layout',
					'value' => array(
                        esc_html__('Grid', 'milenia-app-textdomain') => 'grid',
                        esc_html__('Carousel', 'milenia-app-textdomain') => 'carousel'
                    ),
                    'admin_label' => false,
					'default' => 'grid'
                ),
				array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Slide title', 'milenia-app-textdomain' ),
                    'param_name' => 'slide_title',
                    'admin_label' => false,
					'dependency' => array(
						'element' => 'layout',
						'value' => 'carousel'
					)
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'description' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected layout.', 'milenia-app-textdomain'),
                    'admin_label' => true,
					'dependency' => array(
						'element' => 'layout',
						'value' => 'grid'
					)
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Show filter', 'milenia-app-textdomain'),
                    'param_name' => 'filter_state',
                    'value' => 0,
                    'admin_label' => false,
					'dependency' => array(
						'element' => 'layout',
						'value' => 'grid'
					)
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Default title', 'milenia-app-textdomain' ),
                    'param_name' => 'filter_default_title',
                    'value' => esc_html__( 'All', 'milenia-app-textdomain' ),
                    'description' => esc_html__( 'Enter the name of the tab that is responsible for displaying all posts.', 'milenia-app-textdomain' ),
                    'group' => esc_html__( 'Filter settings', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'data_total_items',
                    'value' => 6,
                    'description' => esc_html__( 'Enter total amount of projects.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Order by', 'milenia-app-textdomain' ),
                    'param_name' => 'data_order_by',
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
                    'param_name' => 'data_sort_order',
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
                    'param_name' => 'data_offset',
                    'description' => esc_html__( 'Number of grid elements to displace or pass over.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => 'get_terms',
                    "term" => 'tribe_events_cat',
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'data_categories',
                    'description' => esc_html__( 'Select the categories from which the projects will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'tribe_events',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'data_inc',
                    'description' => esc_html__( 'Choose projects which will be included into the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'tribe_events',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'data_exc',
                    'description' => esc_html__( 'Choose projects which will be excluded from the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
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
        add_shortcode('vc_milenia_events', array($this, 'content'));
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
			'columns' => 'milenia-grid--cols-4',
			'layout' => 'grid',
			'slide_title' => '',
			'data_total_items' => 6,
			'data_order_by' => 'date',
			'data_sort_order' => 'DESC',
			'data_offset' => 0,
			'data_categories' => '',
			'data_tags' => '',
			'data_inc' => '',
			'data_exc' => '',
			'filter_state' => 0,
			'filter_default_title' => esc_html__('All', 'milenia-app-textdomain'),
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_events' );

		if($this->attributes['layout'] == 'carousel')
		{
			wp_enqueue_style('owl-carousel');
	        wp_enqueue_script('owl-carousel');
		}

		$this->unique_filter_id = self::getShortcodeUniqueId('vc-events-filter');
		$this->unique_container_id = self::getShortcodeUniqueId('vc-events-container');

		// Sanitization of the attributes
		$columns = $this->throughWhiteList($this->attributes['columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4' ), 'milenia-grid--cols-3');
		$total_items = is_numeric($this->attributes['data_total_items']) ? intval($this->attributes['data_total_items']) : 6;

		$container_classes = array();


        array_push($container_classes, apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_events', $this->attributes));

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

		$grid_classes = array($columns);
		$template = $this->attributes['layout'] == 'grid' ? 'vc-milenia-events-container.tpl' : 'vc-milenia-events-carousel-container.tpl';

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate($template), array(
			'${items}' => $this->getItems(),
			'${filter}' => ((bool) $this->attributes['filter_state']) ? $this->getFilter() : '',
			'${container_id}' => esc_attr( $this->unique_container_id ),
			'${filter_id}' => esc_attr( $this->unique_filter_id ),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($grid_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
	}

    /**
	 * Returns markup of the posts from the blog.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getItems()
	{
		wp_reset_postdata();
		global $post;

		// Sanitization of the attributes
		$total_items = is_numeric($this->attributes['data_total_items']) ? intval($this->attributes['data_total_items']) : 6;
		$offset = is_numeric($this->attributes['data_offset']) ? intval($this->attributes['data_offset']) : 0;
		$order_by = $this->throughWhiteList($this->attributes['data_order_by'], array('date', 'title', 'id', 'rand'), 'date');
		$order = $this->throughWhiteList($this->attributes['data_sort_order'], array('DESC', 'ASC'), 'DESC');

		$query_args = array(
			'post_type' => 'tribe_events',
			'post_status' => 'publish',
			'orderby' => $order_by,
			'order' => $order,
			'numberposts' => $total_items,
			'offset' => $offset
		);

		if(!empty($this->attributes['data_inc']) && $this->attributes['data_inc'] != 'none') {
			$query_args[strrev('edulcni')] = str_replace(' ', '', $this->attributes['data_inc']);
		}

		if(!empty($this->attributes['data_exc']) && $this->attributes['data_exc'] != 'none') {
			$query_args['exclude'] = str_replace(' ', '', $this->attributes['data_exc']);
		}

		if(!empty($this->attributes['data_categories']) && $this->attributes['data_categories'] != 'none') {
			$query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'tribe_events_cat',
                    'field' => 'term_id',
                    'terms' => explode(',', str_replace(' ', '', $this->attributes['data_categories'])),
                    'include_children' => true
                )
            );
		}

		$posts = get_posts($query_args);
		ob_start();

		foreach($posts as $index => $post) {
			setup_postdata($post);
		?>
				<?php if($this->attributes['layout'] == 'grid') : ?>
					<?php require('templates/vc-milenia-events-item.php'); ?>
				<?php else : ?>
					<?php require('templates/vc-milenia-events-carousel-item.php'); ?>
				<?php endif; ?>
			<?php

			$post_categories = get_the_terms(get_the_ID(), 'tribe_events_cat');

            if($post_categories) {
    			foreach($post_categories as $category) {
    				if(!in_array($category->slug, $this->categories)) array_push($this->categories, $category->slug);
    			}
            }
        }

		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Returns markup of the filter element.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getFilter()
	{
		$filter_items = sprintf('<li><a href="#" class="milenia-active" data-filter="%s">%s</a></li>', '*', esc_html($this->attributes['filter_default_title']));
		$filter_items .= implode("\r\n", array_map(array($this, 'wrapFilterItem'), array_unique($this->categories)));

		return $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-filter.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_filter_id),
			'${items}' => $filter_items
		));
	}

	/**
	 * Returns prepared html of the filter item.
	 *
	 * @param string $item
	 * @access protected
	 * @return string
	 */
	protected function wrapFilterItem($item)
	{
		return sprintf('<li><a href="#" data-filter="%s">%s</a></li>', '.tribe_events_cat-' . esc_attr($item), esc_html(ucfirst($item)));
	}
}
?>
