<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;

class SeasonCPT extends EditableCPT {

	protected $postType = 'mphb_season';

	protected function createManagePage() {
		return new \MPHB\Admin\ManageCPTPages\SeasonManageCPTPage( $this->postType );
	}

	/**
	 *
	 * @since 3.9.6 - dependency between mphb_start_date and mphb_end_date fields
	 */
	public function getFieldGroups() {
		$generalGroup  = new Groups\MetaBoxGroup( 'General', __( 'Season Info', 'motopress-hotel-booking' ), $this->postType, 'normal' );
		$generalFields = array();

		$generalFields[] = Fields\FieldFactory::create(
			'mphb_start_date',
			array(
				'type'       => 'datepicker',
				'label'      => __( 'Start date', 'motopress-hotel-booking' ),
				'dependency' => array(
					'as_min' => 'mphb_end_date',
				),
				'required'   => true,
				'readonly'   => false,
			)
		);

		$generalFields[] = Fields\FieldFactory::create(
			'mphb_end_date',
			array(
				'type'     => 'datepicker',
				'label'    => __( 'End date', 'motopress-hotel-booking' ),
				'required' => true,
				'readonly' => false,
			)
		);

		$generalFields[] = Fields\FieldFactory::create(
			'mphb_days',
			array(
				'type'        => 'multiple-select',
				'label'       => __( 'Applied for days', 'motopress-hotel-booking' ),
				'list'        => \MPHB\Utils\DateUtils::getDaysList(),
				'required'    => true,
				'default'     => array_keys( \MPHB\Utils\DateUtils::getDaysList() ),
				'description' => __( 'Hold Ctrl / Cmd to select multiple.', 'motopress-hotel-booking' ),
			)
		);

		$generalGroup->addFields( $generalFields );

		return array( $generalGroup );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {

		$labels = array(
			'name'                  => __( 'Seasons', 'motopress-hotel-booking' ),
			'singular_name'         => __( 'Season', 'motopress-hotel-booking' ),
			'add_new'               => _x( 'Add New', 'Add New Season', 'motopress-hotel-booking' ),
			'add_new_item'          => __( 'Add New Season', 'motopress-hotel-booking' ),
			'edit_item'             => __( 'Edit Season', 'motopress-hotel-booking' ),
			'new_item'              => __( 'New Season', 'motopress-hotel-booking' ),
			'view_item'             => __( 'View Season', 'motopress-hotel-booking' ),
			'search_items'          => __( 'Search Season', 'motopress-hotel-booking' ),
			'not_found'             => __( 'No seasons found', 'motopress-hotel-booking' ),
			'not_found_in_trash'    => __( 'No seasons found in Trash', 'motopress-hotel-booking' ),
			'all_items'             => __( 'Seasons', 'motopress-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into season description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this season', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'description'          => __( 'This is where you can add new seasons.', 'motopress-hotel-booking' ),
			'public'               => false,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'query_var'            => false,
			'capability_type'      => $this->getCapabilityType(),
			'map_meta_cap'         => true,
			'has_archive'          => false,
			'hierarchical'         => false,
			'show_in_menu'         => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'supports'             => array( 'title' ),
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
		);

		register_post_type( $this->postType, $args );
	}

}
