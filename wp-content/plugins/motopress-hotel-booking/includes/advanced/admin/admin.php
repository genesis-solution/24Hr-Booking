<?php
/**
 * Admin Init
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced\Admin;

use MPHB\Advanced\Admin\Tab\Tab;

class Admin {

	protected $tab;

	public function __construct() {

		$this->tab = new Tab();
	}

	public function getTab() {
		return $this->tab;
	}
}
