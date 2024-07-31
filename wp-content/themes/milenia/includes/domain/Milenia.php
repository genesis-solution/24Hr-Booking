<?php
/**
* The main application class
*
* This class is responsible to load all necessary assets, caching, theme activation, etc.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

class Milenia
{
	/**
	 * Contains an Milenia_Admin object.
	 *
	 * @access protected
	 * @var MileniaAdmin
	 */
	protected $admin;

	/**
	 * Contains an Milenia_Helper object.
	 *
	 * @access protected
	 * @var Milenia_Helper
	 */
	protected $helper;

    /**
	 * The one, true instance of the Milenia object.
	 *
	 * @static
	 * @access protected
	 * @var null|object
	 */
    protected static $instance;

    /**
	 * Contains uri of the template directory.
	 *
	 * @access protected
	 * @var null|string
	 */
    protected $template_dir;

	/**
	 * Contains the current page type (default, blogroll, portfolio, etc.).
	 *
	 * @access protected
	 * @var null|string
	 */
	protected $current_page_type;

	/**
	 * Contains the current page style (portfolio/gallery (slideshow, striped-carousel, full-page, masonry, etc.)).
	 *
	 * @access protected
	 * @var null|string
	 */
	protected $current_page_style;

	/**
	 * Contains an array of plugins that theme uses.
	 *
	 * @access protected
	 * @var array
	 */
	protected $bundled_plugins = array();

	/**
	 * Contains default fonts settings of the theme.
	 *
	 * @access protected
	 * @var array
	 */
	protected $google_fonts = array(
		'Open Sans' => array('200', '300', '400', '400italic', '600', '600italic', '700', '700italic', '800', '800italic'),
		'Playfair Display' => array('400', '400italic', '700', '700italic', '900', '900italic'),
		'Old Standard TT' => array('400','400italic','700')
	);

	/**
	 * Contains default charsets for the fonts of the theme.
	 *
	 * @access protected
	 * @var array
	 */
	protected $google_fonts_charsets = array('latin', 'latin-ext');

    /**
	 * Returns the instance of the Milenia class.
	 *
	 * @static
	 * @access public
     * @return Milenia
	 */
    public static function getInstance() {
        if( !isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
	 * Constructor of the class.
	 */
    public function __construct(ConfigurableInterface $admin = null, $helper = null) {

		$this->admin = $admin;
		$this->helper = $helper;

        add_action('wp_enqueue_scripts', array($this, 'registerAssets'));
		add_filter('body_class', array($this, 'bodyClasses'));

        $this->template_dir = defined('MILENIA_TEMPLATE_DIRECTORY_URI') ? MILENIA_TEMPLATE_DIRECTORY_URI : get_template_directory_uri();


			$this->addRequiredThemePlugins(array(
			array(
				'name'               => esc_html__('Milenia Theme Functionality', 'milenia'),
				'slug'               => 'milenia-theme-functionality',
				'source'             => 'pluginus137/milenia-theme-functionality.zip',
				'required'           => true,
				'version'            => '1.2.7',
			),
			array(
				'name'               => esc_html__('Slider Revolution', 'milenia'),
				'slug'               => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/revslider',
				'source'             => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/revslider.zip',
				'required'           => false,
				'version'            => '6.6.20'
			),
			array(
				'name'               => esc_html__('WPBakery Page Builder', 'milenia'),
				'slug'               => 'js_composer',
				'external_url'       => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/js_composer.zip',
				'source'       => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/js_composer.zip',
				'required'           => true,
				'version'            => '7.4'
			),
			array(
				'name'               => esc_html__('Envato Market', 'milenia'),
				'slug'               => 'envato-market',
				'external_url'       => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/envato-market.zip',
				'source'       => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/envato-market.zip',
				'required'           => true,
				'version'            => '2.0.8'
			),
			array(
				'name'               => esc_html__('Meta Box Conditional Logic', 'milenia'),
				'slug'               => 'meta-box-conditional-logic',
				'source'             => 'pluginus137/meta-box-conditional-logic.zip',
				'required'           => true,
				'version'            => '1.6.13'
			),
			array(
				'name'               => esc_html__('Hotel Booking', 'milenia'),
				'slug'               => 'motopress-hotel-booking',
				'source'             => 'pluginus137/motopress-hotel-booking.zip',
				'required'           => true,
				'version'            => '4.8.8'
			),
			array(
				'name'               => esc_html__('Hotel Booking WooCommerce Payments', 'milenia'),
				'slug'               => 'mphb-woocommerce',
				'source'             => 'pluginus137/mphb-woocommerce.zip',
				'required'           => true,
				'version'            => '1.0.10'
			),
			array(
				'name'               => esc_html__('Hotel Booking Payment Request', 'milenia'),
				'slug'               => 'mphb-request-payment',
				'source'             => 'pluginus137/mphb-request-payment.zip',
				'required'           => true,
				'version'            => '1.1.9'
			),
			array(
				'name'               => esc_html__('Hotel Booking Checkout Fields', 'milenia'),
				'slug'               => 'mphb-checkout-fields',
				'source'             => 'pluginus137/mphb-checkout-fields.zip',
				'required'           => true,
				'version'            => '1.2.1'
			),
			array(
				'name'               => esc_html__('Hotel Booking Notifier', 'milenia'),
				'slug'               => 'mphb-notifier',
				'source'             => 'pluginus137/mphb-notifier.zip',
				'required'           => true,
				'version'            => '1.3.1'
			),
			array(
				'name'               => esc_html__('Hotel Booking PDF Invoices', 'milenia'),
				'slug'               => 'mphb-invoices',
				'source'             => 'pluginus137/mphb-invoices.zip',
				'required'           => true,
				'version'            => '1.3.2'
			),
			array(
				'name'               => esc_html__('WOLF - WordPress Posts Bulk Editor and Manager Professional', 'milenia'),
				'slug'               => 'bulk-editor',
				'source'             => 'http://velikorodnov.com/wordpress/sample-data/pluginusan/bulk-editor.zip',
				'required'           => true,
				'version'            => '2.0.8.2'
			)
		));

		add_action('milenia_page_prepend', array($this, 'prependToThePageContent'));
		add_action('customize_register', array($this, 'modifyCustomizer'));


		$this->initialTwitter();
    }

	protected function initialTwitter()
	{
		$consumer_key = $this->getThemeOption('milenia-twitter-consumer-key', '');
		$consumer_secret = $this->getThemeOption('milenia-twitter-consumer-secret', '');
		$access_token = $this->getThemeOption('milenia-twitter-access-token', '');
		$access_secret = $this->getThemeOption('milenia-twitter-access-secret', '');

		// Consumer Key
		define('CONSUMER_KEY', $consumer_key);
		define('CONSUMER_SECRET', $consumer_secret);

		// User Access Token
		define('ACCESS_TOKEN', $access_token);
		define('ACCESS_SECRET', $access_secret);

		// Cache Settings
		define('CACHE_ENABLED', false);
		define('CACHE_LIFETIME', 3600); // in seconds
	}

    /**
	 * Registers assets.
	 *
	 * @access protected
     * @return Milenia
	 */
    public function registerAssets() {

		// order is important
        return $this->registerStyles()
					->integrateTypographySettings()
					->registerFonts()
					->registerInlineStyles()
					->registerScripts()
					->registerInlineScripts();
    }

	/**
	 * Adds necessary classes to the body element.
	 *
	 * @access public
	 * @return array
	 */
	public function bodyClasses( $classes ) {

		if ( is_singular('post') ) {

			$custom_color_scheme_state = $this->getThemeOption('milenia-theme-skin-custom-state', '0', array(
				'overriden_by' => 'milenia-page-theme-skin-custom-state',
				'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
			));

		} else {

			$custom_color_scheme_state = $this->getThemeOption('milenia-theme-skin-custom-state', '0', array(
				'overriden_by' => 'milenia-page-theme-skin-custom-state',
				'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
			));

		}

		if ( $custom_color_scheme_state == '1' ) {
			$color_scheme = 'custom';
		} else {

			if ( is_singular('post') ) {
				$color_scheme = $this->getThemeOption('milenia-theme-skin', 'brown', array(
					'overriden_by' => 'milenia-post-skin',
					'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
				));
			}
			elseif(is_singular('mphb_room_type'))
			{
				$color_scheme = $this->getThemeOption('milenia-theme-skin', 'brown', array(
					'overriden_by' => 'accomodation-skin'
				));
			}
			elseif(is_singular('milenia-portfolio'))
			{
				$color_scheme = $this->getThemeOption('milenia-theme-skin', 'brown', array(
					'overriden_by' => 'milenia-project-skin'
				));
			}
			elseif(is_404())
			{
				$color_scheme = $this->getThemeOption('milenia-404-skin', 'main');

				if($color_scheme == 'main')
				{
					$color_scheme = $this->getThemeOption('milenia-theme-skin', 'brown');
				}
			}
			else
			{
				$color_scheme = $this->getThemeOption('milenia-theme-skin', 'brown', array(
					'overriden_by' => 'milenia-page-skin',
					'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
				));
			}
		}

		$border_layout = $this->getThemeOption('milenia-border-layout', '0', array(
			'overriden_by' => 'milenia-page-border-layout',
			'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
		));

		if($border_layout == '1')
		{
			array_push($classes, 'milenia-body--border-layout');
		}

		array_push($classes, sprintf('milenia-body--scheme-%s', $color_scheme));
		array_push($classes, 'milenia-theme');

		return $classes;
	}

    /**
	 * Registers necessary styles.
	 *
	 * @access protected
     * @return Milenia
	 */
    protected function registerStyles()
	{
		wp_register_style( 'media-element', $this->template_dir . '/assets/vendors/mediaelement/mediaelementplayer.min.css', array(), '4.2.7' );

//		  wp_enqueue_style( 'milenia-reset', $this->template_dir . '/assets/css/reset.min.css', array(), '1.0.0');
        wp_enqueue_style( 'bootstrap', $this->template_dir . '/assets/css/bootstrap.min.css', array(), '3.3.7' );
        wp_enqueue_style( 'fontawesome-brands', 'https://use.fontawesome.com/releases/v5.8.1/css/brands.css', array(), '5.8.1' );
        wp_enqueue_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.1/css/fontawesome.css', array(), '5.8.1' );
        wp_enqueue_style( 'linearicons', $this->template_dir . '/assets/css/linearicons.css', array(), '1.0.0' );
        wp_enqueue_style( 'animate-css', $this->template_dir . '/assets/css/animate.min.css', array(), '1.0.0' );
        wp_enqueue_style( 'milenia-icons', $this->template_dir . '/assets/css/milenia-icon-font.css', array(), '1.0.0' );
		wp_enqueue_style( 'fancybox', $this->template_dir . '/assets/vendors/fancybox/jquery.fancybox.min.css', array(), '3.3.5' );
		wp_enqueue_style( 'arctic-modal', $this->template_dir . '/assets/vendors/arcticmodal/jquery.arcticmodal-0.3.css', array(), '0.0.3' );
		wp_enqueue_style( 'owl-carousel', $this->template_dir . '/assets/vendors/owl-carousel/assets/owl.carousel.min.css', array(), '2.2.3' );
		wp_enqueue_style( 'monkeysan-tooltip', $this->template_dir . '/assets/vendors/monkeysan-tooltip/monkeysan-jquery-tooltip.css', array(), '1.0.0' );

		if(is_home() || is_archive() || is_single() || is_page()) {
			wp_enqueue_style('media-element');
		}

		wp_enqueue_style( 'milenia-style', $this->template_dir . '/style.css', array('bootstrap', 'animate-css', 'fontawesome', 'linearicons' ), '1.0.0' );

        return $this;
    }

	/**
	 * Registers necessary inline stylesheets.
	 *
	 * @access protected
	 * @return Milenia
	 */
	protected function registerInlineStyles()
	{
		$inline_css = '';

		if($this->getThemeOption('milenia-theme-skin-custom-state', false) == '1')
		{
			$inline_css .= $this->getCustomColorSchemeStyles();
		}

		if(!empty($inline_css))
		{
			wp_add_inline_style('milenia-style', $inline_css);
		}

		return $this;
	}

	/**
	 * Returns a piece of styles related to the custom color scheme settings.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getCustomColorSchemeStyles()
	{
		$css = array();

		if ( is_singular('post') ) {

			$custom_color_scheme_primary = $this->getThemeOption( 'milenia-theme-skin-custom-primary', null, array(
				'overriden_by' => 'milenia-page-theme-skin-custom-primary',
				'depend_on'    => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
			) );

			$custom_color_scheme_secondary = $this->getThemeOption('milenia-theme-skin-custom-secondary', null, array(
				'overriden_by' => 'milenia-page-theme-skin-custom-secondary',
				'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
			));

		} else {

			$custom_color_scheme_primary = $this->getThemeOption('milenia-theme-skin-custom-primary', null, array(
				'overriden_by' => 'milenia-page-theme-skin-custom-primary',
				'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
			));

			$custom_color_scheme_secondary = $this->getThemeOption('milenia-theme-skin-custom-secondary', null, array(
				'overriden_by' => 'milenia-page-theme-skin-custom-secondary',
				'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
			));

		}

		if(!empty($custom_color_scheme_primary) || !empty($custom_color_scheme_secondary))
		{
			$primary = array(
				'links' => array(
					'.milenia-body--scheme-custom a:not(.milenia-ln--independent):not(.milenia-btn)',
					'.milenia-body--scheme-custom .milenia-colorizer--scheme-dark a:not(.milenia-ln--independent):not(.milenia-btn)',
					'.milenia-body--scheme-custom .milenia-btn--scheme-primary.milenia-btn--link'
				),
				'color' => array(
					'.milenia-body--scheme-custom .milenia-widget .milenia-weather-indicator--style-3',
					'.milenia-body--scheme-custom .milenia-widget .milenia-event-date',
					'.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon .fa',
	                '.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon .fal',
	                '.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon .far',
	                '.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon .fab',
	                '.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon .fas',
	                '.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon .icon',
	                '.milenia-body--scheme-custom .milenia-header--dark .milenia-list--icon [class*="milenia-font-icon-"]',
					'.milenia-body--scheme-custom .milenia-counter-icon',
					'.milenia-body--scheme-custom .milenia-navigation > .current-menu-ancestor > a',
		            '.milenia-body--scheme-custom .milenia-navigation > .current-menu-parent > a',
		            '.milenia-body--scheme-custom .milenia-navigation > .current-menu-item > a',
					'.milenia-no-touchevents .milenia-body--scheme-custom .milenia-navigation > li:hover > a',
		            '.milenia-no-touchevents .milenia-body--scheme-custom .milenia-navigation > .milenia-seleceted > a',
		            '.milenia-touchevents .milenia-body--scheme-custom .milenia-navigation > .milenia-tapped > a',
					'.milenia-body--scheme-custom .milenia-navigation-vertical li:hover > a',
			        '.milenia-body--scheme-custom .milenia-navigation-vertical li.milenia-seleceted > a',
			        '.milenia-body--scheme-custom .milenia-navigation-vertical li.milenia-tapped > a',
			        '.milenia-body--scheme-custom .milenia-navigation-vertical li.current-menu-ancestor > a',
			        '.milenia-body--scheme-custom .milenia-navigation-vertical li.current-menu-parent > a',
			        '.milenia-body--scheme-custom .milenia-navigation-vertical li.current-menu-item > a',
					'.milenia-body--scheme-custom .milenia-list--icon .fa',
			        '.milenia-body--scheme-custom .milenia-list--icon .fal',
			        '.milenia-body--scheme-custom .milenia-list--icon .far',
			        '.milenia-body--scheme-custom .milenia-list--icon .fab',
			        '.milenia-body--scheme-custom .milenia-list--icon .fas',
			        '.milenia-body--scheme-custom .milenia-list--icon .icon',
			        '.milenia-body--scheme-custom .milenia-list--icon [class*="milenia-font-icon-"]',
			        '.milenia-body--scheme-custom .milenia-list--icon > li::before',
					'.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled):not([class*="milenia-list--scheme"]) ul > li::before',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled):not([class*="milenia-list--scheme"]) > li::before',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li::before',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li > .fal',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li > .far',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li > .fab',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li > .fas',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li > .icon',
			        '.milenia-body--scheme-custom .milenia-entity-content ul:not(.milenia-list--unstyled).milenia-list--icon:not([class*="milenia-list--scheme"]) li > [class*="milenia-font-icon-"]',
					'.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link):hover',
			        '.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link):focus',
			        '.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link).milenia-btn--reverse',
			        '.milenia-body--scheme-custom .milenia-entity .button.mphb-book-button:not(.milenia-btn--link):hover',
			        '.milenia-body--scheme-custom .milenia-entity .button.mphb-book-button:not(.milenia-btn--link):focus',
			        '.milenia-body--scheme-custom .milenia-entity-single .button.mphb-book-button:not(.milenia-btn--link):hover',
			        '.milenia-body--scheme-custom .milenia-entity-single .button.mphb-book-button:not(.milenia-btn--link):focus',
					'.milenia-body--scheme-custom .milenia-panels--style-2 .milenia-panels-title.milenia-panels-active > button',
					'.milenia-body--scheme-custom .milenia-icon-boxes--style-1 .milenia-icon-box-icon',
			        '.milenia-body--scheme-custom .milenia-icon-boxes--style-1 .milenia-icon-box-title',
			        '.milenia-body--scheme-custom .milenia-icon-boxes--style-3 .milenia-icon-box-icon',
					'.milenia-body--scheme-custom .milenia-entities--style-4 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-9 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-7 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-19 .milenia-entity-label',
					'.milenia-body--scheme-custom .milenia-entity-header .milenia-entity-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element .fa',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element .fal',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element .far',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element .fab',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element .fas',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element .icon',
		            '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-link-element [class*="milenia-font-icon-"]',
					'.milenia-body--scheme-custom .milenia-entities--style-10 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-11 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-12 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-13 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-14 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-15 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entities--style-16 .mphb-price',
		            '.milenia-body--scheme-custom .milenia-entity-single.milenia-entity--room .mphb-price',
		            '.milenia-body--scheme-custom .mphb-room-rates-list .mphb-price',
					'.milenia-body--scheme-custom .milenia-entities--style-19 .mphb-price',
					'.milenia-body--scheme-custom .milenia-entities--style-19 .milenia-entity-date-date',
					'.milenia-body--scheme-custom .milenia-entities--style-19 .milenia-entity-date-month-year',
					'.milenia-body--scheme-custom .owl-carousel.owl-carousel--nav-edges .owl-nav .owl-prev:hover',
		            '.milenia-body--scheme-custom .owl-carousel.owl-carousel--nav-edges .owl-nav .owl-next:hover',
					'.milenia-body--scheme-custom .milenia-section-subtitle',
					'.milenia-body--scheme-custom .milenia-testimonial .milenia-rating',
					'.milenia-body--scheme-custom .milenia-estimate .milenia-estimate-mark',
					'.milenia-body--scheme-custom .milenia-countdown .countdown-amount',
					'.milenia-body--scheme-custom .milenia-404-title',
					'.milenia-body--scheme-custom .milenia-dropcap:not(.milenia-dropcap--filled) > *:first-child:first-letter',
					'.milenia-body--scheme-custom .milenia-tabs:not(.milenia-tabs--unstyled).milenia-tabs--style-2 .milenia-active > a:not(.milenia-btn):not(.button)',
					'.milenia-body--scheme-custom .milenia-entities--style-10 .milenia-entity .button.mphb-view-details-button:hover',
		            '.milenia-body--scheme-custom .milenia-entities--style-11 .milenia-entity .button.mphb-view-details-button:hover',
		            '.milenia-body--scheme-custom .milenia-entities--style-12 .milenia-entity .button.mphb-view-details-button:hover',
		            '.milenia-body--scheme-custom .milenia-entities--style-13 .milenia-entity .button.mphb-view-details-button:hover',
					'.milenia-body--scheme-custom .milenia-entities--style-3 .milenia-entity-meta'
				),
				'border-color' => array(
					'.milenia-body--scheme-custom .milenia-divider--scheme-primary',
					'.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link)',
			        '.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link).milenia-btn--reverse:hover',
			        '.milenia-body--scheme-custom .milenia-entity .button.mphb-book-button:not(.milenia-btn--link)',
			        '.milenia-body--scheme-custom .milenia-entity-single .button.mphb-book-button:not(.milenia-btn--link)',
			        '.milenia-body--scheme-custom .widget_wysija input[type="submit"]',
					'.milenia-body--scheme-custom .milenia-entities--style-4 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-6 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-9 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-7 .milenia-entity-label',
			        '.milenia-body--scheme-custom .milenia-entities--style-19 .milenia-entity-label',
					'.milenia-body--scheme-custom .milenia-tabs:not(.milenia-tabs--unstyled).milenia-tabs--style-2 .milenia-active > a::after',
					'.milenia-body--scheme-custom .milenia-entities--style-10 .milenia-entity .button.mphb-view-details-button',
		            '.milenia-body--scheme-custom .milenia-entities--style-11 .milenia-entity .button.mphb-view-details-button',
		            '.milenia-body--scheme-custom .milenia-entities--style-12 .milenia-entity .button.mphb-view-details-button',
		            '.milenia-body--scheme-custom .milenia-entities--style-13 .milenia-entity .button.mphb-view-details-button'
				),
				'background-color' => array(
					// '::-webkit-selection',
					// '::-moz-selection',
					// '::selection',
					'.milenia-body--scheme-custom .calendar_wrap table #today',
					'.milenia-body--scheme-custom .milenia-navigation > li > a::before',
		            '.milenia-body--scheme-custom .milenia-mobile-nav-btn',
					'.milenia-body--scheme-custom .milenia-progress-bars:not(.milenia-progress-bars--secondary) .milenia-progress-bar-indicator',
					'.milenia-body--scheme-custom .milenia-colorizer--scheme-primary .milenia-colorizer-bg-color',
					'.milenia-body--scheme-custom .milenia-navigation-vertical > li > a::before',
					'.milenia-body--scheme-custom blockquote:not(.milenia-blockquote--unstyled):not(.milenia-blockquote--style-2)',
					'.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link)',
			        '.milenia-body--scheme-custom .milenia-btn--scheme-primary:not(.milenia-btn--link).milenia-btn--reverse:hover',
			        '.milenia-body--scheme-custom .milenia-entity .button.mphb-book-button:not(.milenia-btn--link)',
			        '.milenia-body--scheme-custom .milenia-entity-single .button.mphb-book-button:not(.milenia-btn--link)',
			        '.milenia-body--scheme-custom .widget_wysija input[type="submit"]',
					'.milenia-body--scheme-custom .milenia-action-buttons > a',
			        '.milenia-body--scheme-custom .milenia-action-buttons > button',
					'.milenia-body--scheme-custom .milenia-panels:not(.milenia-panels--style-2) .milenia-panels-title.milenia-panels-active > button',
					'.milenia-body--scheme-custom .milenia-icon-boxes--style-1 .milenia-icon-box:hover',
					'.milenia-body--scheme-custom .milenia-entities--style-8 .milenia-entity',
					'.milenia-body--scheme-custom .milenia-entity-link-element',
	                '.milenia-body--scheme-custom .milenia-entity.format-link .milenia-entity-body > p:only-child > a:only-child',
					'.milenia-body--scheme-custom .owl-carousel:not(.owl-carousel--nav-edges) .owl-nav .owl-prev',
		            '.milenia-body--scheme-custom .owl-carousel:not(.owl-carousel--nav-edges) .owl-nav .owl-next',
					'.milenia-body--scheme-custom .datepicker-dropdown tbody td.active.day',
					'.milenia-body--scheme-custom .milenia-singlefield-form button',
					'.milenia-body--scheme-custom .milenia-events-calendar td:hover',
		            '.milenia-body--scheme-custom .milenia-events-calendar .milenia-events-td--selected',
					'.milenia-body--scheme-custom .milenia-dropcap.milenia-dropcap--filled > *:first-child:first-letter',
					'.milenia-body--scheme-custom .milenia-tabs:not(.milenia-tabs--unstyled):not(.milenia-tabs--style-2) .milenia-tabs-nav .milenia-active > a:not(.milenia-btn):not(.button)',
					'.milenia-body--scheme-custom .milenia-banners.milenia-banners--style-2 .milenia-banner-content::before',
					'.milenia-body--scheme-custom .milenia-entities--style-2 .milenia-entity--scheme-primary .milenia-entity-content::before',
					'.milenia-body--scheme-custom .milenia-entities--style-10 .milenia-entity .button.mphb-view-details-button',
		            '.milenia-body--scheme-custom .milenia-entities--style-11 .milenia-entity .button.mphb-view-details-button',
		            '.milenia-body--scheme-custom .milenia-entities--style-12 .milenia-entity .button.mphb-view-details-button',
		            '.milenia-body--scheme-custom .milenia-entities--style-13 .milenia-entity .button.mphb-view-details-button'
				)
			);

			$secondary = array(
				'links' => array(
					'.milenia-body--scheme-custom .milenia-btn--scheme-secondary.milenia-btn--link'
				),
				'color' => array(
					'.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon .fa',
	                '.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon .fal',
	                '.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon .far',
	                '.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon .fab',
	                '.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon .fas',
	                '.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon .icon',
	                '.milenia-body--scheme-custom .milenia-header--light .milenia-list--icon [class*="milenia-font-icon-"]',
					'.milenia-body--scheme-custom .milenia-social-icon--scheme-secondary a:not(.milenia-ln--independent):not(.milenia-btn)',
					'.milenia-body--scheme-custom .milenia-btn--scheme-secondary:not(.milenia-btn--link):hover',
					'.milenia-body--scheme-custom .milenia-btn--scheme-secondary:not(.milenia-btn--link):focus',
					'.milenia-body--scheme-custom .milenia-btn--scheme-secondary:not(.milenia-btn--link).milenia-btn--reverse',
					'.milenia-body--scheme-custom .milenia-icon-boxes--style-2 .milenia-icon-box-icon'
				),
				'border-color' => array(
					'.milenia-body--scheme-custom .milenia-divider--scheme-secondary',
					'.milenia-body--scheme-custom blockquote:not(.milenia-blockquote--unstyled).milenia-blockquote--style-2',
					'.milenia-body--scheme-custom .milenia-btn--scheme-secondary:not(.milenia-btn--link)',
					'.milenia-body--scheme-custom .milenia-btn--scheme-secondary:not(.milenia-btn--link).milenia-btn--reverse:hover'
				),
				'background-color' => array(
					'.milenia-body--scheme-custom .milenia-progress-bars.milenia-progress-bars--secondary .milenia-progress-bar-indicator',
					'.milenia-body--scheme-custom .milenia-colorizer--scheme-secondary .milenia-colorizer-bg-color',
					'.milenia-body--scheme-brown .milenia-btn--scheme-secondary:not(.milenia-btn--link)',
					'.milenia-body--scheme-brown .milenia-btn--scheme-secondary:not(.milenia-btn--link).milenia-btn--reverse:hover'
				)
			);

			foreach($primary as $property => $selectors)
			{
				if(empty($selectors)) continue;
				if($property == 'links')
				{
					$css[] = sprintf('%1$s{color: %2$s; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(%3$s, %2$s), to(%2$s)); background-image: linear-gradient(to bottom, %2$s %3$s, %2$s %3$s);}', implode(',', $selectors), $custom_color_scheme_primary, '100%');
				}
				else
				{
					$css[] = sprintf('%s{%s: %s}', implode(',', $selectors), $property, $custom_color_scheme_primary);
				}
			}

			foreach($secondary as $property => $selectors)
			{
				if(empty($selectors)) continue;

				if($property == 'links')
				{
					$css[] = sprintf('%1$s{color: %2$s; background-image: -webkit-gradient(linear, left top, left bottom, color-stop(%3$s, %2$s), to(%2$s)); background-image: linear-gradient(to bottom, %2$s %3$s, %2$s %3$s);}', implode(',', $selectors), $custom_color_scheme_secondary, '100%');
				}
				else
				{
					$css[] = sprintf('%s{%s: %s}', implode(',', $selectors), $property, $custom_color_scheme_secondary);
				}
			}
		}

		return implode('', $css);
	}

    /**
	 * Registers necessary scripts.
	 *
	 * @access protected
     * @return Milenia
	 */
    protected function registerScripts()
	{
		wp_register_script( 'media-element', $this->template_dir . '/assets/vendors/mediaelement/mediaelement-and-player.min.js', array('jquery', 'isotope'), '4.2.7', true );
		wp_register_script( 'hidden-sidebar', $this->template_dir . '/assets/js/modules/milenia.sidebar-hidden.min.js', array('jquery'), '1.0.0', true );
		wp_register_script( 'milenia-tabs', $this->template_dir . '/assets/vendors/monkeysan.tabs.min.js', array('jquery'), '1.0.0', true );

        wp_enqueue_script( 'modernizr', $this->template_dir . '/assets/vendors/modernizr.js', array(), '3.6.0' );
        wp_enqueue_script( 'isotope', $this->template_dir . '/assets/vendors/isotope.pkgd.min.js', array('jquery'), '3.0.5', true );
        wp_enqueue_script( 'jquery-easings', $this->template_dir . '/assets/vendors/jquery.easing.1.3.js', array('jquery'), '1.3.0', true );
		wp_enqueue_script( 'owl-carousel', $this->template_dir . '/assets/vendors/owl-carousel/owl.carousel.min.js', array('jquery'), '2.3.3', true );
        wp_enqueue_script( 'fancybox', $this->template_dir . '/assets/vendors/fancybox/jquery.fancybox.min.js', array('jquery'), '3.3.4', true );
        wp_enqueue_script( 'monkeysan-nav', $this->template_dir . '/assets/vendors/monkeysan.jquery.nav.1.0.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'monkeysan-sameheight', $this->template_dir . '/assets/vendors/monkeysan.sameheight.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'parallax', $this->template_dir . '/assets/vendors/jquery.parallax-1.1.3.min.js', array('jquery'), '1.1.3', true );
		wp_enqueue_script( 'mad-customselect', $this->template_dir . '/assets/vendors/mad.customselect.js', array('jquery'), '1.1.1', true );
		wp_enqueue_script('momentjs', $this->template_dir . '/assets/vendors/momentjs/moment.min.js', array('jquery'), '1.0.0', true);
		wp_enqueue_script('monkeysan-tooltip', $this->template_dir . '/assets/vendors/monkeysan-tooltip/monkeysan-jquery-tooltip.js', array('jquery'), '1.0.0', true);

        wp_enqueue_script( 'nice-scroll', $this->template_dir . '/assets/vendors/nicescroll/jquery.nicescroll.min.js', array('jquery'), '3.7.6', true );
		wp_enqueue_script( 'arctic-modal', $this->template_dir . '/assets/vendors/arcticmodal/jquery.arcticmodal-0.3.min.js', array('jquery'), '0.0.3', true );

		if(is_home() || is_archive() || is_single() || is_page()) {
			wp_enqueue_script('media-element');
		}
		wp_enqueue_script( 'milenia-header-sticky-section', $this->template_dir . '/assets/js/modules/milenia.sticky-header-section.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'milenia-isotope-wrapper', $this->template_dir . '/assets/js/modules/milenia.isotope.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'milenia-events-calendar', $this->template_dir . '/assets/js/modules/milenia.events-calendar.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'milenia-alert-box', $this->template_dir . '/assets/js/modules/milenia.alert-box.min.js', array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'milenia-core', $this->template_dir . '/assets/js/milenia.app.js', array('jquery'), '1.0.0', true );
//		wp_enqueue_script('retinajs', $this->template_dir . '/assets/vendors/retina.min.js', null, '1.3.0', true);

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

		if(is_singular('mphb_room_type'))
		{
			wp_enqueue_script('milenia-tabs');
		}

        return $this;
    }



	/**
	 * Register necessary inline scripts.
	 *
	 * @access protected
	 * @return Milenia
	 */
	protected function registerInlineScripts()
	{
		wp_localize_script('milenia-core', 'MileniaOptions', array(
			'preloader' => $this->getThemeOption('page-loader-state', '0'),
			'mobile_breakpoint' => absint($this->getThemeOption('milenia-mobile-breakpoint', 767)),
			'moment_locale' => defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : $this->getThemeOption('milenia-moment-localization', 'en')
		));

		wp_localize_script('milenia-core', 'MileniaAJAXData', array(
			'url' => admin_url('admin-ajax.php'),
			'AJAX_token' => wp_create_nonce('milenia-ajax-nonce')
		));

		wp_localize_script('milenia-init', 'MileniaFancyboxI18N', array(
			'CLOSE' => esc_html__('Close', 'milenia'),
			'NEXT' => esc_html__('Next', 'milenia'),
			'PREV' => esc_html__('Previous', 'milenia'),
			'ERROR' => esc_html__('The requested content cannot be loaded. Please try again later.', 'milenia'),
			'PLAY_START' => esc_html__('Start slideshow', 'milenia'),
			'PLAY_STOP' => esc_html__('Pause slideshow', 'milenia'),
			'FULL_SCREEN' => esc_html__('Full screen', 'milenia'),
			'THUMBS' => esc_html__('Thumbnails', 'milenia'),
			'DOWNLOAD' => esc_html__('Download', 'milenia'),
			'SHARE' => esc_html__('Share', 'milenia'),
			'ZOOM' => esc_html__('Zoom', 'milenia')
		));

		return $this;
	}

	/**
	 * Appends some content parts to the beginning of the page.
	 *
	 * @access public
	 * @return void
	 */
	public function prependToThePageContent()
	{

	}

	/**
	 * .
	 *
	 * @access protected
	 * @return Milenia
	 */
	protected function integrateTypographySettings()
	{
		global $milenia_settings;

		$elements_fonts_fallback = array(
			'body' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Open Sans',
				'font-size' => '16px',
				'line-height' => '26px'
			),
			'h1' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Playfair Display',
				'font-size' => '52px',
				'line-height' => '62px'
			),
			'h2' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Playfair Display',
				'font-size' => '48px',
				'line-height' => '58px'
			),
			'h3' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Playfair Display',
				'font-size' => '36px',
				'line-height' => '43px'
			),
			'h4' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Playfair Display',
				'font-size' => '30px',
				'line-height' => '36px'
			),
			'h5' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Playfair Display',
				'font-size' => '24px',
				'line-height' => '29px'
			),
			'h6' => array(
				'google' => true,
				'font-weight' => '400',
				'font-style' => 'normal',
				'font-family' => 'Playfair Display',
				'font-size' => '18px',
				'line-height' => '22px'
			),
			'first-accented' => array(
				'google' => true,
				'font-family' => 'Playfair Display'
			),
			'second-accented' => array(
				'google' => true,
				'font-family' => 'Old Standard TT'
			)
		);

		$body_font_selectors = array('body', 'blockquote:not(.milenia-blockquote--unstyled) cite', '.milenia-font--like-body, .milenia-btn, .milenia-entity .button', '.milenia-entity-label');

		$first_accented_font_selectors = array('dl dt', '.comment .fn', '.milenia-widget .recentcomments > a', '.milenia-fullscreen-message', '.milenia-booking-form-wrapper--v2 .form-col--arrival-date .milenia-custom-select .milenia-selected-option, .milenia-booking-form-wrapper--v2 .form-col--departure-date .milenia-custom-select .milenia-selected-option, .milenia-booking-form-wrapper--v2 .form-col--rooms .milenia-custom-select .milenia-selected-option, .milenia-booking-form-wrapper--v2 .form-col--adults .milenia-custom-select .milenia-selected-option, .milenia-booking-form-wrapper--v2 .form-col--children .milenia-custom-select .milenia-selected-option', '.milenia-booking-form-wrapper--v2 .form-col--title', '.milenia-singlefield-form-titled-wrapper .milenia-singlefield-form-title', '.milenia-booking-form-wrapper--v2 .milenia-field-datepicker', '.milenia-booking-form-wrapper--v1 .milenia-field-datepicker .milenia-field-datepicker-month-year', '.milenia-estimate-mark-text', '.milenia-tabbed-carousel-thumb-caption', '.milenia-entities--style-19 .milenia-entity-date-month-year', '.milenia-entities--style-19 .mphb-price', '.milenia-info-box-title', '.milenia-panels-title > button', '.milenia-font--first-accented, .milenia-dropcap > *:first-child:first-letter, .milenia-tabs:not(.milenia-tabs--unstyled) .milenia-tabs-nav, .milenia-tabs:not(.milenia-tabs--unstyled) .milenia-tabs-nav a', '.milenia-widget .milenia-events .milenia-event-date', '.milenia-entity-content .woocommerce-MyAccount-navigation');

		$second_accented_font_selectors = array(
			'.milenia-font--second-accented',
			'.milenia-widget .milenia-events .milenia-event-date-date',
			'.milenia-entities--style-10 .mphb-price',
			'.milenia-entities--style-11 .mphb-price',
			'.milenia-entities--style-12 .mphb-price',
			'.milenia-entities--style-13 .mphb-price',
			'.milenia-entities--style-14 .mphb-price',
			'.milenia-entities--style-15 .mphb-price',
			'.milenia-entities--style-16 .mphb-price',
			'.mphb-room-rates-list .mphb-price',
			'.milenia-entity-single.milenia-entity--room .mphb-price',
			'.milenia-entity-price',
			'blockquote:not(.milenia-blockquote--unstyled)',
			'.milenia-pricing-table-price',
			'.milenia-pricing-table-price:not(:last-child)',
			'.milenia-entities--style-19 .milenia-entity-date-date',
			'.milenia-entity-link-element',
			'.milenia-entity.format-link .milenia-entity-body > p:only-child > a:only-child',
			'.milenia-testimonial blockquote',
			'.milenia-estimate-mark',
			'.milenia-booking-form-wrapper--v1 .milenia-field-datepicker-day',
			'.milenia-booking-form-wrapper--light.milenia-booking-form-wrapper--v4 .form-col--title',
			'.milenia-field-counter-value',
			'.milenia-countdown .countdown-amount',
			'.milenia-weather-indicator--style-2',
			'.milenia-404-title',
			'.milenia-widget .milenia-weather-indicator--style-3'
		);

		$full_typography_template = '
			${selector} {
				font-family: "${font-family}", "sans-serif";
				font-weight: ${font-weight};
				font-style: ${font-style};
				font-size: ${font-size};
				line-height: ${line-height};
			}
		';

		$ff_only_typography_template = '
			${selector} {
				font-family: "${font-family}", "sans-serif";
			}
		';

		$font_data_prepared = array();

		$elements = array( 'body', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'first-accented', 'second-accented');

		$inline_css_data = '';

		if(!is_array($milenia_settings)) $milenia_settings = array();

		foreach($elements as $element) {
			$option_name = sprintf('%s-font', $element);


			if(!isset($milenia_settings[$option_name]) || !is_array($milenia_settings[$option_name])) {
				if(isset($elements_fonts_fallback[$element])) {
					$milenia_settings[$option_name] = $elements_fonts_fallback[$element];
				}
			}

            if ( isset($milenia_settings[$option_name]['google']) && $milenia_settings[$option_name]['google'] !== 'false' ) {
				$font_data = $milenia_settings[$option_name];
                $font = $milenia_settings[$option_name]['font-family'];

				if(!isset($this->google_fonts[$font])) {
					$this->google_fonts[$font] = array();
				}

				$font_weight = isset($font_data['font-weight']) ? $font_data['font-weight'] : '400';
				$font_style = isset($font_data['font-style']) && $font_data['font-style'] == 'italic' ? 'italic' : '';
				$font_weight_final = $font_weight . $font_style;

				if(!in_array($font_weight_final, $this->google_fonts[$font])) {
					array_push($this->google_fonts[$font], $font_weight_final);
				}

				if(in_array($element, array('first-accented', 'second-accented'))) {
					$font_data_prepared = array(
						'${selector}' => implode(',', ($element == 'first-accented' ? $first_accented_font_selectors : $second_accented_font_selectors)),
						'${font-family}' => $font
					);
					$template = $ff_only_typography_template;
				}
				else {

					$font_data_prepared = array(
						'${selector}' => $element,
						'${font-family}' => $font,
						'${font-weight}' => (isset($font_data['font-weight']) && !empty($font_data['font-weight'])) ? $font_data['font-weight'] : $elements_fonts_fallback[$element]['font-weight'],
						'${font-style}' => (isset($font_data['font-style']) && !empty($font_data['font-style'])) ? $font_data['font-style'] : $elements_fonts_fallback[$element]['font-style'],
						'${font-size}' => isset($font_data['font-size']) ? $font_data['font-size'] : $elements_fonts_fallback[$element]['font-size'],
						'${line-height}' => isset($font_data['line-height']) ? $font_data['line-height'] : $elements_fonts_fallback[$element]['line-height']
					);

					$template = $full_typography_template;

					if($element == 'body' && count($body_font_selectors)) {
						$like_body_font_data_prepared = array(
							'${selector}' => implode(',', $body_font_selectors),
							'${font-family}' => $font
						);

						$inline_css_data .= str_replace(array_keys($like_body_font_data_prepared), array_values($like_body_font_data_prepared), $ff_only_typography_template);
					}
				}

				$inline_css_data .= str_replace(array_keys($font_data_prepared), array_values($font_data_prepared), $template);
            }

		}

		if(!empty($inline_css_data)) {
			wp_add_inline_style('milenia-style', $inline_css_data);
		}

		return $this;
	}

    /**
     * Registers theme fonts.
     *
     * @access protected
     * @return Milenia
     */
    protected function registerFonts()
	{

		global $milenia_settings;

		$fonts_charsets_state = is_array($milenia_settings) && isset($milenia_settings['milenia-google-charsets-state']) && $milenia_settings['milenia-google-charsets-state'];
		$fonts_charsets = boolval($fonts_charsets_state) && isset($milenia_settings['milenia-google-charsets']) && !empty($milenia_settings['milenia-google-charsets']) ? $milenia_settings['milenia-google-charsets'] : $this->google_fonts_charsets;

		wp_enqueue_style('milenia-google-fonts', milenia_google_fonts_url($this->google_fonts, $fonts_charsets), null, null);

        return $this;
    }

	/**
     * Returns an instance of the class that implements ConfigurableInterface interface.
     *
     * @access public
     * @return ConfigurableInterface
     */
	public function admin() {
		return $this->admin;
	}

	/**
     * Returns an instance of the class that implements HelperInterface interface.
     *
     * @access public
     * @return HelperInterface
     */
	public function helper() {
		return $this->helper;
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
    public function getThemeOption($name, $fallback = '', array $data = array())
	{
		if( !isset( $this->admin ) ) return $fallback;

		return $this->admin->getOption($name, $fallback, $data);
	}

	/**
	* Sets an option value programmatically.
	*
	* @param string $name - the option name
	* @param mixed $value - value of the option
	* @access public
	* @return void
	*/
    public function setThemeOption($name, $value)
	{
		if( !isset( $this->admin ) ) return null;

		return $this->admin->setOption($name, $value);
	}

	/**
	 * Returns array of required plugins.
	 *
	 * @access public
	 * @return array
	 */
	public function getRequiredThemePlugins()
	{
		return $this->bundled_plugins;
	}

	/**
	 * Registers required plugins.
	 *
	 * @param array $plugins
	 * @access public
	 * @return Milenia
	 */
	public function addRequiredThemePlugins($plugins)
	{
		$this->bundled_plugins = array_merge($this->bundled_plugins, $plugins);

		return $this;
	}

	/**
	 * Modifies the customizer.
	 *
	 * @access public
	 */
	public function modifyCustomizer()
	{
		global $wp_customize;

		if($wp_customize && method_exists($wp_customize, 'remove_control') && method_exists($wp_customize, 'remove_section')) {
			$wp_customize->remove_control('site_icon');
			$wp_customize->remove_control('display_header_text');
			$wp_customize->remove_section('colors');
			$wp_customize->remove_section('header_image');
		}
	}

	/**
	 * Returns true when the theme options is enabled.
	 *
	 * @access public
	 * @return bool
	 */
	public function themeOptionsEnabled()
	{
		global $milenia_settings;
		return isset($milenia_settings) && is_array($milenia_settings) && array_key_exists('milenia-post-archive-sidebar', $milenia_settings);
	}

	/**
	 * Returns true when the theme functionality plugin is enabled.
	 *
	 * @access public
	 * @return bool
	 */
	public function functionalityEnabled()
	{
		global $MileniaFunctionality;
		return isset($MileniaFunctionality);
	}
}
?>
