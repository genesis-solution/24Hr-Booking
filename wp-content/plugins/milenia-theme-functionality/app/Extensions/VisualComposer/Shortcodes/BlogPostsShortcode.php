<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class BlogPostsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Blog Posts', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_blog_posts',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows posts from the blog.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_blog_posts_style',
                    'value' => array(
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-entities--style-4',
                        esc_html__('Style 3', 'milenia-app-textdomain') => 'milenia-entities--style-6 milenia-entities--without-media',
                        esc_html__('Style 4', 'milenia-app-textdomain') => 'milenia-entities--style-7',
                        esc_html__('Style 5', 'milenia-app-textdomain') => 'milenia-entities--style-6',
                        esc_html__('Style 6', 'milenia-app-textdomain') => 'milenia-entities--style-8'
                    ),
                    'description' => esc_html__('Select display style.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Layout', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_blog_posts_layout',
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
                    'param_name' => 'milenia_blog_posts_columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'description' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected layout.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'No content', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_no_content_state',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'No content', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_no_read_more_btn_state',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'No gutters', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_no_gutters',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Show filter', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_filter_state',
                    'value' => 0,
                    'admin_label' => true
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Reduce the number of characters in blockquotes', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_reduce_bq_characters',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Default title', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_filter_default_title',
                    'value' => esc_html__( 'All', 'milenia-app-textdomain' ),
                    'description' => esc_html__( 'Enter the name of the tab that is responsible for displaying all posts.', 'milenia-app-textdomain' ),
                    'group' => esc_html__( 'Filter settings', 'milenia-app-textdomain' ),
                    'dependency' => array(
                        'element' => 'milenia_blog_posts_filter_state',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_total_items',
                    'value' => 8,
                    'description' => esc_html__( 'Enter total amount of posts.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Order by', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_order_by',
                    'value' => array(
                        esc_html__( 'Date', 'milenia-app-textdomain' ) => 'date',
                        esc_html__( 'Title', 'milenia-app-textdomain' ) => 'title',
                        esc_html__( 'ID', 'milenia-app-textdomain' ) => 'id',
                        esc_html__( 'Author', 'milenia-app-textdomain' ) => 'author',
                        esc_html__( 'Random', 'milenia-app-textdomain' ) => 'rand'
                    ),
                    'description' => esc_html__( 'Select a column by which posts will be ordered.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Sort order', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_sort_order',
                    'value' => array(
                        esc_html__( 'Descending', 'milenia-app-textdomain' ) => 'DESC',
                        esc_html__( 'Ascending', 'milenia-app-textdomain' ) => 'ASC'
                    ),
                    'description' => esc_html__( 'Select the sort order.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Offset', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_offset',
                    'description' => esc_html__( 'Number of grid elements to displace or pass over.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => "get_terms",
                    "term" => "category",
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_categories',
                    'description' => esc_html__( 'Select the categories from which the posts will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => "get_terms",
                    "term" => "post_tag",
                    "column" => "slug",
                    'heading' => esc_html__( 'Tags', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_tags',
                    'description' => esc_html__( 'Select the tags that posts should contain.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'post',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_include',
                    'description' => esc_html__( 'Select posts which will be included into the displayed collection (comma separated).', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'post',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_blog_posts_data_exclude',
                    'description' => esc_html__( 'Select posts which will be excluded from the displayed collection (comma separated).', 'milenia-app-textdomain' ),
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
        add_shortcode('vc_milenia_blog_posts', array($this, 'content'));
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
			'milenia_blog_posts_style' => 'milenia-entities--style-4',
			'milenia_blog_posts_layout' => 'grid',
			'milenia_blog_posts_columns' => 'milenia-grid--cols-4',
			'milenia_blog_posts_data_total_items' => 8,
			'milenia_blog_posts_data_order_by' => 'date',
			'milenia_blog_posts_data_sort_order' => 'DESC',
			'milenia_blog_posts_data_offset' => 0,
			'milenia_blog_posts_no_gutters' => 0,
			'milenia_blog_posts_no_content_state' => 0,
			'milenia_blog_posts_no_read_more_btn_state' => 0,
			'milenia_blog_posts_data_categories' => '',
			'milenia_blog_posts_data_tags' => '',
			'milenia_blog_posts_data_include' => '',
			'milenia_blog_posts_data_exclude' => '',
			'milenia_blog_posts_filter_state' => 0,
            'milenia_reduce_bq_characters' => 0,
			'milenia_blog_posts_filter_default_title' => esc_html__('All', 'milenia-app-textdomain'),
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_blog_posts' );

		wp_enqueue_script('isotope');
		wp_enqueue_style('owl-carousel');
		wp_enqueue_script('owl-carousel');
		wp_enqueue_script('media-element');
		wp_enqueue_style('media-element');

		$this->unique_filter_id = self::getShortcodeUniqueId('vc-milenia-blog-posts-filter');
		$this->unique_container_id = self::getShortcodeUniqueId('vc-milenia-blog-posts-container');

		// Sanitization of the attributes
		$this->style = $this->throughWhiteList($this->attributes['milenia_blog_posts_style'], array('milenia-entities--style-9', 'milenia-entities--style-4', 'milenia-entities--style-6 milenia-entities--without-media', 'milenia-entities--style-7', 'milenia-entities--style-6', 'milenia-entities--style-8'), 'milenia-entities--style-9');
		$this->layout = $this->throughWhiteList($this->attributes['milenia_blog_posts_layout'], array( 'grid', 'masonry' ), 'grid');
		$this->columns = $columns = $this->throughWhiteList($this->attributes['milenia_blog_posts_columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4' ), 'milenia-grid--cols-4');
		$total_items = is_numeric($this->attributes['milenia_blog_posts_data_total_items']) ? intval($this->attributes['milenia_blog_posts_data_total_items']) : 8;

		if($this->style == 'milenia-entities--style-6 milenia-entities--without-media') {
			$container_classes = array('milenia-entities--style-6', 'milenia-entities--without-media');
		}
		else {
			$container_classes = array($this->style);
		}

		if(boolval($this->attributes['milenia_blog_posts_no_content_state'])) {
			array_push($container_classes, 'milenia-entities--no-content');
		}

		$grid_classes = array($columns);

		set_query_var('milenia-post-archive-style', $this->style);
		set_query_var('milenia-post-archive-isotope-layout', $this->layout);
		set_query_var('milenia-post-no-content', $this->attributes['milenia_blog_posts_no_content_state']);
		set_query_var('milenia-post-no-read-more-btn', $this->attributes['milenia_blog_posts_no_read_more_btn_state']);

		if(boolval($this->attributes['milenia_blog_posts_no_gutters'])) {
			array_push($grid_classes, 'milenia-grid--no-gutters');
		}

		if(!empty($this->attributes['milenia_extra_class_name']))
		{
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
		{
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-blog-posts-container.tpl'), array(
			'${items}' => $this->getItems(),
			'${filter}' => boolval($this->attributes['milenia_blog_posts_filter_state']) ? $this->getFilter() : '',
			'${container_id}' => esc_attr($this->unique_container_id),
			'${filter_id}' => esc_attr($this->unique_filter_id),
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${grid_classes}' => $this->sanitizeHtmlClasses($grid_classes),
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
		$total_items = is_numeric($this->attributes['milenia_blog_posts_data_total_items']) ? intval($this->attributes['milenia_blog_posts_data_total_items']) : 8;
		$offset = is_numeric($this->attributes['milenia_blog_posts_data_offset']) ? intval($this->attributes['milenia_blog_posts_data_offset']) : 0;
		$order_by = $this->throughWhiteList($this->attributes['milenia_blog_posts_data_order_by'], array('date', 'title', 'id', 'author', 'rand'), 'date');
		$order = $this->throughWhiteList($this->attributes['milenia_blog_posts_data_sort_order'], array('DESC', 'ASC'), 'DESC');
		$category = ($this->attributes['milenia_blog_posts_data_categories'] != 'none') ? $this->attributes['milenia_blog_posts_data_categories'] : null;
		$tag = ($this->attributes['milenia_blog_posts_data_tags'] != 'none') ? $this->attributes['milenia_blog_posts_data_tags'] : null;
		$include_posts = str_replace(' ', '', $this->attributes['milenia_blog_posts_data_include']);
		$exclude_posts = str_replace(' ', '', $this->attributes['milenia_blog_posts_data_exclude']);

		$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'orderby' => $order_by,
			'order' => $order,
			'posts_per_page' => $total_items,
			'offset' => $offset,
			'category' => $category,
			'tag' => $tag,
			'include' => (empty($include_posts) || $include_posts == 'none') ? null : $include_posts,
			'exclude' => (empty($exclude_posts) || $exclude_posts == 'none') ? null : $exclude_posts
		);

		if ( is_user_logged_in() ) {
			if ( current_user_can('editor') || current_user_can('administrator') ) {
				$args['post_status'] = array('publish', 'private');
			}
		}

		$posts = new \WP_Query($args);

		ob_start();

		if ( $posts->have_posts() ): ?>

			<?php $index = 0; ?>

			<?php while ( $posts->have_posts() ) : $posts->the_post();  ?>

				<?php
				$post_categories = get_the_category(get_the_ID());
				foreach($post_categories as $category)
				{
					if($category->cat_ID == 1) continue;

					if(!in_array($category, $this->categories)) array_push($this->categories, $category);
				}

				if($this->layout == 'masonry' && $this->style == 'milenia-entities--style-8')
				{
					if($index % 7 == 0 || (($index % 6 == 0) && $index % 3 - 1 != 0))
					{
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-rectangle');
					}
					elseif($index && $index % 3 == 0) {
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-vertical-rectangle');
					}
					elseif($index % 3 - 1 == 0)
					{
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-square');
					}
					else
					{
						set_query_var('milenia-post-thumb-size', 'entity-thumb-size-rectangle');
					}
				}

				require('templates/vc-milenia-blog-posts-item.php');
				?>

				<?php $index++; ?>

			<?php endwhile ?>

			<?php wp_reset_postdata(); ?>

		<?php endif;

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
		$filter_items = sprintf('<li><a href="#" class="milenia-active" data-filter="%s">%s</a></li>', '*', esc_html($this->attributes['milenia_blog_posts_filter_default_title']));
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
		return sprintf('<li><a href="#" data-filter="%s">%s</a></li>', '.category-' . esc_attr($item->slug), esc_html(ucfirst($item->name)));
	}
}
?>
