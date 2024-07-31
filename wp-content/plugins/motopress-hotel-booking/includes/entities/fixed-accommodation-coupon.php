<?php


namespace MPHB\Entities;

class FixedAccommodationCoupon extends AbstractCoupon {

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

			// Prevent discount more then room price
			$discount = min( $roomPrice, $this->amount );

			// Prevent discount less then 0
			$discount = max( $discount, 0.0 );
		}

		return $discount;
	}
}
