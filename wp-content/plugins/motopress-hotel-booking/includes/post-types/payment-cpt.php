<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\ManageCPTPages;
use \MPHB\Entities;

class PaymentCPT extends EditableCPT {

	protected $postType = 'mphb_payment';
	protected $statuses;

	public function __construct() {
		parent::__construct();
		$this->statuses = new PaymentCPT\Statuses( $this->postType );

		add_filter( 'parent_file', array( $this, 'parent_file' ), 10, 1 );
	}

	protected function createEditPage() {
		return new \MPHB\Admin\EditCPTPages\PaymentEditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	protected function createManagePage() {
		return new ManageCPTPages\PaymentManageCPTPage( $this->postType );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {

		$labels = array(
			'name'                  => __( 'Payment History', 'motopress-hotel-booking' ),
			'singular_name'         => __( 'Payment', 'motopress-hotel-booking' ),
			'add_new'               => _x( 'Add New', 'Add New Payment', 'motopress-hotel-booking' ),
			'add_new_item'          => __( 'Add New Payment', 'motopress-hotel-booking' ),
			'edit_item'             => __( 'Edit Payment', 'motopress-hotel-booking' ),
			'new_item'              => __( 'New Payment', 'motopress-hotel-booking' ),
			'view_item'             => __( 'View Payment', 'motopress-hotel-booking' ),
			'search_items'          => __( 'Search Payment', 'motopress-hotel-booking' ),
			'not_found'             => __( 'No payments found', 'motopress-hotel-booking' ),
			'not_found_in_trash'    => __( 'No payments found in Trash', 'motopress-hotel-booking' ),
			'all_items'             => __( 'Payments', 'motopress-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into payment description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this payment', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'description'          => __( 'Payments.', 'motopress-hotel-booking' ),
			'public'               => false,
			'show_ui'              => true,
			'query_var'            => false,
			'has_archive'          => false,
			'hierarchical'         => false,
			'show_in_menu'         => false,
			'supports'             => false,
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'capability_type'      => $this->getCapabilityType(),
			// 'capabilities'           => array(
			// 'create_posts' => 'do_not_allow',
			// ),
				'map_meta_cap'     => true,
		);

		register_post_type( $this->postType, $args );
	}

	/**
	 *
	 * @param int $bookingId
	 * @return array
	 */
	private function retrieveDefaultsFromBooking( $bookingId ) {
		$defaults = array();

		$booking = MPHB()->getBookingRepository()->findById( $bookingId );

		if ( ! $booking ) {
			return $defaults;
		}

		$customer = $booking->getCustomer();

		$defaults['_mphb_email']      = $customer->getEmail();
		$defaults['_mphb_first_name'] = $customer->getFirstName();
		$defaults['_mphb_last_name']  = $customer->getLastName();
		$defaults['_mphb_phone']      = $customer->getPhone();

		return $defaults;
	}

	/**
	 *
	 * @return array
	 */
	private function detectCustomFieldsDefaults() {

		if ( empty( $_GET['mphb_defaults'] ) || ! is_array( $_GET['mphb_defaults'] ) ) {
			return array();
		}

		// we sanitize this data later
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$defaults = array_map( 'wp_unslash', $_GET['mphb_defaults'] );

		if ( ! empty( $defaults['_mphb_booking_id'] ) ) {

			$bookingDefaults = $this->retrieveDefaultsFromBooking( absint( $defaults['_mphb_booking_id'] ) );

			$defaults = array_merge( $bookingDefaults, $defaults );
		}

		return $defaults;
	}

	/**
	 *
	 * @return Groups\MetaBoxGroup[]
	 */
	public function getFieldGroups() {

		$defaults = $this->detectCustomFieldsDefaults();

		$mainGroup = new Groups\MetaBoxGroup( 'mphb_main', __( 'Payment Details', 'motopress-hotel-booking' ), $this->postType, 'normal' );

		$gatewaysList = array_map(
			function( $gateway ) {
				return $gateway->getTitle();
			},
			MPHB()->gatewayManager()->getList()
		);

		$bookings     = MPHB()->getBookingPersistence()->getPosts(
			array(
				'fields' => 'ids',
			)
		);
		$bookingsList = ! empty( $bookings ) ? array_combine( $bookings, $bookings ) : array();

		$paymentFields = array(
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
				'_mphb_gateway',
				array(
					'type'     => 'select',
					'label'    => __( 'Gateway', 'motopress-hotel-booking' ),
					'list'     => array( '' => '— Select —' ) + $gatewaysList,
					'default'  => ! empty( $defaults['_mphb_gateway'] ) ? sanitize_text_field( $defaults['_mphb_gateway'] ) : MPHB()->settings()->payment()->getDefaultGateway(),
					'required' => true,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_gateway_mode',
				array(
					'type'     => 'select',
					'label'    => __( 'Gateway Mode', 'motopress-hotel-booking' ),
					'list'     => array(
						'sandbox' => __( 'Sandbox', 'motopress-hotel-booking' ),
						'live'    => __( 'Live', 'motopress-hotel-booking' ),
					),
					'default'  => ! empty( $defaults['_mphb_gateway_mode'] ) ? sanitize_text_field( $defaults['_mphb_gateway_mode'] ) : 'sandbox',
					'required' => true,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_amount',
				array(
					'type'     => 'number',
					'label'    => __( 'Amount', 'motopress-hotel-booking' ),
					'default'  => ! empty( $defaults['_mphb_amount'] ) ? round( floatval( $defaults['_mphb_amount'] ), 2 ) : 0,
					'step'     => 0.01,
					'min'      => 0,
					'size'     => 'price',
					'required' => true,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_fee',
				array(
					'type'    => 'number',
					'label'   => __( 'Fee', 'motopress-hotel-booking' ),
					'default' => 0,
					'step'    => 0.01,
					'min'     => 0,
					'size'    => 'price',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_currency',
				array(
					'type'     => 'select',
					'label'    => __( 'Currency', 'motopress-hotel-booking' ),
					'list'     => MPHB()->settings()->currency()->getBundle()->getLabels(),
					'default'  => MPHB()->settings()->currency()->getCurrencyCode(),
					'required' => true,
				)
			),
			Fields\FieldFactory::create(
				'_mphb_payment_type',
				array(
					'type'    => 'text',
					'label'   => __( 'Payment Type', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_transaction_id',
				array(
					'type'    => 'text',
					'label'   => __( 'Transaction ID', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_booking_id',
				array(
					'type'    => 'select',
					'list'    => array( '' => __( '— Select —', 'motopress-hotel-booking' ) ) + $bookingsList,
					'label'   => __( 'Booking ID', 'motopress-hotel-booking' ),
					'default' => ! empty( $defaults['_mphb_booking_id'] ) ? absint( $defaults['_mphb_booking_id'] ) : '',
				)
			),
		);

		$mainGroup->addFields( $paymentFields );

		$billingInfoGroup = new Groups\MetaBoxGroup( 'mphb_billing_info', __( 'Billing Info', 'motopress-hotel-booking' ), $this->postType );

		$billingInfoFields = array(
			Fields\FieldFactory::create(
				'_mphb_first_name',
				array(
					'type'    => 'text',
					'label'   => __( 'First Name', 'motopress-hotel-booking' ),
					'default' => ! empty( $defaults['_mphb_first_name'] ) ? sanitize_text_field( $defaults['_mphb_first_name'] ) : '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_last_name',
				array(
					'type'    => 'text',
					'label'   => __( 'Last Name', 'motopress-hotel-booking' ),
					'default' => ! empty( $defaults['_mphb_last_name'] ) ? sanitize_text_field( $defaults['_mphb_last_name'] ) : '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_email',
				array(
					'type'    => 'text',
					'label'   => __( 'Email', 'motopress-hotel-booking' ),
					'default' => ! empty( $defaults['_mphb_email'] ) ? sanitize_email( $defaults['_mphb_email'] ) : '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_phone',
				array(
					'type'    => 'text',
					'label'   => __( 'Phone', 'motopress-hotel-booking' ),
					'default' => ! empty( $defaults['_mphb_phone'] ) ? sanitize_text_field( $defaults['_mphb_phone'] ) : '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_country',
				array(
					'type'    => 'text',
					'label'   => __( 'Country', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_address1',
				array(
					'type'    => 'text',
					'label'   => __( 'Address 1', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_address2',
				array(
					'type'    => 'text',
					'label'   => __( 'Address 2', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_city',
				array(
					'type'    => 'text',
					'label'   => __( 'City', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_state',
				array(
					'type'    => 'text',
					'label'   => __( 'State / County', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
			Fields\FieldFactory::create(
				'_mphb_zip',
				array(
					'type'    => 'text',
					'label'   => __( 'Postal Code (ZIP)', 'motopress-hotel-booking' ),
					'default' => '',
				)
			),
		);

		$billingInfoGroup->addFields( $billingInfoFields );

		return array( $mainGroup, $billingInfoGroup );
	}

	/**
	 *
	 * @return PaymentCPT\Statuses
	 */
	public function statuses() {
		return $this->statuses;
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
