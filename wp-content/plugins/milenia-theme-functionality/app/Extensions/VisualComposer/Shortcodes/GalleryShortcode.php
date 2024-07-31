<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class GalleryShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Gallery', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_gallery',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a gallery.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Layout', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_gallery_layout',
                    'value' => array(
                        esc_html__('Grid', 'milenia-app-textdomain') => 'grid',
                        esc_html__('Masonry', 'milenia-app-textdomain') => 'masonry'
                    ),
                    'description' => esc_html__('Select layout.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_gallery_columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'description' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected layout.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'No gutters', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_no_gutters',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Show filter', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_filter_state',
                    'value' => 0,
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Default title', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_filter_default_title',
                    'value' => esc_html__( 'All', 'milenia-app-textdomain' ),
                    'description' => esc_html__( 'Enter the name of the tab that is responsible for displaying all posts.', 'milenia-app-textdomain' ),
                    'group' => esc_html__( 'Filter settings', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'milenia_gallery_filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_data_total_items',
                    'value' => 6,
                    'description' => esc_html__( 'Enter total amount of projects.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Sort order', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_data_sort_order',
                    'value' => array(
                        esc_html__( 'Descending', 'milenia-app-textdomain' ) => 'DESC',
                        esc_html__( 'Ascending', 'milenia-app-textdomain' ) => 'ASC'
                    ),
                    'description' => esc_html__( 'Select the sort order.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_terms',
                    'term' => 'milenia-gallery-categories',
                    'column' => 'slug',
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_data_categories',
                    'description' => esc_html__( 'Select the categories from which the galleries will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-galleries',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_data_inc',
                    'description' => esc_html__( 'Choose galleries which will be included into the displayed collection.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-galleries',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_gallery_data_exc',
                    'description' => esc_html__( 'Choose galleries which will be excluded from the displayed collection.', 'milenia-app-textdomain' ),
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
        add_shortcode('vc_milenia_gallery', array($this, 'content'));
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
			'milenia_gallery_layout' => 'grid',
			'milenia_gallery_columns' => 'milenia-grid--cols-4',
			'milenia_gallery_data_total_items' => 6,
			'milenia_gallery_data_sort_order' => 'DESC',
			'milenia_gallery_data_categories' => '',
			'milenia_gallery_data_inc' => '',
			'milenia_gallery_data_exc' => '',
            'milenia_gallery_no_gutters' => 0,
			'milenia_gallery_filter_state' => 0,
			'milenia_gallery_filter_default_title' => esc_html__('All', 'milenia-app-textdomain'),
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_gallery' );

		$this->unique_filter_id = self::getShortcodeUniqueId('vc-gallery-filter');
		$this->unique_container_id = self::getShortcodeUniqueId('vc-gallery-container');

        set_query_var('milenia-gallery-container-id', $this->unique_container_id);

		$this->layout = $this->throughWhiteList($this->attributes['milenia_gallery_layout'], array( 'grid', 'masonry' ), 'grid');
		$columns = $this->throughWhiteList($this->attributes['milenia_gallery_columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4' ), 'milenia-grid--cols-3');
		$total_items = is_numeric($this->attributes['milenia_gallery_data_total_items']) ? intval($this->attributes['milenia_gallery_data_total_items']) : 6;

		$container_classes = array();
        $grid_classes = array($columns);

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

        if(((bool) $this->attributes['milenia_gallery_no_gutters']))
        {
            array_push($grid_classes, 'milenia-grid--no-gutters');
        }

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-gallery-container.tpl'), array(
			'${items}' => $this->getItems(),
            '${filter}' => ((bool) $this->attributes['milenia_gallery_filter_state']) ? $this->getFilter() : '',
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
		$MileniaGalleryRepository = new \MileniaGalleryRepository();
		wp_reset_postdata();

		// Sanitization of the attributes
		$total_items = is_numeric($this->attributes['milenia_gallery_data_total_items']) ? intval($this->attributes['milenia_gallery_data_total_items']) : 6;
		$order = $this->throughWhiteList($this->attributes['milenia_gallery_data_sort_order'], array('DESC', 'ASC'), 'DESC');
		$categories = !empty($this->attributes['milenia_gallery_data_categories']) ? $this->attributes['milenia_gallery_data_categories'] : array();
		$inc = !empty($this->attributes['milenia_gallery_data_inc'])  ? explode(',', str_replace(' ', '', $this->attributes['milenia_gallery_data_inc'])) : array();
		$exc = !empty($this->attributes['milenia_gallery_data_exc'])  ? explode(',', str_replace(' ', '', $this->attributes['milenia_gallery_data_exc'])) : array();

//		$columns = $this->throughWhiteList($this->attributes['milenia_gallery_columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4' ), 'milenia-grid--cols-3');

		$items = $MileniaGalleryRepository->fromCategories($categories, 'milenia-gallery-categories')
										->in($inc)
										->out($exc)
										->order($order)
										->limit($total_items)
                                        ->get();

        if(is_array($items) && count($items)) {
            ob_start();

			$milenia_loop_counter = 0;

			foreach($items as $index => $item) {
				set_query_var('milenia-gallery-item', serialize($item)); ?>

				<?php require('templates/vc-milenia-gallery-item.php'); ?>

            	<?php if(isset($item['parent_gallery_id'])) {
                    $item_categories = get_the_terms($item['parent_gallery_id'], 'milenia-gallery-categories');
                }

                if(isset($item_categories) && !empty($item_categories)) {
                    foreach($item_categories as $category) {
                        if(!in_array($category, $this->categories)) array_push($this->categories, $category);
                    }
                }

				$milenia_loop_counter++;
			}

            return ob_get_clean();
        }

        wp_reset_postdata();

        return '';
	}

	/**
	 * Returns markup of the filter element.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getFilter()
	{
		$filter_items = sprintf('<li><a href="#" class="milenia-active" data-filter="%s">%s</a></li>', '*', esc_html($this->attributes['milenia_gallery_filter_default_title']));
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
		return sprintf('<li><a href="#" data-filter="%s">%s</a></li>', '.milenia-gallery-categories-' . esc_attr($item->slug), esc_html(ucfirst($item->name)));
	}
}
?>
