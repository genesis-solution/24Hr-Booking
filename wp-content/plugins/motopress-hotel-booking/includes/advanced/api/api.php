<?php
/**
 * API endpoint handler.
 *
 * This handles API related functionality in Motopress Hotel Booking.
 * The main REST API in Motopress Hotel Booking which is built on top of the WP REST API.
 *
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api;

class Api {

	/**
	 * This is domain for the REST API and takes
	 * first-order position in endpoint URLs.
	 *
	 * @var string
	 */
	const VENDOR = 'mphb';

	/**
	 * This is the major version for the REST API and takes
	 * first-order position in endpoint URLs.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * REST API authentication class instance.
	 *
	 * @var ApiAuthentication
	 */
	public $authentication;

	public function init() {
		Server::instance()->init();
		$this->authentication = new ApiAuthentication();
	}

}
