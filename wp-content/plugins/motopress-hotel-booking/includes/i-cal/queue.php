<?php

namespace MPHB\iCal;

class Queue {

	const TABLE_NAME = 'mphb_sync_queue';

	const STATUS_WAIT        = 'wait';
	const STATUS_IN_PROGRESS = 'in-progress';
	const STATUS_DONE        = 'done';
	const STATUS_AUTO        = 'auto';

	/**
	 * @var OptionsHandler
	 */
	protected $options;

	/**
	 * @var string Table name.
	 */
	protected $mphb_sync_queue;

	/**
	 * @param OptionsHandler $options
	 */
	public function __construct( $options ) {
		global $wpdb;

		$this->options         = $options;
		$this->mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;
	}

	public function getNextItem() {
		return $this->options->getOption( 'next_item', '' );
	}

	public function getQueue() {
		return $this->options->getOption( 'queue', array() );
	}

	public function isEmpty() {
		$queue = $this->getQueue();
		return empty( $queue );
	}

	public function isFinished() {
		$nextItem = $this->getNextItem();
		return empty( $nextItem );
	}

	public function setNextItem( $item ) {
		$this->options->updateOption( 'next_item', $item );
	}

	public function setQueue( $queue ) {
		$this->options->updateOption( 'queue', $queue );
	}

	public function addItems( $items ) {
		// Insert new items into database
		$this->insertItems( $items );

		// Add new items to queue
		$queue = $this->getQueue();

		if ( empty( $queue ) || $this->isFinished() ) {
			// Start new queue
			$queue = $items;
		} else {
			// Add more items to current queue
			$queue = array_merge( $queue, $items );
		}

		// Save new queue
		$this->setQueue( $queue );

		// Update next item
		$nextItem = $this->getNextItem();

		if ( empty( $nextItem ) ) {
			$nextItem = reset( $items );

			$this->setNextItem( $nextItem );
		}
	}

	protected function insertItems( $items ) {
		global $wpdb;

		// We'll reverse the order on progress page (to show the latest items
		// first), so reverse items here to make each portion to change it's
		// progress from the first item to last: 6-7-3-4-5-1-2
		$items = array_reverse( $items );

		// Prepare values for INSERT INTO query (queue name and it's status)
		$values = array_map(
			function ( $item ) {
				// ('13562489_38', 'wait')
				return "('" . esc_sql( $item ) . "', '" . Queue::STATUS_WAIT . "')";
			},
			$items
		);

		$query = "INSERT INTO {$this->mphb_sync_queue} (queue_name, queue_status)"
			. ' VALUES ' . implode( ', ', $values );

		$wpdb->query( $query );
	}

	/**
	 * @since 4.2.2
	 *
	 * @return int[]
	 *
	 * @global \wpdb $wpdb
	 */
	public function getQueuedRoomIds() {
		global $wpdb;

		$queueItems = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT queue_name FROM {$this->mphb_sync_queue} WHERE queue_status = %s OR queue_status = %s",
				self::STATUS_IN_PROGRESS,
				self::STATUS_WAIT
			)
		);

		$roomIds = array_map( 'mphb_parse_queue_room_id', $queueItems );

		return $roomIds;
	}

	/**
	 * @return string Key of the next room if exists, or empty string (the end
	 *                of queue).
	 */
	public function next() {
		$nextItem = $this->getNextItem();

		$queue     = $this->getQueue();
		$itemIndex = array_search( $nextItem, $queue );

		if ( $itemIndex === false ) {
			$itemIndex = count( $queue ); // The queue is broken, skip all items
		}

		$oldValue = $nextItem;
		$newIndex = $itemIndex + 1;
		$newValue = isset( $queue[ $newIndex ] ) ? $queue[ $newIndex ] : '';

		$this->setNextItem( $newValue );

		// The previous is done
		$this->previousIsDone();

		// New item is in progress
		$this->updateStatus( $oldValue, self::STATUS_IN_PROGRESS ); // $oldValue == current item at this moment

		return $oldValue;
	}

	/**
	 * @param string $item Queue name.
	 * @param string $status "in-progress"|"done"
	 *
	 * @global \wpdb $wpdb
	 */
	protected function updateStatus( $item, $status ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"UPDATE {$this->mphb_sync_queue}"
				. ' SET queue_status = %s'
				. ' WHERE queue_name = %s',
			$status,
			$item
		);

		$wpdb->query( $query );
	}

	protected function previousIsDone() {
		global $wpdb;

		$query = $wpdb->prepare(
			"UPDATE {$this->mphb_sync_queue}"
				. ' SET queue_status = %s'
				. ' WHERE queue_status = %s',
			self::STATUS_DONE,
			self::STATUS_IN_PROGRESS
		);

		$wpdb->query( $query );
	}

	protected function allIsDone() {
		global $wpdb;

		$query = $wpdb->prepare(
			"UPDATE {$this->mphb_sync_queue}"
				. ' SET queue_status = %s'
				. ' WHERE queue_status = %s OR queue_status = %s',
			self::STATUS_DONE,
			self::STATUS_WAIT,
			self::STATUS_IN_PROGRESS
		);

		$wpdb->query( $query );
	}

	/**
	 * Remove the item only from queue, not from the database.
	 */
	public function removeItem( $item ) {
		$queue     = $this->getQueue();
		$itemIndex = array_search( $item, $queue );

		if ( $itemIndex === false ) {
			return;
		}

		// Remove item from queue
		if ( $itemIndex === 0 ) {
			array_shift( $queue );
		} else {
			array_splice( $queue, $itemIndex, 1 );
		}

		// Update next item, if required
		if ( $this->getNextItem() == $item ) {
			$this->next();
		}

		// Update queue array only after method next()
		$this->setQueue( $queue );
	}

	public function abort() {
		$this->setNextItem( '' );
		$this->allIsDone();
	}

	public function clear() {
		$this->abort();
		$this->setQueue( array() );
	}

	/**
	 * Push an item into database manually (required for uploader).
	 *
	 * @param string $item
	 *
	 * @global \wpdb $wpdb
	 */
	public static function createUploaderItem( $item ) {
		global $wpdb;

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$itemExists = (bool) self::findId( $item );

		if ( ! $itemExists ) {
			$wpdb->insert(
				$mphb_sync_queue,
				array(
					'queue_name'   => $item,
					'queue_status' => self::STATUS_AUTO,
				)
			);
		}
	}

	public static function findId( $item ) {
		global $wpdb;

		// Old versions don't have proper tables yet, don't generate error here
		if ( ! mphb_db_version_at_least( '3.0.2' ) ) {
			return 0;
		}

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			'SELECT queue_id'
				. " FROM {$mphb_sync_queue}"
				. ' WHERE queue_name = %s',
			$item
		);

		$queueId = $wpdb->get_var( $query );

		return ! is_null( $queueId ) ? $queueId : 0;
	}

	/**
	 * @param array $items Queue names.
	 * @return array [%Queue ID% => ["queue", "status"]]
	 *
	 * @global \wpdb $wpdb
	 */
	public static function selectItems( $items ) {
		global $wpdb;

		// Fix: SQL syntax error in query near "IN (": "SELECT * FROM ... WHERE queue_name IN ()"
		// See: \MPHB\Ajax::ical_sync_get_progress() in includes/ajax.php
		if ( empty( $items ) ) {
			return array();
		}

		$items = esc_sql( $items );
		// Wrap items with ''
		$items = array_map(
			function ( $item ) {
				return "'" . $item . "'";
			},
			$items
		);

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$query = "SELECT * FROM {$mphb_sync_queue}"
			. ' WHERE queue_name IN (' . implode( ', ', $items ) . ')';

		// $rows = array of ["queue_id", "queue_name", "queue_status"]
		$rows = $wpdb->get_results( $query, ARRAY_A );

		// $items = [%Queue ID% => ["queue", "status"]]
		$items = array();

		foreach ( $rows as $row ) {
			$id = (int) $row['queue_id'];

			$items[ $id ] = array(
				'queue'  => $row['queue_name'],
				'status' => $row['queue_status'],
			);
		}

		return $items;
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 * @return array [%Queue ID% => ["queue", "status"]]
	 *
	 * @global \wpdb $wpdb
	 */
	public static function selectItemsPage( $offset, $limit ) {
		global $wpdb;

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			"SELECT * FROM {$mphb_sync_queue}"
				. ' WHERE queue_status != %s'
				. ' ORDER BY queue_id DESC'
				. ' LIMIT %d, %d',
			self::STATUS_AUTO,
			$offset,
			$limit
		);

		// $rows = array of ["queue_id", "queue_name", "queue_status"]
		$rows = $wpdb->get_results( $query, ARRAY_A );

		// $items = [%Queue ID% => ["queue", "status"]]
		$items = array();

		foreach ( $rows as $row ) {
			$id = (int) $row['queue_id'];

			$items[ $id ] = array(
				'queue'  => $row['queue_name'],
				'status' => $row['queue_status'],
			);
		}

		return $items;
	}

	public static function countItems() {
		global $wpdb;

		// Old versions don't have proper tables yet, don't generate error here
		if ( ! mphb_db_version_at_least( '3.0.2' ) ) {
			return 0;
		}

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			'SELECT COUNT(*)'
				. " FROM {$mphb_sync_queue}"
				. ' WHERE queue_status != %s',
			self::STATUS_AUTO
		);

		return $wpdb->get_var( $query );
	}

	public static function deleteItem( $item ) {
		global $wpdb;

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			"DELETE FROM {$mphb_sync_queue}"
				. ' WHERE queue_name = %s',
			$item
		);

		$wpdb->query( $query );
	}

	/**
	 * @param int[] $itemIds
	 *
	 * @global \wpdb $wpdb
	 *
	 * @since 3.6.1
	 */
	public static function deleteItemsByIds( $itemIds ) {
		global $wpdb;

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;
		$query           = "DELETE FROM {$mphb_sync_queue} WHERE queue_id IN (" . implode( ', ', $itemIds ) . ')';

		$wpdb->query( $query );
	}

	/**
	 * Delete all "wait", "in-progress" or "done" items, but leave "auto"-items.
	 *
	 * @global \wpdb $wpdb
	 */
	public static function deleteSync() {
		global $wpdb;

		$mphb_sync_queue = $wpdb->prefix . self::TABLE_NAME;

		$query = $wpdb->prepare(
			"DELETE FROM {$mphb_sync_queue}"
				. ' WHERE queue_status != %s',
			self::STATUS_AUTO
		);

		$wpdb->query( $query );
	}
}
