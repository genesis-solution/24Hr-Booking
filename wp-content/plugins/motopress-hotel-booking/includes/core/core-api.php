<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Facade of the Hotel Booking core. Any code outside the core must
 * use it instead of inner objects and functions. All object caching
 * must be placed here!
 */
class CoreAPI {

	const OPTION_NAME_CACHE_DATA_PREFIX           = 'MPHB_cache_data_prefix';
	const WP_CACHE_GROUP                          = 'MPHB';
	const CACHED_DATA_NOT_FOUND                   = 'MPHB_CACHED_DATA_NOT_FOUND';
	const MAX_SIZE_OF_TRANSIENT_CACHED_DATA_ARRAY = 370;

	private $cacheDataPrefix = null;

	public function __construct() {

		add_action(
			'plugins_loaded',
			function() {
				$this->addClearObjectCacheHooks();
			}
		);
	}

	private function addClearObjectCacheHooks() {

		$hookNamesForClearAllCache = array(
			'mphb_booking_status_changed',
			'save_post_' . MPHB()->postTypes()->room()->getPostType(),
			'save_post_' . MPHB()->postTypes()->roomType()->getPostType(),
			'save_post_' . MPHB()->postTypes()->rate()->getPostType(),
			'save_post_' . MPHB()->postTypes()->season()->getPostType(),
			// TODO: much better take into account only edit confirmed bookings
			'save_post_' . MPHB()->postTypes()->booking()->getPostType(),
			'update_option_mphb_check_in_days',
			'update_option_mphb_check_out_days',
			'update_option_mphb_min_stay_length',
			'update_option_mphb_max_stay_length',
			'update_option_mphb_booking_rules_custom',
			'update_option_mphb_min_advance_reservation',
			'update_option_mphb_max_advance_reservation',
			'update_option_mphb_buffer_days',
			'update_option_mphb_do_not_apply_booking_rules_for_admin',
		);

		foreach ( $hookNamesForClearAllCache as $hookName ) {
			add_action(
				$hookName,
				function() {
					$this->cacheDataPrefix = time();
					// for optimization we can create several cache prefixes
					// and delete them by different lists of hooks
					update_option( static::OPTION_NAME_CACHE_DATA_PREFIX, $this->cacheDataPrefix, true );
				}
			);
		}
	}

	private function getPrefixedCacheDataId( string $cacheDataId ) {

		if ( ! $this->cacheDataPrefix ) {

			$this->cacheDataPrefix = get_option( static::OPTION_NAME_CACHE_DATA_PREFIX );

			if ( ! $this->cacheDataPrefix ) {

				$this->cacheDataPrefix = time();
				update_option( static::OPTION_NAME_CACHE_DATA_PREFIX, $this->cacheDataPrefix, true );
			}
		}

		return $this->cacheDataPrefix . '_' . $cacheDataId;
	}

	private function getCachedData( string $cacheDataId, string $cacheDataSubId = '', bool $isUseTransientCache = false ) {

		$result = null;

		if ( $isUseTransientCache ) {

			$result = get_transient( $this->getPrefixedCacheDataId( $cacheDataId ) );

			if ( false === $result ) {
				$result = static::CACHED_DATA_NOT_FOUND;
			}
		} else {

			$isCachedDataWasFound = true;

			$result = wp_cache_get(
				$this->getPrefixedCacheDataId( $cacheDataId ),
				static::WP_CACHE_GROUP,
				false,
				$isCachedDataWasFound
			);

			if ( ! $isCachedDataWasFound ) {
				$result = static::CACHED_DATA_NOT_FOUND;
			}
		}

		if ( ! empty( $cacheDataSubId ) && is_array( $result ) && static::CACHED_DATA_NOT_FOUND !== $result ) {

			if ( isset( $result[ $cacheDataSubId ] ) ) {

				$result = $result[ $cacheDataSubId ];

			} else {

				$result = static::CACHED_DATA_NOT_FOUND;
			}
		}

		return $result;
	}

	/**
	 * IMPORTANT: try to create minimum transient cache records to reduce database size and usage!
	 */
	private function setCachedData( string $cacheDataId, string $cacheDataSubId, $data, int $expirationInSeconds = 1800 /** 30 min */, bool $isUseTransientCache = false ) {

		$cachingData = $data;

		if ( ! empty( $cacheDataSubId ) ) {

			$alreadyCachedData = static::getCachedData( $cacheDataId, '', $isUseTransientCache );

			if ( static::CACHED_DATA_NOT_FOUND === $alreadyCachedData || ! is_array( $alreadyCachedData ) ) {

				$cachingData = array();

			} else {

				$cachingData = $alreadyCachedData;
			}

			$cachingData[ $cacheDataSubId ] = $data;

			if ( static::MAX_SIZE_OF_TRANSIENT_CACHED_DATA_ARRAY < count( $cachingData ) ) {
				return;
			}
		}

		if ( $isUseTransientCache ) {

			set_transient(
				$this->getPrefixedCacheDataId( $cacheDataId ),
				$cachingData,
				$expirationInSeconds
			);

		} else {
			wp_cache_set(
				$this->getPrefixedCacheDataId( $cacheDataId ),
				$cachingData,
				static::WP_CACHE_GROUP,
				$expirationInSeconds
			);
		}
	}

	/**
	 * @return int[] ids of all room types on main language
	 */
	public function getAllRoomTypeOriginalIds(): array {

		$cacheDataId = 'getAllRoomTypeOriginalIds';
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = MPHB()->getRoomTypePersistence()->getPosts(
				array(
					'mphb_language' => 'original',
				)
			);

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}

	/**
	 * @return Entities\RoomType or null if nothing is found
	 */
	public function getRoomTypeById( int $roomTypeId ) {
		// we already have entities cache by id in repository!
		return MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
	}

	/**
	 * @return array with [
	 *      'booked' => [ 'Y-m-d' => rooms count, ... ],
	 *      'check-ins' => [ 'Y-m-d' => rooms count, ... ],
	 *      'check-outs' => [ 'Y-m-d' => rooms count, ... ],
	 * ]
	 */
	public function getBookedDaysForRoomType( int $roomTypeOriginalId ) {

		$cacheDataId = 'getBookedDaysForRoomType' . $roomTypeOriginalId;
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = MPHB()->getRoomRepository()->getBookedDays( $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}

	public function getActiveRoomsCountForRoomType( int $roomTypeOriginalId ) {

		$cacheDataId = 'getActiveRoomsCountForRoomType' . $roomTypeOriginalId;
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = RoomAvailabilityHelper::getActiveRoomsCountForRoomType( $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}

	public function getBlockedRoomsCountsForRoomType( int $roomTypeOriginalId ) {

		$cacheDataId = 'getBlockedRoomsCountsForRoomType' . $roomTypeOriginalId;
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = MPHB()->getRulesChecker()->customRules()->getBlockedRoomsCounts( $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}

	/**
	 * @return \MPHB\Core\RoomTypeAvailabilityStatus constant
	 */
	public function getRoomTypeAvailabilityStatus( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		$cacheDataId = 'getRoomTypeAvailabilityStatus' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $date->format( 'Y-m-d' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = RoomAvailabilityHelper::getRoomTypeAvailabilityStatus( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}

	/**
	 * @param $considerCheckIn - if true then check-in date considered as booked if there is no any available room
	 * @param $considerCheckOut - if true then check-out date considered as booked if there is no any available room
	 * @return true if given date is booked (there is no any available room)
	 */
	public function isBookedDate( int $roomTypeOriginalId, \DateTime $requestedDate, $considerCheckIn = true, $considerCheckOut = false ) {

		$cacheDataId = 'isBookedDate' . $roomTypeOriginalId;
		$dataSubId   = $requestedDate->format( 'Y-m-d' ) . '_' . ( $considerCheckIn ? '1' : '0' ) . '_' . ( $considerCheckOut ? '1' : '0' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = RoomAvailabilityHelper::isBookedDate( $roomTypeOriginalId, $requestedDate, $considerCheckIn, $considerCheckOut );

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}


	public function isCheckInEarlierThanMinAdvanceDate( int $roomTypeOriginalId, \DateTime $checkInDate, bool $isIgnoreBookingRules ): bool {

		$cacheDataId = 'isCheckInEarlierThanMinAdvanceDate' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $checkInDate->format( 'Y-m-d' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = ! $isIgnoreBookingRules && ! MPHB()->getRulesChecker()->reservationRules()->
				verifyMinAdvanceReservationRule( $checkInDate, $checkInDate, $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}

	public function isCheckInLaterThanMaxAdvanceDate( int $roomTypeOriginalId, \DateTime $checkInDate, bool $isIgnoreBookingRules ): bool {

		$cacheDataId = 'isCheckInLaterThanMaxAdvanceDate' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $checkInDate->format( 'Y-m-d' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = ! $isIgnoreBookingRules && ! MPHB()->getRulesChecker()->reservationRules()->
				verifyMaxAdvanceReservationRule( $checkInDate, $checkInDate, $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}


	public function isStayInNotAllowed( int $roomTypeOriginalId, \DateTime $checkInDate, \DateTime $checkOutDate, bool $isIgnoreBookingRules ): bool {

		$cacheDataId = 'isStayInNotAllowed' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $checkInDate->format( 'Y-m-d' ) . '_' . $checkOutDate->format( 'Y-m-d' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = ! $isIgnoreBookingRules && ! MPHB()->getRulesChecker()->customRules()->
				verifyNotStayInRestriction( $checkInDate, $checkOutDate, $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}


	public function isCheckOutNotAllowed( int $roomTypeOriginalId, \DateTime $checkOutDate, bool $isIgnoreBookingRules ): bool {

		$cacheDataId = 'isCheckOutNotAllowed' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $checkOutDate->format( 'Y-m-d' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = ! $isIgnoreBookingRules && 
				(
					! MPHB()->getRulesChecker()->customRules()->verifyNotCheckOutRestriction( $checkOutDate, $checkOutDate, $roomTypeOriginalId ) ||
					! MPHB()->getRulesChecker()->reservationRules()->verifyCheckOutDaysReservationRule( $checkOutDate, $checkOutDate, $roomTypeOriginalId )
				);

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}


	public function getMinStayLengthReservationDaysCount( int $roomTypeOriginalId, \DateTime $checkInDate, bool $isIgnoreBookingRules ): int {

		$cacheDataId = 'getMinStayLengthReservationDaysCount' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $checkInDate->format( 'Y-m-d' );
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = 1; // nights count

			if ( ! $isIgnoreBookingRules ) {

				$minStayNightsFromRule = MPHB()->getRulesChecker()->reservationRules()->
					getMinStayLengthReservationDaysCount( $checkInDate, $roomTypeOriginalId );

				if ( ! empty( $minStayNightsFromRule ) ) {

					$result = $minStayNightsFromRule;
				}
			}

			$this->setCachedData( $cacheDataId, $dataSubId, $result );
		}

		return $result;
	}

	/**
	 * @return int|null
	 */
	public function getMaxStayLengthReservationDaysCount( int $roomTypeOriginalId, \DateTime $checkInDate, bool $isIgnoreBookingRules ) {

		$result = null; // nights count

		if ( ! $isIgnoreBookingRules ) {

			$maxStayNightsFromRule = MPHB()->getRulesChecker()->reservationRules()->
				getMaxStayLengthReservationDaysCount( $checkInDate, $roomTypeOriginalId );

			if ( ! empty( $maxStayNightsFromRule ) ) {

				$result = $maxStayNightsFromRule;
			}
		}

		return $result;
	}

	/**
	 * @return \MPHB\Core\RoomTypeAvailabilityData
	 */
	public function getRoomTypeAvailabilityData( int $roomTypeOriginalId, \DateTime $date, bool $isIgnoreBookingRules ) {

		$cacheDataId = 'getRoomTypeAvailabilityData' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$dataSubId   = $date->format( 'Y-m-d' );
		// TODO: return true for transient cache?
		$result      = $this->getCachedData( $cacheDataId, $dataSubId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = RoomAvailabilityHelper::getRoomTypeAvailabilityData( $roomTypeOriginalId, $date, $isIgnoreBookingRules );

			// do not cache data in 1 year because it is too infrequently needed by users
			if ( $date < ( new \DateTime() )->add( new \DateInterval( 'P1Y' ) ) ) {

				// TODO: return true for transient cache?
				$this->setCachedData( $cacheDataId, $dataSubId, $result, 1800 /** 30 min */ );
			}
		}

		return $result;
	}

	/**
	 * Returns first available date for check-in for room type or
	 * any of room types if $roomTypeOriginalId = 0
	 *
	 * @return \DateTime
	 */
	public function getFirstAvailableCheckInDate( int $roomTypeOriginalId, bool $isIgnoreBookingRules ) {

		$cacheDataId = 'getFirstAvailableCheckInDate' . $roomTypeOriginalId . ( $isIgnoreBookingRules ? '_1' : '_0' );
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = RoomAvailabilityHelper::getFirstAvailableCheckInDate( $roomTypeOriginalId, $isIgnoreBookingRules );

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}

	/**
	 * @return array with dates (string in format Y-m-d) which have rate
	 */
	public function getDatesRatesForRoomType( int $roomTypeOriginalId ) {

		$cacheDataId = 'getDatesRatesForRoomType' . $roomTypeOriginalId;
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$rates = $this->getRoomTypeActiveRates( $roomTypeOriginalId );

			$result = array();

			foreach ( $rates as $rate ) {

				$result = array_merge( $result, array_keys( $rate->getDatePrices() ) );
			}

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}

	/**
	 * @return array with \MPHB\Entities\Rate
	 */
	public function getRoomTypeActiveRates( int $roomTypeOriginalId ) {

		$cacheDataId = 'getRoomTypeActiveRates' . $roomTypeOriginalId;
		$result      = $this->getCachedData( $cacheDataId );

		if ( static::CACHED_DATA_NOT_FOUND === $result ) {

			$result = MPHB()->getRateRepository()->findAllActiveByRoomType( $roomTypeOriginalId );

			$this->setCachedData( $cacheDataId, '', $result );
		}

		return $result;
	}


	/**
	 * @return float room type minimal price for min days stay with taxes and fees
	 * @throws Exception if booking is not allowed for given date
	 */
	public function getMinRoomTypeBasePriceForDate( int $roomTypeOriginalId, \DateTime $startDate ) {

		return mphb_get_room_type_base_price( $roomTypeOriginalId, $startDate, $startDate );
	}

	/**
	 * @param array $atts with:
	 * 'decimal_separator' => string,
	 * 'thousand_separator' => string,
	 * 'decimals' => int, Number of decimals
	 * 'is_truncate_price' => bool, false by default
	 * 'currency_position' => string, Possible values: after, before, after_space, before_space
	 * 'currency_symbol' => string,
	 * 'literal_free' => bool, Use "Free" text instead of 0 price.
	 * 'trim_zeros' => bool, true by default
	 * 'period' => bool,
	 * 'period_title' => '',
	 * 'period_nights' => 1,
	 * 'as_html' => bool, true by default
	 */
	public function formatPrice( float $price, array $atts = array() ) {
		return mphb_format_price( $price, $atts );
	}
}
