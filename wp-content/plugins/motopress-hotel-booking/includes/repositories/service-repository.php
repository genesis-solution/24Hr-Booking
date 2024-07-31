<?php

namespace MPHB\Repositories;

use \MPHB\Entities;
use \MPHB\Utils\ValidateUtils;

class ServiceRepository extends AbstractPostRepository {

	protected $type = 'service';

	/**
	 *
	 * @param int  $id
	 * @param bool $force Optional. FALSE by defautl.
	 * @return Entities\Service
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	public function mapPostToEntity( $post ) {

		if ( is_a( $post, '\WP_Post' ) ) {
			$id = $post->ID;
		} else {
			$id   = absint( $post );
			$post = get_post( $id );
		}

		$price = get_post_meta( $id, 'mphb_price', true );

		$periodicity = get_post_meta( $id, 'mphb_price_periodicity', true );
		if ( empty( $periodicity ) ) {
			$periodicity = 'once';
		}

		$minQuantity = get_post_meta( $id, 'mphb_min_quantity', true );
		$minQuantity = ValidateUtils::parseInt( $minQuantity, 1 );

		$maxQuantity = get_post_meta( $id, 'mphb_max_quantity', true );
		if ( $maxQuantity !== '' ) {
			$maxQuantity = ValidateUtils::parseInt( $maxQuantity, 0 );
		}

		$isAutoLimit = get_post_meta( $id, 'mphb_is_auto_limit', true );
		$isAutoLimit = ValidateUtils::validateBool( $isAutoLimit );

		$repeat = get_post_meta( $id, 'mphb_price_quantity', true );
		if ( empty( $repeat ) ) {
			$repeat = 'once';
		}

		$atts = array(
			'id'            => $id,
			'original_id'   => MPHB()->translation()->getOriginalId( $id, MPHB()->postTypes()->service()->getPostType() ),
			'title'         => get_the_title( $id ),
			'description'   => get_post_field( 'post_content', $id ),
			'price'         => $price ? floatval( $price ) : 0.0,
			'periodicity'   => $periodicity,
			'min_quantity'  => $minQuantity,
			'max_quantity'  => $maxQuantity,
			'is_auto_limit' => $isAutoLimit,
			'repeat'        => $repeat,
		);

		return Entities\Service::create( $atts );
	}

	/**
	 *
	 * @param Entities\Service $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {

		$postAtts = array(
			'ID'           => $entity->getId(),
			'post_metas'   => array(),
			'post_status'  => $entity->getId() ? get_post_status( $entity->getId() ) : 'publish',
			'post_type'    => MPHB()->postTypes()->service()->getPostType(),
			'post_title'   => $entity->getTitle(),
			'post_content' => $entity->getDescription(),
		);

		$postAtts['post_metas'] = array(
			'mphb_price'             => $entity->getPrice(),
			'mphb_price_periodicity' => $entity->getPeriodicity(),
			'mphb_min_quantity'      => $entity->getMinQuantity(),
			'mphb_max_quantity'      => $entity->getMaxQuantity(),
			'mphb_is_auto_limit'     => $entity->isAutoLimit(),
			'mphb_price_quantity'    => $entity->getRepeatability(),
		);

		return new Entities\WPPostData( $postAtts );
	}

}
