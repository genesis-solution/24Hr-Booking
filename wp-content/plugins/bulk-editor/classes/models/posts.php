<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_POSTS {

    private $fields_keys = NULL;
    private $settings = NULL;
    private $storage = NULL;
    public $suppress_filters = false; //for example while do wpbe_title_autocomplete we not need to apply another filters
    public $cached_posts = array(); //posts caching - for 400 percents quicker!!

    public function __construct($settings, $storage) {
        $this->settings = $settings;
        $this->storage = $storage;
        $this->fields_keys = $this->settings->get_fields_keys();
    }

    public function gets($args) {

        if (!empty($args['search'])) {
            //this textinput is hidden to avoid customers confucsing, doesn work, hidden
            /*
              $_REQUEST['wpbe_txt_search'] = $args['search'];
              $_REQUEST['wpbe_sku_search'] = $args['search'];
              add_filter('posts_where', array($this, 'posts_txt_where'), 101);
              add_filter('posts_where', array($this, 'posts_sku_where'), 102);
             *
             */
        }

        //***
        //get_comments - under todo and not done yet
        if (!isset($args['get_comments'])) {

            if (isset($args['order_by'])) {
                $order_by = sanitize_key($args['order_by']);
                if ($order_by === 'id') {
                    $order_by = 'ID';
                }
            } else {
                $order_by = 'ID';
            }

            //***
            //fix to avoid notice when seacrhing going
            if (!isset($args['offset'])) {
                $args['offset'] = 0;
            }

            $pr = array(
                'post_type' => $this->settings->current_post_type,
                'post_status' => isset($args['post_status']) ? $args['post_status'] : array_keys(WPBE_HELPER::get_post_statuses()),
                'orderby' => $order_by,
                'order' => isset($args['order']) ? sanitize_key($args['order']) : 'asc',
                'posts_per_page' => isset($args['per_page']) ? intval($args['per_page']) : 10,
                'paged' => isset($args['per_page']) ? intval(($args['offset'] / $args['per_page']) + 1) : 1
            );

            if ($pr['post_type'] === 'attachment') {
                $pr['post_status'][] = 'inherit';
            }

            //get one post data

            if (isset($args['p'])) {
                $pr['p'] = $args['p'];
            }

            //***

            if (isset($args['nopaging'])) {
                $pr['nopaging'] = $args['nopaging'];
            }

            if (isset($args['max_num_pages'])) {
                $pr['max_num_pages'] = $args['max_num_pages'];
            }

            if (isset($args['post__not_in'])) {
                $pr['post__not_in'] = $args['post__not_in'];
            }

            //***
            //for bulk get post count
            if (isset($args['fields'])) {
                $pr['fields'] = $args['fields'];
            }

            if (isset($args['no_found_rows'])) {
                $pr['no_found_rows'] = $args['no_found_rows'];
                $pr['posts_per_page'] = -1;
                unset($pr['paged']);
            }

            //***

            if (isset($this->settings->get_fields()[$order_by]) AND $this->settings->get_fields()[$order_by]['field_type'] === 'meta') {
                $pr['meta_key'] = $order_by;
                if (in_array($this->settings->get_fields()[$order_by]['type'], array('number', 'timestamp', 'unix'))) {
                    $pr['orderby'] = 'meta_value_num';
                } else {
                    $pr['orderby'] = 'meta_value';
                }
            }

            //WPML compatibility
            if (class_exists('SitePress') AND isset($args['lang'])) {
                //https://wpml.org/forums/topic/passing-language-to-wp_query-2/
                global $sitepress;
                $sitepress->switch_lang($args['lang']);
            }
        } else {

            //here place leaved for comments maybe
        }

        //***

        if (!$this->suppress_filters) {
            $pr = apply_filters('wpbe_apply_query_filter_data', $pr);
        }

        if (empty($pr['tax_query'])) {
            unset($pr['tax_query']);
        }

        if (empty($pr['meta_query'])) {
            unset($pr['meta_query']);
        }

        $pr['lang'] = apply_filters('wpbe_current_language', '');

        return new WP_Query($pr);
    }

    public function update_page_field($post_id, $field_key, $value, $field_type = '') {

        if (!$post_id) {
            return FALSE;
        }

        $fields = $this->settings->get_fields();

        //***
        //lets check if current user is not administrator and is he can edit this field
        if (!$this->is_current_user_can_edit_field($field_key)) {
            return esc_html__('forbidden', 'bulk-editor');
        }

        //***
        $value = apply_filters('wpbe_before_update_post_field', $value, $post_id, $field_key);

        $post = $this->get_post($post_id);

        $answer = '';
        if (empty($field_type)) {
            $field_type = $fields[$field_key]['field_type'];
        }

//***

        do_action('wpbe_before_update_page_field', $field_key, $post_id, 0);

//***
        //should be clean text with commas only
        if (in_array($field_key, ['to_ping', 'pinged'])) {
            $value = trim(strip_tags($value));
        }

        /*
          if ($field_key == "attribute_visibility") {
          $field_type = 'meta';
          }
         *
         */

//***

        if (isset($fields[$field_key]['sanitize']) AND $fields[$field_key]['sanitize'] == 'array' AND!empty($value)) {
            $value = WPBE_HELPER::string_to_array($value); //in db its keeps as array, so lets conver it
        }

        if (isset($_REQUEST['num_rounding']) AND $_REQUEST['num_rounding'] > 0 AND $fields[$field_key]['type'] === 'number') {
            if (isset($fields[$field_key]['sanitize']) AND $fields[$field_key]['sanitize'] === 'floatval') {
                $round_to = intval($_REQUEST['num_rounding']);
                $div = intval('1' . str_repeat('0', $this->settings->nums_round_decimals));

                switch ($round_to) {
                    case 5:
                    case 10:

                        $value += floatval($round_to / $div);
                        $cents = floatval($value - floor($value));
                        $f = intval(($cents * $div) . ''); //float num fix, another way wrong results
                        $fraction = floatval($f % $round_to) / $div;
                        $value = floor($value) + ($cents - $fraction);

                        break;

                    case 9:
                    case 19:
                    case 29:
                    case 39:
                    case 49:
                    case 59:
                    case 69:
                    case 79:
                    case 89:
                    case 99:

                        $value = intval($value) + floatval($round_to / $div);

                    default:
                        break;
                }
            }
        }

//***

        if ($fields[$field_key]['type'] === 'number' AND isset($fields[$field_key]['sanitize']) AND $fields[$field_key]['sanitize'] === 'floatval') {
            $value = apply_filters('wpbe_number_field_manipulation', $value, $field_key, $post_id);
        }


//***
//echo $field_type . ' + ' . $value . ' + ';
        switch ($field_type) {
            case 'meta':
                if ($field_key === 'sticky_posts') {

                    $mk = 'sticky_posts';
                    //WPML compatibility
                    $lang = apply_filters('wpbe_current_language', '');
                    if (!empty($lang)) {
                        $mk .= '_' . $lang;
                    }


                    $meta = get_option($mk, []);

                    if (intval($value) === 1) {
                        $meta[] = $post_id;
                    } else {

                        if (!empty($meta)) {
                            foreach ($meta as $k => $pid) {
                                if (intval($pid) === intval($post_id)) {
                                    unset($meta[$k]);
                                    break;
                                }
                            }
                        }
                    }

                    update_option($mk, $meta);
                    break;
                }
                if ('_thumbnail_id' === $field_key) {
                    set_post_thumbnail($post_id, $value);
                    break;
                }
                if (isset($_REQUEST['is_serialized']) AND $_REQUEST['is_serialized']) {
                    parse_str($value, $value); //for serialized meta data saving
                }

                if ($fields[$field_key]['edit_view'] == 'meta_popup_editor') {
                    $value = $this->__process_jsoned_meta_data($value);
                    $answer = json_encode($value, JSON_HEX_QUOT | JSON_HEX_TAG);
                } else {
                    $answer = $value;
                }
                if ($fields[$field_key]['edit_view'] == 'switcher') { //do switcher
                        $value = WPBE_HELPER::over_switcher_swicher_to_val($value, $field_key);
                }
                update_post_meta($post_id, $field_key, $value);
                $this->__call_hooks_after_post_update($post_id);
                break;

            case 'taxonomy':

                if ($fields[$field_key]['type'] === 'array') {
                    if (!is_array($value)) {
                        $value = array(intval($value));
                    } else {
                        foreach ($value as $k => $tid) {
                            $value[$k] = intval($tid);
                        }
                    }
                } else {
                    //string, do nothing
                }

                wp_set_post_terms($post_id, $value, $fields[$field_key]['taxonomy'], false);
                $this->__call_hooks_after_post_update($post_id);

                break;

            case 'gallery_popup_editor':

                update_post_meta($post_id, $field_key, $value);

                $answer = WPBE_HELPER::render_html(WPBE_PATH . 'views/elements/draw_gallery_popup_editor_btn.php', array(
                            'field_key' => $field_key,
                            'post_id' => $post_id,
                            'images' => $value
                ));

                break;

            default:

                wp_update_post(array(
                    'ID' => $post_id,
                    $field_key => $value
                ));

                $answer = get_post_field($field_key, $post_id);

                $this->__call_hooks_after_post_update($post_id);

                break;
        }

//***

        if ($fields[$field_key]['edit_view'] == 'textinput') {
            $answer = $this->__sanitize_answer_value($field_key, (isset($fields[$field_key]['sanitize']) ? $fields[$field_key]['sanitize'] : ''), $answer);
        }

//***
//FOR ANY FLEXIBLE COMPATIBILITY
        do_action('wpbe_after_update_page_field', $post_id, $post, $field_key, $value, $field_type);

        //update posts cache
        $this->cached_posts[$post_id] = $post;

        return $answer;
    }

//util
    public function __call_hooks_after_post_update($post_id) {
        $pp = get_post($post_id);
        do_action('save_post', $post_id, $pp, true);
        do_action('edit_post', $post_id, $pp);
    }

//service
    public function __sanitize_answer_value($field_key, $sanitize, $val) {

        $res = $val;

        switch ($sanitize) {
            case 'sanitize_key':
                $res = sanitize_key($val);
                break;
            case 'esc_url':
                $res = esc_url($val);
                break;
            case 'urldecode':
                $res = urldecode($val);
                break;
            case 'floatval':
                /*
                  $val = str_replace(',', '.', $val);
                  $val = str_replace(' ', '', $val);

                  $res = number_format(floatval($val), $this->settings->nums_round_decimals);
                 *
                 */

                $res = floatval($val);

                break;
            case 'intval':
                $res = intval($val);
                break;

            case 'array':
                if (is_array($val) AND!empty($val)) {
                    $res = WPBE_HELPER::array_to_string($val);
                }
                break;
        }

        return $res;
    }

//++++++++++++++++++++++++++++++++++++++++

    public function get_post_field($post_id, $field_key, $post_parent = 0) {
        if (!$post_id) {
            return FALSE;
        }

//***
        $res = '';
        $field_type = $this->settings->get_fields()[$field_key]['field_type'];

//fix for description of one variation - as an example or tricks
        if ($field_key == 'post_content' AND $post_parent > 0) {
            //as example for manipulations
        }

//***

        switch ($field_type) {
            case 'meta':
                if ($field_key === 'sticky_posts') {

                    $mk = 'sticky_posts';
                    //WPML compatibility
                    $lang = apply_filters('wpbe_current_language', '');
                    if (!empty($lang)) {
                        $mk .= '_' . $lang;
                    }

                    $m = get_option($mk, []);
                    $res = intval(in_array($post_id, $m));
                } else {
                    $res = get_post_meta($post_id, $field_key, true);
                }
                break;

            case 'field':
                $res = get_post_field($field_key, $post_id);
                break;

            case 'taxonomy':
                $res = wp_get_post_terms($post_id, $this->settings->get_fields()[$field_key]['taxonomy'], array(
                    //'fields' => 'ids',
                    'hide_empty' => false,
                ));

                break;
        }

        return $res;
    }

    public function get_post($post_id) {

        if (!isset($this->cached_posts[$post_id])) {
            $this->cached_posts[$post_id] = get_post($post_id, ARRAY_A);
        }

        return $this->cached_posts[$post_id];
    }

    public function normalize_calendar_date($value, $field_key) {
        if ($value != 0) {//if not clearing
            $value = explode('-', $value);

            if (isset($this->settings->active_fields[$field_key]['set_day_end']) AND $this->settings->active_fields[$field_key]['set_day_end']) {
                $value = mktime(23, 59, 59, $value[1], $value[2], $value[0]);
            } else {
                $value = mktime($value[3], $value[4], 0, $value[1], $value[2], $value[0]);
            }

//***
            //should be attended in process/later
            //$gmt_offset = intval(get_option('gmt_offset')) * 3600;
            //$value += $gmt_offset;
//***

            if ($this->settings->active_fields[$field_key]['type'] === 'timestamp'
                    AND $this->settings->active_fields[$field_key]['field_type'] === 'field') {
                $date = new DateTime();
                $date->setTimestamp($value);
                /*
                  if ($this->settings->active_fields[$field_key]['set_day_end']) {
                  $value = $date->format('Y-m-d 23:59:59');
                  } else {
                  $value = $date->format('Y-m-d 00:00:00');
                  }
                 *
                 */

                $value = $date->format('Y-m-d H:i:s');
            }
        }

        return $value;
    }

    public function get_attributes($post_id, $type = "all") {
        return [];
    }

//service
    public function __string_replacer($val, $post_id) {

        if (is_string($val)) {
            if (stripos($val, '{TITLE}') !== false) {
                $val = str_ireplace('{TITLE}', $this->get_post_field($post_id, 'post_title'), $val);
            }

            if (stripos($val, '{ID}') !== false) {
                $val = str_ireplace('{ID}', $post_id, $val);
            }



//***


            if (stripos($val, '{MENU_ORDER}') !== false) {
                $val = str_ireplace('{MENU_ORDER}', $this->get_post_field($post_id, 'menu_order'), $val);
            }


//***

            if (stripos($val, '{meta:') !== false) {
//syntax {meta:_woocs_regular_price_USD} - lets take data from metafield
                $val = explode(':', $val);
                $val = $this->get_post_field($post_id, trim($val[1], ' }'));
            }
        }

        return apply_filters('wpbe_apply_string_replacer', $val, $post_id);
    }
	public function string_macros($val, $field_key, $post_id) {
		if (!is_string($val)) {
			return $val;
		}		
		$original_val = $this->get_post_field($post_id, $field_key);
		
		if (is_string($original_val)) {
            if (stripos($val, '{DO_STRING_UP}') !== false) {
                $val = str_ireplace('{DO_STRING_UP}', mb_strtoupper($original_val), $val);
            }
            if (stripos($val, '{DO_STRING_DOWN}') !== false) {
                $val = str_ireplace('{DO_STRING_DOWN}', mb_strtolower($original_val), $val);
            }
            if (stripos($val, '{DO_STRING_TITLE}') !== false) {
                $val = str_ireplace('{DO_STRING_TITLE}', mb_convert_case($original_val, MB_CASE_TITLE, "UTF-8"), $val);
            }
            if (stripos($val, '{DO_STRING_UP_FIRST}') !== false) {
				$fc = mb_strtoupper(mb_substr($original_val, 0, 1));
				$original_val = $fc . mb_substr($original_val, 1);				
                $val = str_ireplace('{DO_STRING_UP_FIRST}', $original_val, $val);
            }			
			
			 
		}
		return $val;
	}
    public function is_current_user_can_edit_field($field_key) {

        if (!in_array($this->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
            $site_editor_visibility = $this->settings->get_site_editor_visibility();
            $user_can = apply_filters('wpbe_user_can_edit', $site_editor_visibility[$field_key], $field_key, $site_editor_visibility);
            if (!intval($user_can)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    //service function to process serialized meta data
    public function __process_jsoned_meta_data($raw_data) {
        $result = array();

        //***
        //for js arrays
        if (isset($raw_data['keys']) AND!empty($raw_data['keys'])) {
            $tmp = array();
            foreach ($raw_data['keys'] as $kk => $kv) {
                if (!is_null($kv)) {
                    if (!is_array($kv) AND isset($raw_data['keys'][$kv]) AND is_array($raw_data['keys'][$kv])) {
                        if (!empty($raw_data['keys'][$kv])) {
                            $tmp[WPBE_HELPER::prepare_meta_keys($kv)] = array();
                            foreach ($raw_data['keys'][$kv] as $kkk => $vvv) {
                                if (isset($raw_data['values'][$kv][$kkk])) {
                                    $tmp[WPBE_HELPER::prepare_meta_keys($kv)][WPBE_HELPER::prepare_meta_keys($vvv)] = $raw_data['values'][$kv][$kkk];
                                }
                            }
                        }
                    } else {
                        if (!is_array($kv)) {
                            if (isset($raw_data['values'][WPBE_HELPER::prepare_meta_keys($kk)])) {
                                $tmp[WPBE_HELPER::prepare_meta_keys($kv)] = $raw_data['values'][WPBE_HELPER::prepare_meta_keys($kk)];
                            }
                        }
                    }
                }
            }

            $result = $tmp;
        }


        //***
        //for js objects
        if (isset($raw_data['keys2']) AND!empty($raw_data['keys2'])) {
            $tmp = array();
            foreach ($raw_data['keys2'] as $k => $keys) {
                if (!empty($keys)) {
                    $o = array();

                    foreach ($keys as $kk => $key) {
                        if ($this->is_json($raw_data['values2'][$k][$kk])) {
                            $o[$key] = json_decode($raw_data['values2'][$k][$kk], ARRAY_A);
                        } else {
                            $o[$key] = $raw_data['values2'][$k][$kk];
                        }
                    }

                    $tmp[$k] = $o;
                }
            }

            $result = array_merge($result, $tmp);
        }

        //***
        //if meta value is just string or number
        if (empty($result)) {
            $result = $raw_data;
        }

        //***

        return $result;
    }

    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}
