<?php

namespace MPHB\Admin\ManageTaxPages;

class FacilityManageTaxPage extends ManageTaxPage {

	public function __construct( $taxType, $atts = array() ) {

		parent::__construct( $taxType, $atts );

		$this->description = __( 'These are accommodation amenities, generally free ones. E.g. air-conditioning, wifi.', 'motopress-hotel-booking' );
	}
}
