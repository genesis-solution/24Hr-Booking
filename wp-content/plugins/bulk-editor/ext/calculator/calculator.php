<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//calculator for numerical textinputs
final class WPBE_CALCULATOR extends WPBE_EXT {

    protected $slug = 'calculator'; //unique

    public function __construct() {
        add_action('wpbe_ext_scripts', array($this, 'wpbe_ext_scripts'), 1);
        add_action('wpbe_page_end', array($this, 'wpbe_page_end'), 1);
    }

    public function wpbe_ext_scripts() {
        wp_enqueue_script('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/js/' . $this->slug . '.js', [], WPBE_VERSION);
        wp_enqueue_style('wpbe_ext_' . $this->slug, $this->get_ext_link() . 'assets/css/' . $this->slug . '.css', [], WPBE_VERSION);
    }

    public function wpbe_page_end() {
        $data = array();
        echo WPBE_HELPER::render_html($this->get_ext_path() . 'views/panel.php', $data);
    }

}
