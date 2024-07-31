<?php
/**
 * The MileniaFunctionality class.
 *
 * This class is responsible to add necessary functionality to the Milenia theme.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia-app-textdomain') );
}

if( !class_exists('MileniaFunctionality') )
{
    class MileniaFunctionality
    {
        /**
         * Contains an instances that implement MileniaCustomPostTypeInterface.
         *
         * @access protected
         * @var array
         */
        protected $custom_post_types = array();

        /**
         * Contains an instance that implements MetaBoxRegistratorInterface.
         *
         * @access protected
         * @var MetaBoxRegistratorInterface
         */
        protected $MetaBoxRegistrator;

        /**
         * The class constructor.
         */
        public function __construct(MetaBoxRegistratorInterface $MetaBoxRegistrator = null)
        {
            $this->MetaBoxRegistrator = $MetaBoxRegistrator;

            add_action('plugins_loaded', array(&$this, 'loadTextDomain'));
			add_action('wp_enqueue_scripts', array($this, 'registerAssets'));

			if( class_exists('ReduxFramework') ) {
				add_action("redux/extensions/milenia_settings/before", array( $this, 'register_custom_extension_loader' ), 0);
			}
        }

		/**
		 * Registers assets.
		 *
		 * @access protected
	     * @return MileniaFunctionality
		 */
	    public function registerAssets()
		{
			// order is important
	        return $this->registerStyles()
						->registerScripts()
						->registerInlineScripts();
	    }

		/**
		 * Registers necessary styles.
		 *
		 * @access protected
	     * @return MileniaFunctionality
		 */
	    protected function registerStyles()
		{
			wp_enqueue_style( 'milenia_form_styler', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/jQueryFormStyler/jquery.formstyler.css', null, '1.0.0' );
			wp_enqueue_style('milenia-theme-functionality-css', MILENIA_FUNCTIONALITY_URL . '/assets/css/milenia-theme-functionality-core.css', null, '1.0.0');

			if(is_rtl())
			{
				wp_enqueue_style('milenia-theme-functionality-css-rtl', MILENIA_FUNCTIONALITY_URL . '/assets/css/milenia-theme-functionality-core-rtl.css', null, '1.0.0');
			}

			if(is_singular('mphb_room_type')) {
				wp_enqueue_style('milenia-jquery-jrevslider-css', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/revolution/css/settings.css', null, '5.4.5');
			}

	        return $this;
	    }

		/**
		 * Registers necessary scripts.
		 *
		 * @access protected
	     * @return MileniaFunctionality
		 */
	    protected function registerScripts()
		{
			global $post, $Milenia;

			if(defined('ICL_LANGUAGE_CODE')) {
				wp_enqueue_script('moment-locale', MILENIA_FUNCTIONALITY_URL . 'assets/vendors/moment/locale/' . ICL_LANGUAGE_CODE . '.js',  array('momentjs'), '1.0.0', true);
			}
			elseif($Milenia && $Milenia->getThemeOption('milenia-moment-localization', 'en') !== 'en') {
				wp_enqueue_script('moment-locale', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/moment/locale/' . $Milenia->getThemeOption('milenia-moment-localization', 'en') . '.js',  array('momentjs'), '1.0.0', true);
			}

//			wp_register_script('instafeed', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/instafeed.min.js', null, '1.3.3', true);
//	        wp_register_script('instafeed-wrapper', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/instafeed.wrapper.min.js', null, '1.0.0', true);

			wp_enqueue_script( 'milenia_form_styler', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/jQueryFormStyler/jquery.formstyler.min.js', array( 'jquery' ), true);
			wp_enqueue_script( 'milenia-theme-functionality', MILENIA_FUNCTIONALITY_URL . '/assets/js/milenia-theme-functionality-core.js', array('jquery'), '1.0.0', true);

			if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'tribe_events') ) {
				wp_enqueue_script( 'milenia-tribe-events-helpers', MILENIA_FUNCTIONALITY_URL . '/assets/js/milenia-tribe-events-helpers.js', array('jquery'), '1.0.0', true);
			}

			if(is_singular('mphb_room_type')) {
				wp_enqueue_script( 'milenia-jquery-revslider-tools', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/revolution/js/jquery.themepunch.tools.min.js', array('jquery'), '1.0.0', true);
				wp_enqueue_script( 'milenia-jquery-jrevslider', MILENIA_FUNCTIONALITY_URL . '/assets/vendors/revolution/js/jquery.themepunch.revolution.min.js', array('jquery'), '5.4.8', true);
				wp_enqueue_script( 'milenia-mphb-room-type-helpers', MILENIA_FUNCTIONALITY_URL . '/assets/js/milenia-theme-mphb-room-type-helpers.js', array('jquery'), '1.0.0', true);
			}

	        return $this;
	    }

		/**
		 * Registers necessary inline scripts.
		 *
		 * @access protected
	     * @return MileniaFunctionality
		 */
	    protected function registerInlineScripts()
		{
			wp_localize_script('milenia-theme-functionality', 'MileniaFunctionalityAJAXData', array(
				'url' => admin_url('admin-ajax.php'),
				'AJAX_token' => wp_create_nonce('milenia-functionality-ajax-nonce')
			));

			wp_localize_script('milenia-theme-functionality', 'MileniaCountdownLocalization', array(
				'labels' => array(
					esc_html__('Years', 'milenia-app-textdomain'),
					esc_html__('Month', 'milenia-app-textdomain'),
					esc_html__('Weeks', 'milenia-app-textdomain'),
					esc_html__('Days', 'milenia-app-textdomain'),
					esc_html__('Hours', 'milenia-app-textdomain'),
					esc_html__('Minutes', 'milenia-app-textdomain'),
					esc_html__('Seconds', 'milenia-app-textdomain')
				)
			));

	        return $this;
	    }

        /**
         * Loads text domain of the plugin.
         *
         * @access public
         */
        public function loadTextDomain()
        {
            load_plugin_textdomain( 'milenia-app-textdomain', false, MILENIA_FUNCTIONALITY_ROOT . '/languages/' );
        }

        /**
         * Registers new meta boxes.
         *
         * @param array $meta_boxes
         * @access public
         * @return MileniaFunctionality
         */
        public function registerMetaBoxes( $meta_boxes = array() )
        {
            if(isset($this->MetaBoxRegistrator))
            {
                $this->MetaBoxRegistrator->register( $meta_boxes );
            }
            return $this;
        }

        /**
         * Registers new post types.
         *
         * @param string $name
         * @param array $args
         * @access public
         * @return MileniaFunctionality
         */
        public function registerCustomPostType($name, array $args )
        {
			$CustomPostType = new MileniaPostType($name, $args);

			array_push($this->custom_post_types, $CustomPostType);

			return $CustomPostType;
        }

		/**
		 * Registers a loader of custom Redux Framework extensions.
		 *
		 * @param ReduxFramework $ReduxFramework
		 * @access public
		 * @return void
		 */
		public function register_custom_extension_loader($ReduxFramework)
		{
			$path = MILENIA_FUNCTIONALITY_ROOT . 'extensions/';
			$folders = scandir( $path, 1 );




			foreach($folders as $folder) {

				if ($folder === '.' or $folder === '..' or !is_dir($path . $folder)) {
					continue;
				}


				$extension_class = 'ReduxFramework_Extension_' . $folder;

				if (!class_exists( $extension_class)) {
					// In case you wanted override your override, hah.
					$class_file = $path . $folder . '/extension_' . $folder . '.php';
					$class_file = apply_filters( 'redux/extension/' . $ReduxFramework->args['opt_name'] . '/' . $folder, $class_file );

					if ($class_file) {
						require_once($class_file);
					}
				}
				if (!isset($ReduxFramework->extensions[ $folder ])) {
					$ReduxFramework->extensions[ $folder ] = new $extension_class( $ReduxFramework );
				}
			}
		}
    }
}
?>
