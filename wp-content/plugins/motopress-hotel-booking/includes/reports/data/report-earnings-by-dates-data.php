<?php

namespace MPHB\Reports\Data;

use MPHB\Utils\DateUtils;
use MPHB\Reports\ReportFilters;

class ReportEarningsByDatesData extends ReportByDatesData {

	/**
	 * @var array
	 */
	protected $dataTypes;

	/**
	 * @var array
	 */
	protected $dataFilters;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts = array() ) {
		parent::__construct( $atts );

		$this->dataTypes = array(
			'confirmed' => _x( 'Confirmed', 'Booking status', 'motopress-hotel-booking' ),
			'pending'   => _x( 'Pending', 'Booking status', 'motopress-hotel-booking' ),
			'cancelled' => _x( 'Cancelled', 'Booking status', 'motopress-hotel-booking' ),
			'abandoned' => _x( 'Abandoned', 'Booking status', 'motopress-hotel-booking' ),
		);

		$this->dataFilters = array( 'totalPrice', 'totalWithoutTax', 'totalFees', 'totalServices', 'totalDiscount', 'totalBookings' );
	}

	/**
	 *
	 * @return array
	 */
	private function requestReportData() {
		$reportData = array();
		$range      = $this->getRange();
		$dataTypes  = $this->dataTypes;

		$args     = $this->prepareRequestParams();
		$responce = $this->requestBookings( $args );

		return $responce;
	}

	/**
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	private function prepareRequestParams( $atts = array() ) {
		$args['precision'] = 'days';
		$range             = $this->getRange();

		if ( in_array( $range, array( 'today', 'yesterday' ) ) ) {
			$args['precision'] = 'hours';
		} elseif ( in_array( $range, array( 'this_year', 'last_year' ) ) ) {
			$args['precision'] = 'months';
		}

		return $args;
	}

	/**
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function requestBookings( $args ) {
		foreach ( $this->getDataTypes() as $dataType ) {
			$bookingsData[ $dataType ] = array();
		}

		$bookings = array();

		add_filter( 'posts_where', array( $this, 'filterBookingsByDates' ), 10, 1 );
		$bookings = MPHB()->getBookingRepository()->findAll();
		remove_filter( 'posts_where', array( $this, 'filterBookingsByDates' ), 10, 1 );

		if ( ! empty( $bookings ) ) {
			foreach ( $bookings as $booking ) {
				$date   = $booking->getDateTime();
				$status = $booking->getStatus();

				if ( in_array( $status, array( 'pending', 'pending-payment', 'pending-user' ) ) ) {
					$dataType = 'pending';
				} else {
					$dataType = $status;
				}

				switch ( $args['precision'] ) {
					case 'days':
						$date = $date->format( 'Y-m-d 00:00:00' );
						break;
					case 'hours':
						$date = $date->format( 'Y-m-d H:00:00' );
						break;
					case 'months':
						$date = $date->format( 'Y-m' );
						break;
				}

				$bookingsData[ $dataType ][ $date ][] = $this->prepareBooking( $booking );
			}
		}

		return $this->iterateBookings( $bookingsData );
	}

	public function filterBookingsByDates( $where ) {
			$dateFrom = $this->getDateFrom();
			$dateTo   = $this->getDateTo();

			$where .= sprintf( " AND post_date_gmt >= '%s' AND post_date_gmt <= '%s'", $dateFrom, $dateTo );

			return $where;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 *
	 * @return array
	 */
	private function prepareBooking( $booking ) {
		$bookingData = array();

		$bookingData['id']              = $booking->getId();
		$bookingData['totalPrice']      = $booking->getTotalPrice();
		$bookingData['totalWithoutTax'] = 0;
		$bookingData['totalTax']        = 0;
		$bookingData['totalFees']       = 0;
		$bookingData['totalServices']   = 0;
		$bookingData['totalDiscount']   = 0;

		$priceBreakdown  = $booking->getLastPriceBreakdown();
		$roomsBreakdown  = isset( $priceBreakdown['rooms'] ) ? $priceBreakdown['rooms'] : array();
		$tax['room']     = 0;
		$tax['services'] = 0;
		$tax['fees']     = 0;

		if ( ! empty( $roomsBreakdown ) ) {
			foreach ( $roomsBreakdown as $breakdown ) {
				$bookingData['totalDiscount']   += $breakdown['room']['discount'];
				$bookingData['totalServices']   += $breakdown['services']['total'];
				$bookingData['totalFees']       += $breakdown['fees']['total'];
				$bookingData['totalWithoutTax'] += $breakdown['room']['total'] + $breakdown['services']['total'] + $breakdown['fees']['total'] - $breakdown['room']['discount'];
				$tax['room']                    += $breakdown['taxes']['room']['total'];
				$tax['services']                += $breakdown['taxes']['services']['total'];
				$tax['fees']                    += $breakdown['taxes']['fees']['total'];
				$bookingData['totalTax']        += $tax['room'] + $tax['services'] + $tax['fees'];
			}
		}

		return $bookingData;
	}

	/**
	 *
	 * @param array $bookingsForIteration
	 *
	 * @return array
	 */
	private function iterateBookings( $bookingsForIteration ) {
		$iteration = array();

		if ( ! empty( $bookingsForIteration ) ) {
			foreach ( $bookingsForIteration as $status => $bookingsByDate ) {
				$iterationByDate = array();
				foreach ( $bookingsByDate as $date => $bookings ) {
					$bookingsData = array();

					$bookingsData['bookingIds']      = array();
					$bookingsData['totalPrice']      = 0;
					$bookingsData['totalWithoutTax'] = 0;
					$bookingsData['totalTax']        = 0;
					$bookingsData['totalFees']       = 0;
					$bookingsData['totalServices']   = 0;
					$bookingsData['totalDiscount']   = 0;
					$bookingsData['totalBookings']   = 0;

					foreach ( $bookings as $booking ) {
						$bookingsData['bookingIds'][]     = $booking['id'];
						$bookingsData['totalPrice']      += $booking['totalPrice'];
						$bookingsData['totalWithoutTax'] += $booking['totalWithoutTax'];
						$bookingsData['totalTax']        += $booking['totalTax'];
						$bookingsData['totalFees']       += $booking['totalFees'];
						$bookingsData['totalServices']   += $booking['totalServices'];
						$bookingsData['totalDiscount']   += $booking['totalDiscount'];
						$bookingsData['totalBookings']   += 1;
					}
					$iterationByDate[ $date ] = $bookingsData;
				}
				$iteration[ $status ] = $iterationByDate;
			}
		}

		return $iteration;
	}

	/**
	 *
	 * @return array
	 */
	public function getReportData() {
		return $this->requestReportData();
	}

	/**
	 *
	 * @return array
	 */
	public function getDataTypes() {
		return $this->dataTypes;
	}

	/**
	 *
	 * @return array
	 */
	public function getDataFilters() {
		return $this->dataFilters;
	}
}


