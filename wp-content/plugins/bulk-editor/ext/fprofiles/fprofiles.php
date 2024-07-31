<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//Profiles of filter sets
final class WPBE_FPROFILES extends WPBE_EXT {

    protected $slug = 'fprofiles'; //unique
    protected $fprofiles = null;

    public function __construct() {
        include_once $this->get_ext_path() . 'models/profiles.php';
        $this->fprofiles = new WPBE_FILTER_PROFILES($this->settings);

        add_action('wpbe_ext_scripts', array($this, 'wpbe_ext_scripts'), 1);
        add_action('wpbe_tools_panel_buttons', array($this, 'wpbe_tools_panel_buttons'), 1);
        add_action('wpbe_page_end', array($this, 'wpbe_page_end'), 1);
    }

    public function wpbe_ext_scripts() {
        wp_enqueue_script('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/js/' . $this->slug . '.js',[],WPBE_VERSION);
        wp_enqueue_style('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/css/' . $this->slug . '.css',[],WPBE_VERSION);
        ?>
        <script>
            lang.<?php echo $this->slug ?> = {};
            //lang.<?php echo $this->slug ?>.test = '<?php esc_html_e('test', 'bulk-editor') ?>';
        </script>
        <?php
    }

    public function wpbe_tools_panel_buttons() {
        ?>
        <a href="#" class="button button-secondary wpbe_tools_panel_fprofile_btn" title="<?php esc_html_e('Filters profiles', 'bulk-editor') ?>"></a>
        <?php
    }

    public function wpbe_page_end() {
        $data = array();
        $data['fprofiles'] = $this->fprofiles->get();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

}
