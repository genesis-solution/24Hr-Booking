<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\ManageCPTPages;
use \MPHB\Admin\EditCPTPages;

class BookingCPT extends EditableCPT {

	protected $postType = 'mphb_booking';
	protected $statuses;
	protected $logs;

	public function __construct() {

		parent::__construct();

		$this->statuses = new BookingCPT\Statuses( $this->postType );
		$this->logs     = new BookingCPT\Logs( $this->postType );
		add_action( 'delete_post', array( $this, 'removeReservedRooms' ) );
	}

	protected function createEditPage() {
		return new EditCPTPages\BookingEditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	protected function createManagePage() {
		return new ManageCPTPages\BookingManageCPTPage( $this->postType );
	}

	public function getFieldGroups() {

		$mainGroup = new Groups\MetaBoxGroup( 'mphb_main', __( 'Booking Information', 'motopress-hotel-booking' ), $this->postType, 'normal' );

		$mainGroupFields = array(
			Fields\FieldFactory::create(
				'_id',
				array(
					'type'     => 'post-id',
					'label'    => __( 'ID', 'motopress-hotel-booking' ),
					'size'     => 'all-options',
					'readonly' => true,
				)
			),
			Fields\FieldFactory::create(
				'mphb_check_in_date',
				array(
					'type'     => 'datepicker',
					'label'    => __( 'Check-in Date', 'motopress-hotel-booking' ),
					'required' => true,
					'readonly' => true,
				)
			),
			Fields\FieldFactory::create(
				'mphb_check_out_date',
				array(
					'type'     => 'datepicker',
					'label'    => __( 'Check-out Date', 'motopress-hotel-booking' ),
					'required' => true,
					'readonly' => true,
				)
			),
		);

		$bookingId = mphb_get_editing_post_id();
		$booking   = $bookingId > 0 ? mphb_get_booking( $bookingId ) : null;

		if ( ! is_null( $booking ) && ! $booking->isImported() ) {
			$mainGroupFields[] = Fields\FieldFactory::create(
				'mphb_edit_dates',
				array(
					'type'        => 'link-button',
					'inner_label' => __( 'Edit Dates', 'motopress-hotel-booking' ),
					'href'        => MPHB()->getEditBookingMenuPage()->getUrl( array( 'booking_id' => $bookingId ) ),
				)
			);
		}

		$mainGroup->addFields( $mainGroupFields );

		$customerGroup = new Groups\MetaBoxGroup( 'mphb_customer', __( 'Customer Information', 'motopress-hotel-booking' ), $this->postType );

		$customerGroupFields = array(
			Fields\FieldFactory::create(
				'mphb_first_name',
				array(
					'type'    => 'text',
					'label'   => __( 'First Name', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_last_name',
				array(
					'type'    => 'text',
					'label'   => __( 'Last Name', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_email',
				array(
					'type'    => 'email',
					'label'   => __( 'Email', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_phone',
				array(
					'type'    => 'text',
					'label'   => __( 'Phone', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_country',
				array(
					'type'    => 'select',
					'list'    => array( '' => __( '— Select —', 'motopress-hotel-booking' ) ) + MPHB()->settings()->main()->getCountriesBundle()->getCountriesList(),
					'label'   => __( 'Country', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_address1',
				array(
					'type'    => 'text',
					'label'   => __( 'Address', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_city',
				array(
					'type'    => 'text',
					'label'   => __( 'City', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_state',
				array(
					'type'    => 'text',
					'label'   => __( 'State / County', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_zip',
				array(
					'type'    => 'text',
					'label'   => __( 'Postcode', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'mphb_note',
				array(
					'type'  => 'textarea',
					'rows'  => 8,
					'label' => __( 'Customer Note', 'motopress-hotel-booking' ),
				)
			),
		);

		$customerGroup->addFields( $customerGroupFields );

		$miscGroup = new Groups\MetaBoxGroup( 'mphb_other', __( 'Additional Information', 'motopress-hotel-booking' ), $this->postType );

		$miscGroupFields = array(
			Fields\FieldFactory::create(
				'mphb_coupon_id',
				array(
					'type'  => 'select',
					'label' => __( 'Coupon', 'motopress-hotel-booking' ),
					'list'  => array( '' => __( '— Select —', 'motopress-hotel-booking' ) ) + MPHB()->getCouponPersistence()->getIdTitleList(),
				)
			),
			Fields\FieldFactory::create(
				'mphb_total_price',
				array(
					'type'  => 'total-price',
					'size'  => 'long-price',
					'label' => __( 'Total Booking Price', 'motopress-hotel-booking' ),
				)
			),
			Fields\FieldFactory::create(
				'_mphb_booking_price_breakdown',
				array(
					'type'  => 'price-breakdown',
					'label' => __( 'Price Breakdown', 'motopress-hotel-booking' ),
				)
			),
		);

		$miscGroup->addFields( $miscGroupFields );

		$internalNoteGroup = new Groups\MetaBoxGroup( 'mphb_internal_notes', __( 'Notes', 'motopress-hotel-booking' ), $this->postType, 'advanced', 'low', array( 'wide' => true ) );

		$internalNoteGroupFields = array(
			Fields\FieldFactory::create(
				'_mphb_booking_internal_notes',
				array(
					'type'      => 'notes-list',
					'default'   => array(),
					'fields'    => array(
						Fields\FieldFactory::create(
							'note',
							array(
								'type'    => 'textarea',
								'default' => '',
								'label'   => __( 'Note', 'motopress-hotel-booking' ),
							)
						),
					),
					'add_label' => __( 'Add new', 'motopress-hotel-booking' ),
				)
			),
		);

		$internalNoteGroup->addFields( $internalNoteGroupFields );

		return array(
			$mainGroup,
			$customerGroup,
			$miscGroup,
			$internalNoteGroup,
		);
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {

		$labels = array(
			'name'                  => __( 'Bookings', 'motopress-hotel-booking' ),
			'singular_name'         => __( 'Booking', 'motopress-hotel-booking' ),
			'add_new'               => _x( 'Add New Booking', 'Add New Booking', 'motopress-hotel-booking' ),
			'add_new_item'          => __( 'Add New Booking', 'motopress-hotel-booking' ),
			'edit_item'             => __( 'Edit Booking', 'motopress-hotel-booking' ),
			'new_item'              => __( 'New Booking', 'motopress-hotel-booking' ),
			'view_item'             => __( 'View Booking', 'motopress-hotel-booking' ),
			'search_items'          => __( 'Search Booking', 'motopress-hotel-booking' ),
			'not_found'             => __( 'No bookings found', 'motopress-hotel-booking' ),
			'not_found_in_trash'    => __( 'No bookings found in Trash', 'motopress-hotel-booking' ),
			'all_items'             => __( 'All Bookings', 'motopress-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into booking description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this booking', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'map_meta_cap'         => true,
			'public'               => false,
			'exclude_from_search'  => true,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'query_var'            => false,
			'capability_type'      => $this->getCapabilityType(),
			'has_archive'          => false,
			'hierarchical'         => false,
			'show_in_menu'         => MPHB()->menus()->getMainMenuSlug(),
			'supports'             => false,
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'capabilities'         => array(
				'create_posts' => 'do_not_allow',
			),
		);

		register_post_type( $this->postType, $args );
	}

	/**
	 *
	 * @return BookingCPT\Statuses
	 */
	public function statuses() {
		return $this->statuses;
	}

	/**
	 *
	 * @return BookingCPT\Logs
	 */
	public function logs() {
		return $this->logs;
	}

	/**
	 *
	 * @param int $bookingId
	 */
	public function removeReservedRooms( $bookingId ) {
		if ( get_post_type( $bookingId ) !== $this->getPostType() ) {
			return;
		}
		$reservedRooms = MPHB()->getReservedRoomRepository()->findAllByBooking( $bookingId );
		foreach ( $reservedRooms as $reservedRoom ) {
			wp_delete_post( $reservedRoom->getId(), true );
		}
	}

}
