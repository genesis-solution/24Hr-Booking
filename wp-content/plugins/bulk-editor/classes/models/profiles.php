<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//CRUD - column sets profiles
class WPBE_PROFILES {

    protected $option_key = 'wpbe_profiles_';
    protected $settings = NULL;
    protected $storage = NULL;
    protected $non_deletable_profiles = array('default');
    protected $create_profile_ajax_action = 'wpbe_create_profile';
    protected $load_profile_ajax_action = 'wpbe_load_profile';
    protected $delete_profile_ajax_action = 'wpbe_delete_profile';

    public function __construct($settings) {
        $this->option_key .= get_current_user_id(); //we need do this to divide different users options set
        $this->settings = $settings;
        $this->storage = new WPBE_STORAGE();

        add_action('wp_ajax_' . $this->load_profile_ajax_action, array($this, 'load_profile'), 1);
        add_action('wp_ajax_' . $this->create_profile_ajax_action, array($this, 'create_profile'), 1);
        add_action('wp_ajax_' . $this->delete_profile_ajax_action, array($this, 'delete_profile'), 1);

        $this->init_constructor_data();
    }

    protected function init_constructor_data() {
        //hooks
        add_filter('wpbe_print_plugin_options', array($this, 'wpbe_print_plugin_options'), 1);
        add_action('wpbe_page_end', function() {
            ?>
            <script>
                var wpbe_non_deletable_profiles = ['<?php echo implode("','", $this->non_deletable_profiles) ?>'];
            </script>
            <?php
        }, 1);

        //***
        if (!$this->get()) {
            //lets create default profile after first plugin init after its intstallation
            $this->create(array(
                '__checker', 'ID', '_thumbnail_id', 'post_title', 'post_content', 'post_excerpt', 'post_status'
                    ), esc_html__('Default', 'bulk-editor'), 'default');
        }
    }

    public function create($data, $title, $key = '') {
        $profile_data = (array) $this->get();
        if (empty($key)) {
            $key = uniqid();
        }

        $profile_data[$key] = array(
            'title' => $title,
            'data' => $data
        );

        $this->update($profile_data);

        return $key;
    }

    public function get($key = '') {
        $profiles = get_option($this->option_key, array());

        if (isset($profiles[$key])) {
            return $profiles[$key];
        }

        return $profiles;
    }

    public function update($profile_data) {
        update_option($this->option_key, $profile_data);
    }

    public function delete($key) {

        if (in_array($key, $this->non_deletable_profiles)) {
            return false;
        }

        //***

        $profile_data = $this->get();
        unset($profile_data[$key]);
        $this->update($profile_data);

        return true;
    }

    public function get_current($data) {
        $profiles = $this->get();
        if (!empty($profiles)) {
            foreach ($profiles as $key => $p) {
                if ($p['data'] === $data AND count($data) === count($p['data'])) {
                    return array('key' => $key, 'title' => $p['title']);
                }
            }
        }

        return array();
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
        $profile = $this->get(sanitize_text_field($_REQUEST['profile_key']));

        if (!empty($profile)) {
            if (isset($profile['data']) AND ! empty($profile['data'])) {
                $options = $this->settings->get_options();
                if (isset($options['fields']) AND ! empty($options['fields'])) {

                    //set zero to all
                    foreach ($options['fields'] as $key => $o) {
                        $options['fields'][$key]['show'] = 0;
                    }

                    //collect columns from profile to set them on the top and ordered as they saved
                    $new_columns_fields_structure = array();
                    foreach ($profile['data'] as $cid) {
                        if (isset($options['fields'][$cid])) {
                            $new_columns_fields_structure[$cid] = $options['fields'][$cid];
                            $new_columns_fields_structure[$cid]['show'] = 1;
                        }
                    }

                    //remove columns from options which are in profile
                    foreach ($options['fields'] as $key => $o) {
                        if (isset($new_columns_fields_structure[$key])) {
                            unset($options['fields'][$key]);
                        }
                    }


                    $options['fields'] = array_merge($new_columns_fields_structure, $options['fields']);
                    $this->settings->update_options($options);
                }
            } else {
                echo -1;
            }
        } else {
            echo -1;
        }

        exit;
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
            $columns = array();

            foreach ($this->settings->get_fields(false) as $key => $f) {
                if (intval($f['show']) === 1) {
                    $columns[] = $key;
                }
            }

            echo $this->create($columns, $profile_title);
        }

        exit;
    }

    //ajax
    public function delete_profile() {

        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
		if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            die('0');
        }
        $this->delete(sanitize_text_field($_REQUEST['profile_key']));

        exit;
    }

    //hook
    public function wpbe_print_plugin_options($args) {
        $args['current_profile'] = $this->get_current(array_keys($this->settings->active_fields));
        return $args;
    }

}
