<?php

class Milenia_Integrations {

	private $supported_integrations;
	public $integrations;

	public function __construct() {
		$this->supported_integrations = array(
			'woocommerce' => array(
				class_exists( 'WooCommerce' ),
				'Milenia_WooCommerce',
			)
		);

		$this->load_integrations();
	}

	public function has( $key ) {
		return isset( $this->integrations[ $key ] );
	}

	public function get( $key ) {
		if ( ! $this->has( $key ) ) {
			return false;
		}

		return $this->integrations[ $key ];
	}

	public function add( $key, $class ) {
		$this->integrations[ $key ] = $class;
	}

	private function load_integrations() {
		foreach ( $this->supported_integrations as $key => $integration ) {
			if ( $integration[0] ) {
				require_once( get_theme_file_path( 'includes/integrations/' . trailingslashit( $key ) . 'class-' . $key . '.php' ) );
				$class = new $integration[1];
				$this->add( $key, $class );
			}
		}
	}

}
