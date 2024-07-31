<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_EXPORT extends WPBE_EXT {

    protected $slug = 'export'; //unique
    private $exlude_keys = array('__checker'); //do not export
    private $csv_delimiter = ',';

    public function __construct() {
        add_action('wpbe_ext_scripts', array($this, 'wpbe_ext_scripts'), 1);

        //ajax
        add_action('wp_ajax_wpbe_export_posts_count', array($this, 'wpbe_export_posts_count'), 1);
        add_action('wp_ajax_wpbe_export_posts', array($this, 'wpbe_export_posts'), 1);

        //tabs
        $this->add_tab($this->slug, 'top_panel', esc_html__('Export', 'bulk-editor'), 'export');
        add_action('wpbe_ext_top_panel_' . $this->slug, array($this, 'wpbe_ext_panel'), 1);
        
        $this->check_export_files();
    }

    public function wpbe_ext_scripts() {
        wp_enqueue_script('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/js/' . $this->slug . '.js', [], WPBE_VERSION);
        wp_enqueue_style('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/css/' . $this->slug . '.css', [], WPBE_VERSION);
        ?>
        <script>
            lang.<?php echo $this->slug ?> = {};
            lang.<?php echo $this->slug ?>.want_to_export = '<?php esc_html_e('Should the export be started?', 'bulk-editor') ?>';
            lang.<?php echo $this->slug ?>.exporting = '<?php esc_html_e('Exporting', 'bulk-editor') ?> ...';
            lang.<?php echo $this->slug ?>.exported = '<?php esc_html_e('Exported', 'bulk-editor') ?> ...';
            lang.<?php echo $this->slug ?>.export_is_going = "<?php esc_html_e('ATTENTION: Export operation is going!', 'bulk-editor') ?>";

        </script>
        <?php
    }

    public function wpbe_ext_panel() {
        $data = array();
        $data['download_link'] = $this->get_ext_link() . '__exported_files/';
        $data['download_link_xml'] = $this->get_ext_link() . '__exported_files/';
        $data['active_fields'] = $this->get_active_fields();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

    //ajax
    public function wpbe_export_posts_count() {
        if (!WPBE_HELPER::can_manage_data()) {
            return;
        }

        //***
        $active_fields = $this->get_active_fields();

        //***

        $this->csv_delimiter = $_REQUEST['csv_delimiter'];
        $file_postfix = sanitize_text_field($_REQUEST['file_postfix']);

        //***

        switch ($_REQUEST['format']) {
            case 'csv':

                if (!empty($active_fields)) {
                    $file_path = $this->get_ext_path() . "__exported_files/wpbe_exported{$file_postfix}.csv";
                    $fp = fopen($file_path, "w");
                    $titles = array();
                    $attribute_index = 1; //for attributes columns

                    foreach ($active_fields as $field_key => $field) {
                        if (!in_array($field_key, $this->exlude_keys)) {

                            switch ($field['field_type']) {
                                case 'meta':
                                    $titles[] = '"Meta: ' . $field_key . '"';
                                    break;

                                default:
                                    $titles[] = '"' . $field['title'] . '"'; //head titles
                                    break;
                            }
                        }
                    }

                    //***

                    $titles = implode($this->csv_delimiter, $titles);
                    fputs($fp, $titles . $this->csv_delimiter . PHP_EOL);
                    fclose($fp);
                }


                break;
            case 'xml':
                //die(json_encode($active_fields));
                if (!empty($active_fields)) {
                    $file_path = $this->get_ext_path() . "__exported_files/wpbe_exported{$file_postfix}.xml";
                    $dom = new domDocument("1.0", "utf-8");

                    $rss = $dom->createElementNS('http://wordpress.org/export/1.2/excerpt/', 'rss');
                    $dom->appendChild($rss);
                    $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wp', 'http://wordpress.org/export/1.2/');
                    $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
                    $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
                    $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:excerpt', 'http://wordpress.org/export/1.2/excerpt/');
                    $rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dc', 'http://purl.org/dc/elements/1.1/');

                    $root = $dom->createElement("channel");
                    $rss->appendChild($root);
                    /**/
                    $title = $dom->createElement("title", get_bloginfo('name'));
                    $root->appendChild($title);
                    $link = $dom->createElement('link', get_bloginfo('url'));
                    $root->appendChild($link);
                    $description = $dom->createElement('description', get_bloginfo('description'));
                    $root->appendChild($description);
                    $pubDate = $dom->createElement('pubDate', date('r'));
                    $root->appendChild($pubDate);


                    $author = $dom->createElement("wp:author");
                    $root->appendChild($author);
                    $current_user = wp_get_current_user();
                    $author_id = $dom->createElement('wp:author_id', $current_user->ID);
                    $author->appendChild($author_id);
                    $author_login = $dom->createElement('wp:author_login', $current_user->user_login);
                    $author->appendChild($author_login);
                    $author_email = $dom->createElement('wp:author_email', $current_user->user_email);
                    $author->appendChild($author_email);

                    $author_cdata = $dom->createCDATASection($current_user->display_name);
                    $author_display_name = $dom->createElement('wp:author_display_name');
                    $author_element = $author->appendChild($author_display_name);
                    $author_element->appendChild($author_cdata);

                    $author_cdata = $dom->createCDATASection($current_user->user_firstname);
                    $author_first_name = $dom->createElement('wp:author_first_name');
                    $author_element = $author->appendChild($author_first_name);
                    $author_element->appendChild($author_cdata);

                    $author_cdata = $dom->createCDATASection($current_user->user_lastname);
                    $author_last_name = $dom->createElement('wp:author_last_name');
                    $author_element = $author->appendChild($author_last_name);
                    $author_element->appendChild($author_cdata);


                    $dom->save($file_path);
                }
                break;

            case 'excel':
                //todo
                break;
        }


        if (!isset($_REQUEST['no_filter'])) {
            //get count of filtered - doesn work if export is for checked posts
            $posts = $this->posts->gets(array(
                'fields' => 'ids',
                'no_found_rows' => true
            ));
            echo json_encode($posts->posts);
        }

        exit;
    }

    //ajax
    public function wpbe_export_posts() {
        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }

        //***

        $behavior = 1;
        if (isset($_REQUEST['behavior']) AND intval($_REQUEST['behavior']) == 0) {
            $behavior = 0;
        }

        $this->csv_delimiter = $_REQUEST['csv_delimiter'];
        $file_postfix = sanitize_text_field($_REQUEST['file_postfix']);

        $combination = array();
        if (isset($_REQUEST['combination'])) {
            $combination = $_REQUEST['combination'];
        }

        //***
        if (!empty($_REQUEST['posts_ids'])) {
            switch ($_REQUEST['format']) {
                case 'csv':
                    $file = $this->get_ext_path() . "__exported_files/wpbe_exported{$file_postfix}.csv";
                    $fp = fopen($file, 'a+');
                    $posts_ids = array();

                    foreach ($_REQUEST['posts_ids'] as $post_id) {
                        $posts_ids[] = $post_id;
                        $post = $this->posts->get_post($post_id);
                    }

                    //***

                    foreach ($posts_ids as $post_id) {
                        fputcsv($fp, $this->get_post_fields($post_id, $this->get_active_fields()), $this->csv_delimiter);
                    }

                    fclose($fp);
                    break;
                case 'xml':
                    $file = $this->get_ext_path() . "__exported_files/wpbe_exported{$file_postfix}.xml";
                    $dom = new DOMDocument("1.0", "utf-8");
                    $dom->load($file);
                    $rss = $dom->firstChild;
                    $root = $rss->firstChild;
                    foreach ($_REQUEST['posts_ids'] as $post_id) {
                        $item = $dom->createElement("item");
                        $root->appendChild($item);
                        //die(json_encode($this->get_meta_for_xml($product_id, $this->get_active_fields())));
                        //wp data
                        //die(json_encode($this->get_category_for_xml($product_id, $this->get_active_fields())));


                        $wp_data = $this->get_post_data_for_xml($post_id, $this->get_active_fields());
                        foreach ($wp_data as $key => $val) {
                            if (in_array($key, array('content:encoded', 'excerpt:encoded'))) {
                                $wp_cdata = $dom->createCDATASection($val);
                                $data_item = $dom->createElement($key);
                                $wp_data = $item->appendChild($data_item);
                                $wp_data->appendChild($wp_cdata);
                            } else {
                                $data_item = $dom->createElement($key, htmlspecialchars($val));
                                if ('guid' == $key) {
                                    $isPermaLink = $dom->createAttribute('isPermaLink');
                                    $isPermaLink->value = 'false';
                                    $data_item->appendChild($isPermaLink);
                                }
                                $item->appendChild($data_item);
                            }
                        }
                        // tax data
                        $tax_data = $this->get_category_for_xml($post_id, $this->get_active_fields());
                        foreach ($tax_data as $key => $val) {
                            if (is_array($val)) {
                                foreach ($val as $term) {
                                    $tax_cdata = $dom->createCDATASection($term->name);
                                    $tax_item = $dom->createElement("category");
                                    $tax_data = $item->appendChild($tax_item);
                                    $tax_data->appendChild($tax_cdata);
                                    $domain = $dom->createAttribute('domain');
                                    $nicename = $dom->createAttribute('nicename');
                                    $domain->value = $key;
                                    $nicename->value = $term->slug;
                                    $tax_item->appendChild($domain);
                                    $tax_item->appendChild($nicename);
                                }
                            }
                        }

                        //meta data
                        $meta = $this->get_meta_for_xml($post_id, $this->get_active_fields());
                        foreach ($meta as $key => $val) {
                            if (is_array($val)) {
                                $val = serialize($val);
                            }
                            $meta_item = $dom->createElement("wp:postmeta");
                            $item->appendChild($meta_item);

                            $meta_key = $dom->createElement('wp:meta_key', $key);
                            $meta_item->appendChild($meta_key);

                            $meta_cdata = $dom->createCDATASection($val);
                            $meta_val = $dom->createElement('wp:meta_value');
                            $meta_data = $meta_item->appendChild($meta_val);
                            $meta_data->appendChild($meta_cdata);
                        }
                    }
                    $dom->save($file);
                    break;
                case 'excel':
                    //todo
                    break;

                default:
                    break;
            }
        }


        wp_die('done');
    }

    private function get_category_for_xml($post_id, $fields) {
        $data = array();
        foreach ($fields as $field_key => $field) {
            if (isset($field['taxonomy']) OR!empty($field['taxonomy'])) {
                $data[$field['taxonomy']] = $this->posts->get_post_field($post_id, $field_key);
            }
            if (isset($field['attribute']) OR!empty($field['attribute'])) {
                $data[$field['attribute']] = $this->posts->get_post_field($post_id, $field_key);
            }
            if ($field_key == 'catalog_visibility') {
                $data['product_visibility'] = wp_get_post_terms($post_id, 'product_visibility');
            }
        }
        return $data;
    }

    private function get_meta_for_xml($post_id, $fields) {
        $data = array();
        foreach ($fields as $field_key => $field) {
            if (isset($field['meta_key']) AND!empty($field['meta_key'])) {
                $val = $this->posts->get_post_field($post_id, $field_key);
                if (is_array($val)) {
                    $val = serialize($val);
                }
                $data[$field['meta_key']] = $val;
            }
        }

        $prod_data = get_post_meta($post_id, '_product_attributes', true);
        if ($prod_data) {
            $data['_product_attributes'] = $prod_data;
        }


        return $data;
    }

    private function get_post_data_for_xml($post_id, $fields) {
        $post_type = get_post_type($post_id);
        if (!$post_type) {
            $post_type = 'post';
        }
        $data = array(
            'wp:post_type' => $post_type,
            'dc:creator' => 'WOLF',
            'description' => '',
            'guid' => get_permalink($post_id),
            'link' => get_permalink($post_id),
            'pubDate' => mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true, $post_id), false),
        );



        $aloved_fields = array(
            'ID' => 'wp:post_id',
            'post_title' => 'title',
            'post_content' => 'content:encoded',
            'post_excerpt' => 'excerpt:encoded',
            'post_date' => 'wp:post_date',
            'post_name' => 'wp:post_name',
            'post_status' => 'wp:status',
            'menu_order' => 'wp:menu_order',
            'post_date_gmt' => 'wp:post_date_gmt',
            'comment_status' => 'wp:comment_status',
            'ping_status' => 'wp:ping_status',
            'post_parent' => 'wp:post_parent',
            'post_password' => 'wp:post_password',
            'sticky_posts' => 'wp:is_sticky'
        );
        foreach ($fields as $field_key => $field) {
            if (isset($aloved_fields[$field_key])) {
                $val = $this->posts->get_post_field($post_id, $field_key);
                $data[$aloved_fields[$field_key]] = $val;
            }
        }

        return $data;
    }

    private function get_post_fields($post_id, $fields) {
        $answer = array();
        if (!empty($fields)) {

            foreach ($fields as $field_key => $field) {
                if (!in_array($field_key, $this->exlude_keys)) {

                    $a = $this->filter_fields_vals($this->posts->get_post_field($post_id, $field_key), $field_key, $field, $post_id);

                    switch ($field['field_type']) {
                        case 'xxx':
                            //empty
                            break;
                        default:
                            if (is_array($a)) {
                                if (!empty($a)) {
                                    $titles = [];

                                    foreach ($a as $o) {
                                        if (is_object($o)) {
                                            $titles[] = $o->name;
                                        } else {
                                            $titles[] = $o;
                                        }
                                    }

                                    $answer[] = implode(',', $titles);
                                } else {
                                    $answer[] = '';
                                }
                            } else {
                                $answer[] = $a;
                            }

                            break;
                    }
                }
            }
        }

        return $answer;
    }

    //values replaces to the human words
    private function filter_fields_vals($value, $field_key, $field, $post_id) {
        switch ($field['field_type']) {
            case 'taxonomy':

                if (is_array($value) AND!empty($value)) {
                    $tmp = array();
                    if (in_array($field['taxonomy'], array('post_type'))) {
                        foreach ($value as $term) {
                            $tmp[] = $term->slug;
                        }
                        $value = implode(',', $tmp);
                    } else {
                        foreach ($value as $term) {
                            $tmp[] = $term->term_id;
                        }
                    }
                } else {
                    $value = '';
                }

                break;

            case 'meta':
                //just especially for thumbnail only
                if ($field['edit_view'] == 'thumbnail') {
                    $image = wp_get_attachment_image_src($value, 'full');
                    if ($image) {
                        $value = $image[0];
                    }
                }

                if ($field['edit_view'] == 'meta_popup_editor') {
                    if (!empty($value)) {
                        $value = json_encode($value, JSON_HEX_QUOT | JSON_HEX_TAG);
                    }
                }

                break;

            case 'field':
                if ($field_key == 'post_parent') {
                    $value = intval($value);
                    if ($value > 0) {
                        $value = 'id:' . $value;
                    }
                }
                break;
        }

        return $value;
    }

    //**********************************************************************

    public function get_active_fields() {
        static $fields_observed = array(); //cache

        if (empty($fields_observed)) {
            $fields_observed = $this->settings->active_fields;
        }

        return $fields_observed;
    }
    public function check_export_files() {
            $transient = 'wpbe_time_last_check';
            $max_age = 3600 * 24 * 2;
            $last_check = get_transient($transient);
            if (!$last_check) {
                    $last_check = 0;
            }

            $over_time = $last_check + $max_age;

            if ($over_time < time()) {
                    $this->delete_old_export_files($max_age);
                    $last_check = set_transient($transient, time());
                    return;
            }


    }    
    public function delete_old_export_files($max_age) {
            $list = array();

            $limit = time() - $max_age;
            $dir = $this->get_ext_path() . "__exported_files/";
            $dir = realpath($dir);

            if (!is_dir($dir)) {
                    return;
            }

            $dh = opendir($dir);
            if ($dh === false) {
                    return;
            }

            while (($file = readdir($dh)) !== false) {
                    $file = $dir . '/' . $file;
                    if (!is_file($file)) {
                            continue;
                    }

                    if (filemtime($file) < $limit) {
                            $list[] = $file;
                            unlink($file);
                    }
            }
            closedir($dh);
    }   

}
