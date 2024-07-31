<?php
/**
* The MileniaConfigurator class.
*
* This class is responsible to manage theme options panel.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if(interface_exists('ConfigurableInterface')) {

	class MileniaConfigurator implements ConfigurableInterface
	{
		/**
		 * Contains an instance of the ReduxFramework class.
		 *
		 * @access protected
		 * @var ReduxFramework
		 */
	    protected $ReduxFramework;

		/**
		 * Contains information about the theme.
		 *
		 * @access protected
		 * @var object
		 */
	    protected $theme;

		/**
		 * Contains all sections of the theme options panel.
		 *
		 * @access protected
		 * @var array
		 */
	    protected $sections = array();

		/**
		 * Contains all parameters for the ReduxFramework instance.
		 *
		 * @access protected
		 * @var array
		 */
	    protected $args = array();


		/**
		 * Contains options that have been setted programmatically.
		 *
		 * @access protected
		 * @var array
		 */
		protected $setted_settings = array();

		/**
		 * Constructor of the class.
		 */
	    public function __construct()
		{
	        if( !class_exists( 'ReduxFramework' ) || get_option('milenia_init_theme', '0') != '1' ) return;

	        $this->theme = wp_get_theme();

	        $this->sections = $this->buildSections( array('general', 'preloader', 'sitelogo', 'appearance', 'header', 'breadcrumb', 'footer', 'blog', 'pages', '404', '3dPartyAPI', 'Localization', 'WooCommerce') );
	        $this->args = $this->setArgs();

	        if( !isset($this->args['opt_name']) ) return;

	        $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
	    }

		/**
		* Sets an option value programmatically.
		*
		* @param string $name - the option name
		* @param mixed $value - value of the option
		* @access public
		* @return void
		*/
		public function setOption($name, $value)
		{
			$this->setted_settings[$name] = $value;
		}

		/**
		 * Returns the ReduxFramework instance.
		 *
		 * @access public
		 * @return ReduxFramework
		 */
	    public function getReduxInstance()
		{
	        return $this->ReduxFramework;
	    }

		/**
		 * Packs all sections of the theme options panel to dedicated array.
		 *
		 * @param array $sections
		 * @access protected
		 * @return array
		 */
		protected function buildSections( $sections = array() ) {
			$sections_ready = array();

			foreach($sections as $section) {
				if(method_exists($this, 'section' . ucwords($section))) {
					$sections_ready = array_merge($sections_ready, call_user_func(array($this, 'section'.ucwords($section))));
				}
			}

			return $sections_ready;
		}

		/**
		 * Describes the 'Preloader' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionPreloader()
		{
			return array(
				array(
					'icon' => 'el-icon-dashboard',
					'icon_class' => 'icon',
					'title' => esc_html__('Preloader', 'milenia'),
					'fields' => array(
						array(
							'id' => 'page-loader-state',
							'type' => 'switch',
							'title' => esc_html__( 'State', 'milenia' ),
							'default' => true,
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
							'desc' => esc_html__('Choose the "Show" option if you want to show preloader while a page is loading.', 'milenia')
						)
					)
				)
			);
		}

		/**
		 * Describes the 'Preloader' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionSitelogo()
		{
			return array(
				array(
					'icon' => 'el-icon-picture',
					'icon_class' => 'icon',
					'title' => esc_html__('Site logo', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-logo',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'title' => esc_html__('Logo', 'milenia'),
							'default' => array(
								'url' => MILENIA_TEMPLATE_DIRECTORY_URI . '/assets/images/logo-brown.png'
							)
						),
						array(
							'id'       => 'milenia-logo-hidpi',
							'type'     => 'media',
							'url'      => true,
							'readonly' => false,
							'title'    => esc_html__( 'Logo HiDPI', 'milenia' ),
							'default'  => array(
								'url' => MILENIA_TEMPLATE_DIRECTORY_URI . '/assets/images/logo-brown@2x.png'
							)
						),
					)
				),
				array(
					'subsection' => true,
					'title' => esc_html__('Favicon', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-favicon',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'title' => esc_html__('Favicon', 'milenia'),
							'desc' => esc_html__('Will be displayed in the site tab.', 'milenia')
						),
						array(
							'id' => 'milenia-favicon-divider',
							'type' => 'divider'
						),
						array(
							'id' => 'milenia-apple-touch-icon-info',
							'type' => 'info',
							'title' => esc_html__('Apple touch icon will be displayed at the desktop of an apple device in case user add the site to the desktop.', 'milenia'),
							'notice' => false
						),
						array(
							'id' => 'milenia-apple-touch-icon',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'title' => esc_html__('Apple touch icon', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-57x57',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 57,
							'height' => 57,
							'title' => esc_html__('Apple touch icon (57x57)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-72x72',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 72,
							'height' => 72,
							'title' => esc_html__('Apple touch icon (72x72)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-76x76',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 76,
							'height' => 76,
							'title' => esc_html__('Apple touch icon (76x76)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-114x114',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 114,
							'height' => 114,
							'title' => esc_html__('Apple touch icon (114x114)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-120x120',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 120,
							'height' => 120,
							'title' => esc_html__('Apple touch icon (120x120)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-144x144',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 144,
							'height' => 144,
							'title' => esc_html__('Apple touch icon (144x144)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-152x152',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 152,
							'height' => 152,
							'title' => esc_html__('Apple touch icon (152x152)', 'milenia')
						),
						array(
							'id' => 'milenia-apple-touch-icon-180x180',
							'type' => 'media',
							'url'=> true,
							'readonly' => false,
							'width' => 180,
							'height' => 180,
							'title' => esc_html__('Apple touch icon (180x180)', 'milenia')
						)
					)
	            )
			);
		}

		/**
		 * Describes the 'General' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionGeneral() {
			return array(
				array(
					'icon' => 'el-icon-wrench',
					'icon_class' => 'icon',
					'title' => esc_html__('General', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-border-layout',
							'type' => 'switch',
							'title' => esc_html__('Offset around pages', 'milenia'),
							'on' => esc_html__('Yes', 'milenia'),
							'off' => esc_html__('No', 'milenia'),
							'default' => false
						),
						array(
							'id' => 'milenia-phone',
							'type' => 'text',
							'title' => esc_html__('Phone number', 'milenia'),
							'default' => ''
						),
						array(
							'id' => 'milenia-email',
							'type' => 'text',
							'title' => esc_html__('Email address', 'milenia'),
							'default' => ''
						),
						array(
							'id' => 'milenia-social-links-facebook',
							'type' => 'text',
							'title' => esc_html__('Facebook profile', 'milenia'),
							'desc' => esc_html__('Paste the Facebook profile link.', 'milenia'),
							'default' => '#'
						),
						array(
							'id' => 'milenia-social-links-google-plus',
							'type' => 'text',
							'title' => esc_html__('Google+', 'milenia'),
							'desc' => esc_html__('Paste the Google+ profile link.', 'milenia'),
							'default' => '#'
						),
						array(
							'id' => 'milenia-social-links-twitter',
							'type' => 'text',
							'title' => esc_html__('Twitter profile', 'milenia'),
							'desc' => esc_html__('Paste the Twitter profile link.', 'milenia'),
							'default' => '#'
						),
						array(
							'id' => 'milenia-social-links-tripadvisor',
							'type' => 'text',
							'title' => esc_html__('TripAdvisor profile', 'milenia'),
							'desc' => esc_html__('Paste the TripAdvisor profile link.', 'milenia'),
							'default' => '#'
						),
						array(
							'id' => 'milenia-social-links-instagram',
							'type' => 'text',
							'title' => esc_html__('Instagram profile', 'milenia'),
							'desc' => esc_html__('Paste the Instagram profile link.', 'milenia'),
							'default' => '#'
						),
						array(
							'id' => 'milenia-social-links-youtube',
							'type' => 'text',
							'title' => esc_html__('YouTube chanel', 'milenia'),
							'desc' => esc_html__('Paste the YouTube chanel link.', 'milenia'),
							'default' => null
						),
						array(
							'id' => 'milenia-social-links-flickr',
							'type' => 'text',
							'title' => esc_html__('Flickr profile', 'milenia'),
							'desc' => esc_html__('Paste the Flickr profile link.', 'milenia'),
							'default' => null
						),
						array(
							'id' => 'milenia-social-links-booking',
							'type' => 'text',
							'title' => esc_html__('Booking', 'milenia'),
							'desc' => esc_html__('Booking profile link.', 'milenia'),
							'default' => null
						),
						array(
							'id' => 'milenia-social-links-airbnb',
							'type' => 'text',
							'title' => esc_html__('Airbnb', 'milenia'),
							'desc' => esc_html__('Airbnb profile link.', 'milenia'),
							'default' => null
						),
						array(
							'id' => 'milenia-social-links-whatsapp',
							'type' => 'text',
							'title' => esc_html__('WhatsApp', 'milenia'),
							'desc' => esc_html__('WhatsApp profile link.', 'milenia'),
							'default' => null
						),
						array(
							'id' => 'responsive-info',
							'type' => 'info',
							'title' => esc_html__('Responsive', 'milenia'),
							'notice' => false
						),
						array(
							'id' => 'milenia-mobile-breakpoint',
							'type' => 'text',
							'title' => esc_html__('Mobile breakpoint', 'milenia'),
							'default' => '767'
						),
					)
				)
			);
		}

		/**
		 * Describes the 'Apperance' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionAppearance() {
			return array(
				array(
					'icon' => 'el-icon-broom',
					'icon_class' => 'icon',
					'title' => esc_html__('Appearance', 'milenia')
				),
	            array(
	                'icon_class' => 'icon',
					'icon' => 'el-icon-font',
					'subsection' => true,
					'title' => esc_html__('Typography', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-google-charsets-state',
							'type' => 'switch',
							'title' => esc_html__('Select Google Font character sets', 'milenia'),
							'default' => false,
							'on' => esc_html__('Yes', 'milenia'),
							'off' => esc_html__('No', 'milenia'),
						),
						array(
							'id' => 'milenia-google-charsets',
							'type' => 'button_set',
							'title' => esc_html__('Google Font character sets', 'milenia'),
							'multi' => true,
							'required' => array('milenia-google-charsets-state', 'equals', true),
							'options'=> array(
								'cyrillic' => 'Cyrrilic',
								'cyrillic-ext' => 'Cyrrilic Extended',
								'greek' => 'Greek',
								'greek-ext' => 'Greek Extended',
								'khmer' => 'Khmer',
								'latin' => 'Latin',
								'latin-ext' => 'Latin Extneded',
								'vietnamese' => 'Vietnamese'
							),
							'default' => array('latin','latin-ext')
						),
						array(
							'id' => 'body-font',
							'type' => 'typography',
							'title' => esc_html__('Body font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Open Sans',
								'font-style' => 'normal',
								'font-size' => '16px',
								'line-height' => '26px'
							)
						),
						array(
							'id' => 'firt-accented-font-info',
							'type' => 'info',
							'title' => esc_html__('Headings, blockquotes, accordion panels, etc.', 'milenia'),
							'notice' => false
						),
						array(
							'id' => 'first-accented-font',
							'type' => 'typography',
							'title' => esc_html__('First accented font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'font-weight' => false,
							'font-size' => false,
							'line-height' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-family' => 'Playfair Display'
							)
						),
						array(
							'id' => 'second-accented-font-info',
							'type' => 'info',
							'title' => esc_html__('Prices', 'milenia'),
							'notice' => false
						),
						array(
							'id' => 'second-accented-font',
							'type' => 'typography',
							'title' => esc_html__('Second accented font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'font-weight' => false,
							'font-size' => false,
							'line-height' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-family' => 'Old Standard TT'
							)
						),
						array(
							'id' => 'h1-font',
							'type' => 'typography',
							'title' => esc_html__('Heading 1 font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Playfair Display',
								'font-style' => 'normal',
								'font-size' => '52px',
								'line-height' => '62px'
							)
						),
						array(
							'id' => 'h2-font',
							'type' => 'typography',
							'title' => esc_html__('Heading 2 font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Playfair Display',
								'font-style' => 'normal',
								'font-size' => '48px',
								'line-height' => '58px'
							)
						),
						array(
							'id' => 'h3-font',
							'type' => 'typography',
							'title' => esc_html__('Heading 3 font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Playfair Display',
								'font-style' => 'normal',
								'font-size' => '36px',
								'line-height' => '43px'
							)
						),
						array(
							'id' => 'h4-font',
							'type' => 'typography',
							'title' => esc_html__('Heading 4 font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Playfair Display',
								'font-style' => 'normal',
								'font-size' => '30px',
								'line-height' => '36px'
							)
						),
						array(
							'id' => 'h5-font',
							'type' => 'typography',
							'title' => esc_html__('Heading 5 font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Playfair Display',
								'font-style' => 'normal',
								'font-size' => '24px',
								'line-height' => '29px'
							)
						),
						array(
							'id' => 'h6-font',
							'type' => 'typography',
							'title' => esc_html__('Heading 6 font', 'milenia'),
							'google' => true,
							'subsets' => false,
							'font-style' => false,
							'text-align' => false,
							'color' => false,
							'default'=> array(
								'google' => true,
								'font-weight' => '400',
								'font-family' => 'Playfair Display',
								'font-style' => 'normal',
								'font-size' => '18px',
								'line-height' => '22px'
							)
						)
					)
	            ),
				array(
	                'icon_class' => 'icon',
					'icon' => 'el-icon-brush',
					'subsection' => true,
					'title' => esc_html__('Color Scheme', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-theme-skin-custom-state',
							'type' => 'switch',
							'title' => esc_html__('Custom color scheme', 'milenia'),
							'on' => esc_html__('On', 'milenia'),
							'off' => esc_html__('Off', 'milenia'),
							'default' => false
						),
						array(
							'id' => 'milenia-theme-skin',
							'type' => 'palette',
							'title' => esc_html__( 'Choose color scheme', 'milenia' ),
							'palettes' => array(
								'brown'  => array(
									'#ae745a',
									'#09355c',
								),
								'gray' => array(
									'#948685',
								),
								'blue' => array(
									'#19b1d1',
								),
								'lightbrown' => array(
									'#c19b76',
								),
								'green' => array(
									'#7eb71c',
									'#febd11'
								)
							),
							'default' => 'brown',
							'required' => array('milenia-theme-skin-custom-state', 'equals', false)
						),
						array(
							'id' => 'milenia-theme-skin-custom-primary',
							'type' => 'color',
							'title' => esc_html__('[Custom] Primary', 'milenia'),
							'transparent' => false,
							'required' => array('milenia-theme-skin-custom-state', 'equals', true)
						),
						array(
							'id' => 'milenia-theme-skin-custom-secondary',
							'type' => 'color',
							'title' => esc_html__('[Custom] Secondary', 'milenia'),
							'transparent' => false,
							'required' => array('milenia-theme-skin-custom-state', 'equals', true)
						)
					)
	            )
			);
		}

		/**
		 * Describes the 'Header' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionHeader() {
			return array(
				array(
					'icon' => 'el-icon-website',
					'icon_class' => 'icon',
					'title' => esc_html__('Header', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-header-type',
							'type' => 'image_select',
							'multi' => false,
							'title' => esc_html__('Layout', 'milenia'),
							'options' => array(
								'milenia-header-layout-v1' => array( 'alt' => esc_html__('Layout v1', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/header-layout-v2.png' ),
								'milenia-header-layout-v2' => array( 'alt' => esc_html__('Layout v2', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/header-layout-v5.png' ),
								'milenia-header-layout-v3' => array( 'alt' => esc_html__('Layout v3', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/header-layout-v4.png' ),
								'milenia-header-layout-v4' => array( 'alt' => esc_html__('Layout v4', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/header-layout-v1.png' ),
								'milenia-header-layout-v5' => array( 'alt' => esc_html__('Layout v5', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/header-layout-v3.png' )
							),
							'default' => 'milenia-header-layout-v5',
							'full_width' => true,
							'class' => 'milenia-image-select-column'
						),
						array(
							'id' => 'milenia-header-transparent',
							'type' => 'switch',
							'title' => esc_html__('Fixed transparent header', 'milenia'),
							'required' => array('milenia-header-type', 'equals', array('milenia-header-layout-v2', 'milenia-header-layout-v4')),
							'desc' => esc_html__('Please note that in case this option is enabled the header will be displayed as a fixed element (above the content).', 'milenia'),
							'on' => esc_html__('Enabled', 'milenia'),
							'off' => esc_html__('Disabled', 'milenia'),
							'default' => true
						),
						array(
							'id' => 'milenia-header-color-scheme',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Color scheme', 'milenia'),
							'required' => array('milenia-header-type', 'equals', array('milenia-header-layout-v1', 'milenia-header-layout-v3', 'milenia-header-layout-v5')),
							'options' => array(
								'milenia-header--light' => esc_html__('Light', 'milenia'),
								'milenia-header--dark' => esc_html__('Dark', 'milenia')
							),
							'default' => 'milenia-header--light'
						),
						array(
							'id' => 'milenia-header-container',
							'type' => 'button_set',
							'required' => array('milenia-header-type', 'equals', array('milenia-header-layout-v2', 'milenia-header-layout-v4', 'milenia-header-layout-v5')),
							'title' => esc_html__('Content width', 'milenia'),
							'options' => array(
								'container' => esc_html__('Container', 'milenia'),
								'container-fluid' => esc_html__('Full width', 'milenia')
							),
							'default' => 'container'
						),
						array(
							'id' => 'milenia-header-transparentable-color-scheme',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Color scheme', 'milenia'),
							'required' => array('milenia-header-transparent', 'equals', false),
							'options' => array(
								'milenia-header--light' => esc_html__('Light', 'milenia'),
								'milenia-header--dark' => esc_html__('Dark', 'milenia')
							),
							'default' => 'milenia-header--light'
						),
						array(
							'id' => 'milenia-header-navigation-section',
							'type' => 'switch',
							'title' => esc_html__('Navigation section', 'milenia'),
							'required' => array('milenia-header-type', 'equals', 'milenia-header-layout-v5'),
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
							'default' => true
						),
						array(
							'id' => 'milenia-header-sticky',
							'type' => 'switch',
							'title' => esc_html__('Sticky', 'milenia'),
							'required' => array('milenia-header-type', '!=', 'milenia-header-layout-v5'),
							'on' => esc_html__('Enabled', 'milenia'),
							'off' => esc_html__('Disabled', 'milenia'),
							'default' => true
						),
						array(
							'id' => 'milenia-header-sticky-responsive-breakpoint',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Sticky Responsive breakpoint', 'milenia'),
							'required' => array('milenia-header-sticky', 'equals', true),
							'options' => array(
								'milenia-header-section--sticky-sm' => esc_html__('sm', 'milenia'),
								'milenia-header-section--sticky-md' => esc_html__('md', 'milenia'),
								'milenia-header-section--sticky-lg' => esc_html__('lg', 'milenia'),
								'milenia-header-section--sticky-xl' => esc_html__('xl', 'milenia'),
								'milenia-header-section--sticky-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-header-section--sticky-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-header-section--sticky-xl'
						),
						array(
							'id' => 'milenia-header-layout-v5-sticky',
							'type' => 'switch',
							'title' => esc_html__('Sticky', 'milenia'),
							'required' => array(
								array('milenia-header-type', 'equals', 'milenia-header-layout-v5'),
								array('milenia-header-navigation-section', 'equals', true)
							),
							'on' => esc_html__('Enabled', 'milenia'),
							'off' => esc_html__('Disabled', 'milenia'),
							'default' => true
						),
						array(
							'id' => 'milenia-header-layout-v5-sticky-responsive-breakpoint',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Sticky Responsive breakpoint', 'milenia'),
							'required' => array('milenia-header-layout-v5-sticky', 'equals', true),
							'options' => array(
								'milenia-header-section--sticky-sm' => esc_html__('sm', 'milenia'),
								'milenia-header-section--sticky-md' => esc_html__('md', 'milenia'),
								'milenia-header-section--sticky-lg' => esc_html__('lg', 'milenia'),
								'milenia-header-section--sticky-xl' => esc_html__('xl', 'milenia'),
								'milenia-header-section--sticky-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-header-section--sticky-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-header-section--sticky-md'
						),
						array(
							'id' => 'milenia-header-top-bar',
							'type' => 'switch',
							'required' => array('milenia-header-type', 'equals', array('milenia-header-layout-v1', 'milenia-header-layout-v2')),
							'title' => esc_html__('Top bar', 'milenia'),
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
							'default' => true
						),
						array(
						   	'id' => 'milenia-header-top-bar-left-column-elements',
						   	'type' => 'select',
						   	'multi' => true,
						   	'required' => array('milenia-header-top-bar', 'equals', true),
						   	'title' => esc_html__("[Top bar] Left column elements", 'milenia'),
						   	'options' => array(
						   		'info' => esc_html__('General info (phone, email)', 'milenia'),
						   		'subnav' => esc_html__('Sub navigation', 'milenia')
						   	),
						   	'default' => array()
					   	),
						array(
							'id' => 'milenia-header-top-bar-right-column-elements',
							'type' => 'select',
							'multi' => true,
							'required' => array('milenia-header-top-bar', 'equals', true),
							'title' => esc_html__("[Top bar] Right column elements", 'milenia'),
							'options' => array(
								'info' => esc_html__('General info (phone, email)', 'milenia'),
								'subnav' => esc_html__('Sub navigation', 'milenia')
							),
							'default' => array()
						),
						array(
							'id' => 'milenia-header-left-column-elements',
							'type' => 'select',
							'multi' => true,
							'required' => array('milenia-header-type', 'equals', array('milenia-header-layout-v5', 'milenia-header-layout-v4', 'milenia-header-layout-v3')),
							'title' => esc_html__("Left column elements", 'milenia'),
							'options' => array(
								'search' => esc_html__('Search button', 'milenia'),
								'languages' => esc_html__('Language', 'milenia'),
								'action-btn' => esc_html__('Action button', 'milenia'),
								'weather' => esc_html__('Weather indicator', 'milenia'),
								'hidden-sidebar-btn' => esc_html__('Hidden sidebar button', 'milenia'),
								'menu-btn' => esc_html__('Vertical menu button', 'milenia')
							),
							'default' => array()
						),
						array(
							'id' => 'milenia-header-right-column-elements',
							'type' => 'select',
							'multi' => true,
							'required' => array('milenia-header-type', 'equals', array('milenia-header-layout-v1', 'milenia-header-layout-v2', 'milenia-header-layout-v5', 'milenia-header-layout-v4')),
							'title' => esc_html__("Right column elements", 'milenia'),
							'options' => array(
								'search' => esc_html__('Search button', 'milenia'),
								'languages' => esc_html__('Language', 'milenia'),
								'action-btn' => esc_html__('Action button', 'milenia'),
								'weather' => esc_html__('Weather indicator', 'milenia'),
								'hidden-sidebar-btn' => esc_html__('Hidden sidebar button', 'milenia'),
								'menu-btn' => esc_html__('Vertical menu button', 'milenia')
							),
							'default' => array()
						),
						array(
							'id' => 'milenia-header-items-caption',
							'type' => 'info',
							'desc' => esc_html__('The fields below belong to items that were selected in "Right column elements" or "Left column elements" settings.', 'milenia'),
							'style' => 'info'
						),
						array(
							'id' => 'milenia-header-action-btn-text',
							'type' => 'text',
							'title' => esc_html__("[Action Button] Text", 'milenia'),
							'default' => esc_html__('Book Now', 'milenia')
						),
						array(
							'id' => 'milenia-header-action-btn-url',
							'type' => 'text',
							'title' => esc_html__("[Action Button] URL", 'milenia'),
							'default' => '#'
						),
						array(
							'id' => 'milenia-header-action-btn-target',
							'type' => 'checkbox',
							'title' => esc_html__("[Action Button] Open in a new tab", 'milenia'),
							'default' => '0'
						),
						array(
							'id' => 'milenia-header-action-btn-nofollow',
							'type' => 'checkbox',
							'title' => esc_html__("[Action Button] Nofollow option", 'milenia'),
							'default' => '0'
						),
						array(
							'id' => 'milenia-header-vertical-menu-logo',
							'type' => 'media',
							'title' => esc_html__("[Vertical Navigation] Logo", 'milenia')
						),
						array(
							'id' => 'milenia-header-hidden-sidebar-widget-area',
							'type' => 'select',
							'title' => esc_html__('[Hidden Sidebar] Widget Area', 'milenia'),
							'data' => 'sidebars',
							'default' => 'widget-area-1'
						)
					)
				)
			);
		}

		/**
		 * Describes the 'Breadcrumb' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionBreadcrumb()
		{
			return array(
				array(
					'icon' => 'el-icon-website',
					'icon_class' => 'icon',
					'title' => esc_html__('Breadcrumb', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-breadcrumb-state',
							'type' => 'switch',
							'title' => esc_html__( 'Show breadcrumb section', 'milenia' ),
							'default' => true,
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
						),
						array(
							'id' => 'milenia-breadcrumb',
							'type' => 'breadcrumb_section',
							'required' => array('milenia-breadcrumb-state', 'equals', true),
							'title' => esc_html__( 'Breadcrumb settings', 'milenia' ),
							'full_width' => true
						)
					)
				)
			);
		}

		/**
		 * Describes the 'Footer' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionFooter() {
			return array(
				array(
					'icon' => 'el-icon-th-large',
					'icon_class' => 'icon',
					'title' => esc_html__('Footer', 'milenia'),
					'fields' => array(
						array(
							'id' => 'footer-sections',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Using sections', 'milenia' ),
							'desc' => esc_html__('Choose sections the footer will be contain which.', 'milenia'),
							'options' => array(
								'footer-section-1' => esc_html__('Section #1', 'milenia'),
								'footer-section-2' => esc_html__('Section #2', 'milenia'),
								'footer-section-3' => esc_html__('Section #3', 'milenia'),
								'footer-section-4' => esc_html__('Section #4', 'milenia'),
								'footer-section-5' => esc_html__('Section #5', 'milenia')
							),
							'default' => array('footer-section-1', 'footer-section-2')
						),

						// Footer Section #1

						array(
							'id'   => 'footer-section-1-title',
							'type' => 'raw',
							'full_width' => true,
							'required' => array( 'footer-sections', 'contains', 'footer-section-1' ),
							'content' => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Section #1', 'milenia'))
						),
						array(
							'id' => 'footer-section-1-src',
							'type' => 'select',
							'title' => esc_html__('Widget area', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-1' ),
							'data' => 'sidebars',
							'default' => 'widget-area-3'
						),
						array(
							'id' => 'footer-section-1-padding-y',
							'type' => 'spacing',
							'mode' => 'padding',
							'title' => esc_html__('Padding (y axis)', 'milenia'),
							'desc' => esc_html__('In pixels.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-1' ),
							'top' => true,
							'units' => 'px',
							'bottom' => true,
							'right' => false,
							'left' => false,
							'default' => array(
								'padding-top' => '90px',
								'padding-bottom' => '90px',
								'units' => 'px'
							)
						),
						array(
							'id' => 'footer-section-1-color-settings-states',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Appearance', 'milenia'),
							'desc' => esc_html__('The section has default appearance but if you don\'t like it you can change. Choose a property you want to change.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-1' ),
							'options' => array(
								'background' => esc_html__('Background color', 'milenia'),
								'text-color' => esc_html__('Text color', 'milenia'),
								'links-color' => esc_html__('Links color', 'milenia'),
								'headings-color' => esc_html__('Headings color', 'milenia')
							),
							'default' => array('background')
						),
						array(
							'id' => 'footer-section-1-bg',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Background', 'milenia'),
							'required' => array( 'footer-section-1-color-settings-states', 'contains', 'background' ),
							'options' => array(
								'dark' => esc_html__('Dark', 'milenia'),
								'light-default' => esc_html__('Light', 'milenia'),
								'primary' => esc_html__('[Current scheme] Primary', 'milenia'),
								'secondary' => esc_html__('[Current scheme] Secondary', 'milenia'),
								'custom' => esc_html__('Custom', 'milenia')
							),
							'default' => 'dark'
						),
						array(
							'id' => 'footer-section-1-bg-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Background', 'milenia'),
							'required' => array('footer-section-1-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#1c1c1c'
						),
						array(
							'id' => 'footer-section-1-text-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Text color', 'milenia'),
							'required' => array('footer-section-1-color-settings-states', 'contains', 'text-color'),
							'transparent' => false,
							'default' => '#858585'
						),
						array(
							'id' => 'footer-section-1-links-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Links color', 'milenia'),
							'required' => array('footer-section-1-color-settings-states', 'contains', 'links-color'),
							'transparent' => false,
							'default' => '#ae745a'
						),
						array(
							'id' => 'footer-section-1-headings-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Headings color', 'milenia'),
							'required' => array('footer-section-1-color-settings-states', 'contains', 'headings-color'),
							'transparent' => false,
							'default' => '#ffffff'
						),
						array(
							'id' => 'footer-section-1-columns',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Columns', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'options' => array(
								'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia'),
								'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia'),
								'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia'),
								'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia')
							),
							'default' => 'milenia-grid--cols-4'
						),
						array(
							'id' => 'footer-section-1-widgets',
							'type' => 'widgets_area_settings',
							'title' => esc_html__('Widgets settings', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-1' )
						),
						array(
							'id' => 'footer-section-1-responsive-breakpoint',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Responsive breakpoint', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'options' => array(
								'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia'),
								'milenia-grid--responsive-md' => esc_html__('md', 'milenia'),
								'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia'),
								'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia'),
								'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-grid--responsive-sm'
						),
						array(
							'id' => 'footer-section-1-full-width',
							'type' => 'switch',
							'title' => esc_html__('Full width', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'default' => true
						),
						array(
							'id' => 'footer-section-1-uppercased-titles',
							'type' => 'switch',
							'title' => esc_html__('Uppercased titles', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'default' => false
						),
						array(
							'id' => 'footer-section-1-large-offset',
							'type' => 'switch',
							'title' => esc_html__('Large widget title offset', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'default' => false
						),
						array(
							'id' => 'footer-section-1-padding-x',
							'type' => 'switch',
							'title' => esc_html__('Padding (x axis)', 'milenia'),
							'required' => array( 'footer-section-1-full-width', '!=', true ),
							'default' => true
						),
						array(
							'id' => 'footer-section-1-border-top-color',
							'type' => 'color',
							'title' => esc_html__('Border top color (if exists)', 'milenia'),
							'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-section-1-widgets-border',
							'type' => 'switch',
							'title' => esc_html__('Widgets border', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-1'),
							'default' => true,
							'desc' => esc_html__('Widgets inside the section will be vertically separated.', 'milenia')
						),
						array(
							'id' => 'footer-section-1-widgets-border-color',
							'type' => 'color',
							'title' => esc_html__('Widgets border color', 'milenia'),
							'required' => array('footer-section-1-widgets-border', 'equals', true),
							'transparent' => false,
							'default' => '#2e2e2e'
						),

						// Footer Section #2
						array(
							'id'   => 'footer-section-2-title',
							'type' => 'raw',
							'full_width' => true,
							'required' => array( 'footer-sections', 'contains', 'footer-section-2' ),
							'content' => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Section #2', 'milenia'))
						),
						array(
							'id' => 'footer-section-2-src',
							'type' => 'select',
							'title' => esc_html__('Widget area', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-2' ),
							'data' => 'sidebars',
							'default' => 'widget-area-4'
						),
						array(
							'id' => 'footer-section-2-padding-y',
							'type' => 'spacing',
							'mode' => 'padding',
							'title' => esc_html__('Padding (y axis)', 'milenia'),
							'desc' => esc_html__('In pixels.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-2' ),
							'top' => true,
							'units' => 'px',
							'bottom' => true,
							'right' => false,
							'left' => false,
							'default' => array(
								'padding-top' => '90px',
								'padding-bottom' => '90px',
								'units' => 'px'
							)
						),
						array(
							'id' => 'footer-section-2-color-settings-states',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Appearance', 'milenia'),
							'desc' => esc_html__('The section has default appearance but if you don\'t like it you can change. Choose a property you want to change.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-2' ),
							'options' => array(
								'background' => esc_html__('Background color', 'milenia'),
								'text-color' => esc_html__('Text color', 'milenia'),
								'links-color' => esc_html__('Links color', 'milenia'),
								'headings-color' => esc_html__('Headings color', 'milenia')
							),
							'default' => array('background')
						),
						array(
							'id' => 'footer-section-2-bg',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Background', 'milenia'),
							'required' => array( 'footer-section-2-color-settings-states', 'contains', 'background' ),
							'options' => array(
								'dark' => esc_html__('Dark', 'milenia'),
								'light-default' => esc_html__('Light', 'milenia'),
								'primary' => esc_html__('[Current scheme] Primary', 'milenia'),
								'secondary' => esc_html__('[Current scheme] Secondary', 'milenia'),
								'custom' => esc_html__('Custom', 'milenia')
							),
							'default' => 'dark'
						),
						array(
							'id' => 'footer-section-2-bg-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Background', 'milenia'),
							'required' => array('footer-section-2-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#1c1c1c'
						),
						array(
							'id' => 'footer-section-2-text-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Text color', 'milenia'),
							'required' => array('footer-section-2-color-settings-states', 'contains', 'text-color'),
							'transparent' => false,
							'default' => '#858585'
						),
						array(
							'id' => 'footer-section-2-links-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Links color', 'milenia'),
							'required' => array('footer-section-2-color-settings-states', 'contains', 'links-color'),
							'transparent' => false,
							'default' => '#ae745a'
						),
						array(
							'id' => 'footer-section-2-headings-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Headings color', 'milenia'),
							'required' => array('footer-section-2-color-settings-states', 'contains', 'headings-color'),
							'transparent' => false,
							'default' => '#ffffff'
						),
						array(
							'id' => 'footer-section-2-columns',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Columns', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'options' => array(
								'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia'),
								'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia'),
								'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia'),
								'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia')
							),
							'default' => 'milenia-grid--cols-4'
						),
						array(
							'id' => 'footer-section-2-widgets',
							'type' => 'widgets_area_settings',
							'title' => esc_html__('Widgets settings', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-2' )
						),
						array(
							'id' => 'footer-section-2-responsive-breakpoint',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Responsive breakpoint', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'options' => array(
								'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia'),
								'milenia-grid--responsive-md' => esc_html__('md', 'milenia'),
								'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia'),
								'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia'),
								'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-grid--responsive-sm'
						),
						array(
							'id' => 'footer-section-2-full-width',
							'type' => 'switch',
							'title' => esc_html__('Full width', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'default' => true
						),
						array(
							'id' => 'footer-section-2-uppercased-titles',
							'type' => 'switch',
							'title' => esc_html__('Uppercased titles', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'default' => false
						),
						array(
							'id' => 'footer-section-2-large-offset',
							'type' => 'switch',
							'title' => esc_html__('Large widget title offset', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'default' => false
						),
						array(
							'id' => 'footer-section-2-padding-x',
							'type' => 'switch',
							'title' => esc_html__('Padding (x axis)', 'milenia'),
							'required' => array( 'footer-section-2-full-width', '!=', true ),
							'default' => true
						),
						array(
							'id' => 'footer-section-2-border-top-color',
							'type' => 'color',
							'title' => esc_html__('Border top color (if exists)', 'milenia'),
							'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-section-2-widgets-border',
							'type' => 'switch',
							'title' => esc_html__('Widgets border', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-2'),
							'default' => true,
							'desc' => esc_html__('Widgets inside the section will be vertically separated.', 'milenia')
						),
						array(
							'id' => 'footer-section-2-widgets-border-color',
							'type' => 'color',
							'title' => esc_html__('Widgets border color', 'milenia'),
							'required' => array('footer-section-2-widgets-border', 'equals', true),
							'transparent' => false,
							'default' => '#2e2e2e'
						),

						// Footer Section #3
						array(
							'id'   => 'footer-section-3-title',
							'type' => 'raw',
							'full_width' => true,
							'required' => array( 'footer-sections', 'contains', 'footer-section-3' ),
							'content' => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Section #3', 'milenia'))
						),
						array(
							'id' => 'footer-section-3-src',
							'type' => 'select',
							'title' => esc_html__('Widget area', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-3' ),
							'data' => 'sidebars',
							'default' => 'widget-area-5'
						),
						array(
							'id' => 'footer-section-3-padding-y',
							'type' => 'spacing',
							'mode' => 'padding',
							'title' => esc_html__('Padding (y axis)', 'milenia'),
							'desc' => esc_html__('In pixels.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-3' ),
							'top' => true,
							'units' => 'px',
							'bottom' => true,
							'right' => false,
							'left' => false,
							'default' => array(
								'padding-top' => '90px',
								'padding-bottom' => '90px',
								'units' => 'px'
							)
						),
						array(
							'id' => 'footer-section-3-color-settings-states',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Appearance', 'milenia'),
							'desc' => esc_html__('The section has default appearance but if you don\'t like it you can change. Choose a property you want to change.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-3' ),
							'options' => array(
								'background' => esc_html__('Background color', 'milenia'),
								'text-color' => esc_html__('Text color', 'milenia'),
								'links-color' => esc_html__('Links color', 'milenia'),
								'headings-color' => esc_html__('Headings color', 'milenia')
							),
							'default' => array('background')
						),
						array(
							'id' => 'footer-section-3-bg',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Background', 'milenia'),
							'required' => array( 'footer-section-3-color-settings-states', 'contains', 'background' ),
							'options' => array(
								'dark' => esc_html__('Dark', 'milenia'),
								'light-default' => esc_html__('Light', 'milenia'),
								'primary' => esc_html__('[Current scheme] Primary', 'milenia'),
								'secondary' => esc_html__('[Current scheme] Secondary', 'milenia'),
								'custom' => esc_html__('Custom', 'milenia')
							),
							'default' => 'dark'
						),
						array(
							'id' => 'footer-section-3-bg-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Background', 'milenia'),
							'required' => array('footer-section-3-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#1c1c1c'
						),
						array(
							'id' => 'footer-section-3-text-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Text color', 'milenia'),
							'required' => array('footer-section-3-color-settings-states', 'contains', 'text-color'),
							'transparent' => false,
							'default' => '#858585'
						),
						array(
							'id' => 'footer-section-3-links-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Links color', 'milenia'),
							'required' => array('footer-section-3-color-settings-states', 'contains', 'links-color'),
							'transparent' => false,
							'default' => '#ae745a'
						),
						array(
							'id' => 'footer-section-3-headings-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Headings color', 'milenia'),
							'required' => array('footer-section-3-color-settings-states', 'contains', 'headings-color'),
							'transparent' => false,
							'default' => '#ffffff'
						),
						array(
							'id' => 'footer-section-3-columns',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Columns', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'options' => array(
								'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia'),
								'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia'),
								'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia'),
								'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia')
							),
							'default' => 'milenia-grid--cols-4'
						),
						array(
							'id' => 'footer-section-3-widgets',
							'type' => 'widgets_area_settings',
							'title' => esc_html__('Widgets settings', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-3' )
						),
						array(
							'id' => 'footer-section-3-responsive-breakpoint',
							'type' => 'select',
							'multi' => false,
							'title' => esc_html__('Responsive breakpoint', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'options' => array(
								'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia'),
								'milenia-grid--responsive-md' => esc_html__('md', 'milenia'),
								'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia'),
								'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia'),
								'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-grid--responsive-sm'
						),
						array(
							'id' => 'footer-section-3-full-width',
							'type' => 'switch',
							'title' => esc_html__('Full width', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'default' => true
						),
						array(
							'id' => 'footer-section-3-uppercased-titles',
							'type' => 'switch',
							'title' => esc_html__('Uppercased titles', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'default' => false
						),
						array(
							'id' => 'footer-section-3-large-offset',
							'type' => 'switch',
							'title' => esc_html__('Large widget title offset', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'default' => false
						),
						array(
							'id' => 'footer-section-3-padding-x',
							'type' => 'switch',
							'title' => esc_html__('Padding (x axis)', 'milenia'),
							'required' => array( 'footer-section-3-full-width', '!=', true ),
							'default' => true
						),
						array(
							'id' => 'footer-section-3-border-top-color',
							'type' => 'color',
							'title' => esc_html__('Border top color (if exists)', 'milenia'),
							'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-section-3-widgets-border',
							'type' => 'switch',
							'title' => esc_html__('Widgets border', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-3'),
							'default' => true,
							'desc' => esc_html__('Widgets inside the section will be vertically separated.', 'milenia')
						),
						array(
							'id' => 'footer-section-3-widgets-border-color',
							'type' => 'color',
							'title' => esc_html__('Widgets border color', 'milenia'),
							'required' => array('footer-section-3-widgets-border', 'equals', true),
							'transparent' => false,
							'default' => '#2e2e2e'
						),

						// Footer Section #4

						array(
							'id'   => 'footer-section-4-title',
							'type' => 'raw',
							'full_width' => true,
							'required' => array( 'footer-sections', 'contains', 'footer-section-4' ),
							'content' => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Section #4', 'milenia'))
						),
						array(
							'id' => 'footer-section-4-src',
							'type' => 'select',
							'title' => esc_html__('Widget area', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-4' ),
							'data' => 'sidebars',
							'default' => 'widget-area-6'
						),
						array(
							'id' => 'footer-section-4-padding-y',
							'type' => 'spacing',
							'mode' => 'padding',
							'title' => esc_html__('Padding (y axis)', 'milenia'),
							'desc' => esc_html__('In pixels.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-4' ),
							'top' => true,
							'units' => 'px',
							'bottom' => true,
							'right' => false,
							'left' => false,
							'default' => array(
								'padding-top' => '90px',
								'padding-bottom' => '90px',
								'units' => 'px'
							)
						),
						array(
							'id' => 'footer-section-4-color-settings-states',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Appearance', 'milenia'),
							'desc' => esc_html__('The section has default appearance but if you don\'t like it you can change. Choose a property you want to change.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-4' ),
							'options' => array(
								'background' => esc_html__('Background color', 'milenia'),
								'text-color' => esc_html__('Text color', 'milenia'),
								'links-color' => esc_html__('Links color', 'milenia'),
								'headings-color' => esc_html__('Headings color', 'milenia')
							),
							'default' => array('background')
						),
						array(
							'id' => 'footer-section-4-bg',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Background', 'milenia'),
							'required' => array( 'footer-section-4-color-settings-states', 'contains', 'background' ),
							'options' => array(
								'dark' => esc_html__('Dark', 'milenia'),
								'light-default' => esc_html__('Light', 'milenia'),
								'primary' => esc_html__('[Current scheme] Primary', 'milenia'),
								'secondary' => esc_html__('[Current scheme] Secondary', 'milenia'),
								'custom' => esc_html__('Custom', 'milenia')
							),
							'default' => 'dark'
						),
						array(
							'id' => 'footer-section-4-bg-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Background', 'milenia'),
							'required' => array('footer-section-4-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#1c1c1c'
						),
						array(
							'id' => 'footer-section-4-text-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Text color', 'milenia'),
							'required' => array('footer-section-4-color-settings-states', 'contains', 'text-color'),
							'transparent' => false,
							'default' => '#858585'
						),
						array(
							'id' => 'footer-section-4-links-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Links color', 'milenia'),
							'required' => array('footer-section-4-color-settings-states', 'contains', 'links-color'),
							'transparent' => false,
							'default' => '#ae745a'
						),
						array(
							'id' => 'footer-section-4-headings-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Headings color', 'milenia'),
							'required' => array('footer-section-4-color-settings-states', 'contains', 'headings-color'),
							'transparent' => false,
							'default' => '#ffffff'
						),
						array(
							'id' => 'footer-section-4-columns',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Columns', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'options' => array(
								'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia'),
								'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia'),
								'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia'),
								'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia')
							),
							'default' => 'milenia-grid--cols-4'
						),
						array(
							'id' => 'footer-section-4-widgets',
							'type' => 'widgets_area_settings',
							'title' => esc_html__('Widgets settings', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-4' )
						),
						array(
							'id' => 'footer-section-4-responsive-breakpoint',
							'type' => 'select',
							'multi' => false,
							'title' => esc_html__('Responsive breakpoint', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'options' => array(
								'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia'),
								'milenia-grid--responsive-md' => esc_html__('md', 'milenia'),
								'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia'),
								'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia'),
								'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-grid--responsive-sm'
						),
						array(
							'id' => 'footer-section-4-full-width',
							'type' => 'switch',
							'title' => esc_html__('Full width', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'default' => true
						),
						array(
							'id' => 'footer-section-4-uppercased-titles',
							'type' => 'switch',
							'title' => esc_html__('Uppercased titles', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'default' => false
						),
						array(
							'id' => 'footer-section-4-large-offset',
							'type' => 'switch',
							'title' => esc_html__('Large widget title offset', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'default' => false
						),
						array(
							'id' => 'footer-section-4-padding-x',
							'type' => 'switch',
							'title' => esc_html__('Padding (x axis)', 'milenia'),
							'required' => array( 'footer-section-4-full-width', '!=', true ),
							'default' => true
						),
						array(
							'id' => 'footer-section-4-border-top-color',
							'type' => 'color',
							'title' => esc_html__('Border top color (if exists)', 'milenia'),
							'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-section-4-widgets-border',
							'type' => 'switch',
							'title' => esc_html__('Widgets border', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-4'),
							'default' => true,
							'desc' => esc_html__('Widgets inside the section will be vertically separated.', 'milenia')
						),
						array(
							'id' => 'footer-section-4-widgets-border-color',
							'type' => 'color',
							'title' => esc_html__('Widgets border color', 'milenia'),
							'required' => array('footer-section-4-widgets-border', 'equals', true),
							'transparent' => false,
							'default' => '#2e2e2e'
						),

						// Footer Section #5

						array(
							'id'   => 'footer-section-5-title',
							'type' => 'raw',
							'full_width' => true,
							'required' => array( 'footer-sections', 'contains', 'footer-section-5' ),
							'content' => sprintf('<h2 class="milenia-marked-title">%s</h2>', esc_html__('Section #5', 'milenia'))
						),
						array(
							'id' => 'footer-section-5-src',
							'type' => 'select',
							'title' => esc_html__('Widget area', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-5' ),
							'data' => 'sidebars',
							'default' => 'widget-area-7'
						),
						array(
							'id' => 'footer-section-5-padding-y',
							'type' => 'spacing',
							'mode' => 'padding',
							'title' => esc_html__('Padding (y axis)', 'milenia'),
							'desc' => esc_html__('In pixels.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-5' ),
							'top' => true,
							'units' => 'px',
							'bottom' => true,
							'right' => false,
							'left' => false,
							'default' => array(
								'padding-top' => '90px',
								'padding-bottom' => '90px',
								'units' => 'px'
							)
						),
						array(
							'id' => 'footer-section-5-color-settings-states',
							'type' => 'button_set',
							'multi' => true,
							'title' => esc_html__('Appearance', 'milenia'),
							'desc' => esc_html__('The section has default appearance but if you don\'t like it you can change. Choose a property you want to change.', 'milenia'),
							'required' => array( 'footer-sections', 'contains', 'footer-section-5' ),
							'options' => array(
								'background' => esc_html__('Background color', 'milenia'),
								'text-color' => esc_html__('Text color', 'milenia'),
								'links-color' => esc_html__('Links color', 'milenia'),
								'headings-color' => esc_html__('Headings color', 'milenia')
							),
							'default' => array('background')
						),
						array(
							'id' => 'footer-section-5-bg',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Background', 'milenia'),
							'required' => array( 'footer-section-5-color-settings-states', 'contains', 'background' ),
							'options' => array(
								'dark' => esc_html__('Dark', 'milenia'),
								'light-default' => esc_html__('Light', 'milenia'),
								'primary' => esc_html__('[Current scheme] Primary', 'milenia'),
								'secondary' => esc_html__('[Current scheme] Secondary', 'milenia'),
								'custom' => esc_html__('Custom', 'milenia')
							),
							'default' => 'dark'
						),
						array(
							'id' => 'footer-section-5-bg-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Background', 'milenia'),
							'required' => array('footer-section-5-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#1c1c1c'
						),
						array(
							'id' => 'footer-section-5-text-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Text color', 'milenia'),
							'required' => array('footer-section-5-color-settings-states', 'contains', 'text-color'),
							'transparent' => false,
							'default' => '#858585'
						),
						array(
							'id' => 'footer-section-5-links-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Links color', 'milenia'),
							'required' => array('footer-section-5-color-settings-states', 'contains', 'links-color'),
							'transparent' => false,
							'default' => '#ae745a'
						),
						array(
							'id' => 'footer-section-5-headings-color-custom',
							'type' => 'color',
							'title' => esc_html__('[Custom] Headings color', 'milenia'),
							'required' => array('footer-section-5-color-settings-states', 'contains', 'headings-color'),
							'transparent' => false,
							'default' => '#ffffff'
						),
						array(
							'id' => 'footer-section-5-columns',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('Columns', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'options' => array(
								'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia'),
								'milenia-grid--cols-2' => esc_html__('2 Column', 'milenia'),
								'milenia-grid--cols-3' => esc_html__('3 Column', 'milenia'),
								'milenia-grid--cols-4' => esc_html__('4 Column', 'milenia')
							),
							'default' => 'milenia-grid--cols-4'
						),
						array(
							'id' => 'footer-section-5-widgets',
							'type' => 'widgets_area_settings',
							'title' => esc_html__('Widgets settings', 'milenia'),
							'required' => array( 'footer-sections','contains', 'footer-section-5' )
						),
						array(
							'id' => 'footer-section-5-responsive-breakpoint',
							'type' => 'select',
							'multi' => false,
							'title' => esc_html__('Responsive breakpoint', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'options' => array(
								'milenia-grid--responsive-sm' => esc_html__('sm', 'milenia'),
								'milenia-grid--responsive-md' => esc_html__('md', 'milenia'),
								'milenia-grid--responsive-lg' => esc_html__('lg', 'milenia'),
								'milenia-grid--responsive-xl' => esc_html__('xl', 'milenia'),
								'milenia-grid--responsive-xxl' => esc_html__('xxl', 'milenia'),
								'milenia-grid--responsive-xxxl' => esc_html__('xxxl', 'milenia'),
							),
							'default' => 'milenia-grid--responsive-sm'
						),
						array(
							'id' => 'footer-section-5-full-width',
							'type' => 'switch',
							'title' => esc_html__('Full width', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'default' => true
						),
						array(
							'id' => 'footer-section-5-uppercased-titles',
							'type' => 'switch',
							'title' => esc_html__('Uppercased titles', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'default' => false
						),
						array(
							'id' => 'footer-section-5-large-offset',
							'type' => 'switch',
							'title' => esc_html__('Large widget title offset', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'default' => false
						),
						array(
							'id' => 'footer-section-5-padding-x',
							'type' => 'switch',
							'title' => esc_html__('Padding (x axis)', 'milenia'),
							'required' => array( 'footer-section-5-full-width', '!=', true ),
							'default' => true
						),
						array(
							'id' => 'footer-section-5-border-top-color',
							'type' => 'color',
							'title' => esc_html__('Border top color (if exists)', 'milenia'),
							'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-section-5-widgets-border',
							'type' => 'switch',
							'title' => esc_html__('Widgets border', 'milenia'),
							'required' => array('footer-sections', 'contains', 'footer-section-5'),
							'default' => true,
							'desc' => esc_html__('Widgets inside the section will be vertically separated.', 'milenia')
						),
						array(
							'id' => 'footer-section-5-widgets-border-color',
							'type' => 'color',
							'title' => esc_html__('Widgets border color', 'milenia'),
							'required' => array('footer-section-5-widgets-border', 'equals', true),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-copyright-section-state',
							'type' => 'switch',
							'title' => esc_html__('Copyright section', 'milenia'),
							'default' => true
						),
						array(
							'id' => 'footer-copyright-section-text',
							'type' => 'text',
							'title' => esc_html__('[Copyright section] Text', 'milenia'),
							'default' => sprintf(esc_html__('Copyright &copy; %d %s. All Rights Reserved.', 'milenia'), date('Y'), get_bloginfo('name')),
							'required' => array('footer-copyright-section-state', 'equals', true),
							'description' => esc_html__('Enter copyright text that will be displayed at the bottom of the site footer.', 'milenia')
						),
						array(
							'id' => 'footer-copyright-section-bg',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__('[Copyright section] Background', 'milenia'),
							'required' => array('footer-copyright-section-state', 'equals', true),
							'options' => array(
								'dark' => esc_html__('Dark', 'milenia'),
								'light-default' => esc_html__('Light', 'milenia'),
								'primary' => esc_html__('[Current scheme] Primary', 'milenia'),
								'secondary' => esc_html__('[Current scheme] Secondary', 'milenia'),
								'custom' => esc_html__('Custom', 'milenia')
							),
							'default' => 'dark'
						),
						array(
							'id' => 'footer-copyright-section-bg-custom',
							'type' => 'color',
							'title' => esc_html__('[Copyright section] Custom background', 'milenia'),
							'required' => array('footer-copyright-section-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#1c1c1c'
						),
						array(
							'id' => 'footer-copyright-section-text-color',
							'type' => 'color',
							'title' => esc_html__('[Copyright section] Custom text color', 'milenia'),
							'required' => array('footer-copyright-section-bg', 'equals', 'custom'),
							'transparent' => false,
							'default' => '#858585'
						),
						array(
							'id' => 'footer-copyright-section-border-top-color',
							'type' => 'color',
							'title' => esc_html__('[Copyright section] Border top color (if exists)', 'milenia'),
							'desc' => esc_html__('The border will be added to divide two sections with background.', 'milenia'),
							'required' => array('footer-copyright-section-state', 'equals', true),
							'transparent' => false,
							'default' => '#2e2e2e'
						),
						array(
							'id' => 'footer-copyright-section-full-width',
							'type' => 'switch',
							'title' => esc_html__('[Copyright section] Full width', 'milenia'),
							'required' => array('footer-copyright-section-state', 'equals', true),
							'default' => true
						),
					)
				)
			);
		}

		/**
		 * Describes the 'Blog' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionBlog() {
			return array(
				array(
					'icon' => 'el-icon-pencil',
					'icon_class' => 'icon',
					'title' => esc_html__('Blog', 'milenia')
				),
				array(
					'icon_class' => 'icon',
					'icon' => 'el-icon-th',
					'subsection' => true,
					'title' => esc_html__('Post Archive', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-post-archive-style',
							'type' => 'image_select',
							'multi' => false,
							'title' => esc_html__( 'Default display style', 'milenia' ),
							'options' => array(
								'milenia-entities--style-4' => array( 'alt' => esc_html__('Layout v1', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/post-archive-layout-v1.png' ),
								'milenia-entities--style-6 milenia-entities--without-media' => array( 'alt' => esc_html__('Layout v2', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/post-archive-layout-v2.png' ),
								'milenia-entities--style-6' => array( 'alt' => esc_html__('Layout v3', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/post-archive-layout-v3.png' ),
								'milenia-entities--style-7' => array( 'alt' => esc_html__('Layout v4', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/post-archive-layout-v4.png' ),
								'milenia-entities--style-8' => array( 'alt' => esc_html__('Layout v5', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/post-archive-layout-v5.png' )
							),
							'default' => 'milenia-entities--style-7'
						),
						array(
							'id' => 'milenia-post-archive-isotope-layout',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__( 'Container layout', 'milenia' ),
							'options' => array(
								'grid' => esc_html__('Grid', 'milenia'),
								'masonry' => esc_html__('Masonry', 'milenia')
							),
							'default' => 'grid'
						),
						array(
							'id' => 'blog-default-columns-info',
							'type' => 'info',
							'title' => esc_html__('Pay attention the theme could set columns automatically in case where selected value cannot be set in selected layout.', 'milenia'),
							'notice' => false
						),
						array(
							'id' => 'milenia-post-archive-columns',
							'type' => 'button_set',
							'multi' => false,
							'title' => esc_html__( 'Columns', 'milenia' ),
							'options' => array(
								'milenia-grid--cols-1' => esc_html__('1 Column', 'milenia'),
								'milenia-grid--cols-2' => esc_html__('2 Columns', 'milenia'),
								'milenia-grid--cols-3' => esc_html__('3 Columns', 'milenia'),
								'milenia-grid--cols-4' => esc_html__('4 Columns', 'milenia')
							),
							'default' => 'milenia-grid--cols-1'
						),
						array(
							'id' => 'milenia-post-archive-layout',
							'type' => 'image_select',
							'title' => esc_html__('Page layout', 'milenia'),
							'options' => array(
								'milenia-left-sidebar' => array( 'alt' => esc_html__('Left Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-left.jpg' ),
								'milenia-has-not-sidebar' => array( 'alt' => esc_html__('Without Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-full.jpg' ),
								'milenia-right-sidebar' => array( 'alt' => esc_html__('Right Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-right.jpg' ),
								'milenia-full-width' => array( 'alt' => esc_html__('Full Width', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/page-layout-fullwidth.png' )
							),
							'default' => 'milenia-right-sidebar'
						),
						array(
							'id' => 'milenia-post-archive-sidebar',
							'type' => 'select',
							'title' => esc_html__('Select sidebar', 'milenia'),
							'required' => array( 'milenia-post-archive-layout','equals', array('milenia-left-sidebar', 'milenia-right-sidebar') ),
							'data' => 'sidebars',
							'default' => 'widget-area-1'
						),
						array(
							'id' => 'milenia-post-archive-vertical-padding',
							'type' => 'spacing',
							'left' => false,
							'right' => false,
							'mode' => 'padding',
							'units' => '',
							'units_extended' => 'false',
							'title' => esc_html__( 'Page vertical padding', 'milenia' ),
							'default' => array(
								'padding-top' => 85,
								'padding-bottom' => 85,
								'units' => ''
							)
						)
					)
				),
				array(
					'icon_class' => 'icon',
					'icon' => 'el-icon-file-edit',
					'subsection' => true,
					'title' => esc_html__('Single Post', 'milenia'),
					'fields' => array(
						array(
							'id' => 'post-single-layout',
							'type' => 'image_select',
							'title' => esc_html__('Page layout', 'milenia'),
							'options' => array(
								'milenia-left-sidebar' => array( 'alt' => esc_html__('Left Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-left.jpg' ),
								'milenia-has-not-sidebar' => array( 'alt' => esc_html__('Without Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-full.jpg' ),
								'milenia-right-sidebar' => array( 'alt' => esc_html__('Right Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-right.jpg' )
							),
							'default' => 'milenia-right-sidebar'
						),
						array(
							'id' => 'post-single-sidebar',
							'type' => 'select',
							'title' => esc_html__('Select sidebar', 'milenia'),
							'required' => array( 'post-single-layout','equals', array('milenia-left-sidebar', 'milenia-right-sidebar') ),
							'data' => 'sidebars',
							'default' => 'widget-area-2'
						),
						array(
							'id' => 'post-single-show-tags',
							'type' => 'switch',
							'title' => esc_html__( 'Tags', 'milenia' ),
							'default' => true,
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
						),
						array(
							'id' => 'post-single-show-social-links',
							'type' => 'switch',
							'title' => esc_html__( 'Share buttons', 'milenia' ),
							'default' => false,
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
						),
						array(
							'id' => 'post-single-related-posts-state',
							'type' => 'switch',
							'title' => esc_html__( 'Related Posts', 'milenia' ),
							'default' => true,
							'on' => esc_html__('Show', 'milenia'),
							'off' => esc_html__('Hide', 'milenia'),
						)
					)
				)
			);
		}

		/**
		 * Describes the 'Pages' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionPages()
		{
			return array(
				array(
					'icon' => 'el-icon-file',
					'icon_class' => 'icon',
					'title' => esc_html__('Pages', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-page-vertical-padding',
							'type' => 'spacing',
							'left' => false,
							'right' => false,
							'mode' => 'padding',
							'units' => '',
							'units_extended' => 'false',
							'title' => esc_html__( 'Page vertical padding', 'milenia' ),
							'default' => array(
								'padding-top' => 85,
								'padding-bottom' => 85,
								'units' => ''
							)
						),
						array(
							'id' => 'milenia-page-layout',
							'type' => 'image_select',
							'title' => esc_html__('Page layout', 'milenia'),
							'options' => array(
								'milenia-left-sidebar' => array( 'alt' => esc_html__('Left Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-left.jpg' ),
								'milenia-has-not-sidebar' => array( 'alt' => esc_html__('Without Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-full.jpg' ),
								'milenia-right-sidebar' => array( 'alt' => esc_html__('Right Sidebar', 'milenia'), 'img' => MILENIA_TEMPLATE_DIRECTORY_URI . '/admin/assets/images/layout-right.jpg' )
							),
							'default' => 'milenia-right-sidebar'
						),
						array(
							'id' => 'milenia-page-sidebar',
							'type' => 'select',
							'title' => esc_html__('Select sidebar', 'milenia'),
							'required' => array( 'milenia-page-layout','equals', array('milenia-left-sidebar', 'milenia-right-sidebar') ),
							'data' => 'sidebars',
							'default' => 'widget-area-5'
						)
					)
				)
			);
		}

		/**
		 * Describes the '404 page' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function section404() {
			return array(
				array(
					'icon' => 'el-icon-warning-sign',
					'icon_class' => 'icon',
					'title' => esc_html__('404 page', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-404-error-text',
							'type' => 'text',
							'title' => esc_html__('Error text', 'milenia'),
							'default' => esc_html__("We're sorry, but we can't find the page you were looking for.", 'milenia')
						),
						array(
							'id' => 'milenia-404-error-description',
							'type' => 'textarea',
							'title' => esc_html__('Error description', 'milenia'),
							'default' => esc_html__("It's probably some thing we've done wrong but now we know about it and we'll try to fix it.", 'milenia')
						),
						array(
							'id' => 'milenia-404-skin',
							'type' => 'button_set',
							'options'=> array(
								'main' => 'Main color scheme',
								'brown' => 'Brown',
								'gray' => 'Gray',
								'blue' => 'Blue',
								'lightbrown' => 'Lightbrown',
								'green' => 'Green'
							),
							'title' => esc_html__('Color scheme', 'milenia'),
							'default' => 'main'
						)
					)
				),
				array(
					'subsection' => true,
					'title' => esc_html__('Search results', 'milenia'),
					'icon' => 'el-icon-search',
					'icon_class' => 'icon',
					'fields' => array(
						array(
							'id' => 'milenia-search-not-found-message',
							'type' => 'text',
							'title' => esc_html__('"Not found" message', 'milenia'),
							'default' => esc_html__('Nothing found', 'milenia')
						),
						array(
							'id' => 'milenia-search-not-found-description',
							'type' => 'textarea',
							'title' => esc_html__('"Not found" description', 'milenia'),
							'default' => esc_html__('Nothing found on your request.', 'milenia')
						)
					)
	            )
			);
		}

		/**
		 * Describes the '3dPartyAPI' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function section3dPartyAPI() {
			return array(
				array(
					'icon' => 'el-icon-cogs',
					'icon_class' => 'icon',
					'title' => esc_html__('3d party API', 'milenia'),
					'fields' => array(
						array(
							'id' => 'milenia-google-map-api-key',
							'type' => 'text',
							'title' => esc_html__('[Google Map] API key', 'milenia')
						),
						array(
							'id' => 'milenia-twitter-consumer-key',
							'type' => 'text',
							'title' => esc_html__('[Twitter] Consumer Key', 'milenia')
						),
						array(
							'id' => 'milenia-twitter-consumer-secret',
							'type' => 'text',
							'title' => esc_html__('[Twitter] Consumer Secret', 'milenia')
						),
						array(
							'id' => 'milenia-twitter-access-token',
							'type' => 'text',
							'title' => esc_html__('[Twitter] Access Token', 'milenia')
						),
						array(
							'id' => 'milenia-twitter-access-secret',
							'type' => 'text',
							'title' => esc_html__('[Twitter] Access Secret', 'milenia')
						),
						array(
							'id' => 'milenia-apixu-api-key',
							'type' => 'text',
							'title' => esc_html__('[Weatherstack] API Key', 'milenia'),
							'description' => esc_html__('API Key from weatherstack.com to build weather forecasts.', 'milenia')
						),
						array(
							'id' => 'milenia-apixu-city',
							'type' => 'text',
							'title' => esc_html__('[Weatherstack] City name', 'milenia'),
							'description' => esc_html__('Please note that the entered value might not exist in the result set got from the weather forecast system. In such a case the weather widget will not work properly. The best choice is to enter the nearest famous city in the format "{City name} {Countryname}".', 'milenia'),
						),
						array(
							'id' => 'milenia-rooms-review-criterias',
							'type' => 'text',
							'title' => esc_html__('[Accommodations] Review criterias', 'milenia'),
							'description' => esc_html__('Comma-separated.', 'milenia'),
							'default' => esc_html__('Accommodation, Location, Meals, Facilities', 'milenia')
						),
						array(
							'id'      => 'milenia-single-room-sections',
							'type'    => 'sorter',
							'title' => esc_html__('[Accommodations] Single room sections', 'milenia'),
							'desc'    => 'Organize how you want the sections to appear on the single room',
							'options' => array(
								'enabled'  => array(
									'description' => esc_html__('Description', 'milenia'),
									'amenities'   => esc_html__('Amenities', 'milenia'),
									'rates' => esc_html__('Rates', 'milenia'),
									'reviews'   => esc_html__('Reviews', 'milenia'),
									'availability' => esc_html__('Availability', 'milenia'),
									'reservation'   => esc_html__('Reservation Form', 'milenia')
								),
								'disabled' => array(
								)
							),
						)
					)
				)
			);
		}

		/**
		 * Describes the '3dPartyAPI' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionLocalization() {
			return array(
				array(
					'icon' => 'el-icon-globe',
					'icon_class' => 'globe',
					'title' => esc_html__('Localization', 'milenia'),
					'fields' => array(
						array(
						   	'id' => 'milenia-moment-localization',
						   	'type' => 'select',
//						   	'required' => array('milenia-header-top-bar', 'equals', true),
						   	'title' => esc_html__("MomentJS", 'milenia'),
						   	'options' => array(
								'af' => 'af',
								'ar' => 'ar',
								'ar-dz' => 'ar-dz',
								'ar-kw' => 'ar-kw',
								'ar-ly' => 'ar-ly',
								'ar-ma' => 'ar-ma',
								'ar-sa' => 'ar-sa',
								'ar-tn' => 'ar-tn',
								'az' => 'az',
								'be' => 'be',
								'bg' => 'bg',
								'bm' => 'bm',
								'bn' => 'bn',
								'bo' => 'bo',
								'br' => 'br',
								'bs' => 'bs',
								'ca' => 'ca',
								'cs' => 'cs',
								'cv' => 'cv',
								'cy' => 'cy',
								'da' => 'da',
								'de' => 'de',
								'de-at' => 'de-at',
								'de-ch' => 'de-ch',
								'dv' => 'dv',
								'el' => 'el',
								'en' => 'en',
								'en-au' => 'en-au',
								'en-ca' => 'en-ca',
								'en-gb' => 'en-gb',
								'en-ie' => 'en-ie',
								'en-il' => 'en-il',
								'en-nz' => 'en-nz',
								'en-SG' => 'en-SG',
								'eo' => 'eo',
								'es' => 'es',
								'es-do' => 'es-do',
								'es-us' => 'es-us',
								'et' => 'et',
								'eu' => 'eu',
								'fa' => 'fa',
								'fi' => 'fi',
								'fo' => 'fo',
								'fr' => 'fr',
								'fr-ca' => 'fr-ca',
								'fr-ch' => 'fr-ch',
								'fy' => 'fy',
								'ga' => 'ga',
								'gd' => 'gd',
								'gl' => 'gl',
								'gom-latn' => 'gom-latn',
								'gu' => 'gu',
								'he' => 'he',
								'hi' => 'hi',
								'hr' => 'hr',
								'hu' => 'hu',
								'hy-am' => 'hy-am',
								'id' => 'id',
								'is' => 'is',
								'it' => 'it',
								'it-ch' => 'it-ch',
								'ja' => 'ja',
								'jv' => 'jv',
								'ka' => 'ka',
								'kk' => 'kk',
								'km' => 'km',
								'kn' => 'kn',
								'ko' => 'ko',
								'ku' => 'ku',
								'ky' => 'ky',
								'lb' => 'lb',
								'lo' => 'lo',
								'lt' => 'lt',
								'lv' => 'lv',
								'me' => 'me',
								'mi' => 'mi',
								'ml' => 'ml',
								'mn' => 'mn',
								'mr' => 'mr',
								'ms' => 'ms',
								'ms-my' => 'ms-my',
								'mt' => 'mt',
								'my' => 'my',
								'nb' => 'nb',
								'ne' => 'ne',
								'nl' => 'nl',
								'nl-be' => 'nl-be',
								'nn' => 'nn',
								'pa-in' => 'pa-in',
								'pl' => 'pl',
								'pt' => 'pt',
								'pt-br' => 'pt-br',
								'ro' => 'ro',
								'ru' => 'ru',
								'sd' => 'sd',
								'se' => 'se',
								'si' => 'si',
								'sk' => 'sk',
								'sl' => 'sl',
								'sq' => 'sq',
								'sr' => 'sr',
								'sr-cyrl' => 'sr-cyrl',
								'ss' => 'ss',
								'sv' => 'sv',
								'sw' => 'sw',
								'ta' => 'ta',
								'te' => 'te',
								'tet' => 'tet',
								'tg' => 'tg',
								'th' => 'th',
								'tlh' => 'tlh',
								'tl-ph' => 'tl-ph',
								'tr' => 'tr',
								'tzl' => 'tzl',
								'tzm' => 'tzm',
								'tzm-latn' => 'tzm-latn',
								'ug-cn' => 'ug-cn',
								'uk' => 'uk',
								'ur' => 'ur',
								'uz' => 'uz',
								'uz-latn' => 'uz-latn',
								'x-pseudo' => 'x-pseudo',
								'yo' => 'yo',
								'zh-cn' => 'zh-cn',
								'zh-hk' => 'zh-hk',
								'zh-tw' => 'zh-tw'
						   	),
						   	'default' => 'en'
					   	),
					)
				)
			);
		}


		/**
		 * Describes the '3dPartyAPI' tab of the theme options.
		 *
		 * @access protected
		 * @return array
		 */
		protected function sectionWooCommerce() {
			return array(
				array(
					'icon' => 'el-icon-cogs',
					'icon_class' => 'icon',
					'title' => esc_html__('WooCommerce', 'milenia'),
					'fields' => array(
						array(
							'id' => 'product-archive-columns',
							'type' => 'button_set',
							'title' => esc_html__('Archive Columns', 'milenia' ),
							'options' => array(
								3 => 3,
								4 => 4,
								5 => 5,
							),
							'default' => 4
						),
						array(
							'id' => 'product-archive-per-page',
							'type' => 'text',
							'title' => esc_html__('Number of products displayed per page', 'milenia' ),
							'default' => 9
						),
					)
				),
				array(
					'icon_class' => 'icon',
					'subsection' => true,
					'title' => esc_html__('Single Product', 'milenia' ),
					'fields' => array(
						array(
							'id' => 'product-upsells-count',
							'type' => 'text',
							'title' => esc_html__('Up-Sells Count items', 'milenia' ),
							'default' => 4
						),
					)
				),
				array(
					'icon_class' => 'icon',
					'subsection' => true,
					'title' => esc_html__('Cart', 'milenia' ),
					'fields' => array(
						array(
							'id' => 'product-crosssell',
							'type' => 'switch',
							'title' => esc_html__('Show Cross-Sells', 'milenia' ),
							'default' => true,
							'on' => esc_html__('Yes', 'milenia' ),
							'off' => esc_html__('No', 'milenia' ),
						),
						array(
							'id' => 'product-crosssell-columns',
							'type' => 'text',
							'required' => array( 'product-crosssell','equals',true ),
							'title' => esc_html__('Cross Sells Columns', 'milenia' ),
							'default' => '2'
						),
						array(
							'id' => 'product-crosssell-count',
							'type' => 'text',
							'required' => array( 'product-crosssell','equals',true ),
							'title' => esc_html__('Cross Sells Limit', 'milenia' ),
							'default' => '2'
						),
					)
				)
			);
		}

		/**
		 * Returns parameters for the ReduxFramework instance.
		 *
		 * @access protected
		 * @return array
		 */
	    protected function setArgs() {
			return array(
				'opt_name'          => 'milenia_settings',
				'display_name'      => $this->theme->get('Name') . ' ' . esc_html__('Theme Options', 'milenia'),
				'display_version'   => esc_html__('Theme Version: ', 'milenia') . strtolower($this->theme->get('Version')),
				'menu_type'         => 'submenu',
				'allow_sub_menu'    => true,
				'menu_title'        => esc_html__('Theme Options', 'milenia'),
				'page_title'        => esc_html__('Theme Options', 'milenia'),
				'footer_credit'     => esc_html__('Theme Options', 'milenia'),

				'google_api_key' => 'AIzaSyBQft4vTUGW75YPU6c0xOMwLKhxCEJDPwg',
				'disable_google_fonts_link' => true,

				'async_typography'  => false,
				'admin_bar'         => false,
				'admin_bar_icon'    => 'dashicons-admin-generic',
				'admin_bar_priority' => 50,
				'global_variable'   => 'milenia_settings',
				'dev_mode'          => false,
				'customizer'        => true,
				'compiler'          => false,

				'page_priority'     => null,
				'page_parent'       => 'themes.php',
				'page_permissions'  => 'manage_options',
				'menu_icon'         => '',
				'last_tab'          => '',
				'page_icon'         => 'icon-themes',
				'page_slug'         => 'milenia_settings',
				'save_defaults'     => true,
				'default_show'      => false,
				'default_mark'      => '',
				'show_import_export' => true,
				'show_options_object' => false,

				'transient_time'    => 60 * MINUTE_IN_SECONDS,
				'output'            => false,
				'output_tag'        => false,

				'database'          => '',
				'system_info'       => false,

				'hints' => array(
					'icon'          => 'icon-question-sign',
					'icon_position' => 'right',
					'icon_color'    => 'lightgray',
					'icon_size'     => 'normal',
					'tip_style'     => array(
						'color'         => 'light',
						'shadow'        => true,
						'rounded'       => false,
						'style'         => '',
					),
					'tip_position'  => array(
						'my' => 'top left',
						'at' => 'bottom right',
					),
					'tip_effect'    => array(
						'show'          => array(
							'effect'        => 'slide',
							'duration'      => '500',
							'event'         => 'mouseover',
						),
						'hide'      => array(
							'effect'    => 'slide',
							'duration'  => '500',
							'event'     => 'click mouseleave',
						),
					),
				),
				'ajax_save'                 => true,
				'use_cdn'                   => true,
			);
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
	    public function getOption($name, $fallback = '', array $data = array())
		{
			global $milenia_settings;

			if(isset($this->setted_settings[$name])) {
				return $this->setted_settings[$name];
			}

			$current_object_id = !empty($data) && isset($data['object_id']) && !empty($data['object_id']) ? intval($data['object_id']) : get_queried_object_id();

			if( function_exists('rwmb_get_value') && isset($data['overriden_by']) && $current_object_id) {
				if( isset($data['depend_on'])) {
					if( rwmb_get_value($data['depend_on']['key'], null, $current_object_id) == $data['depend_on']['value'] ) {
						$overriden_by = rwmb_get_value($data['overriden_by'], null, $current_object_id);

						if($overriden_by === false && isset($milenia_settings[$name])) {
							return $milenia_settings[$name];
						}

						return (is_null($overriden_by) || (is_string($overriden_by) && !strlen($overriden_by)) || ($overriden_by === false)) ? $fallback : $overriden_by;
					}
					elseif( isset($milenia_settings[$name]) ) {
						return $milenia_settings[$name];
					}
					else {
						return $fallback;
					}
				}
				else {
					$overriden_by = rwmb_get_value($data['overriden_by'], null, $current_object_id);
					return (is_null($overriden_by) || (is_string($overriden_by) && !strlen($overriden_by)) || ($overriden_by === false)) ? $fallback : $overriden_by;
				}
			}

			if( isset($milenia_settings[$name]) ) return $milenia_settings[$name];
			elseif( function_exists('rwmb_get_value') && $current_object_id ) {
				$current_object_value = rwmb_get_value($name, null, $current_object_id);

				return (is_null($current_object_value) || (is_string($current_object_value) && !strlen($current_object_value)) || ($current_object_value === false)) ? $fallback : $current_object_value;
			}

			return $fallback;
		}
	}
}
?>
