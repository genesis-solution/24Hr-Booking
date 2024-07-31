<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class OffersShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Offers', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_offers',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows offers.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Item style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_offers_item_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-pricing-tables--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-pricing-tables--style-2'
                    ),
                    'description' => esc_html__('Select an item style.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Layout', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_offers_layout',
                    'value' => array(
                        esc_html__('Grid', 'milenia-app-textdomain') => 'grid',
                        esc_html__('Masonry', 'milenia-app-textdomain') => 'masonry'
                    ),
                    'description' => esc_html__('Select layout.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_offers_columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'description' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected layout.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Show filter', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_filter_state',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Show categories', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_categories_state',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Default title', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_filter_default_title',
                    'value' => esc_html__( 'All', 'milenia-app-textdomain' ),
                    'description' => esc_html__( 'Enter the name of the tab that is responsible for displaying all posts.', 'milenia-app-textdomain' ),
                    'group' => esc_html__( 'Filter settings', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'milenia_offers_filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_data_total_items',
                    'value' => 6,
                    'description' => esc_html__( 'Enter total amount of projects.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Order by', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_data_order_by',
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
                    'param_name' => 'milenia_offers_data_sort_order',
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
                    'param_name' => 'milenia_offers_data_offset',
                    'description' => esc_html__( 'Number of grid elements to displace or pass over.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => 'get_terms',
                    "term" => 'milenia-offers-categories',
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_data_categories',
                    'description' => esc_html__( 'Select the categories from which the projects will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-offers',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_data_inc',
                    'description' => esc_html__( 'Choose projects which will be included into the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-offers',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_offers_data_exc',
                    'description' => esc_html__( 'Choose projects which will be excluded from the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
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
        add_shortcode('vc_milenia_offers', array($this, 'content'));
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
			'milenia_offers_item_style' => 'milenia-pricing-tables--style-1',
			'milenia_offers_layout' => 'grid',
			'milenia_offers_columns' => 'milenia-grid--cols-4',
			'milenia_offers_data_total_items' => 6,
			'milenia_offers_data_order_by' => 'date',
			'milenia_offers_data_sort_order' => 'DESC',
			'milenia_offers_data_offset' => 0,
			'milenia_offers_data_categories' => '',
			'milenia_offers_data_tags' => '',
			'milenia_offers_data_inc' => '',
			'milenia_offers_data_exc' => '',
			'milenia_offers_categories_state' => 0,
			'milenia_offers_filter_state' => 0,
			'milenia_offers_filter_default_title' => esc_html__('All', 'milenia-app-textdomain'),
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_offers' );

		$this->unique_filter_id = self::getShortcodeUniqueId('vc-offers-filter');
		$this->unique_container_id = self::getShortcodeUniqueId('vc-offers-container');

		// Sanitization of the attributes
		$item_style = $this->throughWhiteList($this->attributes['milenia_offers_item_style'], array( 'milenia-pricing-tables--style-1', 'milenia-pricing-tables--style-2' ), 'milenia-pricing-tables--style-1');
		$this->layout = $this->throughWhiteList($this->attributes['milenia_offers_layout'], array( 'grid', 'masonry' ), 'grid');
		$columns = $this->throughWhiteList($this->attributes['milenia_offers_columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4' ), 'milenia-grid--cols-3');
		$total_items = is_numeric($this->attributes['milenia_offers_data_total_items']) ? intval($this->attributes['milenia_offers_data_total_items']) : 6;

		$container_classes = array($item_style);
		$grid_classes = array($columns);

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		if(boolval($this->attributes['milenia_offers_categories_state'])) {
			set_query_var('milenia-item-categories-state', '1');
		}
		else {
			set_query_var('milenia-item-categories-state', '0');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-offers-container.tpl'), array(
			'${items}' => $this->getItems(),
			'${filter}' => ((bool) $this->attributes['milenia_offers_filter_state']) ? $this->getFilter() : '',
			'${container_id}' => esc_attr( $this->unique_container_id ),
			'${filter_id}' => esc_attr( $this->unique_filter_id ),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($grid_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation']),
			'${isotope_layout}' => esc_attr($this->layout),
			'${data_total_items}' => esc_attr($total_items)
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
		$total_items = is_numeric($this->attributes['milenia_offers_data_total_items']) ? intval($this->attributes['milenia_offers_data_total_items']) : 6;
		$offset = is_numeric($this->attributes['milenia_offers_data_offset']) ? intval($this->attributes['milenia_offers_data_offset']) : 0;
		$order_by = $this->throughWhiteList($this->attributes['milenia_offers_data_order_by'], array('date', 'title', 'id', 'rand'), 'date');
		$order = $this->throughWhiteList($this->attributes['milenia_offers_data_sort_order'], array('DESC', 'ASC'), 'DESC');

		$query_args = array(
			'post_type' => 'milenia-offers',
			'post_status' => 'publish',
			'orderby' => $order_by,
			'order' => $order,
			'numberposts' => $total_items,
			'offset' => $offset
		);

		if(!empty($this->attributes['milenia_offers_data_inc']) && $this->attributes['milenia_offers_data_inc'] != 'none') {
			$query_args[strrev('edulcni')] = str_replace(' ', '', $this->attributes['milenia_offers_data_inc']);
		}

		if(!empty($this->attributes['milenia_offers_data_exc']) && $this->attributes['milenia_offers_data_exc'] != 'none') {
			$query_args['exclude'] = str_replace(' ', '', $this->attributes['milenia_offers_data_exc']);
		}

		if(!empty($this->attributes['milenia_offers_data_categories']) && $this->attributes['milenia_offers_data_categories'] != 'none') {
			$query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'milenia-offers-categories',
                    'field' => 'term_id',
                    'terms' => explode(',', str_replace(' ', '', $this->attributes['milenia_offers_data_categories'])),
                    'include_children' => true
                )
            );
		}

		$posts = get_posts($query_args);
		ob_start();

		foreach($posts as $index => $post) {
			setup_postdata($post);
			if($this->layout == 'masonry') set_query_var('milenia-item-thumb-size', sprintf('entity-thumb-size-%s', ($index % 2 == 0) ? 'vertical-rectangle' : 'rectangle'));
		?>
				<?php require('templates/vc-milenia-offers-item.php'); ?>
			<?php

			$post_categories = get_the_terms(get_the_ID(), 'milenia-offers-categories');

			foreach($post_categories as $category) {
				if(!in_array($category, $this->categories)) array_push($this->categories, $category);
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
		// milenia_dump($this->categories);

		$filter_items = sprintf('<li><a href="#" class="milenia-active" data-filter="%s">%s</a></li>', '*', esc_html($this->attributes['milenia_offers_filter_default_title']));
		$filter_items .= implode("\r\n", array_map(array($this, 'wrapFilterItem'), $this->categories));

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

		return sprintf('<li><a href="#" data-filter="%s">%s</a></li>', '.milenia-offers-categories-' . esc_attr($item->slug), esc_html(ucfirst($item->name)));
	}
}
?>
