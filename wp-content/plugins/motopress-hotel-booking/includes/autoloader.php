<?php

namespace MPHB;

class Autoloader {

	const CLASSES_NAMESPACE_PREFIX = 'MPHB\\';

	/**
	 *
	 * @var int
	 */
	private $prefixLength;

	/**
	 *
	 * @var string
	 */
	private $basePath;

	private $customPathList = array();

	/**
	 * @param string $basePath Path to plugin directory
	 */
	public function __construct( $basePath ) {

		$this->prefixLength = strlen( static::CLASSES_NAMESPACE_PREFIX );
		$this->basePath     = $basePath;

		$this->setupCustomPathList();

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	private function setupCustomPathList() {

		$this->customPathList['Libraries\\WP_SessionManager\\Recursive_ArrayAccess'] = 'includes/libraries/wp-session-manager/class-recursive-arrayaccess.php';
		$this->customPathList['Libraries\\WP_SessionManager\\WP_Session']            = 'includes/libraries/wp-session-manager/class-wp-session.php';
		$this->customPathList['Libraries\\EDD_Plugin_Updater\\EDD_Plugin_Updater']   = 'includes/libraries/edd-plugin-updater/edd-plugin-updater.php';

		$this->customPathList['Core\\CoreAPI']                    = 'includes/core/core-api.php';
		$this->customPathList['Core\\RoomTypeAvailabilityStatus'] = 'includes/core/data/room-type-availability-status.php';
		$this->customPathList['Core\\AbstractDataTransferObject'] = 'includes/core/data/abstract-data-transfer-object.php';
		$this->customPathList['Core\\RoomTypeAvailabilityData']   = 'includes/core/data/room-type-availability-data.php';
		$this->customPathList['Core\\RoomAvailabilityHelper']     = 'includes/core/helpers/room-availability-helper.php';

		$this->customPathList['AjaxApi\\AbstractAjaxApiAction']       = 'includes/ajax-api/ajax-actions/abstract-ajax-api-action.php';
		$this->customPathList['AjaxApi\\GetRoomTypeCalendarData']     = 'includes/ajax-api/ajax-actions/get-room-type-calendar-data.php';
		$this->customPathList['AjaxApi\\GetRoomTypeAvailabilityData'] = 'includes/ajax-api/ajax-actions/get-room-type-availability-data.php';
		$this->customPathList['AjaxApi\\GetAdminCalendarBookingInfo'] = 'includes/ajax-api/ajax-actions/get-admin-calendar-booking-info.php';

		// iCalendar lib
		$this->customPathList['Libraries\\iCalendar\\ZCiCal']           = 'includes/libraries/ZContent-icalendar/zapcallib.php';
		$this->customPathList['Libraries\\iCalendar\\ZCiCalNode']       = 'includes/libraries/ZContent-icalendar/zapcallib.php';
		$this->customPathList['Libraries\\iCalendar\\ZCiCalDataNode']   = 'includes/libraries/ZContent-icalendar/zapcallib.php';
		$this->customPathList['Libraries\\iCalendar\\ZDateHelper']      = 'includes/libraries/ZContent-icalendar/zapcallib.php';
		$this->customPathList['Libraries\\iCalendar\\ZCRecurringDate']  = 'includes/libraries/ZContent-icalendar/zapcallib.php';
		$this->customPathList['Libraries\\iCalendar\\ZCTimeZoneHelper'] = 'includes/libraries/ZContent-icalendar/zapcallib.php';
	}

	/**
	 * @param string $class
	 */
	public function autoload( $class ) {

		$class = ltrim( $class, '\\' );

		// does the class use the namespace prefix?
		if ( strncmp( static::CLASSES_NAMESPACE_PREFIX, $class, $this->prefixLength ) !== 0 ) {
			// no, move to the next registered autoloader
			return false;
		}

		$relativeClass = substr( $class, $this->prefixLength );

		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $this->getRelativeClassFilePath( $relativeClass );

		// if the file exists, require it
		if ( file_exists( $file ) ) {

			require_once $file;
			return $file;
		}
		return false;
	}


	private function getRelativeClassFilePath( string $class ): string {

		$classFilePath = '';

		if ( array_key_exists( $class, $this->customPathList ) ) {

			$classFilePath = $this->basePath . $this->customPathList[ $class ];

		} else {

			//$path = $this->basePath . 'includes/' . $this->defaultConvert( $class );
			$classFilePath = str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

			$classFilePath = preg_replace( '/([a-z])([A-Z])/', '$1-$2', $classFilePath );
			$classFilePath = preg_replace( '/([A-Z])([A-Z][a-z])/', '$1-$2', $classFilePath );
			$classFilePath = strtolower( $classFilePath );

			$classFilePath = str_replace( '_', '-', $classFilePath );

			$classFilePath = $this->basePath . 'includes/' . $classFilePath;
		}

		return $classFilePath;
	}
}
