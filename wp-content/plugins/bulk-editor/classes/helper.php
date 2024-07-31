<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_HELPER {

    static $users = array();

    public static function draw_link($data) {
        $link = "<a href='{$data['href']}'";

        if (isset($data['class'])) {
            $link .= " class='{$data['class']}'";
        }

        if (isset($data['style'])) {
            $link .= " style='{$data['style']}'";
        }

        if (isset($data['id'])) {
            $link .= " id='{$data['id']}'";
        }

        if (isset($data['target'])) {
            $link .= " target='{$data['target']}'";
        }

        if (isset($data['title_attr'])) {
            $link .= " title='{$data['title_attr']}'";
        }

        if (isset($data['more'])) {
            $link .= " {$data['more']} ";
        }

        $link .= '>' . $data['title'] . '</a>';
        return $link;
    }

    public static function get_users() {

        if (empty(self::$users)) {
			$roles__in = [];
			foreach( wp_roles()->roles as $role_slug => $role )
			{
				if( ! empty( $role['capabilities']['publish_posts'] ) )
					$roles__in[] = $role_slug;
			}


            $users_arg = apply_filters('wpbe_users_args', array(
                'fields' => array('ID', 'display_name'),
               // 'who' => 'authors',
				'role__in' => $roles__in 
				)
            );			

            $users = get_users($users_arg);

            foreach ($users as $user) {
                self::$users[$user->ID] = $user->display_name;
            }
        }

        return self::$users;
    }

    public static function draw_select($data, $is_multi = false) {
        $multiple = '';
        if ($is_multi) {
            $multiple = 'multiple size=2';
        }

        //for filters
        $name = '';
        if (isset($data['name'])) {
            $name = "name='{$data['name']}'";
        }


        $disabled = '';
        if (isset($data['disabled']) AND $data['disabled']) {
            $disabled = "disabled=''";
        }

        if (isset($data['id']) AND $data['id']) {
            $id = $data['id'];
        } else {
            $id = "mselect_{$data['field']}_{$data['post_id']}";
        }

        //***

        $onchange = '';
        if (isset($data['onchange'])) {
            $onchange = "onchange='{$data['onchange']};'";
        }

        $onmouseover = '';
        if (isset($data['onmouseover'])) {
            $onmouseover = "onmouseover='{$data['onmouseover']};'";
        }

        //***
        $selected = '';
        if (isset($data['selected'])) {
            if (is_array($data['selected'])) {
                $selected = implode(',', $data['selected']);
            } else {
                $selected = $data['selected'];
            }
        }
		
        $select = "<div class='select-wrap'><select {$multiple} {$name} {$disabled} {$onchange} {$onmouseover} id='{$id}' data-field='{$data['field']}' data-post-id='{$data['post_id']}' data-placeholder=' ' data-selected='{$selected}' class='{$data['class']}'>";

        //***

        if (isset($data['options'])) {
            $in_selected = array();

            //***

            if (isset($data['selected'])) {
                if (is_array($data['selected'])) {
                    $in_selected = $data['selected'];
                } else {
                    $in_selected[] = $data['selected'];
                }
            }

            //***

            foreach ($data['options'] as $key => $title) {

                $selected = false;
                if (in_array($key, $in_selected)) {
                    $selected = TRUE;
                }
                $select .= '<option ' . selected($selected, TRUE, false) . " value='{$key}'>" . $title . '</option>';
            }
        }

        $select .= '</select></div>';
        return $select;
    }

    public static function draw_advanced_switcher($is, $numcheck, $name, $labels, $vals, $trigger_target = '', $css_classes = '') {
        return self::render_html(WPBE_PATH . 'views/elements/draw_advanced_switcher.php', array(
                    'is' => $is,
                    'numcheck' => $numcheck,
                    'name' => $name,
                    'labels' => $labels,
                    'vals' => $vals,
                    'trigger_target' => $trigger_target,
                    'css_classes' => $css_classes
        ));
    }

    public static function draw_calendar($post_id, $post_title, $field_key, $val, $name = '', $print_placeholder = false) {
        return self::render_html(WPBE_PATH . 'views/elements/draw_calendar.php', array(
                    'post_id' => $post_id,
                    'post_title' => $post_title,
                    'field_key' => $field_key,
                    'val' => $val,
                    'name' => $name,
                    'print_placeholder' => $print_placeholder
        ));
    }

    public static function draw_taxonomy_popup_btn($data, $tax_key, $post) {
        return self::render_html(WPBE_PATH . 'views/elements/draw_taxonomy_popup_btn.php', array(
                    'data' => $data,
                    'tax_key' => $tax_key,
                    'post' => $post
        ));
    }

    public static function draw_attribute_list_btn($terms, $selected_terms_ids, $tax_key, $post) {
        return self::render_html(WPBE_PATH . 'views/elements/draw_attribute_list_btn.php', array(
                    'terms' => $terms,
                    'selected_terms_ids' => $selected_terms_ids,
                    'tax_key' => $tax_key,
                    'post' => $post
        ));
    }

    public static function draw_popup_editor_btn($val, $field_key, $post) {
        return self::render_html(WPBE_PATH . 'views/elements/draw_popup_editor_btn.php', array(
                    'val' => $val,
                    'field_key' => $field_key,
                    'post' => $post
        ));
    }

    public static function draw_gallery_popup_editor_btn($field_key, $post_id, $images = array()) {
        return self::render_html(WPBE_PATH . 'views/elements/draw_gallery_popup_editor_btn.php', array(
                    'field_key' => $field_key,
                    'post_id' => $post_id,
                    'images' => $images
        ));
    }

    public static function draw_upsells_popup_editor_btn($field_key, $post_id, $ids = array()) {
        return self::render_html(WPBE_PATH . 'views/elements/draw_upsells_popup_editor_btn.php', array(
                    'field_key' => $field_key,
                    'post_id' => $post_id,
                    'ids' => $ids
        ));
    }

    public static function draw_meta_popup_editor_btn($field_key, $post_id, $btn_title = '') {
        return self::render_html(WPBE_PATH . 'views/elements/draw_meta_popup_editor_btn.php', array(
                    'field_key' => $field_key,
                    'post_id' => $post_id,
                    'btn_title' => $btn_title
        ));
    }

    public static function draw_tooltip($text, $direction = 'down') {
        ?>
        <a class="info_helper zebra_tips1" title="<?= $text ?>"><span class="icon-info"></span></a>
        <?php
    }

    public static function draw_restricked($text = '', $direction = 'right') {
        return self::render_html(WPBE_PATH . 'views/elements/draw_restricked.php', array(
                    'text' => $text,
                    'direction' => $direction
        ));
    }

    public static function draw_image($src, $alt = '', $class = '', $width = '') {
        return self::render_html(WPBE_PATH . 'views/elements/draw_image.php', array(
                    'src' => $src,
                    'alt' => $alt,
                    'class' => $class,
                    'width' => $width
        ));
    }

    public static function draw_checkbox($attributes = array(), $is_checked = false) {
        $ch = '<input type="checkbox" ';
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $ch .= $key . '=' . '"' . $value . '" ';
            }
        }

        if ($is_checked) {
            $ch .= 'checked ';
        }

        $ch .= '/>';
        return $ch;
    }

    public static function strtolower($string) {
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string, 'UTF-8');
        } else {
            $string = strtolower($string);
        }

        return $string;
    }

    public static function array_to_string($array) {
        $string = '';
        foreach ($array as $key => $value) {
            $string .= $key . ':' . $value . ',';
        }
        return trim($string, ',');
    }

    public static function string_to_array($string) {
        $res = array();
        $tmp = explode(',', $string);
        if (substr_count($string, ':') > 0) {
            //if indexes of array matter: 3:1,4:2,5:1 - index:value
            if (!empty($tmp)) {
                $vv = array();
                foreach ($tmp as $v) {
                    $v = explode(':', $v);
                    $vv[$v[0]] = $v[1];
                }
                $res = $vv;
            }
        } else {
            //1,2,5,7,12
            $res = $tmp;
        }

        return $res;
    }
	public static function over_switcher_swicher_to_val($val, $key) {
		global $WPBE;
		$switcher_values = $WPBE->settings->override_switcher_fieds;
		//$switcher_values = 'dist:yes,stest:true';
		$sw_array = explode(',', $switcher_values);
		foreach ($sw_array as $rule) {
			$rule_array = explode(':', $rule);
			if (count($rule_array) > 1 && $rule_array[0] == $key) {
				$values_array = explode('^', $rule_array[1]);
				if ($val == 1) {
					return $values_array[0];
				} elseif (count($values_array) > 1 && !$val) {
					return $values_array[1];
				}
			}
		}
		return $val;
	}	
	public static function over_switcher_val_to_swicher($val, $key) {
		global $WPBE;
		$switcher_values = $WPBE->settings->override_switcher_fieds;
		//$switcher_values = 'dist:yes,stest:true';
		$sw_array = explode(',', $switcher_values);
		foreach ($sw_array as $rule) {
			$rule_array = explode(':', $rule);
			if (count($rule_array) > 1 && $rule_array[0] == $key) {
				$values_array = explode('^', $rule_array[1]);
				if ($values_array[0] == $val ) {
					return 1;
				} else {
					return '';
				}
			}
		}
		return $val;
	}
    public static function get_taxonomies_terms_hierarchy($taxonomy) {

        $res = array();

        $object_terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        $data = array();

        if (!empty($object_terms)) {
            foreach ($object_terms as $term) {
                if (is_object($term)) {
                    $data[$term->parent][] = array(
                        'term_id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'desc' => $term->description,
                        //'parent' => $term->parent,
                        'childs' => array()
                    );
                }
            }

            //***

            $res = self::__sort_taxonomies_by_parents($data);
        }

        return $res;
    }

    private static function __sort_taxonomies_by_parents($data, $parent_id = 0) {
        if (isset($data[$parent_id])) {
            if (!empty($data[$parent_id])) {
                foreach ($data[$parent_id] as $key => $o) {
                    if (isset($data[$o['term_id']])) {
                        $data[$parent_id][$key]['childs'] = self::__sort_taxonomies_by_parents($data, $o['term_id']);
                    }
                }

                return $data[$parent_id];
            }
        }

        return array();
    }

    public static function prepare_meta_keys($key) {
        //return sanitize_title(trim($key));
        return trim($key);
    }

    public static function draw_rounding_drop_down() {
        ?>
        <select class="wpbe_num_rounding">
            <option value="0"><?php esc_html_e('no rounding', 'bulk-editor') ?></option>
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="9">9</option>
            <option value="19">19</option>
            <option value="29">29</option>
            <option value="39">39</option>
            <option value="49">49</option>
            <option value="59">59</option>
            <option value="69">69</option>
            <option value="79">79</option>
            <option value="89">89</option>
            <option value="99">99</option>
        </select>
        <?php
    }

    public static function render_html($pagepath, $data = array()) {

        if (is_array($data) AND!empty($data)) {
            if (isset($data['pagepath'])) {
                unset($data['pagepath']);
            }
            extract($data);
        }

        //***

        ob_start();
        include(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $pagepath));
        return ob_get_clean();
    }

    public static function get_post_statuses($use_filter = 1) {
        $statuses = get_post_statuses();

        if ($use_filter) {
            $statuses = apply_filters('wpbe_post_statuses', $statuses);
        }
        return $statuses;
    }

    public static function can_manage_data($user_id = 0) {

        if ($user_id === 0) {
            $user = wp_get_current_user();
        } else {
            $user = get_userdata($user_id);
        }

        $can = false;

        if (array_intersect(apply_filters('wpbe_permit_special_roles', ['administrator']), $user->roles) OR in_array('editor', $user->roles)) {
            $can = true;
        }
		if (array_intersect(apply_filters('wpbe_author_area_roles', []), $user->roles)) {
			$can = true;
		}
        return $can;
    }
    public static function sanitize_array($array) {
 
        if (!empty($array) AND is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = self::sanitize_array($value);
                } else {
                    $array[$key] = wp_kses($value, wp_kses_allowed_html('post'));
                }
            }
        }

        return $array;
    }
    //for site editors
    public static function filter_post_types() {
        global $WPBE;
        $args = apply_filters('wpbe_post_type_args', array('public' => true));

        $options = get_post_types($args);

        $user = wp_get_current_user();
        $role = (array) $user->roles;

        if (!in_array('administrator', $role)) {
            $se_allowed_post_types = explode(',', self::get_site_editors_post_types());

            if (!empty($se_allowed_post_types)) {
                foreach ($options as $pt) {
                    if (!in_array($pt, $se_allowed_post_types)) {
                        unset($options[$pt]);
                    }
                }
            }
        }

        return $options;
    }

    public static function get_show_text_editor() {
        return get_option('wpbe_show_text_editor', 0);
    }

    public static function get_site_separate_settings() {
        return get_option('wpbe_site_separate_settings', 0);
    }

    public static function get_site_editors_post_types() {
        return get_option('wpbe_site_editors_post_types', '');
    }
	public static function write_log($message){
		$path = WPBE_PATH . 'wpbe.log';
		$data_log = date("Y-m-d H:i:s") . " - " . $message . PHP_EOL;
		file_put_contents($path, $data_log, FILE_APPEND);
	}	
}
