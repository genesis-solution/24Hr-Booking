<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;
use \MPHB\Admin\ManageCPTPages;

class CouponCPT extends EditableCPT {

	protected $postType = 'mphb_coupon';

	public function __construct() {
		parent::__construct();
		add_action( 'mphb_booking_confirmed', array( $this, 'udpateCouponUsage' ), 10, 2 );

		add_filter( 'parent_file', array( $this, 'parent_file' ), 10, 1 );
	}

	public function getFieldGroups() {

		$mainGroup = new Groups\MetaBoxGroup( 'mphb_main', __( 'Coupon Information', 'motopress-hotel-booking' ), $this->postType, 'normal' );

		$mainGroupFields = array(
			Fields\FieldFactory::create(
				'_mphb_description',
				array(
					'type'    => 'textarea',
					'label'   => __( 'Description', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_type',
				array(
					'type'  => 'select',
					'label' => __( 'Type', 'motopress-hotel-booking' ),
					'list'  => array(
						''                   => __( 'Percentage', 'motopress-hotel-booking' ),
						'per_accomm'         => __( 'Fixed per accommodation per stay', 'motopress-hotel-booking' ),
						'per_accomm_per_day' => __( 'Fixed per accommodation per day', 'motopress-hotel-booking' ),
					),
				)
			),
			Fields\FieldFactory::create(
				'_mphb_amount',
				array(
					'type'        => 'number',
					'label'       => __( 'Coupon Amount', 'motopress-hotel-booking' ),
					'default'     => 0,
					'min'         => 0,
					'step'        => 0.01,
					'size'        => 'long-price',
					'required'    => true,
					'description' => __( 'Enter percent or fixed amount according to selected type.', 'motopress-hotel-booking' ),
				)
			),
			Fields\FieldFactory::create(
				'_mphb_expiration_date',
				array(
					'type'     => 'datepicker',
					'label'    => __( 'Expiration Date', 'motopress-hotel-booking' ),
					'readonly' => false,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_include_room_types',
				array(
					'type'    => 'multiple-select',
					'list'    => MPHB()->getRoomTypePersistence()->getIdTitleList(
						array(
							'mphb_language' => 'original',
						)
					),
					'label'   => __( 'Accommodation Types', 'motopress-hotel-booking' ),
					'default' => array(),
				)
			),
			Fields\FieldFactory::create(
				'_mphb_check_in_date_after',
				array(
					'type'     => 'datepicker',
					'label'    => __( 'Check-in After', 'motopress-hotel-booking' ),
					'readonly' => false,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_check_out_date_before',
				array(
					'type'     => 'datepicker',
					'label'    => __( 'Check-out Before', 'motopress-hotel-booking' ),
					'readonly' => false,
				)
			),

			Fields\FieldFactory::create(
				'_mphb_min_days_before_check_in',
				array(
					'type'        => 'number',
					'label'       => __( 'Min days before check-in', 'motopress-hotel-booking' ),
					'description' => __( 'For early bird discount. The coupon code applies if a booking is made in a minimum set number of days before the check-in date.', 'motopress-hotel-booking' ),
					'min'         => 0,
					'default'     => 0,
					'step'        => 1,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_max_days_before_check_in',
				array(
					'type'        => 'number',
					'label'       => __( 'Max days before check-in', 'motopress-hotel-booking' ),
					'description' => __( 'For last minute discount. The coupon code applies if a booking is made in a maximum set number of days before the check-in date.', 'motopress-hotel-booking' ),
					'min'         => 0,
					'default'     => 0,
					'step'        => 1,
				)
			),

			Fields\FieldFactory::create(
				'_mphb_min_nights',
				array(
					'type'    => 'number',
					'label'   => __( 'Minimum Days', 'motopress-hotel-booking' ),
					'min'     => 1,
					'default' => 1,
					'step'    => 1,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_max_nights',
				array(
					'type'    => 'number',
					'label'   => __( 'Maximum Days', 'motopress-hotel-booking' ),
					'min'     => 0,
					'default' => 0,
					'step'    => 1,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_usage_limit',
				array(
					'type'    => 'number',
					'label'   => __( 'Usage Limit', 'motopress-hotel-booking' ),
					'min'     => 0,
					'default' => 0,
					'step'    => 1,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_usage_count',
				array(
					'type'     => 'text',
					'size'     => 'small',
					'label'    => __( 'Usage Count', 'motopress-hotel-booking' ),
					'readonly' => true,
					'default'  => 0,
				)
			),
		);

		$mainGroup->addFields( $mainGroupFields );

		return array(
			$mainGroup,
		);
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Coupons', 'motopress-hotel-booking' ),
			'singular_name'      => __( 'Coupon', 'motopress-hotel-booking' ),
			'add_new'            => _x( 'Add New', 'Add New Coupon', 'motopress-hotel-booking' ),
			'add_new_item'       => __( 'Add New Coupon', 'motopress-hotel-booking' ),
			'edit_item'          => __( 'Edit Coupon', 'motopress-hotel-booking' ),
			'new_item'           => __( 'New Coupon', 'motopress-hotel-booking' ),
			'view_item'          => __( 'View Coupon', 'motopress-hotel-booking' ),
			'search_items'       => __( 'Search Coupon', 'motopress-hotel-booking' ),
			'not_found'          => __( 'No coupons found', 'motopress-hotel-booking' ),
			'not_found_in_trash' => __( 'No coupons found in Trash', 'motopress-hotel-booking' ),
			'all_items'          => __( 'All Coupons', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'public'               => false,
			'exclude_from_search'  => true,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'show_in_menu'         => false,
			'query_var'            => false,
			'capability_type'      => $this->getCapabilityType(),
			'map_meta_cap'         => true,
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'has_archive'          => false,
			'hierarchical'         => false,
			'supports'             => array( 'title' ),
		);

		register_post_type( $this->postType, $args );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param string                 $oldStatus
	 */
	public function udpateCouponUsage( $booking, $oldStatus ) {
		if ( $booking->getCouponId() ) {
			$coupon = MPHB()->getCouponRepository()->findById( $booking->getCouponId() );
			if ( $coupon ) {
				$coupon->increaseUsageCount();
				MPHB()->getCouponRepository()->save( $coupon );
			}
		}
	}

	protected function createEditPage() {
		return new \MPHB\Admin\EditCPTPages\CouponEditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	protected function createManagePage() {
		return new ManageCPTPages\CouponManageCPTPage( $this->postType );
	}

	/**
	 * Set correct active/current menu and submenu in the WordPress Admin menu
	 */
	public function parent_file( $parent_file ) {

		global $submenu_file, $current_screen;

		if ( $current_screen->post_type == $this->postType ) {
			$submenu_file = 'edit.php?post_type=' . $this->postType;
			$parent_file  = MPHB()->menus()->getMainMenuSlug();
		}

		return $parent_file;
	}

}
