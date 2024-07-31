<?php

namespace MPHB\Admin;

use \MPHB\iCal\Logger;
use \MPHB\iCal\LogsHandler;

class SyncLogsListTable extends \WP_List_Table {

	/**
	 * @var int
	 */
	protected $queueId = 0;

	protected $logsRender;

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'sync-log',
				'plural'   => 'sync-logs',
				'ajax'     => false, // Does this page support AJAX?
			)
		);

		if ( isset( $_REQUEST['queue-id'] ) ) {
			$this->queueId = absint( $_REQUEST['queue-id'] );
		}

		$this->logsRender = new LogsHandler();
	}

	protected function query_items() {
		$limit  = $this->get_items_per_page( 'sync_logs_per_page', 100 );
		$offset = ( $this->get_pagenum() - 1 ) * $limit;

		$items = Logger::selectLogs( $this->queueId, $offset, $limit );

		$totalCount = Logger::countLogs( $this->queueId );
		$pagesCount = ceil( $totalCount / $limit );

		$this->set_pagination_args(
			array(
				'total_items' => $totalCount,
				'per_page'    => $limit,
				'total_pages' => $pagesCount,
			)
		);

		return $items;
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
			'status'  => __( 'Status', 'motopress-hotel-booking' ),
			'message' => __( 'Message', 'motopress-hotel-booking' ),
		);
	}

	public function column_status( $item ) {
		$class = 'mphb-status-' . $item['status'];

		switch ( $item['status'] ) {
			case 'success':
				$text = __( 'Success', 'motopress-hotel-booking' );
				break;
			case 'info':
				$text = __( 'Info', 'motopress-hotel-booking' );
				break;
			case 'warning':
				$text = __( 'Warning', 'motopress-hotel-booking' );
				break;
			case 'error':
				$text = __( 'Error', 'motopress-hotel-booking' );
				break;

			default:
				$text = ucfirst( str_replace( '-', ' ', $item['status'] ) );
				break;
		}

		return '<span class="' . esc_attr( $class ) . '">' . $text . '</span>';
	}

	public function column_message( $item ) {
		return $this->logsRender->logToHtml( $item, true );
	}

	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		$classes[] = 'mphb-ical-sync-table';
		$classes[] = 'mphb-sync-logs-table';

		return $classes;
	}

	public function get_plural() {
		return $this->_args['plural'];
	}
}
