<?php

namespace MPHB\Bundles;

class UnitsBundle {

	private $labels;
	private $symbols;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 4 );
	}

	public function init() {
		$labels  = array(
			'm2'  => __( 'Square Meter', 'motopress-hotel-booking' ),
			'f2'  => __( 'Square Foot', 'motopress-hotel-booking' ),
			'yd2' => __( 'Square Yard', 'motopress-hotel-booking' ),
		);
		$symbols = array(
			'm2'  => __( 'm²', 'motopress-hotel-booking' ),
			'f2'  => __( 'ft²', 'motopress-hotel-booking' ),
			'yd2' => __( 'yd²', 'motopress-hotel-booking' ),
		);

		foreach ( $labels as $key => &$label ) {
			$label .= ' (' . $symbols[ $key ] . ')';
		}
		$this->labels  = $labels;
		$this->symbols = $symbols;
	}

	public function getLabels() {
		return $this->labels;
	}

	public function getSymbols() {
		return $this->symbols;
	}

	public function getLabel( $key ) {
		return isset( $this->labels[ $key ] ) ? $this->labels[ $key ] : '';
	}

	public function getSymbol( $key ) {
		return isset( $this->symbols[ $key ] ) ? $this->symbols[ $key ] : '';
	}

}
