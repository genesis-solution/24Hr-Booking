<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\Booking;
use MPHB\Entities\Customer;
use MPHB\Advanced\Api\ApiHelper;
use MPHB\Entities\ReservedRoom;
use MPHB\Entities\ReservedService;
use MPHB\Utils\DateUtils;
use WP_Error;

class BookingData extends AbstractPostData {

	/**
	 * @var Booking
	 */
	public $entity;

	public static function getRepository() {
		return MPHB()->getBookingRepository();
	}

	public static function getProperties() {
		return array(
			'id'                      => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'                  => array(
				'description' => 'Booking status.',
				'type'        => 'string',
				'default'     => MPHB()->postTypes()->booking()->statuses()->getDefaultNewBookingStatus(),
				'enum'        => array_keys( MPHB()->postTypes()->booking()->statuses()->getStatuses() ),
				'context'     => array( 'view', 'edit' ),
			),
			'date_created'            => array(
				'description' => 'The date the booking was created.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_created_utc'        => array(
				'description' => 'The date the booking was created, as UTC.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'key'                     => array(
				'description' => 'Booking key.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'check_in_date'           => array(
				'description' => sprintf( 'Check in date as %s.', MPHB()->settings()->dateTime()->getDateTransferFormat() ),
				'type'        => 'string',
				'format'      => 'date',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'check_out_date'          => array(
				'description' => sprintf( 'Check out date as %s.', MPHB()->settings()->dateTime()->getDateTransferFormat() ),
				'type'        => 'string',
				'format'      => 'date',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'check_in_time'           => array(
				'description' => 'Check in time in H:i:s format.',
				'type'        => 'string',
				'pattern'     => '^\d{2}(:\d{2}(:\d{2}(\.\d+)?)?)?$',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'check_out_time'          => array(
				'description' => 'Check out time in H:i:s format.',
				'type'        => 'string',
				'pattern'     => '^\d{2}(:\d{2}(:\d{2}(\.\d+)?)?)?$',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'customer'                => array(
				'description' => 'Customer Information.',
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'properties'  => CustomerData::getProperties(),
			),
			'reserved_accommodations' => array(
				'description' => 'Reserved Accommodations.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'accommodation'                => array(
							'description' => 'Accommodation id.',
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'required'    => true,
						),
						'accommodation_type'           => array(
							'description' => 'Accommodation type id.',
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'rate'                         => array(
							'description' => 'Rate id.',
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'adults'                       => array(
							'description' => 'Adults.',
							'type'        => 'integer',
							'minimum'     => MPHB()->settings()->main()->getMinAdults(),
							'context'     => array( 'view', 'edit' ),
							'required'    => true,
						),
						'children'                     => array(
							'description' => 'Children.',
							'type'        => 'integer',
							'minimum'     => MPHB()->settings()->main()->getMinChildren(),
							'context'     => array( 'view', 'edit' ),
						),
						'guest_name'                   => array(
							'description' => 'Guest name.',
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'services'                     => array(
							'description' => 'Services.',
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'       => array(
										'description' => 'Services id.',
										'type'        => 'integer',
										'context'     => array( 'view', 'edit' ),
										'required'    => true,
									),
									'adults'   => array(
										'description' => 'Quantity of adults. Used for services that are paid for for each person.',
										'type'        => 'integer',
										'context'     => array( 'view', 'edit' ),
										'default'     => 1,
									),
									'quantity' => array(
										'description' => 'How many times the customer will be charged.',
										'type'        => 'integer',
										'context'     => array( 'view', 'edit' ),
										'default'     => 1,
									),
									'price'    => array(
										'description' => 'Price for a service.',
										'type'        => 'number',
										'context'     => array( 'view', 'edit' ),
										'readonly'    => true,
									),
								),
							),
						),
						'accommodation_price_per_days' => array(
							'description' => 'Accommodation price breakdown per days.',
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'date'  => array(
										'description' => 'Services id.',
										'type'        => 'string',
										'format'      => 'date',
										'context'     => array( 'view', 'edit' ),
										'readonly'    => true,
									),
									'price' => array(
										'description' => 'Services id.',
										'type'        => 'number',
										'context'     => array( 'view', 'edit' ),
										'readonly'    => true,
									),
								),
							),
						),
						'fees'                         => array(
							'type'     => 'array',
							'items'    => array(
								'type'       => 'object',
								'context'    => array( 'view', 'edit' ),
								'properties' => array(
									'label' => array(
										'type'     => 'string',
										'context'  => array( 'view', 'edit' ),
										'readonly' => true,
									),
									'value' => array(
										'type'     => 'number',
										'context'  => array( 'view', 'edit' ),
										'readonly' => true,
									),
								),
							),
							'context'  => array( 'view', 'edit' ),
							'readonly' => true,
						),
						'taxes'                        => array(
							'type'       => 'object',
							'properties' => array(
								'accommodation' => array(
									'type'     => 'array',
									'items'    => array(
										'type'       => 'object',
										'context'    => array( 'view', 'edit' ),
										'properties' => array(
											'label' => array(
												'type'     => 'string',
												'context'  => array( 'view', 'edit' ),
												'readonly' => true,
											),
											'value' => array(
												'type'     => 'number',
												'context'  => array( 'view', 'edit' ),
												'readonly' => true,
											),
										),
									),
									'context'  => array( 'view', 'edit' ),
									'readonly' => true,
								),
								'services'      => array(
									'type'     => 'array',
									'items'    => array(
										'type'       => 'object',
										'context'    => array( 'view', 'edit' ),
										'properties' => array(
											'label' => array(
												'type'     => 'string',
												'context'  => array( 'view', 'edit' ),
												'readonly' => true,
											),
											'value' => array(
												'type'     => 'number',
												'context'  => array( 'view', 'edit' ),
												'readonly' => true,
											),
										),
									),
									'context'  => array( 'view', 'edit' ),
									'readonly' => true,
								),
								'fees'          => array(
									'type'     => 'array',
									'items'    => array(
										'type'       => 'object',
										'context'    => array( 'view', 'edit' ),
										'properties' => array(
											'label' => array(
												'type'     => 'string',
												'context'  => array( 'view', 'edit' ),
												'readonly' => true,
											),
											'value' => array(
												'type'     => 'number',
												'context'  => array( 'view', 'edit' ),
												'readonly' => true,
											),
										),
									),
									'context'  => array( 'view', 'edit' ),
									'readonly' => true,
								),
							),
							'context'    => array( 'view', 'edit' ),
							'readonly'   => true,
						),
						'discount'                     => array(
							'description' => 'Discount by coupon.',
							'type'        => 'number',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
			),
			'coupon_code'             => array(
				'description' => 'Coupon code.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'currency'                => array(
				'description' => 'Currency the booking was created with, in ISO format.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'total_price'             => array(
				'description' => 'Total price.',
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'checkout_id'             => array(
				'description' => 'Checkout id.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'payments'                => array(
				'description' => 'Payments.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => 'Identifier of payment resource.',
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'status'   => array(
							'description' => 'Payment status.',
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'amount'   => array(
							'description' => 'Amount.',
							'type'        => 'number',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'currency' => array(
							'description' => 'Payment currency in ISO format.',
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
			),
			'imported'                => array(
				'description' => 'Imported.',
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'ical_description'        => array(
				'description' => 'ICal description.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'ical_prodid'             => array(
				'description' => 'ICal prodid.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'ical_summary'            => array(
				'description' => 'ICal summary.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'note'                    => array(
				'description' => 'Note.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'internal_notes'          => array(
				'description' => 'Internal notes.',
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'note'     => array(
							'description' => 'Note text.',
							'type'        => 'string',
						),
						'user'     => array(
							'description' => 'User id.',
							'type'        => 'integer',
						),
						'date_utc' => array(
							'description' => 'Note date in UTC.',
							'type'        => 'string',
							'format'      => 'date-time',
						),
					),
				),
				'readonly'    => true,
			),
		);
	}

	/**
	 * @param array $accommodationPrices [{y-m-d} => {price_value}]
	 *
	 * @return array [ ['date'=>{y-m-d}, 'price'=>{price_value}] ]
	 */
	protected function prepareAccommodationPrices( $accommodationPrices ) {
		$preparedAccommodationPrices = array();
		if ( ! count( $accommodationPrices ) ) {
			return $preparedAccommodationPrices;
		}
		foreach ( $accommodationPrices as $date => $price ) {
			$preparedAccommodationPrices[] = array(
				'date'  => $date,
				'price' => $price,
			);
		}

		return $preparedAccommodationPrices;
	}

	protected function fillEmptyPricesTaxesFees( $reservedAccommodations ) {
		return array_map(
			function ( $reservedAccommodation ) {
				$reservedAccommodation['accommodation_price_per_days'] = array();
				$reservedAccommodation['fees']                         = array();
				$reservedAccommodation['taxes']['accommodation']       = array();
				$reservedAccommodation['taxes']['services']            = array();
				$reservedAccommodation['taxes']['fees']                = array();
				$reservedAccommodation['discount']                     = 0;

				return $reservedAccommodation;
			},
			$reservedAccommodations
		);
	}

	/**
	 * @param $reservedAccommodations
	 *
	 * @return array
	 */
	protected function appendPricesTaxesFees( $reservedAccommodations ) {
		if ( $this->imported && $this->total_price == 0 ) {
			return $this->fillEmptyPricesTaxesFees( $reservedAccommodations );
		}

		$priceBreakdownRaw = $this->entity->getPriceBreakdown();

		foreach ( $reservedAccommodations as $key => $reservedAccommodation ) {
			$accommodationPrices      = $priceBreakdownRaw['rooms'][ $key ]['room']['list'];
			$accommodationPricePerDay = count( $accommodationPrices ) ? $this->prepareAccommodationPrices( $accommodationPrices ) : array();
			$reservedAccommodations[ $key ]['accommodation_price_per_days'] = $accommodationPricePerDay;

			$accommodationServicePrices = $priceBreakdownRaw['rooms'][ $key ]['services']['list'];
			foreach ( $accommodationServicePrices as $serviceKey => $servicePrice ) {
				$reservedAccommodations[ $key ]['services'][ $serviceKey ]['price'] = $servicePrice['total'];
			}

			$reservedAccommodations[ $key ]['fees'] = $priceBreakdownRaw['rooms'][ $key ]['fees']['list'];

			$reservedAccommodations[ $key ]['taxes']['accommodation'] = array_map(
				function ( $accommodationTax ) {
					$accommodationTax['value'] = $accommodationTax['price'];
					unset( $accommodationTax['price'] );

					return $accommodationTax;
				},
				$priceBreakdownRaw['rooms'][ $key ]['taxes']['room']['list']
			);
			$reservedAccommodations[ $key ]['taxes']['services']      = array_map(
				function ( $accommodationTax ) {
					$accommodationTax['value'] = $accommodationTax['price'];
					unset( $accommodationTax['price'] );

					return $accommodationTax;
				},
				$priceBreakdownRaw['rooms'][ $key ]['taxes']['services']['list']
			);
			$reservedAccommodations[ $key ]['taxes']['fees']          = array_map(
				function ( $accommodationTax ) {
					$accommodationTax['value'] = $accommodationTax['price'];
					unset( $accommodationTax['price'] );

					return $accommodationTax;
				},
				$priceBreakdownRaw['rooms'][ $key ]['taxes']['fees']['list']
			);

			$reservedAccommodations[ $key ]['discount'] = $priceBreakdownRaw['rooms'][ $key ]['room']['discount'];
		}

		return $reservedAccommodations;
	}

	/**
	 * @return array
	 */
	protected function getReservedAccommodations() {
		$reservedAccommodations        = array();
		$reservedAccommodationEntities = MPHB()->getReservedRoomRepository()->findAllByBooking( $this->entity->getId() );
		if ( ! count( $reservedAccommodationEntities ) ) {
			return $reservedAccommodations;
		}
		foreach ( $reservedAccommodationEntities as $key => $reservedAccommodation ) {
			$reservedAccommodations[ $key ]['accommodation']      = $reservedAccommodation->getRoomId();
			$reservedAccommodations[ $key ]['accommodation_type'] = intval( $reservedAccommodation->getRoomTypeId() );
			$reservedAccommodations[ $key ]['rate']               = $reservedAccommodation->getRateId();
			$reservedAccommodations[ $key ]['adults']             = $reservedAccommodation->getAdults();
			$reservedAccommodations[ $key ]['children']           = $reservedAccommodation->getChildren();
			$reservedAccommodations[ $key ]['guest_name']         = $reservedAccommodation->getGuestName();
			$reservedAccommodations[ $key ]['services']           = array_map(
				function ( $service ) {
					$serviceResponse['id'] = $service->getId();
					if ( $service->isFlexiblePay() ) {
						$serviceResponse['quantity'] = $service->getQuantity();
					}
					if ( $service->isPayPerAdult() ) {
						$serviceResponse['adults'] = $service->getAdults();
					}

					return $serviceResponse;

				},
				$reservedAccommodation->getReservedServices()
			);
		}

		return $reservedAccommodations;
	}

	protected function getPayments() {
		$payments = array();

		if ( ! $this->id ) {
			return $payments;
		}

		$paymentEntities = MPHB()->getPaymentRepository()->findAll( array( 'booking_id' => $this->id ) );

		foreach ( $paymentEntities as $paymentEntity ) {
			$payments[] = array(
				'id'       => $paymentEntity->getId(),
				'status'   => str_replace( PaymentData::STATUS_PREFIX, '', $paymentEntity->getStatus() ),
				'amount'   => $paymentEntity->getAmount(),
				'currency' => $paymentEntity->getCurrency(),
			);
		}

		return $payments;
	}

	protected function getImported() {
		return $this->entity->isImported();
	}

	protected function getCheckInDate() {
		$checkInDate = $this->entity->getCheckInDate();

		return ApiHelper::prepareDateResponse( $checkInDate );
	}

	protected function getCheckOutDate() {
		$checkOutDate = $this->entity->getCheckOutDate();

		return ApiHelper::prepareDateResponse( $checkOutDate );
	}

	protected function getCheckInTime() {
		return MPHB()->settings()->dateTime()->getCheckInTime();
	}

	protected function getCheckOutTime() {
		return MPHB()->settings()->dateTime()->getCheckOutTime();
	}

	protected function getCurrency() {
		return MPHB()->settings()->currency()->getCurrencyCode();
	}

	protected function getIcalDescription() {
		return $this->entity->getICalDescription();
	}

	protected function getIcalProdid() {
		return $this->entity->getICalProdid();
	}

	protected function getIcalSummary() {
		return $this->entity->getICalSummary();
	}

	protected function getInternalNotes() {
		return array_map(
			function ( $note ) {
				$note['date_utc'] = wp_date( ApiHelper::DATETIME_FORMAT_ISO8601, $note['date'], new \DateTimeZone( 'UTC' ) );
				unset( $note['date'] );

				return $note;
			},
			$this->entity->getInternalNotes()
		);
	}

	protected function setCouponCode( $code ) {
		if ( ! $code ) {
			return true;
		}
		$coupon = MPHB()->getCouponRepository()->findByCode( mphb_clean( $code ) );
		if ( ! $coupon ) {
			return new WP_Error( 'mphb_rest_invalid_coupon', 'Coupon not found.', array( 'status' => 400 ) );
		}

		$isApplyCoupon = $this->entity->applyCoupon( $coupon );
		if ( is_wp_error( $isApplyCoupon ) ) {
			return new WP_Error( 'mphb_rest_invalid_coupon', $isApplyCoupon->get_error_message(), array( 'status' => 400 ) );
		}
		return true;
	}


	/**
	 * @param  array $newCustomerData  Array with a new data about Customer
	 *
	 * @return Customer
	 */
	private function prepareCustomer( $newCustomerData ) {
		$oldCustomerData = array();
		$customer        = $this->entity->getCustomer();

		if ( $customer instanceof Customer ) {
			$customerDataObject = new CustomerData( $customer );
			$oldCustomerData    = $customerDataObject->getData();
		}

		$preparedCustomerData = array_merge(
			$oldCustomerData,
			array_intersect_key( $newCustomerData, array_flip( CustomerData::getFields() ) )
		);

		return new Customer( $preparedCustomerData );
	}

	/**
	 * @return array
	 */
	protected function getCustomer() {
		$customerEntity = $this->entity->getCustomer();
		if ( ! $customerEntity ) {
			$customerEntity = new Customer();
		}
		$customerData = new CustomerData( $customerEntity );

		return $customerData->getData();
	}

	protected function setCustomer( $customerFields ) {
		$customer = $this->prepareCustomer( $customerFields );
		$this->entity->setCustomer( $customer );
	}

	/**
	 * @param $date
	 *
	 * @throws \Exception
	 */
	protected function setCheckInDate( $date ) {
		try {
			$checkInDate = ApiHelper::prepareDateRequest( $date );
		} catch ( \Exception $e ) {
			throw new \Exception( 'Check-in date is not valid.' );
		}

		$today = ApiHelper::prepareDateRequest( date( 'Y-m-d' ) );
		if ( ! $this->entity->getId() && DateUtils::calcNights( $today, $checkInDate ) < 0 ) {
			throw new \Exception( 'Check-in date cannot be earlier than today.' );
		}

		$checkOutDate = $this->entity->getCheckOutDate();
		$this->entity->setDates( $checkInDate, $checkOutDate );
	}

	/**
	 * @param $date
	 *
	 * @throws \Exception
	 */
	protected function setCheckOutDate( $date ) {
		$checkInDate = $this->entity->getCheckInDate();
		try {
			$checkOutDate = ApiHelper::prepareDateRequest( $date );
		} catch ( \Exception $e ) {
			throw new \Exception( 'Check-out date is not valid.' );
		}

		$this->entity->setDates( $checkInDate, $checkOutDate );
	}

	/**
	 * @param  array $reservedServicesRequest
	 *
	 * @return ReservedService[]
	 * @throws \Exception
	 */
	private function parseReservedServices( array $reservedServicesRequest ) {
		$reservedServices = array();
		foreach ( $reservedServicesRequest as $key => $serviceDetails ) {
			if ( ! count( $serviceDetails ) ) {
				continue;
			}

			$reservedService = ReservedService::create(
				array(
					'id'       => $serviceDetails['id'],
					'adults'   => isset( $serviceDetails['adults'] ) ? $serviceDetails['adults'] : 1,
					'quantity' => isset( $serviceDetails['quantity'] ) ? $serviceDetails['quantity'] : 1,
				)
			);

			if ( is_null( $reservedService ) ) {
				throw new \Exception(
					sprintf(
						'Invalid %s: %d.',
						sprintf( 'services[%d][id]', $key ),
						$serviceDetails['id']
					)
				);
			}
			$reservedServices[] = $reservedService;
		}

		return $reservedServices;
	}


	/**
	 * @param  int $accommodationId
	 *
	 * @return int
	 * @throws \Exception
	 */
	private function getRateIdForAccommodationId( $accommodationId ) {
		$checkInDate   = $this->entity->getCheckInDate();
		$checkOutDate  = $this->entity->getCheckOutDate();
		$rateArgs      = array(
			'check_in_date'  => $checkInDate,
			'check_out_date' => $checkOutDate,
		);
		$accommodation = MPHB()->getRoomRepository()->findById( $accommodationId );
		if ( is_null( $accommodation ) ) {
			throw new \Exception( sprintf( 'Invalid %s: %d.', 'accommodation', $accommodationId ) );
		}
		$accommodationTypeId = $accommodation->getRoomTypeId();

		/**
		 * @var $isNeedCheckRules bool
		 * Verify is skipped if the booking exists and contains past dates.
		 * Makes possibility update past bookings information.
		 */
		$isNeedCheckRules = DateUtils::calcNightsSinceToday( $checkInDate ) > 0 && ! is_null( $this->entity->getId() );
		if ( $isNeedCheckRules && ! MPHB()->getRulesChecker()->verify( $checkInDate, $checkOutDate, $accommodationTypeId ) ) {
			$accommodationType = MPHB()->getRoomTypeRepository()->findById( $accommodationTypeId );
			throw new \Exception(
				sprintf( 'Invalid %s: %d.', 'accommodation_type', $accommodationTypeId ) . ' ' .
								  sprintf( 'Selected dates do not meet booking rules for type %s', $accommodationType->getTitle() )
			);
		}

		$allowedRates = MPHB()->getRateRepository()->findAllActiveByRoomType( $accommodationTypeId, $rateArgs );
		if ( empty( $allowedRates ) ) {
			throw new \Exception( 'There are no rates for requested dates.' );
		}
		$rate = reset( $allowedRates );

		return $rate->getOriginalId();
	}

	/**
	 * @param  array $reservedAccommodationsRequest
	 *
	 * @return ReservedRoom[]
	 * @throws \Exception
	 */
	private function parseReservedAccommodations( array $reservedAccommodationsRequest ) {
		$reservedAccommodations = array();
		foreach ( $reservedAccommodationsRequest as $accommodationDetails ) {
			$accommodation = array(
				'room_id'  => $accommodationDetails['accommodation'],
				'rate_id'  => $this->getRateIdForAccommodationId( $accommodationDetails['accommodation'] ),
				'adults'   => $accommodationDetails['adults'],
				'children' => $accommodationDetails['children'] ?? 0,
			);
			if ( isset( $accommodationDetails['services'] ) && is_array( $accommodationDetails['services'] ) ) {
				try {
					$accommodation['reserved_services'] = $this->parseReservedServices( $accommodationDetails['services'] );
				} catch ( \Exception $e ) {
					throw new \Exception( $e->getMessage() );
				}
			}

			$reservedAccommodations[] = ReservedRoom::create( $accommodation );
		}

		return $reservedAccommodations;
	}

	protected function setReservedAccommodations( $value ) {
		$reservedAccommodations = $this->parseReservedAccommodations( $value );
		$this->entity->setRooms( $reservedAccommodations );
	}

	/**
	 * @param  \DateTime $checkInDate
	 * @param  \DateTime $checkOutDate
	 * @param  Booking   $booking
	 *
	 * @return bool
	 */
	private function isAvailableDates( \DateTime $checkInDate, \DateTime $checkOutDate, Booking $booking ) {
		$reservedAccommodationIds = $booking->getRoomIds();
		$lockedAccommodationIds   = MPHB()->getRoomRepository()->getLockedRooms(
			$checkInDate,
			$checkOutDate,
			0,
			array( 'exclude_bookings' => $booking->getId() )
		);
		foreach ( $reservedAccommodationIds as $reservedAccommodationId ) {
			if ( in_array( $reservedAccommodationId, $lockedAccommodationIds, true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function validate() {
		if ( $this->entity->isImported() ) {
			throw new \Exception( 'You cannot edit the imported booking. Please update the source booking and resync your calendars.' );
		}

		if ( $this->entity->getStatus() === 'trash' ) {
			throw new \Exception( 'You cannot edit the trashed booking.' );
		}

		$checkInDate            = $this->entity->getCheckInDate();
		$checkOutDate           = $this->entity->getCheckOutDate();
		$reservedAccommodations = $this->entity->getReservedRooms();
		if ( empty( $reservedAccommodations ) ) {
			throw new \Exception( 'There are no accommodations selected for reservation.' );
		}
		foreach ( $reservedAccommodations as $accommodation ) {
			$accommodationTypeId = $accommodation->getRoomTypeId();
			$accommodationType   = MPHB()->getRoomTypeRepository()->findById( $accommodationTypeId );
			$originalTypeId      = ( ! is_null( $accommodationType ) ) ? $accommodationType->getOriginalId() : $accommodationTypeId;
			$rateId              = $accommodation->getRateId();
			$adults              = $accommodation->getAdults();
			$children            = $accommodation->getChildren();

			if ( ! $accommodationType || $accommodationType->getStatus() != 'publish' ) {
				throw new \Exception( sprintf( 'Invalid %s: %d.', 'accommodation_type', $accommodationType->getId() ) );
			}

			$allowedRates   = MPHB()->getRateRepository()->findAllActiveByRoomType(
				$originalTypeId,
				array(
					'check_in_date'  => $checkInDate,
					'check_out_date' => $checkOutDate,
					'mphb_language'  => 'original',
				)
			);
			$allowedRateIds = array_map(
				function ( \MPHB\Entities\Rate $rate ) {
					return $rate->getOriginalId();
				},
				$allowedRates
			);

			if ( ! in_array( $rateId, $allowedRateIds ) ) {
				throw new \Exception( 'Rate is not valid.' );
			}

			if ( $adults > $accommodationType->getAdultsCapacity() ) {
				throw new \Exception( 'Adults number is not valid.' );
			}

			if ( $children > $accommodationType->getChildrenCapacity() ) {
				throw new \Exception( 'Children number is not valid.' );
			}

			if ( $accommodationType->hasLimitedTotalCapacity() && $adults + $children > $accommodationType->getTotalCapacity() ) {
				throw new \Exception( 'The total number of guests is not valid.' );
			}

			if ( ( $checkInDate > $checkOutDate ) || ( (int) $checkInDate->diff( $checkOutDate )->format( '%R%a' ) < 1 ) ) {
				throw new \Exception( 'Check-out date cannot be earlier than check-in date.' );
			}
		}

		if ( ! $this->isAvailableDates( $checkInDate, $checkOutDate, $this->entity ) ) {
			throw new \Exception( 'Dates unavailable for booking.' );
		}

		return true;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getData() {
		$data   = array();
		$fields = static::getFields();
		foreach ( $fields as $field ) {
			switch ( $field ) {
				case 'reserved_accommodations':
					$data[ $field ] = $this->appendPricesTaxesFees( $this->{$field} );
					break;
				default:
					$data[ $field ] = $this->{$field};
			}
		}

		return $data;
	}

	/**
	 * @throws \Exception
	 */
	public function save() {
		$this->validate();
		$this->entity->updateTotal();

		$isNewBooking = is_null( $this->entity->getId() );

		parent::save();

		// Update the details of the reserved rooms of the booking.
		// Because now reservedRooms saved only on create new booking (with non-auto-draft status ).
		// TODO needs to allow save reserved rooms on update.
		// @see: BookingRepository::save()
		if ( ! $isNewBooking ) {
			MPHB()->getBookingRepository()->updateReservedRooms( $this->entity->getId() );
		}
	}
}
