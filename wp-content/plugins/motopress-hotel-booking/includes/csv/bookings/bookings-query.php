<?php

namespace MPHB\CSV\Bookings;

use MPHB\Utils\DateUtils;
use MPHB\Utils\ValidateUtils;

class BookingsQuery {

	/**
	 * @var array
	 */
	protected $inputArgs = array();

	/**
	 * @var \WP_Error|array
	 */
	protected $queryArgs = array();

	/**
	 * @var int[]
	 */
	protected $foundIds = array();

	/**
	 * @param array $args Use mphb_clean() before passing arguments here.
	 */
	public function __construct( $args ) {
		$this->inputArgs = $this->validate( $args );
		$this->queryArgs = $this->parseQuery( $this->inputArgs );
	}

	/**
	 * @param array $args
	 * @return array
	 */
	protected function validate( $args ) {

		$validArgs = array();

		if ( isset( $args['room'] ) ) {
			$validArgs['room'] = ValidateUtils::parseInt( $args['room'], -1 );
		}

		if ( isset( $args['start_date'] ) ) {
			$validArgs['start_date'] = DateUtils::convertDateFormat( $args['start_date'], MPHB()->settings()->dateTime()->getDateFormat(), 'Y-m-d' );
		}

		if ( isset( $args['end_date'] ) ) {
			$validArgs['end_date'] = DateUtils::convertDateFormat( $args['end_date'], MPHB()->settings()->dateTime()->getDateFormat(), 'Y-m-d' );
		}

		if ( isset( $args['columns'] ) ) {
			$validArgs['columns'] = is_array( $args['columns'] ) ? $args['columns'] : array();
		}

		// Copy other arguments without changes
		foreach ( array( 'status', 'search_by' ) as $name ) {
			if ( isset( $args[ $name ] ) ) {
				$validArgs[ $name ] = $args[ $name ];
			}
		}

		return $validArgs;
	}

	/**
	 * @param array $args
	 * @return \WP_Error|array
	 *
	 * @since 3.7.0 added new filter - "mphb_export_bookings_start_date_meta_query".
	 * @since 3.7.0 added new filter - "mphb_export_bookings_end_date_meta_query".
	 * @since 3.7.0 added new filter - "mphb_export_bookings_query_args".
	 */
	protected function parseQuery( $args ) {
		// Check all required fields
		$requiredFields = array( 'room', 'status', 'start_date', 'end_date', 'search_by', 'columns' );
		$missingFields  = array_diff( $requiredFields, array_keys( $args ) );

		if ( count( $missingFields ) > 0 ) {
			return new \WP_Error( 'not-enough-data', __( 'Please complete all required fields and try again.', 'motopress-hotel-booking' ), array( 'missing-fields' => $missingFields ) );
		}

		// Check columns
		$columns = $args['columns'];

		if ( empty( $columns ) ) {
			return new \WP_Error( 'not-enough-data', __( 'Please select columns to export.', 'motopress-hotel-booking' ), array( 'missing-fields' => array( 'columns' ) ) );
		}

		// Build query args
		$queryArgs = array(
			'orderby' => 'ID',
			'order'   => 'ASC',
		);

		// 1. Add status to query
		$status = $args['status'];

		if ( in_array( $status, array_keys( MPHB()->postTypes()->booking()->statuses()->getStatuses() ) ) ) {
			$queryArgs['post_status'] = $status;
		}

		// 2.1. Add dates query if $searchBy = "booking-date"
		$searchBy  = $args['search_by'];
		$startDate = $args['start_date'];
		$endDate   = $args['end_date'];

		if ( $searchBy == 'booking-date' ) {
			$period = array();

			if ( ! empty( $startDate ) ) {
				$date = \DateTime::createFromFormat( 'Y-m-d', $startDate );
				$date->modify( '-1 day' );

				$period['after'] = $date->format( 'Y-m-d' );
			}

			if ( ! empty( $endDate ) ) {
				$date = \DateTime::createFromFormat( 'Y-m-d', $endDate );
				$date->modify( '+1 day' );

				$period['before'] = $date->format( 'Y-m-d' );
			}

			if ( ! empty( $period ) ) {
				$queryArgs['date_query'] = array( $period );
			}
		}

		// 2.2. Add meta query for dates if $searchBy in "reserved-rooms",
		// "check-in", "check-out" or "in-house"
		$metaQuery = array();

		if ( ! empty( $startDate ) ) {
			switch ( $searchBy ) {
				case 'reserved-rooms':
					$metaQuery[] = array(
						'key'     => 'mphb_check_in_date',
						'value'   => $startDate,
						'compare' => '>=',
					);
					break;
				case 'check-in':
					$metaQuery[] = array(
						'key'     => 'mphb_check_in_date',
						'value'   => $startDate,
						'compare' => '>=',
					);
					break;
				case 'check-out':
					$metaQuery[] = array(
						'key'     => 'mphb_check_out_date',
						'value'   => $startDate,
						'compare' => '>=',
					);
					break;
				case 'in-house':
					$metaQuery[] = array(
						'key'     => 'mphb_check_out_date',
						'value'   => $startDate,
						'compare' => '>=',
					);
					break;
				case 'booking-date':
					break;
				default:
					$metaQuery = apply_filters( 'mphb_export_bookings_start_date_meta_query', $metaQuery, $startDate, $searchBy );
					break;
			}
		}

		if ( ! empty( $endDate ) ) {
			switch ( $searchBy ) {
				case 'reserved-rooms':
					$metaQuery[] = array(
						'key'     => 'mphb_check_out_date',
						'value'   => $endDate,
						'compare' => '<=',
					);
					break;
				case 'check-in':
					$metaQuery[] = array(
						'key'     => 'mphb_check_in_date',
						'value'   => $endDate,
						'compare' => '<=',
					);
					break;
				case 'check-out':
					$metaQuery[] = array(
						'key'     => 'mphb_check_out_date',
						'value'   => $endDate,
						'compare' => '<=',
					);
					break;
				case 'in-house':
					$metaQuery[] = array(
						'key'     => 'mphb_check_in_date',
						'value'   => $endDate,
						'compare' => '<=',
					);
					break;
				case 'booking-date':
					break;
				default:
					$metaQuery = apply_filters( 'mphb_export_bookings_end_date_meta_query', $metaQuery, $endDate, $searchBy );
					break;
			}
		}

		if ( ! empty( $metaQuery ) ) {
			$queryArgs['meta_query'] = $metaQuery;
		}

		// 3. Filter imported bookings
		$filterQuery = array(
			'relation' => 'OR',
			array(
				'key'     => '_mphb_sync_id',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => '_mphb_sync_id',
				'value' => '',
			),
		);

		if ( ! empty( $metaQuery ) ) {
			$queryArgs['meta_query'][] = $filterQuery;
		} else {
			$queryArgs['meta_query'] = $filterQuery;
		}

		// 4. All done
		return apply_filters( 'mphb_export_bookings_query_args', $queryArgs );
	}

	/**
	 * @return self
	 */
	public function query() {
		$this->foundIds = array();

		if ( ! is_wp_error( $this->queryArgs ) ) {
			$this->foundIds = MPHB()->getBookingPersistence()->getPosts( $this->queryArgs );
			$this->foundIds = array_map( 'intval', $this->foundIds );
		}

		return $this;
	}

	/**
	 * @param int $roomTypeId
	 * @return self
	 *
	 * @global \wpdb $wpdb
	 */
	public function filterByRoomType( $roomTypeId ) {
		global $wpdb;

		if ( $roomTypeId == -1 || empty( $this->foundIds ) ) {
			return $this;
		}

		$roomTypeId = MPHB()->translation()->getOriginalId( $roomTypeId, MPHB()->postTypes()->roomType()->getPostType() );

		$filterQuery = $wpdb->prepare(
			'SELECT DISTINCT reserved_rooms.post_parent AS ID'
				. " FROM {$wpdb->posts} AS reserved_rooms"
				. " INNER JOIN {$wpdb->postmeta} AS reserved_rooms_meta ON reserved_rooms.ID = reserved_rooms_meta.post_id AND reserved_rooms_meta.meta_key = '_mphb_room_id'"
				. " INNER JOIN {$wpdb->postmeta} AS rooms_meta ON reserved_rooms_meta.meta_value = rooms_meta.post_id AND rooms_meta.meta_key = 'mphb_room_type_id' AND rooms_meta.meta_value = %d"
				. " WHERE reserved_rooms.post_type = '%s'"
				. ' AND reserved_rooms.post_parent IN (' . implode( ',', $this->foundIds ) . ')',
			$roomTypeId,
			MPHB()->postTypes()->reservedRoom()->getPostType()
		);

		$this->foundIds = $wpdb->get_col( $filterQuery );
		$this->foundIds = array_map( 'intval', $this->foundIds );

		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getIds() {
		return $this->foundIds;
	}

	/**
	 * @return array Input arguments: "status", "start_date" etc.
	 */
	public function getInputs() {
		return $this->inputArgs;
	}

	/**
	 * @return boolean
	 */
	public function hasErrors() {
		return is_wp_error( $this->queryArgs );
	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		if ( is_wp_error( $this->queryArgs ) ) {
			return $this->queryArgs->get_error_message();
		}

		return '';
	}
}
