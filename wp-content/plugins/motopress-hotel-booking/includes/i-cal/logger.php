<?php

namespace MPHB\iCal;

class Logger {

	const TABLE_NAME = 'mphb_sync_logs';

	/**
	 * @var int
	 */
	protected $queueId = 0;

	/**
	 * @var string Table name.
	 */
	protected $mphb_sync_logs = '';

	public function __construct( $queueId = 0 ) {
		global $wpdb;

		$this->setQueueId( $queueId );
		$this->mphb_sync_logs = $wpdb->prefix . self::TABLE_NAME;
	}

	public function setQueueId( $queueId ) {
		$this->queueId = intval( $queueId );
	}

	public function getQueueId() {
		return $this->queueId;
	}

	public function success( $message ) {
		$this->log( 'success', $message );
	}

	public function info( $message ) {
		$this->log( 'info', $message );
	}

	public function warning( $message ) {
		$this->log( 'warning', $message );
	}

	public function error( $message ) {
		$this->log( 'error', $message );
	}

	/**
	 * @param string $status "success"|"info"|"warning"|"error"
	 * @param string $message
	 */
	public function log( $status, $message ) {
		global $wpdb;

		if ( empty( $this->queueId ) ) {
			return;
		}

		$wpdb->insert(
			$this->mphb_sync_logs,
			array(
				'queue_id'    => $this->queueId,
				'log_status'  => $status,
				'log_message' => $message,
			)
		);
	}

	public function getLogs( $skipCount = 0 ) {
		return self::selectLogs( $this->queueId, $skipCount, 400000000 );
	}

	public function clear() {
		if ( ! empty( $this->queueId ) ) {
			self::deleteQueue( $this->queueId );
		}
	}

	public static function selectLogs( $queueId, $offset, $limit ) {
		global $wpdb;

		$mphb_sync_logs = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			'SELECT log_status, log_message'
				. " FROM {$mphb_sync_logs}"
				. ' WHERE queue_id = %d'
				. " LIMIT {$offset}, {$limit}",
			$queueId
		);

		$rows = $wpdb->get_results( $query, ARRAY_A );

		$logs = array_map(
			function ( $row ) {
				return array(
					'status'  => $row['log_status'],
					// Transform nulls
					'message' => ! empty( $row['log_message'] ) ? $row['log_message'] : '',
				);
			},
			$rows
		);

		return $logs;
	}

	public static function countLogs( $queueId ) {
		global $wpdb;

		$mphb_sync_logs = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			'SELECT COUNT(*)'
				. " FROM {$mphb_sync_logs}"
				. ' WHERE queue_id = %d',
			$queueId
		);

		return $wpdb->get_var( $query );
	}

	/**
	 * @param int $queueId
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteQueue( $queueId ) {
		global $wpdb;

		$mphb_sync_logs = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			"DELETE FROM {$mphb_sync_logs}"
				. ' WHERE queue_id = %d',
			$queueId
		);

		$wpdb->query( $query );
	}

	/**
	 * @param int[] $queueIds
	 *
	 * @global \wpdb $wpdb
	 *
	 * @since 3.6.1
	 */
	public static function deleteQueues( $queueIds ) {
		global $wpdb;

		$mphb_sync_logs = $wpdb->prefix . self::TABLE_NAME;
		$query          = "DELETE FROM {$mphb_sync_logs} WHERE queue_id IN (" . implode( ', ', $queueIds ) . ')';

		$wpdb->query( $query );
	}

	/**
	 * Delete all logs, where queue status is "wait", "in-progress" or "done",
	 * but leave logs of the "auto"-items.
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteSync() {
		global $wpdb;

		$mphb_sync_logs  = $wpdb->prefix . self::TABLE_NAME;
		$mphb_sync_queue = $wpdb->prefix . Queue::TABLE_NAME;

		$query = $wpdb->prepare(
			"DELETE logs FROM {$mphb_sync_logs} AS logs"
				. " INNER JOIN {$mphb_sync_queue} AS queue ON logs.queue_id = queue.queue_id"
				. ' WHERE queue.queue_status != %s',
			Queue::STATUS_AUTO
		);

		$wpdb->query( $query );
	}

	/**
	 * Sometime background process may add a log message before it recognizes,
	 * that it was aborted. Delete those log message.
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteGhosts() {
		global $wpdb;

		$mphb_sync_logs  = $wpdb->prefix . self::TABLE_NAME;
		$mphb_sync_queue = $wpdb->prefix . Queue::TABLE_NAME;

		$query = "DELETE logs FROM {$mphb_sync_logs} AS logs"
			. " LEFT JOIN {$mphb_sync_queue} AS queue ON logs.queue_id = queue.queue_id"
			. ' WHERE queue.queue_id IS NULL';

		return $wpdb->query( $query );
	}
}
