<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\Entities;

class SeasonManageCPTPage extends ManageCPTPage {

	public function __construct( $postType, $atts = array() ) {
		parent::__construct( $postType, $atts );
		$this->description = __( 'Seasons are real periods of time, dates or days that come with different prices for accommodations. E.g. Winter 2018 ($120 per night), Christmas ($150 per night).', 'motopress-hotel-booking' );
	}

	public function filterColumns( $columns ) {
		$customColumns = array(
			'start_date' => __( 'Start', 'motopress-hotel-booking' ),
			'end_date'   => __( 'End', 'motopress-hotel-booking' ),
			'days'       => __( 'Days', 'motopress-hotel-booking' ),
		);
		$offset        = array_search( 'date', array_keys( $columns ) ); // Set custom columns position before "DATE" column
		$columns       = array_slice( $columns, 0, $offset, true ) + $customColumns + array_slice( $columns, $offset, count( $columns ) - 1, true );

		unset( $columns['date'] );

		return $columns;
	}

	public function filterSortableColumns( $columns ) {
		return $columns;
	}

	public function renderColumns( $column, $postId ) {
		$season = MPHB()->getSeasonRepository()->findById( $postId );
		switch ( $column ) {
			case 'start_date':
				$startDate = $season->getStartDate();
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $startDate ? \MPHB\Utils\DateUtils::formatDateWPFront( $startDate ) : static::EMPTY_VALUE_PLACEHOLDER;
				break;
			case 'end_date':
				$endDate = $season->getEndDate();
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $endDate ? \MPHB\Utils\DateUtils::formatDateWPFront( $endDate ) : static::EMPTY_VALUE_PLACEHOLDER;
				break;
			case 'days':
				$days = $season->getDays();
				if ( empty( $days ) ) {
					esc_html_e( 'None', 'motopress-hotel-booking' );
				} elseif ( count( $days ) === 7 ) {
					esc_html_e( 'All', 'motopress-hotel-booking' );
				} else {
					echo esc_html( join( ', ', array_map( array( '\MPHB\Utils\DateUtils', 'getDayByKey' ), $days ) ) );
				}
				break;
		}
	}

}
