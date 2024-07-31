<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderFront extends RevSliderFrontGlobal {

	public $v6_slider			 = false;
	public $JSON_slider			 = '';

	public function __construct(){
		$this->add_actions();
	}

	/**
	 * Add all actions that the frontend needs here
	 **/
	public function add_scripts(){
		wp_enqueue_script('_tpt', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', '', RS_REVISION, ['strategy' => 'async']);
		wp_enqueue_script('sr7', RS_PLUGIN_URL_CLEAN . 'public/js/sr7.js', '', RS_REVISION, ['strategy' => 'async']);			
		wp_enqueue_style('sr7css', RS_PLUGIN_URL_CLEAN . 'public/css/sr7.css', '', RS_REVISION);
		
		//check if a v6 slider is on the page, if yes then load the migration JS
		//we fill $JSON_slider here, so that we can push migration.js in the header properly
		$global = $this->get_global_settings();
		if($this->get_val($global, array('getTec', 'core'), 'MIX') !== 'REST') $this->JSON_slider = $this->load_v7_slider();

		if($this->v6_slider) wp_enqueue_script('sr7migration', RS_PLUGIN_URL_CLEAN . 'public/js/migration.js', '', RS_REVISION, ['strategy' => 'async']);
		do_action('sr_front_add_scripts', $this);
	}

	/**
	 * Add custom HTML to the style tags
	 **/
	public function add_html_to_style_tags($html, $handle) {
		// Check if it's the stylesheet you want to modify
		if ($handle !== 'sr7pagecsslp') return $html;

		$html = str_replace("rel='stylesheet'", "rel='preload'", $html);		
		$html = str_replace('/>', 'as="style" fetchpriority="low" onload="this.rel=\'stylesheet\'" />', $html);
		$html = str_replace("rel='stylesheet'", "rel='preload'", $html);
		$html = str_replace('/>', 'as="style" fetchpriority="high" onload="this.rel=\'stylesheet\'" />', $html);

		return $html;
	}
	  
	public function add_actions(){
		add_action('wp_enqueue_scripts', array($this, 'add_scripts'));
		add_action('wp_head', array($this, 'js_add_header_scripts'), 99);
		add_action('wp_head', array($this, 'load_header_fonts'));
		add_filter('style_loader_tag', array($this, 'add_html_to_style_tags'), 10, 2);
		add_action('wp_footer', array($this, 'load_google_fonts'));
		add_action('wp_footer', array($this, 'add_deprecation_warnings'));
		add_action('wp_head', array('RevSliderFront', 'add_meta_generator'));
	}

	public function js_add_header_scripts(){
		global $SR_GLOBALS;
		if($SR_GLOBALS['header_js'] === true) return false;

		$global = $this->get_global_settings();

		$breakpoints = array();
		$breakpoints[] = intval($this->get_val($global, array('size', 'desktop'), '1240'));
		$breakpoints[] = intval($this->get_val($global, array('size', 'notebook'), '1024'));
		$breakpoints[] = intval($this->get_val($global, array('size', 'tablet'), '778'));
		$breakpoints[] = intval($this->get_val($global, array('size', 'mobile'), '480'));

		$libs = array(); 
		$css = array();
		if (file_exists(RS_PLUGIN_PATH . 'public/js/libs/three.js')) $libs[] = "'THREE'";
		if (file_exists(RS_PLUGIN_PATH . 'public/js/libs/webgl.js')) $libs[] = "'WEBGL'";
		if (file_exists(RS_PLUGIN_PATH . 'public/js/libs/tpgsap.js')) $libs[] = "'tpgsap'";
		if (file_exists(RS_PLUGIN_PATH . 'public/css/sr7.lp.css')) $css[] = "'csslp'";
		if (file_exists(RS_PLUGIN_PATH . 'public/css/sr7.btns.css')) $css[] = "'cssbtns'";
		if (file_exists(RS_PLUGIN_PATH . 'public/css/sr7.nav.css')) $css[] = "'cssnav'";
		if (file_exists(RS_PLUGIN_PATH . 'public/css/sr7.media.css')) $css[] = "'cssmedia'";
		

		$script = '<script>' . "\n";
		$script .= "	window._tpt			??= {};" . "\n";
		$script .= "	window.SR7			??= {};" . "\n";
		$script .= "	_tpt.R				??= {};" . "\n";
		$script .= "	_tpt.R.fonts		??= {};" . "\n";
		$script .= "	_tpt.R.fonts.customFonts??= {};" . "\n";
		$script .= "	SR7.F 				??= {};" . "\n";
		$script .= "	SR7.G				??= {};" . "\n";
		$script .= "	SR7.LIB				??= {};" . "\n";
		$script .= "	SR7.E				??= {};" . "\n";
		$script .= "	SR7.E.gAddons		??= {};" . "\n";
		$script .= "	SR7.E.php 			??= {};" . "\n";
		$script .= "	SR7.E.nonce			= '". wp_create_nonce('RevSlider_Front') ."';" . "\n";
		$script .= "	SR7.E.ajaxurl		= '". admin_url('admin-ajax.php') ."';" . "\n";
		$script .= "	SR7.E.resturl		= '". get_rest_url() ."';" . "\n";
		$script .= "	SR7.E.slug_path		= '". str_replace(array("\n", "\r"), '', RS_PLUGIN_SLUG_PATH) ."';" . "\n";
		$script .= "	SR7.E.slug			= '". str_replace(array("\n", "\r"), '', RS_PLUGIN_SLUG) ."';" . "\n";
		$script .= "	SR7.E.plugin_url	= '". str_replace(array("\n", "\r"), '', RS_PLUGIN_URL) ."';" . "\n";
		$script .= "	SR7.E.wp_plugin_url = '". str_replace(array("\n", "\r"), '', WP_PLUGIN_URL) . "/" ."';" . "\n";
		$script .= "	SR7.E.revision		= '". RS_REVISION ."';" . "\n";
		$script .= "	SR7.E.fontBaseUrl	= '". $this->modify_fonts_url('https://fonts.googleapis.com/css2?family=') ."';" . "\n";
		$script .= "	SR7.G.breakPoints 	= [".implode(',', $breakpoints)."];" . "\n";
		$script .= "	SR7.E.modules 		= ['module','page','slide','layer','draw','animate','srtools','canvas','defaults','migration','carousel','navigation','media','modifiers'];" . "\n";
		if(!empty($libs))	$script .= '	SR7.E.libs 			= [' . implode(',', $libs) . '];' . "\n";
		if(!empty($css))	$script .= '	SR7.E.css 			= [' . implode(',', $css) . '];' . "\n";
		$script .= "	SR7.E.resources		= {};" . "\n";

		$script = apply_filters('revslider_js_add_header_scripts_js', $script);

		if($this->get_val($global, array('getTec', 'core'), 'MIX') !== 'REST'){
			$script .= '	SR7.JSON			??= {};' ."\n";
			$script .= $this->JSON_slider; //is getting added before
		}

		// Add Page Handler Inline Script
		$script .= (file_get_contents(RS_PLUGIN_PATH . 'public/js/page.js'));

		$script .= '</script>' . "\n";
		echo apply_filters('revslider_js_add_header_scripts', $script);
		
		$SR_GLOBALS['header_js'] = true;
	}

	public function load_v7_slider(){
		global $SR_GLOBALS, $post;
		
		$id				= (isset($post->ID)) ? $post->ID : '';
		$all_shortcodes	= $this->get_shortcode_from_page($id);
		$script			= '';

		if(empty($all_shortcodes)) return $script;

		$table_temp = $SR_GLOBALS['use_table_version'];
		$SR_GLOBALS['use_table_version'] = ($SR_GLOBALS['front_version'] === 6) ? 6 : 7;

		if($SR_GLOBALS['serial'] > 0) $SR_GLOBALS['serial'] = 0;
		$global		= $this->get_global_settings();
		$mode		= $this->get_val($global, array('getTec', 'core'), 'MIX');
		$start_ver	= $SR_GLOBALS['use_table_version'];
		foreach($all_shortcodes ?? [] as $alias){
			$SR_GLOBALS['use_table_version'] = $start_ver;
			$slider	= new RevSliderSlider();
			if(!$slider->check_alias($alias)){
				//check in v6/v7 database depending on where we are currently
				$SR_GLOBALS['use_table_version'] = ($SR_GLOBALS['use_table_version'] === 6) ? 7 : 6;
				if(!$slider->check_alias($alias)) continue;
			}
			
			$slider->init_by_alias($alias, false);
			if($slider->inited === false) continue;
			
			$SR_GLOBALS['serial']++;
			$sid		= $slider->get_id();
			$slider_id	= $slider->get_param('id', '');
			$html_id	= (trim($slider_id) !== '') ? $slider_id : 'SR7_'.$sid.'_'.$SR_GLOBALS['serial'];
			$html_id	= $this->set_html_id_v7($html_id, true);
			$full		= ($slider->v7 === false || ($slider->v7 === true && ($slider->get_param('fixed', false) !== false || in_array($slider->get_param('type', ''), array('scene', 'hero', 'carousel'))))) ? true : false;
			$data		= $slider->get_full_slider_JSON(false, $full);
			$data		= apply_filters('sr_load_slider_json', $data, $this);

			//load dom data directly
			$script .= "	SR7.JSON['".$html_id."'] = ".json_encode($data).";"."\n";
			if($SR_GLOBALS['use_table_version'] === 6) $this->v6_slider = true;

			if($mode === 'MIX' && $SR_GLOBALS['serial'] >= 2) break;
		}
		$SR_GLOBALS['collections']['ids'] = array(); //reset html ids here, so that they are later on empty when the page is parsed
		$SR_GLOBALS['serial'] = 0; //reset to 0, hope for the best
		$SR_GLOBALS['use_table_version'] = $table_temp;

		return $script;
	}

	/**
	 * print in header
	 **/
	public function load_header_fonts(){
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">'."\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>'."\n";
	}

	/**
	 * Load Used Google Fonts
	 * add google fonts of all sliders found on the page
	 * @since: 6.0
	 */
	public function load_google_fonts(){
		$fonts	= $this->print_clean_font_import_v7();
		
		if(empty($fonts)) return;

		echo "\n".$fonts."\n";

		global $SR_GLOBALS;
		if(empty($SR_GLOBALS['fonts']['loaded'])) return;
	
		$domFonts = array();
		echo '<script>'."\n";
		foreach($SR_GLOBALS['fonts']['loaded'] as $handle => $values){
			$handle = preg_replace('/[^-0-9a-zA-Z+]/', '', str_replace(' ', '+', $handle));
			if(isset($values['url'])){
				echo "_tpt.R.fonts.customFonts['". $handle ."'] = ". json_encode($values) .";"."\n";
			}else{
				$domFonts[$handle] = array(
					'normal'	=> $this->get_val($values, array('variants', 'normal'), array()),
					'italic'	=> $this->get_val($values, array('variants', 'italic'), array())
				);
			}
		}
		if(!empty($domFonts)){
			echo "_tpt.R.fonts.domFonts = ". json_encode($domFonts) .";"."\n";
		}
		echo '</script>'."\n";
	}

	/**
	 * adds deprecation warnings of functions that will cease to exist in v7
	 */
	public function add_deprecation_warnings(){
		global $SR_GLOBALS;
		if(empty($SR_GLOBALS['deprecated'])) return;

		echo '<script>';
		echo 'SR7.E.php.warnings	= '.json_encode($SR_GLOBALS['deprecated']).';';
		echo '</script>'."\n";

	}

	
}
