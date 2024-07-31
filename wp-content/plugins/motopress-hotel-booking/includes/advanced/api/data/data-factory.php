<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\AccommodationAttribute;
use MPHB\Entities\Booking;
use MPHB\Entities\Customer;
use MPHB\Entities\Payment;
use MPHB\Entities\Room;
use MPHB\Entities\Rate;
use MPHB\Entities\RoomType;
use MPHB\Entities\Season;
use MPHB\Entities\Service;
use MPHB\Entities\PercentCoupon;

class DataFactory {

	/**
	 * @param  string $rest_base
	 *
	 * @return AbstractData
	 */
	public static function create( $rest_base ) {

		switch ( $rest_base ) {
			case 'accommodations':
				return new AccommodationData( new Room( array() ) );
			case 'accommodation_types':
				$accommodationTypes = self::createEmptyAccommodationType();
				return new AccommodationTypeData( $accommodationTypes );
			case 'accommodation_types/attributes':
				$attributes = self::createEmptyAccommodationAttribute();
				return new AccommodationTypesAttributeData( $attributes );
			case 'accommodation_types/services':
				$services = self::createEmptyService();
				return new ServiceData( $services );
			case 'bookings':
				$booking = self::createEmptyBooking();
				return new BookingData( $booking );
			case 'coupons':
				$coupon = self::createEmptyCoupon();
				return new CouponData( $coupon );
			case 'payments':
				$payment = self::createEmptyPayment();
				return new PaymentData( $payment );
			case 'rates':
				$rate = self::createEmptyRate();
				return new RateData( $rate );
			case 'seasons':
				$season = self::createEmptySeason();
				return new SeasonData( $season );
			default:
				throw new \Exception( 'Not found relevant class for data of endpoint: ' . $rest_base );
		}
	}

	private static function createEmptyService() {
		$atts = array(
			'id'            => null,
			'original_id'   => null,
			'title'         => null,
			'description'   => null,
			'periodicity'   => null,
			'min_quantity'  => null,
			'max_quantity'  => null,
			'is_auto_limit' => null,
			'repeat'        => null,
			'price'         => null,
		);

		return Service::create( $atts );
	}

	private static function createEmptySeason() {
		$atts = array(
			'id'          => null,
			'title'       => null,
			'description' => null,
			'start_date'  => null,
			'end_date'    => null,
			'days'        => array(),
		);

		return new Season( $atts );
	}

	private static function createEmptyRate() {
		$atts = array(
			'id'            => null,
			'title'         => null,
			'description'   => null,
			'room_type_id'  => null,
			'season_prices' => array(),
			'active'        => null,
		);

		return new Rate( $atts );
	}

	private static function createEmptyBooking() {
		$atts = array(
			'id'               => null,
			'check_in_date'    => null,
			'check_out_date'   => null,
			'reserved_rooms'   => null,
			'customer'         => new Customer(),
			'total_price'      => null,
			'note'             => null,
			'status'           => null,
			'coupon_id'        => null,
			'ical_prodid'      => null,
			'ical_summary'     => null,
			'ical_description' => null,
			'language'         => null,
			'checkout_id'      => null,
			'internal_notes'   => array(),
		);

		return new Booking( $atts );
	}

	private static function createEmptyPayment() {
		$atts = array(
			'id'            => null,
			'date'          => null,
			'modifiedDate'  => null,
			'status'        => null,
			'gatewayId'     => null,
			'gatewayMode'   => null,
			'transactionId' => null,
			'amount'        => null,
			'currency'      => null,
			'bookingId'     => null,
			'email'         => null,
		);

		return new Payment( $atts );
	}

	private static function createEmptyAccommodationType() {
		$atts = array(
			'id'             => null,
			'original_id'    => null,
			'title'          => null,
			'description'    => null,
			'excerpt'        => null,
			'adults'         => null,
			'children'       => null,
			'total_capacity' => null,
			'bed_type'       => null,
			'size'           => null,
			'view'           => null,
			'services_ids'   => array(),
			'categories'     => array(),
			'tags'           => array(),
			'facilities'     => array(),
			'attributes'     => array(),
			'image_id'       => null,
			'gallery_ids'    => null,
			'status'         => null,
		);

		return new RoomType( $atts );
	}

	private static function createEmptyAccommodationAttribute() {
		$atts = array(
			'id'                 => null,
			'status'             => null,
			'title'              => null,
			'enable_archives'    => null,
			'visible_in_details' => null,
			'default_sort_order' => null,
			'default_text'       => null,
		);

		return new AccommodationAttribute( $atts );
	}

	private static function createEmptyCoupon() {
		$atts = array(
			'id'                 => null,
			'code'               => null,
			'description'        => null,
			'amount'             => null,
			'status'             => null,
			'expirationDate'     => null,
			'roomTypes'          => null,
			'checkInDateAfter'   => null,
			'checkOutDateBefore' => null,
			'minNights'          => null,
			'maxNights'          => null,
			'usageLimit'         => null,
			'usageCount'         => null,
		);

		return new PercentCoupon( $atts );
	}
}
