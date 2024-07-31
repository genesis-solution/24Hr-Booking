<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\Customer;

class CustomerData extends AbstractData {
	/**
	 * @var Customer
	 */
	public $entity;

	public static function getProperties() {
		return array(
			'first_name' => array(
				'description' => 'First Name',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'last_name'  => array(
				'description' => 'Last Name',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'email'      => array(
				'description' => 'Email',
				'oneOf'       => array(
					array(
						'type'   => 'string',
						'format' => 'email',
					),
					array(
						'type'      => 'string',
						'maxLength' => 0,
					),
				),
				'context'     => array( 'view', 'edit' ),
			),
			'phone'      => array(
				'description' => 'Phone',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'country'    => array(
				'description' => 'Country code in ISO 3166-1 alpha-2 format',
				'type'        => 'string',
				'enum'        => array_merge(
					array( '' ),
					array_keys( MPHB()->settings()->main()->getCountriesBundle()->getCountriesList() )
				),
				'context'     => array( 'view', 'edit' ),
			),
			'state'      => array(
				'description' => 'State / County',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'city'       => array(
				'description' => 'City.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'zip'        => array(
				'description' => 'Postcode.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'address1'   => array(
				'description' => 'Address.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
		);
	}
}
