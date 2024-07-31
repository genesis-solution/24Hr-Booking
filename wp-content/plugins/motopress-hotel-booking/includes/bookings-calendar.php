<?php

namespace MPHB;

use \MPHB\Entities;

class BookingsCalendar {

	const ALL_ROOM_TYPES      = '0';
	const PERIOD_TYPE_MONTH   = 'month';
	const PERIOD_TYPE_QUARTER = 'quarter';
	const PERIOD_TYPE_YEAR    = 'year';
	const PERIOD_TYPE_CUSTOM  = 'custom';
	const ATTS_FIELD_NAME     = 'mphb_bookings_calendar';

	/**
	 *
	 * @var string
	 */
	private $periodType;

	/**
	 *
	 * @var int
	 */
	private $periodPage;

	/**
	 *
	 * @var \DateTime
	 */
	private $customPeriodFrom;

	/**
	 *
	 * @var \DateTime
	 */
	private $customPeriodTo;

	/**
	 *
	 * @var \DatePeriod
	 */
	private $period;

	/**
	 *
	 * @var array
	 */
	private $periodArr;

	/**
	 *
	 * @var \DateTime
	 */
	private $periodStartDate;

	/**
	 *
	 * @var \DateTime
	 */
	private $periodEndDate;

	/**
	 *
	 * @var string
	 */
	private $roomTypeId;

	/**
	 *
	 * @var \WP_POST[]
	 */
	private $roomPosts = array();

	/**
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 *
	 * @var string
	 */
	private $searchRoomAvailabilityStatus;

	/**
	 *
	 * @var \DateTime
	 */
	private $searchDateFrom;

	/**
	 *
	 * @var \DateTime
	 */
	private $searchDateTo;

	/**
	 *
	 * @var bool
	 */
	private $isUseSearch = false;

	/**
	 *
	 * @param array     $atts
	 * @param int       $atts['room_type_id'] Which room type show. 0 for all room types.
	 * @param string    $atts['period_type'] Period to show. Possible values: month, quarter, year, custom.
	 * @param \DateTime $atts['custom_period_from'] First date of custom period. Need period_type set to custom.
	 * @param \DateTime $atts['custom_period_to'] Last date of custom period. Need period_type set to custom.
	 */
	public function __construct( $atts = array() ) {
		$defaultAtts = array(
			'room_type_id'                    => self::ALL_ROOM_TYPES,
			'period_type'                     => self::PERIOD_TYPE_MONTH,
			'period_page'                     => 0,
			'custom_period_from'              => new \DateTime( date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) ) ),
			'custom_period_to'                => new \DateTime( '+14 days' ),
			'search_date_from'                => null,
			'search_date_to'                  => null,
			'search_room_availability_status' => '',
		);

		$atts = array_merge( $defaultAtts, $atts );
		$atts = $this->parseFiltersAtts( $atts );

		$this->roomTypeId = absint( $atts['room_type_id'] );

		$this->periodType = $atts['period_type'];
		$this->periodPage = $atts['period_page'];
		if ( $this->periodType === self::PERIOD_TYPE_CUSTOM ) {
			$this->customPeriodFrom = $atts['custom_period_from'];
			$this->customPeriodTo   = $atts['custom_period_to'];
		}

		$this->searchRoomAvailabilityStatus = $atts['search_room_availability_status'];
		$this->searchDateFrom               = $atts['search_date_from'];
		$this->searchDateTo                 = $atts['search_date_to'];

		$this->isUseSearch = ! empty( $this->searchRoomAvailabilityStatus ) &&
			! is_null( $this->searchDateFrom ) &&
			! is_null( $this->searchDateTo );

		$this->setupPeriod();
		$this->setupRooms();
		$this->setupData();
		$this->setupBlocks();
	}

	private function setupRooms() {

		$roomAtts = array(
			'fields'         => 'all',
			'posts_per_page' => -1,
		);

		if ( $this->isUseSearch ) {
			$searchAtts  = array(
				'availability' => $this->searchRoomAvailabilityStatus,
				'from_date'    => $this->searchDateFrom,
				'to_date'      => $this->searchDateTo,
			);
			$findedRooms = MPHB()->getRoomPersistence()->searchRooms( $searchAtts );

			if ( empty( $findedRooms ) ) {
				$this->roomPosts = array();
				return;
			}

			$roomAtts['post__in'] = $findedRooms;
		}

		if ( $this->roomTypeId != self::ALL_ROOM_TYPES ) {
			$roomAtts['room_type_id'] = $this->roomTypeId;
		} else {
			$roomAtts['room_type_id'] = MPHB()->getRoomTypePersistence()->getPosts();
		}

		$this->roomPosts = MPHB()->getRoomPersistence()->getPosts( $roomAtts );
	}

	private function setupData() {
		$data = array();

		$requestedRoomIds = wp_list_pluck( $this->roomPosts, 'ID' );

		$atts = array(
			'fields'              => 'all',
			'room_locked'         => true,
			'date_from'           => $this->periodStartDate->format( 'Y-m-d' ),
			'date_to'             => $this->periodEndDate->format( 'Y-m-d' ),
			'period_edge_overlap' => true,
			'rooms'               => $requestedRoomIds,
		);

		$bookings = MPHB()->getBookingRepository()->findAll( $atts );

		foreach ( $bookings as $booking ) {

			$reservedRooms = $booking->getReservedRooms();

			if ( empty( $reservedRooms ) ) {
				continue;
			}

			$customer = $booking->getCustomer();

			$bookingDetails = array(
				'customer' => $customer->getName(),
				'email'    => $customer->getEmail(),
				'phone'    => $customer->getPhone(),
			);

			foreach ( $reservedRooms as $reservedRoom ) {
				$roomId = $reservedRoom->getRoomId();

				if ( ! in_array( $roomId, $requestedRoomIds ) ) {
					continue;
				}

				if ( ! array_key_exists( $roomId, $data ) ) {
					$data[ $roomId ] = array();
				}

				$bookingDetails['guest_name'] = $reservedRoom->getGuestName();
				$bookingDetails['adults']     = $reservedRoom->getAdults();
				$bookingDetails['children']   = $reservedRoom->getChildren();
				$bookingDetails['ical']       = array();

				if ( $booking->isImported() ) {
					$bookingDetails['ical'] = array(
						'uid'         => $reservedRoom->getUid(),
						'summary'     => $booking->getICalSummary(),
						'description' => $booking->getICalDescription(),
						'prodid'      => $booking->getICalProdid(),
					);
				}

				// Add check-in and middle dates
				$checkInDateYmd = $booking->getCheckInDate()->format( 'Y-m-d' );

				foreach ( $booking->getDates() as $ymdDate => $date ) {
					if ( ! isset( $data[ $roomId ][ $ymdDate ] ) ) {
						$data[ $roomId ][ $ymdDate ] = array();
					}

					$roomDateDetails = array(
						'is_locked'         => true,
						'is_check_in'       => $ymdDate === $checkInDateYmd,
						'booking_status'    => $booking->getStatus(),
						'booking_id'        => $booking->getId(),
						'booking_edit_link' => get_edit_post_link( $booking->getId() ),
						'booking_details'   => $bookingDetails,
					);

					$data[ $roomId ][ $ymdDate ] = array_merge( $data[ $roomId ][ $ymdDate ], $roomDateDetails );
				}

				// Add day of check-out
				$checkOutDateYmd = $booking->getCheckOutDate()->format( 'Y-m-d' );

				if ( ! isset( $data[ $roomId ][ $checkOutDateYmd ] ) ) {
					$data[ $roomId ][ $checkOutDateYmd ] = array();
				}

				$data[ $roomId ][ $checkOutDateYmd ] = array_merge(
					$data[ $roomId ][ $checkOutDateYmd ],
					array(
						'is_check_out'              => true,
						'check_out_booking_id'      => $booking->getId(),
						'check_out_booking_status'  => $booking->getStatus(),
						'check_out_booking_details' => $bookingDetails,
					)
				);

					// Add buffer days
					$roomTypeId = intval( get_post_meta( $roomId, 'mphb_room_type_id', true ) );
					$bufferDays = mphb_get_buffer_days( $booking->getCheckInDate(), $roomTypeId );

				if ( $bufferDays > 0 ) {
					$bufferDates = array_keys( mphb_modify_booking_buffer_period( $booking, $bufferDays, true ) );

					$firstDateYmd = reset( $bufferDates );
					$lastDateYmd  = end( $bufferDates );

					foreach ( $bufferDates as $bufferDateYmd ) {
						if ( ! isset( $data[ $roomId ][ $bufferDateYmd ] ) ) {
							$data[ $roomId ][ $bufferDateYmd ] = array(
								'is_buffer_first'  => false,
								'is_buffer_second' => false,
							);
						}

						$data[ $roomId ][ $bufferDateYmd ]['is_buffer'] = true;

						if ( $bufferDateYmd != $firstDateYmd && $bufferDateYmd != $checkOutDateYmd ) {
							$data[ $roomId ][ $bufferDateYmd ]['is_buffer_first'] = true;
						}

						if ( $bufferDateYmd != $lastDateYmd && $bufferDateYmd != $checkInDateYmd ) {
							$data[ $roomId ][ $bufferDateYmd ]['is_buffer_second'] = true;
						}
					}
				}
			}
		}

		$this->data = $data;
	}

	private function setupBlocks() {
		$rooms = array();

		foreach ( $this->roomPosts as $post ) {
			$typeId = get_post_meta( $post->ID, 'mphb_room_type_id', true );
			$typeId = is_numeric( $typeId ) ? (int) $typeId : 0;

			if ( ! isset( $rooms[ $typeId ] ) ) {
				$rooms[ $typeId ] = array();
			}

			$rooms[ $typeId ][] = $post->ID;
		}

		$data = $this->data;

		$customRules = MPHB()->getRulesChecker()->customRules();

		foreach ( $rooms as $typeId => $roomIds ) {
			$roomDates = $customRules->getCommentsByDates( $typeId, $roomIds );

			foreach ( $roomDates as $roomId => $dates ) {
				foreach ( $dates as $dateYmd => $comments ) {
					if ( ! isset( $data[ $roomId ][ $dateYmd ] ) ) {
						$data[ $roomId ][ $dateYmd ] = array();
					}

					$data[ $roomId ][ $dateYmd ] = array_merge(
						$data[ $roomId ][ $dateYmd ],
						array(
							'is_blocked' => true,
							'comments'   => $comments,
						)
					);
				}
			}
		}

		$this->data = $data;
	}

	/**
	 *
	 * @param int       $roomId
	 * @param \DateTime $date
	 * @return array
	 */
	private function getRoomDateDetails( $roomId, $date ) {

		$details = array();

		$dateFormatted = $date->format( 'Y-m-d' );
		$details       = array(
			'is_locked'        => false,
			'is_check_out'     => false,
			'is_check_in'      => false,
			'is_blocked'       => false,
			'is_buffer'        => false,
			'is_buffer_first'  => false,
			'is_buffer_second' => false,
		);

		if ( isset( $this->data[ $roomId ] ) && isset( $this->data[ $roomId ][ $dateFormatted ] ) ) {
			$details = array_merge( $details, $this->data[ $roomId ][ $dateFormatted ] );
		}

		return $details;
	}

	private function setupPeriod() {
		switch ( $this->periodType ) {
			case self::PERIOD_TYPE_QUARTER:
				$this->period = Utils\DateUtils::createQuarterPeriod( $this->periodPage );
				break;
			case self::PERIOD_TYPE_YEAR:
				$curYear      = date( 'Y', current_time( 'timestamp' ) );
				$year         = $curYear + $this->periodPage;
				$firstDay     = new \DateTime( 'first day of January ' . $year );
				$lastDay      = new \DateTime( 'last day of December ' . $year );
				$this->period = Utils\DateUtils::createDatePeriod( $firstDay, $lastDay, true );
				break;
			case self::PERIOD_TYPE_CUSTOM:
				$firstDay     = $this->customPeriodFrom;
				$lastDay      = $this->customPeriodTo;
				$this->period = Utils\DateUtils::createDatePeriod( $firstDay, $lastDay, true );
				break;
			case self::PERIOD_TYPE_MONTH: // default period
			default:
				$baseFirstDay = new \DateTime( 'first day of this month' );
				$relationSign = $this->periodPage < 0 ? '-' : '+';
				$firstDay     = clone $baseFirstDay;
				$firstDay->modify( $relationSign . absint( $this->periodPage ) . ' month' );
				$lastDay = clone $firstDay;
				$lastDay->modify( 'last day of this month' );
				$this->period = Utils\DateUtils::createDatePeriod( $firstDay, $lastDay, true );
				break;
		}

		$this->periodArr = iterator_to_array( $this->period );

		$this->periodEndDate   = end( $this->periodArr );
		$this->periodStartDate = reset( $this->periodArr );
	}

	/**
	 *
	 * @param array $defaults
	 * @return array
	 */
	private function parseFiltersAtts( $defaults = array() ) {

		$atts = $defaults;

		if ( isset( $_GET[ self::ATTS_FIELD_NAME ] ) ) {

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$filtersQuery = $_GET[ self::ATTS_FIELD_NAME ];

			if ( isset( $filtersQuery['room_type_id'] ) ) {
				$atts['room_type_id'] = absint( $filtersQuery['room_type_id'] );
			}

			if ( isset( $filtersQuery['period'] ) && array_key_exists( $filtersQuery['period'], $this->getPeriodsList() ) ) {
				$atts['period_type'] = $filtersQuery['period'];

				if ( $atts['period_type'] === self::PERIOD_TYPE_CUSTOM ) {
					if ( isset( $filtersQuery['custom_period'] ) && isset( $filtersQuery['custom_period']['date_from'] ) ) {
						$customPeriodFrom = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateFormat(), $filtersQuery['custom_period']['date_from'] );

						$atts['custom_period_from'] = $customPeriodFrom ? $customPeriodFrom : $atts['custom_period_from'];
					}
					if ( isset( $filtersQuery['custom_period'] ) && isset( $filtersQuery['custom_period']['date_to'] ) ) {

						$customPeriodTo = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateFormat(), $filtersQuery['custom_period']['date_to'] );

						$atts['custom_period_to'] = $customPeriodTo ? $customPeriodTo : $atts['custom_period_to'];
						if ( $atts['custom_period_from']->format( 'Y-m-d' ) > $atts['custom_period_to']->format( 'Y-m-d' ) ) {
							$atts['custom_period_to'] = $atts['custom_period_from'];
						}
					}
				} elseif ( isset( $filtersQuery[ 'period_page_' . $atts['period_type'] ] ) ) {
					$atts['period_page'] = intval( $filtersQuery[ 'period_page_' . $atts['period_type'] ] );
				}
			}

			// Period modificators
			if ( isset( $filtersQuery['action_period_next'] ) ) {
				$atts['period_page'] ++;
			}
			if ( isset( $filtersQuery['action_period_prev'] ) ) {
				$atts['period_page'] --;
			}

			if ( isset( $filtersQuery['action_search'] ) ) {
				$atts['search_date_from'] = is_null( $atts['search_date_from'] ) ? new \DateTime( current_time( 'mysql' ) ) : $atts['search_date_from'];
				$atts['search_date_to']   = is_null( $atts['search_date_to'] ) ? new \DateTime( current_time( 'mysql' ) ) : $atts['search_date_to'];
			}

			if ( isset( $filtersQuery['search_room_availability_status'] ) &&
				! empty( $filtersQuery['search_room_availability_status'] ) &&
				array_key_exists( $filtersQuery['search_room_availability_status'], $this->getSearchRoomAvailabilityStatuses() )
			) {
				$atts['search_room_availability_status'] = $filtersQuery['search_room_availability_status'];
			}

			if ( isset( $filtersQuery['search_date_from'] ) && ! empty( $filtersQuery['search_date_from'] ) ) {
				$searchDateFrom           = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateFormat(), $filtersQuery['search_date_from'] );
				$atts['search_date_from'] = $searchDateFrom ? $searchDateFrom : $atts['search_date_from'];
			}

			if ( isset( $filtersQuery['search_date_to'] ) && ! empty( $filtersQuery['search_date_to'] ) ) {
				$searchDateTo           = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateFormat(), $filtersQuery['search_date_to'] );
				$atts['search_date_to'] = $searchDateTo ? $searchDateTo : $atts['search_date_to'];

				// do not allow search "to" date be earlier than "from" date
				if ( ! is_null( $atts['search_date_to'] ) &&
					! is_null( $atts['search_date_from'] ) &&
					$atts['search_date_to']->format( 'Ymd' ) < $atts['search_date_from']->format( 'Ymd' )
				) {
					$atts['search_date_to'] = $atts['search_date_from'];
				}
			}
		}

		return $atts;
	}

	public static function getPeriodsList() {
		return array(
			self::PERIOD_TYPE_MONTH   => __( 'Month', 'motopress-hotel-booking' ),
			self::PERIOD_TYPE_QUARTER => __( 'Quarter', 'motopress-hotel-booking' ),
			self::PERIOD_TYPE_YEAR    => __( 'Year', 'motopress-hotel-booking' ),
			self::PERIOD_TYPE_CUSTOM  => __( 'Custom', 'motopress-hotel-booking' ),
		);
	}

	public function render() {
		MPHB()->getAdminScriptManager()->enqueue();

		$period = $this->periodType;
		if ( $period == self::PERIOD_TYPE_CUSTOM ) {
			$period .= '-period';
		}

		$accTypesCount = count( $this->roomPosts );
		$size          = ( $accTypesCount > 5 ) ? 'default' : 'large';

		?>
		<div class="mphb-bookings-calendar-wrapper">
			<?php $this->renderFilters(); ?>
			<div class="mphb-booking-calendar-tables-wrapper <?php echo esc_attr( "mphb-booking-calendar-{$period}-tables" ); ?> <?php echo esc_attr( "mphb-booking-calendar-size-{$size}" ); ?>">
				<?php $this->renderRoomsTable(); ?>
				<div class="mphb-bookings-calendar-holder">
					<?php $this->renderDatesTable(); ?>
				</div>
			</div>
			<div id="mphb-bookings-calendar-popup" class="mphb-popup mphb-hide">
				<div class="mphb-popup-backdrop"></div>
				<div class="mphb-popup-body">
					<div class="mphb-header">
						<h2 class="mphb-title mphb-inline"><?php esc_html_e( 'Booking #%s', 'motopress-hotel-booking' ); ?></h2>
						<span class="mphb-preloader mphb-hide"></span>
						<span class="mphb-status mphb-hide"><?php echo esc_html_x( 'Confirmed', 'Booking status', 'motopress-hotel-booking' ); ?></span>
						<button class="mphb-close-popup-button dashicons dashicons-no-alt"></button>
					</div>
					<div class="mphb-content"></div>
					<div class="mphb-footer">
						<a href="#" class="button button-primary mphb-edit-button"><?php esc_html_e( 'Edit', 'motopress-hotel-booking' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function renderFilters() {
		?>
		<div class="mphb-bookings-calendar-filters-wrapper">
			<?php
			if ( $this->isUseSearch ) {
				$this->renderSearchResultsLabel();
			}
			?>
			<form id="mphb-bookings-calendar-filters" method="get" class="wp-filter">
				<?php
				$parameters = array();

				if ( isset( $_GET['page'] ) ) {
					$parameters['page'] = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				}
				?>
				<div class="mphb-bookings-calendar-date alignleft">
					<?php
					foreach ( $parameters as $paramName => $paramValue ) {
						printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $paramName ), esc_attr( $paramValue ) );
					}
					?>
					<?php $this->renderRoomTypeSelect(); ?>
					<?php $this->renderPeriodFilter(); ?>
					<?php submit_button( __( 'Show', 'motopress-hotel-booking' ), 'button', self::ATTS_FIELD_NAME . '[action_filter]', false ); ?>
				</div>
				<div class="mphb-bookings-calendar-search alignleft">
					<?php $this->renderRoomSearch(); ?>
					<?php submit_button( __( 'Search', 'motopress-hotel-booking' ), 'button', self::ATTS_FIELD_NAME . '[action_search]', false ); ?>
				</div>
				<div class="mphb-bookings-calendar-legend alignright">
					<legend class="legend-item booked" title="<?php esc_attr_e( 'Booked', 'motopress-hotel-booking' ); ?>"><span>
																				<?php
																				esc_html_e( 'Booked', 'motopress-hotel-booking' );
																				?>
						</span></legend>
					<legend class="legend-item pending" title="<?php esc_attr_e( 'Pending', 'motopress-hotel-booking' ); ?>"><span>
																				 <?php
																					esc_html_e( 'Pending', 'motopress-hotel-booking' );
																					?>
						</span></legend>
					<legend class="legend-item external" title="<?php esc_attr_e( 'External', 'motopress-hotel-booking' ); ?>"><span>
																				  <?php
																					esc_html_e( 'External', 'motopress-hotel-booking' );
																					?>
						</span></legend>
					<legend class="legend-item blocked" title="<?php esc_attr_e( 'Blocked', 'motopress-hotel-booking' ); ?>"><span>
																				 <?php
																					esc_html_e( 'Blocked', 'motopress-hotel-booking' );
																					?>
						</span></legend>
				</div>
			</form>
		</div>
		<?php
	}

	private function renderSearchResultsLabel() {
		$availabilityStatuses = $this->getSearchRoomAvailabilityStatuses();

		$status   = $availabilityStatuses[ $this->searchRoomAvailabilityStatus ];
		$dateFrom = \MPHB\Utils\DateUtils::formatDateWPFront( $this->searchDateFrom );
		$dateTo   = \MPHB\Utils\DateUtils::formatDateWPFront( $this->searchDateTo );
		?>
		<h3 class="mphb-booking-calendar-search-description">
			<?php
			echo esc_html( sprintf( __( 'Search results for accommodations that have bookings with status "%s" from %s until %s', 'motopress-hotel-booking' ), $status, $dateFrom, $dateTo ) );
			?>
		</h3>
		<?php
	}

	private function getSearchRoomAvailabilityStatuses() {
		return array(
			''        => __( 'All Statuses', 'motopress-hotel-booking' ),
			'free'    => __( 'Free', 'motopress-hotel-booking' ),
			'booked'  => __( 'Booked', 'motopress-hotel-booking' ),
			'pending' => __( 'Pending', 'motopress-hotel-booking' ),
			'locked'  => __( 'Locked (Booked or Pending)', 'motopress-hotel-booking' ),
		);
	}

	private function renderRoomSearch() {
		$roomAvailabilityStatuses = $this->getSearchRoomAvailabilityStatuses();
		$datesFromToClass         = $this->searchRoomAvailabilityStatus === '' ? ' mphb-hide' : '';
		$dateFrom                 = ! is_null( $this->searchDateFrom ) ? $this->searchDateFrom->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '';
		$dateTo                   = ! is_null( $this->searchDateTo ) ? $this->searchDateTo->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '';
		?>
		<select name="<?php echo esc_attr( self::ATTS_FIELD_NAME ); ?>[search_room_availability_status]"
				id="mphb-booking-calendar-search-room-availability-status">

			<?php foreach ( $roomAvailabilityStatuses as $status => $statusLabel ) : ?>

				<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $this->searchRoomAvailabilityStatus, $status ); ?>>
					<?php echo esc_html( $statusLabel ); ?>
				</option>

			<?php endforeach; ?>

		</select>
		<input type="text"
			   class="mphb-datepick mphb-search-date-from mphb-date-input-width<?php echo esc_attr( $datesFromToClass ); ?>"
			   name="<?php echo esc_attr( self::ATTS_FIELD_NAME ); ?>[search_date_from]"
			   placeholder="<?php esc_attr_e( 'From', 'motopress-hotel-booking' ); ?>"
			   value="<?php echo esc_attr( $dateFrom ); ?>"
			   inputmode="none" />
		<input type="text"
			   class="mphb-datepick mphb-search-date-to mphb-date-input-width<?php echo esc_attr( $datesFromToClass ); ?>"
			   name="<?php echo esc_attr( self::ATTS_FIELD_NAME ); ?>[search_date_to]"
			   placeholder="<?php esc_attr_e( 'Until', 'motopress-hotel-booking' ); ?>"
			   value="<?php echo esc_attr( $dateTo ); ?>"
			   inputmode="none" />

		<?php
	}

	private function renderRoomTypeSelect() {
		$roomTypes = MPHB()->getRoomTypePersistence()->getPosts(
			array(
				'fields' => 'all',
			)
		);
		?>
		<select id="mphb-bookings-calendar-filter-room-type"
				name="<?php echo esc_attr( self::ATTS_FIELD_NAME ); ?>[room_type_id]">
			<option <?php selected( $this->roomTypeId, self::ALL_ROOM_TYPES ); ?> value="<?php echo esc_attr( self::ALL_ROOM_TYPES ); ?>" >
				<?php esc_html_e( 'All Accommodation Types', 'motopress-hotel-booking' ); ?>
			</option>

			<?php foreach ( $roomTypes as $roomType ) : ?>

				<option <?php selected( $this->roomTypeId, $roomType->ID ); ?> value="<?php echo esc_attr( $roomType->ID ); ?>">
					<?php echo esc_html( $roomType->post_title ); ?>
				</option>

			<?php endforeach; ?>

		</select>
		<?php
	}

	private function renderPeriodFilter() {
		$periods       = $this->getPeriodsList();
		$prevNextClass = $this->periodType === 'custom' ? ' mphb-hide' : '';
		?>
		<?php
		foreach ( $periods as $period => $periodLabel ) :
			if ( $period === self::PERIOD_TYPE_CUSTOM ) {
				continue;
			}
			$periodPage = $this->periodType === $period ? $this->periodPage : 0;
			?>
			<input type="hidden" name="<?php echo esc_attr( self::ATTS_FIELD_NAME . '[period_page_' . $period . ']' ); ?>" value="<?php echo esc_attr( $periodPage ); ?>"  />
		<?php endforeach; ?>

		<label for="mphb-bookings-calendar-filter-period"><?php esc_html_e( 'Period:', 'motopress-hotel-booking' ); ?></label>
		<?php
		submit_button(
			'&lt;',
			'button mphb-period-prev' . $prevNextClass,
			self::ATTS_FIELD_NAME . '[action_period_prev]',
			false,
			array( 'title' => __( '&lt; Prev', 'motopress-hotel-booking' ) )
		);
		?>
		<?php // phpcs:ignore ?>
		<select id="mphb-bookings-calendar-filter-period" name="<?php echo self::ATTS_FIELD_NAME; ?>[period]">
			<?php foreach ( $periods as $period => $periodLabel ) : ?>
				<option <?php selected( $this->periodType, $period ); ?> value="<?php echo esc_attr( $period ); ?>">
					<?php echo esc_html( $periodLabel ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
		submit_button(
			'&gt;',
			'button mphb-period-next' . $prevNextClass,
			self::ATTS_FIELD_NAME . '[action_period_next]',
			false,
			array( 'title' => __( 'Next &gt;', 'motopress-hotel-booking' ) )
		);
		$this->renderCustomPeriodFilter();
	}

	private function renderCustomPeriodFilter() {
		$customPeriodWrapperClass = $this->periodType !== 'custom' ? ' mphb-hide' : '';

		$dateFrom = ! is_null( $this->customPeriodFrom ) ? $this->customPeriodFrom->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '';
		$dateTo   = ! is_null( $this->customPeriodTo ) ? $this->customPeriodTo->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '';
		?>
		<div class="mphb-custom-period-wrapper
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $customPeriodWrapperClass;
		?>
		">
			<?php // phpcs:ignore ?>
			<input type="text" class="mphb-datepick mphb-custom-period-from mphb-date-input-width" name="<?php echo self::ATTS_FIELD_NAME; ?>[custom_period][date_from]" placeholder="<?php esc_attr_e( 'From', 'motopress-hotel-booking' ); ?>" value="<?php echo esc_attr( $dateFrom ); ?>" inputmode="none" />
			<?php // phpcs:ignore ?>
			<input type="text" class="mphb-datepick mphb-custom-period-to mphb-date-input-width" name="<?php echo self::ATTS_FIELD_NAME; ?>[custom_period][date_to]" placeholder="<?php esc_attr_e( 'Until', 'motopress-hotel-booking' ); ?>" value="<?php echo esc_attr( $dateTo ); ?>" inputmode="none" />
		</div>
		<?php
	}

	public function renderRoomsTable() {
		?>
		<table class="mphb-bookings-calendar-rooms widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Accommodation', 'motopress-hotel-booking' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $this->roomPosts ) ) : ?>
					<?php foreach ( $this->roomPosts as $roomPost ) : ?>
						<tr>
							<td title="<?php echo esc_attr( $roomPost->post_title ); ?>">
								<a href="<?php echo esc_url( get_edit_post_link( $roomPost->ID ) ); ?>"><?php echo esc_html( $roomPost->post_title ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td></td></tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e( 'Accommodation', 'motopress-hotel-booking' ); ?></th>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	function renderDatesTable() {
		?>
		<table class="mphb-bookings-date-table widefat">
			<thead>
				<?php $this->renderDatesTableHeadingsRow(); ?>
			</thead>
			<tbody>
				<?php if ( ! empty( $this->roomPosts ) ) : ?>
					<?php foreach ( $this->roomPosts as $roomPost ) : ?>
						<tr room-id="<?php echo esc_attr( $roomPost->ID ); ?>">
							<?php
							foreach ( $this->periodArr as $date ) {
								$this->renderPseudoCell( $roomPost->ID, $date );
							}
							?>
						</tr>
					<?php endforeach; // rooms loop ?>
				<?php else : ?>
					<tr>
						<td class="mphb-no-rooms-found" colspan="<?php echo esc_attr( \MPHB\Utils\DateUtils::calcNights( $this->periodStartDate, $this->periodEndDate ) * 2 ); ?>">
							<?php esc_html_e( 'No accommodations found.', 'motopress-hotel-booking' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<?php $this->renderDatesTableHeadingsRow(); ?>
			</tfoot>
		</table>
		<?php
	}

	public function renderDatesTableHeadingsRow() {
		?>
		<tr>
			<?php foreach ( $this->periodArr as $date ) : ?>
				<?php $isToday = $date->format( 'Y-m-d' ) === current_time( 'Y-m-d' ); ?>
				<?php $thClass = $isToday ? 'mphb-date-today' : ''; ?>
				<th colspan="2" class="
				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $thClass;
				?>
					">
					<?php echo esc_html( $date->format( 'j' ) ); ?>
					<br />
					<?php echo esc_html_x( $date->format( 'M' ), $date->format( 'F' ) . ' abbreviation' ); ?>
					<br />
					<small class="mphb-subscript"><?php echo esc_html( $date->format( 'Y' ) ); ?></small>
					<br />
					<small class="mphb-subscript"><?php echo esc_html__( $date->format( 'D' ) ); ?></small>
				</th>
			<?php endforeach; ?>
		</tr>
		<?php
	}

	/**
	 *
	 * @param string    $roomId
	 * @param \DateTime $date
	 *
	 * @since 3.9.6 - imported bookings are marked.
	 */
	private function renderPseudoCell( $roomId, $date ) {
		$firstPartClass    = '';
		$secondPartClass   = '';
		$firstPartContent  = '';
		$secondPartContent = '';

		$dateDetails = $this->getRoomDateDetails( $roomId, $date );
		$isToday     = $date->format( 'Y-m-d' ) === mphb_current_time( 'Y-m-d' );
		if ( $isToday ) {
			$firstPartClass  .= ' mphb-date-today';
			$secondPartClass .= ' mphb-date-today';
		}

		if ( $dateDetails['is_check_in'] ) {
			$secondPartClass .= ' mphb-date-check-in';
			switch ( $dateDetails['booking_status'] ) {
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED:
					$secondPartClass .= ' mphb-date-check-in-booked';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING:
					$secondPartClass .= ' mphb-date-check-in-pending mphb-date-check-in-pending-admin';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER:
					$secondPartClass .= ' mphb-date-check-in-pending mphb-date-check-in-pending-user';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT:
					$secondPartClass .= ' mphb-date-check-in-pending mphb-date-check-in-pending-payment';
					break;
			}
		}

		if ( $dateDetails['is_check_out'] ) {
			$firstPartClass .= ' mphb-date-check-out';
			switch ( $dateDetails['check_out_booking_status'] ) {
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED:
					$firstPartClass .= ' mphb-date-check-out-booked';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING:
					$firstPartClass .= ' mphb-date-check-out-pending mphb-date-check-out-pending-admin';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER:
					$firstPartClass .= ' mphb-date-check-out-pending mphb-date-check-out-pending-user';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT:
					$firstPartClass .= ' mphb-date-check-out-pending mphb-date-check-out-pending-payment';
					break;
			}
		}

		if ( $dateDetails['is_locked'] ) {
			if ( ! $dateDetails['is_check_in'] && ! $dateDetails['is_check_out'] ) {
				$firstPartClass .= ' mphb-date-room-locked';
				switch ( $dateDetails['booking_status'] ) {
					case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED:
						$firstPartClass .= ' mphb-date-booked';
						break;
					case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING:
						$firstPartClass .= ' mphb-date-pending mphb-date-pending-admin';
						break;
					case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER:
						$firstPartClass .= ' mphb-date-pending mphb-date-pending-user';
						break;
					case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT:
						$firstPartClass .= ' mphb-date-pending mphb-date-pending-payment';
						break;
				}
			}

			if ( $dateDetails['is_check_in'] ) {

				$bookingNights = 1;

				if ( isset( $dateDetails['booking_id'] ) ) {

					$booking = MPHB()->getBookingRepository()->findById( $dateDetails['booking_id'] );

					if ( $booking ) {

						$bookingNights = \MPHB\Utils\DateUtils::calcNights( $booking->getCheckInDate(), $booking->getCheckOutDate() );
					}
				}

				$linkClass = 'mphb-link-to-booking booking-nights-' . $bookingNights;

				$secondPartContent = '<a class="' . esc_attr( $linkClass ) . '" href="' . esc_url( $dateDetails['booking_edit_link'] ) . '" data-booking-id="' . esc_attr( $dateDetails['booking_id'] ) . '" data-booking-status="' . esc_attr( $dateDetails['booking_status'] ) . '">' . $dateDetails['booking_id'] .
					( ( isset( $dateDetails['booking_details'] ) && isset( $dateDetails['booking_details']['customer'] ) ) ?
						'<span class="mphb-customer">' . $dateDetails['booking_details']['customer'] . '</span>' : '' ) .
				'</a>';
			} else {
				$firstPartContent  = '<a class="mphb-silent-link-to-booking" href="' . esc_url( $dateDetails['booking_edit_link'] ) . '" data-booking-id="' . esc_attr( $dateDetails['booking_id'] ) . '" data-booking-status="' . esc_attr( $dateDetails['booking_status'] ) . '"></a>';
				$secondPartContent = '<a class="mphb-silent-link-to-booking" href="' . esc_url( $dateDetails['booking_edit_link'] ) . '" data-booking-id="' . esc_attr( $dateDetails['booking_id'] ) . '" data-booking-status="' . esc_attr( $dateDetails['booking_status'] ) . '"></a>';
			}
			$secondPartClass .= ' mphb-date-room-locked';
			switch ( $dateDetails['booking_status'] ) {
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED:
					$secondPartClass .= ' mphb-date-booked';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING:
					$secondPartClass .= ' mphb-date-pending mphb-date-pending-admin';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER:
					$secondPartClass .= ' mphb-date-pending mphb-date-pending-user';
					break;
				case \MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT:
					$secondPartClass .= ' mphb-date-pending mphb-date-pending-payment';
					break;
			}
		}

		$dateBefore = clone $date;
		$dateBefore->modify( '-1 day' );
		$dateBeforeFormatted = $dateBefore->format( 'Y-m-d' );

		if ( isset( $this->data[ $roomId ] ) &&
			isset( $this->data[ $roomId ][ $dateBeforeFormatted ] ) &&
			isset( $this->data[ $roomId ][ $dateBeforeFormatted ]['is_blocked'] ) &&
			$this->data[ $roomId ][ $dateBeforeFormatted ]['is_blocked'] ) {

			$firstPartClass .= ' mphb-date-room-locked mphb-date-blocked';
		}

		if ( $dateDetails['is_blocked'] ) {

			$secondPartClass .= ' mphb-date-room-locked mphb-date-blocked';
		}

		// Mark buffer days
		if ( ! $dateDetails['is_blocked'] && $dateDetails['is_buffer'] ) {
			// Don't replace check-out classes and don't mark as buffered the middle booking days
			if ( $dateDetails['is_buffer_first'] && ! $dateDetails['is_check_out'] && strpos( $firstPartClass, 'mphb-date-room-locked' ) === false ) {
				$firstPartClass .= ' mphb-date-room-locked mphb-date-buffer';
			}

			// Don't replace check-in classes and don't mark as buffered the middle booking days
			if ( $dateDetails['is_buffer_second'] && ! $dateDetails['is_check_in'] && strpos( $secondPartClass, 'mphb-date-room-locked' ) === false ) {
				$secondPartClass .= ' mphb-date-room-locked mphb-date-buffer';
			}
		}

		// Mark as free
		if ( ! $dateDetails['is_locked'] && ! $dateDetails['is_blocked'] ) {
			if ( ! $dateDetails['is_check_out'] && ! $dateDetails['is_buffer_first'] ) {
				$firstPartClass .= ' mphb-date-free';
			}
			if ( ! $dateDetails['is_buffer_second'] ) {
				$secondPartClass .= ' mphb-date-free';
			}
		}

		// Mark imported bookings
		$isCurrentBookingImported  = isset( $dateDetails['booking_details'] ) && $dateDetails['booking_details']['ical'];
		$isPreviousBookingImported = isset( $dateDetails['check_out_booking_details'] ) && $dateDetails['check_out_booking_details']['ical'];

		$bookingPendingStatuses   = array(
			\MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING,
			\MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_USER,
			\MPHB\PostTypes\BookingCPT\Statuses::STATUS_PENDING_PAYMENT,
		);
		$isCurrentBookingPending  = ! empty( $dateDetails['booking_status'] ) && in_array( $dateDetails['booking_status'], $bookingPendingStatuses );
		$isPreviousBookingPending = ! empty( $dateDetails['check_out_booking_status'] ) && in_array( $dateDetails['check_out_booking_status'], $bookingPendingStatuses );

		if ( $isCurrentBookingImported || $isPreviousBookingImported ) {

			if ( ( ! $dateDetails['is_check_in'] && $isCurrentBookingImported && ! $isCurrentBookingPending ) ||
				( $dateDetails['is_check_out'] && $isPreviousBookingImported && ! $isPreviousBookingPending ) ) {

				$firstPartClass .= ' mphb-date-imported-booking';
			}

			if ( ( ! $dateDetails['is_check_out'] && $isCurrentBookingImported && ! $isCurrentBookingPending ) ||
				( $dateDetails['is_check_in'] && $isCurrentBookingImported && ! $isCurrentBookingPending ) ) {

				$secondPartClass .= ' mphb-date-imported-booking';
			}
		}

		// Generate titles
		$firstTitle  = $this->generateCellTitle( $date, $dateDetails, 'first' );
		$secondTitle = $this->generateCellTitle( $date, $dateDetails, 'second' );
		?>
		<td class="mphb-date-first-part 
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $firstPartClass;
		?>
			" title="<?php echo esc_attr( $firstTitle ); ?>"><?php echo $firstPartContent; ?></td>
		<td class="mphb-date-second-part 
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $secondPartClass;
		?>
			" title="<?php echo esc_attr( $secondTitle ); ?>"><?php echo $secondPartContent; ?></td>
		<?php
	}

	/**
	 * @param \DateTime $date
	 * @param array     $details
	 * @param string    $part "first"|"second"
	 * @return string
	 */
	private function generateCellTitle( $date, $details, $part ) {
		$titles = array();

		if ( $details['is_check_out'] ) {
			$titles[] = sprintf( __( 'Check-out #%d', 'motopress-hotel-booking' ), (int) $details['check_out_booking_id'] );
		}

		if ( $details['is_check_in'] ) {
			$titles[] = sprintf( __( 'Check-in #%d', 'motopress-hotel-booking' ), (int) $details['booking_id'] );
		}

		if ( $details['is_locked'] && ! ( $details['is_check_in'] || $details['is_check_out'] ) ) {
			$titles[] = sprintf( __( 'Booking #%d', 'motopress-hotel-booking' ), (int) $details['booking_id'] );
		} elseif ( $details['is_blocked'] ) {
			$titles[] = ! empty( $details['comments'] ) ? $details['comments'] : __( 'Blocked', 'motopress-hotel-booking' );
		} elseif ( $part == 'first' && $details['is_buffer_first'] ) {
			if ( ! $details['is_check_out'] ) {
				$titles[] = __( 'Buffer time.', 'motopress-hotel-booking' );
			}
		} elseif ( $part == 'second' && $details['is_buffer_second'] ) {
			if ( ! $details['is_check_in'] ) {
				$titles[] = __( 'Buffer time.', 'motopress-hotel-booking' );
			}
		} elseif ( ! $details['is_locked'] && ! $details['is_check_out'] ) { // is_check_out != is_locked
			$titles[] = _x( 'Free', 'Availability', 'motopress-hotel-booking' );
		}

		$dateString         = $date->format( 'D j, M Y:' );
		$availabilityString = implode( ', ', $titles );
		$summary            = $dateString . ' ' . $availabilityString;

		$bookingDetails = array();
		if ( $part == 'first' && $details['is_check_out'] ) {
			$bookingDetails = $details['check_out_booking_details'];
		} elseif ( isset( $details['booking_details'] ) ) {
			// Don't show details on the first part of the cell with check-in
			if ( $part != 'first' || ! $details['is_check_in'] ) {
				$bookingDetails = $details['booking_details'];
			}
		}

		$ical = array();
		if ( isset( $bookingDetails['ical'] ) ) {
			$ical = $bookingDetails['ical'];
			unset( $bookingDetails['ical'] );
		}

		$info = array_merge( array( 'summury' => $summary ), $bookingDetails );

		$info = array_map( 'trim', $info );
		$info = array_filter( $info );

		if ( isset( $info['adults'] ) ) {
			$info['adults'] = sprintf( __( 'Adults: %s', 'motopress-hotel-booking' ), $info['adults'] );
		}

		if ( isset( $info['children'] ) ) {
			$info['children'] = sprintf( __( 'Children: %s', 'motopress-hotel-booking' ), $info['children'] );
		}

		$title = implode( '&#10;', $info );

		if ( ! empty( $ical ) ) {
			if ( ! empty( $ical['uid'] ) ) {
				$title .= '&#10;' . sprintf( __( 'Booking imported with UID %s.', 'motopress-hotel-booking' ), $ical['uid'] );
			} else {
				$title .= '&#10;' . __( 'Imported booking.', 'motopress-hotel-booking' );
			}

			if ( ! empty( $ical['summary'] ) ) {
				$title .= '&#10;' . sprintf( __( 'Summary: %s.', 'motopress-hotel-booking' ), $ical['summary'] );
			}

			if ( ! empty( $ical['description'] ) ) {
				$title .= '&#10;' . sprintf( __( 'Description: %s.', 'motopress-hotel-booking' ), $ical['description'] );
			}

			if ( ! empty( $ical['prodid'] ) ) {
				$title .= '&#10;' . sprintf( __( 'Source: %s.', 'motopress-hotel-booking' ), $ical['prodid'] );
			}
		}

		return $title;
	}
}
