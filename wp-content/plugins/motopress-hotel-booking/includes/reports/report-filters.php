<?php

namespace MPHB\Reports;

class ReportFilters {

	const REPORT_TYPES = array( 'earnings' );

	const DEFAULT_REPORT_TYPE = 'earnings';

	const DATES_RANGE_TYPES = array(
		'today',
		'yesterday',
		'this_week',
		'last_week',
		'last_thirty_days',
		'this_month',
		'last_month',
		'this_quarter',
		'last_quarter',
		'this_year',
		'last_year',
		'custom',
	);

	const DEFAULT_DATES_RANGE_TYPE = 'last_thirty_days';

	/**
	 *
	 * @return array
	 */
	public static function getReportTypes() {
		$typesList = array();

		foreach ( self::REPORT_TYPES as $reportType ) {
			switch ( $reportType ) {
				case 'earnings':
					$description = __( 'Revenue', 'motopress-hotel-booking' );
					break;
			}

			$typesList[] = array(
				'type'        => $reportType,
				'description' => $description,
			);
		}

		return apply_filters( 'mphb_filter_report_types', $typesList );
	}

	/**
	 *
	 * @return array
	 */
	public static function getDatesRanges() {
		$rangesList = array();

		foreach ( self::DATES_RANGE_TYPES as $rangeType ) {
			switch ( $rangeType ) {
				case 'today':
					$description = __( 'Today', 'motopress-hotel-booking' );
					break;
				case 'yesterday':
					$description = __( 'Yesterday', 'motopress-hotel-booking' );
					break;
				case 'this_week':
					$description = __( 'This week', 'motopress-hotel-booking' );
					break;
				case 'last_week':
					$description = __( 'Last week', 'motopress-hotel-booking' );
					break;
				case 'last_thirty_days':
					$description = __( 'Last 30 days', 'motopress-hotel-booking' );
					break;
				case 'this_month':
					$description = __( 'This month', 'motopress-hotel-booking' );
					break;
				case 'last_month':
					$description = __( 'Last month', 'motopress-hotel-booking' );
					break;
				case 'this_quarter':
					$description = __( 'This quarter', 'motopress-hotel-booking' );
					break;
				case 'last_quarter':
					$description = __( 'Last quarter', 'motopress-hotel-booking' );
					break;
				case 'this_year':
					$description = __( 'This year', 'motopress-hotel-booking' );
					break;
				case 'last_year':
					$description = __( 'Last year', 'motopress-hotel-booking' );
					break;
				case 'custom':
					$description = __( 'Custom', 'motopress-hotel-booking' );
					break;
			}

			$rangesList[] = array(
				'type'        => $rangeType,
				'description' => $description,
			);
		}

		return apply_filters( 'mphb_filter_dates_range_types', $rangesList );
	}

}


