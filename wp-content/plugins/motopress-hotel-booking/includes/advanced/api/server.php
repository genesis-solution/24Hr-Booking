<?php
/**
 *
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api;

use MPHB\Advanced\Api\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Class responsible for loading the REST API and all REST API namespaces.
 */
class Server {

	use SingletonTrait;

	const NAMESPACE_V1 = __NAMESPACE__ . '\Controllers\V1\\';

	/**
	 * @var array List of all included API controllers for version 1
	 */
	const CONTROLLERS_V1 = array(
		'bookings'                           => 'BookingsController',
		'booking_availability'               => 'BookingAvailabilityController',
		'payments'                           => 'PaymentsController',
		'accommodations'                     => 'AccommodationsController',
		'accommodation_types'                => 'AccommodationTypesController',
		'accommodation_type_categories'      => 'AccommodationTypeCategoriesController',
		'accommodation_type_tags'            => 'AccommodationTypeTagsController',
		'accommodation_type_amenities'       => 'AccommodationTypeAmenitiesController',
		'accommodation_type_services'        => 'AccommodationTypeServicesController',
		'accommodation_type_images'          => 'AccommodationTypeImagesController',
		'accommodation_type_attributes'      => 'AccommodationTypeAttributesController',
		'accommodation_type_attribute_terms' => 'AccommodationTypeAttributeTermsController',
		'coupons'                            => 'CouponsController',
		'rates'                              => 'RatesController',
		'seasons'                            => 'SeasonsController',
		'booking_rules'                      => 'BookingRulesController',
		'taxes_and_fees'                     => 'TaxesAndFeesController',
	);

	/**
	 * REST API namespaces and endpoints.
	 *
	 * @var array
	 */
	protected $controllers = array();

	/**
	 * Hook into WordPress ready to init the REST API as needed.
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'registerRestRoutes' ), 10 );
	}

	/**
	 * Register REST API routes.
	 */
	public function registerRestRoutes() {
		foreach ( $this->getRestNamespaces() as $namespace => $controllers ) {
			foreach ( $controllers as $controller_name => $controller_class ) {
				$this->controllers[ $namespace ][ $controller_name ] = new $controller_class();
				$this->controllers[ $namespace ][ $controller_name ]->register_routes();
			}
		}
	}

	/**
	 * Get API namespaces - new namespaces should be registered here.
	 *
	 * @return array List of Namespaces and Main controller classes.
	 */
	private function getRestNamespaces() {
		return apply_filters(
			'mphb_rest_api_get_rest_namespaces',
			array(
				'mphb/v1' => $this->getControllers( 1 ),
			)
		);
	}

	/**
	 * List of controllers whit their namespace for mphb/$version
	 *
	 * @param  int $version  Version of Api
	 *
	 * @return array
	 */
	private function getControllers( int $version ) {
		global $ver;
		$ver = $version;
		if ( ! defined( 'self::NAMESPACE_V' . $version ) ||
			 ! defined( 'self::CONTROLLERS_V' . $version ) ) {
			wp_die( 'Version API of ' . esc_html( $version ) . ' not found.' );
		}

		return array_map(
			function ( $controller ) {
				global $ver;
				return constant( 'self::NAMESPACE_V' . $ver ) . $controller;
			},
			constant( 'self::CONTROLLERS_V' . $ver )
		);
	}
}
