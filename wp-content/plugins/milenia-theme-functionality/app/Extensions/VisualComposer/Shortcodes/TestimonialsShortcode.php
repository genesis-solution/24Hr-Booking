<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class TestimonialsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
{
    /**
     * Returns a parameters array of the shortcode.
     *
     * @access public
     * @return array
     */
    public function getParams()
    {
        return array(
            'name' => esc_html__('Testimonials', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_testimonials',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows clients testimonials.', 'milenia-app-textdomain'),
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
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-testimonials--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-testimonials--style-2',
                        esc_html__('Style 3', 'milenia-app-textdomain') => 'milenia-testimonials--style-3'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'columns',
                    'value' => array(
                        esc_html__('1 Column', 'milenia-app-textdomain') => 'milenia-grid--cols-1',
                        esc_html__('2 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Carousel', 'milenia-app-textdomain' ),
                    'param_name' => 'is_carousel',
                    'admin_label' => false
                ),
	            array(
		            'type' => 'checkbox',
		            'heading' => esc_html__( 'Autoplay', 'milenia-app-textdomain' ),
		            'param_name' => 'autoplay',
		            'description' => esc_html__( 'Enables autoplay mode.', 'milenia-app-textdomain' ),
		            'value' => array( esc_html__( 'Yes, please', 'milenia-app-textdomain' ) => 'yes' ),
		            'dependency' => array(
			            'element' => 'is_carousel',
			            'not_empty' => true
		            )
	            ),
	            array(
		            'type' => 'textfield',
		            'heading' => esc_html__( 'Autoplay timeout', 'milenia-app-textdomain' ),
		            'param_name' => 'autoplaytimeout',
		            'description' => esc_html__( 'Autoplay interval timeout', 'milenia-app-textdomain' ),
		            'value' => 5000,
		            'dependency' => array(
			            'element' => 'autoplay',
			            'value' => array( 'yes' )
		            )
	            ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'total_items',
                    'value' => 3,
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
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Offset', 'milenia-app-textdomain' ),
                    'param_name' => 'offset',
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => "get_terms",
                    "term" => "milenia-testimonials-categories",
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'categories',
                    'description' => esc_html__( 'Select the categories from which the items will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-testimonials',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'inc',
                    'description' => esc_html__( 'Enter the identifiers of items which will be included into the displayed collection (comma separated).', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-testimonials',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'exc',
                    'description' => esc_html__( 'Enter the identifiers of items which will be excluded from the displayed collection (comma separated).', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__( 'Assessment color', 'milenia-app-textdomain' ),
                    'param_name' => 'assessment_color',
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__( 'Text color', 'milenia-app-textdomain' ),
                    'param_name' => 'text_color',
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__( 'Author name color', 'milenia-app-textdomain' ),
                    'param_name' => 'author_name_color',
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_html__( 'Carousel dots color', 'milenia-app-textdomain' ),
                    'param_name' => 'dots_color',
                    'admin_label' => false,
                    'group' => esc_html__( 'Appearance', 'milenia-app-textdomain' )
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
        add_shortcode('vc_milenia_testimonials', array($this, 'content'));
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
			'milenia_widget_title' => '',
            'style' => 'milenia-testimonials--style-1',
			'columns' => 'milenia-grid--cols-1',
            'is_carousel' => '',
			'total_items' => 2,
			'order_by' => 'date',
			'sort_order' => 'DESC',
			'offset' => 0,
			'categories' => '',
			'inc' => '',
			'exc' => '',
            'assessment_color' => '',
            'text_color' => '',
            'author_name_color' => '',
            'dots_color' => '',
            'no_grid_system' => '',
            'css' => '',
            'css_animation' => 'none',
			'autoplay' => '',
			'autoplaytimeout' => 3000,
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_testimonials' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-testimonials');
        $style = $this->throughWhiteList($this->attributes['style'], array('milenia-testimonials--style-1', 'milenia-testimonials--style-2', 'milenia-testimonials--style-3'), 'milenia-testimonials--style-1');
		$columns = $this->throughWhiteList($this->attributes['columns'], array('milenia-grid--cols-2', 'milenia-grid--cols-1'), 'milenia-grid--cols-1');
		$container_classes = array('milenia-testimonials', $style);
        $row_css = '';

        if(!((bool) $this->attributes['no_grid_system'])) {
            $grid_classes = array('milenia-grid', $columns, 'milenia-grid--shortcode');
        }
        else
        {
            $grid_classes = array('milenia-testimonials-inner');
        }


        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_testimonials', $this->attributes ));

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

        set_query_var('milenia-testimonial-style', $style);
        set_query_var('milenia-testimonial-text-color', $this->attributes['text_color']);
        set_query_var('milenia-testimonial-assessment-color', $this->attributes['assessment_color']);
        set_query_var('milenia-testimonial-author-name-color', $this->attributes['author_name_color']);

        if(boolval($this->attributes['is_carousel']))
        {
            $grid_classes[] = 'owl-carousel';
        }

        if(!empty($this->attributes['dots_color']) && boolval($this->attributes['is_carousel']))
        {
            $row_css .= $this->makeCSS(
                sprintf('#%s .owl-dots .owl-dot', $this->unique_id),
                array(
                    'background-color' => $this->attributes['dots_color']
                )
            );
        }

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-testimonials/container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${items}' => $this->getItems(),
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${grid_classes}' => $this->sanitizeHtmlClasses($grid_classes),
			'${autoplay}' => $this->attributes['autoplay'] == 'yes' ? 'true' : 'false',
			'${autoplaytimeout}' => esc_attr($this->attributes['autoplaytimeout']),
			'${css_animation}' => esc_attr($this->attributes['css_animation']),
            '${data_row_css}' => esc_attr($row_css)
		));
	}

    /**
	 * Returns markup of the team members elements.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getItems()
	{
		global $post;

		$testimonials_template = array();

		// Sanitization of the attributes
		$total_items = is_numeric($this->attributes['total_items']) ? intval($this->attributes['total_items']) : 4;
		$offset = is_numeric($this->attributes['offset']) ? intval($this->attributes['offset']) : 0;
		$order_by = $this->throughWhiteList($this->attributes['order_by'], array('date', 'title', 'id', 'rand'), 'date');
		$order = $this->throughWhiteList($this->attributes['sort_order'], array('DESC', 'ASC'), 'DESC');
		$category = ($this->attributes['categories'] != 'none') ? $this->attributes['categories'] : null;
		$include_posts = str_replace(' ', '', $this->attributes['inc']);
		$exclude_posts = str_replace(' ', '', $this->attributes['exc']);

		$args = array(
			'post_type' => 'milenia-testimonials',
			'post_status' => 'publish',
			'orderby' => $order_by,
			'order' => $order,
			'numberposts' => $total_items,
			'offset' => $offset,
			strrev('edulcni') => empty($include_posts) || $include_posts == 'none' ? null : $include_posts,
			'exclude' => empty($exclude_posts) || $exclude_posts == 'none' ? null : $exclude_posts
		);

		if($category) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'milenia-testimonials-categories',
					'field' => 'id',
					'terms' => $category,
					'include_children' => true
				)
			);
		}

		$testimonials = get_posts($args);

		ob_start();

		if(is_array($testimonials) && count($testimonials)) {
			foreach($testimonials as $post) {
				setup_postdata($post);
				require('templates/vc-milenia-testimonials/item.php');
			}
			wp_reset_postdata();
		}


		return ob_get_clean();
	}
}
?>
