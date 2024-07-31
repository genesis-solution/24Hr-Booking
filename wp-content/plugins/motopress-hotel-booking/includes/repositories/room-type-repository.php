<?php

namespace MPHB\Repositories;

use \MPHB\Entities;

class RoomTypeRepository extends AbstractPostRepository {

	protected $type = 'room_type';

	/**
	 * @param Entities\RoomType $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$postAtts = array(
			'ID'             => $entity->getId(),
			'post_status'    => $entity->getStatus(),
			'post_title'     => $entity->getTitle(),
			'post_content'   => $entity->getDescription(),
			'post_excerpt'   => $entity->getExcerpt(),
			'post_type'      => MPHB()->postTypes()->roomType()->getPostType(),
			'featured_image' => $entity->getFeaturedImageId(),
		);

		$postAtts['post_metas'] = array(
			'mphb_adults_capacity'   => $entity->getAdultsCapacity(),
			'mphb_children_capacity' => $entity->getChildrenCapacity(),
			'mphb_total_capacity'    => $entity->getTotalCapacity(),
			'mphb_bed'               => $entity->getBedType(),
			'mphb_size'              => $entity->getSize(),
			'mphb_view'              => $entity->getView(),
			'mphb_services'          => $entity->getServices(),
		);

		$postAtts['taxonomies'] = array(
			MPHB()->postTypes()->roomType()->getTagTaxName()      => wp_list_pluck( $entity->getTags(), 'term_id' ),
			MPHB()->postTypes()->roomType()->getCategoryTaxName() => wp_list_pluck( $entity->getCategories(), 'term_id' ),
			MPHB()->postTypes()->roomType()->getFacilityTaxName() => wp_list_pluck( $entity->getFacilities(), 'term_id' ),
		);

		foreach ( $entity->getAttributes() as $attributeName => $attributeTermIds ) {
			$attributeTaxonomy                            = mphb_attribute_taxonomy_name( $attributeName );
			$postAtts['taxonomies'][ $attributeTaxonomy ] = $attributeTermIds;
		}

		return new Entities\WPPostData( $postAtts );
	}

	function mapPostToEntity( $post ) {

		$post       = ( is_a( $post, '\WP_Post' ) ) ? $post : get_post( $post );
		$id         = $post->ID;
		$originalId = MPHB()->translation()->getOriginalId( $id, MPHB()->postTypes()->roomType()->getPostType() );

		$adults = get_post_meta( $id, 'mphb_adults_capacity', true );
		$adults = (int) ( ! empty( $adults ) ? $adults : MPHB()->settings()->main()->getMinAdults() );

		$children = get_post_meta( $id, 'mphb_children_capacity', true );
		$children = (int) ( false !== $children ? $children : MPHB()->settings()->main()->getMinChildren() );

		$total = get_post_meta( $id, 'mphb_total_capacity', true );

		if ( $total !== '' ) {
			$total = intval( $total );
		}

		$size = get_post_meta( $id, 'mphb_size', true );
		$size = ! empty( $size ) ? (float) $size : 0.0;

		$services = get_post_meta( $id, 'mphb_services', true );
		$services = ! empty( $services ) ? $services : array();

		$gallery = get_post_meta( $id, 'mphb_gallery', true );
		$gallery = ! empty( $gallery ) ? explode( ',', $gallery ) : array();

		$atts = array(
			'id'             => $id,
			'original_id'    => $originalId,
			'title'          => $post->post_title,
			'description'    => $post->post_content,
			'excerpt'        => $post->post_excerpt,
			'adults'         => $adults,
			'children'       => $children,
			'total_capacity' => $total,
			'bed_type'       => get_post_meta( $id, 'mphb_bed', true ),
			'size'           => $size,
			'view'           => get_post_meta( $id, 'mphb_view', true ),
			'services_ids'   => $services,
			'image_id'       => get_post_thumbnail_id( $id ),
			'gallery_ids'    => $gallery,
			'categories'     => wp_get_post_terms( $id, MPHB()->postTypes()->roomType()->getCategoryTaxName() ),
			'tags'           => wp_get_post_terms( $id, MPHB()->postTypes()->roomType()->getTagTaxName() ),
			'facilities'     => wp_get_post_terms( $id, MPHB()->postTypes()->roomType()->getFacilityTaxName() ),
			'attributes'     => $this->getAttributes( $id ),
			'status'         => get_post_status( $originalId ),
		);

		return new Entities\RoomType( $atts );
	}

	protected function getAttributes( $roomTypeId ) {

		global $mphbAttributes;

		$attributes = array();

		foreach ( $mphbAttributes as $attribute ) {

			$attributeName = $attribute['attributeName'];
			$taxonomyName  = $attribute['taxonomyName'];

			$terms = wp_get_post_terms( $roomTypeId, $taxonomyName );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$terms                        = array_combine( wp_list_pluck( $terms, 'term_id' ), wp_list_pluck( $terms, 'name' ) );
				$attributes[ $attributeName ] = $terms;
			}
		}

		return $attributes;
	}

	public function getIdTitleList( $atts = array() ) {

		$defaults = array(
			'fields'          => 'all',
			// 'orderby'        => 'ID',
			// 'order'          => 'ASC',
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ),
		);

		$atts = array_merge( $defaults, $atts );

		$posts = $this->persistence->getPosts( $atts );

		$list = array();
		foreach ( $posts as $post ) {
			$list[ $post->ID ] = $post->post_title;
		}
		return $list;
	}

	/**
	 * @param int  $id
	 * @param bool $force Optional.
	 * @return Entities\RoomType
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}
}
