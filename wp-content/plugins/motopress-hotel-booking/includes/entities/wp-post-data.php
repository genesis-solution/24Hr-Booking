<?php

namespace MPHB\Entities;

/**
 * @param array     $atts
 * @param int       $atts['ID']
 * @param string    $atts['post_title']
 * @param string    $atts['post_content']
 * @param string    $atts['post_excerpt']
 * @param string    $atts['post_status']
 * @param int       $atts['post_parent']
 * @param string    $atts['post_date']
 * @param int       $atts['featured_image']
 * @param array     $atts['post_metas']
 * @param array     $atts['taxonomies']
 */
class WPPostData {

	/**
	 *
	 * @var int
	 */
	private $ID;

	/**
	 *
	 * @var string
	 */
	private $post_title;

	/**
	 *
	 * @var string
	 */
	private $post_type = 'post';

	/**
	 *
	 * @var string
	 */
	private $post_content;

	/**
	 *
	 * @var string
	 */
	private $post_excerpt;

	/**
	 *
	 * @var string
	 */
	private $post_status;

	/**
	 *
	 * @var int
	 */
	private $post_parent;

	/**
	 *
	 * @var string
	 */
	private $post_date;

	/**
	 *
	 * @var int|null
	 */
	private $featured_image = null;

	/**
	 *
	 * @var array
	 */
	private $post_metas = array();

	/**
	 *
	 * @var array
	 */
	private $taxonomies = array();

	public function __construct( $atts = array() ) {
		foreach ( $atts as $attName => $att ) {
			$this->$attName = $att;
		}
	}

	public function getPostAtts() {

		$postData['post_type'] = $this->post_type;

		if ( isset( $this->ID ) ) {
			$postData['ID'] = $this->ID;
		}

		if ( isset( $this->post_status ) ) {
			$postData['post_status'] = $this->post_status;
		}
		if ( isset( $this->post_date ) ) {
			$postData['post_date'] = $this->post_date;
		}
		if ( isset( $this->post_content ) ) {
			$postData['post_content'] = $this->post_content;
		}
		if ( isset( $this->post_excerpt ) ) {
			$postData['post_excerpt'] = $this->post_excerpt;
		}
		if ( isset( $this->post_title ) ) {
			$postData['post_title'] = $this->post_title;
		}
		if ( isset( $this->post_parent ) ) {
			$postData['post_parent'] = $this->post_parent;
		}

		return $postData;
	}

	/**
	 *
	 * @return bool
	 */
	public function hasID() {
		return ! is_null( $this->ID );
	}

	public function getID() {
		return $this->ID;
	}

	public function getTitle() {
		return $this->post_title;
	}

	public function getPostMetas() {
		return $this->post_metas;
	}

	public function getTaxonomies() {
		return $this->taxonomies;
	}

	public function hasFeaturedImage() {
		return ! is_null( $this->featured_image );
	}

	public function getFeaturedImage() {
		return $this->featured_image;
	}

	public function getStatus() {
		return $this->post_status;
	}

	public function setID( $ID ) {
		$this->ID = $ID;
	}

	public function setTitle( $title ) {
		$this->post_title = $title;
	}

	public function setStatus( $status ) {
		$this->post_status = $status;
	}

	/**
	 * @param string $metaName
	 * @param mixed  $value
	 *
	 * @since 3.6.1
	 */
	public function setPostMeta( $metaName, $value ) {
		$this->post_metas[ $metaName ] = $value;
	}

}
