<?php
/*
  Plugin Name: WOLF - WordPress Posts Bulk Editor and Manager Professional
  Plugin URI: https://bulk-editor.pro/
  Description: Tools for managing and bulk edit posts, pages and all custom types data in the reliable and flexible way! Be professionals with managing data of your site!
  Requires at least: WP 4.9
  Tested up to: WP 6.4
  Author: realmag777
  Author URI: https://pluginus.net/
  Version: 2.0.8.2
  Requires PHP: 5.6
  Tags: bulk,bulk edit,bulk editor,posts editor,bulk delete,real estate,posts manager,meta bulk edit
  Text Domain: bulk-editor
  Domain Path: /languages
  Forum URI: https://pluginus.net/support/forum/wpbe-wordpress-posts-bulk-editor-professional/
 */

//update_option('wpbe_options_' . get_current_user_id(), ''); //reset of the plugin settings - be care
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (defined('WPBE_PATH')) {
    add_action('admin_notices', function () {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e("Hello, looks like you bought and installed premium version of WOLF - WordPress Posts Bulk Editor and Manager Professional, please deactivate free version before, then uninstall it. 2 versions of the same plugin cannot be activated on the same time!", 'bulk-editor'); ?></p>
        </div>
        <?php
    });
    return;
}

//***

define('WPBE_PATH', plugin_dir_path(__FILE__));
define('WPBE_LINK', plugin_dir_url(__FILE__));
define('WPBE_ASSETS_LINK', WPBE_LINK . 'assets/');
define('WPBE_DATA_PATH', WPBE_PATH . 'data/');
define('WPBE_PLUGIN_NAME', plugin_basename(__FILE__));
define('WPBE_VERSION', '2.0.8.2');
//define('WPBE_VERSION', uniqid('wpbe-')); //dev
define('WPBE_MIN_WP_VERSION', '4.9');

//libs
include WPBE_PATH . 'lib/storage.php';

//data
include_once WPBE_DATA_PATH . 'fields.php';
include_once WPBE_DATA_PATH . 'settings.php';

//classes
include WPBE_PATH . 'classes/helper.php';
include WPBE_PATH . 'classes/models/profiles.php';
include WPBE_PATH . 'classes/models/settings.php';
include WPBE_PATH . 'classes/models/posts.php';
include WPBE_PATH . 'classes/ext.php';

//30-01-2024
final class WPBE {

    public $storage = NULL;
    public $settings = NULL;
    public $posts = NULL;
    public $profiles = NULL;
    private $ext = array('filters', 'bulk', 'export', 'meta', 'history', 'calculator', 'info', 'fprofiles', 'author_area');
    public $show_notes = false;
    //extensions
    public $filters = null;
    public $bulk = null;
    public $export = null;
    public $meta = null;
    public $history = null;
    public $calculator = null;
    public $info = null;
    public $fprofiles = null;
    public $author_area = null;

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter('wpbe_post_statuses', array($this, 'add_statuses'));
    }

    public function init() {

        if (!is_admin()) {
            return;
        }
        //wp editor
        add_action('admin_footer', array($this, 'wp_editor_compatibility'));

        //no one operation is possible if user is not a posts administrator!!
        if (!WPBE_HELPER::can_manage_data()) {
            return;
        }

        $this->ask_favour();

//***

        add_filter('plugin_action_links_' . WPBE_PLUGIN_NAME, array($this, 'plugin_action_links'), 50);

        if (class_exists('SitePress')) {
            add_filter('wpbe_current_language', function () {
                //WPML compatibility
                //because if it will be selected 'all' language - will be shown default one
                return ICL_LANGUAGE_CODE;
            });
        }

//***

        if (isset($_GET['page']) AND $_GET['page'] == 'wpbe') {

            add_action('admin_notices', function () {
                $user_id = get_current_user_id();
                if (!get_user_meta($user_id, 'wpbe_notice_dismissed')) {
                    echo '<div class="notice notice-warning"><p>' . sprintf(esc_html__('If you not familiar with the plugin, firstly %s please', 'bulk-editor'), WPBE_HELPER::draw_link(array(
                                'title' => esc_html__('visit this page', 'bulk-editor'),
                                'href' => 'https://bulk-editor.pro/document/posts-editor/',
                                'target' => '_blank'
                            ))) . '</p><a href="admin.php?page=wpbe&wpbe-notice-dismissed=1&notice_nonce=' . wp_create_nonce('wpbe_notice_nonce') . '" class="notice-dismiss"></a></div>';
                }
            });
            add_action('admin_init', function () {
                $user_id = get_current_user_id();
                if (isset($_GET['wpbe-notice-dismissed']) && isset($_GET['notice_nonce']) && wp_verify_nonce($_GET['notice_nonce'], 'wpbe_notice_nonce')) {
                    add_user_meta($user_id, 'wpbe_notice_dismissed', 'true', true);
                }
            });
        }

        //side bar menu
        if (WPBE_HELPER::can_manage_data()) {
            add_action('admin_menu', function () {
                add_menu_page(esc_html__('WOLF Bulk Editor', 'bulk-editor'), esc_html__('WOLF Bulk Editor', 'bulk-editor'), 'publish_posts', 'wpbe', function () {
                    $this->print_plugin_options();
                }, 'dashicons-hammer', 90);
            }, 99);
        }

        add_action('admin_bar_menu', function ($wp_admin_bar) {
            $opt = get_option('wpbe_options_' . get_current_user_id()); //not beauty but we need it here

            if (isset($opt['options']['show_admin_bar_menu_btn']) AND intval($opt['options']['show_admin_bar_menu_btn']) === 1) {
                $args = array(
                    'id' => 'wpbe-btn',
                    'title' => esc_html__('WOLF Bulk Editor', 'bulk-editor'),
                    'href' => admin_url('admin.php?page=wpbe'),
                    'meta' => array(
                        'class' => 'wp-admin-bar-wpbe-btn',
                        'title' => 'WOLF - Posts Bulk Editor Professional'
                    )
                );
                $wp_admin_bar->add_node($args);
            }
            unset($opt);
        }, 250);

//do not init hooks and all other parts of the plugins as we not need it on all site pages
        if (!$this->is_should_init()) {
            return;
        }

//***
//include extensions and their hooks
        if (!empty($this->ext)) {
            foreach ($this->ext as $ext_slug) {
                include WPBE_PATH . 'ext' . DIRECTORY_SEPARATOR . $ext_slug . DIRECTORY_SEPARATOR . $ext_slug . '.php';
                $class_name = 'WPBE_' . strtoupper($ext_slug);
                $this->$ext_slug = new $class_name();
            }
        }

//wpbe_ext - include extensions from wp-content folder
        $wpbe_more_ext_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wpbe_ext';
        if (file_exists($wpbe_more_ext_path)) {
            $dir = new DirectoryIterator($wpbe_more_ext_path);
            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDir() AND !$fileinfo->isDot()) {
                    $ext_slug = trim($fileinfo->getFilename());
                    include $wpbe_more_ext_path . DIRECTORY_SEPARATOR . $ext_slug . DIRECTORY_SEPARATOR . $ext_slug . '.php';
                    $class_name = 'WPBE_' . strtoupper($ext_slug);
                    $this->$ext_slug = new $class_name();
                    $this->ext[] = $ext_slug;
                }
            }
        }


//***
//init variables and hooks of the extensions will be applied, for example hook wpbe_extend_fields
        $this->storage = new WPBE_STORAGE();
        $this->settings = new WPBE_SETTINGS(); //must be reinit because hooks from exts applied!!
        $this->profiles = new WPBE_PROFILES($this->settings);
        $this->posts = new WPBE_POSTS($this->settings, $this->storage);

        if (!empty($this->ext)) {
            foreach ($this->ext as $ext_slug) {
//we do it to allow ext hooks works everywhere (in the application and all its extensions)
                $this->$ext_slug->init_vars($this->storage, $this->profiles, $this->settings, $this->posts);
            }
        }


//***

        load_plugin_textdomain('bulk-editor', false, 'bulk-editor/languages');

//ajax
        add_action('wp_ajax_wpbe_get_posts', array($this, 'wpbe_get_posts'), 1);
        add_action('wp_ajax_wpbe_update_page_field', array($this, 'wpbe_update_page_field'), 1);
        add_action('wp_ajax_wpbe_redraw_table_row', array($this, 'wpbe_redraw_table_row'), 1);
        add_action('wp_ajax_wpbe_get_post_field', array($this, 'get_post_field'), 1);
        add_action('wp_ajax_wpbe_get_gallery', array($this, 'wpbe_get_gallery'), 1);
        add_action('wp_ajax_wpbe_get_upsells', array($this, 'wpbe_get_upsells'), 1);

        add_action('wp_ajax_wpbe_create_new_post', array($this, 'wpbe_create_new_post'), 1);
        add_action('wp_ajax_wpbe_duplicate_posts', array($this, 'wpbe_duplicate_posts'), 1);
        add_action('wp_ajax_wpbe_delete_posts', array($this, 'wpbe_delete_posts'), 1);

        add_action('wp_ajax_wpbe_create_new_term', array($this, 'wpbe_create_new_term'), 1);
        add_action('wp_ajax_wpbe_update_tax_term', array($this, 'wpbe_update_tax_term'), 1);
        add_action('wp_ajax_wpbe_delete_tax_term', array($this, 'wpbe_delete_tax_term'), 1);

        add_action('wp_ajax_wpbe_set_active_post_type', function () {
            if (!empty($_REQUEST['post_type']) AND post_type_exists($_REQUEST['post_type'])) {
                $this->storage->set_val('wpbe_current_post_type_' . get_current_user_id(), $_REQUEST['post_type']);
            }
            exit;
        }, 1);

//***
        add_action('wp_ajax_wpbe_title_autocomplete', array($this, 'wpbe_title_autocomplete'));
        add_action('wp_ajax_wpbe_save_options', array($this, 'wpbe_save_options'), 1);

//***
        add_post_type_support($this->settings->current_post_type, 'author');
    }

    /**
     * Show action links on the plugins page screen
     */
    public function plugin_action_links($links) {

        $wpbe_links = array(
            '<a href="' . admin_url('admin.php?page=wpbe') . '">' . esc_html__('Posts Editor', 'bulk-editor') . '</a>',
            '<a target="_blank" href="' . esc_url('https://bulk-editor.pro/') . '"><span class="icon-book"></span>&nbsp;' . esc_html__('Documentation', 'bulk-editor') . '</a>'
        );

        if ($this->show_notes) {
            $wpbe_links[] = '<a target="_blank" href="https://pluginus.net/affiliate/wordpress-posts-bulk-editor"><span style="color: red; font-weight: bold;">&nbsp;Go Pro!</span></a>';
        }

        return array_merge($wpbe_links, $links);
    }

    public function admin_enqueue_scripts() {
        if (isset($_GET['page']) AND $_GET['page'] == 'wpbe') {
            ?>
            <script>
                var lang = {
                    move: "<?php esc_html_e('move', 'bulk-editor') ?>",
                    search: "<?php esc_html_e('Search', 'bulk-editor') ?>",
                    rest_failed: "<?php esc_html_e('Failed', 'bulk-editor') ?>",
                    error: "<?php esc_html_e('Error', 'bulk-editor') ?>",
                    delete: "<?php esc_html_e('delete', 'bulk-editor') ?>",
                    ignore: "<?php esc_html_e('ignore', 'bulk-editor') ?>",
                    no_deletable: "<?php esc_html_e('This is not deletable!', 'bulk-editor') ?>",
                    no_items: "<?php esc_html_e('no items', 'bulk-editor') ?>",
                    none: "<?php esc_html_e('none', 'bulk-editor') ?>",
                    no_data: "<?php esc_html_e('no data', 'bulk-editor') ?>",
                    loading: "<?php esc_html_e('Loading', 'bulk-editor') ?> ...",
                    loaded: "<?php esc_html_e('Loaded', 'bulk-editor') ?>.",
                    saved: "<?php esc_html_e('Saved', 'bulk-editor') ?>.",
                    saving: "<?php esc_html_e('Saving', 'bulk-editor') ?> ...",
                    apply: "<?php esc_html_e('Apply', 'bulk-editor') ?>",
                    cancel: "<?php esc_html_e('Cancel', 'bulk-editor') ?>",
                    canceled: "<?php esc_html_e('Canceled', 'bulk-editor') ?>",
                    sure: "<?php esc_html_e('Sure?', 'bulk-editor') ?>",
                    creating: "<?php esc_html_e('Creating', 'bulk-editor') ?> ...",
                    created: "<?php esc_html_e('Created!', 'bulk-editor') ?>",
                    duplicating: "<?php esc_html_e('Duplicating', 'bulk-editor') ?> ...",
                    duplicated: "<?php esc_html_e('Duplicated!', 'bulk-editor') ?>",
                    deleting: "<?php esc_html_e('Deleting', 'bulk-editor') ?> ...",
                    deleted: "<?php esc_html_e('Deleted!', 'bulk-editor') ?>",
                    reseting: "<?php esc_html_e('Reseting', 'bulk-editor') ?> ...",
                    reseted: "<?php esc_html_e('Reseted!', 'bulk-editor') ?>",
                    upload_image: "<?php esc_html_e('Upload image', 'bulk-editor') ?>",
                    upload_images: "<?php esc_html_e('Upload images', 'bulk-editor') ?>",
                    upload_file: "<?php esc_html_e('Upload file', 'bulk-editor') ?>",
                    fill_up_data: "<?php esc_html_e('Fill up the data please!', 'bulk-editor') ?>",
                    enter_duplicate_count: "<?php printf(esc_html__('Enter how many time duplicate selected [%s](s)!', 'bulk-editor'), $this->settings->current_post_type) ?>",
                    enter_new_count: "<?php printf(esc_html__('Enter how many new [%s](s) to create!', 'bulk-editor'), $this->settings->current_post_type) ?>",
                    search_input_placeholder: "<?php esc_html_e('Text search by title or SKU', 'bulk-editor') ?>",
                    show_panel: "<?php esc_html_e('Show: Filters/Bulk Edit/Export', 'bulk-editor') ?>",
                    close_panel: "<?php esc_html_e('Hide: Filters/Bulk Edit/Export', 'bulk-editor') ?>",
                    per_page: "<?php esc_html_e('Per page', 'bulk-editor') ?>",
                    color_picker_col: "<?php esc_html_e('Select background color', 'bulk-editor') ?>",
                    color_picker_txt: "<?php esc_html_e('Select text color', 'bulk-editor') ?>",
                    sEmptyTable: "<?php esc_html_e('No data available in the table', 'bulk-editor') ?>",
                    sInfo: "<?php esc_html_e('Showing _START_ to _END_ of _TOTAL_ entries', 'bulk-editor') ?>",
                    sInfoEmpty: "<?php esc_html_e('Showing 0 to 0 of 0 entries', 'bulk-editor') ?>",
                    sInfoFiltered: "<?php esc_html_e('(filtered from _MAX_ total entries)', 'bulk-editor') ?>",
                    sLoadingRecords: "<?php esc_html_e('Loading...', 'bulk-editor') ?>",
                    sProcessing: "<?php esc_html_e('Processing...', 'bulk-editor') ?>",
                    sZeroRecords: "<?php esc_html_e('No matching records found', 'bulk-editor') ?>",
                    sFirst: "<?php esc_html_e('First', 'bulk-editor') ?>",
                    sLast: "<?php esc_html_e('Last', 'bulk-editor') ?>",
                    sNext: "<?php esc_html_e('Next', 'bulk-editor') ?>",
                    sPrevious: "<?php esc_html_e('Previous', 'bulk-editor') ?>",
                    action_state_1: "<?php printf(esc_html__('all the [%s] on the site', 'bulk-editor'), $this->settings->current_post_type) ?>",
                    action_state_2: "<?php printf(esc_html__('the filtered [%s]. To remove filtering press reset button on the tools panel below', 'bulk-editor'), $this->settings->current_post_type) ?>",
                    action_state_31: "<?php printf(esc_html__('the selected [%s](s)', 'bulk-editor'), $this->settings->current_post_type) ?>",
                    action_state_32: "<?php printf(esc_html__('You can reset selection of the [%s](s) by its reset button on the panel of the editor OR uncheck them manually!', 'bulk-editor'), $this->settings->current_post_type) ?>",
                    term_maybe_exist: "<?php esc_html_e('Maybe term(s) with such name(s) already exists!', 'bulk-editor') ?>",
                    free_ver_profiles: "<?php esc_html_e('In FREE version of the plugin you can create one profile only!', 'bulk-editor') ?>",
                    append_sub_item: "<?php esc_html_e('append sub item', 'bulk-editor') ?>",
                    is_deactivated_in_free: "<?php esc_html_e('This field is deactivated in FREE version for bulk edit!', 'bulk-editor') ?>",
                    checked_post: "<?php esc_html_e('Posts checked', 'bulk-editor') ?>",
                };

                var wpbe_settings = {
                    show_thumbnail_preview: <?php echo intval($this->settings->show_thumbnail_preview) ?>,
                    load_switchers: <?php echo intval($this->settings->load_switchers) ?>,
                    autocomplete_max_elem_count: <?php echo intval($this->settings->autocomplete_max_elem_count) ?>
                };
                var wpbe_field_update_nonce = "<?php echo wp_create_nonce('wpbe_field_update') ?>";
                var wpbe_assets_link = "<?php echo WPBE_ASSETS_LINK ?>";
                var spinner = wpbe_assets_link + "/images/spinner.gif";
                var start_page = <?php echo (isset($_GET['start_page']) ? intval($_GET['start_page']) : 0) ?>;

                //***

                var wpbe_lang = '<?php echo apply_filters('wpbe_current_language', '') ?>';//for translating compatibilities


            </script>

            <?php
            wp_enqueue_style('wpbe_open_sans_font', '//fonts.googleapis.com/css?family=Open+Sans');
            wp_enqueue_style('wpbe-bootstrap-grid', WPBE_ASSETS_LINK . 'css/bootstrap-grid.css', [], WPBE_VERSION);
            wp_enqueue_style('wpbe', WPBE_ASSETS_LINK . 'css/wpbe.css', [], WPBE_VERSION);
            wp_enqueue_style('wpbe_scrollbar', WPBE_ASSETS_LINK . 'css/jquery.scrollbar.css', [], WPBE_VERSION);
            wp_enqueue_style('wpbe_fontello', WPBE_ASSETS_LINK . 'css/fontello.css', [], WPBE_VERSION);

//***

            wp_enqueue_media();
            wp_enqueue_script('media-upload');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');

            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            wp_enqueue_script('wpbe_modernizr', WPBE_ASSETS_LINK . 'js/modernizr.js', [], WPBE_VERSION);

            wp_enqueue_style('wpbe_datatables', WPBE_ASSETS_LINK . 'css/tables.css', [], WPBE_VERSION);
            wp_enqueue_script('wpbe_datatables_net', WPBE_ASSETS_LINK . 'js/jquery.dataTables.js', array('jquery'), WPBE_VERSION);
            wp_enqueue_script('wpbe_data_tables', WPBE_ASSETS_LINK . 'js/data-tables.js', array('wpbe_datatables_net'), WPBE_VERSION);
            wp_enqueue_script('wpbe_jquery_growl', WPBE_ASSETS_LINK . 'js/jquery.growl.js', array('jquery'), WPBE_VERSION);

            wp_enqueue_style('wpbe_switchery', WPBE_ASSETS_LINK . 'js/switchery/switchery.min.css', [], WPBE_VERSION);
            wp_enqueue_script('wpbe_switchery', WPBE_ASSETS_LINK . 'js/switchery/switchery.min.js', array('jquery'), WPBE_VERSION);

            wp_enqueue_style('wpbe_chosen', WPBE_ASSETS_LINK . 'js/chosen/chosen.min.css', [], WPBE_VERSION);
            wp_enqueue_script('wpbe_chosen', WPBE_ASSETS_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'), WPBE_VERSION);

            wp_enqueue_style('wpbe_autocomplete', WPBE_ASSETS_LINK . 'js/easy-autocomplete/easy-autocomplete.min.css', [], WPBE_VERSION);
            wp_enqueue_style('wpbe_autocomplete_theme', WPBE_ASSETS_LINK . 'js/easy-autocomplete/easy-autocomplete.themes.min.css', [], WPBE_VERSION);
            wp_enqueue_script('wpbe_autocomplete', WPBE_ASSETS_LINK . 'js/easy-autocomplete/jquery.easy-autocomplete.min.js', array('jquery'), WPBE_VERSION);

            wp_enqueue_style('wpbe_datetimepicker', WPBE_ASSETS_LINK . 'js/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css', [], WPBE_VERSION);
            wp_enqueue_script('wpbe_datetimepicker_moment', WPBE_ASSETS_LINK . 'js/datepicker/moment-with-locales.min.js', array('jquery'), WPBE_VERSION);
            wp_enqueue_script('wpbe_datetimepicker', WPBE_ASSETS_LINK . 'js/bootstrap-material-datetimepicker/bootstrap-material-datetimepicker.js', array('jquery'), WPBE_VERSION);

            wp_enqueue_script('wpbe_placeholder_label', WPBE_ASSETS_LINK . 'js/jquery.placeholder.label.min.js', array('jquery'), WPBE_VERSION);
            wp_enqueue_script('wpbe_tooltip', WPBE_ASSETS_LINK . 'js/tooltip.js', array('jquery'), WPBE_VERSION);

            wp_enqueue_script('wpbe_tabs', WPBE_ASSETS_LINK . 'js/tabs.js', [], WPBE_VERSION);
            wp_enqueue_script('wpbe_scrollbar', WPBE_ASSETS_LINK . 'js/jquery.scrollbar.min.js', [], WPBE_VERSION);

            //***

            wp_enqueue_script('wpbe', WPBE_ASSETS_LINK . 'js/wpbe.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'wpbe_tabs'), WPBE_VERSION);
            do_action('wpbe_ext_scripts'); //including extensions scripts
        }
    }

    public function print_plugin_options() {
        $args = array();
        $args['options'] = $this->settings->get_options();
        $args['total_settings'] = $this->settings->get_total_settings();
        $args['tax_keys'] = array();

        $args['meta_popup_editor'] = FALSE;

//to generate terms in popup taxonomies data
        if (!empty($this->settings->active_fields)) {
            foreach ($this->settings->active_fields as $k => $f) {
                if ($f['field_type'] == 'taxonomy' AND $f['edit_view'] == 'popup') {
                    $args['tax_keys'][] = $f['taxonomy'];
                }


//***

                if ($f['edit_view'] == 'popupeditor') {
                    $args['is_popupeditor'] = TRUE;
                }


                if ($f['edit_view'] == 'gallery_popup_editor') {
                    $args['is_gallery'] = TRUE;
                }

                if ($f['edit_view'] == 'meta_popup_editor') {
                    $args['meta_popup_editor'] = TRUE;
                }
            }
        }

        //***

        $args['active_fields'] = $this->settings->active_fields;
        $args['settings_fields'] = $this->settings->get_fields();
        $args['settings_fields_full'] = $this->settings->get_fields(false);
        $args['settings_fields_keys'] = $this->settings->get_fields_keys();
        $args['editable'] = $this->settings->editable;
        $args['default_sortby_col_num'] = $this->settings->get_default_sortby_col_num();
        $args['default_sort'] = $this->settings->default_sort;
        $args['no_order'] = $this->settings->no_order;
        $args['per_page'] = $this->settings->per_page;
        $args['extend_per_page'] = $this->settings->add_more_per_page;
        $args['show_notes'] = $this->show_notes;
        $args['current_user_role'] = $this->settings->current_user_role;
        $args['profiles'] = $this->profiles->get();

        //***

        echo WPBE_HELPER::render_html(WPBE_PATH . 'views/wpbe.php', apply_filters('wpbe_print_plugin_options', $args));
    }

//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//ajax
    public function wpbe_get_posts($args = array(), $return = false) {

        if (!WPBE_HELPER::can_manage_data()) {
            return;
        }

//***

        $res = array();
        $res['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
        $res['data'] = array();
        $fileds_keys = $this->settings->get_fields_keys();

        if (empty($args)) {
            //for ajax only
            $args = array(
                'lang' => apply_filters('wpbe_current_language', ''),
                'per_page' => intval($_REQUEST['length']),
                'offset' => intval($_REQUEST['start']),
                'order_by' => $fileds_keys[intval($_REQUEST['order'][0]['column'])],
                'order' => sanitize_key($_REQUEST['order'][0]['dir']),
                'search' => $_REQUEST['search']['value']
            );
        }

        $posts = $this->posts->gets($args);

        $res['recordsFiltered'] = $res['recordsTotal'] = $posts->found_posts;
        if ($posts->found_posts > 0) {
            $posts_types = array();
            $posts_titles = array();
            foreach ($posts->posts as $p) {
                $post_type = 'simple';
                $res['data'][] = $this->__pack_row($p);
                $posts_types[$p->ID] = $post_type;
                $posts_titles[$p->ID] = str_replace('"', "", str_replace("'", "", $p->post_title));

                //get variations if exists and requested
                if ($post_type == 'variable' AND ( isset($_REQUEST['wpbe_show_variations']) AND intval($_REQUEST['wpbe_show_variations']) > 0)) {
                    //removed, but for todo ideas
                }

                //***
                //data for javascript functionality on the front
                $res['posts_types'] = $posts_types;
                $res['posts_titles'] = $posts_titles;
            }
        }

        //***

        if (!$return) {
            //if requested by ajax
            wp_die(json_encode($res));
        }

        return $res;
    }

//service
    private function __pack_row($p) {
        $row = array();
        $p = (array) $p;

        foreach ($this->settings->get_fields_keys() as $key) {
            $row[] = $this->wrap_field_val($p, $key);
        }

        $row[] = WPBE_HELPER::draw_link(array(
                    'title' => '&#xea0b;',
                    'href' => get_permalink($p['ID']),
                    'target' => '_blank',
                    'class' => 'button button-primary button-small',
                    'title_attr' => esc_html__('View the post on the site front', 'bulk-editor')
                )) . '&nbsp;' . WPBE_HELPER::draw_link(array(
                    'title' => '&#xea25;',
                    'href' => get_admin_url() . 'post.php?post=' . $p['ID'] . '&action=edit',
                    'target' => '_blank',
                    'class' => 'button button-primary button-small',
                    'title_attr' => esc_html__('Editing of the post on its page', 'bulk-editor')
        ));

        return $row;
    }

//ajax
    public function wpbe_update_page_field($post_id = 0, $field_key = '', $value = '') {

        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }

        if (!$post_id) {
            $post_id = intval($_REQUEST['post_id']);
        }
        if (!isset($_REQUEST['value']) || $_REQUEST['value'] == null) {
            $_REQUEST['value'] = array();
        }
        $field_type = '';
        if (isset($_REQUEST['field_type'])) {
            $field_type = sanitize_text_field($_REQUEST['field_type']);
        }
        //leaved as attention info
        //$field_key = sanitize_key($_REQUEST['field']);//if sanitize not all meta keys works normally!!

        if (empty($field_key)) {
            $field_key = sanitize_text_field(trim($_REQUEST['field'])); //if sanitize by sanitize_key not all meta keys works normally!!
        }

        if ($post_id > 0 AND isset($_REQUEST['value'])) {

            if (empty($value)) {
                if (is_array($_REQUEST['value'])) {
                    //$value = (array) $_REQUEST['value'];
                    $value = WPBE_HELPER::sanitize_array((array) $_REQUEST['value']);
                } else {

                    $allowedpost = wp_kses_allowed_html('post');
                    if ('post_content' == $field_key OR 'post_excerpt' == $field_key OR $this->settings->active_fields[$field_key]['edit_view'] === 'popupeditor') {
                        $is_encoded = false;
                        $allowedpost['iframe'] = array(
                            'align' => true,
                            'frameborder' => true,
                            'height' => true,
                            'width' => true,
                            'sandbox' => true,
                            'seamless' => true,
                            'scrolling' => true,
                            'srcdoc' => true,
                            'src' => true,
                            'class' => true,
                            'id' => true,
                            'style' => true,
                            'border' => true,
                        );
                    }
//***

                    if (is_string($_REQUEST['value'])) {
                        $is_encoded = preg_match('~%[0-9A-F]{2}~i', $_REQUEST['value']);
                        if ($is_encoded) {
                            //for example gallery
                            $data_array = [];

                            parse_str($_REQUEST['value'], $data_array);
                            $data_array = WPBE_HELPER::sanitize_array($data_array);
                            if (isset($data_array[$field_key])) {
                                $data_array = $data_array[$field_key];
                            }

                            switch ($field_type) {
                                case 'gallery_popup_editor':
                                    $value = implode(',', $data_array); //images ids

                                    break;
                            }
                        }

                        if (empty($value)) {

                            if (apply_filters('wpbe_use_kses_for_page_field', true)) {
                                $value = wp_kses($_REQUEST['value'], $allowedpost);
                            } else {
                                $value = sanitize_text_field($_REQUEST['value']);
                            }


                            if ($this->settings->active_fields[$field_key]['edit_view'] === 'meta_popup_editor') {
                                $value = htmlspecialchars_decode($value);
                            }
                        }
                    } else {
                        $value = sanitize_text_field(trim($_REQUEST['value']));
                    }
                }
            }





//***
            //normalize calendar date
            if ($this->settings->active_fields[$field_key]['edit_view'] === 'calendar') {
                $value = $this->posts->normalize_calendar_date($value, $field_key);
            }




            $value = $this->posts->__string_replacer($value, $post_id);
            echo $this->posts->update_page_field($post_id, $field_key, $value, $field_type);
        }

        if (isset($_REQUEST['post_id'])) {
            exit; //ajax
        }
    }

//ajax
    public function wpbe_redraw_table_row() {
        if (is_array($_REQUEST['value'])) {
            $value = (array) $_REQUEST['value'];
        } else {
            $value = trim($_REQUEST['value']);
        }

//***

        $post_id = intval($_REQUEST['post_id']);

        if (isset($_REQUEST['field']) AND !empty($_REQUEST['field'])) {
            $field_key = sanitize_key($_REQUEST['field']);
            $this->posts->update_page_field($post_id, $field_key, $value);
        }


//generate table row by $post_id
        $res = $this->wpbe_get_posts(array(
            'p' => $post_id,
            'post_type' => array($this->settings->current_post_type)
                ), true);

        if (!empty($res['data'])) {
            echo(json_encode($res['data'][0]));
        }

        exit;
    }

//ajax
    public function get_post_field() {
        echo $this->posts->get_post_field(intval($_REQUEST['post_id']), sanitize_key($_REQUEST['field']), (isset($_REQUEST['post_parent']) ? $_REQUEST['post_parent'] : 0));
        exit;
    }

//ajax
    public function wpbe_get_gallery() {

        $post_id = intval($_REQUEST['post_id']);

        if (!$post_id) {
            exit;
        }

        $post = $this->posts->get_post($post_id);

        echo WPBE_HELPER::render_html(WPBE_PATH . 'views/parts/post-gallery.php', array(
            'images' => $post->get_gallery_image_ids('edit')
        ));

        exit;
    }

//ajax
    public function wpbe_get_upsells() {

        $post_id = intval($_REQUEST['post_id']);

        if (!$post_id) {
            exit;
        }

        $post = $this->posts->get_post($post_id);

        echo WPBE_HELPER::render_html(WPBE_PATH . 'views/parts/post-upsells.php', array(
            'posts' => $post->get_upsell_ids('edit')
        ));

        exit;
    }

//ajax
    public function wpbe_save_options() {

        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['save_nonce']) || !wp_verify_nonce($_REQUEST['save_nonce'], 'wpbe_settings_nonce')) {
            die('0');
        }
        $data = array();
        parse_str($_REQUEST['formdata'], $data);

        if (isset($data['wpbe_options'])) {
            if (is_array($data['wpbe_options'])) {
                $this->settings->update_options($data['wpbe_options']);
            }

            //***
            //save site editor fields visibility
            if (in_array($this->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
                $visibility = array();

                foreach ($data['wpbe_options']['fields'] as $key => $v) {
                    if (isset($v['site_editor_visibility'])) {
                        $visibility[$key] = intval($v['site_editor_visibility']);
                    }
                }

                update_option('wpbe_site_editor_visibility', $visibility);

                //***
                update_option('wpbe_site_editors_post_types', $data['wpbe_site_editors_post_types']);

                if (isset($data['wpbe_site_separate_settings'])) {
                    update_option('wpbe_site_separate_settings', $data['wpbe_site_separate_settings']);
                    update_option('wpbe_show_text_editor', $data['wpbe_show_text_editor']);
                }
            }
        }

        exit;
    }

//ajax
    public function wpbe_title_autocomplete() {
        $results = array();
        $results[] = array(
            "name" => esc_html__("Posts not found!", 'bulk-editor'),
            "id" => 0,
            "type" => "",
            "link" => "#",
            "icon" => WPBE_ASSETS_LINK . 'images/not-found.jpg'
        );

//***

        if (!empty($_REQUEST['wpbe_txt_search'])) {
            $args = array(
                'nopaging' => true,
                'post_type' => array($this->settings->current_post_type),
                'post_status' => array_keys(get_post_statuses()),
                'order_by' => 'title',
                'order' => 'ASC',
                'per_page' => intval($_REQUEST['auto_res_count']) > 0 ? intval($_REQUEST['auto_res_count']) : 10,
                'max_num_pages' => intval($_REQUEST['auto_res_count']) > 0 ? intval($_REQUEST['auto_res_count']) : 10
            );

//***

            if (!empty($_REQUEST['exept_ids'])) {
                $exept_ids = array(); //which posts exclude as they are on the list already
                parse_str($_REQUEST['exept_ids'], $exept_ids);
                $args['post__not_in'] = $exept_ids['wpbe_prod_ids'];
            }

//***
            $st = $_REQUEST['wpbe_txt_search'];
            $_REQUEST['wpbe_txt_search'] = array();
            $_REQUEST['wpbe_txt_search_behavior'] = array();
            $_REQUEST['wpbe_txt_search']['post_title'] = $st;
            $_REQUEST['wpbe_txt_search_behavior']['post_title'] = 'like';
            $this->posts->suppress_filters = true;
            add_filter('posts_where', array($this->filters, 'posts_txt_where'), 101);
            $query = $this->posts->gets($args);

//+++
//http://easyautocomplete.com/guide
            if ($query->have_posts()) {
                $results = array();
                foreach ($query->posts as $p) {
                    $data = array(
                        "name" => $p->post_title . ' (#' . $p->ID . ')',
                        "id" => $p->ID,
                        "type" => "post"
                    );
                    if (has_post_thumbnail($p->ID)) {
                        $img_src = wp_get_attachment_image_src(get_post_thumbnail_id($p->ID), 'thumbnail');
                        $data['icon'] = $img_src[0];
                    } else {
                        $data['icon'] = WPBE_ASSETS_LINK . 'images/not-found.jpg';
                    }
                    $data['link'] = get_post_permalink($p->ID);
                    $results[] = $data;
                }
            }
        }


        wp_die(json_encode($results));
    }

//ajax
    public function wpbe_create_new_post() {

        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            wp_die('0');
        }
        $to_create = intval($_REQUEST['to_create']);
        while ($to_create) {
            wp_insert_post(array(
                'post_type' => $this->settings->current_post_type,
                'post_title' => sprintf(esc_html__('New %s', 'bulk-editor'), $this->settings->current_post_type),
                'post_status' => apply_filters('wpbe_new_post_status', 'draft', $this->settings->current_post_type)
            ));

            $to_create--;
        }

        exit;
    }

//ajax
    public function wpbe_duplicate_posts() {

        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            wp_die('0');
        }
        if (!empty($_REQUEST['posts_ids'])) {

            global $wpdb;

            foreach ($_REQUEST['posts_ids'] as $post_id) {
                $post = get_post($post_id);
                if (isset($post) && $post != null) {

                    $args = array(
                        'comment_status' => $post->comment_status,
                        'ping_status' => $post->ping_status,
                        'post_author' => get_current_user_id(),
                        'post_content' => $post->post_content,
                        'post_excerpt' => $post->post_excerpt,
                        'post_name' => '',
                        'post_parent' => $post->post_parent,
                        'post_password' => $post->post_password,
                        'post_status' => 'publish',
                        'post_title' => $post->post_title . ' (' . esc_html__('copy') . ')',
                        'post_type' => $post->post_type,
                        'to_ping' => $post->to_ping,
                        'menu_order' => $post->menu_order
                    );

                    $new_post_id = wp_insert_post($args);
                    $taxonomies = get_object_taxonomies($post->post_type); //returns array of taxonomy names for post type, ex array("category", "post_tag");

                    if (!empty($taxonomies)) {
                        foreach ($taxonomies as $taxonomy) {
                            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                        }
                    }

                    //***                   

                    $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id={$post_id}");

                    if (count($post_meta_infos) !== 0) {
                        $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)";
                        foreach ($post_meta_infos as $meta_info) {
                            $meta_key = $meta_info->meta_key;
                            if ($meta_key == '_wp_old_slug') {
                                continue;
                            }
                            $meta_value = addslashes($meta_info->meta_value);
                            $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                        }
                        $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                        $wpdb->query($sql_query);
                    }

                    //***

                    $this->posts->update_page_field($new_post_id, 'post_status', 'draft');
                }
            }

            //wp_cache_flush();
        }

        wp_die('done');
    }

//ajax
    public function wpbe_delete_posts() {

        if (!WPBE_HELPER::can_manage_data()) {
            wp_die('0');
        }
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_tools_panel_nonce')) {
            wp_die('0');
        }
        if (!empty($_REQUEST['posts_ids'])) {
            foreach ($_REQUEST['posts_ids'] as $post_id) {
                //wp_delete_post($post_id, false);
                wp_trash_post($post_id);
            }

            //wp_cache_flush();
        }

        wp_die('done');
    }

    public function wrap_field_val($post, $field_key) {

        $res = NULL;

        $post = $this->posts->get_post($post['ID']);
        $post_id = $post['ID'];

        if (isset($this->settings->active_fields[$field_key]['allow_post_types'])) {
            try {
                if (!in_array($this->settings->current_post_type, $this->settings->active_fields[$field_key]['allow_post_types'])) {
                    return WPBE_HELPER::draw_restricked();
                }
            } catch (Exception $e) {
//***
            }
        }

        if (isset($this->settings->active_fields[$field_key]['prohibit_post_types'])) {
            try {
                if (in_array($this->settings->current_post_type, $this->settings->active_fields[$field_key]['prohibit_post_types'])) {
                    return WPBE_HELPER::draw_restricked();
                }
            } catch (Exception $e) {
//***
            }
        }

//***
        $val = '';
        switch ($this->settings->active_fields[$field_key]['field_type']) {
            case 'meta':
                $val = $this->posts->get_post_field($post_id, $field_key);
                break;

            case 'taxonomy':
                $terms = $this->posts->get_post_field($post_id, $field_key);

                $ids = array();
                $titles = array();

//***

                if (!empty($terms)) {
                    foreach ($terms as $t) {
                        $ids[] = $t->term_id;
                        $titles[] = $t->name;
                    }
                }

                if (!empty($ids)) {
                    $ids = array_map(function ($v) {
                        return intval($v);
                    }, $ids);
                }

//***

                if ($this->settings->active_fields[$field_key]['type'] === 'array') {
                    $val = array(
                        'terms_ids' => $ids,
                        'terms_titles' => $titles
                    );

//for drop-down view
                    if ($this->settings->active_fields[$field_key]['edit_view'] == 'select') {
                        $val['selected'] = $val['terms_ids'];
                    }
                } else {
//string, for example: post_type
                    $val = $titles[0];
                }

                break;

            default:
                if (isset($post[$field_key])) {
                    $val = $post[$field_key];
                }
                break;
        }

//***

        switch ($this->settings->active_fields[$field_key]['edit_view']) {
            case 'select':

                $select_options = $this->settings->active_fields[$field_key]['select_options'];

                //as example
                if ($field_key === 'post_status') {
                    //***
                }

                //***

                $res = WPBE_HELPER::draw_select(array(
                            'field' => $field_key,
                            'post_id' => $post_id,
                            'class' => 'wpbe_data_select',
                            'options' => $select_options,
                            'selected' => (isset($val['selected']) ? $val['selected'] : $val),
                            'disabled' => (isset($this->settings->active_fields[$field_key]['disabled']) ? $this->settings->active_fields[$field_key]['disabled'] : FALSE),
                            'onchange' => 'wpbe_act_select(this)'
                ));
                break;

            case 'multi_select':

                $res = WPBE_HELPER::render_html(WPBE_PATH . 'views/elements/multi_select.php', array(
                            'field_key' => $field_key,
                            'post_id' => $post_id,
                            'val' => $val,
                            'active_fields' => $this->settings->active_fields,
                            'post' => $post,
                ));
                break;

            case 'popup':
                $res = WPBE_HELPER::draw_taxonomy_popup_btn($val, $field_key, $post);
                break;

            case 'popupeditor':
                $res = WPBE_HELPER::draw_popup_editor_btn($val, $field_key, $post);
                break;

            case 'meta_popup_editor':
                $res = WPBE_HELPER::draw_meta_popup_editor_btn($field_key, $post_id);
                break;

            case 'thumbnail':
                $thumbnail = wp_get_attachment_image_src($val, 'thumbnail');
                $full = wp_get_attachment_image_src($val, 'full');

                if (!empty($thumbnail)) {
                    $thumbnail = $thumbnail[0];
                    $full = $full[0];
                } else {
                    $thumbnail = WPBE_ASSETS_LINK . 'images/not-found.jpg';
                    $full = WPBE_ASSETS_LINK . 'images/not-found.jpg';
                }

                $onmouseover = '';
                if ($this->settings->show_thumbnail_preview) {
                    $onmouseover = 'onmouseover="wpbe_init_image_preview(this)"';
                }

                $res = '<a href="' . $full . '" onclick="return wpbe_act_thumbnail(this)" ' . $onmouseover . ' title="' . $post['post_title'] . '"><img src="' . $thumbnail . '" class="attachment-thumbnail size-thumbnail" alt="" /></a>';
                break;

            case 'switcher':
                $labels = array_values($this->settings->active_fields[$field_key]['select_options']);
                $values = array_keys($this->settings->active_fields[$field_key]['select_options']);
                if ($val) {//do switcher
                    $val = WPBE_HELPER::over_switcher_val_to_swicher($val, $field_key);
                }
                $res = WPBE_HELPER::draw_advanced_switcher(($val == $values[0] ? TRUE : FALSE), $post_id . '_' . $field_key, $field_key, array('true' => $labels[0], 'false' => $labels[1]), array('true' => $values[0], 'false' => $values[1]), 'yes');
                break;

            case 'calendar':
                if ($this->settings->active_fields[$field_key]['type'] === 'timestamp') {
                    $val = strtotime($val);
                }

                $post_title = $post['post_title'];

                if ($post['post_type'] === 'post_variation') {
                    //removed
                }

                $res = WPBE_HELPER::draw_calendar($post_id, $post_title . ' (' . $this->settings->active_fields[$field_key]['title'] . ')', $field_key, $val);
                break;

            case 'checkbox':
                //using for posts selection
                $res = WPBE_HELPER::draw_checkbox(array(
                            'class' => 'wpbe_post_check',
                            'data-post-id' => $post_id
                ));
                break;

            case 'gallery_popup_editor':

                $images = $this->posts->get_post_field($post_id, $field_key);

                if ($images) {
                    $images = explode(',', $images);
                } else {
                    $images = [];
                }

                return WPBE_HELPER::render_html(WPBE_PATH . 'views/elements/draw_gallery_popup_editor_btn.php', array(
                            'field_key' => $field_key,
                            'post_id' => $post_id,
                            'images' => $images
                ));

                break;

            default:
//textinput
                $sanitize = '';
                if (isset($this->settings->active_fields[$field_key]['sanitize'])) {
                    $sanitize = $this->settings->active_fields[$field_key]['sanitize'];
                }

                $res = $this->posts->__sanitize_answer_value($field_key, $sanitize, $val);

                break;
        }

        //***
        //lets show post ID as LINK to the post
        if ($field_key === 'ID') {
            $class = 'wpbe-id-permalink';
            $title = esc_html__('see the post on the site', 'bulk-editor');
            $res = '<a href="' . get_permalink($res) . '" class="' . $class . '" title="' . $title . '" target="_blank">' . $res . '</a>';
        }

        //***

        return apply_filters('wpbe_wrap_field_val', $res, $post, $field_key);
    }

//ajax
    public function wpbe_create_new_term() {

        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }
        $titles = $_REQUEST['titles'];
        $slugs = $_REQUEST['slugs'];
        $taxonomy = $_REQUEST['tax_key'];

        //***

        if (!empty($titles)) {

            if (substr_count($titles, ',') > 0) {
                $titles = explode(',', $titles);
            } else {
                $titles = array($titles);
            }


            if (substr_count($slugs, ',') > 0) {
                $slugs = explode(',', $slugs);
            } else {
                $slugs = array($slugs);
            }

            //***

            $terms_ids = array();
            foreach ($titles as $k => $t) {
                $t = trim($t);
                if (!term_exists($t, $taxonomy)) {
                    if (!empty($t)) {
                        $res = wp_insert_term($t, $taxonomy, array(
                            'parent' => intval($_REQUEST['parent']),
                            'slug' => (isset($slugs[$k]) ? trim($slugs[$k]) : '')
                        ));
                        $terms_ids[] = $res['term_id'];
                    } else {
                        unset($titles[$k]);
                    }
                }
            }

            //***

            echo json_encode(array(
                'titles' => array_reverse($titles),
                'terms_ids' => array_reverse($terms_ids),
                'terms' => WPBE_HELPER::get_taxonomies_terms_hierarchy($taxonomy)
            ));
        }
        exit;
    }

//do not init functionality on all site pages as it not nessesary
    private function is_should_init() {
//do not onit it exept of one wpbe page and its ajax requests
        $init = isset($_GET['page']) AND $_GET['page'] === 'wpbe';

        if (defined('DOING_AJAX')) {
            if (strpos($_REQUEST['action'], 'wpbe_') !== FALSE) {
                $init = true;
            }
        }

        return $init;
    }

    public function add_statuses($statuses) {

        $statuses['future'] = esc_html__('Scheduled', 'bulk-editor');

        return $statuses;
    }

    public function wpbe_delete_tax_term() {
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }
        $term_id = (int) $_REQUEST['term_id'];
        $taxonomy = sanitize_text_field(trim($_REQUEST['tax_key']));
        if (!taxonomy_exists($taxonomy)) {
            die('Wrong taxonomy name.');
        }
        $result = wp_delete_term($term_id, $taxonomy);
        // check the result
        if (is_wp_error($result)) {

            die('error wp_update_term');
        } else {

            echo json_encode(WPBE_HELPER::get_taxonomies_terms_hierarchy($taxonomy));
        }
        exit;
    }

    public function wpbe_update_tax_term() {
        if (!isset($_REQUEST['wpbe_nonce']) || !wp_verify_nonce($_REQUEST['wpbe_nonce'], 'wpbe_field_update')) {
            die('Forbidden!');
        }
        
        $term_id = (int) $_REQUEST['term_id'];
        $title = sanitize_textarea_field($_REQUEST['title']);
        $slug = sanitize_textarea_field($_REQUEST['slug']);
        $description = sanitize_textarea_field($_REQUEST['description'] ?? '');
        $parent = (int) $_REQUEST['parent'];
        $taxonomy = sanitize_text_field(trim($_REQUEST['tax_key']));
        if (!taxonomy_exists($taxonomy)) {
            die('Wrong taxonomy name.');
        }

        $result = wp_update_term($term_id, $taxonomy, [
            'name' => $title,
            'slug' => $slug,
            'description' => $description,
            'parent' => $parent
        ]);

        // check the result
        if (is_wp_error($result)) {

            die('error wp_update_term');
        } else {

            echo json_encode(WPBE_HELPER::get_taxonomies_terms_hierarchy($taxonomy));
        }
        exit;
    }

    public function wp_editor_compatibility() {
        if (!isset($_GET['wpbe_popup_editor']) || $_GET['wpbe_popup_editor'] != 1) {
            return;
        }
        ?>
        <style>
            .edit-post-header__settings .editor-post-switch-to-draft{
                display: none;
            }
            .edit-post-header__settings .block-editor-post-preview__dropdown{
                display: none;
            }
            .edit-post-header__settings .editor-post-publish-button{
                display: none;
            }
            .edit-post-sidebar .components-panel .components-panel__body {
                display: none;
            }
            .edit-post-header .edit-post-fullscreen-mode-close{
                display: none;
            }
            .edit-post-header__settings .interface-more-menu-dropdown {
                display: block;
            }
        </style>
        <script>

            function wpbe_popup_editor_get_content() {
                return wp.data.select("core/editor").getEditedPostContent();
            }
        </script>
        <?php
    }

    public function ask_favour() {
        if (get_option('wpbe_rate_alert', 0)) {
            //old rate system mark for already set review users
            return;
        }

        $slug = strtolower(get_class($this));

        add_action("wp_ajax_{$slug}_dismiss_rate_alert", function () use ($slug) {
            update_option("{$slug}_dismiss_rate_alert", 2);
        });

        add_action("wp_ajax_{$slug}_later_rate_alert", function () use ($slug) {
            update_option("{$slug}_later_rate_alert", time() + 4 * 7 * 24 * 60 * 60); //4 weeks
        });

        //+++

        add_action('admin_notices', function () use ($slug) {

            if (!current_user_can('manage_options')) {
                return; //show to admin only
            }

            if (intval(get_option("{$slug}_dismiss_rate_alert", 0)) === 2) {
                return;
            }

            if (intval(get_option("{$slug}_later_rate_alert", 0)) === 0) {
                update_option("{$slug}_later_rate_alert", time() + 1 * 3 * 24 * 60 * 60); //3 days after install
                return;
            }

            if (intval(get_option("{$slug}_later_rate_alert", 0)) > time()) {
                return;
            }

            $link = 'https://codecanyon.net/downloads#item-24376112';
            $on = 'CodeCanyon';
            if ($this->show_notes) {
                $link = 'https://wordpress.org/support/plugin/bulk-editor/reviews/?filter=5#new-post';
                $on = 'WordPress';
            }
            ?>
            <div class="notice notice-info" id="pn_<?php echo $slug ?>_ask_favour" style="position: relative;">
                <button onclick="javascript: pn_<?php echo $slug ?>_dismiss_review(1);
                                    void(0);" title="<?php _e('Later', 'bulk-editor'); ?>" class="notice-dismiss"></button>
                <div id="pn_<?php echo $slug ?>_review_suggestion">
                    <p><?php _e('Hi! Are you enjoying using WOLF – WordPress Posts Bulk Editor Professional?', 'bulk-editor'); ?></p>
                    <p><a href="javascript: pn_<?php echo $slug ?>_set_review(1); void(0);"><?php _e('Yes, I love it', 'bulk-editor'); ?></a> 🙂 | <a href="javascript: pn_<?php echo $slug ?>_set_review(0); void(0);"><?php _e('Not really...', 'bulk-editor'); ?></a></p>
                </div>

                <div id="pn_<?php echo $slug ?>_review_yes" style="display: none;">
                    <p><?php printf(__('That\'s awesome! Could you please do us a BIG favor and give it a 5-star rating on %s to help us spread the word and boost our motivation?', 'bulk-editor'), $on) ?></p>
                    <p style="font-weight: bold;">~ PluginUs.NET developers team</p>
                    <p>
                        <a href="<?php echo $link ?>" style="display: inline-block; margin-right: 10px;" onclick="pn_<?php echo $slug ?>_dismiss_review(2)" target="_blank"><?php esc_html_e('Okay, you deserve it', 'bulk-editor'); ?></a>
                        <a href="javascript: pn_<?php echo $slug ?>_dismiss_review(1); void(0);" style="display: inline-block; margin-right: 10px;"><?php esc_html_e('Nope, maybe later', 'bulk-editor'); ?></a>
                        <a href="javascript: pn_<?php echo $slug ?>_dismiss_review(2); void(0);"><?php esc_html_e('I already did', 'bulk-editor'); ?></a>
                    </p>
                </div>

                <div id="pn_<?php echo $slug ?>_review_no" style="display: none;">
                    <p><?php _e('We are sorry to hear you aren\'t enjoying WOLF. We would love a chance to improve it. Could you take a minute and let us know what we can do better?', 'bulk-editor'); ?></p>
                    <p>
                        <a href="https://pluginus.net/contact-us/" onclick="pn_<?php echo $slug ?>_dismiss_review(2)" target="_blank"><?php esc_html_e('Give Feedback', 'bulk-editor'); ?></a>&nbsp;
                        |&nbsp;<a href="javascript: pn_<?php echo $slug ?>_dismiss_review(2); void(0);"><?php esc_html_e('No thanks', 'bulk-editor'); ?></a>
                    </p>
                </div>


                <script>
                    function pn_<?php echo $slug ?>_set_review(yes) {
                        document.getElementById('pn_<?php echo $slug ?>_review_suggestion').style.display = 'none';
                        if (yes) {
                            document.getElementById('pn_<?php echo $slug ?>_review_yes').style.display = 'block';
                        } else {
                            document.getElementById('pn_<?php echo $slug ?>_review_no').style.display = 'block';
                        }
                    }

                    function pn_<?php echo $slug ?>_dismiss_review(what = 1) {
                        //1 maybe later, 2 do not ask more
                        jQuery('#pn_<?php echo $slug ?>_ask_favour').fadeOut();

                        if (what === 1) {
                            jQuery.post(ajaxurl, {
                                action: '<?php echo $slug ?>_later_rate_alert'
                            });
                        } else {
                            jQuery.post(ajaxurl, {
                                action: '<?php echo $slug ?>_dismiss_rate_alert'
                            });
                        }

                        return true;
                    }
                </script>
            </div>
            <?php
        });
    }
}

//***

$WPBE = new WPBE();
$GLOBALS['WPBE'] = $WPBE;
add_action('init', array($WPBE, 'init'), 9999);

