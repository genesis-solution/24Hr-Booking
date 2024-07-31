<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_FILTERS extends WPBE_EXT {

    protected $slug = 'filters'; //unique

    public function __construct() {
        load_plugin_textdomain('bulk-editor', false, 'bulk-editor/languages');

        add_action('wpbe_ext_scripts', array($this, 'wpbe_ext_scripts'), 1);

        //ajax
        add_action('wp_ajax_wpbe_filter_posts', array($this, 'wpbe_filter_posts'), 1);
        add_action('wp_ajax_wpbe_reset_filter', array($this, 'wpbe_reset_filter'), 1);

        //hooks
        add_filter('wpbe_print_plugin_options', array($this, 'wpbe_print_plugin_options'), 1);
        add_filter('wpbe_apply_query_filter_data', array($this, 'wpbe_apply_query_filter_data'));

        //tabs
        $this->add_tab($this->slug, 'top_panel', esc_html__('Filters', 'bulk-editor'), 'filter');
        add_action('wpbe_ext_top_panel_' . $this->slug, array($this, 'wpbe_ext_panel'), 1);
        
        add_action('wpbe_tools_panel_buttons_end', array($this, 'wpbe_tools_panel_buttons_end'), 20);
    }

    public function wpbe_ext_scripts() {
        wp_enqueue_script('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/js/' . $this->slug . '.js', [], WPBE_VERSION);
        ?>
        <script>
            lang.<?php echo $this->slug ?> = {};
            lang.<?php echo $this->slug ?>.filtering = "<?php esc_html_e('Filtering', 'bulk-editor') ?> ...";
            lang.<?php echo $this->slug ?>.filtered = "<?php esc_html_e('Filtered! Table redrawing ...', 'bulk-editor') ?>";
        </script>
        <?php
    }

    public function wpbe_ext_panel() {
        $data = array();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

    //ajax
    public function wpbe_filter_posts() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }

        $filter_data = array();
        parse_str($_REQUEST['filter_data'], $filter_data);
        $this->storage->set_val('wpbe_filter_' . $_REQUEST['filter_current_key'], $filter_data['wpbe_filter']);
        wp_die('done');
    }

    //ajax
    public function wpbe_reset_filter() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }

        $this->reset_filter_storage_data($_REQUEST['filter_current_key']);
        wp_die('done');
    }

    //hook
    public function wpbe_print_plugin_options($args) {
        return $args;
    }

    public function reset_filter_storage_data($filter_current_key) {
        $this->storage->unset_val('wpbe_filter_' . $filter_current_key);
    }

    public function posts_txt_where($where = '') {

        $txt_where_sql = "";

        if (!empty($_REQUEST['wpbe_txt_search'])) {
            foreach ($_REQUEST['wpbe_txt_search'] as $skey => $svalue) {

                if (empty($svalue) AND $svalue !== "0") {
                    continue;
                }

                $behavior = $_REQUEST['wpbe_txt_search_behavior'][$skey];
                $wpbe_text = wp_specialchars_decode(trim(urldecode($svalue)));

                //***

                if (empty($wpbe_text) AND $wpbe_text !== "0") {
                    return $where;
                }

                //***

                $wpbe_text = trim(WPBE_HELPER::strtolower($wpbe_text));
                $wpbe_text = preg_replace('/\s+/', ' ', $wpbe_text);
                //$wpbe_text = preg_quote($wpbe_text, '&');
                //$wpbe_text = str_replace(' ', '?(.*)', $wpbe_text);
                $wpbe_text = str_replace("\&#039;", "\'", $wpbe_text);

                //http://dev.mysql.com/doc/refman/5.7/en/regexp.html
                //***

                $search_by_full_word = FALSE; //OPTION!!

                if ($search_by_full_word) {
                    $wpbe_text = '[[:<:]]' . $wpbe_text . '[[:>:]]';
                }

                //***

                switch ($behavior) {
                    case 'exact':
                        $text_where = "  LOWER({$skey}) = '__WPBE_TEXT__'";
                        break;

                    case 'not':
                        $text_where = "  LOWER({$skey}) NOT REGEXP '__WPBE_TEXT__'";
                        break;

                    case 'begin':
                        $text_where = "  LOWER({$skey}) REGEXP '^__WPBE_TEXT__'";
                        break;

                    case 'end':
                        $text_where = "  LOWER({$skey}) REGEXP '__WPBE_TEXT__$'";
                        break;
                    case 'empty':
                        $text_where = "  LOWER({$skey}) =''";
                        break;
                    default:
                        //like
                        $text_where = "  LOWER({$skey}) REGEXP '__WPBE_TEXT__'";
                        break;
                }

                //***

                if (substr_count($wpbe_text, '^') > 0) {
                    $wpbe_text = explode('^', $wpbe_text);
                    $sql_tpl = '(';

                    //***
                    $not_been_not = 0; //for operation (!) at the end
                    foreach ($wpbe_text as $st) {
                        $sql = $text_where;
                        $cond = 'OR';
                        if (substr($st, 0,1) =='~') {
                            $st = substr($st, 1);
                            $cond = 'AND';
                        }
                        if ($behavior == 'like') {
                            if (substr($st, 0,1) == '!') {
                                $st = substr($st, 1);
                                $sql = str_replace('REGEXP', 'NOT REGEXP', $sql);

                                if (!$not_been_not) {
                                    $cond = ') AND (';
                                } else {
                                    $cond = 'AND';
                                }
                                $not_been_not += 1;
                            }
                        }

                        //***

                        $tmp = $cond . ' ' . (str_replace('__WPBE_TEXT__', $st, $sql)) . ' ';
                        $sql_tpl .= $tmp;
                    }

                    //***

                    $sql_tpl = str_replace('(OR', '', $sql_tpl);
                    $sql_tpl = trim($sql_tpl, ' OR');
                    $sql_tpl = trim($sql_tpl, ' AND');

                    $text_where = $sql_tpl;
                } else {
                    $sql = $text_where;
                    $st = $wpbe_text;
                    $text_where = str_replace('__WPBE_TEXT__', $st, $sql);
                }

                //***

                $txt_where_sql .= ($text_where . ' AND ');
            }

            //***

            $txt_where_sql = trim($txt_where_sql, ' AND');

            $where .= " AND ( " . $txt_where_sql . " ) ";
        }

        //***
        //echo $where;
        return $where;
    }

    public function posts_post_author_where($where = '') {

        if (isset($_REQUEST['wpbe_post_author_search']) AND ! empty($_REQUEST['wpbe_post_author_search'])) {
            $post_author = intval($_REQUEST['wpbe_post_author_search']);
            $where .= sprintf("AND ( post_author=%s )", $post_author);
        }
        return $where;
    }

    public function wpbe_post_from_to($where = '') {

        if (isset($_REQUEST['wpbe_from']) AND ! empty($_REQUEST['wpbe_from'])) {
            foreach ($_REQUEST['wpbe_from'] as $ff => $value) {
                $where .= " AND {$ff} >= '{$value}'";
            }
        }


        if (isset($_REQUEST['wpbe_to']) AND ! empty($_REQUEST['wpbe_to'])) {
            foreach ($_REQUEST['wpbe_to'] as $ff => $value) {
                if (intval($value) > 0) {
                    $where .= " AND {$ff} <= '{$value}'";
                }
            }
        }


        return $where;
    }

    //hook
    public function wpbe_apply_query_filter_data($args) {

        $wpbe_filter = array();

        if (isset($_REQUEST['filter_current_key']) AND ! empty($_REQUEST['filter_current_key'])) {
            $wpbe_filter = $this->storage->get_val('wpbe_filter_' . $_REQUEST['filter_current_key']);
        }

        $fields = $this->settings->get_fields(false);

        $tax_query = array();
        $meta_query = array();

        if (!empty($wpbe_filter)) {

            if (isset($wpbe_filter['taxonomies']) AND ! empty($wpbe_filter['taxonomies'])) {

                foreach ($wpbe_filter['taxonomies'] as $tax_key => $terms_ids) {
                    $operator = $wpbe_filter['taxonomies_operators'][$tax_key];
                    $children = apply_filters('wpbe_filter_include_children', false, $tax_key);
                    if ($operator === 'AND') {
                        //https://wordpress.stackexchange.com/questions/236902/wordpress-tax-query-and-operator-not-functioning-as-desired
                        //when to set operatot to AND - no results
                        foreach ($terms_ids as $tid) {
                            $tax_query[] = array(
                                'taxonomy' => $tax_key,
                                'field' => 'term_id',
                                'terms' => $tid,
                                'include_children' => $children
                            );
                        }
                    } else {
                        $q = array(
                            'taxonomy' => $tax_key,
                            'field' => 'term_id', //term_id, slug
                            'terms' => $terms_ids,
                            'include_children' => $children
                                
                        );

                        //if ($wpbe_filter['taxonomies_operators'][$tax_key] != 'OR') {
                        $q['operator'] = $wpbe_filter['taxonomies_operators'][$tax_key]; //OR, NOT IN
                        //}

                        $tax_query[] = $q;
                    }
                }
            }

            if (isset($wpbe_filter['taxonomies_operators']) AND is_array($wpbe_filter['taxonomies_operators'])) {
                foreach ($wpbe_filter['taxonomies_operators'] as $key_tax => $operator) {
                    if ("NOT EXISTS" == $operator OR "EXISTS" == $operator) {
                        $tax_query[] = array(
                            'taxonomy' => $key_tax,
                            'operator' => $operator,
                        );
                    }
                }
            }


            //***
            //meta keys by which allowed filtering
            $number_keys = array();
            $string_keys = array();

            foreach ($fields as $k => $f) {
                if (isset($wpbe_filter[$k]) AND ! empty($wpbe_filter[$k]) AND isset($f['meta_key'])) {
                    if (in_array($f['type'], array('number', 'timestamp', 'unix'))) {
                        $number_keys[] = $k;
                    } else {
                        $string_keys[] = $k;
                    }
                }
            }

            //***

            if (!empty($number_keys)) {

                foreach ($number_keys as $key) {
                    if (isset($wpbe_filter[$key])) {

                        $meta_key = $fields[$key]['meta_key'];

                        if (isset($wpbe_filter[$key]) AND ! empty($wpbe_filter[$key])) {

                            if (in_array($fields[$key]['type'], array('number', 'unix'))) {

                                $from = 0;
                                $to = 0;

                                if ($fields[$key]['edit_view'] === 'calendar') {
                                    if (!empty($wpbe_filter[$key]['from'])) {
                                        $from = $this->posts->normalize_calendar_date($wpbe_filter[$key]['from'], $key);
                                    }

                                    if (!empty($wpbe_filter[$key]['to'])) {
                                        $to = $this->posts->normalize_calendar_date($wpbe_filter[$key]['to'], $key);
                                    }
                                } else {
                                    $from = floatval(str_replace(',', '.', $wpbe_filter[$key]['from']));
                                    $to = floatval(str_replace(',', '.', $wpbe_filter[$key]['to']));
                                }


                                //***

                                if ($from == 0 AND $to == 0) {
                                    continue; //nothing to select
                                }

                                //***

                                if ($from < $to) {
                                    //https://dev.mysql.com/doc/refman/5.7/en/precision-math-decimal-characteristics.html
                                    $meta_query[] = array(
                                        'key' => $meta_key,
                                        'value' => array($from, $to),
                                        //if to simply set DECIMAL without range - wrong range of data will be found, for example if to search from 100 to 150 will be found range 99.xx - 150!
                                        'type' => 'DECIMAL(30,20)',
                                        'compare' => 'BETWEEN'
                                    );
                                } elseif ($from > $to) {
                                    $meta_query[] = array(
                                        'key' => $meta_key,
                                        'value' => $from,
                                        'type' => 'DECIMAL(30,20)',
                                        'compare' => '>='
                                    );
                                } else {
                                    //$from == $to
                                    $meta_query[] = array(
                                        'key' => $meta_key,
                                        'value' => $from,
                                        'type' => 'DECIMAL(30,20)',
                                        'compare' => '='
                                    );
                                }
                            }
                        }

                        //+++
                    }
                }
            }

            //***
            //for string meta keys
            if (!empty($string_keys)) {
                foreach ($string_keys as $string_key) {

                    $is = true;

                    if (isset($wpbe_filter[$string_key]['value'])) {
                        if (intval($wpbe_filter[$string_key]['value']) === -1 OR strlen($wpbe_filter[$string_key]['value']) === 0) {
                            $is = false;
                        }
                    } else {
                        if (intval($wpbe_filter[$string_key]) === -1 OR strlen($wpbe_filter[$string_key]) === 0) {
                            $is = false;
                        }
                    }
                    if (isset($wpbe_filter[$string_key]['behavior']) && ($wpbe_filter[$string_key]['behavior'] == 'empty' || $wpbe_filter[$string_key]['behavior'] == 'not_empty')) {
                        $is = true;
                    }
                    //+++

                    if ($is) {
                        if ($wpbe_filter[$string_key] === 'zero') {
                            //fix for metafields of type switcher added in WPBE extension
                            $meta_query[] = array(
                                'key' => $fields[$string_key]['meta_key'],
                                'value' => 0,
                                'type' => 'DECIMAL',
                                'compare' => isset($wpbe_filter[$string_key]['behavior']) ? $wpbe_filter[$string_key]['behavior'] : '='
                            );
                        } elseif (isset($wpbe_filter[$string_key]['behavior']) AND $wpbe_filter[$string_key]['behavior'] == 'empty') {
                            $meta_query[] = array(
                                'relation' => 'OR',
                                array(
                                    'key' => $fields[$string_key]['meta_key'],
                                    'value' => '',
                                    'compare' => '='
                                ),
                                array(
                                    'key' => $fields[$string_key]['meta_key'],
                                    'compare' => 'NOT EXISTS'
                                )
                            );
                        } elseif (isset($wpbe_filter[$string_key]['behavior']) AND $wpbe_filter[$string_key]['behavior'] == 'not_empty') {
                            $meta_query[] = array(
                                'key' => $fields[$string_key]['meta_key'],
                                'value' => '',
                                'compare' => '!='
                            );
                        } else {
                            $meta_query[] = array(
                                'key' => $fields[$string_key]['meta_key'],
                                'value' => isset($wpbe_filter[$string_key]['value']) ? $wpbe_filter[$string_key]['value'] : $wpbe_filter[$string_key],
                                'type' => 'CHAR',
                                'compare' => isset($wpbe_filter[$string_key]['behavior']) ? $wpbe_filter[$string_key]['behavior'] : '='
                            );
                        }
                    }
                }
            }
        }

        //***
        $txt_search = array();
        $txt_search['post_title'] = isset($wpbe_filter['post_title']);
        $txt_search['post_content'] = isset($wpbe_filter['post_content']);
        $txt_search['post_excerpt'] = isset($wpbe_filter['post_excerpt']);
        $txt_search['post_name'] = isset($wpbe_filter['post_name']);
        $txt_search['post_password'] = isset($wpbe_filter['post_password']);

        $_REQUEST['wpbe_txt_search'] = array();
        $_REQUEST['wpbe_txt_search_behavior'] = array();
        foreach ($txt_search as $skey => $is) {
            if (isset($wpbe_filter[$skey]) AND ( !empty($wpbe_filter[$skey]['value']) OR $wpbe_filter[$skey]['value'] === "0" OR $wpbe_filter[$skey]['behavior'] == 'empty')) {
                $_REQUEST['wpbe_txt_search'][$skey] = $wpbe_filter[$skey]['value'];
                if ($wpbe_filter[$skey]['behavior'] == 'empty') {
                    $_REQUEST['wpbe_txt_search'][$skey] = 'empty';
                }                
                $_REQUEST['wpbe_txt_search_behavior'][$skey] = $wpbe_filter[$skey]['behavior'];
            }
        }

        if (!empty($_REQUEST['wpbe_txt_search'])) {
            add_filter('posts_where', array($this, 'posts_txt_where'), 101);
        }


        //****

        if (isset($wpbe_filter['post_author']) AND ! empty($wpbe_filter['post_author']) AND $wpbe_filter['post_author'] != -1) {
            $_REQUEST['wpbe_post_author_search'] = $wpbe_filter['post_author'];
            add_filter('posts_where', array($this, 'posts_post_author_where'), 103);
        }


        //***
        //if ordering is by meta key
        if (isset($fields[$args['orderby']]['meta_key']) AND ! empty($fields[$args['orderby']]['meta_key'])) {
            $args['meta_key'] = $fields[$args['orderby']]['meta_key'];
            if (in_array($fields[$args['orderby']]['type'], array('number', 'timestamp', 'unix'))) {
                $args['orderby'] = 'meta_value_num meta_value';
            } else {
                $args['orderby'] = 'meta_value';
            }
        }

        //***

        if (isset($wpbe_filter['post__in']) AND ! empty($wpbe_filter['post__in']['value'])) {

            $p_ids = array();
            $tmp = explode(',', $wpbe_filter['post__in']['value']);

            if (!empty($tmp)) {
                foreach ($tmp as $vv) {
                    if (substr_count($vv, '-') > 0) {
                        $vv = explode('-', trim($vv));
                        if (!empty($vv[0]) AND ! empty($vv[1])) {
                            if ($vv[0] !== $vv[1]) {
                                $start = $vv[0] < $vv[1] ? $vv[0] : $vv[1];
                                $finish = $vv[1] > $vv[0] ? $vv[1] : $vv[0];
                                while (true) {
                                    $p_ids[] = $start;
                                    $start++;
                                    if ($start > $finish) {
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        $p_ids[] = intval($vv);
                    }
                }
            }

            $args['post__in'] = $p_ids;
        }

        //++++++++++++++++++++++++++++++++++++++++++

        $from_to_fields = ['menu_order', 'post_parent'];

        foreach ($fields as $key => $value) {
            if ($value['edit_view'] === 'calendar' AND $value['field_type'] !== 'meta') {
                $from_to_fields[] = $key;
            }
        }

        $tmp = 103;
        foreach ($from_to_fields as $ff) {
            if ((isset($wpbe_filter[$ff . '_from']) AND ! empty($wpbe_filter[$ff . '_from']))
                    OR ( isset($wpbe_filter[$ff . '_to']) AND ! empty($wpbe_filter[$ff . '_to']))) {
                $_REQUEST['wpbe_from'][$ff] = isset($wpbe_filter[$ff . '_from']) ? $wpbe_filter[$ff . '_from'] : null;
                $_REQUEST['wpbe_to'][$ff] = isset($wpbe_filter[$ff . '_to']) ? $wpbe_filter[$ff . '_to'] : null;
                add_filter('posts_where', array($this, 'wpbe_post_from_to'), $tmp++);
            }
        }

        //++++++++++++++++++++++++++++++++++++++++++
        //post_status
        if (isset($wpbe_filter['post_status']) AND intval($wpbe_filter['post_status']) !== -1) {
            $args['post_status'] = array($wpbe_filter['post_status']);
        }


        //comment_status
        if (isset($wpbe_filter['comment_status']) AND intval($wpbe_filter['comment_status']) !== -1) {
            $args['comment_status'] = $wpbe_filter['comment_status'];
        }

        //ping_status
        if (isset($wpbe_filter['ping_status']) AND intval($wpbe_filter['ping_status']) !== -1) {
            $args['ping_status'] = $wpbe_filter['ping_status'];
        }


        //post_mime_type
        if (isset($wpbe_filter['post_mime_type']) AND intval($wpbe_filter['post_mime_type']) !== -1) {
            $args['post_mime_type'] = [$wpbe_filter['post_mime_type']];
        }
		//thumbnail
		if (isset($wpbe_filter['_thumbnail_id']) AND intval($wpbe_filter['_thumbnail_id']) !== -1) {
			if ($wpbe_filter['_thumbnail_id'] == 'not_empty') {
				$meta_query[]=array(
					'key' => '_thumbnail_id',
					'compare' => 'EXISTS'
				);
			} else {
				$meta_query[]=array(
					'key' => '_thumbnail_id',
					'compare' => 'NOT EXISTS'
				);
				
			}

		}

        //sticky_posts
        if (isset($wpbe_filter['sticky_posts']) AND intval($wpbe_filter['sticky_posts']) !== -1) {
            $args['ignore_sticky_posts'] = 0;

            $mk = 'sticky_posts';
            //WPML compatibility
            $lang = apply_filters('wpbe_current_language', '');
            if (!empty($lang)) {
                $mk .= '_' . $lang;
            }

            if (intval($wpbe_filter['sticky_posts']) === 1) {
                if (isset($args['post__in']) AND ! empty($args['post__in'])) {
                    $args['post__in'] = array_merge($args['post__in'], get_option($mk));
                } else {
                    $args['post__in'] = get_option($mk);
                }
            } else {
                if (isset($args['post__not_in']) AND ! empty($args['post__not_in'])) {
                    $args['post__not_in'] = array_merge($args['post__not_in'], get_option($mk));
                } else {
                    $args['post__not_in'] = get_option($mk);
                }
            }
        }

        //***

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
        }

        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
        }

        //***
        $args['tax_query'] = $tax_query;
        $args['meta_query'] = $meta_query;

        return $args;
    }
    
    public function wpbe_tools_panel_buttons_end() {
        global $WPBE;
        ?>
        &nbsp;|&nbsp;<span>
            <?php
            $behavior_options = array(
                'like' => __('LIKE', 'bulk-editor'),
                'exact' => __('EXACT', 'bulk-editor'),
                'not' => __('NOT', 'bulk-editor'),
                'begin' => __('BEGIN', 'bulk-editor'),
                'end' => __('END', 'bulk-editor'),
            );                    
            
            $search_options = apply_filters('wpbe_quick_search_options', array(
                'post_title' => __('Title', 'bulk-editor'),
                'post__in' => __('ID', 'bulk-editor'),
            ));
            
            $fields = $this->settings->quick_search_fieds;
            if ($fields) {
                $fields = WPBE_HELPER::string_to_array($fields);
                $search_options = array_merge($search_options, $fields);
            }                    
                    
                    
            ?>

                <div class='tools_panel_filter-unit-wrap'>
                    <div class="col-lg-6">

                        <div style="padding-right: 2px;">
                            <input type="text" placeholder="<?php _e('quick search by ...', 'bulk-editor') ?>" name="wpbe_filter_form_tools_value" value="" />
                        </div>
                        
                    </div>
                    <div class="col-lg-2">

                        <select name="wpbe_filter_tools_options" class="wpbe_filter_tools_select">
                            <?php foreach ($search_options as $key => $title) : ?>
                                <option value="<?php echo $key ?>"><?php echo $title ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>                              
                    <div class="col-lg-2">

                        <select name="wpbe_filter_tools_behavior">
                            <?php foreach ($behavior_options as $key => $title) : ?>
                                <option value="<?php echo $key ?>"><?php echo $title ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>

                    <div class="col-lg-2">
                        <a href="#" class="button button-primary button-large" id="wpbe_filter_btn_tools_panel"></a>
                    </div>    
                    <div style="clear: both;"></div>

                </div>

        </span>
        <?php
    }
}
