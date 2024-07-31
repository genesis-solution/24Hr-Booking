<?php

namespace MPHB\Repositories;

use \MPHB\Entities;

class SeasonRepository extends AbstractPostRepository {

	protected $type = 'season';

	/**
	 *
	 * @param array $atts
	 * @return Entities\Season[]
	 */
	public function findAll( $atts = array() ) {
		return parent::findAll( $atts );
	}

	/**
	 *
	 * @param int $id
	 * @return Entities\Season|null
	 */
	public function findById( $id, $force = false ) {
		return parent::findById( $id, $force );
	}

	public function mapPostToEntity( $post ) {

		$id = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;

		$startDate = get_post_meta( $id, 'mphb_start_date', true );
		$endDate   = get_post_meta( $id, 'mphb_end_date', true );
		$days      = get_post_meta( $id, 'mphb_days', true );

		$seasonArgs = array(
			'id'          => $id,
			'title'       => get_the_title( $id ),
			'description' => get_post_field( 'post_content', $id ),
			'start_date'  => ! empty( $startDate ) ? \DateTime::createFromFormat( 'Y-m-d', $startDate ) : null,
			'end_date'    => ! empty( $endDate ) ? \DateTime::createFromFormat( 'Y-m-d', $endDate ) : null,
			'days'        => ! empty( $days ) ? $days : array(),
		);

		return new Entities\Season( $seasonArgs );
	}

	/**
	 *
	 * @param Entities\Season $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ) {
		$postAtts = array(
			'ID'           => $entity->getId(),
			'post_metas'   => array(),
			'post_status'  => $entity->getId() ? get_post_status( $entity->getId() ) : 'publish',
			'post_title'   => $entity->getTitle(),
			'post_content' => $entity->getDescription(),
			'post_type'    => MPHB()->postTypes()->season()->getPostType(),
		);

		$postAtts['post_metas'] = array(
			'mphb_start_date' => ! is_null( $entity->getStartDate() ) ? $entity->getStartDate()->format( 'Y-m-d' ) : null,
			'mphb_end_date'   => ! is_null( $entity->getEndDate() ) ? $entity->getEndDate()->format( 'Y-m-d' ) : null,
			'mphb_days'       => $entity->getDays(),
		);

		return new Entities\WPPostData( $postAtts );
	}

}
