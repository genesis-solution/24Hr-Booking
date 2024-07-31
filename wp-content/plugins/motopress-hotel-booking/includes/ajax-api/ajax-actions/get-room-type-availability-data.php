<?php

namespace MPHB\AjaxApi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetRoomTypeAvailabilityData extends AbstractAjaxApiAction {

	const REQUEST_DATA_ROOM_TYPE_ID   = 'room_type_id';
	const REQUEST_DATA_CHECK_IN_DATE  = 'check_in_date';
	const REQUEST_DATA_CHECK_OUT_DATE = 'check_out_date';
	const REQUEST_DATA_ADULTS_COUNT   = 'adults_count';
	const REQUEST_DATA_CHILDREN_COUNT = 'children_count';


	public static function getAjaxActionNameWithouPrefix() {
		return 'get_room_type_availability_data';
	}

	protected static function isValidateWPNonce(): bool {
		// validate wp nonce only for admin area to support caching plugins
		return static::getBooleanFromRequest( static::REQUEST_DATA_IS_ADMIN );
	}

	/**
	 * @throws Exception when validation of request parameters failed
	 */
	protected static function getValidatedRequestData() {

		$requestData = parent::getValidatedRequestData();

		$requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ] = static::getIntegerFromRequest( static::REQUEST_DATA_ROOM_TYPE_ID, true );

		if ( 0 >= $requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ] ) {

			throw new \Exception(
				'Parameter ' . static::REQUEST_DATA_ROOM_TYPE_ID .
				' must be integer > 0 but (' . $requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ] . ') was given.'
			);
		}

		$requestData[ static::REQUEST_DATA_CHECK_IN_DATE ]  = static::getDateFromRequest( static::REQUEST_DATA_CHECK_IN_DATE, true );
		$requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ] = static::getDateFromRequest( static::REQUEST_DATA_CHECK_OUT_DATE, true );

		if ( $requestData[ static::REQUEST_DATA_CHECK_IN_DATE ] > $requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ] ) {

			throw new \Exception(
				'Parameter ' . static::REQUEST_DATA_CHECK_IN_DATE . ' (' .
				$requestData[ static::REQUEST_DATA_CHECK_IN_DATE ]->format( 'Y-m-d' ) .
				') can not be after ' .
				static::REQUEST_DATA_CHECK_OUT_DATE . ' (' .
				$requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ]->format( 'Y-m-d' ) .
				')'
			);
		}

		$requestData[ static::REQUEST_DATA_ADULTS_COUNT ] = static::getIntegerFromRequest( static::REQUEST_DATA_ADULTS_COUNT, false, -1 );

		$isDirectBooking                = MPHB()->settings()->main()->isDirectRoomBooking();
		$isDirectBookingCapacityEnabled = 'capacity' === MPHB()->settings()->main()->getDirectBookingPricing();

		if ( ( ! $isDirectBooking || $isDirectBookingCapacityEnabled ) &&
			-1 !== $requestData[ static::REQUEST_DATA_ADULTS_COUNT ] &&
			false === \MPHB\Utils\ValidateUtils::validateAdults( $requestData[ static::REQUEST_DATA_ADULTS_COUNT ] ) ) {

			throw new \Exception(
				'Parameter ' . static::REQUEST_DATA_ADULTS_COUNT .
				' must be integer between min and max adults from settings but (' . $requestData[ static::REQUEST_DATA_ADULTS_COUNT ] . ') was given.'
			);
		}

		$requestData[ static::REQUEST_DATA_CHILDREN_COUNT ] = static::getIntegerFromRequest( static::REQUEST_DATA_CHILDREN_COUNT, false, -1 );

		if ( ( ! $isDirectBooking || $isDirectBookingCapacityEnabled ) &&
			-1 !== $requestData[ static::REQUEST_DATA_CHILDREN_COUNT ] &&
			false === \MPHB\Utils\ValidateUtils::validateChildren( $requestData[ static::REQUEST_DATA_CHILDREN_COUNT ] ) ) {

			throw new \Exception(
				'Parameter ' . static::REQUEST_DATA_CHILDREN_COUNT .
				' must be integer between min and max children from settings but (' . $requestData[ static::REQUEST_DATA_CHILDREN_COUNT ] . ') was given.'
			);
		}

		return $requestData;
	}


	protected static function doAction( array $requestData ) {

		$roomType = MPHB()->getCoreAPI()->getRoomTypeById( $requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ] );

		if ( ! MPHB()->getRulesChecker()->reservationRules()->verify(
			$requestData[ static::REQUEST_DATA_CHECK_IN_DATE ],
			$requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ],
			$requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ]
		) ) {

			wp_send_json_error(
				array( 'message' => __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) )
			);
		}

		$searchArgs = apply_filters(
			'mphb_search_available_rooms',
			array(
				'availability'      => 'free',
				'from_date'         => $requestData[ static::REQUEST_DATA_CHECK_IN_DATE ],
				'to_date'           => $requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ],
				'room_type_id'      => $roomType->getOriginalId(),
				'skip_buffer_rules' => false,
			)
		);

		$availableRooms = MPHB()->getRoomPersistence()->searchRooms( $searchArgs );

		$unavailableRooms = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms(
			$requestData[ static::REQUEST_DATA_CHECK_IN_DATE ],
			$requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ],
			$roomType->getOriginalId()
		);

		$unavailableRooms = array_intersect( $availableRooms, $unavailableRooms ); // Filter not available rooms
		$freeRoomsCount   = count( $availableRooms ) - count( $unavailableRooms );

		// Calculate the price for the period
		$price     = 0;
		$priceHtml = '';

		if ( MPHB()->settings()->main()->getDirectBookingPricing() != 'disabled' ) {

			$args = array();

			if ( -1 !== $requestData[ static::REQUEST_DATA_ADULTS_COUNT ] &&
				-1 !== $requestData[ static::REQUEST_DATA_CHILDREN_COUNT ] ) {

				$args['adults']   = $requestData[ static::REQUEST_DATA_ADULTS_COUNT ];
				$args['children'] = $requestData[ static::REQUEST_DATA_CHILDREN_COUNT ];
			}

			$price = mphb_get_room_type_period_price(
				$requestData[ static::REQUEST_DATA_CHECK_IN_DATE ],
				$requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ],
				$roomType,
				$args
			);

			$nights = \MPHB\Utils\DateUtils::calcNights(
				$requestData[ static::REQUEST_DATA_CHECK_IN_DATE ],
				$requestData[ static::REQUEST_DATA_CHECK_OUT_DATE ]
			);

			$taxesAndFees = $roomType->getTaxesAndFees();
			$taxesAndFees->setRoomPrice( $price );
			$taxesAndFees->setupParams(
				array(
					'period_nights'   => $nights,
					'adults_amount'   => $requestData[ static::REQUEST_DATA_ADULTS_COUNT ],
					'children_amount' => $requestData[ static::REQUEST_DATA_CHILDREN_COUNT ],
				)
			);

			$priceFormatAtts = array(
				'period'        => true,
				'period_nights' => $nights,
				'period_title'  => __( 'Based on your search parameters', 'motopress-hotel-booking' ),
			);

			// This filter is documented in template-functions.php
			$priceHtml = apply_filters(
				'mphb_tmpl_the_room_type_price_for_dates',
				mphb_format_price( $price, $priceFormatAtts, $roomType ),
				$taxesAndFees,
				$priceFormatAtts,
				$price
			);
		}

		if ( 0 < $freeRoomsCount ) {

			wp_send_json_success(
				array(
					'freeCount' => $freeRoomsCount,
					'price'     => $price,
					'priceHtml' => $priceHtml,
				),
				200
			);

		} else {
			wp_send_json_error( array( 'message' => __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) ) );
		}
	}
}
