<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class TeamMembersShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Team Members', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_team_members',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows team members.', 'milenia-app-textdomain'),
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
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_team_members_columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'admin_label' => true
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'Show social icons', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_social_icons',
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_data_total_items',
                    'value' => 4,
                    'description' => esc_html__( 'Enter total amount of items.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Order by', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_data_order_by',
                    'value' => array(
                        esc_html__( 'Date', 'milenia-app-textdomain' ) => 'date',
                        esc_html__( 'Title', 'milenia-app-textdomain' ) => 'title',
                        esc_html__( 'ID', 'milenia-app-textdomain' ) => 'id',
                        esc_html__( 'Random', 'milenia-app-textdomain' ) => 'rand'
                    ),
                    'description' => esc_html__( 'Select a column by which items will be ordered.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__( 'Sort order', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_data_sort_order',
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
                    'param_name' => 'milenia_team_members_data_offset',
                    'description' => esc_html__( 'Number of grid elements to displace or pass over.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    "type" => "get_terms",
                    "term" => "milenia-tm-categories",
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_data_categories',
                    'description' => esc_html__( 'Select the categories from which the items will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-team-members',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_data_inc',
                    'description' => esc_html__( 'Enter the identifiers of items which will be included into the displayed collection (comma separated).', 'milenia-app-textdomain' ),
                    'admin_label' => true,
                    'group' => esc_html__( 'Data settings', 'milenia-app-textdomain' )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-team-members',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_team_members_data_exc',
                    'description' => esc_html__( 'Enter the identifiers of items which will be excluded from the displayed collection (comma separated).', 'milenia-app-textdomain' ),
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
        add_shortcode('vc_milenia_team_members', array($this, 'content'));
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
            'css_animation' => 'none',
            'milenia_team_members_columns' => 'milenia-grid--cols-4',
            'milenia_team_members_social_icons' => false,
            'milenia_team_members_data_total_items' => 4,
            'milenia_team_members_data_order_by' => 'date',
            'milenia_team_members_data_sort_order' => 'DESC',
            'milenia_team_members_data_offset' => 0,
            'milenia_team_members_data_categories' => '',
            'milenia_team_members_data_inc' => '',
            'milenia_team_members_data_exc' => '',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_team_members' );

        $columns = $this->throughWhiteList($this->attributes['milenia_team_members_columns'], array('milenia-grid--cols-4', 'milenia-grid--cols-3', 'milenia-grid--cols-2', 'milenia-grid--cols-1'), 'milenia-grid--cols-4');
        $container_classes = array();
        $team_members_element_classes = array();
        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-team-members');

        array_push($team_members_element_classes, $columns);

        if(!empty($this->attributes['milenia_extra_class_name'])) {
            array_push($container_classes, $this->attributes['milenia_extra_class_name']);
        }
        if($this->attributes['css_animation'] == 'none') {
            array_push($container_classes, 'milenia-visible');
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-team-members-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${items}' => $this->getItems(),
            '${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
            '${team_members_element_classes}' => esc_attr($this->sanitizeHtmlClasses($team_members_element_classes)),
            '${css_animation}' => esc_attr($this->attributes['css_animation'])
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

		$team_members_template = array();

		// Sanitization of the attributes
		$total_items = is_numeric($this->attributes['milenia_team_members_data_total_items']) ? intval($this->attributes['milenia_team_members_data_total_items']) : 4;
		$offset = is_numeric($this->attributes['milenia_team_members_data_offset']) ? intval($this->attributes['milenia_team_members_data_offset']) : 0;
		$order_by = $this->throughWhiteList($this->attributes['milenia_team_members_data_order_by'], array('date', 'title', 'id', 'rand'), 'date');
		$order = $this->throughWhiteList($this->attributes['milenia_team_members_data_sort_order'], array('DESC', 'ASC'), 'DESC');
		$category = ($this->attributes['milenia_team_members_data_categories'] != 'none') ? $this->attributes['milenia_team_members_data_categories'] : null;
		$include_posts = str_replace(' ', '', $this->attributes['milenia_team_members_data_inc']);
		$exclude_posts = str_replace(' ', '', $this->attributes['milenia_team_members_data_exc']);

		$args = array(
			'post_type' => 'milenia-team-members',
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
					'taxonomy' => 'milenia-tm-categories',
					'field' => 'id',
					'terms' => $category,
					'include_children' => true
				)
			);
		}

		$team_members = get_posts($args);

		ob_start();

		if(is_array($team_members) && count($team_members)) {
			foreach($team_members as $post) {
				setup_postdata($post);
				require('templates/vc-milenia-team-members-item.php');
			}
			wp_reset_postdata();
		}


		return ob_get_clean();
	}
}
?>
