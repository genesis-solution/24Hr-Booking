<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//CRUD - filters sets profiles
class WPBE_FILTER_PROFILES extends WPBE_PROFILES {

    protected $option_key = 'wpbe_filter_profiles_';
    protected $non_deletable_profiles = array('default');
    protected $create_profile_ajax_action = 'wpbe_create_filter_profile';
    protected $load_profile_ajax_action = 'wpbe_load_filter_profile';
    protected $delete_profile_ajax_action = 'wpbe_delete_filter_profile';

    public function __construct($settings) {
        parent::__construct(new WPBE_SETTINGS());
        add_action('wp_ajax_wpbe_get_filter_profile_data', array($this, 'get_filter_profile_data'), 1);
    }

   protected function init_constructor_data() {
        /*
        if (!$this->get()) {
            //***
        }
         * 
         */
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //ajax
    public function load_profile() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
		if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            die('0');
        }
        $profile = $this->get($_REQUEST['profile_key']);

        if (!empty($profile)) {
            if (isset($profile['data']) AND ! empty($profile['data'])) {
                $this->storage->set_val('wpbe_filter_' . $_REQUEST['profile_key'], $profile['data']);
            } else {
                wp_die('-1');
            }
        } else {
            wp_die('-1');
        }

        wp_die('1');
    }

    //ajax
    public function create_profile() {

        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
		if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            die('0');
        }
        $profile_title = trim(htmlentities(sanitize_text_field($_REQUEST['profile_title']), ENT_NOQUOTES));

        if (!empty($profile_title)) {

            $filter_current_key = sanitize_text_field($_REQUEST['filter_current_key']);

            if (!empty($profile_title) AND ! empty($filter_current_key)) {
                echo $this->create($this->storage->get_val('wpbe_filter_' . $filter_current_key), $profile_title, $filter_current_key);
            }
        }

        exit;
    }

    //ajax
    public function get_filter_profile_data() {
        $res = array();
        $res['taxonomies'] = array();
        $res['taxonomies_operators'] = array();
        $res['taxonomies_terms_titles'] = array();
        $profile = $this->get($_REQUEST['profile_key']);
		if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            die('0');
        }
        if (!empty($profile['data'])) {
            foreach ($profile['data'] as $key => $value) {

                if (in_array($key, array('taxonomies_operators', 'tax_query', 'meta_query'))) {
                    continue;
                }

                //***

                if ($key == 'taxonomies') {

                    if (!empty($value)) {
                        foreach ($value as $tax_key => $terms) {
                            $res['taxonomies'][$tax_key] = $terms;
                            if (!empty($terms)) {
                                foreach ($terms as $term_id) {
                                    $term = get_term_by('id', $term_id, $tax_key);
                                    $res['taxonomies_terms_titles'][$term_id] = $term->name;
                                }
                            }
                            $res['taxonomies_operators'][$tax_key] = $profile['data']['taxonomies_operators'][$tax_key];
                        }
                    }

                    continue;
                }

                //***

                if (is_array($value)) {
                    if (isset($value['value']) AND ! empty($value['value'])) {
                        $res[$key]['value'] = $value['value'];
                        $res[$key]['behavior'] = $value['behavior'];
                    }

                    if (isset($value['from']) AND ! empty($value['from'])) {
                        $res[$key]['from'] = $value['from'];
                    }

                    if (isset($value['to']) AND ! empty($value['to'])) {
                        $res[$key]['to'] = $value['to'];
                    }
                } else {
                    if (!empty($value) AND intval($value) !== -1) {
                        /* check it in time
                          if ($this->settings->get_fields(false)[$key]['edit_view'] == 'calendar') {
                          $posts = new WPBE_POSTS($this->settings, $this->storage);
                          $value = $posts->normalize_calendar_date($value, $key);
                          }
                         */

                        $res[$key] = $value;
                    }
                }
            }
        }

        //***

        $html = '';

        if (!empty($res)) {
            foreach ($res as $key => $value) {

                if (in_array($key, array('taxonomies_operators', 'taxonomies_terms_titles'))) {
                    continue;
                }

                //***

                if ($key == 'taxonomies') {
                    if (!empty($value)) {
                        foreach ($value as $tax_key => $terms) {
                            if (!empty($terms)) {
                                foreach ($terms as $term_id) {
                                    $html .= '<li>' . $res['taxonomies_terms_titles'][$term_id] . ' (<i>' . $res['taxonomies_operators'][$tax_key] . '</i>)' . '</li>';
                                }
                            }
                        }
                    }

                    continue;
                }


                //***

                if (is_array($value)) {
                    if (isset($value['value'])) {
                        $html .= '<li><b>' . $key . '</b>: <i>' . $value['value'] . '</i> (' . $value['behavior'] . ')</li>';
                    } else {
                        $tmp = array(
                            'from' => '-',
                            'to' => '-'
                        );
                        if (isset($value['from'])) {
                            $tmp['from'] = $value['from'];
                        }

                        if (isset($value['to'])) {
                            $tmp['to'] = $value['to'];
                        }

                        $html .= '<li><b>' . $key . '</b>: <i>' . $tmp['from'] . ' - ' . $tmp['to'] . '</i></li>';
                    }
                } else {
                    $html .= '<li><b>' . $key . '</b>: <i>' . $value . '</i></li>';
                }
            }
        }

        //***

        $answer = array(
            'res' => $res,
            'html' => $html
        );

        wp_die(json_encode($answer));
    }

}
