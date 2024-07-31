<?php

namespace MPHB\Entities;

use MPHB\Utils\DateUtils;

class Booking {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 * @var \DateTime
	 */
	private $dateTime;

	/**
	 *
	 * @var \DateTime
	 */
	private $checkInDate;

	/**
	 *
	 * @var \DateTime
	 */
	private $checkOutDate;

	/**
	 *
	 * @var ReservedRoom[]
	 */
	private $reservedRooms = array();

	/**
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 *
	 * @var string
	 */
	private $note;

	/**
	 *
	 * @var float
	 */
	private $totalPrice = 0.0;

	/**
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Language of customer
	 *
	 * @var string
	 */
	private $language;

	/**
	 *
	 * @var int
	 */
	private $couponId;

	/**
	 *
	 * @var string
	 */
	private $iCalProdid = '';

	/**
	 *
	 * @var string
	 */
	private $iCalSummary = '';

	/**
	 *
	 * @var string
	 */
	private $iCalDescription = '';

	/**
	 * 32-character UUID4 string.
	 *
	 * Used only on booking step of checkout shortcode. When user submits data
	 * (and creates booking with "pending" status) and then clicks "Back" button
	 * in browser, we can use this ID to find already created booking to merge
	 * it's data with the new one and let the user to proceed to payment again.
	 * When checkout is finished, checkoutId have not any usage.
	 *
	 * @see Task MB-573.
	 *
	 * @var string
	 */
	private $checkoutId = '';

	/**
	 * Identifies the source calendar of the imported booking. "Outdated" for
	 * all bookings imported before v3.4.0.
	 *
	 * @var string
	 *
	 * @see Task MB-906.
	 */
	protected $syncId = '';

	/**
	 * In which queue we imported this booking.
	 *
	 * @var int
	 */
	protected $syncQueueId = 0;

	protected $priceBreakdown = array();

	private $internalNotes;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		$this->setupParameters( $atts );

	}

	/**
	 *
	 * @param array          $atts
	 * @param int            $atts['id']
	 * @param \DateTime      $atts['check_in_date']
	 * @param \DateTime      $atts['check_out_date']
	 * @param ReservedRoom[] $atts['reserved_rooms']
	 * @param Customer       $atts['customer']
	 * @param float          $atts['total_price']
	 * @param string         $atts['note']
	 * @param string         $atts['status']
	 * @param int            $atts['coupon_id'] Optional.
	 * @param string         $atts['ical_prodid'] Optional.
	 * @param string         $atts['ical_summary'] Optional.
	 * @param string         $atts['ical_description'] Optional.
	 * @param string         $atts['language']
	 * @param string         $atts['checkout_id'] Optional.
	 * @param array          $atts['internal_notes'] Optional.
	 */
	public static function create( $atts ) {
		return new self( $atts );
	}

	/**
	 *
	 * @param array          $atts
	 * @param int            $atts['id']
	 * @param \DateTime      $atts['check_in_date']
	 * @param \DateTime      $atts['check_out_date']
	 * @param ReservedRoom[] $atts['reserved_rooms']
	 * @param Customer       $atts['customer']
	 * @param float          $atts['total_price']
	 * @param string         $atts['note']
	 * @param string         $atts['status']
	 * @param int            $atts['coupon_id'] Optional.
	 * @param string         $atts['ical_prodid'] Optional.
	 * @param string         $atts['ical_summary'] Optional.
	 * @param string         $atts['ical_description'] Optional.
	 * @param string         $atts['language']
	 * @param string         $atts['checkout_id'] Optional.
	 * @param array          $atts['internal_notes'] Optional.
	 */
	protected function setupParameters( $atts = array() ) {

		if ( isset( $atts['id'] ) ) {
			$this->id = $atts['id'];
		}

		if ( isset( $atts['datetime'] ) && is_a( $atts['datetime'], '\DateTime' ) ) {
			$this->dateTime = $atts['datetime'];
		}

		if ( isset( $atts['check_in_date'], $atts['check_out_date'] ) &&
			is_a( $atts['check_in_date'], '\DateTime' ) &&
			is_a( $atts['check_out_date'], '\DateTime' )
		) {
			$this->checkInDate  = $atts['check_in_date'];
			$this->checkOutDate = $atts['check_out_date'];
		}

		if ( isset( $atts['reserved_rooms'] ) ) {
			$this->reservedRooms = $atts['reserved_rooms'];
		}

		if ( isset( $atts['customer'] ) ) {
			$this->customer = $atts['customer'];
		}

		$this->status = isset( $atts['status'] ) ? $atts['status'] : \MPHB\PostTypes\BookingCPT\Statuses::STATUS_AUTO_DRAFT;

		if ( isset( $atts['note'] ) ) {
			$this->note = $atts['note'];
		}

		if ( isset( $atts['total_price'] ) ) {
			$this->totalPrice = $atts['total_price'];
		} else {
			$this->updateTotal();
		}

		if ( isset( $atts['coupon_id'] ) ) {
			$this->couponId = $atts['coupon_id'];
		}

		if ( ! empty( $atts['ical_prodid'] ) ) {
			$this->iCalProdid = $atts['ical_prodid'];
		}

		if ( isset( $atts['ical_summary'] ) ) {
			// Empty string is correct value, so empty() is not appliable
			$this->iCalSummary = $atts['ical_summary'];
		}

		if ( isset( $atts['ical_description'] ) ) {
			// Empty string is correct value, so empty() is not appliable
			$this->iCalDescription = $atts['ical_description'];
		}

		$this->language = isset( $atts['language'] ) ? $atts['language'] : MPHB()->translation()->getCurrentLanguage();

		if ( isset( $atts['checkout_id'] ) ) {
			$this->checkoutId = $atts['checkout_id'];
		}

		// If booking has such meta value and meta value is not empty - then the
		// booking is imported
		if ( isset( $atts['sync_id'] ) ) {
			$this->syncId = $atts['sync_id'];
		}

		if ( ! empty( $atts['sync_queue_id'] ) ) {
			$this->syncQueueId = intval( $atts['sync_queue_id'] );
		}

		$atts['internal_notes'] = ! empty( $atts['internal_notes'] ) ? $atts['internal_notes'] : array();

		$this->internalNotes = $atts['internal_notes'];
	}

	/**
	 *
	 * @param string $status
	 */
	public function setStatus( $status ) {
		$this->status = $status;
	}

	public function generateKey() {
		$key = uniqid( "booking_{$this->id}_", true );
		update_post_meta( $this->id, 'mphb_key', $key );
		return $key;
	}

	/**
	 * Note: you need to call updateTotal() manually when all changes are done.
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 *
	 * @since 3.8
	 */
	public function setDates( $checkInDate, $checkOutDate ) {
		$this->checkInDate  = $checkInDate;
		$this->checkOutDate = $checkOutDate;
	}

	/**
	 * @since 3.9.7
	 */
	public function getDateTime() {
		return $this->dateTime;
	}

	/**
	 * Note: you need to call updateTotal() manually when all changes are done.
	 *
	 * @param \MPHB\Entities\ReservedRoom[] $rooms
	 *
	 * @since 3.8
	 */
	public function setRooms( $rooms ) {
		$this->reservedRooms = $rooms;
	}

	public function updateTotal() {
		$this->totalPrice = $this->calcPrice();
	}

	/**
	 * @param $load Optional. Load from post metas if not set. TRUE by default.
	 * @return array|null
	 *
	 * @since 3.5.1
	 * @since 3.7.1 added optional parameter $load.
	 */
	public function getLastPriceBreakdown( $load = true ) {
		if ( empty( $this->priceBreakdown ) && $load ) {
			$prices = get_post_meta( $this->id, '_mphb_booking_price_breakdown', true );

			if ( ! empty( $prices ) ) {
				$prices = json_decode( mphb_strip_price_breakdown_json( $prices ), true );
			}

			if ( ! empty( $prices ) ) {
				$this->priceBreakdown = $prices;
			}
		}

		return $this->priceBreakdown;
	}

	/**
	 * Calculates new price breakdown from scratch.
	 *
	 * @return array
	 */
	public function getPriceBreakdown() {

		$coupon = null;
		if ( MPHB()->settings()->main()->isCouponsEnabled() && $this->couponId ) {
			$coupon = MPHB()->getCouponRepository()->findById( $this->couponId );
			if ( ! $coupon || ! $coupon->validate( $this ) ) {
				$coupon = null;
			}
		}

		$roomsBreakdown = array();

		// Calc each Room Price with services, fees, room coupon, taxes
		foreach ( $this->reservedRooms as $reservedRoom ) {
			$roomsBreakdown[] = $reservedRoom->getPriceBreakdown( $this->checkInDate, $this->checkOutDate, $coupon, $this->language );
		}

		// Calculate total. array_sum(array_column(...)) replaced with
		// array_reduce(). Fixes "Fatal error: Call to undefined function
		// MPHB\Entities\array_column()" on PHP 5.3
		$total = array_reduce(
			$roomsBreakdown,
			function ( $total, $breakdown ) {
				return $total + $breakdown['discount_total'];
			},
			0.0
		);

		// Calc total discount
		$discount = 0.0;
		if ( $coupon ) {
			$discount = array_sum(
				array_map(
					function ( $breakdown ) {
						return $breakdown['room']['discount'];
					},
					$roomsBreakdown
				)
			);
		}

		$priceBreakdown = array(
			'rooms' => $roomsBreakdown,
			'total' => apply_filters( 'mphb_booking_calculate_total_price', $total, $this ),
		);

		if (
			MPHB()->settings()->main()->getConfirmationMode() === 'payment' &&
			MPHB()->settings()->payment()->getAmountType() === 'deposit'
		) {
			$deposit = $this->calcDepositAmount( $total );

			// If not in the time frame, then they both will be equal
			if ( $deposit < $total ) {
				$priceBreakdown['deposit'] = $deposit;
			}
		}

		if ( ! is_null( $coupon ) ) {
			$priceBreakdown['coupon'] = array(
				'code'     => $coupon->getCode(),
				'discount' => $discount,
			);
		}

		$this->priceBreakdown = apply_filters( 'mphb_booking_price_breakdown', $priceBreakdown, $this );

		return $this->priceBreakdown;
	}

	/**
	 *
	 * @return float
	 */
	public function calcPrice() {

		if ( is_null( $this->checkInDate ) || is_null( $this->checkOutDate ) ) {
			return 0.0;
		}

		$breakdown = $this->getPriceBreakdown();
		return $breakdown['total'];

	}

	/**
	 * @param float|null $total
	 * @return float
	 *
	 * @since 3.8.3 Added new filter - "mphb_booking_deposit_price".
	 */
	public function calcDepositAmount( $total = null ) {
		if ( ! isset( $total ) ) {
			$total = $this->totalPrice;
		}

		$deposit = $total;

		if ( MPHB()->settings()->payment()->getAmountType() === 'deposit' ) {
			$timeFrame = MPHB()->settings()->payment()->getDepositTimeFrame();

			if ( $timeFrame === false || DateUtils::calcNightsSinceToday( $this->checkInDate ) >= $timeFrame ) {
				$depositAmount = (float) MPHB()->settings()->payment()->getDepositAmount();

				if ( MPHB()->settings()->payment()->getDepositType() === 'percent' ) {
					$deposit = round( $total * ( $depositAmount / 100 ), 2 );
				} else {
					$deposit = $depositAmount;
				}
			}
		}

		$deposit = apply_filters( 'mphb_booking_deposit_price', $deposit, $this );

		return $deposit;
	}

	/**
	 *
	 * @param string $message
	 * @param int    $author
	 */
	public function addLog( $message, $author = null ) {
		if ( is_null( $author ) ) {
			$author = is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ? get_current_user_id() : 0;
		}

		$commentdata = array(
			'comment_post_ID'      => $this->getId(),
			'comment_content'      => $message,
			'user_id'              => $author,
			'comment_date'         => mphb_current_time( 'mysql' ),
			'comment_date_gmt'     => mphb_current_time( 'mysql', get_option( 'gmt_offset' ) ),
			'comment_approved'     => 1,
			'comment_parent'       => 0,
			'comment_author'       => '',
			'comment_author_IP'    => '',
			'comment_author_url'   => '',
			'comment_author_email' => '',
			'comment_type'         => 'mphb_booking_log',
		);

		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			$apiKeyId                    = MPHB()->getAdvanced()->getApi()->authentication->getCurrentAuthKeyId();
			$commentdata['comment_meta'] = array( 'api_key_id' => $apiKeyId );
		}

		wp_insert_comment( $commentdata );
	}

	public function getLogs() {

		do_action( 'mphb_booking_before_get_logs' );

		$logs = get_comments(
			array(
				'post_id' => $this->getId(),
				'order'   => 'ASC',
			)
		);

		do_action( 'mphb_booking_after_get_logs' );

		return $logs;
	}

	/**
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 *
	 * @return string
	 */
	public function getKey() {
		return get_post_meta( $this->id, 'mphb_key', true );
	}

	/**
	 *
	 * @return \DateTime
	 */
	public function getCheckInDate() {
		return $this->checkInDate;
	}

	/**
	 *
	 * @return \DateTime
	 */
	public function getCheckOutDate() {
		return $this->checkOutDate;
	}

	/**
	 *
	 * @return ReservedRoom[]
	 */
	public function getReservedRooms() {
		return $this->reservedRooms;
	}

	/**
	 * @return int[]
	 *
	 * @since 3.8
	 */
	public function getReservedRoomIds() {
		return array_map(
			function ( $reservedRoom ) {
				return (int) $reservedRoom->getId();
			},
			$this->reservedRooms
		);
	}

	/**
	 * @return int[]
	 *
	 * @since 3.8
	 */
	public function getRoomIds() {
		return array_map(
			function ( $reservedRoom ) {
				return (int) $reservedRoom->getRoomId();
			},
			$this->reservedRooms
		);
	}

	/**
	 *
	 * @param  Customer $customer
	 */
	public function setCustomer( Customer $customer ) {
		$this->customer = $customer;
	}

	/**
	 *
	 * @return Customer
	 */
	public function getCustomer() {
		return $this->customer;
	}

	/**
	 * @return string
	 */
	public function getNote() {
		return $this->note;
	}

	public function setNote( string $note ) {
		$this->note = $note;
	}

	/**
	 *
	 * @return float
	 */
	public function getTotalPrice() {
		return $this->totalPrice;
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
	 * @return array of dates where key is date in 'Y-m-d' format and value is date in frontend date format
	 */
	public function getDates( $fromToday = false ) {

		$fromDate = $this->checkInDate->format( 'Y-m-d' );
		$toDate   = $this->checkOutDate->format( 'Y-m-d' );

		if ( $fromToday ) {
			$today    = mphb_current_time( 'Y-m-d' );
			$fromDate = $fromDate >= $today ? $fromDate : $today;
		}
		return DateUtils::createDateRangeArray( $fromDate, $toDate );
	}

	/**
	 * Set expiration time of pending confirmation for booking
	 *
	 * @param string $type Possible types: user, payment.
	 * @param int    $expirationTime
	 */
	public function updateExpiration( $type, $expirationTime ) {
		update_post_meta( $this->id, "mphb_pending_{$type}_expired", $expirationTime );
	}

	/**
	 * Retrieve expiration time for booking in UTC.
	 *
	 * @param string $type Possible types: user, payment.
	 * @return int
	 */
	public function retrieveExpiration( $type ) {
		return intval( get_post_meta( $this->id, "mphb_pending_{$type}_expired", true ) );
	}

	/**
	 * Delete expiration time of pending confirmation for booking.
	 *
	 * @param string $type Possible types: user, payment.
	 */
	public function deleteExpiration( $type ) {
		delete_post_meta( $this->id, "mphb_pending_{$type}_expired" );
	}

	/**
	 *
	 * @return string
	 */
	public function getICalProdid() {
		return $this->iCalProdid;
	}

	/**
	 *
	 * @return string|null
	 */
	public function getICalSummary() {
		return $this->iCalSummary;
	}

	/**
	 *
	 * @return string|null
	 */
	public function getICalDescription() {
		return $this->iCalDescription;
	}

	/**
	 * Retrieve language of customer
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 *
	 * @param int $paymentId
	 * @return bool
	 */
	public function isExpectPayment( $paymentId ) {
		$expectPayment = get_post_meta( $this->id, '_mphb_wait_payment', true );
		return $paymentId == $expectPayment;
	}

	/**
	 *
	 * @param int $paymentId
	 */
	public function setExpectPayment( $paymentId ) {
		update_post_meta( $this->id, '_mphb_wait_payment', $paymentId );
	}

	/**
	 * @return int|false Payment ID or FALSE if the booking does not expect any
	 *     payment.
	 *
	 * @since 3.6.1
	 */
	public function getExpectPaymentId() {
		$expectPayment = get_post_meta( $this->id, '_mphb_wait_payment', true );

		if ( $expectPayment !== '' ) {
			return (int) $expectPayment;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param AbstractCoupon $coupon
	 *
	 * @return boolean|\WP_Error
	 */
	public function applyCoupon( $coupon ) {

		$isValidCoupon = $coupon->validate( $this, true );
		if ( is_wp_error( $isValidCoupon ) ) {
			return $isValidCoupon;
		}

		$this->couponId = $coupon->getId();
		$this->updateTotal();

		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getCouponCode() {
		$coupon = MPHB()->getCouponRepository()->findById( $this->couponId );
		return $coupon ? $coupon->getCode() : $this->couponId;
	}

	/**
	 *
	 * @return int
	 */
	public function getCouponId() {
		return $this->couponId;
	}

	/**
	 *
	 * @return string
	 */
	public function getCheckoutId() {
		return $this->checkoutId;
	}

	/**
	 * @since 3.4.0
	 */
	public function getSyncId() {
		return $this->syncId;
	}

	/**
	 * @since 3.4.0
	 */
	public function getSyncQueueId() {
		return $this->syncQueueId;
	}

	/**
	 * @since 3.9.3
	 */
	public function getInternalNotes() {
		return $this->internalNotes;
	}

	/**
	 * @return bool
	 *
	 * @since 3.4.0
	 */
	public function isImported() {
		return ! empty( $this->syncId );
	}

	/**
	 * @return bool
	 *
	 * @since 4.2.2
	 */
	public function isPending() {
		return in_array( $this->getStatus(), MPHB()->postTypes()->booking()->statuses()->getPendingRoomStatuses() );
	}
}
