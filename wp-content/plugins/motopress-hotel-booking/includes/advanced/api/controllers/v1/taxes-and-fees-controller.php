<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Controllers\V1;

use MPHB\Advanced\Api\Controllers\AbstractRestOptionsController;
use MPHB\Advanced\Api\Data\OptionsSchema;

class TaxesAndFeesController extends AbstractRestOptionsController {

	const ENDPOINT_REPLACEMENT_RULES = array(
		'rooms'               => 'accommodations',
		'per_room_per_day'    => 'per_accommodation_per_day',
		'per_room_percentage' => 'per_accommodation_percentage',
	);

	protected $namespace = 'mphb/v1';

	protected $rest_base = 'taxes_and_fees';

	/**
	 * @return OptionsSchema
	 */
	protected function initOptions() {

		$options = new OptionsSchema();

		$feesSchema = array(
			'type'  => 'array',
			'items' => array(
				'oneOf' => array(
					$this->getPerGuestPerDayProperty(),
					$this->getPerAccommodationPerDayProperty(),
					$this->getPerAccommodationPercentageProperty(),
				),
			),
		);
		$options->addOption( 'mphb_fees', 'fees', $feesSchema );

		$accommodationTaxes = array(
			'type'  => 'array',
			'items' => array(
				'oneOf' => array(
					$this->getPerGuestPerDayProperty(),
					$this->getPerAccommodationPerDayProperty(),
					$this->getPerAccommodationPercentageProperty(),
				),
			),
		);
		$options->addOption( 'mphb_accommodation_taxes', 'accommodation_taxes', $accommodationTaxes );

		$serviceTaxes = array(
			'type'  => 'array',
			'items' => array(
				'type'       => 'object',
				'properties' => array(
					'label'  => array(
						'type' => 'string',
					),
					'type'   => array(
						'type' => 'string',
						'enum' => array( 'percentage' ),
					),
					'amount' => array(
						'type'    => 'number',
						'minimum' => 0,
					),
				),
			),
		);
		$options->addOption( 'mphb_service_taxes', 'service_taxes', $serviceTaxes );

		$feeTaxes = array(
			'type'  => 'array',
			'items' => array(
				'type'       => 'object',
				'properties' => array(
					'label'  => array(
						'type' => 'string',
					),
					'type'   => array(
						'type' => 'string',
						'enum' => array( 'percentage' ),
					),
					'amount' => array(
						'type'    => 'number',
						'minimum' => 0,
					),
				),
			),
		);
		$options->addOption( 'mphb_fee_taxes', 'fee_taxes', $feeTaxes );

		return $options;
	}

	private function getAccommodationTypeProperty() {
		return array(
			'name'        => 'accommodation_type_ids',
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
				'enum' => $this->getAccommodationTypeIds(),
			),
			'uniqueItems' => true,
			'required'    => true,
		);
	}

	/**
	 * @return int[]
	 */
	private function getAccommodationTypeIds() {
		$atts                 = array(
			'fields' => 'ids',
		);
		$accommodationTypeIds = MPHB()->getRoomTypePersistence()->getPosts( $atts );
		array_unshift( $accommodationTypeIds, 0 );

		return $accommodationTypeIds;
	}

	private function getPerGuestPerDayProperty() {
		return array(
			'title'      => 'Per guest / per day',
			'type'       => 'object',
			'properties' => array(
				'label'          => array(
					'type' => 'string',
				),
				'limit'          => array(
					'type'    => 'integer',
					'minimum' => 0,
				),
				'included'       => array(
					'type' => 'boolean',
				),
				'accommodations' => $this->getAccommodationTypeProperty(),
				'type'           => array(
					'type' => 'string',
					'enum' => array( 'per_guest_per_day' ),
				),
				'amount'         => array(
					'type'       => 'object',
					'properties' => array(
						'adults'   => array(
							'type'     => 'number',
							'minimum'  => 0,
							'required' => true,
						),
						'children' => array(
							'type'     => 'number',
							'minimum'  => 0,
							'required' => true,
						),
					),
				),
			),
		);
	}

	private function getPerAccommodationPerDayProperty() {
		return array(
			'title'      => 'Per accommodation / per day',
			'type'       => 'object',
			'properties' => array(
				'label'          => array(
					'type' => 'string',
				),
				'limit'          => array(
					'type'    => 'integer',
					'minimum' => 0,
				),
				'included'       => array(
					'type' => 'boolean',
				),
				'accommodations' => $this->getAccommodationTypeProperty(),
				'type'           => array(
					'type' => 'string',
					'enum' => array( 'per_accommodation_per_day' ),
				),
				'amount'         => array(
					'type'    => 'number',
					'minimum' => 0,
				),
			),
		);
	}

	private function getPerAccommodationPercentageProperty() {
		return array(
			'title'      => 'Per accommodation percentage',
			'type'       => 'object',
			'properties' => array(
				'label'          => array(
					'type' => 'string',
				),
				'included'       => array(
					'type' => 'boolean',
				),
				'accommodations' => $this->getAccommodationTypeProperty(),
				'type'           => array(
					'type' => 'string',
					'enum' => array( 'per_accommodation_percentage' ),
				),
				'amount'         => array(
					'type'    => 'number',
					'minimum' => 0,
				),
			),
		);
	}

	private function renameRequestOptionPropertyType( $option ) {
		if ( ! count( $option ) ) {
			return $option;
		}
		foreach ( $option as $key => $optionItem ) {
			switch ( $optionItem['type'] ) {
				case 'per_guest_per_day':
					$amount = array(
						$optionItem['amount']['adults'],
						$optionItem['amount']['children'],
					);
					break;
				case 'per_accommodation_per_day':
					$amount = $optionItem['amount'];
					break;
				default:
					throw new \Exception( 'Unknown value of property "type".' );
			}
			$option[ $key ]['amount'] = $amount;
		}

		return $option;
	}

	private function renameResponseOptionPropertyType( $option ) {
		if ( ! count( $option ) ) {
			return $option;
		}
		foreach ( $option as $key => $optionItem ) {
			switch ( $optionItem['type'] ) {
				case 'per_guest_per_day':
					$fields = array( 'adults', 'children' );
					if ( count( $optionItem['amount'] ) != count( $fields ) ) {
						throw new \Exception( 'Unknown type of type property.' );
					}
					$option[ $key ]['amount'] = array_combine( $fields, array_values( $optionItem['amount'] ) );
					break;
				case 'per_accommodation_per_day':
					if ( ! is_int( $optionItem['amount'] ) && ! is_float( $optionItem['amount'] ) ) {
						throw new \Exception( 'Unknown error.' );
					}

					$option[ $key ]['amount'] = $optionItem['amount'];
					break;
			}
		}

		return $option;
	}

	private function addDefaultLimitAndRooms( $option ) {
		return array_map(
			function ( $optionItem ) {
				$optionItem['limit'] = '0';
				$optionItem['rooms'] = '-';

				return $optionItem;
			},
			$option
		);
	}

	protected function prepareResponseFees( $option ) {
		return $this->renameResponseOptionPropertyType( $option );
	}

	protected function prepareResponseAccommodationTaxes( $option ) {
		return $this->renameResponseOptionPropertyType( $option );
	}

	protected function prepareRequestFees( $option ) {
		return $this->renameRequestOptionPropertyType( $option );
	}

	protected function prepareRequestAccommodationTaxes( $option ) {
		return $this->renameRequestOptionPropertyType( $option );
	}

	protected function prepareRequestServiceTaxes( $option ) {
		return $this->addDefaultLimitAndRooms( $option );
	}

	protected function prepareRequestFeeTaxes( $option ) {
		return $this->addDefaultLimitAndRooms( $option );
	}
}
