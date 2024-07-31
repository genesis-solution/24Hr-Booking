<?php

namespace MPHB\Persistences;

class CPTPersistence {

	protected $postType;

	public function __construct( $postType ) {
		$this->postType = $postType;
	}

	/**
	 * @param array $atts
	 *
	 * @since 3.7 added new filter - "{postType}_persistence_get_posts_atts".
	 * @since 3.8 parameter "suppress_filters" not forced to the value FALSE anymore.
	 */
	public function getPosts( $atts = array() ) {

		$defaultAtts = $this->getDefaultQueryAtts();

		$atts = array_merge( $defaultAtts, $atts );

		$atts = $this->modifyQueryAtts( $atts );

		$atts['ignore_sticky_posts'] = true;

		$atts = apply_filters( 'mphb_persistence_get_posts_atts', $atts, $this->postType );
		$atts = apply_filters( "{$this->postType}_persistence_get_posts_atts", $atts );

		if ( isset( $atts['meta_query'] ) and MPHB()->isWPVersion( '4.1', '<' ) ) {
			$atts['mphb_fix_meta_query'] = true;
			$atts['mphb_meta_query']     = $atts['meta_query'];

			unset( $atts['meta_query'] );
		}

		$this->addGetPostsFilters();

		do_action( '_mphb_persistence_before_get_posts', $atts );

		if ( isset( $atts['post__in'] ) && empty( $atts['post__in'] ) ) {
			$posts = array();
		} else {
			$posts = get_posts( $atts );
		}

		do_action( '_mphb_persistence_after_get_posts', $atts, $posts );

		$this->removeGetPostsFilters();

		return $posts;
	}

	protected function addGetPostsFilters() {
		add_filter( 'posts_where', array( $this, '_customizeGetPostsWhere' ), 10, 2 );
		add_filter( 'posts_join', array( $this, '_customizeGetPostsJoin' ), 10, 2 );
		add_filter( 'posts_distinct', array( $this, '_customizeGetPostsDistinct' ), 10, 2 );
	}

	protected function removeGetPostsFilters() {
		remove_filter( 'posts_where', array( $this, '_customizeGetPostsWhere' ), 10, 2 );
		remove_filter( 'posts_join', array( $this, '_customizeGetPostsJoin' ), 10, 2 );
		remove_filter( 'posts_distinct', array( $this, '_customizeGetPostsDistinct' ), 10, 2 );
	}

	/**
	 *
	 * @param string    $where
	 * @param \WP_Query $wp_query
	 */
	public function _customizeGetPostsWhere( $where, $wp_query ) {
		return $where;
	}

	/**
	 *
	 * @param string    $where
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function _customizeGetPostsJoin( $join, $wp_query ) {
		return $join;
	}

	/**
	 *
	 * @param string    $distinct
	 * @param \WP_Query $wp_query
	 * @return string
	 */
	public function _customizeGetPostsDistinct( $distinct, $wp_query ) {
		return $distinct;
	}

	/**
	 *
	 * @param array $atts
	 * @return int
	 */
	public function getCount( $atts = array() ) {
		$atts['fields']         = 'ids';
		$atts['posts_per_page'] = -1;

		return count( $this->getPosts( $atts ) );
	}

	public function getPost( $id ) {

		do_action( 'mphb_persistence_before_get_post_by_id', $id );

		$post = get_post( $id );

		do_action( 'mphb_persistence_after_get_post_by_id', $id, $post );

		return $post && $post->post_type === $this->postType ? $post : null;
	}

	/**
	 * Insert Post to DB
	 *
	 * @param array $postAttrs Attributes of post
	 * @return int The post ID on success. The value 0 on failure.
	 *
	 * @since 3.7.0 added new filter - "{postType}_persistence_new_post_data".
	 */
	public function create( \MPHB\Entities\WPPostData $postData ) {

		$postAtts = apply_filters( "{$this->postType}_persistence_new_post_data", $postData->getPostAtts() );

		$postId = wp_insert_post( $postAtts );

		if ( $postId ) {
			$postData->setID( $postId );
			$this->updatePostRelatedData( $postData );
		}

		return $postId;
	}

	/**
	 *
	 * @param \MPHB\Entities\WPPostData $postData
	 */
	protected function updatePostRelatedData( \MPHB\Entities\WPPostData $postData ) {
		foreach ( $postData->getPostMetas() as $postMetaName => $postMetaValue ) {
			if ( ! is_null( $postMetaValue ) ) {
				update_post_meta( $postData->getID(), $postMetaName, $postMetaValue );
			} else {
				delete_post_meta( $postData->getID(), $postMetaName );
			}
		}
		if ( $postData->hasFeaturedImage() ) {
			$featuredImage = $postData->getFeaturedImage();
			if ( $featuredImage ) {
				set_post_thumbnail( $postData->getID(), $featuredImage );
			} else {
				delete_post_thumbnail( $postData->getID() );
			}
		}
		foreach ( $postData->getTaxonomies() as $taxName => $terms ) {
			wp_set_post_terms( $postData->getID(), $terms, $taxName );
		}
	}

	/**
	 *
	 * @param \MPHB\Entities\WPPostData $postData
	 * @return int
	 */
	public function update( \MPHB\Entities\WPPostData $postData ) {
		wp_update_post( $postData->getPostAtts() );
		$this->updatePostRelatedData( $postData );
		return $postData->getID();
	}

	/**
	 *
	 * @param \MPHB\Entities\WPPostData $postData
	 * @return int
	 */
	public function createOrUpdate( \MPHB\Entities\WPPostData $postData ) {
		return $postData->hasID() ? $this->update( $postData ) : $this->create( $postData );
	}

	/**
	 *
	 * @param \MPHB\Entities\WPPostData $postData
	 * @return int Id of returned post. 0 on failure.
	 *
	 * @since 3.7.0 added new action - "{postType}_persistence_before_delete_post".
	 */
	public function delete( \MPHB\Entities\WPPostData $postData ) {
		do_action( "{$this->postType}_persistence_before_delete_post", $postData->getID(), $postData );

		$post = wp_delete_post( $postData->getID() );

		if ( is_a( $post, '\WP_Post' ) ) {
			$postId = $post->ID;
		} elseif ( is_array( $post ) ) {
			$postId = $post['ID'];
		} else {
			$postId = 0;
		}

		return $postId;
	}

	/**
	 *
	 * @param array $atts
	 * @return array
	 */
	protected function modifyQueryAtts( $atts ) {
		$atts['post_type'] = $this->postType;
		return apply_filters( "{$this->postType}_persistence_modify_query_atts", $atts );
	}

	/**
	 * @param array $customAtts Optional. Empty array by default.
	 * @return array
	 *
	 * @since 3.7.0 added optional parameter $customAtts.
	 */
	protected function getDefaultQueryAtts( $customAtts = array() ) {
		$atts = array_merge(
			array(
				'posts_per_page'   => -1,
				'post_status'      => array(
					'publish',
				),
				'post_type'        => $this->postType,
				'fields'           => 'ids',
				'suppress_filters' => false,
			),
			$customAtts
		);

		return apply_filters( "{$this->postType}_persistence_default_query_atts", $atts );
	}

	/**
	 *
	 * @param int[]|\WP_Post[] $posts Array of post ids or posts
	 * @return array Array id => title
	 */
	public function convertToIdTitleList( $posts ) {
		$list = array();

		foreach ( $posts as $post ) {
			$postId = ( is_a( $post, '\WP_Post' ) ) ? $post->ID : $post;

			$list[ $postId ] = get_the_title( $postId );
		}
		return $list;
	}

	public function getIdTitleList( $atts = array(), $extend = array() ) {

		$defaults = array(
			'fields'      => 'all',
			'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ),
		);

		$atts = array_merge( $defaults, $atts );

		$posts = $this->getPosts( $atts );

		$list = $extend;
		foreach ( $posts as $post ) {
			$list[ $post->ID ] = $post->post_title;
		}
		return $list;
	}

}
