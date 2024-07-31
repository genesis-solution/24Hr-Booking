<?php

/**
 * @since 4.0.0
 */

namespace MPHB\UsersAndRoles;

class Role {

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array
	 */
	public $capabilities;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @param array $atts
	 */
	public function __construct( $atts ) {
		$this->name         = isset( $atts['name'] ) ? $atts['name'] : '';
		$this->capabilities = isset( $atts['capabilities'] ) ? $atts['capabilities'] : array();
		$this->description  = isset( $atts['description'] ) ? esc_attr( $atts['description'] ) : '';
	}

	/**
	 * Creates new WPRole.
	 */
	public function add() {
		add_role(
			$this->name,
			$this->description,
			$this->capabilities
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @return array
	 */
	public function getCapabilities() {
		return $this->capabilities;
	}
}
