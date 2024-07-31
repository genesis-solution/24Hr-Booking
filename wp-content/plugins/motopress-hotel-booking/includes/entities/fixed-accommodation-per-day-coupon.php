<?php


namespace MPHB\Entities;

class FixedAccommodationPerDayCoupon extends AbstractCoupon {
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

			$breakdown = $reservedRoom->getRoomPriceBreakdown( $checkInDate, $checkOutDate );

			foreach ( $breakdown as $price ) {
				$dateDiscount = max( min( $price, $this->amount ), 0 );
				$discount    += $dateDiscount;
			}
		}

		return $discount;
	}
}
