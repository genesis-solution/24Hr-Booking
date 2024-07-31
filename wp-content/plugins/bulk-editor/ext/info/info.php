<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

final class WPBE_INFO extends WPBE_EXT {

    protected $slug = 'info'; //unique

    public function __construct() {
        //tabs
        $this->add_tab($this->slug, 'panel', esc_html__('Help', 'bulk-editor'), 'info');
        add_action('wpbe_ext_panel_' . $this->slug, array($this, 'wpbe_ext_panel'), 1);
    }

    public function wpbe_ext_scripts() {
        ?>
        <script>
            lang.<?php echo $this->slug ?> = {};
            //lang.<?php echo $this->slug ?>.test = '<?php esc_html_e('test', 'bulk-editor') ?> ...';
        </script>
        <?php
    }

    public function wpbe_ext_panel() {
        $data = array();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

}
