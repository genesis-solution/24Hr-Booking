<?php

namespace MPHB\Persistences;

class RoomTypeDependencedPersistence extends CPTPersistence {

	protected function modifyQueryAtts( $atts ) {
		$atts = $this->_changeToOriginalsRoomTypeIds( $atts );
		$atts = $this->_addRoomTypeCriteria( $atts );

		$atts = parent::modifyQueryAtts( $atts );

		return $atts;
	}

	protected function _changeToOriginalsRoomTypeIds( $atts ) {

		if ( ! isset( $atts['room_type_id'] ) ) {
			return $atts;
		}

		if ( is_array( $atts['room_type_id'] ) ) {
			$atts['room_type_id'] = array_map(
				function( $id ) {
					return MPHB()->translation()->getOriginalId( $id, MPHB()->postTypes()->roomType()->getPostType() );
				},
				$atts['room_type_id']
			);
		} else {
			$atts['room_type_id'] = MPHB()->translation()->getOriginalId( $atts['room_type_id'], MPHB()->postTypes()->roomType()->getPostType() );
		}
		return $atts;
	}

	protected function _addRoomTypeCriteria( $atts ) {

		if ( ! isset( $atts['room_type_id'] ) ) {
			return $atts;
		}

		if ( is_array( $atts['room_type_id'] ) ) {
			$queryPart = array(
				'key'     => 'mphb_room_type_id',
				'value'   => $atts['room_type_id'],
				'compare' => 'IN',
			);
		} else {
			$queryPart = array(
				'key'     => 'mphb_room_type_id',
				'value'   => $atts['room_type_id'],
				'compare' => '=',
			);
		}

		$atts['meta_query'] = mphb_add_to_meta_query( $queryPart, isset( $atts['meta_query'] ) ? $atts['meta_query'] : null );

		unset( $atts['room_type_id'] );

		return $atts;
	}

}
