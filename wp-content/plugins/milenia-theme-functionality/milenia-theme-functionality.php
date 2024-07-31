<?php
/**
 * Plugin Name: Milenia Theme - Functionality
 * Description: Adds functionality to Milenia Theme.
 * Version: 1.2.7
 * Author: Monkeysan
 * Author URI: https://themeforest.net/user/monkeysan/portfolio
 * License: GPL2
 */

use Milenia\Core\App;
use Milenia\Core\Support\ServiceProvider\ServiceProviderRunner;

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

define('MILENIA_FUNCTIONALITY_ROOT', plugin_dir_path(__FILE__));
define('MILENIA_FUNCTIONALITY_CORE', MILENIA_FUNCTIONALITY_ROOT . 'core/');
define('MILENIA_FUNCTIONALITY_URL', plugin_dir_url(__FILE__));

if( !function_exists('milenia_theme_functionality_loading') ) {
    function milenia_theme_functionality_loading() {
        global $MileniaFunctionality;
        load_plugin_textdomain( 'milenia-app-textdomain', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

        require_once(MILENIA_FUNCTIONALITY_ROOT . 'vendor/autoload.php');

        // bootstrap
        App::bind('config', require MILENIA_FUNCTIONALITY_CORE . 'config.php');
        ServiceProviderRunner::init();


        $milenia_theme_functionality_work_directories = array('includes', 'interfaces', 'classes', 'classes/repositories');

        // Autoloading of the necessary files
        foreach($milenia_theme_functionality_work_directories as $directory) {
            $files = glob(MILENIA_FUNCTIONALITY_ROOT . $directory . DIRECTORY_SEPARATOR . '*.php');
            if($files) {
                foreach($files as $file) {
                    if(is_file($file) && is_readable($file)) require_once($file);
                }
            }
        }

        // Configuration of the theme functionality
        $MileniaFunctionality = new MileniaFunctionality(new MileniaMetaBoxRegistrator());

        // Initialization of the theme functionality
        require_once(MILENIA_FUNCTIONALITY_ROOT . 'milenia-meta-boxes-init.php');
        require_once(MILENIA_FUNCTIONALITY_ROOT . 'milenia-gallery-builders-init.php');
    }
}

add_filter('plugins_loaded', 'milenia_theme_functionality_loading');
?>
