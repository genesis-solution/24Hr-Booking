<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class BannersShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Banners', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_banners',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a sequence of banners.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'param_group',
                    'heading' => esc_html__('Panels', 'milenia-app-textdomain'),
                    'param_name' => 'banners',
                    'description' => esc_html__('Here you can create accordion panels.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'attach_image',
                            'heading' => esc_html__('Image', 'milenia-app-textdomain'),
                            'param_name' => 'image',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'title',
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textarea',
                            'heading' => esc_html__('[Style 2] Content', 'milenia-app-textdomain'),
                            'param_name' => 'banner_content',
                            'admin_label' => false,
                            'description' => esc_html__('You can use the following HTML tags in this field', 'milenia-app-textdomain') . ':' . esc_attr( '<i></i>, <u></u>, <b></b>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <ul></ul>, <ol></ol>, <li></li>' )
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__('Link Settings', 'milenia-app-textdomain'),
                            'param_name' => 'link',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'checkbox',
                            'heading' => esc_html__('[Style 1] 2x size', 'milenia-app-textdomain'),
                            'value' => 0,
                            'param_name' => 'size_2x',
                            'admin_label' => false
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Style', 'milenia-app-textdomain'),
                    'param_name' => 'style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-banners--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-banners--style-2'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'columns',
                    'value' => array(
                        esc_html__('3 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 Columns', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('No gutters', 'milenia-app-textdomain'),
                    'param_name' => 'no_gutters',
                    'value' => 0,
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Add newsletter block', 'milenia-app-textdomain'),
                    'param_name' => 'newsletter',
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'style',
                        'value' => 'milenia-banners--style-1'
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Index', 'milenia-app-textdomain'),
                    'param_name' => 'newsletter_index',
                    'description' => esc_html__('Place of the newsletter block.', 'milenia-app-textdomain'),
                    'value' => '0',
                    'admin_label' => false,
                    'group' => esc_html__('Newsletter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'newsletter',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Subtitle', 'milenia-app-textdomain'),
                    'param_name' => 'newsletter_subtitle',
                    'admin_label' => false,
                    'group' => esc_html__('Newsletter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'newsletter',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                    'param_name' => 'newsletter_title',
                    'admin_label' => false,
                    'group' => esc_html__('Newsletter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'newsletter',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Form id', 'milenia-app-textdomain'),
                    'param_name' => 'newsletter_form_id',
                    'admin_label' => false,
                    'group' => esc_html__('Newsletter settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'newsletter',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Add testimonials block', 'milenia-app-textdomain'),
                    'param_name' => 'testimonials',
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'style',
                        'value' => 'milenia-banners--style-1'
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Index', 'milenia-app-textdomain'),
                    'param_name' => 'testimonials_index',
                    'description' => esc_html__('Place of the testimonials block.', 'milenia-app-textdomain'),
                    'value' => '0',
                    'admin_label' => false,
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Total items', 'milenia-app-textdomain' ),
                    'param_name' => 'total_items',
                    'value' => 3,
                    'admin_label' => false,
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
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
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
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
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__( 'Offset', 'milenia-app-textdomain' ),
                    'param_name' => 'offset',
                    'admin_label' => false,
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
                ),
                array(
                    "type" => "get_terms",
                    "term" => "milenia-testimonials-categories",
                    'heading' => esc_html__( 'Categories', 'milenia-app-textdomain' ),
                    'param_name' => 'categories',
                    'description' => esc_html__( 'Select the categories from which the items will be loaded.', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-testimonials',
                    'heading' => esc_html__( 'Include', 'milenia-app-textdomain' ),
                    'param_name' => 'inc',
                    'description' => esc_html__( 'Enter the identifiers of items which will be included into the displayed collection (comma separated).', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
                ),
                array(
                    'type' => 'get_posts',
                    'post_type' => 'milenia-testimonials',
                    'heading' => esc_html__( 'Exclude', 'milenia-app-textdomain' ),
                    'param_name' => 'exc',
                    'description' => esc_html__( 'Enter the identifiers of items which will be excluded from the displayed collection (comma separated).', 'milenia-app-textdomain' ),
                    'admin_label' => false,
                    'group' => esc_html__('Testimonials settings', 'milenia-app-textdomain'),
                    'dependency' => array(
                        'element' => 'testimonials',
                        'not_empty' => true
                    )
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
        add_shortcode('vc_milenia_banners', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['banners'] = vc_param_group_parse_atts( $atts['banners'] );

		$this->attributes = shortcode_atts( array(
			'milenia_widget_title' => '',
			'banners' => array(),
			'style' => 'milenia-banners--style-1',
			'columns' => 'milenia-grid--cols-3',
			'no_gutters' => 0,
            'newsletter' => 0,
            'newsletter_index' => '0',
            'newsletter_subtitle' => '',
            'newsletter_title' => '',
            'newsletter_form_id' => '',
            'testimonials' => 0,
            'testimonials_index' => '0',

            'total_items' => 3,
			'order_by' => 'date',
			'sort_order' => 'DESC',
			'offset' => 0,
			'categories' => '',
			'inc' => '',
			'exc' => '',

			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_banners' );

        $style = $this->throughWhiteList($this->attributes['style'], array(
			'milenia-banners--style-1',
			'milenia-banners--style-2'
		), 'milenia-banners--style-1');
        $columns = $this->throughWhiteList($this->attributes['columns'], array(
			'milenia-grid--cols-3',
			'milenia-grid--cols-2',
			'milenia-grid--cols-1'
		), 'milenia-grid--cols-3');

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-panels');
		$items_template = array();
		$container_classes = array('milenia-banners', $style);
		$grid_classes = array('milenia-grid', 'milenia-grid--isotope', 'milenia-grid--shortcode', $columns);

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_banners', $this->attributes ));

        if($this->attributes['no_gutters'])
        {
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

        if((bool) $this->attributes['newsletter'] && (bool) $this->attributes['testimonials'] && $this->attributes['newsletter_index'] == $this->attributes['testimonials_index'])
        {
            $this->attributes['testimonials_index'] = intval($this->attributes['testimonials_index']) + 1;
        }

		if( count($this->attributes['banners']) && count($this->attributes['banners'][0]) )
        {
			foreach($this->attributes['banners'] as $index => $banner)
            {
                if((bool) $this->attributes['newsletter'] && intval($this->attributes['newsletter_index']) == $index)
                {
                    array_push($items_template, $this->getNewsletterBlock());
                }
                if((bool) $this->attributes['testimonials'] && intval($this->attributes['testimonials_index']) == $index)
                {
                    array_push($items_template, $this->getTestimonialsBlock());
                }


                $banner = wp_parse_args($banner, array(
                    'image' => '',
                    'title' => '',
                    'banners_content' => '',
                    'link' => '',
                    'size_2x' => 0
                ));

                $item_classes = array('milenia-banner');
                $item_media = '';
                $item_content = '';
                $item_actions = '';
                $item_grid_classes = array('milenia-grid-item');

                if(!empty($banner['link']))
                {
                    $banner_link = vc_build_link($banner['link']);
                }

                if(isset($banner_link) && !empty($banner_link['title']) && isset($banner_link['title']) && isset($banner_link['url']) && !empty($banner_link['url']))
                {
                    $item_actions = $this->prepareShortcodeTemplate(
                        self::loadShortcodeTemplate(
                            'vc-milenia-banners/item-actions.tpl'
                        ),
                        array(
                            '${text}' => esc_html($banner_link['title']),
                            '${url}' => esc_url($banner_link['url']),
                            '${target}' => isset($banner_link['target']) && !empty($banner_link['target']) ? 'target="'.esc_attr($banner_link['target']).'"' : '',
                            '${title}' => 'title="'.esc_attr($banner_link['title']).'"',
                            '${rel}' => isset($banner_link['rel']) && !empty($banner_link['rel']) ? 'rel="'.esc_attr($banner_link['rel']).'"' : ''
                        )
                    );
                }

                if(!empty($banner['image']))
                {
                    $item_media = $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-banners/item-media.tpl'), array(
                        '${image}' => wp_get_attachment_image(intval($banner['image']), 'full'),
                        '${image_url}' => wp_get_attachment_image_url(intval($banner['image']), 'full')
    				));
                }
                else
                {
                    $item_classes[] = 'milenia-banner--no-image';
                }

                if($banner['size_2x'])
                {
                    $item_grid_classes[] = 'milenia-grid-item--2x';
                }



				array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-banners/item.tpl'), array(
                    '${media}' => $item_media,
                    '${item_classes}' => $this->sanitizeHtmlClasses($item_classes),
                    '${item_grid_classes}' => $this->sanitizeHtmlClasses($item_grid_classes),
                    '${content}' => $style != 'milenia-banners--style-1' ? wpautop( wp_kses( $banner['banner_content'], array(
						'i' => array(),
						'u' => array(),
						'b' => array(),
						'strong' => array(),
						'p' => array(),
						's' => array(),
						'q' => array(),
						'blockquote' => array(),
						'cite' => array(),
						'ul' => array(
							'type' => true
						),
						'ol' => array(
							'type' => true
						),
						'li' => array()
					) ) ) : '',
                    '${title}' => esc_html($banner['title']),
					'${actions}' => $item_actions
				)));
			}
		}
        else
        {
            if((bool) $this->attributes['newsletter'])
            {
                array_push($items_template, $this->getNewsletterBlock());
            }
            if((bool) $this->attributes['testimonials'])
            {
                array_push($items_template, $this->getTestimonialsBlock());
            }
        }

        if(count($this->attributes['banners']) && count($this->attributes['banners'][0]) && (bool) $this->attributes['newsletter'] && $this->attributes['newsletter_index'] >= count($this->attributes['banners']))
        {
            array_push($items_template, $this->getNewsletterBlock());
        }
        if(count($this->attributes['banners']) && count($this->attributes['banners'][0]) && (bool) $this->attributes['testimonials'] && $this->attributes['testimonials_index'] >= count($this->attributes['banners']))
        {
            array_push($items_template, $this->getNewsletterBlock());
        }

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-banners/container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
            '${items}' => implode('', $items_template),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${grid_classes}' => $this->sanitizeHtmlClasses($grid_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }

    public function getNewsletterBlock()
    {
        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-banners/newsletter.tpl'), array(
            '${shortcode}' => do_shortcode(
                sprintf(
                    '[vc_milenia_newsletter form_id="%d" button_color="milenia-newsletter--btn-dark"][/vc_milenia_newsletter]',
                    intval($this->attributes['newsletter_form_id'])
                )
            ),
            '${subtitle}' => !empty($this->attributes['newsletter_subtitle']) ? sprintf('<h6 class="milenia-section-subtitle milenia-font--like-body">%s</h6>', esc_html($this->attributes['newsletter_subtitle'])) : '',
            '${title}' => !empty($this->attributes['newsletter_title']) ? sprintf('<h2 class="milenia-section-title">%s</h2>', esc_html($this->attributes['newsletter_title'])) : ''
		));
    }

    public function getTestimonialsBlock()
    {
        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-banners/testimonials.tpl'), array(
            '${shortcode}' => do_shortcode(
                sprintf(
                    '[vc_milenia_testimonials no_grid_system="true" is_carousel="true" total_items="%d" order_by="%s" sort_order="%s" offset="%d" categories="%s" inc="%s" exc="%s"][/vc_milenia_testimonials]',
                    intval($this->attributes['total_items']),
                    $this->attributes['order_by'],
                    $this->attributes['sort_order'],
                    intval($this->attributes['offset']),
                    $this->attributes['categories'],
                    $this->attributes['inc'],
                    $this->attributes['exc']
                )
            )
		));
    }
}
?>
