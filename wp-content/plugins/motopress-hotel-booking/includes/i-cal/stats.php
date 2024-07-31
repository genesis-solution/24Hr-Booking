<?php

namespace MPHB\iCal;

class Stats {

	const TABLE_NAME = 'mphb_sync_stats';

	/**
	 * @var int
	 */
	protected $queueId = 0;

	/**
	 * @var string Table name.
	 */
	protected $mphb_sync_stats = '';

	public function __construct( $queueId = 0 ) {

		global $wpdb;

		$this->setQueueId( $queueId );
		$this->mphb_sync_stats = $wpdb->prefix . self::TABLE_NAME;
	}

	public function setQueueId( $queueId ) {
		$this->queueId = intval( $queueId );
	}

	public function getQueueId() {
		return $this->queueId;
	}

	public function increaseImportsTotal( $increment ) {
		$this->increaseField( 'import_total', $increment );
	}

	public function increaseSucceedImports( $increment ) {
		$this->increaseField( 'import_succeed', $increment );
	}

	public function increaseSkippedImports( $increment ) {
		$this->increaseField( 'import_skipped', $increment );
	}

	public function increaseFailedImports( $increment ) {
		$this->increaseField( 'import_failed', $increment );
	}

	public function increaseCleansTotal( $increment ) {
		$this->increaseField( 'clean_total', $increment );
	}

	public function increaseDoneCleans( $increment ) {
		$this->increaseField( 'clean_done', $increment );
	}

	public function increaseSkippedCleans( $increment ) {
		$this->increaseField( 'clean_skipped', $increment );
	}

	protected function increaseField( $field, $increment ) {
		global $wpdb;

		if ( empty( $this->queueId ) ) {
			return;
		}

		$query = $wpdb->prepare(
			"UPDATE {$this->mphb_sync_stats}"
				. " SET {$field} = {$field} + %d"
				. ' WHERE queue_id = %d',
			$increment,
			$this->queueId
		);

		$wpdb->query( $query );
	}

	public function getStats() {
		global $wpdb;

		$query = $wpdb->prepare(
			'SELECT import_total, import_succeed, import_skipped, import_failed,'
				. ' clean_total, clean_done, clean_skipped'
				. " FROM {$this->mphb_sync_stats}"
				. ' WHERE queue_id = %d',
			$this->queueId
		);

		$row = $wpdb->get_row( $query, ARRAY_A );

		if ( ! is_null( $row ) ) {
			return array(
				'total'   => $row['import_total'] + $row['clean_total'],
				'succeed' => $row['import_succeed'],
				'skipped' => $row['import_skipped'] + $row['clean_skipped'],
				'failed'  => $row['import_failed'],
				'removed' => $row['clean_done'],
			);
		} else {
			return self::emptyStats();
		}
	}

	public static function emptyStats() {
		return array(
			'total'   => 0,
			'succeed' => 0,
			'skipped' => 0,
			'failed'  => 0,
			'removed' => 0,
		);
	}

	/**
	 * @global \wpdb $wpdb
	 *
	 * @param int[] $queueIds
	 * @return array [%Queue ID% => [total, succeed, skipped, removed, failed]]
	 */
	public static function selectStats( $queueIds ) {
		global $wpdb;

		if ( empty( $queueIds ) ) {
			return array();
		}

		$mphb_sync_stats = $wpdb->prefix . self::TABLE_NAME;
		$query           = 'SELECT queue_id, import_total, import_succeed, import_skipped,'
			. ' import_failed, clean_total, clean_done, clean_skipped'
			. " FROM {$mphb_sync_stats}"
			. ' WHERE queue_id IN (' . implode( ', ', $queueIds ) . ')';

		$rows = $wpdb->get_results( $query, ARRAY_A );

		// Convert $rows array into $stats [%Queue ID% => [total, succeed, ...]]
		$stats = array();

		foreach ( $rows as $row ) {
			$id = (int) $row['queue_id'];
			unset( $row['queue_id'] ); // Leave only stats

			// Convert all values to int
			$row = array_map( 'absint', $row );

			$stats[ $id ] = array(
				'total'   => $row['import_total'] + $row['clean_total'],
				'succeed' => $row['import_succeed'],
				'skipped' => $row['import_skipped'] + $row['clean_skipped'],
				'failed'  => $row['import_failed'],
				'removed' => $row['clean_done'],
			);
		}

		// Use empty values for unexistant IDs
		$results = array();

		foreach ( $queueIds as $queueId ) {
			if ( isset( $stats[ $queueId ] ) ) {
				$results[ $queueId ] = $stats[ $queueId ];
			} else {
				$results[ $queueId ] = self::emptyStats();
			}
		}

		return $results;
	}

	/**
	 * @param int $queueId
	 *
	 * @global \wpdb $wpdb
	 */
	public static function resetStats( $queueId ) {
		global $wpdb;

		$mphb_sync_stats = $wpdb->prefix . self::TABLE_NAME;

		$itemExists = (bool) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT stat_id'
				. " FROM {$mphb_sync_stats}"
				. ' WHERE queue_id = %d',
				$queueId
			)
		);

		$values  = array(
			'queue_id'       => $queueId,
			// It's important to set all values to 0, even when they are not
			// required; otherwise uploader will not reset it's stats
			'import_total'   => 0,
			'import_succeed' => 0,
			'import_skipped' => 0,
			'import_failed'  => 0,
			'clean_total'    => 0,
			'clean_done'     => 0,
			'clean_skipped'  => 0,
		);
		$formats = '%d';
		$where   = array( 'queue_id' => $queueId );

		if ( $itemExists ) {
			$wpdb->update( $mphb_sync_stats, $values, $where, $formats );
		} else {
			$wpdb->insert( $mphb_sync_stats, $values, $formats );
		}
	}

	/**
	 * @param int $queueId
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteQueue( $queueId ) {
		global $wpdb;

		$mphb_sync_stats = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			"DELETE FROM {$mphb_sync_stats} WHERE queue_id = %d",
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

		$mphb_sync_stats = $wpdb->prefix . self::TABLE_NAME;
		$query           = "DELETE FROM {$mphb_sync_stats} WHERE queue_id IN (" . implode( ', ', $queueIds ) . ')';

		$wpdb->query( $query );
	}

	/**
	 * Delete all stats, where queue status is "wait", "in-progress" or "done",
	 * but leave stats of the "auto"-items.
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteSync() {
		global $wpdb;

		$mphb_sync_stats = $wpdb->prefix . self::TABLE_NAME;
		$mphb_sync_queue = $wpdb->prefix . Queue::TABLE_NAME;

		$query = $wpdb->prepare(
			"DELETE stats FROM {$mphb_sync_stats} AS stats"
				. " INNER JOIN {$mphb_sync_queue} AS queue ON stats.queue_id = queue.queue_id"
				. ' WHERE queue.queue_status != %s',
			Queue::STATUS_AUTO
		);

		$wpdb->query( $query );
	}
}
