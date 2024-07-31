<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class ProgressBarsShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
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
            'name' => esc_html__('Progress Bars', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_progress_bars',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Shows progress bars.', 'milenia-app-textdomain'),
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
                    'heading' => esc_html__('Progress bars', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_progress_bars',
                    'description' => esc_html__('Here you can create progress bars.', 'milenia-app-textdomain'),
                    'params' => array(
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Title', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_progress_bar_title',
                            'description' => esc_html__('Enter text used as a title of the bar.', 'milenia-app-textdomain'),
                            'admin_label' => true
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_html__('Value', 'milenia-app-textdomain'),
                            'param_name' => 'milenia_progress_bar_value',
                            'description' => esc_html__('Enter value in percentage (from 0 to 100).', 'milenia-app-textdomain'),
                            'admin_label' => true
                        )
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Predefined color scheme', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_progress_bars_predefined_scheme',
                    'value' => array(
                        esc_html__('Primary', 'milenia-app-textdomain') => 'milenia-progress-bars--primary',
                        esc_html__('Secondary', 'milenia-app-textdomain') => 'milenia-progress-bars--secondary'
                    ),
                    'description' => esc_html__('Specifies color scheme of the progress bars. It depends on the selected page skin.', 'milenia-app-textdomain'),
                    'admin_label' => false
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
        add_shortcode('vc_milenia_progress_bars', array($this, 'content'));
    }

    /**
     * Returns an html markup of the shortcode.
     *
     * @access public
     * @return string
     */
    public function content($atts, $content = null)
    {
        $atts['milenia_progress_bars'] = vc_param_group_parse_atts( $atts['milenia_progress_bars'] );

		$this->attributes = shortcode_atts( array(
			'milenia_widget_title' => '',
			'milenia_progress_bars' => array(),
			'milenia_progress_bars_predefined_scheme' => 'milenia-progress-bars--primary',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_progress_bars' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-progress-bars');
		$color_scheme = $this->throughWhiteList($this->attributes['milenia_progress_bars_predefined_scheme'], array('milenia-progress-bars--primary', 'milenia-progress-bars--secondary'), 'milenia-progress-bars--primary');

		$container_classes = array();
		$element_classes = array($color_scheme);
		$items_template = array();

		if(!empty($this->attributes['milenia_extra_class_name'])) {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none') {
			array_push($container_classes, 'milenia-visible');
		}

		if(count($this->attributes['milenia_progress_bars']) && count($this->attributes['milenia_progress_bars'][0])) {
			foreach ($this->attributes['milenia_progress_bars'] as $index => $progress_bar) {

				$progress_bar_value = is_numeric($progress_bar['milenia_progress_bar_value']) ? intval($progress_bar['milenia_progress_bar_value']) : 0;
				$progress_bar_title = (empty($progress_bar['milenia_progress_bar_title']) || !isset($progress_bar['milenia_progress_bar_title'])) ? '&nbsp;' : $progress_bar['milenia_progress_bar_title'];

				if($progress_bar_value < 0) $progress_bar_value = 0;
				elseif($progress_bar_value > 100) $progress_bar_value = 100;

				array_push($items_template, $this->prepareShortcodeTemplate(self::loadShortcodeTemplate('vc-milenia-progress-bars-item.tpl'), array(
					'${progress_bar_id}' => esc_attr(sprintf('%s-%d', $this->unique_id, $index)),
					'${progress_bar_title}' => esc_html($progress_bar_title),
					'${progress_bar_value}' => esc_attr($progress_bar_value)
				)));
			}
		}

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-progress-bars-container.tpl'), array(
            '${unique_id}' => esc_attr($this->unique_id),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${items}' => implode("\r\n", $items_template),
			'${container_classes}' => esc_attr($this->sanitizeHtmlClasses($container_classes)),
			'${element_classes}' => esc_attr($this->sanitizeHtmlClasses($element_classes)),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
	}
}
?>
