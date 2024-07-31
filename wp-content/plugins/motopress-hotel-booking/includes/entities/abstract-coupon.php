<?php

namespace MPHB\Entities;

abstract class AbstractCoupon {

	/**
	 * @var float
	 */
	protected $amount;

	/**
	 * @var string
	 */
	protected $code;

	/**
	 * @var int
	 */
	protected $usageLimit;

	/**
	 * @var \DateTime
	 */
	protected $checkOutDateBefore;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var \DateTime
	 */
	protected $checkInDateAfter;

	/**
	 * @var int
	 */
	protected $minDaysBeforeCheckIn;

	/**
	 * @var int
	 */
	protected $maxDaysBeforeCheckIn;

	/**
	 * @var int
	 */
	protected $maxNights;

	/**
	 * @var int
	 */
	protected $usageCount = 0;

	/**
	 * @var int
	 */
	protected $minNights;

	/**
	 * @var array
	 */
	protected $roomTypes = array();

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $status;

	/**
	 * @var \DateTime
	 */
	protected $expirationDate;


	function __construct( $atts ) {

		if ( isset( $atts['id'] ) ) {
			$this->id = $atts['id'];
		}

		$this->code        = $atts['code'] ?? '';
		$this->description = $atts['description'] ?? '';
		$this->amount      = $atts['amount'];
		$this->status      = $atts['status'] ?? 'publish';

		$this->expirationDate     = ! empty( $atts['expiration_date'] ) ? $atts['expiration_date'] : null;
		$this->roomTypes          = ! empty( $atts['room_types'] ) ? array_map( 'intval', $atts['room_types'] ) : array();
		$this->checkInDateAfter   = ! empty( $atts['check_in_date_after'] ) ? $atts['check_in_date_after'] : null;
		$this->checkOutDateBefore = ! empty( $atts['check_out_date_before'] ) ? $atts['check_out_date_before'] : null;

		$this->minDaysBeforeCheckIn = ! empty( $atts['min_days_before_check_in'] ) ? $atts['min_days_before_check_in'] : 0;
		$this->maxDaysBeforeCheckIn = ! empty( $atts['max_days_before_check_in'] ) ? $atts['max_days_before_check_in'] : 0;

		$this->minNights  = ! empty( $atts['min_nights'] ) ? $atts['min_nights'] : 1;
		$this->maxNights  = ! empty( $atts['max_nights'] ) ? $atts['max_nights'] : 0;
		$this->usageLimit = ! empty( $atts['usage_limit'] ) ? $atts['usage_limit'] : 0;
		$this->usageCount = ! empty( $atts['usage_count'] ) ? $atts['usage_count'] : 0;
	}

	/**
	 * @return int
	 */
	function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	function getStatus() {
		return $this->status;
	}

	/**
	 * @return string
	 */
	function getCode() {
		return $this->code;
	}

	/**
	 * @return string
	 */
	function getDescription() {
		return $this->description;
	}

	/**
	 * @return float
	 */
	function getAmount() {
		return $this->amount;
	}

	/**
	 * @return \DateTime|null
	 */
	function getExpirationDate() {
		return $this->expirationDate;
	}

	/**
	 * @return array
	 */
	function getRoomTypes() {
		return $this->roomTypes;
	}

	/**
	 * @return \DateTime|null
	 */
	function getCheckInDateAfter() {
		return $this->checkInDateAfter;
	}

	/**
	 * @return \DateTime|null
	 */
	function getCheckOutDateBefore() {
		return $this->checkOutDateBefore;
	}

	/**
	 * @return int
	 */
	function getMinDaysBeforeCheckIn() {
		return $this->minDaysBeforeCheckIn;
	}

	/**
	 * @return int
	 */
	function getMaxDaysBeforeCheckIn() {
		return $this->maxDaysBeforeCheckIn;
	}

	/**
	 * @return int
	 */
	function getMinNights() {
		return $this->minNights;
	}

	/**
	 * @return int
	 */
	function getMaxNights() {
		return $this->maxNights;
	}

	/**
	 * @return int
	 */
	function getUsageLimit() {
		return $this->usageLimit;
	}

	/**
	 * @param Booking $booking
	 * @param boolean $returnError
	 *
	 * @return boolean|\WP_Error
	 */
	public function validate( $booking, $returnError = false ) {

		if ( ! $this->isPublished() ) {
			return $returnError ? new \WP_Error( 'not_valid', __( 'Coupon is not valid.', 'motopress-hotel-booking' ) ) : false;
		}

		if ( $this->isExpired() ) {
			return $returnError ? new \WP_Error( 'expired', __( 'This coupon has expired.', 'motopress-hotel-booking' ) ) : false;
		}

		if ( ! $this->isValidForBookingContents( $booking ) ) {
			return $returnError ? new \WP_Error( 'not_applicable', __( 'Sorry, this coupon is not applicable to your booking contents.', 'motopress-hotel-booking' ) ) : false;
		}

		if ( $this->isExceedUsageLimit() ) {
			return $returnError ? new \WP_Error( 'not_applicable', __( 'Coupon usage limit has been reached.', 'motopress-hotel-booking' ) ) : false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isExpired() {
		return $this->expirationDate && $this->expirationDate->format( 'Y-m-d' ) <= current_time( 'Y-m-d' );
	}

	/**
	 * @return bool
	 */
	public function isPublished() {
		return $this->status === 'publish';
	}

	public function isApplicableForRoomType( $roomTypeId ) {
		return empty( $this->roomTypes ) || in_array( $roomTypeId, $this->roomTypes );
	}

	/**
	 * @param Booking $booking
	 * @return boolean
	 */
	public function isValidForBookingContents( $booking ) {

		if ( ! empty( $this->roomTypes ) ) {
			$roomTypeIds = array_map(
				function ( ReservedRoom $reservedRoom ) {
					return (int) $reservedRoom->getRoomTypeId();
				},
				$booking->getReservedRooms()
			);

			if ( ! array_intersect( $roomTypeIds, $this->roomTypes ) ) {
				return false;
			}
		}

		if ( ! is_null( $this->checkInDateAfter ) &&
			 $this->checkInDateAfter->format( 'Y-m-d' ) > $booking->getCheckInDate()->format( 'Y-m-d' ) ) {
			return false;
		}

		if ( ! is_null( $this->checkOutDateBefore ) &&
			 $this->checkOutDateBefore->format( 'Y-m-d' ) < $booking->getCheckOutDate()->format( 'Y-m-d' ) ) {
			return false;
		}

		if ( 0 < $this->minDaysBeforeCheckIn || 0 < $this->maxDaysBeforeCheckIn ) {

			$bookingReservationDateTime = null == $booking->getDateTime() ? new \DateTime() : $booking->getDateTime();

			$daysBetweenReservationAndCheckIn = $bookingReservationDateTime->diff( $booking->getCheckInDate() )->format( '%a' );

			if ( 0 < $this->minDaysBeforeCheckIn && $daysBetweenReservationAndCheckIn < $this->minDaysBeforeCheckIn ) {
				return false;
			}

			if ( 0 < $this->maxDaysBeforeCheckIn && $daysBetweenReservationAndCheckIn > $this->maxDaysBeforeCheckIn ) {
				return false;
			}
		}

		$bookingNights = \MPHB\Utils\DateUtils::calcNights( $booking->getCheckInDate(), $booking->getCheckOutDate() );

		if ( $this->minNights > 0 && $bookingNights < $this->minNights ) {
			return false;
		}

		if ( $this->maxNights > 0 && $bookingNights > $this->maxNights ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isExceedUsageLimit() {
		return $this->usageLimit > 0 && $this->usageCount >= $this->usageLimit;
	}

	/**
	 * @return int
	 */
	public function getUsageCount() {
		return $this->usageCount;
	}

	public function increaseUsageCount() {
		$this->usageCount ++;
	}

	/**
	 * @param ReservedRoom $reservedRoom
	 * @param \DateTime    $checkInDate
	 * @param \DateTime    $checkOutDate
	 *
	 * @return float
	 */
	abstract public function calcRoomDiscount( $reservedRoom, $checkInDate, $checkOutDate );
}
