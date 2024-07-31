<?php

namespace MPHB\Persistences;

class RatePersistence extends RoomTypeDependencedPersistence {

	protected function modifyQueryAtts( $atts ) {

		if ( ! isset( $atts['orderby'] ) ) {
			$atts['orderby'] = 'menu_order';
			$atts['order']   = 'ASC';
		}

		if ( isset( $atts['active'] ) ) {
			$atts['post_status'] = $atts['active'] ? array( 'publish' ) : array( 'draft' );
			unset( $atts['active'] );
		}

		$atts = parent::modifyQueryAtts( $atts );

		return $atts;
	}
}
