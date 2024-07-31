<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\Rate;
use MPHB\Entities\SeasonPrice;

class RateData extends AbstractPostData {

	/**
	 * @var Rate
	 */
	public $entity;

	public static function getRepository() {
		return MPHB()->getRateRepository();
	}

	public static function getProperties() {
		return array(
			'id'                    => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'                => array(
				'description' => 'Status.',
				'type'        => 'string',
				'enum'        => array( 'active', 'disabled' ),
				'context'     => array( 'view', 'edit' ),
				'default'     => 'active',
			),
			'title'                 => array(
				'description' => 'Title.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'description'           => array(
				'description' => 'Description.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'accommodation_type_id' => array(
				'description' => 'Unique identifier of accommodation type.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'season_prices'         => array(
				'description' => 'Season prices.',
				'type'        => 'array',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
				'items'       => array(
					'type'       => 'object',
					'required'   => true,
					'properties' => array(
						'priority'   => array(
							'description' => 'Higher number means more priority.',
							'type'        => 'integer',
							'context'     => array( 'embed', 'view', 'edit' ),
							'required'    => true,
						),
						'base_price' => array(
							'description' => 'Base price.',
							'type'        => 'number',
							'context'     => array( 'embed', 'view', 'edit' ),
							'required'    => true,
						),
						'season_id'  => array(
							'description' => 'Season id.',
							'type'        => 'integer',
							'context'     => array( 'embed', 'view', 'edit' ),
							'required'    => true,
						),
						// #nolite-start
						'variations' => array(
							'description' => 'Variations.',
							'type'        => 'array',
							'context'     => array( 'embed', 'view', 'edit' ),
							'required'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'adults'   => array(
										'description' => 'Adults.',
										'type'        => 'integer',
										'context'     => array( 'embed', 'view', 'edit' ),
										'required'    => true,
									),
									'children' => array(
										'description' => 'Children.',
										'type'        => 'integer',
										'context'     => array( 'embed', 'view', 'edit' ),
										'required'    => true,
									),
									'price'    => array(
										'description' => 'Price.',
										'type'        => 'number',
										'context'     => array( 'embed', 'view', 'edit' ),
										'required'    => true,
									),
								),
							),
						),
						// #nolite-end
					),
				),
			),
		);
	}

	protected function getStatus() {
		if ( isset( $this->status ) ) {
			return $this->status;
		}

		return $this->entity->isActive() ? 'active' : 'disabled';
	}

	protected function getAccommodationTypeId() {
		if ( isset( $this->accommodation_type_id ) ) {
			return $this->accommodation_type_id;
		}

		return (int) $this->entity->getRoomTypeId();
	}

	protected function getBasePrice() {
		if ( isset( $this->base_price ) ) {
			return $this->base_price;
		}

		return $this->entity->getMinBasePrice();
	}

	// #nolite-start
	private function parseSeasonPriceVariations( SeasonPrice $seasonPrice ) {
		$variations       = $seasonPrice->getPricesAndVariations();
		$parsedVariations = array();
		if ( ! count( $variations ) || false === $variations['enable_variations'] || ! count( $variations['variations'] ) ) {
			return $parsedVariations;
		}

		foreach ( $variations['variations'] as $variation ) {
			$parsedVariations[] = array(
				'adults'   => $variation['adults'],
				'children' => $variation['children'],
				'price'    => $variation['prices'][0],
			);
		}

		return $parsedVariations;
	}

	// #nolite-end

	protected function getSeasonPrices() {
		$seasonPriceData = array();
		$seasonPrices    = $this->entity->getSeasonPrices();
		if ( ! count( $seasonPrices ) ) {
			return array();
		}
		foreach ( $seasonPrices as $seasonPrice ) {
			$seasonPriceDataItem = array(
				'priority'   => $seasonPrice->getId(),
				'season_id'  => $seasonPrice->getSeasonId(),
				'base_price' => $seasonPrice->getPrice(),
			);
			// #nolite-start
			$seasonPriceDataItem['variations'] = $this->parseSeasonPriceVariations( $seasonPrice );
			// #nolite-end

			$seasonPriceData[] = $seasonPriceDataItem;
		}

		return $seasonPriceData;
	}

	protected function setAccommodationTypeId( $accommodationTypeId ) {
		if ( is_null( MPHB()->getRoomTypePersistence()->getPost( $accommodationTypeId ) ) ) {
			throw new \Exception( sprintf( 'Invalid %s: %d.', 'accommodation_type_id', $accommodationTypeId ) );
		}

		$this->accommodation_type_id = (string) $accommodationTypeId;
	}

	protected function setSeasonPrices( $seasonPrices ) {
		if ( ! count( $seasonPrices ) ) {
			return;
		}
		$seasonPriceEntities = array();

		// Sort the array of season prices by priority.
		// Because in rate object season price priority determinate by order of array of season prices.
		array_multisort(
			array_map(
				function ( $element ) {
					return $element['priority'];
				},
				$seasonPrices
			),
			SORT_ASC,
			$seasonPrices
		);

		foreach ( $seasonPrices as $key => $seasonPrice ) {
			if ( ! MPHB()->getSeasonRepository()->findById( $seasonPrice['season_id'] ) ) {
				throw new \Exception( sprintf( 'Invalid %s: %d.', sprintf( 'season_prices[%d][season_id]', $key ), $seasonPrice['season_id'] ) );
			}

			$atts = array(
				'id'        => $seasonPrice['priority'],
				'season_id' => $seasonPrice['season_id'],
				'price'     => array(
					'periods' => array( 1 ),
					'prices'  => array( floatval( $seasonPrice['base_price'] ) ),
				),
			);
			// #nolite-start
			if ( count( $seasonPrice['variations'] ) ) {
				$seasonPrice['variations'] = array_map(
					function ( $variation ) {
						$variation['price']    = floatval( $variation['price'] );
						$variation['adults']   = intval( $variation['adults'] );
						$variation['children'] = intval( $variation['children'] );

						return $variation;
					},
					$seasonPrice['variations']
				);
				$seasonPrice['base_price'] = floatval( $seasonPrice['base_price'] );
			}
			$atts['price']['variations'] = $seasonPrice['variations'];
			if ( count( $seasonPrice['variations'] ) ) {
				$atts['price']['enable_variations'] = true;
			} else {
				$atts['price']['enable_variations'] = false;

			}
			// #nolite-end

			$seasonPriceEntities[] = SeasonPrice::create( $atts );
		}
		$this->season_prices = $seasonPriceEntities;
	}

	public function getSeasonIds() {
		$seasonPrices = $this->entity->getSeasonPrices();
		if ( ! count( $seasonPrices ) ) {
			return array();
		}

		return array_map(
			function ( $seasonPrice ) {
				return $seasonPrice->getSeasonId();
			},
			$seasonPrices
		);
	}

	private function setDataToEntity() {
		$atts   = array(
			'id' => $this->id,
		);
		$fields = static::getWritableFieldKeys();
		foreach ( $fields as $field ) {
			switch ( $field ) {
				case 'status':
					$atts['active'] = $this->status === 'active' ? true : false;
					break;
				case 'accommodation_type_id':
					$atts['room_type_id'] = $this->accommodation_type_id;
					break;
				case 'season_prices':
					$atts['season_prices'] = isset( $this->{$field} ) ? $this->{$field} : array_reverse( $this->entity->getSeasonPrices() );
					break;
				default:
					$atts[ $field ] = $this->{$field};
			}
			if ( isset( $this->{$field} ) ) {
				unset( $this->{$field} );
			}
		}
		$this->entity = new Rate( $atts );
	}

	public function save() {
		$this->setDataToEntity();

		return parent::save();
	}
}
