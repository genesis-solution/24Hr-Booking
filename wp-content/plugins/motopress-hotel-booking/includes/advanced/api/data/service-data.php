<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\Service;

class ServiceData extends AbstractPostData {

	/**
	 * @var Service
	 */
	public $entity;

	public static function getRepository() {
		return MPHB()->getServiceRepository();
	}

	public static function getProperties() {
		return array(
			'id'            => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'         => array(
				'description' => 'Title.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'description'   => array(
				'description' => 'Description.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'price'         => array(
				'description' => 'Price.',
				'type'        => 'number',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'periodicity'   => array(
				'description' => 'How many times the customer will be charged.',
				'type'        => 'object',
				'required'    => true,
				'context'     => array( 'embed', 'view', 'edit' ),
				'oneOf'       => array(
					array(
						'title'      => 'Once \ Per day',
						'type'       => 'object',
						'properties' => array(
							'typeof' => array(
								'type'     => 'string',
								'enum'     => array( 'once', 'per_night' ),
								'required' => true,
							),
						),
					),
					array(
						'title'      => 'Guest Choice. Use the length of stay as the maximum value.',
						'type'       => 'object',
						'properties' => array(
							'typeof'       => array(
								'type'     => 'string',
								'enum'     => array( 'flexible' ),
								'required' => true,
							),
							'auto_limit'   => array(
								'type'     => 'boolean',
								'enum'     => array( true ),
								'required' => true,
							),
							'min_quantity' => array(
								'type'     => 'integer',
								'minimum'  => 1,
								'required' => true,
							),
							'max_quantity' => array(
								'type'     => 'integer',
								'minimum'  => 0,
								'readonly' => true,
							),
						),
					),
					array(
						'title'      => 'Guest Choice.',
						'type'       => 'object',
						'properties' => array(
							'typeof'       => array(
								'type'     => 'string',
								'enum'     => array( 'flexible' ),
								'required' => true,
							),
							'auto_limit'   => array(
								'type'     => 'boolean',
								'enum'     => array( false ),
								'required' => true,
							),
							'min_quantity' => array(
								'type'     => 'integer',
								'minimum'  => 1,
								'required' => true,
							),
							'max_quantity' => array(
								'type'     => 'integer',
								'minimum'  => 0,
								'required' => true,
							),
						),
					),
				),
			),
			'repeatability' => array(
				'description' => 'Repeatability.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'enum'        => array( 'once', 'per_adult' ),
				'require'     => true,
			),
		);
	}

	protected function getPeriodicity() {

		if ( isset( $this->periodicity ) ) {
			return $this->periodicity;
		}

		return array(
			'typeof'       => $this->entity->getPeriodicity(),
			'auto_limit'   => $this->entity->isAutoLimit(),
			'min_quantity' => $this->entity->getMinQuantity(),
			'max_quantity' => $this->entity->getMaxQuantityNumber(),
		);
	}

	protected function setPeriodicity( $periodicity ) {
		$keys = array( 'typeof', 'auto_limit', 'min_quantity', 'max_quantity' );

		foreach ( $keys as $key ) {
			if ( isset( $periodicity[ $key ] ) ) {
				continue;
			}
			switch ( $key ) {
				case 'typeof':
					$periodicity['typeof'] = $this->entity->getPeriodicity();
					break;
				case 'auto_limit':
					$periodicity['auto_limit'] = $this->entity->isAutoLimit();
					break;
				case 'min_quantity':
					$periodicity['min_quantity'] = $this->entity->getMinQuantity();
					break;
				case 'max_quantity':
					$periodicity['max_quantity'] = $this->entity->getMaxQuantityNumber();
					break;
				default:
					$periodicity[ $key ] = $this->{$key};
			}
		}

		$this->periodicity = $periodicity;
	}

	private function setDataToEntity() {
		$atts = array(
			'id'          => $this->id,
			'original_id' => MPHB()->translation()->getOriginalId(
				$this->id,
				MPHB()->postTypes()->service()->getPostType()
			),
		);

		$fields = static::getWritableFieldKeys();
		foreach ( $fields as $field ) {
			switch ( $field ) {
				case 'repeatability':
					$atts['repeat'] = $this->{$field};
					break;
				case 'periodicity':
					$atts['periodicity']   = $this->periodicity['typeof'];
					$atts['is_auto_limit'] = $this->periodicity['auto_limit'];
					$atts['min_quantity']  = $this->periodicity['min_quantity'];
					$atts['max_quantity']  = $this->periodicity['max_quantity'];
					break;
				default:
					$atts[ $field ] = $this->{$field};
			}
		}
		$this->entity = Service::create( $atts );
	}

	public function save() {
		$this->setDataToEntity();
		parent::save();
	}
}
