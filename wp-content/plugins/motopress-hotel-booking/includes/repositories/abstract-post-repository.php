<?php

namespace MPHB\Repositories;

use \MPHB\Persistences;

abstract class AbstractPostRepository {

	protected $type  = 'abstract';
	protected $items = array();

	/**
	 *
	 * @var Persistences\CPTPersistence
	 */
	protected $persistence;

	public function __construct( Persistences\CPTPersistence $persistence ) {
		$this->persistence = $persistence;
	}

	/**
	 * @return \MPHB\Persistences\CPTPersistence
	 *
	 * @since 3.7.2
	 */
	public function persistence() {
		return $this->persistence;
	}

	/**
	 *
	 * @param int  $id
	 * @param bool $force
	 * @return object
	 */
	public function findById( $id, $force = false ) {

		if ( empty( $this->items[ $id ] ) || $force ) {
			$post               = $this->persistence->getPost( $id );
			$entity             = ! is_null( $post ) ? $this->mapPostToEntity( $post ) : null;
			$this->items[ $id ] = $entity;
		}

		return $this->items[ $id ];
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return mixed
	 *
	 * TODO Bug: will find the latest entity with the right meta_key and null
	 *       meta_value; with a wrong meta_key will return null.
	 */
	public function findByMeta( $key, $value ) {
		$atts = array(
			'meta_key'   => $key,
			'meta_value' => $value,
		);
		return $this->findOne( $atts );
	}

	public function findOne( $atts = array() ) {
		$atts['posts_per_page'] = 1; // Force to search one item
		$items                  = $this->findAll( $atts );

		if ( ! empty( $items ) ) {
			return reset( $items );
		} else {
			return null;
		}
	}

	public function findAll( $atts = array() ) {

		$posts = $this->persistence->getPosts( $atts );

		return $this->mapPostsToEntity( $posts );
	}

	/**
	 *
	 * @param type $entity
	 * @return int
	 */
	public function save( &$entity ) {

		$postData = $this->mapEntityToPostData( $entity );

		$id = $this->persistence->createOrUpdate( $postData );

		if ( $id ) {

			$entity = $this->findById( $id, true );

			$this->items[ $id ] = $entity;
		}

		return $id;
	}

	/**
	 *
	 * @param type $entity
	 * @return int Id of deleted entity. 0 on failure.
	 */
	public function delete( $entity ) {

		$postData  = $this->mapEntityToPostData( $entity );
		$deletedId = $this->persistence->delete( $postData );

		if ( $deletedId ) {
			unset( $this->items[ $deletedId ] );
		}

		return $deletedId;
	}

	protected function mapPostsToEntity( $posts ) {
		do_action( "mphb_{$this->type}_repository_before_mapping_posts", $posts );

		$entities = array_map( array( $this, 'mapPostToEntity' ), $posts );
		$entities = array_filter( $entities );

		foreach ( $entities as $entity ) {
			$this->items[ $entity->getId() ] = $entity;
		}

		return $entities;
	}

	abstract function mapPostToEntity( $post );

	/**
	 * @return \MPHB\Entities\WPPostData
	 */
	abstract function mapEntityToPostData( $entity );
}
