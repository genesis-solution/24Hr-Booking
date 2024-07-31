<?php

namespace MPHB\Admin;

use \MPHB\iCal\BackgroundProcesses\QueuedSynchronizer;
use \MPHB\iCal\Stats;
use \MPHB\iCal\Queue;

class SyncRoomsListTable extends \WP_List_Table {

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'sync-room',
				'plural'   => 'sync-rooms',
				'ajax'     => false, // Does this page support AJAX?
			)
		);
	}

	protected function query_items() {
		$limit  = $this->get_items_per_page( 'sync_rooms_per_page', 20 );
		$offset = ( $this->get_pagenum() - 1 ) * $limit;

		$queue    = Queue::selectItemsPage( $offset, $limit );
		$queueIds = array_keys( $queue );
		$stats    = Stats::selectStats( $queueIds );

		$items = array();

		// array_merge_recursive() is terrible with numeric keys
		foreach ( $queueIds as $queueId ) {
			$items[ $queueId ] = array_merge( $queue[ $queueId ], $stats[ $queueId ] );
		}

		$items = $this->filter_items( $items );

		$totalCount = Queue::countItems();

		$this->set_pagination_args(
			array(
				'total_items' => $totalCount,
				'per_page'    => $limit,
				'total_pages' => ceil( $totalCount / $limit ),
			)
		);

		return $items;
	}

	/**
	 * Remove items, map items, add more fields etc.
	 *
	 * @param array $items
	 * @return array
	 */
	protected function filter_items( $items ) {
		$newItems = array();

		foreach ( $items as $queueId => $item ) {
			$roomId = mphb_parse_queue_room_id( $item['queue'] );
			$room   = MPHB()->getRoomRepository()->findById( $roomId );

			if ( is_null( $room ) || empty( $room->getTitle() ) ) {
				$title = _x( '(no title)', 'Placeholder for empty accommodation title', 'motopress-hotel-booking' );
			} else {
				$title = $room->getTitle();
			}

			$time = QueuedSynchronizer::retrieveTimeFromItem( $item['queue'] );
			$date = date( _x( 'd/m/Y - H:i:s', 'This is date and time format 31/12/2017 - 23:59:59', 'motopress-hotel-booking' ), $time );

			switch ( $item['status'] ) {
				case Queue::STATUS_WAIT:
					$statusText = __( 'Waiting', 'motopress-hotel-booking' );
					break;
				case Queue::STATUS_IN_PROGRESS:
					$statusText = __( 'Processing', 'motopress-hotel-booking' );
					break;
				case Queue::STATUS_DONE:
					$statusText = __( 'Done', 'motopress-hotel-booking' );
					break;

				default:
					$statusText = ucfirst( str_replace( '-', ' ', $item['status'] ) );
					break;
			}

			$newItems[ $queueId ] = array(
				'queue-id'    => $queueId,
				'queue-name'  => $item['queue'],
				'title'       => $title,
				'status'      => $item['status'],
				'status-text' => $statusText,
				'total'       => $item['total'],
				'succeed'     => $item['succeed'],
				'skipped'     => $item['skipped'],
				'failed'      => $item['failed'],
				'removed'     => $item['removed'],
				'date'        => $date,
			);
		}

		return $newItems;
	}

	/**
	 * This method will usually be used to query the database, sort and filter
	 * the data, and generally get it ready to be displayed. At a minimum, we
	 * should set $this->items and $this->set_pagination_args().
	 */
	public function prepare_items() {
		// _column_headers takes an array to be used by class for column headers
		$this->_column_headers = array(
			$this->get_columns(),
			array(), // Hidden columns
			$this->get_sortable_columns(),
		);

		// Query items and set pagination args
		$this->items = $this->query_items();
	}

	public function get_columns() {
		 return array(
			 'title'   => __( 'Accommodation', 'motopress-hotel-booking' ),
			 'status'  => __( 'Status', 'motopress-hotel-booking' ),
			 'total'   => _x( 'Total', 'Total number of processed bookings', 'motopress-hotel-booking' ),
			 'succeed' => __( 'Succeed', 'motopress-hotel-booking' ),
			 'skipped' => __( 'Skipped', 'motopress-hotel-booking' ),
			 'failed'  => __( 'Failed', 'motopress-hotel-booking' ),
			 'removed' => __( 'Removed', 'motopress-hotel-booking' ),
			 'date'    => __( 'Date' ),
		 );
	}

	/**
	 * Method for column "Title", which also contains row actions
	 */
	public function column_title( $item ) {
		$viewUrl = admin_url( 'admin.php?page=mphb_sync_logs' );
		$viewUrl = add_query_arg( 'queue-id', $item['queue-id'], $viewUrl );
		$viewUrl = add_query_arg( 'queue', $item['queue-name'], $viewUrl );

		$actions = array(
			'view'   => sprintf( '<a href="%s">%s</a>', esc_url( $viewUrl ), __( 'View', 'motopress-hotel-booking' ) ),
			'delete' => sprintf( '<a href="%s" class="mphb-remove-item">%s</a>', '#', __( 'Delete', 'motopress-hotel-booking' ) ),
		);

		$output = '<strong><a href="' . esc_url( $viewUrl ) . '">' . $item['title'] . '</a></strong>';

		return $output . $this->row_actions( $actions );
	}

	public function column_status( $item ) {
		$class = 'mphb-status-' . $item['status'];

		return '<span class="' . esc_attr( $class ) . '">' . $item['status-text'] . '</span>';
	}

	public function column_date( $item ) {
		return $item['date'];
	}

	/**
	 * This method is called when the parent class can't find a method
	 * specifically build for a given column.
	 *
	 * @param array  $item A single item from $this->items.
	 * @param string $columnName The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column &lt;td&gt;.
	 */
	public function column_default( $item, $columnName ) {
		if ( ! isset( $item[ $columnName ] ) ) {
			return '<span aria-hidden="true">&#8212;</span>';
		}

		// column_total, column_succeed, column_skipped, column_failed
		if ( $item[ $columnName ] > 0 ) {
			return $item[ $columnName ];
		} else {
			return '<span aria-hidden="true">&#8212;</span>';
		}
	}

	public function single_row( $item ) {
		$atts  = 'data-sync-status="' . esc_attr( $item['status'] ) . '"';
		$atts .= ' data-item-key="' . esc_attr( $item['queue-name'] ) . '"';

		if ( $item['failed'] > 0 ) {
			$atts .= ' class="mphb-have-errors"';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<tr ' . $atts . '>';
			$this->single_row_columns( $item );
		echo '</tr>';
	}

	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		$classes[] = 'mphb-ical-sync-table';
		$classes[] = 'mphb-sync-rooms-table';

		return $classes;
	}

	public function get_plural() {
		return $this->_args['plural'];
	}
}
