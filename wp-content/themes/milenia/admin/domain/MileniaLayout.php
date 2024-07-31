<?php
/**
* The MileniaLayout class.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

if( !class_exists('MileniaLayout') && interface_exists('LayoutInterface') ) {
	class MileniaLayout implements LayoutInterface
	{
		/**
		 * Configurator instance.
		 *
		 * @access protected
		 * @var ConfigurableInterface
		 */
		protected $configurator;

		/**
		 * Contains header type of the current page.
		 *
		 * @access protected
		 * @var null|string
		 */
		protected $current_page_header_type;

		/**
		 * Contains an array of current header items.
		 *
		 * @access protected
		 * @var null|array
		 */
		protected $current_header_items;

		/**
		 * Contains uri of the template directory.
		 *
		 * @access protected
		 * @var null|string
		 */
	    protected $template_dir;

		/**
		 * Contains the current page type.
		 *
		 * @access protected
		 * @var null|string
		 */
	    protected $page_type;

		/**
		 * Contains state of the breadcrumb section.
		 *
		 * @access protected
		 * @var null|string
		 */
		protected $breadcrumb_state;

		/**
		 * Contains the breadcrumb section settings.
		 *
		 * @access protected
		 * @var null|string
		 */
		protected $breadcrumb;

		/**
		 * Contains the footer sections.
		 *
		 * @access protected
		 * @var null|array
		 */
		protected $footer_sections;

		/**
		 * Contains the footer data.
		 *
		 * @access protected
		 * @var array
		 */
		protected $footer_data = array();

        protected $sidebar_state = 'milenia-has-not-sidebar';

        protected $sidebar;

		/**
		 * Constructor of the class.
		 */
	    public function __construct(ConfigurableInterface $configurator)
        {
			$this->configurator = $configurator;

			$this->template_dir = defined('MILENIA_TEMPLATE_DIRECTORY_URI') ? MILENIA_TEMPLATE_DIRECTORY_URI : get_template_directory_uri();


			add_action('wp_enqueue_scripts', array(&$this, 'initialNecessaryInfo') , 5);
			add_action('wp_enqueue_scripts', array($this, 'registerAssets'), 10);
			add_action('milenia_body_prepend', array($this, 'bodyPrepend'));
	    }

		public function initialNecessaryInfo()
		{
			$this->initialPageInfo();
			$this->initialHeaderInfo();
			$this->initialFooterInfo();
			$this->initialBreadcrumbInfo();
			$this->initial404Page();
			$this->initialTeamMemberPage();
			$this->initialSingleRoomTypePage();
			$this->initialSearchResultsPage();
			$this->initialSingleOfferPage();
			$this->initialSingleServicePage();

			return $this;
		}

		/**
		 * Initialization of the single team member page information.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialTeamMemberPage()
		{
			if(is_singular('milenia-team-members'))
			{
				$breadcrumb_settings = array_merge($this->breadcrumb, array(
					'page-title-state' => 'on',
					'breadcrumb-path-state' => 'on',
	                'content-alignment' => 'text-center',
	                'page-title-bottom-offset' => 20,
	                'padding-top' => 40,
	                'padding-right' => 15,
	                'padding-bottom' => 40,
	                'padding-left' => 15
				));

				$this->configurator->setOption('milenia-breadcrumb', $breadcrumb_settings);
				$this->breadcrumb = $breadcrumb_settings;
				$this->breadcrumb_state = 1;
			}

			return $this;
		}

		/**
		 * Initialization of the single room type page information.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialSingleRoomTypePage()
		{
			$room_type_layout = $this->configurator->getOption('accomodation-single-layout-type', 'milenia-right-sidebar');

			if(is_singular('mphb_room_type') && $room_type_layout == 'milenia-right-sidebar')
			{
				$breadcrumb_settings = array_merge($this->breadcrumb, array(
					'content-alignment' => 'text-left',
					'page-title-state' => 'off',
					'padding-top' => 16,
					'padding-bottom' => 16,
					'breadcrumb-path-state' => 'on'
				));

				$this->configurator->setOption('milenia-breadcrumb', $breadcrumb_settings);
				$this->breadcrumb = $breadcrumb_settings;
			}
			elseif(is_singular('mphb_room_type') && $room_type_layout == 'milenia-full-width')
			{
				$this->breadcrumb_state = 0;
			}

			return $this;
		}

		/**
		 * Search results page initialization.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialSearchResultsPage()
		{
			if(is_search()) {
				$breadcrumb_settings = array_merge($this->breadcrumb, array(
					'page-title-state' => 'off',
					'padding-top' => 16,
					'padding-bottom' => 16,
					'breadcrumb-path-state' => 'on'
				));

				$this->configurator->setOption('milenia-breadcrumb', $breadcrumb_settings);
				$this->breadcrumb = $breadcrumb_settings;
			}
			return $this;
		}

		/**
		 * Search results page initialization.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialSingleServicePage()
		{
			if(is_singular('mphb_room_service')) {
				$breadcrumb_settings = array_merge($this->breadcrumb, array(
					'page-title-state' => 'off',
					'padding-top' => 16,
					'padding-bottom' => 16,
					'breadcrumb-path-state' => 'on'
				));

				$this->configurator->setOption('milenia-breadcrumb', $breadcrumb_settings);
				$this->breadcrumb = $breadcrumb_settings;
			}
			return $this;
		}

		/**
		 * Initialization of the single offer page information.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialSingleOfferPage()
		{
			if(is_singular('milenia-offers'))
			{
				$breadcrumb_settings = array_merge($this->breadcrumb, array(
					'page-title-state' => 'off',
					'padding-top' => 16,
					'padding-bottom' => 16,
					'breadcrumb-path-state' => 'on'
				));

				$this->configurator->setOption('milenia-breadcrumb', $breadcrumb_settings);
				$this->breadcrumb = $breadcrumb_settings;
			}

			return $this;
		}

		/**
		 * Fills information of the current page header.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initial404Page()
		{
			if(is_404())
			{
				$this->configurator->setOption('milenia-breadcrumb-state', '0');
				$this->configurator->setOption('milenia-post-archive-vertical-padding', array(
					'padding-top' => 0,
					'padding-bottom' => 0
				));
				$this->breadcrumb_state = 0;
			}

			return $this;
		}

		/**
		 * Fills information of the current page.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialPageInfo()
		{
			$this->page_type = $this->configurator->getOption('milenia-page-type-individual', 'milenia-default', array(
				'object_id' => get_queried_object_id(),
				'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
			));

			return $this;
		}

		/**
		 * Fills information of the current page header.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialHeaderInfo()
		{
			$this->current_page_header_type = $this->configurator->getOption('milenia-header-type', 'milenia-header-layout-v5', array(
				'overriden_by' => 'milenia-page-header-type',
				'depend_on' => array('key' => 'milenia-page-header-state', 'value' => '0')
			));

			switch($this->current_page_header_type) {
				case 'milenia-header-layout-v1' :
				case 'milenia-header-layout-v2' :
					$this->current_header_items = $this->configurator->getOption('milenia-header-right-column-elements', array(), array(
						'overriden_by' => 'milenia-page-header-right-column-elements',
						'depend_on' => array('key' => 'milenia-page-header-state', 'value' => '0')
					));
				break;

				case 'milenia-header-layout-v3' :
					$this->current_header_items = $this->configurator->getOption('milenia-header-left-column-elements', array(), array(
						'overriden_by' => 'milenia-page-header-left-column-elements',
						'depend_on' => array('key' => 'milenia-page-header-state', 'value' => '0')
					));
				break;

				case 'milenia-header-layout-v5' :
				case 'milenia-header-layout-v4' :
					$this->current_header_items = array_merge($this->configurator->getOption('milenia-header-left-column-elements', array(), array(
						'overriden_by' => 'milenia-page-header-left-column-elements',
						'depend_on' => array('key' => 'milenia-page-header-state', 'value' => '0')
					)), $this->configurator->getOption('milenia-header-right-column-elements', array(), array(
						'overriden_by' => 'milenia-page-header-right-column-elements',
						'depend_on' => array('key' => 'milenia-page-header-state', 'value' => '0')
					)));
				break;
			}

			return $this;
		}

		/**
		 * Fills information of the current page breadcrumb section.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function initialBreadcrumbInfo()
		{
			global $Milenia;

			$defaults = array(
				'content-alignment' => 'text-center',
			    'background-color' => '#f1f1f1',
			    'title-color' => '#1c1c1c',
			    'page-title-bottom-offset' => 20,
			    'content-color' => '#858585',
			    'links-color' => '#1c1c1c',
			    'background-image' => 'none',
			    'background-image-url' => 'none',
			    'background-image-opacity' => 1,
			    'padding-top' => 40,
			    'padding-right' => 15,
			    'padding-bottom' => 40,
			    'padding-left' => 15,
			    'parallax' => 'on',
			    'page-title-state' => 'on',
			    'breadcrumb-path-state' => 'on'
			);

			if(is_home())
			{
				$this->breadcrumb_state = 1;
				$this->breadcrumb = $defaults;
				return $this;
			}
			if(is_singular('post'))
			{
				$post_breadcrumb_state = $this->configurator->getOption('milenia-page-breadcrumb-state', '0');

				if($post_breadcrumb_state == '0')
				{
					$this->breadcrumb_state = '0';
					$this->breadcrumb = array();
					return $this;
				}
			}

			$this->breadcrumb_state = $this->configurator->getOption('milenia-breadcrumb-state', (is_singular('page') || is_archive()) ? '1' : '0', array(
				'overriden_by' => 'milenia-page-breadcrumb-state',
				'depend_on' => array( 'key' => 'milenia-page-breadcrumb-settings-state', 'value' => '0' )
			));

			if(!$Milenia->functionalityEnabled())
			{
				$this->breadcrumb = $defaults;
			}
			else
			{
				$this->breadcrumb = $this->configurator->getOption('milenia-breadcrumb', $defaults, array(
					'overriden_by' => 'milenia-page-breadcrumb',
					'depend_on' => array( 'key' => 'milenia-page-breadcrumb-settings-state', 'value' => '0' )
				));
			}

			return $this;
		}

		/**
	     * Returns the breadcrumb section settings.
	     *
	     * @access public
	     * @return array|null
	     */
		public function getBreadcrumb()
		{
			return $this->breadcrumb_state == '1' ? $this->breadcrumb : null;
		}

		/**
	     * Returns type of the current page header.
	     *
	     * @access public
	     * @return string
	     */
	    public function getHeaderType()
		{
			return $this->current_page_header_type;
		}

	    /**
	     * Returns an array of items that are in current page header.
	     *
	     * @access public
	     * @return string
	     */
	    public function getHeaderItems()
		{
			return $this->current_header_items;
		}

		/**
		 * Registers assets that is needed for some layout parts.
		 *
		 * @access protected
	     * @return MileniaLayout
		 */
	    public function registerAssets()
		{
			// order is important
	        return $this->registerStyles()
						->registerInlineStyles()
						->registerScripts()
						->registerInlineScripts();
	    }


		/**
		 * Registers necessary styles.
		 *
		 * @access protected
	     * @return MileniaLayout
		 */
	    protected function registerStyles() {


	        return $this;
	    }

		/**
		 * Registers necessary inline stylesheets.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function registerInlineStyles()
		{
			$footer_styles = '';

			if($this->breadcrumb_state == '1') {
				$breadcrumb_styles = '';
				$breadcrumb_prepared = array();

				foreach ($this->breadcrumb as $key => $value) {
					$breadcrumb_prepared[sprintf('${%s}', $key)] = $value;
				}

				$breadcrumb_container_template = '.milenia-breadcrumb {
					color: ${content-color};
					padding-top: ${padding-top}px;
					padding-right: ${padding-right}px;
					padding-bottom: ${padding-bottom}px;
					padding-left: ${padding-left}px;
				}';

				$breadcrumb_title_template = '.milenia-breadcrumb .milenia-page-title {
					color: ${title-color};
					margin-bottom: ${page-title-bottom-offset}px;
				}';

				$breadcrumb_links_template = '.milenia-breadcrumb a {
					color: ${links-color} !important;
					background-image: -webkit-gradient(linear, left top, left bottom, color-stop(100%, ${links-color}), to(${links-color})) !important;
	                background-image: linear-gradient(to bottom, ${links-color} 100%, ${links-color} 100%) !important;
				}';

				$breadcrumb_styles .= str_replace(array_keys($breadcrumb_prepared), array_values($breadcrumb_prepared), $breadcrumb_container_template);
				$breadcrumb_styles .= str_replace(array_keys($breadcrumb_prepared), array_values($breadcrumb_prepared), $breadcrumb_title_template);
				$breadcrumb_styles .= str_replace(array_keys($breadcrumb_prepared), array_values($breadcrumb_prepared), $breadcrumb_links_template);

				wp_add_inline_style('milenia-style', $breadcrumb_styles);
			}


			foreach ($this->footer_data as $fs_id => $fs_data) {
				if($fs_id == 'footer-copyright-section') continue;

				if(is_array($fs_data['color-settings-states']) && in_array('links-color', $fs_data['color-settings-states']))
				{
					$fs_links_template = '#milenia-${fs-id} a {
						color: ${links-color} !important;
						background-image: -webkit-gradient(linear, left top, left bottom, color-stop(100%, ${links-color}), to(${links-color})) !important;
		                background-image: linear-gradient(to bottom, ${links-color} 100%, ${links-color} 100%) !important;
					}';

					$fs_links_prepared = array(
						'${fs-id}' => $fs_id,
						'${links-color}' => $fs_data['links-color-custom']
					);

					$footer_styles .= str_replace(array_keys($fs_links_prepared), array_values($fs_links_prepared), $fs_links_template);
				}

				if(is_array($fs_data['color-settings-states']) && in_array('text-color', $fs_data['color-settings-states']))
				{
					$fs_text_template = '#milenia-${fs-id} {
						color: ${text-color};
					}';

					$fs_text_prepared = array(
						'${fs-id}' => $fs_id,
						'${text-color}' => $fs_data['text-color-custom']
					);

					$footer_styles .= str_replace(array_keys($fs_text_prepared), array_values($fs_text_prepared), $fs_text_template);
				}

				if(is_array($fs_data['color-settings-states']) && in_array('headings-color', $fs_data['color-settings-states']))
				{
					$fs_headings_template = '#milenia-${fs-id} h1:not(.milenia-color--unchangeable),
					#milenia-${fs-id} h2:not(.milenia-color--unchangeable),
					#milenia-${fs-id} h3:not(.milenia-color--unchangeable),
					#milenia-${fs-id} h4:not(.milenia-color--unchangeable),
					#milenia-${fs-id} h5:not(.milenia-color--unchangeable),
					#milenia-${fs-id} h6:not(.milenia-color--unchangeable) {
						color: ${headings-color};
					}';

					$fs_headings_prepared = array(
						'${fs-id}' => $fs_id,
						'${headings-color}' => $fs_data['headings-color-custom']
					);

					$footer_styles .= str_replace(array_keys($fs_headings_prepared), array_values($fs_headings_prepared), $fs_headings_template);
				}

				if(intval($fs_data['widgets-border']) == 1)
				{
					$fs_widgets_border_template = '#milenia-${fs-id}.milenia-footer-row.milenia-footer-row--widget-border .milenia-widget::after {
						border-color: ${border-color};
					}';

					$fs_widgets_border_prepared = array(
						'${fs-id}' => $fs_id,
						'${border-color}' => $fs_data['widgets-border-color']
					);

					$footer_styles .= str_replace(array_keys($fs_widgets_border_prepared), array_values($fs_widgets_border_prepared), $fs_widgets_border_template);
				}

				$fs_border_top_template = '#milenia-${fs-id} .milenia-footer-row--inner {
					border-color: ${border-color};
				}';

				$fs_border_top_prepared = array(
					'${fs-id}' => $fs_id,
					'${border-color}' => $fs_data['border-top-color']
				);

				$footer_styles .= str_replace(array_keys($fs_border_top_prepared), array_values($fs_border_top_prepared), $fs_border_top_template);
			}

			if(!empty($footer_styles))
			{
				wp_add_inline_style('milenia-style', $footer_styles);
			}

			return $this;
		}

		/**
		 * Registers necessary scripts.
		 *
		 * @access protected
	     * @return MileniaLayout
		 */
	    protected function registerScripts() {
			wp_register_script( 'hidden-sidebar', $this->template_dir . '/assets/js/modules/milenia.sidebar-hidden.min.js', array('jquery'), '1.0.0', true );

			if(is_array($this->current_header_items) && (in_array('hidden-sidebar-btn', $this->current_header_items) || in_array('menu-btn', $this->current_header_items)))
			{
				wp_enqueue_script('hidden-sidebar');
			}

	        return $this;
	    }

		/**
		 * Register necessary inline scripts.
		 *
		 * @access protected
		 * @return MileniaLayout
		 */
		protected function registerInlineScripts()
		{
			if(is_array($this->footer_data) && !empty($this->footer_data))
			{
				$footer_widgets_prepared = array();

				foreach($this->footer_data as $fs_id => $fs_data)
				{
					if($fs_id == 'footer-copyright-section') continue;
					$footer_widgets_prepared[$fs_id] = $fs_data['widgets-settings'];
				}

				wp_localize_script('milenia-core', 'MileniaFooterWidgetsSettings', $footer_widgets_prepared);
			}

			return $this;
		}

        /**
         * Checks sidebar settings of the page.
         *
         * @access protected
         * @return void
         */
        protected function checkSidebarState()
        {
        	global $Milenia;

        	if(!$Milenia->themeOptionsEnabled())
        	{
        		if(is_archive() || is_home())
        		{
        			if(is_active_sidebar('widget-area-1'))
        			{
        				$this->sidebar_state = 'milenia-right-sidebar';
        				$this->sidebar = 'widget-area-1';
        			}
        		}
        		elseif(is_singular('page'))
        		{
        			if(is_active_sidebar('widget-area-5'))
        			{
        				$this->sidebar_state = 'milenia-right-sidebar';
        				$this->sidebar = 'widget-area-5';
        			}
        		}
        		elseif(is_singular('post'))
        		{
        			if(is_active_sidebar('widget-area-2'))
        			{
        				$this->sidebar_state = 'milenia-right-sidebar';
        				$this->sidebar = 'widget-area-2';
        			}
        		}

        		return;
        	}

			if(is_page())
			{
				$this->sidebar_state = $this->configurator->getOption('milenia-page-layout', 'milenia-has-not-sidebar', array(
					'overriden_by' => 'milenia-page-layout-individual',
					'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
				));
	            $this->sidebar = $this->configurator->getOption('milenia-page-sidebar', 'widget-area-1', array(
					'overriden_by' => 'milenia-page-sidebar-individual',
					'depend_on' => array( 'key' => 'milenia-page-settings-inherit-individual', 'value' => '0' )
				));
			}
			elseif(is_singular('post'))
			{
				$this->sidebar_state = $this->configurator->getOption('post-single-layout', 'milenia-has-not-sidebar', array(
					'overriden_by' => 'post-single-layout-individual',
					'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
				));
	            $this->sidebar = $this->configurator->getOption('post-single-sidebar', 'widget-area-1', array(
					'overriden_by' => 'post-single-sidebar-individual',
					'depend_on' => array( 'key' => 'post-single-layout-state-individual', 'value' => '0' )
				));
			}
			elseif(is_singular('mphb_room_type'))
			{
				$this->sidebar_state = $this->configurator->getOption('accomodation-single-layout-type', 'milenia-right-sidebar');
	            $this->sidebar = $this->configurator->getOption('accomodation-single-sidebar', 'widget-area-2');
			}
			else
			{
				$this->sidebar_state = $this->configurator->getOption('milenia-post-archive-layout', 'milenia-has-not-sidebar');
	            $this->sidebar = $this->configurator->getOption('milenia-post-archive-sidebar', 'widget-area-1');
			}
        }

		/**
		 * Returns true in case the page is full width.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isFullWidth()
		{
			if(is_page())
			{
				$this->checkSidebarState();
				return $this->sidebar_state == 'milenia-full-width';
			}

			return false;
		}

        /**
         * Returns sidebar state.
         *
         * @access public
         * @return string
         */
        public function getSidebarState()
        {
            $this->checkSidebarState();
            return in_array($this->sidebar_state, array('milenia-left-sidebar', 'milenia-right-sidebar')) ? 'milenia-has-sidebar' : 'milenia-has-not-sidebar';
        }

        /**
         * Returns the current page sidebar.
         *
         * @access public
         * @return string
         */
        public function getSidebar()
        {
            $this->checkSidebarState();
            return $this->sidebar;
        }

        /**
         * Returns true in case the page has sidebar.
         *
         * @access public
         * @return bool
         */
        public function hasSidebar()
        {
            $this->checkSidebarState();
            return in_array($this->sidebar_state, array('milenia-left-sidebar', 'milenia-right-sidebar')) && is_active_sidebar($this->sidebar);
        }

		/**
         * Gets all the footer data.
         *
         * @access public
         * @return void
         */
		protected function initialFooterInfo()
		{
			global $Milenia;

			$footer_not_overriden = intval($this->configurator->getOption('milenia-page-footer-state-individual', 1));

			$this->footer_sections = $this->configurator->getOption('footer-sections', array('footer-section-1', 'footer-section-2'), array(
				'overriden_by' => 'milenia-page-footer-sections',
				'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
			));

			if($footer_not_overriden || !$Milenia->themeOptionsEnabled())
			{
				$copyright_section = $this->configurator->getOption('footer-copyright-section-state', 1);

				if($copyright_section)
				{
					$this->footer_sections[] = 'footer-copyright-section';
					$this->footer_data['footer-copyright-section'] = array(
						'text' => $this->configurator->getOption('footer-copyright-section-text', sprintf(esc_html__('Copyright &copy; %d %s. All Rights Reserved.', 'milenia'), date('Y'), get_bloginfo('name'))),
						'bg' => $this->configurator->getOption('footer-copyright-section-bg', 'dark'),
						'bg-custom' => $this->configurator->getOption('footer-copyright-section-bg-custom', '#1c1c1c'),
						'text-color' => $this->configurator->getOption('footer-copyright-section-text-color', '#858585'),
						'border-top-color' => $this->configurator->getOption('footer-copyright-section-border-top-color', '#2e2e2e'),
						'full-width' => $this->configurator->getOption('footer-copyright-section-full-width', 1)
					);
				}
			}

			if(in_array('footer-section-1', $this->footer_sections))
			{
				$this->footer_data['footer-section-1'] = array(
					'src' => $this->configurator->getOption('footer-section-1-src', 'widget-area-3', array(
						'overriden_by' => 'milenia-page-footer-section-1-src',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'padding-x' => $this->configurator->getOption('footer-section-1-padding-x', 1, array(
						'overriden_by' => 'milenia-page-footer-section-1-padding-x',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg' => $this->configurator->getOption('footer-section-1-bg', 'dark', array(
						'overriden_by' => 'milenia-page-footer-section-1-bg',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg-custom' => $this->configurator->getOption('footer-section-1-bg-custom', '#1c1c1c', array(
						'overriden_by' => 'milenia-page-footer-section-1-bg-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'color-settings-states' => $this->configurator->getOption('footer-section-1-color-settings-states', array(), array(
						'overriden_by' => 'milenia-page-footer-section-1-color-settings-states',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'text-color-custom' => $this->configurator->getOption('footer-section-1-text-color-custom', '#858585', array(
						'overriden_by' => 'milenia-page-footer-section-1-text-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'links-color-custom' => $this->configurator->getOption('footer-section-1-links-color-custom', '#ae745a', array(
						'overriden_by' => 'milenia-page-footer-section-1-links-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'headings-color-custom' => $this->configurator->getOption('footer-section-1-headings-color-custom', '#ffffff', array(
						'overriden_by' => 'milenia-page-footer-section-1-headings-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'columns' => $this->configurator->getOption('footer-section-1-columns', 'milenia-grid--cols-4', array(
						'overriden_by' => 'milenia-page-footer-section-1-columns',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'full-width' => $this->configurator->getOption('footer-section-1-full-width', 1, array(
						'overriden_by' => 'milenia-page-footer-section-1-full-width',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'uppercased-titles' => $this->configurator->getOption('footer-section-1-uppercased-titles', 0, array(
						'overriden_by' => 'milenia-page-footer-section-1-uppercased-titles',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'large-offset' => $this->configurator->getOption('footer-section-1-large-offset', 0, array(
						'overriden_by' => 'milenia-page-footer-section-1-large-offset',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top' => $this->configurator->getOption('footer-section-1-border-top', 1, array(
						'overriden_by' => 'milenia-page-footer-section-1-border-top',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top-color' => $this->configurator->getOption('footer-section-1-border-top-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-1-border-top-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border' => $this->configurator->getOption('footer-section-1-widgets-border', 1, array(
						'overriden_by' => 'milenia-page-footer-section-1-widgets-border',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border-color' => $this->configurator->getOption('footer-section-1-widgets-border-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-1-widgets-border-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-settings' => $this->configurator->getOption('footer-section-1-widgets', array(), array(
						'overriden_by' => 'milenia-page-footer-section-1-widgets',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'responsive-breakpoint' => $this->configurator->getOption('footer-section-1-responsive-breakpoint', 'milenia-grid--responsive-sm', array(
						'overriden_by' => 'milenia-page-footer-section-1-responsive-breakpoint',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					))
				);

				if($footer_not_overriden)
				{
					$this->footer_data['footer-section-1']['padding-y'] = $this->configurator->getOption('footer-section-1-padding-y', array('padding-top' => 90, 'padding-bottom' => 90));
				}
				else
				{
					$this->footer_data['footer-section-1']['padding-y'] = array(
						'padding-top' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-1-padding-y-top', 90)),
						'padding-bottom' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-1-padding-y-bottom', 90))
					);
				}
			}

			if(in_array('footer-section-2', $this->footer_sections))
			{
				$this->footer_data['footer-section-2'] = array(
					'src' => $this->configurator->getOption('footer-section-2-src', 'widget-area-4', array(
						'overriden_by' => 'milenia-page-footer-section-2-src',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'padding-x' => $this->configurator->getOption('footer-section-2-padding-x', 1, array(
						'overriden_by' => 'milenia-page-footer-section-2-padding-x',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg' => $this->configurator->getOption('footer-section-2-bg', 'dark', array(
						'overriden_by' => 'milenia-page-footer-section-2-bg',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg-custom' => $this->configurator->getOption('footer-section-2-bg-custom', '#1c1c1c', array(
						'overriden_by' => 'milenia-page-footer-section-2-bg-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'color-settings-states' => $this->configurator->getOption('footer-section-2-color-settings-states', array(), array(
						'overriden_by' => 'milenia-page-footer-section-2-color-settings-states',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'text-color-custom' => $this->configurator->getOption('footer-section-2-text-color-custom', '#858585', array(
						'overriden_by' => 'milenia-page-footer-section-2-text-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'links-color-custom' => $this->configurator->getOption('footer-section-2-links-color-custom', '#ae745a', array(
						'overriden_by' => 'milenia-page-footer-section-2-links-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'headings-color-custom' => $this->configurator->getOption('footer-section-2-headings-color-custom', '#ffffff', array(
						'overriden_by' => 'milenia-page-footer-section-2-headings-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'columns' => $this->configurator->getOption('footer-section-2-columns', 'milenia-grid--cols-4', array(
						'overriden_by' => 'milenia-page-footer-section-2-columns',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'full-width' => $this->configurator->getOption('footer-section-2-full-width', 1, array(
						'overriden_by' => 'milenia-page-footer-section-2-full-width',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'uppercased-titles' => $this->configurator->getOption('footer-section-2-uppercased-titles', 0, array(
						'overriden_by' => 'milenia-page-footer-section-2-uppercased-titles',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'large-offset' => $this->configurator->getOption('footer-section-2-large-offset', 0, array(
						'overriden_by' => 'milenia-page-footer-section-2-large-offset',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top' => $this->configurator->getOption('footer-section-2-border-top', 1, array(
						'overriden_by' => 'milenia-page-footer-section-2-border-top',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top-color' => $this->configurator->getOption('footer-section-2-border-top-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-2-border-top-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border' => $this->configurator->getOption('footer-section-2-widgets-border', 1, array(
						'overriden_by' => 'milenia-page-footer-section-2-widgets-border',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border-color' => $this->configurator->getOption('footer-section-2-widgets-border-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-2-widgets-border-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-settings' => $this->configurator->getOption('footer-section-2-widgets', array(), array(
						'overriden_by' => 'milenia-page-footer-section-2-widgets',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'responsive-breakpoint' => $this->configurator->getOption('footer-section-2-responsive-breakpoint', 'milenia-grid--responsive-sm', array(
						'overriden_by' => 'milenia-page-footer-section-2-responsive-breakpoint',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					))
				);

				if($footer_not_overriden)
				{
					$this->footer_data['footer-section-2']['padding-y'] = $this->configurator->getOption('footer-section-2-padding-y', array('padding-top' => 90, 'padding-bottom' => 90));
				}
				else
				{
					$this->footer_data['footer-section-2']['padding-y'] = array(
						'padding-top' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-2-padding-y-top', 90)),
						'padding-bottom' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-2-padding-y-bottom', 90))
					);
				}
			}

			if(in_array('footer-section-3', $this->footer_sections))
			{
				$this->footer_data['footer-section-3'] = array(
					'src' => $this->configurator->getOption('footer-section-3-src', 'widget-area-4', array(
						'overriden_by' => 'milenia-page-footer-section-3-src',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'padding-x' => $this->configurator->getOption('footer-section-3-padding-x', 1, array(
						'overriden_by' => 'milenia-page-footer-section-3-padding-x',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg' => $this->configurator->getOption('footer-section-3-bg', 'dark', array(
						'overriden_by' => 'milenia-page-footer-section-3-bg',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg-custom' => $this->configurator->getOption('footer-section-3-bg-custom', '#1c1c1c', array(
						'overriden_by' => 'milenia-page-footer-section-3-bg-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'color-settings-states' => $this->configurator->getOption('footer-section-3-color-settings-states', array(), array(
						'overriden_by' => 'milenia-page-footer-section-3-color-settings-states',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'text-color-custom' => $this->configurator->getOption('footer-section-3-text-color-custom', '#858585', array(
						'overriden_by' => 'milenia-page-footer-section-3-text-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'links-color-custom' => $this->configurator->getOption('footer-section-3-links-color-custom', '#ae745a', array(
						'overriden_by' => 'milenia-page-footer-section-3-links-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'headings-color-custom' => $this->configurator->getOption('footer-section-3-headings-color-custom', '#ffffff', array(
						'overriden_by' => 'milenia-page-footer-section-3-headings-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'columns' => $this->configurator->getOption('footer-section-3-columns', 'milenia-grid--cols-4', array(
						'overriden_by' => 'milenia-page-footer-section-3-columns',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'full-width' => $this->configurator->getOption('footer-section-3-full-width', 1, array(
						'overriden_by' => 'milenia-page-footer-section-3-full-width',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'uppercased-titles' => $this->configurator->getOption('footer-section-3-uppercased-titles', 0, array(
						'overriden_by' => 'milenia-page-footer-section-3-uppercased-titles',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'large-offset' => $this->configurator->getOption('footer-section-3-large-offset', 0, array(
						'overriden_by' => 'milenia-page-footer-section-3-large-offset',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top' => $this->configurator->getOption('footer-section-3-border-top', 1, array(
						'overriden_by' => 'milenia-page-footer-section-3-border-top',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top-color' => $this->configurator->getOption('footer-section-3-border-top-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-3-border-top-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border' => $this->configurator->getOption('footer-section-3-widgets-border', 1, array(
						'overriden_by' => 'milenia-page-footer-section-3-widgets-border',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border-color' => $this->configurator->getOption('footer-section-3-widgets-border-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-3-widgets-border-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-settings' => $this->configurator->getOption('footer-section-3-widgets', array(), array(
						'overriden_by' => 'milenia-page-footer-section-3-widgets',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'responsive-breakpoint' => $this->configurator->getOption('footer-section-3-responsive-breakpoint', 'milenia-grid--responsive-sm', array(
						'overriden_by' => 'milenia-page-footer-section-3-responsive-breakpoint',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					))
				);

				if($footer_not_overriden)
				{
					$this->footer_data['footer-section-3']['padding-y'] = $this->configurator->getOption('footer-section-3-padding-y', array('padding-top' => 90, 'padding-bottom' => 90));
				}
				else
				{
					$this->footer_data['footer-section-3']['padding-y'] = array(
						'padding-top' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-3-padding-y-top', 90)),
						'padding-bottom' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-3-padding-y-bottom', 90))
					);
				}
			}

			if(in_array('footer-section-4', $this->footer_sections))
			{
				$this->footer_data['footer-section-4'] = array(
					'src' => $this->configurator->getOption('footer-section-4-src', 'widget-area-2', array(
						'overriden_by' => 'milenia-page-footer-section-4-src',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'padding-x' => $this->configurator->getOption('footer-section-4-padding-x', 1, array(
						'overriden_by' => 'milenia-page-footer-section-4-padding-x',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg' => $this->configurator->getOption('footer-section-4-bg', 'dark', array(
						'overriden_by' => 'milenia-page-footer-section-4-bg',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg-custom' => $this->configurator->getOption('footer-section-4-bg-custom', '#1c1c1c', array(
						'overriden_by' => 'milenia-page-footer-section-4-bg-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'color-settings-states' => $this->configurator->getOption('footer-section-4-color-settings-states', array(), array(
						'overriden_by' => 'milenia-page-footer-section-4-color-settings-states',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'text-color-custom' => $this->configurator->getOption('footer-section-4-text-color-custom', '#858585', array(
						'overriden_by' => 'milenia-page-footer-section-4-text-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'links-color-custom' => $this->configurator->getOption('footer-section-4-links-color-custom', '#ae745a', array(
						'overriden_by' => 'milenia-page-footer-section-4-links-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'headings-color-custom' => $this->configurator->getOption('footer-section-4-headings-color-custom', '#ffffff', array(
						'overriden_by' => 'milenia-page-footer-section-4-headings-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'columns' => $this->configurator->getOption('footer-section-4-columns', 'milenia-grid--cols-4', array(
						'overriden_by' => 'milenia-page-footer-section-4-columns',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'full-width' => $this->configurator->getOption('footer-section-4-full-width', 1, array(
						'overriden_by' => 'milenia-page-footer-section-4-full-width',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'uppercased-titles' => $this->configurator->getOption('footer-section-4-uppercased-titles', 0, array(
						'overriden_by' => 'milenia-page-footer-section-4-uppercased-titles',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'large-offset' => $this->configurator->getOption('footer-section-4-large-offset', 0, array(
						'overriden_by' => 'milenia-page-footer-section-4-large-offset',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top' => $this->configurator->getOption('footer-section-4-border-top', 1, array(
						'overriden_by' => 'milenia-page-footer-section-4-border-top',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top-color' => $this->configurator->getOption('footer-section-4-border-top-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-4-border-top-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border' => $this->configurator->getOption('footer-section-4-widgets-border', 1, array(
						'overriden_by' => 'milenia-page-footer-section-4-widgets-border',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border-color' => $this->configurator->getOption('footer-section-4-widgets-border-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-4-widgets-border-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-settings' => $this->configurator->getOption('footer-section-4-widgets', array(), array(
						'overriden_by' => 'milenia-page-footer-section-4-widgets',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'responsive-breakpoint' => $this->configurator->getOption('footer-section-4-responsive-breakpoint', 'milenia-grid--responsive-sm', array(
						'overriden_by' => 'milenia-page-footer-section-4-responsive-breakpoint',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					))
				);

				if($footer_not_overriden)
				{
					$this->footer_data['footer-section-4']['padding-y'] = $this->configurator->getOption('footer-section-4-padding-y', array('padding-top' => 90, 'padding-bottom' => 90));
				}
				else
				{
					$this->footer_data['footer-section-4']['padding-y'] = array(
						'padding-top' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-4-padding-y-top', 90)),
						'padding-bottom' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-4-padding-y-bottom', 90))
					);
				}
			}

			if(in_array('footer-section-5', $this->footer_sections))
			{
				$this->footer_data['footer-section-5'] = array(
					'src' => $this->configurator->getOption('footer-section-5-src', 'widget-area-2', array(
						'overriden_by' => 'milenia-page-footer-section-5-src',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'padding-x' => $this->configurator->getOption('footer-section-5-padding-x', 1, array(
						'overriden_by' => 'milenia-page-footer-section-5-padding-x',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg' => $this->configurator->getOption('footer-section-5-bg', 'dark', array(
						'overriden_by' => 'milenia-page-footer-section-5-bg',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'bg-custom' => $this->configurator->getOption('footer-section-5-bg-custom', '#1c1c1c', array(
						'overriden_by' => 'milenia-page-footer-section-5-bg-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'color-settings-states' => $this->configurator->getOption('footer-section-5-color-settings-states', array(), array(
						'overriden_by' => 'milenia-page-footer-section-5-color-settings-states',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'text-color-custom' => $this->configurator->getOption('footer-section-5-text-color-custom', '#858585', array(
						'overriden_by' => 'milenia-page-footer-section-5-text-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'links-color-custom' => $this->configurator->getOption('footer-section-5-links-color-custom', '#ae745a', array(
						'overriden_by' => 'milenia-page-footer-section-5-links-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'headings-color-custom' => $this->configurator->getOption('footer-section-5-headings-color-custom', '#ffffff', array(
						'overriden_by' => 'milenia-page-footer-section-5-headings-color-custom',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'columns' => $this->configurator->getOption('footer-section-5-columns', 'milenia-grid--cols-4', array(
						'overriden_by' => 'milenia-page-footer-section-5-columns',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'full-width' => $this->configurator->getOption('footer-section-5-full-width', 1, array(
						'overriden_by' => 'milenia-page-footer-section-5-full-width',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'uppercased-titles' => $this->configurator->getOption('footer-section-5-uppercased-titles', 0, array(
						'overriden_by' => 'milenia-page-footer-section-5-uppercased-titles',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'large-offset' => $this->configurator->getOption('footer-section-5-large-offset', 0, array(
						'overriden_by' => 'milenia-page-footer-section-5-large-offset',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top' => $this->configurator->getOption('footer-section-5-border-top', 1, array(
						'overriden_by' => 'milenia-page-footer-section-5-border-top',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'border-top-color' => $this->configurator->getOption('footer-section-5-border-top-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-5-border-top-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border' => $this->configurator->getOption('footer-section-5-widgets-border', 1, array(
						'overriden_by' => 'milenia-page-footer-section-5-widgets-border',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-border-color' => $this->configurator->getOption('footer-section-5-widgets-border-color', '#363636', array(
						'overriden_by' => 'milenia-page-footer-section-5-widgets-border-color',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'widgets-settings' => $this->configurator->getOption('footer-section-5-widgets', array(), array(
						'overriden_by' => 'milenia-page-footer-section-5-widgets',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					)),
					'responsive-breakpoint' => $this->configurator->getOption('footer-section-5-responsive-breakpoint', 'milenia-grid--responsive-sm', array(
						'overriden_by' => 'milenia-page-footer-section-5-responsive-breakpoint',
						'depend_on' => array( 'key' => 'milenia-page-footer-state-individual', 'value' => '0' )
					))
				);

				if($footer_not_overriden)
				{
					$this->footer_data['footer-section-5']['padding-y'] = $this->configurator->getOption('footer-section-5-padding-y', array('padding-top' => 90, 'padding-bottom' => 90));
				}
				else
				{
					$this->footer_data['footer-section-5']['padding-y'] = array(
						'padding-top' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-5-padding-y-top', 90)),
						'padding-bottom' => sprintf('%dpx', $this->configurator->getOption('milenia-page-footer-section-5-padding-y-bottom', 90))
					);
				}
			}

			return $this;
		}

        /**
         * Returns true in case the page has footer.
         *
         * @access public
         * @return bool
         */
        public function hasFooter()
        {
			return is_array($this->footer_sections) && count($this->footer_sections);
        }

		/**
         * Returns the footer data.
         *
         * @access public
         * @return array
         */
		public function getFooter()
		{
			return $this->footer_data;
		}


        /**
         * Returns array of vertical padding of the content area.
         *
         * @access public
         * @return array
         */
        public function verticalContentPadding()
        {
			$milenia_page_vertical_padding = array();

			if(is_singular(array('mphb_room_type')))
			{
				if(is_singular('mphb_room_type'))
				{
					$room_type_layout = $this->configurator->getOption('accomodation-single-layout-type', 'milenia-right-sidebar');

					if($room_type_layout == 'milenia-full-width')
					{
						$milenia_page_vertical_padding = array('padding-top' => 0, 'padding-bottom' => 0);
					}
				}
			}
			else {
				if(is_page())
				{
					if($this->configurator->getOption('milenia-page-settings-inherit-individual', '1') == '0')
					{
						$milenia_page_vertical_padding = array();

						$milenia_page_vertical_padding['padding-top'] =  $this->configurator->getOption('milenia-page-top-padding-individual', 95);
						$milenia_page_vertical_padding['padding-bottom'] =  $this->configurator->getOption('milenia-page-bottom-padding-individual', 95);
					}
					else
					{
						$milenia_page_vertical_padding = $this->configurator->getOption('milenia-page-vertical-padding', array('padding-top' => 95, 'padding-bottom' => 95));
					}
				}
				else
				{
					$milenia_page_vertical_padding = $this->configurator->getOption('milenia-post-archive-vertical-padding', array('padding-top' => 95, 'padding-bottom' => 95));
				}
			}

			if(empty($milenia_page_vertical_padding))
			{
				return $milenia_page_vertical_padding;
			}

            return array_map( array(&$this, 'verticalContentPaddingCallback') , array_values($milenia_page_vertical_padding), array_keys($milenia_page_vertical_padding));
        }

        /**
         * Callback for mapping array of vertical paddings.
         *
         * @access public
         * @return array
         */
        protected function verticalContentPaddingCallback($property, $value)
        {
            return sprintf('%s: %spx;', $value, $property);
        }

        /**
    	 * Returns main layout classes.
    	 *
    	 * @param string $column_name
    	 * @access public
         * @return string
    	 */
        public function getMainLayoutClasses($column_name = 'main') {
            $this->checkSidebarState();

            $layout_classes = array(
    			'main' => array(),
    			'side' => array()
    		);

            if(!is_active_sidebar($this->sidebar))
            {
            	$layout_classes['main'] = array('col');
            }
            else
            {
	    		switch($this->sidebar_state) {
	    			case 'milenia-has-not-sidebar' :
	    			case 'milenia-full-width' :
	    				$layout_classes['main'] = array('col');
	    			break;
	    			case 'milenia-left-sidebar' :
	    				$layout_classes['main'] = array('col-lg-9', 'col-md-8', 'order-md-last');
	    				$layout_classes['side'] = array('col-lg-3', 'col-md-4', 'order-md-first');
	    			break;
	    			case 'milenia-right-sidebar' :
						$layout_classes['main'] = array('col-lg-9', 'col-md-8', 'order-md-first');
						$layout_classes['side'] = array('col-lg-3', 'col-md-4', 'order-md-last');
	    			break;
	    		}
    		}

    		return implode(' ', $layout_classes[$column_name]);
        }

		/**
	     * Returns the current page type.
	     *
	     * @access public
	     * @return string|null
	     */
	    public function getPageType()
		{
			return $this->page_type;
		}

		/**
		 * Adds some necessary layout parts to the body element.
		 *
		 * @access public
		 * @return void
		 */
		public function bodyPrepend()
		{
			$vertical_nav_logo = $this->configurator->getOption('milenia-header-vertical-menu-logo', null, array(
				'overriden_by' => 'milenia-page-header-vertical-menu-logo',
				'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
			));

			if(is_array($vertical_nav_logo) && isset($vertical_nav_logo['full_url']))
			{
				$vertical_nav_logo = $vertical_nav_logo['full_url'];
			}
			elseif(is_array($vertical_nav_logo) && isset($vertical_nav_logo['url']))
			{
				$vertical_nav_logo = $vertical_nav_logo['url'];
			}

			if(is_array($this->current_header_items) && (in_array('hidden-sidebar-btn', $this->current_header_items) || in_array('menu-btn', $this->current_header_items)))
			{ ?>
				<span class="milenia-sidebar-hidden-overlay"></span>
			<?php }

			if(is_array($this->current_header_items) && in_array('hidden-sidebar-btn', $this->current_header_items))
			{
				$hidden_sidebar = $this->configurator->getOption('milenia-header-hidden-sidebar-widget-area', 'widget-area-1', array(
					'overriden_by' => 'milenia-page-header-hidden-sidebar-widget-area',
					'depend_on' => array( 'key' => 'milenia-page-header-state', 'value' => '0' )
				));

				?>
				<!--================ Hidden Sidebar ================-->
				<aside id="milenia-sidebar-hidden" aria-hidden="true" class="milenia--nice-scrolled milenia-sidebar milenia-sidebar-hidden milenia-sidebar-hidden--v2">
					<button type="button" data-sidebar-hidden="#milenia-sidebar-hidden" aria-expanded="false" aria-controls="milenia-sidebar-hidden" aria-haspopup="true" class="milenia-sidebar-hidden-close">
						<span class="icon icon-cross"></span>
					</button>

					<?php if(is_active_sidebar($hidden_sidebar)) : ?>
						<!--================ Hidden Sidebar Content ================-->
						<div class="milenia-sidebar-hidden-content milenia-grid milenia-grid--cols-1">
							<?php dynamic_sidebar($hidden_sidebar); ?>
						</div>
						<!--================ End of Hidden Sidebar Content ================-->
					<?php endif; ?>
				</aside>
				<!--================ End of Hidden Sidebar ================-->
				<?php
			}

			if(is_array($this->current_header_items) && in_array('menu-btn', $this->current_header_items))
			{
				$facebook_profile = $this->configurator->getOption('milenia-social-links-facebook', '#');
				$google_plus_profile = $this->configurator->getOption('milenia-social-links-google-plus', '#');
				$twitter_profile = $this->configurator->getOption('milenia-social-links-twitter', '#');
				$tripadvisor_profile = $this->configurator->getOption('milenia-social-links-tripadvisor', '#');
				$instagram_profile = $this->configurator->getOption('milenia-social-links-instagram', '#');
				$youtube_profile = $this->configurator->getOption('milenia-social-links-youtube', '#');
				$flickr_profile = $this->configurator->getOption('milenia-social-links-flickr', '#');
				$booking_profile = $this->configurator->getOption('milenia-social-links-booking', '#');
				$airbnb_profile = $this->configurator->getOption('milenia-social-links-airbnb', '#');
				$whatsapp_profile = $this->configurator->getOption('milenia-social-links-whatsapp', '#');
				?>
				<!--================ Hidden Sidebar ================-->
				<aside id="milenia-sidebar-vertical-navigation" aria-hidden="true" class="milenia--nice-scrolled milenia-sidebar milenia-sidebar-hidden">
					<button type="button" data-sidebar-hidden="#milenia-sidebar-vertical-navigation" aria-expanded="false" aria-controls="milenia-sidebar-vertical-navigation" aria-haspopup="true" class="milenia-sidebar-hidden-close">
						<span class="icon icon-cross"></span>
					</button>

					<!--================ Hidden Sidebar Header ================-->
			        <header class="milenia-sidebar-hidden-header">
						<div class="milenia-sidebar-hidden-items">
							<?php if($vertical_nav_logo) : ?>
								<div>
									<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php bloginfo('name'); ?>" class="milenia-ln--independent">
										<img src="<?php echo esc_url($vertical_nav_logo); ?>" alt="<?php bloginfo('name'); ?>">
									</a>
								</div>
							<?php endif; ?>
						</div>
			        </header>
			        <!--================ End of Hidden Sidebar Header ================-->

					<!--================ Hidden Sidebar Content ================-->
					<div class="milenia-sidebar-hidden-content">
						<?php milenia_navigation('primary', array(
							'menu_class' => 'milenia-navigation-vertical',
							'container' => 'nav',
							'container_class' => 'milenia-navigation-container milenia--nice-scrolled'
						)); ?>
					</div>
					<!--================ End of Hidden Sidebar Content ================-->

					<!--================ Hidden Sidebar Footer ================-->
					<footer class="milenia-sidebar-hidden-footer">
						<div class="milenia-sidebar-hidden-items">
							<div>
								<?php milenia_navigation('hidden-sidebar-nav', array(
									'menu_class' => 'milenia-sidebar-hidden-actions milenia-list--hr milenia-list--unstyled',
									'container' => 'nav'
								)); ?>
							</div>

							<div>
								<ul class="milenia-social-icons milenia-social-icon--scheme-secondary milenia-list--unstyled">
									<?php if(!empty($facebook_profile)) : ?>
										<li><a href="<?php echo esc_url($facebook_profile); ?>"><i class="fab fa-facebook-f"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($google_plus_profile)) : ?>
										<li><a href="<?php echo esc_url($google_plus_profile); ?>"><i class="fab fa-google-plus-g"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($twitter_profile)) : ?>
										<li><a href="<?php echo esc_url($twitter_profile); ?>"><i class="fab fa-twitter"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($tripadvisor_profile)) : ?>
										<li><a href="<?php echo esc_url($tripadvisor_profile); ?>"><i class="fab fa-tripadvisor"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($instagram_profile)) : ?>
										<li><a href="<?php echo esc_url($instagram_profile); ?>"><i class="fab fa-instagram"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($youtube_profile)) : ?>
										<li><a href="<?php echo esc_url($youtube_profile); ?>"><i class="fab fa-youtube"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($flickr_profile)) : ?>
										<li><a href="<?php echo esc_url($flickr_profile); ?>"><i class="fab fa-flickr"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($booking_profile)) : ?>
										<li><a href="<?php echo esc_url($booking_profile); ?>"><i class="milenia-font-icon-1-icon-booking-icon"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($airbnb_profile)) : ?>
										<li><a href="<?php echo esc_url($airbnb_profile); ?>"><i class="fab fa-airbnb"></i></a></li>
									<?php endif; ?>
									<?php if(!empty($whatsapp_profile)) : ?>
										<li><a href="<?php echo esc_url($whatsapp_profile); ?>"><i class="fab fa-whatsapp"></i></a></li>
									<?php endif; ?>
								</ul>
							</div>
						</div>
					</footer>
					<!--================ End of Hidden Sidebar Footer ================-->
				</aside>
				<!--================ End of Hidden Sidebar ================-->
			<?php }
		}
	}
}
?>
