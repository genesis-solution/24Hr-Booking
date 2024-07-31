<?php
/**
* The MileniaHelper class
*
* This class is responsible to provide all public additional functionality.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

class MileniaHelper
{
    /**
	 * The one, true instance of the Milenia object.
	 *
	 * @static
	 * @access protected
	 * @var null|object
	 */
    protected static $instance;

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
	 * Returns sanitized html classes.
	 *
	 * @param array $classes
	 * @access public
     * @return string
	 */
    public function getSanitizedHtmlClasses($classes = array()) {
        return implode(' ', array_unique(array_map('sanitize_html_class', $classes)));
    }

	/**
	 * Returns plain css string from the array of an element style.
	 *
	 * @param array $styles
	 * @access public
	 * @return string
	 */
	public function getCSSSetFromArray(array $styles = array())
	{
		$result = array();

		foreach($styles as $property => $value) {
		    if(!is_string($value)) continue;
		    array_push($result, sprintf('%s:%s;', $property, $value));
		}

		return implode('', $result);
	}

	/**
	 * Returns a plain CSS rule.
	 *
	 * @param string|array $selector
	 * @param array $properties
	 * @param array $media
	 * @access public
	 * @return string
	 */
	public function getPlainCSS($selector, array $properties = array(), array $media = array())
	{
		$plain = '';

		if(!count($properties)) return;

		if(is_array($selector)) $selector = implode(',', $selector);

		if(count($media) > 1) $plain .= sprintf('@media (%s: %s) {', $media[0], $media[1]);

			$plain .= sprintf('%s {%s}', $selector, $this->getCSSSetFromArray($properties));

		if(count($media) > 1) $plain .= '}';

		return $plain;
	}
}
?>
