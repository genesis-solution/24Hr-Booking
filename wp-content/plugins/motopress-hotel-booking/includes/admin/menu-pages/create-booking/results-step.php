<?php

namespace MPHB\Admin\MenuPages\CreateBooking;

/**
 * Second step.
 */
class ResultsStep extends Step {

	/**
	 * @var int
	 */
	protected $roomTypeId = 0;

	/**
	 * @var int
	 */
	protected $adults = -1;

	/**
	 * @var int
	 */
	protected $children = -1;

	/**
	 * [%Room type ID% => [title, rooms => [id, type_id, title, adults, children, price]]]
	 *
	 * @var array
	 */
	protected $rooms = array();

	public function __construct() {
		parent::__construct( 'results' );
	}

	public function setup() {
		parent::setup();

		/** @see \MPHB\Admin\MenuPages\CreateBooking\Step::render() */
		add_action( "mphb_cb_{$this->name}_after_start", array( $this, 'printWrapperHeader' ) );

		/** @see templates/create-booking/results/reserve-rooms.php */
		add_action( 'mphb_cb_reserve_rooms_form_before_submit_button', array( $this, 'printDateHiddenFields' ) );

		if ( $this->isValidStep ) {
			$rooms = MPHB()->getRoomRepository()->getAvailableRooms( $this->checkInDate, $this->checkOutDate, $this->roomTypeId, array( 'skip_buffer_rules' => false ) );
			$rooms = $this->filterRoomsByRates( $rooms );
			$rooms = $this->filterRoomsByCapacity( $rooms );
			$rooms = $this->filterRoomsByRules( $rooms );

			$this->rooms = $this->pullRoomsData( $rooms );
		}
	}

	private function filterRoomsByRates( $rooms ) {
		$rateSearchAtts = array(
			'check_in_date'  => $this->checkInDate,
			'check_out_date' => $this->checkOutDate,
		);

		foreach ( array_keys( $rooms ) as $roomTypeId ) {
			if ( ! MPHB()->getRateRepository()->isExistsForRoomType( $roomTypeId, $rateSearchAtts ) ) {
				unset( $rooms[ $roomTypeId ] );
			}
		}

		return $rooms;
	}

	private function filterRoomsByCapacity( $rooms ) {
		foreach ( array_keys( $rooms ) as $roomTypeId ) {
			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

			if ( is_null( $roomType ) || $roomType->getAdultsCapacity() < $this->adults || $roomType->getChildrenCapacity() < $this->children ) {
				unset( $rooms[ $roomTypeId ] );
			}
		}

		return $rooms;
	}

	private function filterRoomsByRules( $rooms ) {
		// Don't modify iterating array, use the new one to iterate
		foreach ( array_keys( $rooms ) as $roomTypeId ) {
			if ( ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $this->checkOutDate, $roomTypeId ) ) {
				unset( $rooms[ $roomTypeId ] );
				continue;
			}

			$unavailableRooms = MPHB()->getRulesChecker()->customRules()->getUnavailableRooms( $this->checkInDate, $this->checkOutDate, $roomTypeId );

			if ( ! empty( $unavailableRooms ) ) {
				$availableRooms       = array_diff( $rooms[ $roomTypeId ], $unavailableRooms );
				$rooms[ $roomTypeId ] = $availableRooms;
			}
		}

		return $rooms;
	}

	private function pullRoomsData( $rooms ) {
		$data = array();

		foreach ( $rooms as $roomTypeId => $roomIds ) {
			$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

			$data[ $roomTypeId ] = array(
				'title' => $roomType->getTitle(),
				'rooms' => array(),
				'url'   => get_permalink( $roomTypeId ),
			);

			foreach ( $roomIds as $roomId ) {
				$room = MPHB()->getRoomRepository()->findById( $roomId );

				$data[ $roomTypeId ]['rooms'][] = array(
					'id'       => $roomId,
					'type_id'  => $roomTypeId,
					'title'    => $room->getTitle(),
					'adults'   => $roomType->getAdultsCapacity(),
					'children' => $roomType->getChildrenCapacity(),
					'price'    => mphb_get_room_type_period_price( $this->checkInDate, $this->checkOutDate, $roomType ),
				);
			} // For each room
		} // For each type

		return $data;
	}

	protected function renderValid() {
		$dateFormat   = MPHB()->settings()->dateTime()->getDateTransferFormat();
		$checkInDate  = $this->checkInDate->format( $dateFormat );
		$checkOutDate = $this->checkOutDate->format( $dateFormat );
		$roomsCount   = count( $this->rooms );

		mphb_get_template_part(
			'create-booking/results/rooms-found',
			array(
				'foundRooms'   => $roomsCount,
				'checkInDate'  => \MPHB\Utils\DateUtils::formatDateWPFront( $this->checkInDate ),
				'checkOutDate' => \MPHB\Utils\DateUtils::formatDateWPFront( $this->checkOutDate ),
			)
		);

		if ( $roomsCount > 0 ) {
			mphb_get_template_part(
				'create-booking/results/reserve-rooms',
				array(
					'actionUrl'    => $this->nextUrl,
					'checkInDate'  => $checkInDate,
					'checkOutDate' => $checkOutDate,
					'roomsList'    => $this->rooms,
				)
			);
		}
	}

	public function printWrapperHeader() {
		echo '<h2>' . esc_html__( 'Search Results', 'motopress-hotel-booking' ) . '</h2>';
	}

	protected function parseFields() {
		$this->checkInDate  = $this->parseCheckInDate( INPUT_GET );
		$this->checkOutDate = $this->parseCheckOutDate( INPUT_GET );
		$this->roomTypeId   = $this->parseRoomTypeId( INPUT_GET );
		$this->adults       = $this->parseAdults( INPUT_GET );
		$this->children     = $this->parseChildren( INPUT_GET );
	}
}
