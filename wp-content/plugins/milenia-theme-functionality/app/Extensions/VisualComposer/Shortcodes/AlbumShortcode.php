<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class AlbumShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Album', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_album',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates an album.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'attach_image',
                    'heading' => esc_html__('Cover', 'milenia-app-textdomain'),
                    'param_name' => 'cover',
                    'admin_label' => false
                ),
                array(
                    'type' => 'attach_images',
                    'heading' => esc_html__('Images', 'milenia-app-textdomain'),
                    'param_name' => 'images',
                    'admin_label' => false
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Video URL', 'milenia-app-textdomain'),
                    'param_name' => 'video',
                    'admin_label' => false,
                    'description' => esc_html__('YouTube or Vimeo video url.', 'milenia-app-textdomain')
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
        add_shortcode('vc_milenia_album', array($this, 'content'));
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
			'cover' => '',
            'images' => array(),
            'video' => '',
            'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_album' );


		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-album');
		$container_classes = array('milenia-album');
        $cover = '';
        $action_images = '';
        $action_video = '';

        if(!empty($this->attributes['images']))
        {
            $image_ids = explode(',', $this->attributes['images']);
            $images = array();

            foreach($image_ids as $image_id)
            {
                $images[] = array(
                    'src' => wp_get_attachment_image_url(intval($image_id), 'full'),
                    'opts' => array(
                        'caption' => wp_get_attachment_caption(intval($image_id))
                    )
                );
            }

            $action_images = $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-album/action-images.tpl'), array(
    			'${images}' => esc_js(wp_json_encode($images))
    		));
        }

        if(!empty($this->attributes['video']))
        {
            $action_video = $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-album/action-video.tpl'), array(
    			'${video_url}' => esc_url($this->attributes['video'])
    		));
        }

        if(!empty($this->attributes['cover']))
        {
            $cover = wp_get_attachment_image(intval($this->attributes['cover']), 'entity-thumb-size-square');
        }
        else
        {
            array_push($container_classes, 'milenia-album--no-cover');
        }


        array_push($container_classes, apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_album', $this->attributes ));

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-album/container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
            '${cover}' => $cover,
            '${action_images}' => $action_images,
            '${action_video}' => $action_video,
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }
}
?>
