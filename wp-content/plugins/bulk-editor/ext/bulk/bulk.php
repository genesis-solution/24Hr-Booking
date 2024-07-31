<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_BULK extends WPBE_EXT {

    protected $slug = 'bulk'; //unique
    public $text_keys = array();
    public $num_keys = array();
    public $other_keys = array();

    public function __construct() {

        $this->init_bulk_keys();

        add_action('wpbe_ext_scripts', array($this, 'wpbe_ext_scripts'), 1);
        add_action('wpbe_tools_panel_buttons_end', array($this, 'wpbe_tools_panel_buttons_end'), 1);

        //ajax
        add_action('wp_ajax_wpbe_bulk_posts_count', array($this, 'wpbe_bulk_posts_count'), 1);
        add_action('wp_ajax_wpbe_bulk_posts', array($this, 'wpbe_bulk_posts'), 1);
        add_action('wp_ajax_wpbe_bulk_finish', array($this, 'wpbe_bulk_finish'), 1);
        //add_action('wp_ajax_wpbe_bulk_draw_gallery_btn', array($this, 'wpbe_bulk_draw_gallery_btn'), 1);
        //add_action('wp_ajax_wpbe_bulk_draw_upsell_ids_btn', array($this, 'wpbe_bulk_draw_upsell_ids_btn'), 1);
        add_action('wp_ajax_wpbe_bulk_get_att_terms', array($this, 'wpbe_bulk_get_att_terms'), 1);

        add_action('wp_ajax_wpbe_bulk_delete_posts_count', array($this, 'wpbe_bulk_delete_posts_count'), 1);
        add_action('wp_ajax_wpbe_bulk_delete_posts', array($this, 'wpbe_bulk_delete_posts'), 1);

        add_action('wpbe_bulk_going', array($this, 'wpbe_bulk_going'), 10, 2);

        //tabs
        $this->add_tab($this->slug, 'top_panel', esc_html__('Bulk Edit', 'bulk-editor'), 'pencil');
        add_action('wpbe_ext_top_panel_' . $this->slug, array($this, 'wpbe_ext_panel'), 1);
    }

    public function wpbe_ext_scripts() {
        wp_enqueue_script('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/js/' . $this->slug . '.js', [], WPBE_VERSION);
        wp_enqueue_style('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/css/' . $this->slug . '.css', [], WPBE_VERSION);
        global $WPBE;
        ?>
        <script>
            lang.<?php echo $this->slug ?> = {};
            lang.<?php echo $this->slug ?>.want_to_bulk = "<?php esc_html_e('Will be edited next:', 'bulk-editor') ?>";
            lang.<?php echo $this->slug ?>.want_to_delete = "<?php printf(esc_html__('Sure? Delete [%s]?', 'bulk-editor'), $WPBE->settings->current_post_type) ?>";
            lang.<?php echo $this->slug ?>.deleting = "<?php esc_html_e('Bulk deleting', 'bulk-editor') ?>";
            lang.<?php echo $this->slug ?>.deleted = "<?php printf(esc_html__('[%s](s) deleted!', 'bulk-editor'), $WPBE->settings->current_post_type) ?>";
            lang.<?php echo $this->slug ?>.bulking = "<?php esc_html_e('Bulk editing', 'bulk-editor') ?> ...";
            lang.<?php echo $this->slug ?>.bulked = "<?php printf(esc_html__('[%s](s) edited! Table redrawing ...', 'bulk-editor'), $WPBE->settings->current_post_type) ?>";
            lang.<?php echo $this->slug ?>.bulked2 = "<?php printf(esc_html__('[%s](s) edited!', 'bulk-editor'), $WPBE->settings->current_post_type) ?>";
            lang.<?php echo $this->slug ?>.bulk_is_going = "<?php esc_html_e('ATTENTION: Bulk operation is going!', 'bulk-editor') ?>";
        </script>
        <?php
    }

    public function wpbe_tools_panel_buttons_end() {
        global $WPBE;
        ?>
        &nbsp;|&nbsp;<span>
            <?php echo WPBE_HELPER::draw_advanced_switcher(0, 'wpbe_bind_editing', '', array('true' => esc_html__('binded editing', 'bulk-editor'), 'false' => esc_html__('binded editing', 'bulk-editor')), array('true' => 1, 'false' => 0), 'js_check_wpbe_bind_editing', 'wpbe_bind_editing'); ?>

            <?php
            $bind_tooltip = '';
            if ($WPBE->show_notes) {
                $fields = $WPBE->settings->get_fields();
                if (!empty($fields)) {
                    $bind_tooltip = [];
                    foreach ($fields as $field_key => $f) {
                        if ($f['direct']) {
                            $t = strip_tags($f['title']);
                            if (!empty($t) AND $field_key != 'ID') {
                                $bind_tooltip[] = $t;
                            }
                        }
                    }

                    $bind_tooltip = sprintf(esc_html__('In FREE version of the plugin you can change only next fields: %s', 'bulk-editor'), implode(', ', $bind_tooltip));
                }
            }
            ?>

            <?php echo WPBE_HELPER::draw_tooltip(sprintf(esc_html__('In this mode to the all selected [%s](s) will be set the value of a [%s] field which been edited', 'bulk-editor'), $WPBE->settings->current_post_type, $WPBE->settings->current_post_type) . '. ' . $bind_tooltip) ?>

        </span>&nbsp;|&nbsp;<span>
            <?php
            echo WPBE_HELPER::draw_select(array(
                'options' => WPBE_HELPER::filter_post_types(),
                'selected' => $WPBE->settings->current_post_type,
                'is_multi' => true,
                'field' => '',
                'post_id' => 0,
                'id' => 'wpbe_post_type_selector',
                'class' => ''
            ));
            ?><?php echo WPBE_HELPER::draw_tooltip(esc_html__('Select post type you want to manage', 'bulk-editor')) ?>

        </span>
        <?php
    }

    public function wpbe_ext_panel() {
        $data = array();
        $data['site_editor_visibility'] = $this->settings->get_site_editor_visibility();
        $data['text_keys'] = $this->text_keys;
        $data['num_keys'] = $this->num_keys;
        $data['other_keys'] = $this->other_keys;
        $data['settings_fields'] = $this->settings->get_fields();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

    //ajax
    public function wpbe_bulk_posts_count() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }

        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }	
        //***

        $bulk_data = array();

        if (!isset($_REQUEST['wpbe_bind_editing'])) {
            parse_str($_REQUEST['bulk_data'], $bulk_data);
        } else {
            //binded editing operation works
            $value = $_REQUEST['val'];
            $field_key = $_REQUEST['field'];

            //***

            $bulk_data['wpbe_bulk'] = array(
                'is' => array(
                    $field_key => 1
                ),
                $field_key => array(
                    'value' => $value,
                    'behavior' => $_REQUEST['behavior']
                )
            );
        }


        $this->storage->set_val('wpbe_bulk_' . strtolower($_REQUEST['bulk_key']), $bulk_data['wpbe_bulk']);

        if (!isset($_REQUEST['no_filter'])) {
            //get count of filtered - doesn work if bulk for checked posts
            $posts = $this->posts->gets(array(
                'fields' => 'ids',
                'no_found_rows' => true
            ));
            echo json_encode($posts->posts);
        }

        //***

        do_action('wpbe_bulk_started', $_REQUEST['bulk_key']);

        exit;
    }

    public function wpbe_bulk_delete_posts_count() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }
        $bulk_data = array();

        if (!isset($_REQUEST['wpbe_bind_editing'])) {
            parse_str($_REQUEST['bulk_data'], $bulk_data);
        } else {
            //binded editing operation works
            $value = $_REQUEST['val'];
            $field_key = $_REQUEST['field'];

            //***

            $bulk_data['wpbe_bulk'] = array(
                'is' => array(
                    $field_key => 1
                ),
                $field_key => array(
                    'value' => $value,
                    'behavior' => $_REQUEST['behavior']
                )
            );
        }


        $this->storage->set_val('wpbe_bulk_' . strtolower($_REQUEST['bulk_key']), $bulk_data['wpbe_bulk']);

        if (!isset($_REQUEST['no_filter'])) {
            //get count of filtered - doesn work if bulk for checked posts
            $posts = $this->posts->gets(array(
                'fields' => 'ids',
                'no_found_rows' => true
            ));
            echo json_encode($posts->posts);
        }

        exit;
    }

    public function wpbe_bulk_delete_posts() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }		
        if (!isset($_REQUEST['posts_ids'])) {
            wp_die('0');
        }

        if (is_array($_REQUEST['posts_ids'])) {
            $posts_ids = $_REQUEST['posts_ids'];

            foreach ($posts_ids as $id) {
                wp_trash_post(intval($id));
            }
        } else {
            wp_die('0');
        }

        wp_die(json_encode($_REQUEST['posts_ids']));
        exit;
    }

    //ajax
    public function wpbe_bulk_posts() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }	
        if (!isset($_REQUEST['posts_ids'])) {
            wp_die('0');
        }

        //***

        $fields = $this->settings->get_fields();
        $wpbe_bulk = $this->storage->get_val('wpbe_bulk_' . strtolower($_REQUEST['bulk_key']));
        $_REQUEST['wpbe_bulk_key'] = $_REQUEST['bulk_key'];

        $is_variations_solo = intval($_REQUEST['wpbe_show_variations']);
        $posts_ids = $_REQUEST['posts_ids'];

        //***
        //as we want to change variations only but have ids of parents - lets get variations ids
        if ($is_variations_solo AND!empty($posts_ids)) {
            //removed
        }

        //***

        if (isset($wpbe_bulk['is']) AND!empty($wpbe_bulk['is']) AND!empty($posts_ids)) {

            //***

            foreach ($wpbe_bulk['is'] as $field_key => $is) {

                if ($fields[$field_key]['edit_view'] === 'calendar') {
                    $wpbe_bulk[$field_key]['value'] = $this->posts->normalize_calendar_date($wpbe_bulk[$field_key]['value'], $field_key);
                }

                //***
                //speedfix
                wp_defer_term_counting(false);
                wp_defer_comment_counting(true);

                //***

                if (intval($is) === 1) {

                    foreach ($posts_ids as $post_id) {

                        if ($is_variations_solo) {
                            //if enabled editing of variations only parent-posts are ignored
                            //leave it for comments
                        }

                        //***


                        switch ($field_key) {
                            case 'post_title':
                            case 'post_content':
                            case 'post_excerpt':
                            case 'post_name':
                            case 'post_password':
                            case 'to_ping':
                                $this->__process_text_data($wpbe_bulk, $field_key, $post_id);
                                break;
                            case 'post_status':
                            case 'gallery':
                            case 'post_date':
                            case 'post_date_gmt':
                            case 'post_modified':
                            case 'post_modified_gmt':
                            case 'sticky_posts':
                            case 'post_type':
                            case 'comment_status':
                            case 'ping_status':
                                if (intval($wpbe_bulk[$field_key]['value']) !== -1) {
                                    $this->posts->update_page_field($post_id, $field_key, $wpbe_bulk[$field_key]['value']);
                                    if ($field_key === 'sticky_posts') {
                                        usleep(333); //we need this freeze as sometimes updating of serialized sticky data not in time and overwriting wrong
                                    }
                                }
                                break;

                            case 'menu_order':
                            case 'post_author':
                            case 'post_parent':
                                $this->__process_number_data($wpbe_bulk, $field_key, $post_id);
                                break;

                            default:
                                break;
                        }

                        //***

                        if ($fields[$field_key]['field_type'] === 'taxonomy') {
                            //if (!empty($wpbe_bulk[$field_key]['value'])) {
                            do_action('wpbe_before_update_page_field', $field_key, $post_id, 0); //for the History
                            switch ($wpbe_bulk[$field_key]['behavior']) {
                                case 'append':
                                    if (is_taxonomy_hierarchical($field_key)) {
                                        wp_set_post_terms($post_id, $wpbe_bulk[$field_key]['value'], $field_key, true);
                                    } else {
                                        //post_tag for example
                                        foreach ($wpbe_bulk[$field_key]['value'] as $term_id) {
                                            $t = get_term_by('id', $term_id, $field_key);
                                            wp_set_post_terms($post_id, $t->slug, $field_key, true);
                                        }
                                    }
                                    break;
                                case 'replace':
                                case 'new':
                                    if (is_taxonomy_hierarchical($field_key)) {
                                        wp_set_post_terms($post_id, $wpbe_bulk[$field_key]['value'], $field_key, false);
                                    } else {
                                        //post_tag for example
                                        $append = false; //clean previous by first one then append
                                        if (!empty($wpbe_bulk[$field_key]['value']) AND is_array($wpbe_bulk[$field_key]['value'])) {
                                            foreach ($wpbe_bulk[$field_key]['value'] as $term_id) {
                                                $t = get_term_by('id', $term_id, $field_key);
                                                wp_set_post_terms($post_id, $t->slug, $field_key, $append);
                                                $append = true;
                                            }
                                        }
                                    }
                                    break;
                                case 'remove':
                                    foreach ($wpbe_bulk[$field_key]['value'] as $term_id) {
                                        $t = get_term_by('id', $term_id, $field_key);
                                        wp_remove_object_terms($post_id, $t->slug, $field_key);
                                    }
                                    break;
                            }
                            //}
                        }



                        //***

                        if ($fields[$field_key]['field_type'] === 'meta') {
                            switch ($fields[$field_key]['type']) {
                                case 'string':

                                    //if data is serialized in ine string
                                    if ($fields[$field_key]['edit_view'] == 'meta_popup_editor') {
                                        if (!is_array($wpbe_bulk[$field_key]['value'])) {

                                            //if not else parsed
                                            parse_str($wpbe_bulk[$field_key]['value'], $meta_val);

                                            $wpbe_bulk[$field_key]['value'] = $this->posts->__process_jsoned_meta_data($meta_val);
                                        }
                                    }

                                    //***

                                    if ($fields[$field_key]['edit_view'] == 'gallery_popup_editor') {

                                        if (!is_array($wpbe_bulk[$field_key]['value'])) {
                                            //if not else parsed
                                            parse_str($wpbe_bulk[$field_key]['value'], $meta_val);

                                            if (!empty($meta_val[$field_key])) {
                                                $wpbe_bulk[$field_key]['value'] = implode(',', $meta_val[$field_key]);
                                            }
                                        }
                                        
                                    }

                                    //***

                                    if ($fields[$field_key]['edit_view'] !== 'switcher') {
                                        $this->__process_text_data($wpbe_bulk, $field_key, $post_id);
                                    } else {
                                        if (intval($wpbe_bulk[$field_key]['value']) !== -1) {
                                            $this->posts->update_page_field($post_id, $field_key, intval($wpbe_bulk[$field_key]['value']));
                                        }
                                    }
                                    break;

                                case 'number':
                                    $this->__process_number_data($wpbe_bulk, $field_key, $post_id);
                                    break;

                                default:
                                    break;
                            }
                        }
                    }
                }
            }

            do_action('wpbe_bulk_going', $_REQUEST['wpbe_bulk_key'], count($posts_ids));
        }



        wp_die('done');
    }

    public function wpbe_bulk_going($bulk_key, $posts_count) {
        $count_key = 'wpbe_bulk_' . strtolower($bulk_key) . '_count';
        $count_now = intval($this->storage->get_val($count_key));
        $this->storage->set_val($count_key, $posts_count + $count_now);
    }

    private function __process_text_data($wpbe_bulk, $field_key, $post_id) {
        //if (!empty($wpbe_bulk[$field_key]['value'])) {
        $val = $this->posts->get_post_field($post_id, $field_key);
	$wpbe_bulk[$field_key]['value'] = $this->posts->string_macros($wpbe_bulk[$field_key]['value'], $field_key, $post_id);
        switch ($wpbe_bulk[$field_key]['behavior']) {
            case 'append':
                $val = $this->posts->__string_replacer($val . $wpbe_bulk[$field_key]['value'], $post_id);
                break;
            case 'prepend':
                $val = $this->posts->__string_replacer($wpbe_bulk[$field_key]['value'] . $val, $post_id);
                break;
            case 'new':
                $val = $this->posts->__string_replacer($wpbe_bulk[$field_key]['value'], $post_id);
                break;
            case 'replace':
                $replace_to = $this->posts->__string_replacer($wpbe_bulk[$field_key]['replace_to'], $post_id);
                $replace_from = $this->posts->__string_replacer($wpbe_bulk[$field_key]['value'], $post_id);

                if ($wpbe_bulk[$field_key]['case'] == 'ignore') {
                    $val = str_ireplace($replace_from, $replace_to, $val);
                } else {
                    $val = str_replace($replace_from, $replace_to, $val);
                }

                break;
        }

        //***
        $empty_exceptions = array('tax_class'); //setting empty values is possible with this fields

        $can = true; // !empty($val);

        if (in_array($field_key, $empty_exceptions)) {
            $can = true;
        }

        if ($can) {
            $val = $this->posts->update_page_field($post_id, $field_key, $val);
        }
        //}
    }

    private function __process_number_data($wpbe_bulk, $field_key, $post_id) {
        if ($wpbe_bulk[$field_key]['behavior'] != 'new') {
            $val = floatval($this->posts->get_post_field($post_id, $field_key));
        }

        //***

        switch ($wpbe_bulk[$field_key]['behavior']) {
            case 'new':
                $val = floatval($wpbe_bulk[$field_key]['value']);
                break;

            case 'invalue':
                $val += floatval($wpbe_bulk[$field_key]['value']);
                break;

            case 'devalue':
                $val -= floatval($wpbe_bulk[$field_key]['value']);
                break;

            case 'inpercent':
                $val = $val + $val * floatval($wpbe_bulk[$field_key]['value']) / 100;
                break;

            case 'depercent':
                $val = $val - $val * floatval($wpbe_bulk[$field_key]['value']) / 100;
                break;
        }
        if (isset($_REQUEST['num_formula_action']) AND isset($_REQUEST['num_formula_value']) AND $_REQUEST['num_formula_value'] != '-1') {
            $v_key = esc_textarea($_REQUEST['num_formula_value']);
            $action = esc_textarea($_REQUEST['num_formula_action']);
            $v_data = floatval(get_post_meta($post_id, $v_key, true));

            switch ($action) {
                case '-':
                    $val = $val - $v_data;
                    break;
                case '*':
                    $val = $val * $v_data;
                    break;
                case '/':
                    if ($v_data == 0) {
                        $v_data = 1;
                    }
                    $val = $val / $v_data;
                    break;

                default:
                    $val = $val + $v_data;
                    break;
            }
        }
		

		if (isset($_REQUEST['num_rand_data']) && is_array($_REQUEST['num_rand_data'])) {
			
			$rand_data = WPBE_HELPER::sanitize_array($_REQUEST['num_rand_data']);

			if (isset($rand_data['from']) && isset($rand_data['to']) && ($rand_data['from'] != $rand_data['to']) && ($rand_data['from'] < $rand_data['to'])) {
				$from = (float)$rand_data['from'];
				$to = (float)$rand_data['to'];
				$decimal = 1;
				if (isset($rand_data['decimal'])) {
					$decimal = (int)$rand_data['decimal'];
				}
				$action = '+';
				if (isset($rand_data['action'])) {
					$action = $rand_data['action'];
				}
				
				$rand_val = rand($from * $decimal, $to * $decimal)/$decimal;
				switch ($action) {
					case '-':
						$val = $val - $rand_val;
						break;
					case '*':
						$val = $val * $rand_val;
						break;
					case '/':
						if ($rand_val == 0) {
							$rand_val = 1;
						}
						$val = $val / $rand_val;
						break;

					default:
						$val = $val + $rand_val;
						break;
				}				
				
			}
		}		
        //***

        $convert = TRUE;

        //***

        if ($convert) {
            $val = $this->posts->update_page_field($post_id, $field_key, floatval($val));
        }
    }

    public function wpbe_bulk_finish() {
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }			
        do_action('wpbe_bulk_finished', $_REQUEST['bulk_key']);
        $count_key = 'wpbe_bulk_' . strtolower($_REQUEST['bulk_key']) . '_count';
        wp_die($this->storage->get_val($count_key) . '');
    }

    private function init_bulk_keys() {
        global $WPBE;
        $fields = wpbe_get_fields();

        $this->text_keys = array(
            'post_title' => array(
                'title' => esc_html__('title', 'bulk-editor'),
                'css_classes' => isset($fields['post_title']['css_classes']) ? $fields['post_title']['css_classes'] : ''
            ),
            'post_content' => array(
                'title' => esc_html__('content', 'bulk-editor'),
                'css_classes' => isset($fields['post_content']['css_classes']) ? $fields['post_content']['css_classes'] : ''
            ),
            'post_excerpt' => array(
                'title' => esc_html__('excerpt', 'bulk-editor'),
                'css_classes' => isset($fields['post_excerpt']['css_classes']) ? $fields['post_excerpt']['css_classes'] : ''
            ),
            'post_name' => array(
                'title' => esc_html__('slug', 'bulk-editor'),
                'css_classes' => isset($fields['post_name']['css_classes']) ? $fields['post_name']['css_classes'] : ''
            ),
            'post_password' => array(
                'title' => esc_html__('password', 'bulk-editor'),
                'css_classes' => isset($fields['post_password']['css_classes']) ? $fields['post_password']['css_classes'] : ''
            ),
            'to_ping' => array(
                'title' => esc_html__('send trackbacks to', 'bulk-editor'),
                'css_classes' => isset($fields['to_ping']['css_classes']) ? $fields['to_ping']['css_classes'] : ''
            )
        );

        $this->other_keys = array(
            'post_status' => array(
                'title' => esc_html__('post status', 'bulk-editor'),
                'options' => $fields['post_status']['select_options'],
                'direct' => $fields['post_status']['direct'],
                'css_classes' => isset($fields['post_status']['css_classes']) ? $fields['post_status']['css_classes'] : ''
            ),
            'post_type' => array(
                'title' => esc_html__('post type', 'bulk-editor'),
                'options' => $fields['post_type']['select_options'],
                'direct' => $fields['post_type']['direct'],
                'css_classes' => isset($fields['post_type']['css_classes']) ? $fields['post_type']['css_classes'] : ''
            ),
            'comment_status' => array(
                'title' => esc_html__('comment status', 'bulk-editor'),
                'options' => $fields['comment_status']['select_options'],
                'direct' => $fields['comment_status']['direct'],
                'css_classes' => isset($fields['comment_status']['css_classes']) ? $fields['comment_status']['css_classes'] : ''
            ),
            'ping_status' => array(
                'title' => esc_html__('ping status', 'bulk-editor'),
                'options' => $fields['ping_status']['select_options'],
                'direct' => $fields['ping_status']['direct'],
                'css_classes' => isset($fields['ping_status']['css_classes']) ? $fields['ping_status']['css_classes'] : ''
            )
        );

        //if (in_array($WPBE->settings->current_post_type, $fields['sticky_posts']['allow_post_types'])) {
        $this->other_keys['sticky_posts'] = array(
            'title' => esc_html__('sticky posts', 'bulk-editor'),
            'options' => $fields['sticky_posts']['select_options'],
            'direct' => $fields['sticky_posts']['direct'],
            'css_classes' => isset($fields['sticky_posts']['css_classes']) ? $fields['sticky_posts']['css_classes'] : ''
        );
        //}
        //***

        $options1 = array(
            'invalue' => esc_html__('increase by value', 'bulk-editor'),
            'devalue' => esc_html__('decrease by value', 'bulk-editor'),
            'inpercent' => esc_html__('increase by %', 'bulk-editor'),
            'depercent' => esc_html__('decrease by %', 'bulk-editor'),
            'new' => esc_html__('set new', 'bulk-editor')
        );

        $options2 = array(
            'invalue' => esc_html__('increase by value', 'bulk-editor'),
            'devalue' => esc_html__('decrease by value', 'bulk-editor'),
            'delete' => esc_html__('delete', 'bulk-editor'),
            'new' => esc_html__('set new', 'bulk-editor')
        );

        //***

        $this->num_keys = array(
            'post_parent' => array(
                'title' => esc_html__('post parent', 'bulk-editor'),
                'direct' => $fields['post_parent']['direct'],
                'options' => array(
                    'invalue' => esc_html__('increase by value', 'bulk-editor'),
                    'devalue' => esc_html__('decrease by value', 'bulk-editor'),
                    'inpercent' => esc_html__('increase by %', 'bulk-editor'),
                    'depercent' => esc_html__('decrease by %', 'bulk-editor'),
                    'new' => esc_html__('set new', 'bulk-editor')
                ),
                'css_classes' => isset($fields['post_parent']['css_classes']) ? $fields['post_parent']['css_classes'] : ''
            ),
            'menu_order' => array(
                'title' => esc_html__('menu order', 'bulk-editor'),
                'direct' => $fields['menu_order']['direct'],
                'options' => array(
                    'invalue' => esc_html__('increase by value', 'bulk-editor'),
                    'devalue' => esc_html__('decrease by value', 'bulk-editor'),
                    'new' => esc_html__('set new', 'bulk-editor')
                ),
                'css_classes' => isset($fields['menu_order']['css_classes']) ? $fields['menu_order']['css_classes'] : ''
            )
        );
    }

    //ajax
    public function wpbe_bulk_draw_gallery_btn() {
		 if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }	
        parse_str($_REQUEST['images'], $images);
        $data = array();
        $wpbe_gallery_images = isset($images['wpbe_gallery_images']) ? $images['wpbe_gallery_images'] : array();
        $data['html'] = WPBE_HELPER::draw_gallery_popup_editor_btn($_REQUEST['field'], 0, $wpbe_gallery_images);
        $data['images_ids'] = implode(',', $wpbe_gallery_images); //for any case, but now we not need it because updating of posts applies by serialized data

        wp_die(json_encode($data));
    }

}
