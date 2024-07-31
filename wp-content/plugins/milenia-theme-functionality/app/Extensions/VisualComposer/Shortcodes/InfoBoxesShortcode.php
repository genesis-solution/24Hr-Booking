<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class InfoBoxesShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Info Boxes', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_info_boxes',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a sequence of info boxes.', 'milenia-app-textdomain'),
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
                    'param_name' => 'milenia_info_boxes_style',
                    'value' => array(
                        esc_html__('Style 1', 'milenia-app-textdomain') => 'milenia-entities--style-1',
                        esc_html__('Style 2', 'milenia-app-textdomain') => 'milenia-entities--style-2',
                        esc_html__('Style 3', 'milenia-app-textdomain') => 'milenia-entities--style-3',
                        esc_html__('Style 4', 'milenia-app-textdomain') => 'milenia-entities--style-5'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'param_group',
                    'heading' => esc_html__('Info boxes', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_info_boxes',
                    'description' => esc_html__('Here you can create info boxes.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'title',
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'attach_image',
                            'heading' => esc_html__('Featured Image', 'milenia-app-textdomain'),
                            'param_name' => 'image',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'attach_images',
                            'heading' => esc_html__('Slideshow', 'milenia-app-textdomain'),
                            'param_name' => 'gallery',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'textarea',
                            'heading' => esc_html__('Content', 'milenia-app-textdomain'),
                            'param_name' => 'content',
                            'description' => esc_html__('Enter the content of the icon box. You can use the following HTML tags in this field', 'milenia-app-textdomain') . ': ' . esc_attr( '<i></i>, <u></u>, <b></b>, <em></em>, <strong></strong>, <s></s>, <q></q>, <blockquote></blockquote>, <cite></cite>, <ul></ul>, <li></li>' ),
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('[Meta Link] Text', 'milenia-app-textdomain'),
                            'param_name' => 'meta_link_text',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__('[Meta Link] Settings', 'milenia-app-textdomain'),
                            'param_name' => 'meta_link',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('[Main Link] Text', 'milenia-app-textdomain'),
                            'param_name' => 'link_text',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'vc_link',
                            'heading' => esc_html__("[Main Link] Settings", 'milenia-app-textdomain'),
                            'param_name' => 'link',
                            'admin_label' => false
                        ),
                        array(
                            'type' => 'dropdown',
                            'heading' => esc_html__("[Style 2] Color scheme", 'milenia-app-textdomain'),
                            'param_name' => 'color_scheme',
                            'value' => array(
                                esc_html__('Primary', 'milenia-app-textdomain') => 'milenia-entity--scheme-primary',
                                esc_html__('Light', 'milenia-app-textdomain') => 'milenia-entity--scheme-light',
                                esc_html__('Dark', 'milenia-app-textdomain') => 'milenia-entity--scheme-dark'
                            ),
                            'admin_label' => false,
                            'description' => esc_html__('This option is useful only for info boxes with the second appearance style.', 'milenia-app-textdomain')
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Columns', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_info_boxes_columns',
                    'value' => array(
                        esc_html__('4 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-4',
                        esc_html__('3 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-3',
                        esc_html__('2 columns', 'milenia-app-textdomain') => 'milenia-grid--cols-2',
                        esc_html__('1 column', 'milenia-app-textdomain') => 'milenia-grid--cols-1'
                    ),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_info_boxes_style',
                        'value' => array('milenia-entities--style-1', 'milenia-entities--style-2', 'milenia-entities--style-5')
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Gutters', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_info_boxes_gutters',
                    'value' => array(
                        esc_html__('Yes', 'milenia-app-textdomain') => 'milenia-grid--gutters',
                        esc_html__('No', 'milenia-app-textdomain') => 'milenia-grid--no-gutters'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Filled content area', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_info_boxes_filled',
                    'value' => array(
                        esc_html__('Yes', 'milenia-app-textdomain') => 'milenia-entities--filled',
                        esc_html__('No', 'milenia-app-textdomain') => 'milenia-entities--unfilled'
                    ),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_info_boxes_style',
                        'value' => 'milenia-entities--style-3'
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Reversed chess order', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_info_boxes_reversed',
                    'value' => array(
                        esc_html__('No', 'milenia-app-textdomain') => 'milenia-entities--unreverse',
                        esc_html__('Yes', 'milenia-app-textdomain') => 'milenia-entities--reverse'
                    ),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'milenia_info_boxes_style',
                        'value' => 'milenia-entities--style-3'
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
                    'admin_label' => false,
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
        add_shortcode('vc_milenia_info_boxes', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_info_boxes'] = vc_param_group_parse_atts( $atts['milenia_info_boxes'] );

        $this->attributes = shortcode_atts( array(
            'milenia_widget_title' => '',
            'milenia_info_boxes_style' => 'milenia-entities--style-1',
            'milenia_info_boxes' => array(),
            'milenia_info_boxes_gutters' => 'milenia-grid--gutters',
            'milenia_info_boxes_columns' => 'milenia-grid--cols-4',
            'milenia_info_boxes_filled' => 'milenia-entities--filled',
            'milenia_info_boxes_reversed' => 'milenia-entities--unreverse',
            'css' => '',
            'css_animation' => 'none',
            'milenia_extra_class_name' => ''
        ), $atts, 'vc_milenia_info_boxes' );

        $this->style = $this->throughWhiteList($this->attributes['milenia_info_boxes_style'], array(
            'milenia-entities--style-1',
            'milenia-entities--style-2',
            'milenia-entities--style-3',
            'milenia-entities--style-5'
        ), 'milenia-entities--style-1');

        $this->filled = $this->throughWhiteList($this->attributes['milenia_info_boxes_filled'], array(
            'milenia-entities--filled',
            'milenia-entities--unfilled'
        ), 'milenia-entities--filled');

        if($this->style != 'milenia-entities--style-3')
        {
            $this->columns = $this->throughWhiteList($this->attributes['milenia_info_boxes_columns'], array(
                'milenia-grid--cols-4',
                'milenia-grid--cols-3',
                'milenia-grid--cols-2',
                'milenia-grid--cols-1'
            ), 'milenia-grid--cols-4');
        }
        else
        {
            $this->columns = 'milenia-grid--cols-1';
        }

        $this->gutters = $this->throughWhiteList($this->attributes['milenia_info_boxes_gutters'], array(
            'milenia-grid--gutters',
            'milenia-grid--no-gutters'
        ), 'milenia-grid--gutters');

        $this->reversed = $this->throughWhiteList($this->attributes['milenia_info_boxes_reversed'], array(
            'milenia-entities--unreverse',
            'milenia-entities--reverse'
        ), 'milenia-entities--unreverse');

        $this->unique_id = self::getShortcodeUniqueId('vc-milenia-info-boxes');
        $container_classes = array($this->style, $this->reversed);
        $grid_classes = array($this->columns, $this->gutters);
        $items_template = array();

        if($this->style == 'milenia-entities--style-3')
        {
            array_push($container_classes, $this->filled);
        }

        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_info_boxes', $this->attributes ));

        if(!empty($this->attributes['milenia_extra_class_name'])) {
            array_push($container_classes, $this->attributes['milenia_extra_class_name']);
        }
        if($this->attributes['css_animation'] == 'none') {
            array_push($container_classes, 'milenia-visible');
        }

        if( count($this->attributes['milenia_info_boxes']) && count($this->attributes['milenia_info_boxes'][0]) )
        {
            foreach($this->attributes['milenia_info_boxes'] as $index => $info_box)
            {
                $info_box_data = array(
                    'media' => '',
                    'meta' => '',
                    'title' => '',
                    'body' => '',
                    'footer' => ''
                );
                $info_box_classes = array();
                if(isset($info_box['meta_link']))
                {
                    $info_box_meta_link = vc_build_link($info_box['meta_link']);
                }

                if(isset($info_box['link']))
                {
                    $info_box_link = vc_build_link($info_box['link']);
                }

                if(isset($info_box['meta_link_text']) && !empty($info_box['meta_link_text']))
                {
                    $info_box_data['meta'] = $this->prepareShortcodeTemplate(
                        self::loadShortcodeTemplate(
                            isset($info_box_meta_link) && !empty($info_box_meta_link['url']) ? 'vc-milenia-info-boxes/vc-milenia-info-boxes-meta-link.tpl' :  'vc-milenia-info-boxes/vc-milenia-info-boxes-meta.tpl'
                        ),
                        array(
                            '${link_text}' => esc_html($info_box['meta_link_text']),
                            '${url}' => esc_url($info_box_meta_link['url']),
                            '${target}' => isset($info_box_meta_link['target']) && !empty($info_box_meta_link['target']) ? 'target="'.esc_attr($info_box_meta_link['target']).'"' : '',
                            '${title}' => isset($info_box_meta_link['title']) && !empty($info_box_meta_link['title']) ? 'title="'.esc_attr($info_box_meta_link['title']).'"' : '',
                            '${rel}' => isset($info_box_meta_link['rel']) && !empty($info_box_meta_link['rel']) ? 'rel="'.esc_attr($info_box_meta_link['rel']).'"' : ''
                        )
                    );
                }

                if(!empty($info_box['title']))
                {
                    $info_box_data['title'] = $this->prepareShortcodeTemplate(
                        self::loadShortcodeTemplate(
                            'vc-milenia-info-boxes/vc-milenia-info-boxes-title.tpl'
                        ),
                        array(
                            '${info_box_title}' => esc_html($info_box['title'])
                        )
                    );
                }

                if(!empty($info_box['content']))
                {
                    $info_box_data['body'] = $this->prepareShortcodeTemplate(
                        self::loadShortcodeTemplate(
                            'vc-milenia-info-boxes/vc-milenia-info-boxes-body.tpl'
                        ),
                        array(
                            '${content}' => wpautop( wp_kses( $info_box['content'], array(
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
                            ) ) )
                        )
                    );
                }

                if(!empty($info_box['link_text']) && !empty($info_box_link['url']))
                {
                    if($this->style != 'milenia-entities--style-3')
                    {
                        $info_box_data['footer'] = $this->prepareShortcodeTemplate(
                            self::loadShortcodeTemplate(
                                'vc-milenia-info-boxes/vc-milenia-info-boxes-footer.tpl'
                            ),
                            array(
                                '${link_text}' => esc_html($info_box['link_text']),
                                '${url}' => esc_url($info_box_link['url']),
                                '${target}' => isset($info_box_link['target']) && !empty($info_box_link['target']) ? 'target="'.esc_attr($info_box_link['target']).'"' : '',
                                '${title}' => isset($info_box_link['title']) && !empty($info_box_link['title']) ? 'title="'.esc_attr($info_box_link['title']).'"' : '',
                                '${rel}' => isset($info_box_link['rel']) && !empty($info_box_link['rel']) ? 'rel="'.esc_attr($info_box_link['rel']).'"' : ''
                            )
                        );
                    }
                    else {
                        $info_box_data['footer'] = $this->prepareShortcodeTemplate(
                            self::loadShortcodeTemplate(
                                'vc-milenia-info-boxes/vc-milenia-info-boxes-footer-btn.tpl'
                            ),
                            array(
                                '${link_text}' => esc_html($info_box['link_text']),
                                '${url}' => esc_url($info_box_link['url']),
                                '${target}' => isset($info_box_link['target']) && !empty($info_box_link['target']) ? 'target="'.esc_attr($info_box_link['target']).'"' : '',
                                '${title}' => isset($info_box_link['title']) && !empty($info_box_link['title']) ? 'title="'.esc_attr($info_box_link['title']).'"' : '',
                                '${rel}' => isset($info_box_link['rel']) && !empty($info_box_link['rel']) ? 'rel="'.esc_attr($info_box_link['rel']).'"' : ''
                            )
                        );
                    }
                }

                if($this->style != 'milenia-entities--style-2')
                {
                    if(isset($info_box['gallery']))
                    {
                        array_push($info_box_classes, 'milenia-entity--format-slideshow');
                        $info_box_gallery_images = array();
                        $info_carousel_classes = array();

                        if($this->style == 'milenia-entities--style-3')
                        {
                            array_push($info_carousel_classes, 'owl-carousel--vadaptive');
                        }

                        foreach(explode(',', $info_box['gallery']) as $attachment_id)
                        {
                            if($this->style == 'milenia-entities--style-3')
                            {
                                array_push($info_box_gallery_images, sprintf('<div style="background-image: url(%s)" data-bg-image-src="%1$s" class="milenia-entity-slide"></div>', wp_get_attachment_image_url(intval($attachment_id), 'full')));
                            }
                            else
                            {
                                array_push($info_box_gallery_images, wp_get_attachment_image(intval($attachment_id), 'full', false, array(
                                    'class' => 'owl-carousel-img'
                                )));
                            }
                        }
                        $info_box_data['media'] = $this->prepareShortcodeTemplate(
                            self::loadShortcodeTemplate(
                                'vc-milenia-info-boxes/vc-milenia-info-boxes-carousel.tpl'
                            ),
                            array(
                                '${images}' => implode('', $info_box_gallery_images),
                                '${owl_carousel_classes}' => esc_attr($this->sanitizeHtmlClasses($info_carousel_classes))
                            )
                        );
                    }
                    elseif(isset($info_box['image']) && !empty($info_box['image']) && !empty($info_box_link['url']))
                    {
                        array_push($info_box_classes, 'format-standard');

                        if($this->style == 'milenia-entities--style-3')
                        {
                            $info_box_data['media'] = $this->prepareShortcodeTemplate(
                                self::loadShortcodeTemplate(
                                    'vc-milenia-info-boxes/vc-milenia-info-boxes-featured-image-bg-link.tpl'
                                ),
                                array(
                                    '${image}' => esc_url(wp_get_attachment_image_url(intval($info_box['image']), 'full')),
                                    '${url}' => esc_url($info_box_link['url']),
                                    '${target}' => isset($info_box_link['target']) && !empty($info_box_link['target']) ? 'target="'.esc_attr($info_box_link['target']).'"' : '',
                                    '${title}' => isset($info_box_link['title']) && !empty($info_box_link['title']) ? 'title="'.esc_attr($info_box_link['title']).'"' : '',
                                    '${rel}' => isset($info_box_link['rel']) && !empty($info_box_link['rel']) ? 'rel="'.esc_attr($info_box_link['rel']).'"' : ''
                                )
                            );
                        }
                        else
                        {
                            $info_box_data['media'] = $this->prepareShortcodeTemplate(
                                self::loadShortcodeTemplate(
                                    'vc-milenia-info-boxes/vc-milenia-info-boxes-featured-image-link.tpl'
                                ),
                                array(
                                    '${image}' => wp_get_attachment_image(intval($info_box['image']), 'full', false, array(
                                        'class' => 'owl-carousel-img'
                                    )),
                                    '${url}' => esc_url($info_box_link['url']),
                                    '${target}' => isset($info_box_link['target']) && !empty($info_box_link['target']) ? 'target="'.esc_attr($info_box_link['target']).'"' : '',
                                    '${title}' => isset($info_box_link['title']) && !empty($info_box_link['title']) ? 'title="'.esc_attr($info_box_link['title']).'"' : '',
                                    '${rel}' => isset($info_box_link['rel']) && !empty($info_box_link['rel']) ? 'rel="'.esc_attr($info_box_link['rel']).'"' : ''
                                )
                            );
                        }
                    }
                    elseif(isset($info_box['image']) && !empty($info_box['image']))
                    {
                        array_push($info_box_classes, 'format-standard');

                        if($this->style == 'milenia-entities--style-3')
                        {
                            $info_box_data['media'] = $this->prepareShortcodeTemplate(
                                self::loadShortcodeTemplate(
                                    'vc-milenia-info-boxes/vc-milenia-info-boxes-featured-image-bg.tpl'
                                ),
                                array(
                                    '${image}' => esc_url(wp_get_attachment_image_url(intval($info_box['image']), 'full'))
                                )
                            );
                        }
                        else
                        {
                            $info_box_data['media'] = $this->prepareShortcodeTemplate(
                                self::loadShortcodeTemplate(
                                    'vc-milenia-info-boxes/vc-milenia-info-boxes-featured-image.tpl'
                                ),
                                array(
                                    '${image}' => wp_get_attachment_image(intval($info_box['image']), 'full', false, array(
                                        'class' => 'owl-carousel-img'
                                    ))
                                )
                            );
                        }

                    }
                }
                else
                {
                    if(isset($info_box['color_scheme']) && !empty($info_box['color_scheme']))
                    {
                        array_push($info_box_classes, $info_box['color_scheme']);
                    }

                    if(isset($info_box['image']) && !empty($info_box['image']))
                    {
                        $info_box_data['media'] = $this->prepareShortcodeTemplate(
                            self::loadShortcodeTemplate(
                                'vc-milenia-info-boxes/vc-milenia-info-boxes-featured-image-bg.tpl'
                            ),
                            array(
                                '${image}' => esc_url(wp_get_attachment_image_url(intval($info_box['image']), 'full'))
                            )
                        );
                    }
                }

                array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-info-boxes/vc-milenia-info-boxes-item.tpl'), array(
                    '${info_box_meta}' => $info_box_data['meta'],
                    '${info_box_media}' => $info_box_data['media'],
                    '${info_box_title}' => $info_box_data['title'],
                    '${info_box_body}' => $info_box_data['body'],
                    '${info_box_footer}' => $info_box_data['footer'],
                    '${info_box_classes}' => esc_attr($this->sanitizeHtmlClasses($info_box_classes))
                )));
            }
        }

        return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-info-boxes/vc-milenia-info-boxes-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
            '${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
            '${items}' => implode("\r\n", $items_template),
            '${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
            '${grid_classes}' => esc_attr($this->sanitizeHtmlClasses($grid_classes)),
            '${css_animation}' => esc_attr($this->attributes['css_animation'])
        ));
    }
}
?>
