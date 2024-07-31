<?php

namespace MPHB\CSV\Bookings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper contains methods for getting booking data for the CSV export.
 */
final class BookingsExporterHelper {

	const EXPORT_COLUMN_BOOKING_ID          = 'booking-id';
	const EXPORT_COLUMN_BOOKING_STATUS      = 'booking-status';
	const EXPORT_COLUMN_CHECK_IN            = 'check-in';
	const EXPORT_COLUMN_CHECK_OUT           = 'check-out';
	const EXPORT_COLUMN_ROOM_TYPE           = 'room-type';
	const EXPORT_COLUMN_ROOM_TYPE_ID        = 'room-type-id';
	const EXPORT_COLUMN_ROOM                = 'room';
	const EXPORT_COLUMN_RATE                = 'rate';
	const EXPORT_COLUMN_ADULTS              = 'adults';
	const EXPORT_COLUMN_CHILDREN            = 'children';
	const EXPORT_COLUMN_SERVICES            = 'services';
	const EXPORT_COLUMN_FIRST_NAME          = 'first-name';
	const EXPORT_COLUMN_LAST_NAME           = 'last-name';
	const EXPORT_COLUMN_EMAIL               = 'email';
	const EXPORT_COLUMN_PHONE               = 'phone';
	const EXPORT_COLUMN_COUNTRY             = 'country';
	const EXPORT_COLUMN_ADDRESS             = 'address';
	const EXPORT_COLUMN_CITY                = 'city';
	const EXPORT_COLUMN_STATE               = 'state';
	const EXPORT_COLUMN_POSTCODE            = 'postcode';
	const EXPORT_COLUMN_CUSTOMER_NOTE       = 'customer-note';
	const EXPORT_COLUMN_GUEST_NAME          = 'guest-name';
	const EXPORT_COLUMN_SUBTOTAL            = 'subtotal';
	const EXPORT_COLUMN_TAXES               = 'taxes';
	const EXPORT_COLUMN_TOTAL_TAXES         = 'total-taxes';
	const EXPORT_COLUMN_FEES                = 'fees';
	const EXPORT_COLUMN_TOTAL_FEES          = 'total-fees';
	const EXPORT_COLUMN_SUBTOTAL_SERVICES   = 'subtotal-services';
	const EXPORT_COLUMN_TOTAL_SERVICE_TAXES = 'total-service-taxes';
	const EXPORT_COLUMN_TOTAL_FEE_TAXES     = 'total-fee-taxes';
	const EXPORT_COLUMN_COUPON              = 'coupon';
	const EXPORT_COLUMN_DISCOUNT            = 'discount';
	const EXPORT_COLUMN_PRICE               = 'price';
	const EXPORT_COLUMN_PAID                = 'paid';
	const EXPORT_COLUMN_PAYMENTS            = 'payments';
	const EXPORT_COLUMN_DATE                = 'date';

	// this is helper with static functions only
	private function __construct() {}


	/**
	 * @return array [ column_id => column_label, ... ]
	 */
	public static function getBookingsExportColumns(): array {

		return apply_filters(
			'mphb_export_bookings_columns',
			array(
				static::EXPORT_COLUMN_BOOKING_ID          => __( 'ID', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_BOOKING_STATUS      => __( 'Status', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_CHECK_IN            => __( 'Check-in', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_CHECK_OUT           => __( 'Check-out', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_ROOM_TYPE           => __( 'Accommodation Type', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_ROOM_TYPE_ID        => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_ROOM                => __( 'Accommodation', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_RATE                => __( 'Rate', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_ADULTS              => __( 'Adults/Guests', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_CHILDREN            => __( 'Children', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_SERVICES            => __( 'Services', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_FIRST_NAME          => __( 'First Name', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_LAST_NAME           => __( 'Last Name', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_EMAIL               => __( 'Email', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_PHONE               => __( 'Phone', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_COUNTRY             => __( 'Country', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_ADDRESS             => __( 'Address', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_CITY                => __( 'City', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_STATE               => __( 'State / County', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_POSTCODE            => __( 'Postcode', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_CUSTOMER_NOTE       => __( 'Customer Note', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_GUEST_NAME          => __( 'Full Guest Name', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_SUBTOTAL            => __( 'Accommodation Subtotal', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_TAXES               => __( 'Accommodation Taxes', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_TOTAL_TAXES         => __( 'Total Accommodation Taxes', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_FEES                => __( 'Fees', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_TOTAL_FEES          => __( 'Total Fees', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_SUBTOTAL_SERVICES   => __( 'Services Subtotal', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_TOTAL_SERVICE_TAXES => __( 'Total Service Taxes', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_TOTAL_FEE_TAXES     => __( 'Total Fee Taxes', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_COUPON              => __( 'Coupon', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_DISCOUNT            => __( 'Discount', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_PRICE               => __( 'Total', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_PAID                => __( 'Paid', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_PAYMENTS            => __( 'Payment Details', 'motopress-hotel-booking' ),
				static::EXPORT_COLUMN_DATE                => __( 'Date', 'motopress-hotel-booking' ),
			)
		);
	}

	/**
	 * @param \MPHB\Entities\ReservedRoom $reservedRoom
	 * @param array                       $columnNames - list of column names from contants in this class
	 * @return array [ column_name => column_value, ... ]
	 */
	public static function getReservedRoomData( $reservedRoom, $columnNames ): array {

		$columnsWithData = array();

		$booking            = $reservedRoom->getBooking();
		$roomPriceBreakdown = null !== $reservedRoom->getLastRoomPriceBreakdown() ? $reservedRoom->getLastRoomPriceBreakdown() : array();

		foreach ( $columnNames as $columnName ) {

			$columnValue = '';

			switch ( $columnName ) {

				case static::EXPORT_COLUMN_BOOKING_ID:
					$columnValue = $booking->getId();
					break;

				case static::EXPORT_COLUMN_BOOKING_STATUS:
					$columnValue = mphb_get_status_label( $booking->getStatus() );
					break;

				case static::EXPORT_COLUMN_CHECK_IN:
					$columnValue = $booking->getCheckInDate()->format( MPHB()->settings()->dateTime()->getDateFormat() );
					break;

				case static::EXPORT_COLUMN_CHECK_OUT:
					$columnValue = $booking->getCheckOutDate()->format( MPHB()->settings()->dateTime()->getDateFormat() );
					break;

				case static::EXPORT_COLUMN_ROOM_TYPE:
					$roomTypeIdForCurrentLanguage = MPHB()->translation()->getOriginalId(
						$reservedRoom->getRoomTypeId(),
						MPHB()->postTypes()->roomType()->getPostType()
					);

					$roomTypeForCurrentLanguage = MPHB()->getRoomTypeRepository()->findById( $roomTypeIdForCurrentLanguage );
					$columnValue                = null !== $roomTypeForCurrentLanguage ? $roomTypeForCurrentLanguage->getTitle() : '';
					break;

				case static::EXPORT_COLUMN_ROOM_TYPE_ID:
					$roomTypeIdForCurrentLanguage = MPHB()->translation()->getOriginalId(
						$reservedRoom->getRoomTypeId(),
						MPHB()->postTypes()->roomType()->getPostType()
					);
					$columnValue                  = $roomTypeIdForCurrentLanguage;
					break;

				case static::EXPORT_COLUMN_ROOM:
					$roomIdForCurrentLanguage = MPHB()->translation()->getOriginalId(
						$reservedRoom->getRoomId(),
						MPHB()->postTypes()->room()->getPostType()
					);

					$accommodation = MPHB()->getRoomRepository()->findById( $roomIdForCurrentLanguage );
					$columnValue   = null !== $accommodation ? $accommodation->getTitle() : '';
					break;

				case static::EXPORT_COLUMN_RATE:
					$rateIdForCurrentLanguage = MPHB()->translation()->getOriginalId(
						$reservedRoom->getRateId(),
						MPHB()->postTypes()->rate()->getPostType()
					);

					$rateForCurrentLanguage = MPHB()->getRateRepository()->findById( $rateIdForCurrentLanguage );
					$columnValue            = null !== $rateForCurrentLanguage ? $rateForCurrentLanguage->getTitle() : '';
					break;

				case static::EXPORT_COLUMN_ADULTS:
					$columnValue = $reservedRoom->getAdults();
					break;

				case static::EXPORT_COLUMN_CHILDREN:
					$columnValue = $reservedRoom->getChildren();
					break;

				case static::EXPORT_COLUMN_FIRST_NAME:
					$columnValue = $booking->getCustomer()->getFirstName();
					break;

				case static::EXPORT_COLUMN_LAST_NAME:
					$columnValue = $booking->getCustomer()->getLastName();
					break;

				case static::EXPORT_COLUMN_EMAIL:
					$columnValue = $booking->getCustomer()->getEmail();
					break;

				case static::EXPORT_COLUMN_PHONE:
					$columnValue = $booking->getCustomer()->getPhone();
					break;

				case static::EXPORT_COLUMN_COUNTRY:
					$columnValue = $booking->getCustomer()->getCountry();
					break;

				case static::EXPORT_COLUMN_ADDRESS:
					$columnValue = $booking->getCustomer()->getAddress1();
					break;

				case static::EXPORT_COLUMN_CITY:
					$columnValue = $booking->getCustomer()->getCity();
					break;

				case static::EXPORT_COLUMN_STATE:
					$columnValue = $booking->getCustomer()->getState();
					break;

				case static::EXPORT_COLUMN_POSTCODE:
					$columnValue = $booking->getCustomer()->getZip();
					break;

				case static::EXPORT_COLUMN_CUSTOMER_NOTE:
					$columnValue = $booking->getNote();
					break;

				case static::EXPORT_COLUMN_GUEST_NAME:
					$columnValue = $reservedRoom->getGuestName();
					break;

				case static::EXPORT_COLUMN_SUBTOTAL:
					$columnValue = html_entity_decode( // Decode #&36; into $
						mphb_format_price(
							$roomPriceBreakdown['room']['total'],
							array(
								'as_html'            => false,
								'thousand_separator' => '',
							)
						)
					);
					break;

				case static::EXPORT_COLUMN_SERVICES:
					$columnValue = static::getReservedRoomServices( $reservedRoom );
					break;

				case static::EXPORT_COLUMN_SUBTOTAL_SERVICES:
					if ( ! empty( $roomPriceBreakdown['services']['total'] ) ) {

						$columnValue = html_entity_decode(
							mphb_format_price(
								! empty( $roomPriceBreakdown['services']['total'] ) ? $roomPriceBreakdown['services']['total'] : 0,
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;

				case static::EXPORT_COLUMN_TOTAL_SERVICE_TAXES:
					if ( ! empty( $roomPriceBreakdown['taxes']['services']['total'] ) ) {

						$columnValue = html_entity_decode(
							mphb_format_price(
								$roomPriceBreakdown['taxes']['services']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;

				case static::EXPORT_COLUMN_TAXES:
					$columnValue = static::getReservedRoomTaxes( $roomPriceBreakdown );
					break;

				case static::EXPORT_COLUMN_TOTAL_TAXES:
					if ( ! empty( $roomPriceBreakdown['taxes']['room']['total'] ) ) {

						$columnValue = html_entity_decode(
							mphb_format_price(
								$roomPriceBreakdown['taxes']['room']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;

				case static::EXPORT_COLUMN_FEES:
					$columnValue = static::getReservedRoomFees( $roomPriceBreakdown );
					break;

				case static::EXPORT_COLUMN_TOTAL_FEES:
					if ( ! empty( $roomPriceBreakdown['fees']['total'] ) ) {

						$columnValue = html_entity_decode(
							mphb_format_price(
								$roomPriceBreakdown['fees']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;

				case static::EXPORT_COLUMN_TOTAL_FEE_TAXES:
					if ( ! empty( $roomPriceBreakdown['taxes']['fees']['total'] ) ) {

						$columnValue = html_entity_decode(
							mphb_format_price(
								$roomPriceBreakdown['taxes']['fees']['total'],
								array(
									'as_html'            => false,
									'thousand_separator' => '',
								)
							)
						);
					}
					break;

				case static::EXPORT_COLUMN_COUPON:
					$columnValue = $booking->getCouponCode();
					break;

				case static::EXPORT_COLUMN_DISCOUNT:
					$columnValue = html_entity_decode( // Decode #&36; into $
						mphb_format_price(
							$roomPriceBreakdown['total'] - $roomPriceBreakdown['discount_total'],
							array(
								'as_html'            => false,
								'thousand_separator' => '',
							)
						)
					);
					break;

				case static::EXPORT_COLUMN_PRICE:
					$columnValue = html_entity_decode( // Decode #&36; into $
						mphb_format_price(
							$roomPriceBreakdown['discount_total'],
							array(
								'as_html'            => false,
								'thousand_separator' => '',
							)
						)
					);
					break;

				case static::EXPORT_COLUMN_PAID:
					$columnValue = static::getReservedRoomPaidAmount( $booking, $roomPriceBreakdown );
					break;

				case static::EXPORT_COLUMN_PAYMENTS:
					$columnValue = static::getBookingPayments( $booking );
					break;

				case static::EXPORT_COLUMN_DATE:
					$columnValue = get_the_date( MPHB()->settings()->dateTime()->getDateFormat() . ' H:i:s', $booking->getId() );
					break;
			}

			$columnsWithData[ $columnName ] = $columnValue;
		}

		return apply_filters( 'mphb_export_bookings_parse_columns', $columnsWithData, $booking, $reservedRoom );
	}

	/**
	 * @param \MPHB\Entities\ReservedRoom $reservedRoom
	 */
	private static function getReservedRoomServices( $reservedRoom ): string {

		$reservedServices = $reservedRoom->getReservedServices();

		if ( empty( $reservedServices ) ) {
			return '';
		}

		$services = array();

		foreach ( $reservedServices as $reservedService ) {

			$reservedService = MPHB()->translation()->translateReservedService( $reservedService );

			$service = html_entity_decode( $reservedService->getTitle() );

			if ( $reservedService->isPayPerAdult() ) {
				$service .= ' ' . sprintf( _n( 'x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking' ), $reservedService->getAdults() );
			}

			if ( $reservedService->isFlexiblePay() ) {
				$service .= ' ' . sprintf( _n( 'x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking' ), $reservedService->getQuantity() );
			}

			$services[] = $service;
		}

		return implode( ', ', $services );
	}

	/**
	 * @param array $roomPriceBreakdown
	 */
	private static function getReservedRoomTaxes( $roomPriceBreakdown ): string {

		$taxText = array();

		if ( ! empty( $roomPriceBreakdown['taxes']['room']['list'] ) ) {

			foreach ( $roomPriceBreakdown['taxes']['room']['list'] as $roomTax ) {

				$tax = html_entity_decode(
					mphb_format_price(
						$roomTax['price'],
						array(
							'as_html'            => false,
							'thousand_separator' => '',
						)
					)
				);

				$taxLabel  = $roomTax['label'];
				$taxText[] = "{$tax},{$taxLabel}";
			}
		}

		return ! empty( $taxText ) ? implode( ';', $taxText ) : '';
	}

	/**
	 * @param array $roomPriceBreakdown
	 */
	private static function getReservedRoomFees( $roomPriceBreakdown ): string {

		$taxText = array();

		if ( ! empty( $roomPriceBreakdown['fees']['list'] ) ) {

			foreach ( $roomPriceBreakdown['fees']['list'] as $roomTax ) {
				$tax = html_entity_decode(
					mphb_format_price(
						$roomTax['price'],
						array(
							'as_html'            => false,
							'thousand_separator' => '',
						)
					)
				);

				$taxLabel  = $roomTax['label'];
				$taxText[] = "{$tax},{$taxLabel}";
			}
		}

		return implode( ';', $taxText );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $roomPriceBreakdown
	 */
	private static function getReservedRoomPaidAmount( $booking, $roomPriceBreakdown ): string {

		$reservedRoomPaidAmount = 0;

		$payments = MPHB()->getPaymentRepository()->findAll(
			array(
				'booking_id'  => $booking->getId(),
				'post_status' => \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED,
			)
		);

		$bookingPriceBreakdown = $booking->getLastPriceBreakdown();

		if ( ! empty( $payments ) && 0 < $bookingPriceBreakdown['total'] ) {

			$bookingPaid = 0.0;

			foreach ( $payments as $payment ) {

				$bookingPaid += $payment->getAmount();
			}

			$reservedRoomPaidAmount = $roomPriceBreakdown['discount_total'] / $bookingPriceBreakdown['total'] * $bookingPaid;
		}

		return html_entity_decode(
			mphb_format_price(
				$reservedRoomPaidAmount,
				array(
					'as_html'            => false,
					'thousand_separator' => '',
				)
			)
		);
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	private static function getBookingPayments( $booking ): string {

		$payments = MPHB()->getPaymentRepository()->findAll(
			array(
				'booking_id'  => $booking->getId(),
				'post_status' => \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED,
			)
		);

		$paymentStrings = array();

		foreach ( $payments as $payment ) {

			$paymentId     = $payment->getId();
			$paymentStatus = mphb_get_status_label( $payment->getStatus() );
			$paidAmount    = html_entity_decode(
				mphb_format_price(
					$payment->getAmount(),
					array(
						'as_html'            => false,
						'thousand_separator' => '',
					)
				)
			);

			$paymentGateway      = MPHB()->gatewayManager()->getGateway( $payment->getGatewayId() );
			$paymentGatewayLabel = ! is_null( $paymentGateway ) ? $paymentGateway->getAdminTitle() : $payment->getGatewayId();

			$paymentStrings[] = "#{$paymentId},{$paymentStatus},{$paidAmount},{$paymentGatewayLabel}";
		}

		return implode( ';', $paymentStrings );
	}
}
