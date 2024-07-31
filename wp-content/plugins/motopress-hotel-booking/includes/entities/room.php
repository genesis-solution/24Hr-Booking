<?php

namespace MPHB\Entities;

class Room {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var int
	 */
	private $room_type_id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $description;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		if ( isset( $atts['id'] ) ) {
			$this->id = $atts['id'];
		}

		$this->status       = isset( $atts['status'] ) ? $atts['status'] : 'publish';
		$this->room_type_id = isset( $atts['room_type_id'] ) ? $atts['room_type_id'] : 0;
		$this->title        = isset( $atts['title'] ) ? $atts['title'] : '';
		$this->description  = isset( $atts['description'] ) ? $atts['description'] : '';
	}

	/**
	 *
	 * @return int
	 */
	public function getRoomTypeId() {
		return $this->room_type_id;
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Retrieve link for room post.
	 *
	 * @return string|false
	 */
	public function getLink() {
		return get_permalink( $this->id );
	}

	/**
	 * @return string[] [%syncId% => %calendarUrl%]
	 */
	public function getSyncUrls() {
		return MPHB()->getSyncUrlsRepository()->getUrls( $this->id );
	}

	/**
	 * @param  string $title
	 */
	public function setTitle( $title ) {
		$this->title = $title;
	}

	/**
	 * @param  string $description
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}

	public function setSyncUrls( $urls ) {
		MPHB()->getSyncUrlsRepository()->updateUrls( $this->id, $urls );
	}
}
