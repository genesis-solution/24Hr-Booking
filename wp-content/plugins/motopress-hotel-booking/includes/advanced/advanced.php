<?php
/**
 * Initializing Advanced functionality and admin panel to manage it
 *
 * @package MPHB\Advanced\Admin
 * @since 4.1.0
 */

namespace MPHB\Advanced;

use MPHB\Advanced\Admin\Admin;
use MPHB\Advanced\Api\Api;

class Advanced {

	/**
	 * @var Api
	 */
	protected $api;

	/**
	 * @var Admin
	 */
	protected $admin;

	public function __construct() {

		if ( is_admin() ) {
			$this->admin = new Admin();
		}

		$this->api = new Api();
		$this->api->init();
	}

	/**
	 * @return Admin
	 */
	public function getAdmin() {
		return $this->admin;
	}

	/**
	 * @return Api
	 */
	public function getApi() {
		return $this->api;
	}
}
