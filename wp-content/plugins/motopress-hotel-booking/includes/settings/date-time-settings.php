<?php

namespace MPHB\Settings;

class DateTimeSettings {

	private $defaultDateFormat = 'd/m/Y';
	private $dateFormats       = array(
		'Y-m-d'  => 'yyyy-mm-dd',
		'd/m/Y'  => 'dd/mm/yyyy',
		'm/d/Y'  => 'mm/dd/yyyy',
		'd.m.Y'  => 'dd.mm.yyyy',
		'F j, Y' => 'MM d, yyyy',
	);

	/**
	 * Retrieve plugin's frontend date format. Uses for datepickers.
	 *
	 * @return string
	 */
	public function getDateFormat() {
		return get_option( 'mphb_datepicker_date_format', $this->defaultDateFormat );
	}

	/**
	 *
	 * @return array
	 */
	public function getDateFormatsList() {
		return $this->dateFormats;
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultDateFormat() {
		return $this->defaultDateFormat;
	}

	/**
	 * Retrieve Date Format in js-style
	 *
	 * @return string
	 */
	public function getDateFormatJS() {
		$phpFormat = $this->getDateFormat();
		return isset( $this->dateFormats[ $phpFormat ] ) ? $this->dateFormats[ $phpFormat ] : '';
	}

	/**
	 * Retrieve WP date format
	 *
	 * @return string
	 */
	public function getDateFormatWP() {
		return get_option( 'date_format' );
	}

	/**
	 * Retrieve WP date time format
	 *
	 * @param string $glue Glue string for concatenate date and time format
	 * @return string
	 */
	public function getDateTimeFormatWP( $glue = ' ' ) {
		return get_option( 'date_format' ) . $glue . get_option( 'time_format' );
	}

	/**
	 * Retrieve first day of the week.
	 *
	 * @return int
	 */
	public function getFirstDay() {
		$wpFirstDay = (int) get_option( 'start_of_week', 0 );
		return $wpFirstDay;
	}

	/**
	 *
	 * @return string|array time in format "H:i:s" or array
	 */
	public function getCheckInTime( $asArray = false ) {
		$separator = ':';
		$seconds   = '00';
		$timeHM    = get_option( 'mphb_check_in_time', '11:00' );
		$time      = explode( $separator, $timeHM );
		$time[]    = $seconds;
		return $asArray ? $time : implode( $separator, $time );
	}

	/**
	 * Retrieve check-in time in WordPress time format
	 *
	 * @return string
	 */
	public function getCheckInTimeWPFormatted() {
		$time    = $this->getCheckInTime();
		$timeObj = \DateTime::createFromFormat( 'H:i:s', $time );

		return \MPHB\Utils\DateUtils::formatTimeWPFront( $timeObj );
	}

	/**
	 *
	 * @return string time in format "H:i:s"
	 */
	public function getCheckOutTime( $asArray = false ) {
		$separator = ':';
		$seconds   = '00';
		$timeHM    = get_option( 'mphb_check_out_time', '10:00' );
		$time      = explode( $separator, $timeHM );
		$time[]    = $seconds;
		return $asArray ? $time : implode( $separator, $time );
	}

	/**
	 * Retrieve check-out time in WordPress time format
	 *
	 * @return string
	 */
	public function getCheckOutTimeWPFormatted() {
		$time    = $this->getCheckOutTime();
		$timeObj = \DateTime::createFromFormat( 'H:i:s', $time );

		return \MPHB\Utils\DateUtils::formatTimeWPFront( $timeObj );
	}

	/**
	 *
	 * @return string
	 */
	public function getDateTransferFormat() {
		return 'Y-m-d';
	}

	/**
	 *
	 * @return string
	 */
	public function getDateTransferFormatJS() {
		$phpFormat = $this->getDateTransferFormat();
		return isset( $this->dateFormats[ $phpFormat ] ) ? $this->dateFormats[ $phpFormat ] : '';
	}

}
