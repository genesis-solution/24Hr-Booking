<?php

namespace MPHB\Utils;

class DateUtils {

	/**
	 *
	 * @param \DateTime $date
	 * @return string Date in DB format.
	 */
	public static function formatDateDB( \DateTime $date ) {
		return $date->format( 'Y-m-d' );
	}

	/**
	 *
	 * @param \DateTime $date
	 * @since 3.9.7
	 */
	public static function formatDateTimeDB( \DateTime $date ) {
		return $date->format( 'Y-m-d H:i:s' );
	}

	/**
	 *
	 * @param \DateTime $date
	 * @return string Localized date in WP format.
	 */
	public static function formatDateWPFront( \DateTime $date ) {
		return date_i18n( MPHB()->settings()->dateTime()->getDateFormatWP(), $date->format( 'U' ) );
	}

	/**
	 *
	 * @param \DateTime $date
	 * @return string Localized time in WP format.
	 */
	public static function formatTimeWPFront( \DateTime $date ) {
		return date_i18n( get_option( 'time_format' ), $date->format( 'U' ) );
	}

	/**
	 *
	 * @param string $format See http://php.net/manual/ru/datetime.formats.php
	 * @param string $date
	 * @param bool   $needSetTime
	 * @return \DateTime|bool
	 */
	public static function createCheckInDate( $format, $date, $needSetTime = true ) {
		$dateObj = \DateTime::createFromFormat( $format, $date );
		if ( $dateObj && $needSetTime ) {
			$checkInTime = MPHB()->settings()->dateTime()->getCheckInTime( true );
			$dateObj->setTime( $checkInTime[0], $checkInTime[1], $checkInTime[2] );
		}

		return $dateObj ? $dateObj : false;
	}

	/**
	 *
	 * @param string $format See http://php.net/manual/ru/datetime.formats.php
	 * @param string $date
	 * @param bool   $needSetTime
	 * @return \DateTime|bool
	 */
	public static function createCheckOutDate( $format, $date, $needSetTime = true ) {
		$dateObj = \DateTime::createFromFormat( $format, $date );
		if ( $dateObj && $needSetTime ) {
			$checkOutTime = MPHB()->settings()->dateTime()->getCheckOutTime( true );
			$dateObj->setTime( $checkOutTime[0], $checkOutTime[1], $checkOutTime[2] );
		}
		return $dateObj ? $dateObj : false;
	}

	/**
	 * @param \DateTime $eventDate
	 * @return int
	 *
	 * @since 3.8.3
	 *
	 * @since 3.9.9 Fix the 'today' date by applying the WordPress site's timezone settings.
	 */
	public static function calcNightsSinceToday( $eventDate ) {
		$today = new \DateTime( 'today', self::getSiteTimeZone() );
		return self::calcNights( $today, $eventDate );
	}

	/**
	 *
	 * @note requires PHP 5 >= 5.3
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @return int
	 */
	public static function calcNights( \DateTime $checkInDate, \DateTime $checkOutDate ) {
		$from = clone $checkInDate;
		$to   = clone $checkOutDate;

		// set same time to dates
		$from->setTime( 0, 0, 0 );
		$to->setTime( 0, 0, 0 );

		$diff = $from->diff( $to );

		return (int) $diff->format( '%r%a' );
	}

	/**
	 *
	 * @return array
	 */
	public static function getDaysList() {
		return array(
			__( 'Sunday', 'motopress-hotel-booking' ),
			__( 'Monday', 'motopress-hotel-booking' ),
			__( 'Tuesday', 'motopress-hotel-booking' ),
			__( 'Wednesday', 'motopress-hotel-booking' ),
			__( 'Thursday', 'motopress-hotel-booking' ),
			__( 'Friday', 'motopress-hotel-booking' ),
			__( 'Saturday', 'motopress-hotel-booking' ),
		);
	}

	/**
	 *
	 * @param string $key
	 * @return string
	 */
	public static function getDayByKey( $key ) {
		$daysArr = self::getDaysList();
		return isset( $daysArr[ $key ] ) ? $daysArr[ $key ] : false;
	}

	/**
	 *
	 * @param int|string      $relation Optional.
	 * @param \DateTime|false $baseDate Optional.
	 * @return \DatePeriod
	 */
	public static function createQuarterPeriod( $relation = 0, $baseDate = false ) {
		$relation     = intval( $relation );
		$relationSign = $relation < 0 ? '-' : '+';

		$baseDate      = $baseDate ? $baseDate : new \DateTime();
		$baseDateArray = self::extractDayMonthYear( $baseDate );
		$baseMonth     = $baseDateArray[1];
		$baseYear      = $baseDateArray[2];

		if ( $baseMonth <= 3 ) {
			$baseQuarterFirstDate = new \DateTime( 'first day of January ' . $baseYear );
		} elseif ( $baseMonth <= 6 ) {
			$baseQuarterFirstDate = new \DateTime( 'first day of April ' . $baseYear );
		} elseif ( $baseMonth <= 9 ) {
			$baseQuarterFirstDate = new \DateTime( 'first day of July ' . $baseYear );
		} else {
			$baseQuarterFirstDate = new \DateTime( 'first day of October' . $baseYear );
		}

		$firstDate = clone $baseQuarterFirstDate;
		if ( $relation !== 0 ) {
			$firstDate->modify( $relationSign . ( absint( $relation ) * 3 ) . ' month' );
		}

		$lastDate = clone $firstDate;
		$lastDate->modify( '+2 month' )->modify( 'last day of this month' );

		return self::createDatePeriod( $firstDate, $lastDate, true );
	}

	/**
	 *
	 * @param \DateTime $date
	 *
	 * @since 3.9.7
	 *
	 * @return array [j, n, Y] - date day, month, year
	 */
	public static function extractDayMonthYear( \DateTime $date ) {
		$day   = date( 'j', $date->format( 'U' ) );
		$month = date( 'n', $date->format( 'U' ) );
		$year  = date( 'Y', $date->format( 'U' ) );

		return array( $day, $month, $year );
	}

	/**
	 *
	 * @param \DateTime $date Optional.
	 *
	 * @since 3.9.7
	 */
	public static function createDayPeriod( \DateTime $date ) {
		$dateFrom     = $date->setTime( 0, 0, 0 );
		$dateTo       = self::cloneModify( $date, '+1 day' )->setTime( 0, 0, 0 );
		$dateInterval = new \DateInterval( 'PT1H' );

		return new \DatePeriod( $dateFrom, $dateInterval, $dateTo );
	}

	/**
	 *
	 * @param \DateTime $date Optional.
	 * @param bool      $monday Optional. Start a week from Monday.
	 *
	 * @since 3.9.7
	 */
	public static function createWeekPeriod( \DateTime $date, $monday = true ) {
		if ( $monday ) {
			$dateFrom = self::cloneModify( $date, 'monday this week' );
		} else {
			$dateFrom = self::cloneModify( $date, 'sunday last week' );
		}
		$dateTo = self::cloneModify( $dateFrom, '+6 days' );

		return self::createDatePeriod( $dateFrom, $dateTo, true );
	}

	/**
	 *
	 * @param \DateTime $date Optional.
	 *
	 * @since 3.9.7
	 */
	public static function createMonthPeriod( \DateTime $date ) {
		$dateFrom = self::cloneModify( $date, 'first day of this month' );
		$dateTo   = self::cloneModify( $date, 'last day of this month' );

		return self::createDatePeriod( $dateFrom, $dateTo, true );
	}

	/**
	 *
	 * @param \DateTime $date Optional.
	 * @param bool      $after Optional.
	 *
	 * @since 3.9.7
	 */
	public static function createThirtyDaysPeriod( \DateTime $date, $after = false ) {
		if ( $after ) { // period after of before the date
			$dateFrom = $date;
			$dateTo   = self::cloneModify( $dateFrom, '+30 days' );
		} else {
			$dateFrom = self::cloneModify( $date, '-30 days' );
			$dateTo   = $date;
		}

		return self::createDatePeriod( $dateFrom, $dateTo, true );
	}

	public static function createYearPeriod( \DateTime $date ) {
		$dateFrom     = self::cloneModify( $date, 'first day of january this year' );
		$dateTo       = self::cloneModify( $date, 'first day of january next year' );
		$dateInterval = new \DateInterval( 'P1M' );

		return new \DatePeriod( $dateFrom, $dateInterval, $dateTo );
	}

	/**
	 * @warning PHP <5.3.3 has bug with iterating over DatePeriod twice https://bugs.php.net/bug.php?id=52668
	 *
	 * @param \DateTime|string $dateFrom date in format 'Y-m-d' or \DateTime object
	 * @param \DateTime|string $dateTo date in format 'Y-m-d' or \DateTime object
	 * @param bool             $includeEndDate Optional. Default false.
	 * @return \DatePeriod
	 */
	public static function createDatePeriod( $dateFrom, $dateTo, $includeEndDate = false ) {
		$dateFrom = ( $dateFrom instanceof \DateTime ) ? clone $dateFrom : \DateTime::createFromFormat( 'Y-m-d', $dateFrom );
		$dateTo   = ( $dateTo instanceof \DateTime ) ? clone $dateTo : \DateTime::createFromFormat( 'Y-m-d', $dateTo );

		$dateFrom->setTime( 0, 0, 0 );
		$dateTo->setTime( 0, 0, 0 );

		if ( $includeEndDate ) {
			$dateTo = $dateTo->modify( '+1 day' );
		}

		$interval = new \DateInterval( 'P1D' );
		return new \DatePeriod( $dateFrom, $interval, $dateTo );
	}

	/**
	 * @param \DateTime|string $dateFrom date in format 'Y-m-d' or \DateTime object
	 * @param \DateTime|string $dateTo date in format 'Y-m-d' or \DateTime object
	 *
	 * @return array Array of dates representing in front end date format
	 */
	public static function createDateRangeArray( $dateFrom, $dateTo, $includeEndDate = false ) {
		$dates     = array();
		$dateRange = self::createDatePeriod( $dateFrom, $dateTo, $includeEndDate );

		foreach ( $dateRange as $date ) {
			$dates[ $date->format( 'Y-m-d' ) ] = $date->format( MPHB()->settings()->dateTime()->getDateFormat() );
		}

		return $dates;
	}

	/**
	 *
	 * @param \DateTime $dateObj
	 * @param string    $modify
	 */
	public static function cloneModify( $dateObj, $modify ) {
		$cloneDate = clone $dateObj;
		$cloneDate->modify( $modify );
		return $cloneDate;
	}

	/**
	 *
	 * @param \DateTime $dateObj
	 * @param string    $format
	 * @param string    $modify
	 * @return string
	 */
	public static function formatModifiedDate( $dateObj, $format, $modify ) {
		$modifiedDate = self::cloneModify( $dateObj, $modify );
		return $modifiedDate->format( $format );
	}

	/**
	 *
	 * @param string $date
	 * @param string $inFormat. Optional. Y-m-d by default.
	 * @param string $outFormat. Optional. WP date format.
	 */
	public static function convertDateFormat( $date, $inFormat = 'Y-m-d', $outFormat = null ) {

		if ( is_null( $outFormat ) ) {
			$outFormat = MPHB()->settings()->dateTime()->getDateFormatWP();
		}

		$dateObj = \DateTime::createFromFormat( $inFormat, $date );
		return $dateObj ? $dateObj->format( $outFormat ) : '';
	}

	/**
	 *
	 * @param string $time Time in 24-hour format.
	 * @return array ["hours" => ..., "minutes" => ...].
	 */
	public static function parseTime( $time ) {
		$matched = preg_match( '/^(?<hours>[01][0-9]|2[0-3]):(?<minutes>[0-5][0-9])/', $time, $components );
		if ( $matched ) {
			return array(
				'hours'   => $components['hours'],
				'minutes' => $components['minutes'],
			);
		} else {
			return array(
				'hours'   => '00',
				'minutes' => '00',
			);
		}
	}

	/**
	 *
	 * @param string $time Time in 24-hour format.
	 * @return \DateTime
	 */
	public static function currentDateWithTime( $time ) {
		$time    = self::parseTime( $time );
		$hours   = (int) $time['hours'];
		$minutes = (int) $time['minutes'];

		$date = new \DateTime();
		$date->setTime( $hours, $minutes );

		return $date;
	}

	/**
	 *
	 * @param string $time Time in 24-hour format.
	 * @return int
	 */
	public static function nextTimestampWithTime( $time ) {
		$nextDate = self::currentDateWithTime( $time );
		$nextTime = (int) $nextDate->format( 'U' ) + 59; // Till HH:MM:59

		$currentTime = time();

		$secondsLeft = $nextTime - $currentTime;

		if ( $nextTime >= $currentTime ) {
			return $nextTime;
		} else {
			return $nextTime + DAY_IN_SECONDS;
		}
	}

	public static function dateFormatToRegex( $format ) {
		$regex = strtr(
			$format,
			array(
				'Y' => '\d{4}',
				'm' => '\d{2}',
				'd' => '\d{2}',
				'F' => '\w+',
				'j' => '\d{1,2}',
				'-' => '\-',
				'/' => '\/',
				'.' => '\.',
			)
		);

		return '/^' . $regex . '$/';
	}

	public static function isDate( $dateString ) {
		$dateFormat = MPHB()->settings()->dateTime()->getDateFormat();
		$dateRegex  = self::dateFormatToRegex( $dateFormat );

		return (bool) preg_match( $dateRegex, $dateString );
	}

	/**
	 *
	 * @since 3.9.9
	 *
	 * @return \DateTimeZone
	 */
	public static function getSiteTimeZone() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.3', '<' ) ) {
			$timezone_string = get_option( 'timezone_string' );

			if ( $timezone_string ) {
				return new \DateTimeZone( $timezone_string );
			}

			$offset  = (float) get_option( 'gmt_offset' );
			$hours   = (int) $offset;
			$minutes = ( $offset - $hours );

			$sign      = ( $offset < 0 ) ? '-' : '+';
			$abs_hour  = abs( $hours );
			$abs_mins  = abs( $minutes * 60 );
			$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

			return new \DateTimeZone( $tz_offset );
		} else {
			return wp_timezone();
		}
	}

	/**
	 *
	 * @param int|\WP_Post
	 *
	 * @since 3.9.9
	 *
	 * @return false|\DateTime
	 */
	public static function getPostDateTimeUTC( $post ) {
		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post ) {
			return false;
		}

		$postDate     = $post->post_date;
		$postDateTime = \DateTime::createFromFormat( 'Y-m-d H:i:s', $postDate );

		return $postDateTime;
	}

}
