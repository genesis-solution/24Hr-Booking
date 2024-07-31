<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Entities\AccommodationAttribute;
use MPHB\Repositories\AttributeRepository;

class AccommodationTypesAttributeData extends AbstractPostData {

	/**
	 * @var AccommodationAttribute
	 */
	public $entity;

	/**
	 * @return AttributeRepository
	 */
	public static function getRepository() {
		return MPHB()->getAttributeRepository();
	}

	public static function getProperties() {
		return array(
			'id'                 => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'             => array(
				'description' => 'Accommodation status.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'              => array(
				'description' => 'Title.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
			'enable_archives'    => array(
				'description' => 'Link the attribute to an archive page with all accommodation types that have this attribute.',
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
			),
			'visible_in_details' => array(
				'description' => 'Display the attribute in details section of an accommodation type.',
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
			),
			'default_sort_order' => array(
				'description' => 'Default Sort Order.',
				'type'        => 'string',
				'enum'        => array( 'custom', 'name', 'numeric', 'id' ),
				'context'     => array( 'view', 'edit' ),
			),
			'default_text'       => array(
				'description' => 'Default text.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
			),
		);
	}

}
