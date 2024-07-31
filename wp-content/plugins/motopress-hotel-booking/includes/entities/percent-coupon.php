<?php

namespace MPHB\Entities;

/**
 * Class PercentCoupon
 * Percentage room-related discount coupon
 *
 * @package MPHB\Entities
 */
class PercentCoupon extends AbstractCoupon {

	/**
	 * @return float
	 */
	public function getAmount() {
		// Prevent more than 100% discount
		return min( parent::getAmount(), 100.00 );
	}

	/**
	 * @param ReservedRoom $reservedRoom
	 * @param \DateTime    $checkInDate
	 * @param \DateTime    $checkOutDate
	 *
	 * @return float
	 */
	public function calcRoomDiscount( $reservedRoom, $checkInDate, $checkOutDate ) {
		$discount = 0.0;

		if ( $this->isApplicableForRoomType( $reservedRoom->getRoomTypeId() ) ) {
			$roomPrice = $reservedRoom->calcRoomPrice( $checkInDate, $checkOutDate );
			$discount  = round( $roomPrice * $this->amount / 100, 4 );

			// Prevent discount more then room price
			$discount = min( $discount, $roomPrice );

			// Prevent discount less then 0
			$discount = max( $discount, 0.0 );
		}

		return $discount;
	}
}
