<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;

class RoomCPT extends EditableCPT {

	protected $postType = 'mphb_room';

	protected function createManagePage() {
		return new \MPHB\Admin\ManageCPTPages\RoomManageCPTPage( $this->postType );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {

		$labels = array(
			'name'                  => __( 'Accommodations', 'motopress-hotel-booking' ),
			'singular_name'         => __( 'Accommodation', 'motopress-hotel-booking' ),
			'add_new'               => _x( 'Add New', 'Add New Accommodation', 'motopress-hotel-booking' ),
			'add_new_item'          => __( 'Add New Accommodation', 'motopress-hotel-booking' ),
			'edit_item'             => __( 'Edit Accommodation', 'motopress-hotel-booking' ),
			'new_item'              => __( 'New Accommodation', 'motopress-hotel-booking' ),
			'view_item'             => __( 'View Accommodation', 'motopress-hotel-booking' ),
			'search_items'          => __( 'Search Accommodation', 'motopress-hotel-booking' ),
			'not_found'             => __( 'No accommodations found', 'motopress-hotel-booking' ),
			'not_found_in_trash'    => __( 'No accommodations found in Trash', 'motopress-hotel-booking' ),
			'all_items'             => __( 'Accommodations', 'motopress-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into accommodation description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this accommodation', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'description'          => __( 'This is where you can add new accommodations to your hotel.', 'motopress-hotel-booking' ),
			'public'               => false,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'query_var'            => false,
			'capability_type'      => $this->getCapabilityType(),
			'map_meta_cap'         => true,
			'has_archive'          => false,
			'hierarchical'         => false,
			'show_in_menu'         => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'supports'             => array( 'title', 'excerpt', 'page-attributes' ),
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
		);

		register_post_type( $this->postType, $args );
	}

	public function getFieldGroups() {

		global $pagenow;

		$previousPageURI = wp_get_referer();
		if ( false === $previousPageURI ) {
			$previousPageURI = '';
		}

		$generalGroup    = new Groups\MetaBoxGroup( 'General', __( 'Accommodation', 'motopress-hotel-booking' ), $this->postType );
		$roomTypeIdField = Fields\FieldFactory::create(
			'mphb_room_type_id',
			array(
				'type'     => 'select',
				'list'     => array( '' => __( '— Select —', 'motopress-hotel-booking' ) ) + MPHB()->getRoomTypePersistence()->getIdTitleList(
					array(
						'mphb_language' => 'original',
					)
				),
				'label'    => __( 'Accommodation Type', 'motopress-hotel-booking' ),
				'disabled' => $pagenow !== 'post-new.php' &&
					false === strpos( $previousPageURI, 'post-new.php' ),
				'required' => true,
			)
		);

		$generalGroup->addField( $roomTypeIdField );

		return array( $generalGroup );
	}
}
