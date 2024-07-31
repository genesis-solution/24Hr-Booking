<?php

namespace MPHB\Admin\MenuPages\CreateBooking;

/**
 * First step.
 */
class SearchStep extends Step {

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

	public function __construct() {
		parent::__construct( 'search' );
	}

	public function setup() {
		parent::setup();

		// All parsed fields are optional
		$this->isValidStep = true;

		/** @see templates/create-booking/search/search-form.php */
		add_action( 'mphb_cb_search_form_after_start', array( $this, 'printQueryHiddenFields' ) );
		add_action( 'mphb_cb_search_form_before_submit_button', array( $this, 'printDateHiddenFields' ) );
	}

	protected function renderValid() {
		$dateFormat   = MPHB()->settings()->dateTime()->getDateFormat();
		$checkInDate  = ! is_null( $this->checkInDate ) ? $this->checkInDate->format( $dateFormat ) : '';
		$checkOutDate = ! is_null( $this->checkOutDate ) ? $this->checkOutDate->format( $dateFormat ) : '';

		// array_replace() will preserve all keys, when array_merge() will reset
		// the keys and set 0 to "- Any -", 1 to "0" etc.
		$emptyList    = array( -1 => __( '— Any —', 'motopress-hotel-booking' ) );
		$adultsList   = array_replace( $emptyList, MPHB()->settings()->main()->getAdultsListForSearch() );
		$childrenList = array_replace( $emptyList, MPHB()->settings()->main()->getChildrenListForSearch() );
		$roomsList    = MPHB()->getRoomTypePersistence()->getIdTitleList( array(), array( 0 => __( '— Any —', 'motopress-hotel-booking' ) ) );

		mphb_get_template_part( 'required-fields-tip' );

		mphb_get_template_part(
			'create-booking/search/search-form',
			array(
				'id'           => $this->id,
				'actionUrl'    => $this->nextUrl,
				'checkInDate'  => $checkInDate,
				'checkOutDate' => $checkOutDate,
				'roomTypeId'   => $this->roomTypeId,
				'adults'       => $this->adults,
				'children'     => $this->children,
				'adultsList'   => $adultsList,
				'childrenList' => $childrenList,
				'roomsList'    => $roomsList,
			)
		);
	}

	protected function parseFields() {
		$this->checkInDate  = $this->parseCheckInDate( INPUT_GET );
		$this->checkOutDate = $this->parseCheckOutDate( INPUT_GET );
		$this->roomTypeId   = $this->parseRoomTypeId( INPUT_GET );
		$this->adults       = $this->parseAdults( INPUT_GET );
		$this->children     = $this->parseChildren( INPUT_GET );
	}

}
