<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class InstagramShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Instagram', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_instagram',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates an instagram feed.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('User id', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_user_id',
                    'description' => esc_html__("Instagram user's id. (required)", 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Access token', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_user_access_token',
                    'description' => esc_html__("Instagram user's access token. (required)", 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Client id', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_user_client_id',
                    'description' => esc_html__("Instagram client id. (required)", 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Type', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_type',
                    'value' => array(
                        esc_html__('Simple feed', 'milenia-app-textdomain') => 'simple-feed',
                        esc_html__('Gallery', 'milenia-app-textdomain') => 'gallery',
                        esc_html__('Snake', 'milenia-app-textdomain') => 'snake'
                    ),
                    'description' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected layout.', 'milenia-app-textdomain'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Items per page', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_items_per_page',
                    'description' => esc_html__('Enter an integer number.', 'milenia-app-textdomain'),
                    'value' => 6,
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_columns',
                    'value' => array(
                        esc_html__('6 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-6',
                        esc_html__('5 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-5',
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
                    'heading' => esc_html__( 'Ajax loading', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_instagram_load_more_state',
                    'description' => esc_html__('Allows load items dynamically.', 'milenia-app-textdomain'),
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__( 'No gutters', 'milenia-app-textdomain' ),
                    'param_name' => 'milenia_instagram_no_gutters',
                    'description' => esc_html__('Items will be displayed without gutters.', 'milenia-app-textdomain'),
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Default state] Button text', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_load_more_text',
                    'description' => esc_html__('Will be displayed as default button text.', 'milenia-app-textdomain'),
                    'value' => esc_html__('Load More', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_instagram_load_more_state',
                        'not_empty' => true
                    ),
                    'group' => esc_html__('Ajax loading', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Loading state] Button text', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_load_more_text_loading',
                    'description' => esc_html__('Will be displayed during the loading of new items.', 'milenia-app-textdomain'),
                    'value' => esc_html__('Loading...', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_instagram_load_more_state',
                        'not_empty' => true
                    ),
                    'group' => esc_html__('Ajax loading', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('[Loaded state] Button text', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_instagram_load_more_text_loaded',
                    'description' => esc_html__('Will be displayed when all the items are loaded.', 'milenia-app-textdomain'),
                    'value' => esc_html__('Loaded', 'milenia-app-textdomain'),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_instagram_load_more_state',
                        'not_empty' => true
                    ),
                    'group' => esc_html__('Ajax loading', 'milenia-app-textdomain')
                ),
                vc_map_add_css_animation(),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Extra class name', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_extra_class_name',
                    'admin_label' => true,
                    'description' => esc_html__('Style particular content element differently - add a class name and refer to it in custom CSS.', 'milenia-app-textdomain')
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => esc_html__('Css', 'milenia-app-textdomain'),
                    'param_name' => 'css',
                    'group' => esc_html__('Design options', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_instagram', array($this, 'content'));
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
			'milenia_instagram_user_id' => '',
			'milenia_instagram_user_access_token' => '',
			'milenia_instagram_user_client_id' => '',
			'milenia_instagram_items_per_page' => 6,
			'milenia_instagram_columns' => 'milenia-grid--cols-6',
			'milenia_instagram_no_gutters' => 0,
			'milenia_instagram_type' => 'simple-feed',
			'milenia_instagram_load_more_state' => 0,
			'milenia_instagram_load_more_text' => esc_html__('Load More', 'milenia-app-textdomain'),
			'milenia_instagram_load_more_text_loading' => esc_html__('Loading...', 'milenia-app-textdomain'),
			'milenia_instagram_load_more_text_loaded' => esc_html__('Loaded', 'milenia-app-textdomain'),
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_instagram' );

		wp_enqueue_script('instafeed');
		wp_enqueue_script('instafeed-wrapper');
		wp_enqueue_script('fancybox');
		wp_enqueue_style('fancybox');

		$columns = $this->throughWhiteList($this->attributes['milenia_instagram_columns'], array( 'milenia-grid--cols-1', 'milenia-grid--cols-2', 'milenia-grid--cols-3', 'milenia-grid--cols-4', 'milenia-grid--cols-5', 'milenia-grid--cols-6' ), 'milenia-grid--cols-6');
		$type = $this->throughWhiteList($this->attributes['milenia_instagram_type'], array('simple-feed', 'gallery', 'snake'), 'simple-feed');

		$container_classes = array();
		$grid_classes = array($columns);
		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-instafeed');
		$this->username = sprintf('user-%s', $this->unique_id);

		array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_instagram', $this->attributes ));

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		if(boolval($this->attributes['milenia_instagram_no_gutters'])) {
			array_push($grid_classes, 'milenia-grid--no-gutters');
		}

		switch($type) {
			case 'gallery' :
				array_push($container_classes, 'milenia-gallery');
				array_push($container_classes, 'milenia-gallery--bg-based');
			break;
			case 'simple-feed' :
				array_push($container_classes, 'milenia-instafeed');
			break;
			case 'snake' :
				array_push($container_classes, 'milenia-instafeed');
				array_push($container_classes, 'milenia-instafeed--snake');
			break;
		}

		return $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-instagram-container.tpl'), array(
			'${user}' => esc_attr($this->username),
			'${user_id}' => esc_js($this->attributes['milenia_instagram_user_id']),
			'${user_access_token}' => esc_js($this->attributes['milenia_instagram_user_access_token']),
			'${user_client_id}' => esc_js($this->attributes['milenia_instagram_user_client_id']),
			'${unique_id}' => esc_attr($this->unique_id),
			'${load_more_id}' => esc_attr(sprintf('load-more-%s', $this->unique_id)),
			'${items_per_page}' => esc_attr(intval($this->attributes['milenia_instagram_items_per_page'])),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($grid_classes)),
			'${feed_type}' => esc_js($type),
			'${load_more}' => boolval($this->attributes['milenia_instagram_load_more_state']) ? $this->getLoadMoreButton() : '',
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }

    /**
	 * Returns markup of the button that loads items dynamically.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getLoadMoreButton()
	{
    	ob_start(); ?>
    		<footer class="text-center">
    			<nav>
    				<ul class="milenia-list--unstyled milenia-pagination milenia-pagination--stretched">
    					<li>
    						<a href="#" class="middle" id="<?php echo esc_attr(sprintf('load-more-%s', $this->unique_id)); ?>"
    						   data-ifw-loading-content="<?php echo esc_attr($this->attributes['milenia_instagram_load_more_text_loading']); ?>"
    						   data-ifw-disabled-content="<?php echo esc_attr($this->attributes['milenia_instagram_load_more_text_loaded']); ?>"
    						   data-ifw-base-text="<?php echo esc_attr($this->attributes['milenia_instagram_load_more_text']); ?>"><?php echo esc_html($this->attributes['milenia_instagram_load_more_text']); ?></a>
    					</li>
    				</ul>
    			</nav>
    		</footer>
    	<?php
    	return ob_get_clean();
    }
}
?>
