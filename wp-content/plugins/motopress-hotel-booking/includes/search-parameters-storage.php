<?php

namespace MPHB;

class SearchParametersStorage {

	private $defaults = array();

	public function __construct() {
		$this->defaults = array(
			'mphb_adults'         => '',
			'mphb_children'       => '',
			'mphb_check_in_date'  => '',
			'mphb_check_out_date' => '',
		);
	}

	/**
	 *
	 * @return array Array with keys 'mphb_adults', 'mphb_children', 'mphb_check_in_date', 'mphb_check_out_date' filled stored values or empty strings.
	 */
	public function get() {

		$parameters = MPHB()->getSession()->get( 'mphb_search_parameters' );

		if ( is_null( $parameters ) ) {
			$parameters = array();
		}

		return $this->sanitize( $parameters );
	}

	/**
	 *
	 * @param Entities\RoomType $roomType
	 * @return array Array with keys 'mphb_adults', 'mphb_children', 'mphb_check_in_date', 'mphb_check_out_date' filled stored values or empty strings.
	 */
	public function getForRoomType( Entities\RoomType $roomType ) {
		return $this->sanitizeForRoomType( $this->get(), $roomType );
	}

	/**
	 *
	 * @param array $parameters
	 * @return array
	 */
	private function sanitize( $parameters ) {
		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

		$resultParameters = $this->defaults;

		if ( ! empty( $parameters['mphb_check_in_date'] ) && ! empty( $parameters['mphb_check_out_date'] ) ) {

			$checkInDateObj  = \DateTime::createFromFormat( $dateFormat, $parameters['mphb_check_in_date'] );
			$checkOutDateObj = \DateTime::createFromFormat( $dateFormat, $parameters['mphb_check_out_date'] );
			$todayDateObj    = \DateTime::createFromFormat( 'Y-m-d', mphb_current_time( 'Y-m-d' ) );

			if ( $checkInDateObj &&
				$checkOutDateObj &&
				$checkInDateObj >= $todayDateObj &&
				MPHB()->getRulesChecker()->verify( $checkInDateObj, $checkOutDateObj )
			) {
				$resultParameters['mphb_check_in_date']  = $checkInDateObj->format( $dateFormat );
				$resultParameters['mphb_check_out_date'] = $checkOutDateObj->format( $dateFormat );
			}
		}

		if ( ! empty( $parameters['mphb_adults'] ) ) {

			$adults = intval( $parameters['mphb_adults'] );

			if ( $adults >= MPHB()->settings()->main()->getMinAdults() &&
				$adults <= MPHB()->settings()->main()->getSearchMaxAdults()
			) {
				$resultParameters['mphb_adults'] = (string) $adults;
			}
		}

		if ( isset( $parameters['mphb_children'] ) && $parameters['mphb_children'] !== '' ) {

			$children = intval( $parameters['mphb_children'] );

			if ( $children >= MPHB()->settings()->main()->getMinChildren() &&
				$children <= MPHB()->settings()->main()->getSearchMaxChildren()
			) {
				$resultParameters['mphb_children'] = (string) $children;
			}
		}

		return $resultParameters;
	}

	/**
	 * @param array                   $parameters
	 * @param \MPHB\Entities\RoomType $roomType
	 * @return array
	 *
	 * @since 3.7.0 added new filter - "mphb_search_available_rooms".
	 */
	private function sanitizeForRoomType( $parameters, Entities\RoomType $roomType ) {

		$resultParameters = $this->defaults;

		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

		if ( ! empty( $parameters['mphb_check_in_date'] ) && ! empty( $parameters['mphb_check_out_date'] ) ) {

			$checkInDate  = \DateTime::createFromFormat( $dateFormat, $parameters['mphb_check_in_date'] );
			$checkOutDate = \DateTime::createFromFormat( $dateFormat, $parameters['mphb_check_out_date'] );

			$rateAtts = array(
				'check_in_date'  => $checkInDate,
				'check_out_date' => $checkOutDate,
				'active'         => true,
			);

			$searchAtts = apply_filters(
				'mphb_search_available_rooms',
				array(
					'availability' => 'free',
					'from_date'    => $checkInDate,
					'to_date'      => $checkOutDate,
					'count'        => 1,
					'room_type_id' => $roomType->getOriginalId(),
				)
			);

			$availableRoom = MPHB()->getRoomPersistence()->searchRooms( $searchAtts );

			if ( ! empty( $availableRoom ) &&
				MPHB()->getRateRepository()->isExistsForRoomType( $roomType->getId(), $rateAtts )
			) {
				$resultParameters['mphb_check_in_date']  = $parameters['mphb_check_in_date'];
				$resultParameters['mphb_check_out_date'] = $parameters['mphb_check_out_date'];
			}
		}

		if ( ! empty( $parameters['mphb_adults'] ) ) {

			$adults = intval( $parameters['mphb_adults'] );

			if ( $adults >= MPHB()->settings()->main()->getMinAdults() &&
				$adults <= MPHB()->settings()->main()->getSearchMaxAdults()
			) {
				$resultParameters['mphb_adults'] = (string) $adults;
			}
		}

		if ( isset( $parameters['mphb_children'] ) && $parameters['mphb_children'] !== '' ) {

			$children = intval( $parameters['mphb_children'] );

			if ( $children >= MPHB()->settings()->main()->getMinChildren() &&
				$children <= MPHB()->settings()->main()->getSearchMaxChildren()
			) {
				$resultParameters['mphb_children'] = (string) $children;
			}
		}

		return $resultParameters;
	}

	public function hasStored() {
		$parameters = MPHB()->getSession()->get( 'mphb_search_parameters' );
		return ! is_null( $parameters );
	}

	/**
	 *
	 * @param array  $parameters Array with keys 'mphb_adults', 'mphb_children', 'mphb_check_in_date', 'mphb_check_out_date'.
	 * @param int    $parameters['mphb_adults'] int
	 * @param int    $parameters['mphb_children'] int
	 * @param string $parameters['mphb_check_in_date'] Date in DateTransfer format (Y-m-d)
	 * @param string $parameters['mphb_check_out_date'] Date in DateTransfer format (Y-m-d)
	 */
	public function save( $parameters ) {
		$parameters = $this->sanitize( $parameters );
		MPHB()->getSession()->set( 'mphb_search_parameters', $parameters );
	}

}
