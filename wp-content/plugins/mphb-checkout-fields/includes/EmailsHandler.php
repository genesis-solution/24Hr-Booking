<?php

namespace MPHB\CheckoutFields;

use MPHB\CheckoutFields\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds some functionality to Hotel Booking plugin's emails.
 */
class EmailsHandler {

	public function __construct() {

		add_filter(
			'mphb_email_booking_tags',
			function( array $tags ): array {
				return $this->addCustomEmailsTags( $tags );
			},
			10,
			1
		);

		add_filter(
			'mphb_email_replace_tag',
			function( ?string $replacement, string $tag, \MPHB\Entities\Booking $booking ): string {
				
				if ( null == $replacement ) {
					$replacement = '';
				}
				return $this->replaceCustomEmailsTags( $replacement, $tag, $booking );
			},
			10,
			3
		);
	}

	private function addCustomEmailsTags( array $tags ): array {

		$customFields = mphb_get_custom_customer_fields();

		if ( ! empty( $customFields ) ) {
			$customTags = array_map(
				function ( $fieldArgs, $fieldName ) {
					return array(
						'name'        => 'customer_' . $fieldName,
						// Translators: %s is an email tag name. For example: "Customer Birth Date".
						'description' => sprintf( esc_html__( 'Customer %s', 'mphb-checkout-fields' ), $fieldArgs['label'] ),
					);
				},
				$customFields,
				array_keys( $customFields )
			);

			// Add custom tags after the last customer field
			$lastCustomerTagIndex = mphb_array_usearch(
				$tags,
				function ( $tag ) {
					return $tag['name'] == 'customer_note';
				}
			);

			$tags = mphb_array_insert_after( $tags, $lastCustomerTagIndex, $customTags );
		}

		return $tags;
	}

	private function replaceCustomEmailsTags( string $replacement, string $tag, \MPHB\Entities\Booking $booking ): string {

		if ( ! empty( $replacement ) || strpos( $tag, 'customer_' ) !== 0 ) {
			return $replacement;
		}

		$fieldName = str_replace( 'customer_', '', $tag );

		if ( mphb_is_default_customer_field( $fieldName ) ) {
			return $replacement;
		}

		$checkoutField = Plugin::getInstance()->getCheckoutFieldRepository()->findOne([
			'meta_query' => [
				[
					'key'   => 'mphb_cf_name',
					'value' => $fieldName
				]
			]
		]);

		$value = $booking->getCustomer()->getCustomField( $fieldName );

		if ( ! is_null( $value ) ) {
			if ( ! is_null( $checkoutField ) && in_array( $checkoutField->type, array( 'checkbox', 'country', 'date_of_birth', 'select' ) ) ) {
				switch ( $checkoutField->type ) {
					case 'checkbox':
						if ( $value === '1' ) {
							$value = esc_html__( 'yes', 'mphb-checkout-fields' );
						} elseif ( $value === '0' ) {
							$value = esc_html__( 'no', 'mphb-checkout-fields' );
						} else {
							$value = '';
						}
						break;

					case 'country':
						$value = mphb()->settings()->main()->getCountriesBundle()->getCountryLabel( $value );
						break;

					case 'date_of_birth':
						$date = \DateTime::createFromFormat( 'Y-m-d', $value );

						if ( $date !== false ) {
							$value = date_i18n( mphb()->settings()->dateTime()->getDateFormatWP(), $date->getTimestamp() );
						}
						break;

					case 'select':
						if ( isset( $checkoutField->options[ $value ] ) ) {
							$value = $checkoutField->options[ $value ];
						}
						break;
				}
			}

			$replacement = $value;
		}

		return $replacement;
	}
}
