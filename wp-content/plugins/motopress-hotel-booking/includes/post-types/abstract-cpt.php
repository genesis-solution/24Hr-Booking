<?php

namespace MPHB\PostTypes;

abstract class AbstractCPT {

	protected $postType;

	/**
	 *
	 * @var string
	 */
	protected $capability = 'edit_post';

	/**
	 *
	 * @var \MPHB\Admin\Groups\MetaBoxGroup[]
	 */
	protected $fieldGroups = array();

	public function __construct() {
		$this->addActions();
	}

	protected function addActions() {
		add_action( 'init', array( $this, 'register' ), 6 );
	}

	abstract public function register();

	public function getPostType() {
		return $this->postType;
	}

	/**
	 * Create an array of capability types depending on a post type name.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function getCapabilityType() {
		$cap        = $this->postType;
		$cap_plural = sprintf( '%ss', $cap );
		return array( $cap, $cap_plural );
	}

}
