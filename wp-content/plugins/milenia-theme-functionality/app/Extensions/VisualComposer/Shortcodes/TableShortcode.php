<?php
namespace Milenia\App\Extensions\VisualComposer\Shortcodes;

use Milenia\Core\App;
use Milenia\Core\Extensions\VisualComposer\VisualComposerShortcodeInterface;
use Milenia\Core\Extensions\VisualComposer\VisualComposerExtensionShortcodeBase;

class TableShortcode extends VisualComposerExtensionShortcodeBase implements VisualComposerShortcodeInterface
{
    protected $headings = array();

    /**
     * Returns a parameters array of the shortcode.
     *
     * @access public
     * @return array
     */
    public function getParams()
    {
        return array(
            'name' => esc_html__('Table', 'milenia-app-textdomain'),
            'base' => 'vc_milenia_table',
            'category' => esc_html__('Milenia', 'milenia-app-textdomain'),
            'description' => esc_html__('Creates a table.', 'milenia-app-textdomain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Widget title', 'milenia-app-textdomain'),
                    'param_name' => 'milenia_widget_title',
                    'value' => '',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textarea',
                    'heading' => esc_html__('Table', 'milenia-app-textdomain'),
                    'param_name' => 'table_scheme',
                    'value' => "Heading 1|Heading 2|Heading 3\r\nCell 1|Cell 2|Cell 3\r\nCell 1|Cell 2|Cell3",
                    'description' => esc_html__('Each row is a table row. Use "|" symbol to specify a cell. You also can use the following tags: <a>,<i>,<u>,<b>,<strong>,<s>,<q>.', 'milenia-app-textdomain'),
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Type', 'milenia-app-textdomain'),
                    'param_name' => 'table_type',
                    'value' => array(
                        esc_html__('Horizontal', 'milenia-app-textdomain') => 'horizontal',
                        esc_html__('Vetical', 'milenia-app-textdomain') => 'vertical'
                    ),
                    'admin_label' => false
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => esc_html__('Parse first row cells as headings', 'milenia-app-textdomain'),
                    'param_name' => 'thead',
                    'value' => 'true',
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'table_type',
                        'value' => 'horizontal'
                    )
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_html__('Footer rows', 'milenia-app-textdomain'),
                    'description' => esc_html__('Number of last rows that will be parsed as a table footer.', 'milenia-app-textdomain'),
                    'param_name' => 'tfoot',
                    'admin_label' => false
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => esc_html__('Responsive breakpoint', 'milenia-app-textdomain'),
                    'param_name' => 'breakpoint',
                    'value' => array(
                        esc_html__('sm', 'milenia-app-textdomain') => 'sm',
                        esc_html__('xxxl', 'milenia-app-textdomain') => 'xxxl',
                        esc_html__('xxl', 'milenia-app-textdomain') => 'xxl',
                        esc_html__('xl', 'milenia-app-textdomain') => 'xl',
                        esc_html__('lg', 'milenia-app-textdomain') => 'lg',
                        esc_html__('md', 'milenia-app-textdomain') => 'md'
                    ),
                    'admin_label' => false,
                    'dependency' => array(
                        'element' => 'thead',
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
        add_shortcode('vc_milenia_table', array($this, 'content'));
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
            'table_scheme' => '',
            'table_type' => 'horizontal',
            'thead' => '',
            'tfoot' => '',
            'breakpoint' => 'sm',
			'css' => '',
			'css_animation' => 'none',
			'milenia_extra_class_name' => ''
		), $atts, 'vc_milenia_table' );

		$this->unique_id = self::getShortcodeUniqueId('vc-milenia-table');
		$container_classes = array();

        array_push($container_classes, apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->attributes['css'], ' ' ), 'vc_milenia_table', $this->attributes));

		if(!empty($this->attributes['milenia_extra_class_name']))
        {
			array_push($container_classes, $this->attributes['milenia_extra_class_name']);
		}
		if($this->attributes['css_animation'] == 'none')
        {
			array_push($container_classes, 'milenia-visible');
		}

        $this->headings = array();

		return $this->prepareShortcodeTemplate( self::loadShortcodeTemplate('vc-milenia-table-container.tpl'), array(
			'${unique_id}' => esc_attr($this->unique_id),
			'${table}' => $this->getTable(),
			'${widget_title}' => !empty($this->attributes['milenia_widget_title']) ? sprintf('<h3>%s</h3>', esc_html($this->attributes['milenia_widget_title'])) : '',
			'${container_classes}' => $this->sanitizeHtmlClasses($container_classes),
			'${css_animation}' => esc_attr($this->attributes['css_animation'])
		));
    }

    protected function getTable()
    {
        $table_scheme = $this->attributes['table_scheme'];
        $table_template = array();
        $table_classes = array();

        if(!empty($table_scheme))
        {
            if($this->attributes['table_type'] == 'horizontal')
            {
                $table_classes[] = 'milenia-table--responsive-'.$this->attributes['breakpoint'];
            }
            elseif($this->attributes['table_type'] == 'vertical')
            {
                $table_classes[] = 'milenia-table';
                $table_classes[] = 'milenia-table--vertical';
            }

            $table_template[] = sprintf('<table%s>', !empty($table_classes) ? ' class="'. esc_attr($this->sanitizeHtmlClasses($table_classes)) . '"' : '');
                $table_rows = explode("\n", $table_scheme);
                $table_tfoot_rows = array();

                if(!empty($table_rows))
                {
                    if((bool) $this->attributes['thead'])
                    {
                        $first_row = $this->getRow(array_shift($table_rows), true);
                        if(!empty($first_row))
                        {
                            $table_template[] = '<thead>';
                            $table_template[] = $first_row;
                            $table_template[] = '</thead>';
                        }
                    }

                    if(!empty($this->attributes['tfoot']))
                    {
                        for($i = 0; $i < intval($this->attributes['tfoot']); $i++)
                        {
                            if(!empty($table_rows))
                            {
                                $table_tfoot_rows[] = array_pop($table_rows);
                            }
                        }
                    }

                    if(!empty($table_rows))
                    {
                        $table_template[] = '<tbody>';
                        foreach($table_rows as $index => $row)
                        {
                            $row = $this->getRow($row);
                            if(!empty($row))
                            {
                                $table_template[] = $row;
                            }
                        }
                        $table_template[] = '</tbody>';
                    }

                    if(!empty($table_tfoot_rows))
                    {
                        $table_template[] = '<tfoot>';
                        foreach($table_tfoot_rows as $index => $row)
                        {
                            $row = $this->getRow($row, false, true);
                            if(!empty($row))
                            {
                                $table_template[] = $row;
                            }
                        }
                        $table_template[] = '</tfoot>';
                    }
                }

            $table_template[] = '</table>';
        }

        return implode('', $table_template);
    }

    protected function getRow($row, $is_thead = false, $is_tfoot = false)
    {
        if(empty(trim($row))) return '';

        $cells = explode('|', trim(wp_kses($row, array(
            'a' => array(
                'href' => true,
                'rel' => true,
                'target' => true
            ),
            'i' => array(),
            'u' => array(),
            'b' => array(),
            'strong' => array(),
            's' => array(),
            'q' => array()
        ))));

        $row_template = array();
        if(!empty($cells))
        {
            $row_template[] = '<tr>';
            foreach($cells as $index => $cell)
            {
                if($is_thead)
                {
                    $this->headings[] = $cell;
                }

                $row_template[] = sprintf(
                    '<%s%s>%s</%1$s>',
                    $is_thead || ($this->attributes['table_type'] == 'vertical' && $index == 0) ? 'th' : 'td',
                    (!$is_thead && !$is_tfoot && (bool) $this->attributes['thead']) ? ' data-cell-title="'.esc_attr($this->headings[$index]).'"' : '',
                    trim($cell)
                );
            }
            $row_template[] = '</tr>';
        }

        return implode('', $row_template);
    }
}
?>
