<?php
/**
* The main theme interface that describes functionality of configurable entity.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

interface ConfigurableInterface {

	/**
    * Returns an option value.
    *
    * @param string $name - the option name
	* @param mixed $fallback - fallback value
    * @param array $data - additional data for getting the option
    * @access public
    * @return mixed
    */
    public function getOption($name, $fallback = '', array $data = array());

	/**
	* Sets an option value programmatically.
	*
	* @param string $name - the option name
	* @param mixed $value - value of the option
	* @access public
	* @return void
	*/
    public function setOption($name, $value);
}

?>
