<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\ManageCPTPages;
use \MPHB\Admin\EditCPTPages;

class RateCPT extends EditableCPT {

	protected $postType = 'mphb_rate';

	protected function createEditPage() {
		return new EditCPTPages\RateEditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	protected function createManagePage() {
		return new ManageCPTPages\RateManageCPTPage( $this->postType );
	}

	public function getFieldGroups() {
		$generalGroup = new Groups\MetaBoxGroup( 'rate_cpt', __( 'Rate Info', 'motopress-hotel-booking' ), $this->postType, 'normal' );

		$generalFields = array(
			Fields\FieldFactory::create(
				'mphb_room_type_id',
				array(
					'type'     => 'select',
					'label'    => __( 'Accommodation Type', 'motopress-hotel-booking' ),
					'list'     => array( '' => __( '— Select —', 'motopress-hotel-booking' ) ) + MPHB()->getRoomTypePersistence()->getIdTitleList(
						array(
							'mphb_language' => 'original',
						)
					),
					'required' => true,
				)
			),
			Fields\FieldFactory::create(
				'mphb_season_prices',
				array(
					'type'              => 'complex',
					'label'             => false,
					'fields'            => array(
						Fields\FieldFactory::create(
							'season',
							array(
								'type'     => 'select',
								'label'    => __( 'Season', 'motopress-hotel-booking' ),
								'list'     => MPHB()->getSeasonPersistence()->getIdTitleList(
									array(
										'orderby'       => 'ID',
										'order'         => 'ASC',
										'mphb_language' => 'original',
									)
								),
								'required' => true,
							)
						),
						Fields\FieldFactory::create(
							'price',
							array(
								'type'  => 'variable-pricing',
								'label' => __( 'Price', 'motopress-hotel-booking' ),
							)
						),
					),
					'sortable'          => true,
					'separate_sortable' => true,
					'description'       => __( 'Move price to top to set higher priority.', 'motopress-hotel-booking' ),
					'add_label'         => __( 'Add New Season Price', 'motopress-hotel-booking' ),
					'classes'           => 'mphb-vertical-top',
				)
			),
			Fields\FieldFactory::create(
				'mphb_description',
				array(
					'type'         => 'textarea',
					'label'        => __( 'Description', 'motopress-hotel-booking' ),
					'required'     => false,
					'rows'         => 5,
					'description'  => __( 'Will be displayed on the checkout page.', 'motopress-hotel-booking' ),
					'translatable' => true,
				)
			),
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
			// translators: The value a hotel wishes to sell their rooms. Also called the Cost, Value, Tariff or Room charge.
			'name'                  => __( 'Rates', 'motopress-hotel-booking' ),
			'singular_name'         => __( 'Rate', 'motopress-hotel-booking' ),
			'add_new'               => _x( 'Add New', 'Add New Rate', 'motopress-hotel-booking' ),
			'add_new_item'          => __( 'Add New Rate', 'motopress-hotel-booking' ),
			'edit_item'             => __( 'Edit Rate', 'motopress-hotel-booking' ),
			'new_item'              => __( 'New Rate', 'motopress-hotel-booking' ),
			'view_item'             => __( 'View Rate', 'motopress-hotel-booking' ),
			'search_items'          => __( 'Search Rate', 'motopress-hotel-booking' ),
			'not_found'             => __( 'No rates found', 'motopress-hotel-booking' ),
			'not_found_in_trash'    => __( 'No rates found in Trash', 'motopress-hotel-booking' ),
			'all_items'             => __( 'Rates', 'motopress-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into rate description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this rate', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'description'          => __( 'This is where you can add new rates.', 'motopress-hotel-booking' ),
			'public'               => false,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'query_var'            => false,
			'capability_type'      => $this->getCapabilityType(),
			'has_archive'          => false,
			'hierarchical'         => false,
			'show_in_menu'         => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'supports'             => array( 'page-attributes', 'title' ),
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'map_meta_cap'         => true,
		);

		register_post_type( $this->postType, $args );
	}

}
