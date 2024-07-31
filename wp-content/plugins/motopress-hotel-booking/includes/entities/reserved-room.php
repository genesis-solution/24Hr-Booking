<?php

namespace MPHB\Entities;

class ReservedRoom {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 * @var int
	 */
	private $roomId;

	/**
	 *
	 * @var int
	 */
	private $bookingId;

	/**
	 *
	 * @var int
	 */
	private $rateId;

	/**
	 *
	 * @var int
	 */
	private $adults;

	/**
	 *
	 * @var int
	 */
	private $children;

	/**
	 *
	 * @var ReservedService[]
	 */
	private $reservedServices;

	/**
	 *
	 * @var string
	 */
	private $guestName;

	/**
	 *
	 * @var string
	 */
	private $status;

	/**
	 *
	 * @var string
	 */
	private $uid;

	/**
	 * @var array
	 */
	private $cachedRoomPriceBreakdown = null;
	/**
	 *
	 * @param array              $atts Array of atts
	 * @param int                $atts['id'] Id of room reservation record
	 * @param int                $atts['room_id'] Id of room
	 * @param int                $atts['booking_id'] Id of booking
	 * @param int                $atts['rate_id'] Id of reserved rate
	 * @param int                $atts['adults'] Adults count
	 * @param int                $atts['children'] Children count
	 * @param \ReservedService[] $atts['reserved_services'] Array of Reserved Services
	 * @param string             $atts['guest_name'] Full name of guest
	 * @param string             $atts['status'] Status. Optional. Publish by default.
	 */
	public function __construct( $atts ) {
		if ( isset( $atts['id'] ) ) {
			$this->id = $atts['id'];
		}

		if ( isset( $atts['room_id'] ) ) {
			$this->roomId = (int) $atts['room_id'];
		}

		// Rate ID can be 0. See also \MPHB\Entities\Booking::isImported()
		$this->rateId = (int) $atts['rate_id'];

		$this->adults   = (int) $atts['adults'];
		$this->children = (int) $atts['children'];

		$this->reservedServices = isset( $atts['reserved_services'] ) ? $atts['reserved_services'] : array();

		$this->guestName = isset( $atts['guest_name'] ) ? $atts['guest_name'] : '';

		$this->bookingId = isset( $atts['booking_id'] ) ? (int) $atts['booking_id'] : 0;

		$this->status = isset( $atts['status'] ) ? $atts['status'] : 'publish';

		if ( array_key_exists( 'uid', $atts ) ) { // isset() will return false for null
			$this->uid = $atts['uid'];
		} else {
			$this->uid = mphb_generate_uid();
		}
	}

	/**
	 *
	 * @param array              $atts Array of atts
	 * @param int                $atts['id'] Id of room reservation record
	 * @param int                $atts['room_id'] Id of room
	 * @param int                $atts['booking_id'] Id of booking
	 * @param int                $atts['rate_id'] Id of reserved rate
	 * @param int                $atts['adults'] Adults count
	 * @param int                $atts['children'] Children count
	 * @param \ReservedService[] $atts['reserved_services'] Array of Reserved Services
	 * @param string             $atts['guest_name'] Full name of guest
	 * @return ReservedRoom
	 */
	public static function create( $atts ) {
		return new self( $atts );
	}

	/**
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return int
	 */
	public function getRoomId() {
		return $this->roomId;
	}

	/**
	 *
	 * @return int
	 */
	public function getRateId() {
		return $this->rateId;
	}

	/**
	 *
	 * @return int
	 */
	public function getBookingId() {
		return $this->bookingId;
	}

	/**
	 * @return Booking|null
	 */
	public function getBooking() {
		// we do not need to cache it because repository does this
		return MPHB()->getBookingRepository()->findById( $this->bookingId );
	}

	/**
	 * Retrieve room type id of reserved room
	 *
	 * @return int
	 */
	public function getRoomTypeId() {
		$roomTypeId = 0;
		if ( isset( $this->roomId ) ) {
			$room = MPHB()->getRoomRepository()->findById( $this->roomId );
			if ( $room ) {
				$roomTypeId = $room->getRoomTypeId();
			}
		}
		if ( ! $roomTypeId && isset( $this->rateId ) ) {
			$rate = MPHB()->getRateRepository()->findById( $this->rateId );
			if ( $rate ) {
				$roomTypeId = $rate->getRoomTypeId();
			}
		}
		return $roomTypeId;
	}

	/**
	 * @return RoomType|null
	 */
	public function getRoomType() {
		// we do not need to cache it because repository does this
		return MPHB()->getRoomTypeRepository()->findById( $this->getRoomTypeId() );
	}

	/**
	 *
	 * @return int
	 */
	public function getAdults() {
		return $this->adults;
	}

	/**
	 *
	 * @return int
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 *
	 * @return ReservedService[]
	 */
	public function getReservedServices() {
		return $this->reservedServices;
	}

	/**
	 *
	 * @return string
	 */
	public function getGuestName() {
		return $this->guestName;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 *
	 * @return string
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @return float
	 */
	public function calcRoomPrice( $checkInDate, $checkOutDate ) {

		$price = 0;

		if ( ! empty( $this->rateId ) ) {
			MPHB()->reservationRequest()->setupParameters(
				array(
					'adults'         => $this->getAdults(),
					'children'       => $this->getChildren(),
					'check_in_date'  => $checkInDate,
					'check_out_date' => $checkOutDate,
				)
			);

			$rate = MPHB()->getRateRepository()->findById( $this->rateId );

			// Rate still exists? (Example: old booking and removed rate)
			if ( ! is_null( $rate ) ) {
				$price = $rate->calcPrice( $checkInDate, $checkOutDate );
			}
		}

		return $price;
	}

	/**
	 * @return array|null with stored price breakdown data from booking
	 */
	public function getLastRoomPriceBreakdown() {

		if ( empty( $this->cachedRoomPriceBreakdown ) ) {

			$booking                   = $this->getBooking();
			$bookingLastPriceBreakdown = null !== $booking ? $booking->getLastPriceBreakdown() : null;

			if ( null !== $booking && null !== $bookingLastPriceBreakdown &&
				! empty( $bookingLastPriceBreakdown['rooms'] ) &&
				count( $bookingLastPriceBreakdown['rooms'] ) === count( $booking->getReservedRoomIds() ) ) {

				$reservedRoomIndexInBooking = 0;

				foreach ( $booking->getReservedRooms() as $reservedRoom ) {

					if ( $reservedRoom->getId() === $this->getId() ) {
						break;
					}

					$reservedRoomIndexInBooking++;
				}

				$this->cachedRoomPriceBreakdown = $bookingLastPriceBreakdown['rooms'][ $reservedRoomIndexInBooking ];
			}
		}

		return $this->cachedRoomPriceBreakdown;
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 *
	 * @return array [%yyyy-mm-dd% => %price%]
	 */
	public function getRoomPriceBreakdown( $checkInDate, $checkOutDate ) {

		$breakdown = array();

		if ( ! empty( $this->rateId ) ) {
			MPHB()->reservationRequest()->setupParameters(
				array(
					'adults'         => $this->getAdults(),
					'children'       => $this->getChildren(),
					'check_in_date'  => $checkInDate,
					'check_out_date' => $checkOutDate,
				)
			);

			$rate      = MPHB()->getRateRepository()->findById( $this->rateId );
			$breakdown = $rate->getPriceBreakdown( $checkInDate, $checkOutDate );
		}

		return $breakdown;
	}

	/**
	 * @param \DateTime           $checkInDate
	 * @param \DateTime           $checkOutDate
	 * @param AbstractCoupon|null $coupon
	 * @param string|null         $language
	 *
	 * @return array
	 */
	private function getRoomBreakdown( $checkInDate, $checkOutDate, $coupon, $language ) {
		MPHB()->reservationRequest()->setupParameters(
			array(
				'adults'         => $this->getAdults(),
				'children'       => $this->getChildren(),
				'check_in_date'  => $checkInDate,
				'check_out_date' => $checkOutDate,
			)
		);
		$rateId         = apply_filters( '_mphb_translate_post_id', $this->rateId, $language );
		$rate           = MPHB()->getRateRepository()->findById( $rateId );
		$rateTitle      = $rate ? $rate->getTitle() : '';
		$priceBreakdown = $rate ? $rate->getPriceBreakdown( $checkInDate, $checkOutDate ) : array();
		$price          = $rate ? $rate->calcPrice( $checkInDate, $checkOutDate ) : 0;

		$discount = 0.0;
		if ( $coupon ) {
			$discount = $coupon->calcRoomDiscount( $this, $checkInDate, $checkOutDate );
		}
		// Prevent total less 0
		$discountTotal = max( $price - $discount, 0 );

		$roomTypeId    = apply_filters( '_mphb_translate_post_id', $this->getRoomTypeId() );
		$roomType      = $roomTypeId ? MPHB()->getRoomTypeRepository()->findById( $roomTypeId ) : null;
		$roomBreakdown = array(
			'type'              => $roomType ? $roomType->getTitle() : '',
			'rate'              => $rateTitle,
			'list'              => $priceBreakdown,
			'total'             => $price,                     // "Dates Subtotal"
			'discount'          => $discount, // "Discount"
			'discount_total'    => $discountTotal,             // "Accommodation Subtotal"
			'adults'            => $this->getAdults(),
			'children'          => $this->getChildren(),
			'children_capacity' => $roomType ? $roomType->getChildrenCapacity() : $this->getChildren(),
		);

		return $roomBreakdown;
	}

	/**
	 * @param \DateTime           $checkInDate
	 * @param \DateTime           $checkOutDate
	 * @param AbstractCoupon|null $coupon
	 * @param string|null         $language
	 *
	 * @return array
	 */
	public function getPriceBreakdown( $checkInDate, $checkOutDate, $coupon = null, $language = null ) {

		if ( ! $language ) {
			$language = MPHB()->translation()->getCurrentLanguage();
		}

		$roomBreakdown     = $this->getRoomBreakdown( $checkInDate, $checkOutDate, $coupon, $language );
		$servicesBreakdown = $this->getServicesBreakdown( $checkInDate, $checkOutDate, $language );
		$feesBreakdown     = $this->getFeesBreakdown( $checkInDate, $checkOutDate, $roomBreakdown['discount_total'] );
		$taxesBreakdown    = array(
			'room'     => $this->getRoomTaxesBreakdown( $checkInDate, $checkOutDate, $roomBreakdown['discount_total'] ),
			'services' => $this->getServiceTaxesBreakdown( $servicesBreakdown['total'] ),
			'fees'     => $this->getFeeTaxesBreakdown( $feesBreakdown['total'] ),
		);

		// $total value with taxes and fees, but without discount: booking entity
		// will use the "total" value later to add "coupon" data to breakdown
		$total = $roomBreakdown['total'] + $servicesBreakdown['total'] + $feesBreakdown['total'] +
				 $taxesBreakdown['room']['total'] + $taxesBreakdown['services']['total'] +
				 $taxesBreakdown['fees']['total'];

		$priceBreakdown = array(
			'room'           => $roomBreakdown,
			'services'       => $servicesBreakdown,
			'fees'           => $feesBreakdown,
			'taxes'          => $taxesBreakdown,
			'total'          => $total,
			'discount_total' => $total - $roomBreakdown['discount'],
		);

		return $priceBreakdown;
	}

	private function getFeesBreakdown( $checkInDate, $checkOutDate, $roomPrice ) {
		$roomTypeId = $this->getRoomTypeId();
		$duration   = \MPHB\Utils\DateUtils::calcNights( $checkInDate, $checkOutDate );
		$adults     = $this->getAdults();
		$children   = $this->getChildren();

		$fees = MPHB()->settings()->taxesAndFees()->getFees( $roomTypeId );

		$breakdown = array(
			'list'  => array(),
			'total' => 0,
		);

		foreach ( $fees as $fee ) {
			$feePrice = 0;

			switch ( $fee['type'] ) {
				case 'per_guest_per_day':
					$feePrice = $adults * $fee['amount']['adults'] + $children * $fee['amount']['children'];
					if ( $fee['limit'] == 0 ) {
						$feePrice *= $duration;
					} else {
						$feePrice *= min( $fee['limit'], $duration );
					}
					break;

				case 'per_room_per_day':
					if ( $fee['limit'] == 0 ) {
						$feePrice = $fee['amount'] * $duration;
					} else {
						$feePrice = $fee['amount'] * min( $fee['limit'], $duration );
					}
					break;

				case 'per_room_percentage':
					$feePrice = $roomPrice / 100 * $fee['amount'];
					break;
			}

			$breakdown['list'][] = array(
				'label' => $fee['label'],
				'price' => $feePrice,
			);
			$breakdown['total'] += $feePrice;
		}

		return $breakdown;
	}

	private function getRoomTaxesBreakdown( $checkInDate, $checkOutDate, $roomPrice ) {
		$roomTypeId = $this->getRoomTypeId();
		$duration   = \MPHB\Utils\DateUtils::calcNights( $checkInDate, $checkOutDate );
		$adults     = $this->getAdults();
		$children   = $this->getChildren();

		$taxes = MPHB()->settings()->taxesAndFees()->getAccommodationTaxes( $roomTypeId );

		$breakdown = array(
			'list'  => array(),
			'total' => 0,
		);

		foreach ( $taxes as $tax ) {
			$taxPrice = 0;

			switch ( $tax['type'] ) {
				case 'per_guest_per_day':
					$taxPrice = $adults * $tax['amount']['adults'] + $children * $tax['amount']['children'];
					if ( $tax['limit'] == 0 ) {
						$taxPrice *= $duration;
					} else {
						$taxPrice *= min( $tax['limit'], $duration );
					}
					break;

				case 'per_room_per_day':
					if ( $tax['limit'] == 0 ) {
						$taxPrice = $tax['amount'] * $duration;
					} else {
						$taxPrice = $tax['amount'] * min( $tax['limit'], $duration );
					}
					break;

				case 'per_room_percentage':
					$taxPrice = $roomPrice / 100 * $tax['amount'];
					break;
			}

			$breakdown['list'][] = array(
				'label' => $tax['label'],
				'price' => $taxPrice,
			);
			$breakdown['total'] += $taxPrice;
		}

		return $breakdown;
	}

	private function getServiceTaxesBreakdown( $servicesPrice ) {
		$taxes = MPHB()->settings()->taxesAndFees()->getServiceTaxes();

		$breakdown = array(
			'list'  => array(),
			'total' => 0,
		);

		foreach ( $taxes as $tax ) {
			$taxPrice = 0;

			switch ( $tax['type'] ) {
				case 'percentage':
					$taxPrice = $servicesPrice / 100 * $tax['amount'];
					break;
			}

			$breakdown['list'][] = array(
				'label' => $tax['label'],
				'price' => $taxPrice,
			);
			$breakdown['total'] += $taxPrice;
		}

		return $breakdown;
	}

	private function getFeeTaxesBreakdown( $feesPrice ) {
		$taxes = MPHB()->settings()->taxesAndFees()->getFeeTaxes();

		$breakdown = array(
			'list'  => array(),
			'total' => 0,
		);

		foreach ( $taxes as $tax ) {
			$taxPrice = 0;

			switch ( $tax['type'] ) {
				case 'percentage':
					$taxPrice = $feesPrice / 100 * $tax['amount'];
					break;
			}

			$breakdown['list'][] = array(
				'label' => $tax['label'],
				'price' => $taxPrice,
			);
			$breakdown['total'] += $taxPrice;
		}

		return $breakdown;
	}

	/**
	 * @param int $bookingId
	 */
	public function setBookingId( $bookingId ) {
		$this->bookingId = $bookingId;
	}

	public function setUid( $uid ) {
		$this->uid = $uid;
	}

	/**
	 * @param $checkInDate
	 * @param $checkOutDate
	 * @param $language
	 *
	 * @return array
	 */
	private function getServicesBreakdown( $checkInDate, $checkOutDate, $language ) {
		$servicesBreakdown = array(
			'list'  => array(),
			'total' => 0.0,
		);

		foreach ( $this->reservedServices as $reservedService ) {
			$servicesBreakdown['list'][] = $reservedService->getPriceBreakdown( $checkInDate, $checkOutDate, $language );
			$servicesBreakdown['total'] += $reservedService->calcPrice( $checkInDate, $checkOutDate );
		}

		return $servicesBreakdown;
	}

}
