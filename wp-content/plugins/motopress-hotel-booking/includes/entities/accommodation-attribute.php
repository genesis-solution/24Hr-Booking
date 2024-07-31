<?php

namespace MPHB\Entities;

class AccommodationAttribute {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var bool
	 */
	private $enable_archives;

	/**
	 * @var bool
	 */
	private $visible_in_details;

	/**
	 * @var string
	 */
	private $default_sort_order;

	/**
	 * @var string
	 */
	private $default_text;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		if ( isset( $atts['id'] ) ) {
			$this->id = $atts['id'];
		}

		$this->status             = $atts['status'] ?? 'publish';
		$this->title              = $atts['title'] ?? '';
		$this->enable_archives    = $atts['enable_archives'] ?? false;
		$this->visible_in_details = $atts['visible_in_details'] ?? false;
		$this->default_sort_order = $atts['default_sort_order'] ?? '';
		$this->default_text       = $atts['default_text'] ?? '';
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
	public function getEnableArchives() {
		return $this->enable_archives;
	}

	/**
	 *
	 * @return bool
	 */
	public function getVisibleInDetails() {
		return $this->visible_in_details;
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultSortOrder() {
		return $this->default_sort_order;
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultText() {
		return $this->default_text;
	}

	/**
	 * @param string $title
	 */
	public function setTitle( $title ) {
		$this->title = $title;
	}

	/**
	 * @param bool $enableArchives
	 */
	public function setEnableArchives( $enableArchives ) {
		$this->enable_archives = $enableArchives;
	}

	/**
	 * @param bool $visibleInDetails
	 */
	public function setVisibleInDetails( $visibleInDetails ) {
		$this->visible_in_details = $visibleInDetails;
	}

	/**
	 * @param string $defaultSortOrder
	 */
	public function setDefaultSortOrder( $defaultSortOrder ) {
		$this->default_sort_order = $defaultSortOrder;
	}

	/**
	 * @param string $defaultText
	 */
	public function setDefaultText( $defaultText ) {
		$this->default_text = $defaultText;
	}
}
