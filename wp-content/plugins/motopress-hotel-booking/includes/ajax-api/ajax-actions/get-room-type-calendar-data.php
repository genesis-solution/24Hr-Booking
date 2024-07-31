<?php

namespace MPHB\AjaxApi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetRoomTypeCalendarData extends AbstractAjaxApiAction {

	const REQUEST_DATA_START_DATE              = 'start_date';
	const REQUEST_DATA_END_DATE                = 'end_date';
	const REQUEST_DATA_ROOM_TYPE_ID            = 'room_type_id';
	const REQUEST_DATA_IS_SHOW_PRICES          = 'is_show_prices';
	const REQUEST_DATA_IS_TRUNCATE_PRICES      = 'is_truncate_prices';
	const REQUEST_DATA_IS_SHOW_PRICES_CURRENCY = 'is_show_prices_currency';

	const MAX_REQUEST_DATES_INTERVAL_IN_DAYS = 370;

	public static function getAjaxActionNameWithouPrefix() {
		return 'get_room_type_calendar_data';
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

		$requestData[ static::REQUEST_DATA_START_DATE ] = static::getDateFromRequest( static::REQUEST_DATA_START_DATE, true );
		$requestData[ static::REQUEST_DATA_END_DATE ]   = static::getDateFromRequest( static::REQUEST_DATA_END_DATE, true );

		if ( $requestData[ static::REQUEST_DATA_START_DATE ] > $requestData[ static::REQUEST_DATA_END_DATE ] ) {

			throw new \Exception(
				'Parameter ' . static::REQUEST_DATA_START_DATE . ' (' .
				$requestData[ static::REQUEST_DATA_START_DATE ]->format( 'Y-m-d' ) .
				') can not be after ' .
				static::REQUEST_DATA_END_DATE . ' (' .
				$requestData[ static::REQUEST_DATA_END_DATE ]->format( 'Y-m-d' ) .
				')'
			);
		}

		$datesIntervalInDays = $requestData[ static::REQUEST_DATA_END_DATE ]->diff( $requestData[ static::REQUEST_DATA_START_DATE ] )->days;

		if ( static::MAX_REQUEST_DATES_INTERVAL_IN_DAYS < $datesIntervalInDays ) {

			throw new \Exception(
				'Interval between ' . static::REQUEST_DATA_START_DATE . ' and ' .
				static::REQUEST_DATA_END_DATE . ' can not be more then ' . static::MAX_REQUEST_DATES_INTERVAL_IN_DAYS . ' days.'
			);
		}

		$requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ] = static::getIntegerFromRequest( static::REQUEST_DATA_ROOM_TYPE_ID );

		$requestData[ static::REQUEST_DATA_IS_SHOW_PRICES ] = static::getBooleanFromRequest(
			static::REQUEST_DATA_IS_SHOW_PRICES,
			false,
			MPHB()->settings()->main()->isDirectRoomBooking() &&
			MPHB()->settings()->main()->isRoomTypeCalendarShowPrices()
		);

		$requestData[ static::REQUEST_DATA_IS_TRUNCATE_PRICES ] = static::getBooleanFromRequest(
			static::REQUEST_DATA_IS_TRUNCATE_PRICES,
			false,
			MPHB()->settings()->main()->isDirectRoomBooking() &&
			MPHB()->settings()->main()->isRoomTypeCalendarTruncatePrices()
		);

		$requestData[ static::REQUEST_DATA_IS_SHOW_PRICES_CURRENCY ] = static::getBooleanFromRequest(
			static::REQUEST_DATA_IS_SHOW_PRICES_CURRENCY,
			false,
			MPHB()->settings()->main()->isDirectRoomBooking() &&
			MPHB()->settings()->main()->isRoomTypeCalendarShowPricesCurrency()
		);

		return $requestData;
	}


	protected static function doAction( array $requestData ) {

		$result = array();

		$roomType           = MPHB()->getCoreAPI()->getRoomTypeById( $requestData[ static::REQUEST_DATA_ROOM_TYPE_ID ] );
		$roomTypeOriginalId = null !== $roomType ? $roomType->getOriginalId() : 0;

		$processingDate = clone $requestData[ static::REQUEST_DATA_START_DATE ];

		do {
			$dateData = MPHB()->getCoreAPI()->getRoomTypeAvailabilityData(
				$roomTypeOriginalId,
				$processingDate,
				$requestData[ static::REQUEST_DATA_IS_ADMIN ] && MPHB()->settings()->main()->isBookingRulesForAdminDisabled()
			)->toArray();

			$result[ $processingDate->format( 'Y-m-d' ) ] = $dateData;

			$processingDate->modify( '+1 day' );

		} while ( $processingDate <= $requestData[ static::REQUEST_DATA_END_DATE ] );


		if ( null !== $roomType && $requestData[ static::REQUEST_DATA_IS_SHOW_PRICES ] ) {

			$processingDate = clone $requestData[ static::REQUEST_DATA_START_DATE ];

			$priceFormatAtts = array(
				'is_truncate_price' => $requestData[ static::REQUEST_DATA_IS_TRUNCATE_PRICES ],
				'decimals'          => 0,
				'currency_symbol'   => $requestData[ static::REQUEST_DATA_IS_SHOW_PRICES_CURRENCY ] ? MPHB()->
					settings()->currency()->getCurrencySymbol() : '',
			);

			do {

				$processingFormattedDate = $processingDate->format( 'Y-m-d' );
				$dateData                = $result[ $processingFormattedDate ];

				$nextProcessingDate = clone $processingDate;
				$nextProcessingDate->modify( '+1 day' );
				$nextProcessingFormattedDate = $nextProcessingDate->format( 'Y-m-d' );

				if ( ( \MPHB\Core\RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_AVAILABLE == $dateData['roomTypeStatus'] ||
					\MPHB\Core\RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_EARLIER_MIN_ADVANCE == $dateData['roomTypeStatus'] ||
					\MPHB\Core\RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_LATER_MAX_ADVANCE == $dateData['roomTypeStatus'] ) &&
					( ! $dateData['isCheckInNotAllowed'] ||
					( ! empty( $result[ $nextProcessingFormattedDate ] ) &&
						\MPHB\Core\RoomTypeAvailabilityStatus::ROOM_TYPE_AVAILABILITY_STATUS_NOT_AVAILABLE != $result[ $nextProcessingFormattedDate ]['roomTypeStatus'] ) )
				) {

					$dateData['price'] = MPHB()->getCoreAPI()->formatPrice(
						MPHB()->getCoreAPI()->getMinRoomTypeBasePriceForDate(
							$roomTypeOriginalId,
							$processingDate
						),
						$priceFormatAtts
					);

				} else {
					$dateData['price'] = '<span class="mphb-price">&nbsp;</span>';
				}

				$result[ $processingFormattedDate ] = $dateData;

				$processingDate = $nextProcessingDate;

			} while ( $processingDate <= $requestData[ static::REQUEST_DATA_END_DATE ] );
		}

		wp_send_json_success( $result, 200 );
	}
}
