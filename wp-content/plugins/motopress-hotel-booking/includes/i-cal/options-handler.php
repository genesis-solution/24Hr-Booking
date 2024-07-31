<?php

namespace MPHB\iCal;

class OptionsHandler {

	/**
	 *
	 * @var string
	 */
	protected $prefix = '';

	public function __construct( $prefix ) {
		$this->prefix = $prefix;
	}

	public function getOptionName( $option ) {
		return $this->prefix . '_' . $option;
	}

	/**
	 *
	 * @param string $option
	 * @param mixed  $default Optional. FALSE by default.
	 * @return mixed
	 */
	public function getOption( $option, $default = false ) {
		$option = $this->getOptionName( $option );
		return get_option( $option, $default );
	}

	/**
	 *
	 * @global \WPDB $wpdb
	 * @param string $option
	 * @param mixed  $default Optional. FALSE by default.
	 * @return mixed
	 */
	public function getOptionNoCache( $option, $default = false ) {
		global $wpdb;

		$option = $this->getOptionName( $option );

		// The code partly from the function get_option()
		$oldErrorReportingLevel = $wpdb->suppress_errors();
		$query                  = $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option );
		$row                    = $wpdb->get_row( $query );
		$wpdb->suppress_errors( $oldErrorReportingLevel );

		if ( is_object( $row ) ) {
			return maybe_unserialize( $row->option_value );
		} else {
			return $default;
		}
	}

	/**
	 *
	 * @param string           $option
	 * @param mixed            $value
	 * @param string|bool|null $autoload "yes"/true/null (null also mean "yes")
	 * or "no"/false. <b>Warning: default value differs from update_option()
	 * function</b> - here it's "no" instead of null.
	 *
	 * @return bool
	 */
	public function updateOption( $option, $value, $autoload = 'no' ) {
		$option = $this->getOptionName( $option );
		return update_option( $option, $value, $autoload );
	}

	/**
	 *
	 * @param string $option
	 *
	 * @return bool True, if succeed. False, if failure.
	 */
	public function deleteOption( $option ) {
		$option = $this->getOptionName( $option );
		return delete_option( $option );
	}

}
