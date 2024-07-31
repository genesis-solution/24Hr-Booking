<?php

namespace MPHB\Persistences;

class RoomTypePersistence extends CPTPersistence {

	/**
	 * @param array $customAtts Optional. Empty array by default.
	 * @return array
	 *
	 * @since 3.7.0 added optional parameter $customAtts.
	 */
	protected function getDefaultQueryAtts( $customAtts = array() ) {

		$atts = array_merge(
			array(
				'orderby' => 'menu_order',
				'order'   => 'ASC',
			),
			$customAtts
		);

		return parent::getDefaultQueryAtts( $atts );
	}
}
