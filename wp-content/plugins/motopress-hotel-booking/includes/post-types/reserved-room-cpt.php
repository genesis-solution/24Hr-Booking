<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\ManageCPTPages;
use \MPHB\Admin\EditCPTPages;
use \MPHB\Views;

class ReservedRoomCPT extends AbstractCPT {

	protected $postType = 'mphb_reserved_room';

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {

		$labels = array(
			'name'          => __( 'Reserved Accommodations', 'motopress-hotel-booking' ),
			'singular_name' => __( 'Reserved Accommodation', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'              => $labels,
			'map_meta_cap'        => true,
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'           => false,
			'capability_type'     => $this->getCapabilityType(),
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => false,
		);

		register_post_type( $this->postType, $args );
	}

	public function getFieldGroups() {
		return array();
	}

}
