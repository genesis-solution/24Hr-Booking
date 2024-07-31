<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Advanced\Api\ApiHelper;
use MPHB\Entities\RoomType;

class AccommodationTypeData extends AbstractPostData {

	/**
	 * @var RoomType
	 */
	public $entity;

	public static function getRepository() {
		return MPHB()->getRoomTypeRepository();
	}

	public static function getProperties() {
		return array(
			'id'             => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'         => array(
				'description' => 'Accommodation type status.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'          => array(
				'description' => 'Title.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'description'    => array(
				'description' => 'Description.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'excerpt'        => array(
				'description' => 'Excerpt.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'adults'         => array(
				'description' => 'Adults.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'children'       => array(
				'description' => 'Children.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'total_capacity' => array(
				'description' => 'Total capacity.',
				'type'        => 'integer',
				'default'     => 0,
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'bed_type'       => array(
				'description' => 'Bed Type.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'size'           => array(
				'description' => 'Room square meters.',
				'type'        => 'number',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'view'           => array(
				'description' => 'City view, seaside, swimming pool etc.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'services'       => array(
				'description' => 'Service ids.',
				'type'        => 'array',
				'default'     => array(),
				'items'       => array(
					'description' => 'Service item.',
					'type'        => 'object',
					'required'    => true,
					'properties'  => array(
						'id'    => array(
							'description' => 'Service id.',
							'type'        => 'integer',
							'required'    => true,
						),
						'title' => array(
							'description' => 'Service title.',
							'type'        => 'string',
							'readonly'    => true,
						),
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'categories'     => array(
				'description' => 'Category terms.',
				'type'        => 'array',
				'items'       => array(
					'description' => 'Category item.',
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => 'Category id.',
							'type'        => 'integer',
							'required'    => true,
						),
						'name' => array(
							'description' => 'Category name.',
							'type'        => 'string',
							'readonly'    => true,
						),
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'tags'           => array(
				'description' => 'Tag terms.',
				'type'        => 'array',
				'items'       => array(
					'description' => 'Tag item.',
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => 'Tag id.',
							'type'        => 'integer',
							'required'    => true,
						),
						'name' => array(
							'description' => 'Tag name.',
							'type'        => 'string',
							'readonly'    => true,
						),
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'amenities'      => array(
				'description' => 'Amenity terms.',
				'type'        => 'array',
				'items'       => array(
					'description' => 'Amenity item.',
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => 'Amenity id.',
							'type'        => 'integer',
							'required'    => true,
						),
						'name' => array(
							'description' => 'Amenity name.',
							'type'        => 'string',
							'readonly'    => true,
						),
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'attributes'     => array(
				'description' => 'Attributes.',
				'type'        => 'array',
				'default'     => array(),
				'items'       => array(
					'description' => 'Attribute item.',
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'required'    => true,
					'properties'  => array(
						'id'    => array(
							'description' => 'Attribute id.',
							'type'        => 'integer',
							'required'    => true,
						),
						'title' => array(
							'description' => 'Attribute title.',
							'type'        => 'string',
							'readonly'    => true,
						),
						'terms' => array(
							'description' => 'Attribute terms.',
							'type'        => 'array',
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id'   => array(
										'description' => 'Attribute term id.',
										'type'        => 'integer',
										'required'    => true,
									),
									'name' => array(
										'description' => 'Attribute term name.',
										'type'        => 'string',
										'readonly'    => true,
									),
								),
							),
						),
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'images'         => array(
				'description' => 'Attached images.',
				'readonly'    => true,
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'    => array(
							'description' => 'Image id.',
							'type'        => 'integer',
						),
						'src'   => array(
							'description' => 'Image src.',
							'type'        => 'string',
						),
						'title' => array(
							'description' => 'Image title.',
							'type'        => 'string',
						),
						'alt'   => array(
							'description' => 'Image alt.',
							'type'        => 'string',
						),
					),
				),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
		);
	}

	/**
	 * @param $taxonomy string
	 * @param $termIds array
	 * @param $fieldName string
	 *
	 * @return \WP_Term[]
	 * @throws \Exception
	 */
	protected function formatTermIdsToTerms( $taxonomy, $termIds, $fieldName ) {
		if ( ! count( $termIds ) ) {
			return $termIds;
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'include'    => $termIds,
			'hide_empty' => false,
		);

		$termsExist = get_terms( $args );

		if ( count( $termsExist ) != count( $termIds ) ) {
			$amenityExistIds = wp_list_pluck( $termsExist, 'term_id' );
			$idsNotFound     = array_diff( $termIds, $amenityExistIds );
			throw new \Exception( wp_sprintf( 'You cannot attach non-existing %s: %l', $fieldName, $idsNotFound ) );
		}

		return $termsExist;
	}

	/**
	 * @param $termData  [ [{term_id} => {termTitle}] ]
	 *
	 * @return array [ [ "id" => (int), "name" => (string) ], ... ]
	 */
	protected function formatEntityTermDataToResponse( $termData ) {
		$terms = array();
		foreach ( $termData as $termId => $termName ) {
			$terms[] = array(
				'id'   => $termId,
				'name' => $termName,
			);
		}

		return $terms;
	}

	protected function getAdults() {
		return $this->entity->getAdultsCapacity();
	}

	protected function getChildren() {
		return $this->entity->getChildrenCapacity();
	}

	protected function getTotalCapacity() {
		return intval( $this->entity->getTotalCapacity() );
	}

	protected function getSize() {
		return intval( $this->entity->getSize() );
	}

	/**
	 * @return array [ ['id' => (int), 'name' => (string)] ]
	 */
	protected function getServices() {
		if ( isset( $this->services ) ) {
			return $this->services;
		}

		$services = $this->entity->getServices();

		if ( ! count( $services ) ) {
			return $services;
		}

		return array_map(
			function ( $service ) {
				$service = get_post( $service );

				return array(
					'id'    => $service->ID,
					'title' => $service->post_title,
				);
			},
			$services
		);
	}


	/**
	 * @return array [ ['id' => (int), 'name' => (string)] ]
	 */
	protected function getCategories() {
		if ( ! isset( $this->categories ) ) {
			$categoryTerms = $this->entity->getCategories();
		} else {
			$categoryTerms = $this->categories;
		}

		if ( ! count( $categoryTerms ) ) {
			return $categoryTerms;
		}

		$categoriesTermData = wp_list_pluck( $categoryTerms, 'name', 'term_id' );

		return $this->formatEntityTermDataToResponse( $categoriesTermData );
	}

	/**
	 * @return array [ ['id' => (int), 'name' => (string)] ]
	 */
	protected function getTags() {
		if ( ! isset( $this->tags ) ) {
			$tagTerms = $this->entity->getTags();
		} else {
			$tagTerms = $this->tags;
		}

		if ( ! count( $tagTerms ) ) {
			return $tagTerms;
		}

		$tagsTermData = wp_list_pluck( $tagTerms, 'name', 'term_id' );

		return $this->formatEntityTermDataToResponse( $tagsTermData );
	}

	/**
	 * @return array [ ['id' => (int), 'name' => (string)] ]
	 */
	protected function getAmenities() {
		if ( ! isset( $this->amenities ) ) {
			$amenityTerms = $this->entity->getFacilities();
		} else {
			$amenityTerms = $this->amenities;
		}

		if ( ! count( $amenityTerms ) ) {
			return $amenityTerms;
		}

		$amenitiesTermData = wp_list_pluck( $amenityTerms, 'name', 'term_id' );

		return $this->formatEntityTermDataToResponse( $amenitiesTermData );
	}

	/**
	 * @return array [
	 *  'id' => (int),
	 *  'title' => (string),
	 *  'terms' => ['id' => (int), 'name' => (string)]
	 * ]
	 */
	protected function getAttributes() {
		if ( isset( $this->attributes ) ) {
			$attributes = $this->attributes;
		} else {
			$attributes = $this->entity->getAttributes();
		}

		if ( ! count( $attributes ) ) {
			return $attributes;
		}

		$preparedAttributes = array();
		$atts               = array(
			'post_type'     => 'mphb_room_attribute',
			'post_name__in' => array_keys( $attributes ),
			'no_found_rows' => true,
		);
		$query              = new \WP_Query( $atts );
		$attributesExists   = $query->get_posts();

		if ( ! count( $attributesExists ) ) {
			return $preparedAttributes;
		}

		foreach ( $attributesExists as $attribute ) {
			$attributeName        = $attribute->post_name;
			$attributeItem        = $attributes[ $attributeName ];
			$preparedAttributes[] = array(
				'id'    => $attribute->ID,
				'title' => $attribute->post_title,
				'terms' => $this->formatEntityTermDataToResponse( $attributeItem ),
			);
		}

		return $preparedAttributes;
	}

	/**
	 * Get data of attached featured and gallery images to accommodation type
	 *
	 * @return array
	 */
	protected function getImages() {
		$images   = array();
		$imageIds = array();

		if ( $this->entity->hasFeaturedImage() ) {
			$imageIds[] = $this->entity->getFeaturedImageId();
		}
		if ( $this->entity->hasGallery() ) {
			$galleryIds = array_map( 'intval', $this->entity->getGalleryIds() );
			$imageIds   = array_merge( $imageIds, $galleryIds );
		}

		if ( ! count( $imageIds ) ) {
			return $images;
		}

		foreach ( $imageIds as $imageId ) {
			$image = get_post( $imageId );
			if ( $image ) {
				$images[] = array(
					'id'    => $image->ID,
					'src'   => wp_get_attachment_url( $image->ID ),
					'title' => get_the_title( $image ),
					'alt'   => get_post_meta( $image->ID, '_wp_attachment_image_alt', true ),
				);
			}
		}

		return $images;
	}

	protected function setCategories( $categories ) {
		$categoryIds = wp_list_pluck( $categories, 'id' );
		$taxonomy    = MPHB()->postTypes()->roomType()->getCategoryTaxName();

		$this->categories = $this->formatTermIdsToTerms( $taxonomy, $categoryIds, 'categories' );
	}

	protected function setTags( $tags ) {
		$tagIds   = wp_list_pluck( $tags, 'id' );
		$taxonomy = MPHB()->postTypes()->roomType()->getTagTaxName();

		$this->tags = $this->formatTermIdsToTerms( $taxonomy, $tagIds, 'tags' );
	}

	protected function setAmenities( $amenities ) {
		$amenityIds = wp_list_pluck( $amenities, 'id' );
		$taxonomy   = MPHB()->postTypes()->roomType()->getFacilityTaxName();

		$this->amenities = $this->formatTermIdsToTerms( $taxonomy, $amenityIds, 'amenities' );
	}

	protected function setAttributes( $attributes ) {
		$preparedAttributes = array();
		if ( count( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$post = get_post( $attribute['id'] );

				$taxonomyName = mphb_attribute_taxonomy_name( $post->post_name );
				foreach ( $attribute['terms'] as $termData ) {
					$term = get_term( $termData['id'], $taxonomyName );
					if ( ! $term ) {
						throw new \Exception( 'Attribute with id ' . $termData['id'] . ' not found for attributes: ' . $attribute['id'] );
					}
					$preparedAttributes[ $post->post_name ][ $term->term_id ] = $term->name;
				}
			}
		}
		$this->attributes = $preparedAttributes;
	}

	protected function setServices( $services ) {
		$serviceIds = array();
		if ( count( $services ) ) {
			$serviceIds = wp_list_pluck( $services, 'id' );
		}
		$this->services = $serviceIds;
	}

	private function setDataToEntity() {
		$atts = array(
			'id'          => $this->id,
			'original_id' => $this->original_id,
			'status'      => $this->status ?: 'publish',
		);

		$images          = $this->images;
		$featuredImageId = array();
		$galleryImageIds = array();

		if ( count( $images ) ) {
			$imageIds        = wp_list_pluck( $images, 'id' );
			$featuredImageId = array_shift( $imageIds );
			$galleryImageIds = $imageIds;
		}
		$atts['image_id']    = $featuredImageId;
		$atts['gallery_ids'] = $galleryImageIds;

		$fields = static::getWritableFieldKeys();
		foreach ( $fields as $field ) {
			$getterCallback = 'get' . ApiHelper::convertSnakeToCamelString( $field );
			switch ( $field ) {
				case 'amenities':
					$atts['facilities'] = isset( $this->{$field} ) ? $this->{$field} : $this->entity->getFacilities();
					break;
				case 'services':
					$atts['services_ids'] = isset( $this->{$field} ) ? $this->{$field} : $this->entity->{$getterCallback}();
					break;
				case 'attributes':
				case 'tags':
				case 'categories':
					$atts[ $field ] = isset( $this->{$field} ) ? $this->{$field} : $this->entity->{$getterCallback}();
					break;
				default:
					$atts[ $field ] = $this->{$field};
			}
			if ( isset( $this->{$field} ) ) {
				unset( $this->{$field} );
			}
		}
		$this->entity = new RoomType( $atts );
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function save() {
		$this->setDataToEntity();
		if ( $this->isDataChanged() ) {
			parent::save();
		}

		return true;
	}
}
