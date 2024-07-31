<?php
/**
* The MileniaAdmin class.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if( !class_exists('MileniaAdmin') && interface_exists('ConfigurableInterface') ) {
	class MileniaAdmin implements ConfigurableInterface
	{
		protected $configurator;

	    public function __construct(ConfigurableInterface $configurator) {
			$this->configurator = $configurator;

			add_filter('admin_enqueue_scripts', array(&$this, 'registerAdminAssets'));
			add_filter('page_row_actions', array(&$this, 'updatePagesRowActions'), 99, 2);
			add_action('init', array(&$this, 'modifyAdminPagesOnInit'));
	    }

		/**
	    * Returns an option value.
	    *
	    * @param string $name - the option name
		* @param mixed $fallback - fallback value
	    * @param array $data - additional data for getting the option
	    * @access public
	    * @return mixed
	    */
	    public function getOption($name, $fallback = '', array $data = array()) {
			return $this->configurator->getOption($name, $fallback, $data);
		}

		/**
	    * Sets an option value programmatically.
	    *
	    * @param string $name - the option name
		* @param mixed $value - value of the option
	    * @access public
	    * @return void
	    */
	    public function setOption($name, $value) {
			return $this->configurator->setOption($name, $value);
		}

		/**
		 * Registers necessary admin assets.
		 *
		 * @access public
		 */
		public function registerAdminAssets()
		{
			$this->registerStyles()->registerScripts()->registerInlineScripts();
		}

		/**
		 * Registers necessary admin stylesheets.
		 *
		 * @access protected
		 * @return AdminInterface
		 */
		protected function registerStyles()
		{
			wp_enqueue_style('milenia-icons', get_template_directory_uri() . '/assets/css/milenia-icon-font.css', null, '1.0.0', 'all' );
			wp_enqueue_style('milenia-admin-core-css', get_template_directory_uri() . '/admin/assets/css/admin.milenia.core.css', null, '1.0.0');

			return $this;
		}

		/**
		 * Registers necessary admin scripts.
		 *
		 * @access protected
		 * @return AdminInterface
		 */
		protected function registerScripts()
		{
			wp_enqueue_script('milenia-admin-core', get_template_directory_uri() . '/admin/assets/js/admin.milenia.core.js', array('jquery'), '1.0.0', true);

			return $this;
		}

		/**
		 * Registers necessary admin scripts.
		 *
		 * @access protected
		 * @return AdminInterface
		 */
		protected function registerInlineScripts()
		{
			if(is_admin())
			{
				wp_localize_script('milenia-admin-core', 'MileniaAdminLocalization', array(
					'custom_uploader_title' => esc_html__('Insert image', 'milenia'),
					'custom_uploader_button_text' => esc_html__('Use this image', 'milenia')
				));
			}

			return $this;
		}

		/**
		 * Makes some changes in some admin pages.
		 *
		 * @access public
		 */
		public function modifyAdminPagesOnInit()
		{
			remove_post_type_support('page', 'thumbnail');
		}

		/**
		 * Updates row of post actions.
		 *
		 * @param array $actions
		 * @param WP_Post $post
		 * @access public
		 */
		public function updatePagesRowActions(array $actions, WP_Post $post)
		{
			$page_type = get_post_meta($post->ID, 'milenia-page-type-individual', true);

			if(in_array($page_type, array('milenia-fullpage-fixed-image', 'milenia-fullpage', 'milenia-blogroll', 'milenia-portfolio-gallery')) && isset($actions['edit_vc'])) {
				unset($actions['edit_vc']);
			}

			return $actions;
		}
	}
}
?>
