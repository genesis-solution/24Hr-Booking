<?php

namespace MPHB\Admin\MenuPages\CreateBooking;

abstract class Step {

	protected $name    = 'abstract';
	protected $id      = '';
	protected $nextUrl = '';

	/**
	 * @var \DateTime|null
	 */
	protected $checkInDate = null;

	/**
	 * @var \DateTime|null
	 */
	protected $checkOutDate = null;

	/**
	 * @var bool
	 */
	protected $isValidStep = false;

	/**
	 * @var array
	 */
	protected $parseErrors = array();

	public function __construct( $name ) {
		$this->name          = $name;
		$this->id            = uniqid( "mphb-cb-{$name}-" );
		$this->parseErrors[] = __( 'Search parameters are not set.', 'motopress-hotel-booking' );
	}

	public function setup() {
		$this->parseInput();
		$this->isValidStep = empty( $this->parseErrors );
	}

	public function render() {
		$wrapperClass = apply_filters( "mphb_cb_{$this->name}_wrapper_class", "mphb_cb_{$this->name}-wrapper" );

		/** @hooked None */
		do_action( "mphb_cb_{$this->name}_before_start" );

		echo '<div class="' . esc_attr( $wrapperClass ) . '">';

			/**
			 * @hooked \MPHB\Admin\MenuPages\CreateBooking\ResultsStep::printWrapperHeader - 10
			 */
			do_action( "mphb_cb_{$this->name}_after_start" );

		if ( $this->isValidStep ) {
			$this->renderValid();
		} else {
			$this->renderInvalid();
		}

			/** @hooked None */
			do_action( "mphb_cb_{$this->name}_before_end" );

		echo '</div>';

		/** @hooked None */
		do_action( "mphb_cb_{$this->name}_after_end" );
	}

	abstract protected function renderValid();

	protected function renderInvalid() {
		mphb_get_template_part(
			'create-booking/errors',
			array(
				'errors' => $this->parseErrors,
			)
		);
	}

	public function printQueryHiddenFields() {
		$queryArgs = mphb_get_query_args( $this->nextUrl );

		foreach ( $queryArgs as $name => $value ) {
			printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
		}
	}

	public function printDateHiddenFields() {
		$dateFormat   = MPHB()->settings()->dateTime()->getDateTransferFormat();
		$checkInDate  = ! is_null( $this->checkInDate ) ? $this->checkInDate->format( $dateFormat ) : '';
		$checkOutDate = ! is_null( $this->checkOutDate ) ? $this->checkOutDate->format( $dateFormat ) : '';

		echo '<input id="' . esc_attr( 'mphb_check_in_date-' . $this->id . '-hidden' ) . '" type="hidden" name="mphb_check_in_date" value="' . esc_attr( $checkInDate ) . '" />';
		echo '<input id="' . esc_attr( 'mphb_check_out_date-' . $this->id . '-hidden' ) . '" type="hidden" name="mphb_check_out_date" value="' . esc_attr( $checkOutDate ) . '" />';
	}

	protected function parseInput() {
		$this->parseErrors = array();
		$this->parseFields();
	}

	abstract protected function parseFields();

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return \DateTime|null
	 */
	protected function parseCheckInDate( $input ) {
		/**
		 * @var string|false|null
		 */
		$checkInInput = filter_input( $input, 'mphb_check_in_date' );

		if ( ! empty( $checkInInput ) ) {

			$dateFormat   = MPHB()->settings()->dateTime()->getDateTransferFormat();
			$checkInDate  = \DateTime::createFromFormat( $dateFormat, $checkInInput );
			$today        = \DateTime::createFromFormat( $dateFormat, mphb_current_time( $dateFormat ) );

			if ( ! $checkInDate ) {
				$this->parseError( __( 'Check-in date is not valid.', 'motopress-hotel-booking' ) );
			} elseif ( \MPHB\Utils\DateUtils::calcNights( $today, $checkInDate ) < 0 ) {
				$this->parseError( __( 'Check-in date cannot be earlier than today.', 'motopress-hotel-booking' ) );
			} else {
				return $checkInDate;
			}
		}

		return null;
	}

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return \DateTime|null
	 */
	protected function parseCheckOutDate( $input ) {
		/**
		 * @var string|false|null
		 */
		$checkOutInput = filter_input( $input, 'mphb_check_out_date' );

		if ( ! empty( $checkOutInput ) ) {

			$checkOutDate  = \MPHB\Utils\DateUtils::createCheckOutDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $checkOutInput );

			if ( ! $checkOutDate ) {
				$this->parseError( __( 'Check-out date is not valid.', 'motopress-hotel-booking' ) );
			} elseif ( ! is_null( $this->checkInDate ) && ! MPHB()->getRulesChecker()->verify( $this->checkInDate, $checkOutDate ) ) {
				$this->parseError( __( 'Nothing found. Please try again with different search parameters.', 'motopress-hotel-booking' ) );
			} else {
				return $checkOutDate;
			}
		}

		return null;
	}

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return int
	 */
	protected function parseRoomTypeId( $input ) {
		/**
		 * @var string|false|null
		 */
		$roomTypeId = filter_input( $input, 'mphb_room_type_id', FILTER_VALIDATE_INT, array( 'min_range' => 0 ) );

		if ( is_null( $roomTypeId ) ) {
			$this->parseError( __( 'Accommodation Type is not valid.', 'motopress-hotel-booking' ) );
		}

		return (int) $roomTypeId; // false|null -> 0
	}

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return int
	 */
	protected function parseAdults( $input ) {
		/**
		 * @var string|false|null
		 */
		$adults    = filter_input( $input, 'mphb_adults', FILTER_VALIDATE_INT, array( 'min_range' => -1 ) );
		$minAdults = MPHB()->settings()->main()->getMinAdults();
		$maxAdults = MPHB()->settings()->main()->getSearchMaxAdults();

		if ( is_null( $adults ) || $adults === false ) {
			$this->parseError( __( 'Adults number is not valid.', 'motopress-hotel-booking' ) );
		} elseif ( $adults == -1 || $adults >= $minAdults && $adults <= $maxAdults ) {
			return $adults;
		} else {
			$this->parseError( __( 'Adults number is not valid.', 'motopress-hotel-booking' ) );
		}

		return -1;
	}

	/**
	 * @param int $input INPUT_POST (0) or INPUT_GET (1)
	 *
	 * @return int
	 */
	protected function parseChildren( $input ) {
		/**
		 * @var string|false|null
		 */
		$children    = filter_input( $input, 'mphb_children', FILTER_VALIDATE_INT, array( 'min_range' => -1 ) );
		$minChildren = MPHB()->settings()->main()->getMinChildren();
		$maxChildren = MPHB()->settings()->main()->getSearchMaxChildren();

		if ( is_null( $children ) || $children === false ) {
			$this->parseError( __( 'Children number is not valid.', 'motopress-hotel-booking' ) );
		} elseif ( $children == -1 || $children >= $minChildren && $children <= $maxChildren ) {
			return $children;
		} else {
			$this->parseError( __( 'Children number is not valid.', 'motopress-hotel-booking' ) );
		}

		return -1;
	}

	protected function parseError( $message ) {
		$this->parseErrors[] = $message;
	}

	public function setNextUrl( $url ) {
		$this->nextUrl = $url;
	}

	public function isValidStep() {
		return $this->isValidStep;
	}

}
