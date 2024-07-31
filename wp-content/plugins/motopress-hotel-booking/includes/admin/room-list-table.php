<?php

namespace MPHB\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class RoomListTable extends \WP_List_Table {

	const POSTS_PER_PAGE = 20;

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
				'singular' => 'calendar',
				'plural'   => 'calendars',
				'ajax'     => false, // Does this page support AJAX?
			)
		);

		$this->orderBy = ( isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'date' );
		$this->orderBy = preg_replace( '/\s+.*/', '', $this->orderBy ); // Remove order, allowed by sanitize_sql_orderby()
		$this->order   = ( isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'ASC' );

		if ( ! in_array( $this->order, array( 'ASC', 'DESC' ) ) ) {
			$this->order = 'ASC';
		}

		// Set descendant order for default case
		if ( $this->orderBy == 'date' ) {
			$this->order = 'DESC';
		}
	}

	/**
	 * This required method is where you prepare your data for display. This
	 * method will usually be used to query the database, sort and filter the
	 * data, and generally get it ready to be displayed. At a minimum, we should
	 * set $this->items and $this->set_pagination_args().
	 */
	public function prepare_items() {
		// The $this->_column_headers property takes an array to be used by
		// class for column headers
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		// Handle bulk actions
		$this->process_bulk_action();

		// We'll use WP_Query manually to get the total amount of available
		// posts for pagination
		$queryAtts   = array(
			'post_type'           => MPHB()->postTypes()->room()->getPostType(),
			'post_status'         => 'any',
			'posts_per_page'      => self::POSTS_PER_PAGE,
			'paged'               => $this->get_pagenum(),
			'orderby'             => $this->orderBy,
			'order'               => $this->order,
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		);
		$query       = new \WP_Query();
		$this->items = $query->query( $queryAtts );

		// Map all items to room entity
		$this->items = array_map( array( MPHB()->getRoomRepository(), 'mapPostToEntity' ), $this->items );

		$this->set_pagination_args(
			array(
				'total_items' => $query->found_posts,
				'per_page'    => self::POSTS_PER_PAGE,
				'total_pages' => ceil( $query->found_posts / self::POSTS_PER_PAGE ),
			)
		);
	}

	/**
	 * Required to dictate the table's columns and titles.
	 *
	 * @return array An associative array [ %slug% => %Title% ].
	 */
	public function get_columns() {
		// Note: WordPress will properly handle only "cb" checkboxes for bulk actions
		$columns = array(
			'cb'     => '<input type="checkbox" />',
			'title'  => __( 'Accommodation', 'motopress-hotel-booking' ),
			'export' => __( 'Export', 'motopress-hotel-booking' ),
			'import' => __( 'External Calendars', 'motopress-hotel-booking' ),
		);
		return $columns;
	}

	/**
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortableColumns = array(
			'title' => array( 'title', ( $this->orderBy == 'title' ) ), // true/false - is it already sorted?
		);
		return $sortableColumns;
	}

	/**
	 * This method is called when the parent class can't find a method
	 * specifically build for a given column.
	 *
	 * @param MPHB\Entities\Room $item A singular item (one full row's worth of data).
	 * @param string             $columnName The name/slug of the column to be processed.
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
	 * Required if displaying checkboxes or using bulk actions! The "cb" column
	 * is given special treatment when columns are processed. It always needs to
	 * have it's own method.
	 *
	 * @param MPHB\Entities\Room $item A singular item (one full row's worth of data).
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="ids[]" value="' . esc_attr( $item->getId() ) . '" />';
	}

	/**
	 * Method specially for column "Title".
	 *
	 * @param MPHB\Entities\Room $item A singular item (one full row's worth of data).
	 *
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_title( $item ) {
		$itemId    = $item->getId();
		$editUrl   = admin_url( 'admin.php?page=mphb_ical&accommodation_id=' . $itemId );
		$uploadUrl = admin_url( 'admin.php?page=mphb_ical_import&action=upload&accommodation_id=' . $itemId );
		$syncUrl   = admin_url( 'admin.php?page=mphb_ical_import&action=sync&accommodation_id=' . $itemId );

		$actions = array(
			'edit'                   => sprintf( '<a href="' . esc_url( $editUrl ) . '">%s</a>', __( 'Edit', 'motopress-hotel-booking' ) ),
			// 'editinline'     => sprintf( '<a href="#">%s</a>', __( 'Quick Edit', 'motopress-hotel-booking' ) ),
							'upload' => sprintf( '<a href="' . esc_url( $uploadUrl ) . '">%s</a>', __( 'Import Calendar', 'motopress-hotel-booking' ) ),
			'sync'                   => sprintf( '<a href="' . esc_url( $syncUrl ) . '">%s</a>', __( 'Sync External Calendars', 'motopress-hotel-booking' ) ),
		);

		$roomType = MPHB()->getRoomTypeRepository()->findById( $item->getRoomTypeId() );

		$title = $item->getTitle();
		$title = '<strong><a href="' . esc_url( $editUrl ) . '">' . ( ! empty( $title ) ? $title : _x( '(no title)', 'Placeholder for empty accommodation title', 'motopress-hotel-booking' ) ) . '</a></strong>';
		if ( $roomType ) {
			$title .= '<span style="color:#999">' . $roomType->getTitle() . '</span>';
		}

		return $title . $this->row_actions( $actions );
	}

	/**
	 *
	 * @param \MPHB\Entities\Room $item
	 * @return string
	 */
	public function column_export( $item ) {
		$queryArgs = array(
			'feed'             => 'mphb.ics',
			'accommodation_id' => $item->getId(),
		);

		$icsUrl = add_query_arg( $queryArgs, site_url( '/' ) );

		$export  = ' <code>' . esc_url( $icsUrl ) . '</code>';
		$export .= '<p><a href="' . esc_url( $icsUrl ) . '">' . __( 'Download Calendar', 'motopress-hotel-booking' ) . '</a></p>';

		return $export;
	}

	/**
	 *
	 * @param \MPHB\Entities\Room $item
	 * @return string
	 */
	public function column_import( $item ) {
		$syncUrls = $item->getSyncUrls();

		if ( ! empty( $syncUrls ) ) {
			return implode( '<br/>', $syncUrls );
		} else {
			return $this->column_default( $item, 'import' );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'sync' => __( 'Sync External Calendars', 'motopress-hotel-booking' ),
		);
		return $actions;
	}

	public function process_bulk_action() {
		$action = $this->current_action();
		if ( empty( $action ) ) {
			return;
		}

		// Verify the nonce
		check_admin_referer( 'bulk-' . $this->get_plural() );

		switch ( $action ) {
			case 'sync':
				$ids = isset( $_POST['ids'] ) ? array_map( 'absint', $_POST['ids'] ) : array();
				$ids = array_filter( $ids );

				if ( ! empty( $ids ) ) {
					$importUrl = admin_url( 'admin.php?page=mphb_ical_import&action=sync&accommodation_ids=' . implode( ',', $ids ) );
					wp_redirect( $importUrl );
					exit;
				}

				break;
		}
	}

	/**
	 * Just a getter. Not required for WP_List_Table.
	 */
	public function get_plural() {
		return $this->_args['plural'];
	}

}
