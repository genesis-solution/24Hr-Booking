<?php

namespace MPHB\CheckoutFields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds some functionality to Hotel Booking plugin's reports.
 */
class ReportsHandler {

	public function __construct() {

		add_filter(
			'mphb_export_bookings_columns',
			function( array $columns ): array {
				return $this->filterExportingColumns( $columns );
			},
			10,
			1
		);

		add_filter(
			'mphb_export_bookings_parse_columns',
			function( array $parsedValues, \MPHB\Entities\Booking $booking ): array {
				return $this->filterParsedExportingColumns( $parsedValues, $booking );
			},
			10,
			2
		);
	}

	private function filterExportingColumns( array $columns ): array {

		$customFields = mphb_get_custom_customer_fields();

		foreach ( $customFields as $fieldName => $customField ) {
			$customColumn             = 'customer-' . str_replace( '_', '-', $fieldName );
			$columns[ $customColumn ] = $customField['label'];
		}

		return $columns;
	}

	private function filterParsedExportingColumns( array $parsedValues, \MPHB\Entities\Booking $booking ): array {

		$customFields = mphb_get_custom_customer_fields();
		$customer     = $booking->getCustomer();

		if ( empty( $customFields ) || is_null( $customer ) ) {
			return $parsedValues;
		}

		// Parse custom columns
		foreach ( $customFields as $fieldName => $customField ) {

			$customColumn = 'customer-' . str_replace( '_', '-', $fieldName );

			if ( isset( $parsedValues[ $customColumn ] ) ) {

				$value = (string) $customer->getCustomField( $fieldName );

				switch ( $customField['type'] ) {
					case 'checkbox':
						if ( $value === '1' ) {
							$value = esc_html__( 'Yes', 'mphb-checkout-fields' );
						} elseif ( $value === '0' ) {
							$value = esc_html__( 'No', 'mphb-checkout-fields' );
						}
						break;

					case 'date_of_birth':
						if ( ! empty( $value ) ) {
							$value = \MPHB\Utils\DateUtils::convertDateFormat( $value, 'Y-m-d', mphb()->settings()->dateTime()->getDateFormat() );
						}
						break;
				}

				$parsedValues[ $customColumn ] = $value;
			}
		}

		return $parsedValues;
	}
}
