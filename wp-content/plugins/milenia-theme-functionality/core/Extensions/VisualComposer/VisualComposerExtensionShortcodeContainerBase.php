<?php
/**
* The VisualComposerExtensionShortcodeContainerBase class.
*
* Describes base functionality of the visual composer custom shortcodes.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

namespace Milenia\Core\Extensions\VisualComposer;

use Milenia\Core\App;

class VisualComposerExtensionShortcodeContainerBase
{
	/**
	 * Contains all cached values of the shortcodes.
	 *
	 * @static
	 * @access protected
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * Contains the shortcode attributes.
	 *
	 * @access protected
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Returns path to the vc templates directiory.
	 *
	 * @static
	 * @access protected
	 * @return string
	 */
    protected static function getTemplateDirPath()
	{
		return sprintf('%s%s', MILENIA_FUNCTIONALITY_ROOT, App::get('config')['visual-composer']['templates_path']);
	}


	/**
	 * Returns css code based on specified parameters.
	 *
	 * @param string $selector
	 * @param array $properties
	 * @param string|null $media
	 * @access protected
	 * @return string
	 */
	protected function makeCSS($selector, array $properties, $media = null)
	{
		$selector = $selector . '{%s}';
		$properties_prepared = array();

		foreach($properties as $property => $value)
		{
			$properties_prepared[] = sprintf('%s:%s;', $property, $value);
		}

		if(!empty($media))
		{
			$selector = $media . '{' .$selector. '}';
		}

		return sprintf($selector, implode('', $properties_prepared));
	}

    /**
     * Loads the shortcode template and caches it.
     *
     * @param string $filename
     * @param bool $cache
     * @access protected
     * @static
     * @return string
     */
	protected static function loadShortcodeTemplate($filename, $cache = true)
    {
		if(!isset(self::$cache['templates'])) self::$cache['templates'] = array();

        if( !$cache || !isset(self::$cache['templates'][$filename]) ) {
            global $wp_filesystem;
            $path = sprintf('%s/%s', self::getTemplateDirPath(), $filename);

            if(empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }

            $file_content = $wp_filesystem->get_contents($path);

            if( !$file_content && is_readable($path) ) {
                // trying to load directly if $wp_filesystem has not loaded template
                $file_contents_func = strrev('stnetnoc_teg_elif');
                $file_content = $file_contents_func($path);
            }

			self::$cache['templates'][$filename] = $file_content;
        }

		return self::$cache['templates'][$filename];
    }

    /**
     * Returns the prepared template with inserted attributes.
     *
     * @param string $template
     * @param array $data
     * @access protected
     * @return string
     */
    protected function prepareShortcodeTemplate($template, array $data = array())
    {
        return str_replace(array_keys($data), array_values($data), $template);
    }

	/**
	 * Returns sanitized html classes.
	 *
	 * @param array $classes
	 * @access protected
	 * @return string
	 */
	protected function sanitizeHtmlClasses(array $classes = array())
	{
		return implode(' ', array_unique(array_map('sanitize_html_class', $classes)));
	}

	/**
	 * Returns the value in case when the value is in white list. Otherwise - returns default value.
	 *
	 * @param mixed $value
	 * @param array $white_list
	 * @param mixed $default_value
	 * @access protected
	 * @return mixed
	 */
	protected function throughWhiteList($value, array $white_list = array(), $default_value)
	{
		return in_array($value, $white_list) ? $value : $default_value;
	}

	/**
	 * Returns an unique id for the shortcode.
	 *
	 * @param string $string_id
	 * @access protected
	 * @static
	 * @return string
	 */
	protected static function getShortcodeUniqueId($string_id)
	{
		if(!isset(self::$cache['unique_ids'])) self::$cache['unique_ids'] = array();

		$unique_id = $string_id . '-' . md5( $string_id . rand() ) . '-' . time() . rand();

		if(in_array($unique_id, self::$cache['unique_ids'])) return self::getShortcodeUniqueId( $string_id );

		array_push(self::$cache['unique_ids'], $unique_id);

		return $unique_id;
	}
}

?>
