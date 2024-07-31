<?php

namespace MPHB\Admin;

use MPHB\UsersAndRoles\Customers;

/**
 *
 * @since 4.2.0
 */
class CustomersListTable extends \WP_List_Table {

	/**
	 *
	 * @var string
	 */
	private $orderBy;

	/**
	 *
	 * @var string
	 */
	private $order;

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'customer',
				'plural'   => 'customers',
				'ajax'     => false,
			)
		);

		$this->orderBy = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'date_registered';
		$this->orderBy = preg_replace( '/\s+.*/', '', $this->orderBy );
		$this->order   = ( isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'ASC' );

		if ( $this->orderBy == 'date_registered' ) {
			$this->order = 'DESC';
		}

		if ( ! in_array( $this->order, array( 'ASC', 'DESC' ) ) ) {
			$this->order = 'ASC';
		}
	}

	protected function query_items() {

		/**
		 *
		 * @param int $postsPerPage
		 *
		 * @since 4.2.0
		 */
		$postsPerPage = apply_filters( 'mphb_filter_customers_per_page', 20 );

		$atts = array(
			'orderby'  => $this->orderBy,
			'order'    => $this->order,
			'per_page' => $postsPerPage,
			'paged'    => $this->get_pagenum(),
		);

		$customers = Customers::findCustomers( $atts );

		$totalCustomers = Customers::countCustomers( $atts );

		$items = array_map(
			function( $customer ) {
				return array(
					'id'              => $customer->getId(),
					'full_name'       => trim( sprintf( '%s %s', $customer->getFirstName(), $customer->getLastName() ) ),
					'email'           => $customer->getEmail(),
					'bookings'        => $customer->getBookings(),
					'date_registered' => $customer->getDateCreated(),
					'last_active'     => $customer->getDateModified(),
				);
			},
			$customers
		);

		$pagesCount = ceil( $totalCustomers / $postsPerPage );

		$this->set_pagination_args(
			array(
				'total_items' => $totalCustomers,
				'per_page'    => $postsPerPage,
				'total_pages' => $pagesCount,
			)
		);

		return $items;
	}

	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = $this->query_items();
	}

	/**
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortableColumns = array(
			'full_name'       => array( 'full_name', ( $this->orderBy == 'full_name' ) ),
			'email'           => array( 'email', ( $this->orderBy == 'email' ) ),
			'bookings'        => array( 'bookings', ( $this->orderBy == 'bookings' ) ),
			'last_active'     => array( 'last_active', ( $this->orderBy == 'last_active' ) ),
			'date_registered' => array( 'date_registered', ( $this->orderBy == 'date_registered' ) ),
		);

		return $sortableColumns;
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="ids[]" value="' . (int) $item['id'] . '" />';
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_full_name( $item ) {
		$itemId   = (int) $item['id'];
		$viewUrl  = admin_url( 'admin.php?page=mphb_customers&customer_id=' . $itemId );
		$fullName = empty( $item['full_name'] ) ? sprintf( '#%d', $itemId ) : $item['full_name'];

		$actions = array(
			'id' => sprintf( 'ID: %d', $itemId ),
		);

		if ( current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::EDIT_CUSTOMER ) ) {
			$actions['view'] = sprintf( '<a href="%s">%s</a>', $viewUrl, esc_html__( 'View', 'motopress-hotel-booking' ) );
		}

		if ( current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::DELETE_CUSTOMER ) ) {
			$actions['delete'] = sprintf( '<a href="%s" data-item-key="%d" class="mphb-remove-customer">%s</a>', '#', $itemId, esc_html__( 'Delete', 'motopress-hotel-booking' ) );
		}

		if ( current_user_can( \MPHB\UsersAndRoles\CapabilitiesAndRoles::EDIT_CUSTOMER ) ) {
			return sprintf( '<strong><a href="%s">%s</a></strong>', $viewUrl, sprintf( '%s', esc_html( $fullName ) ) ) . $this->row_actions( $actions );
		} else {
			return sprintf( '%s', esc_html( $fullName ) ) . $this->row_actions( $actions );
		}
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_email( $item ) {
		return esc_html( $item['email'] );
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_date_registered( $item ) {

		$date_registered = strtotime( $item['date_registered'] );

		return '<abbr title="' . esc_attr( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP(), $date_registered ) ) . '">' .
			esc_html( date_i18n( 'Y/m/d', $date_registered ) ) . '</abbr>';
	}

	/**
	 *
	 * @return string Link to bookings manage page with filter by customer_id
	 */
	public function column_bookings( $item ) {
		$bookingsCount = (int) $item['bookings'];
		return (int) $bookingsCount;
	}

	public function column_last_active( $item ) {

		$last_active = strtotime( $item['last_active'] );

		return '<abbr title="' . esc_attr( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP(), $last_active ) ) . '">' .
			esc_html( date_i18n( 'Y/m/d', $last_active ) ) . '</abbr>';
	}

	/**
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_default( $item, $columnName ) {
		switch ( $columnName ) {
			default:
				return '<span aria-hidden="true">&#8212;</span>';
		}
	}

	/**
	 *
	 * @return array An associative array [ %slug% => %Title% ].
	 */
	public function get_columns() {
		return array(
			'cb'              => '<input type="checkbox" />',
			'full_name'       => esc_html__( 'Name', 'motopress-hotel-booking' ),
			'email'           => esc_html__( 'Email', 'motopress-hotel-booking' ),
			'bookings'        => esc_html__( 'Bookings', 'motopress-hotel-booking' ),
			'date_registered' => esc_html__( 'Date Registered', 'motopress-hotel-booking' ),
			'last_active'     => esc_html__( 'Last Active', 'motopress-hotel-booking' ),
		);
	}

	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		$classes = array_diff( $classes, array( 'fixed' ) );

		return $classes;
	}

}
