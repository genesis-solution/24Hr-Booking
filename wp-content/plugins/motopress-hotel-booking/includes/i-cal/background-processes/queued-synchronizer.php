<?php

namespace MPHB\iCal\BackgroundProcesses;

use \MPHB\iCal\Logger as Logs;
use \MPHB\iCal\OptionsHandler;
use \MPHB\iCal\Stats;
use \MPHB\iCal\Queue;

class QueuedSynchronizer {

	/**
	 * @var BackgroundSynchronizer
	 */
	private $synchronizer;

	/**
	 * @var OptionsHandler
	 */
	private $options;

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * @param BackgroundSynchronizer $synchronizer
	 */
	public function __construct( $synchronizer ) {
		$this->synchronizer = $synchronizer;
		$this->options      = $synchronizer->getOptions();
		$this->queue        = new Queue( $this->options );

		add_action( $synchronizer->getIdentifier() . '_complete', array( $this, 'doNext' ) );
	}

	public function getOptionsPrefix() {
		return $this->synchronizer->getIdentifier();
	}

	/**
	 * @return string
	 */
	public function getCurrentItem() {
		return $this->options->getOptionNoCache( 'current_item', '' );
	}

	/**
	 * @param array $roomIds
	 */
	protected function addToQueue( $roomIds ) {
		if ( apply_filters( 'mphb_block_sync', false ) ) {
			return;
		}

		$time = time();

		$items = array_map(
			function ( $roomId ) use ( $time ) {
				return $time . '_' . $roomId;
			},
			$roomIds
		);

		$this->queue->addItems( $items );
	}

	/**
	 * @param string $queueItem "%Timestamp%_%Room ID%"
	 * @return int $roomId
	 */
	public static function retrieveTimeFromItem( $queueItem ) {
		return (int) preg_replace( '/^(\d+)_\d+/', '$1', $queueItem );
	}

	public function removeItem( $queueItem ) {
		// Update current/next item, if required
		if ( $this->getCurrentItem() == $queueItem ) {
			$this->synchronizer->abort();
			$this->synchronizer->touch();
		}

		// Remove item from current queue
		$this->queue->removeItem( $queueItem );

		// Remove item's data from database
		$queueId = Queue::findId( $queueItem );

		Queue::deleteItem( $queueItem );
		Stats::deleteQueue( $queueId );
		Logs::deleteQueue( $queueId );
	}

	public function abortItem( $queueItem ) {
		if ( $this->getCurrentItem() == $queueItem ) {
			$this->synchronizer->abort();
			$this->synchronizer->touch();
		}
	}

	public function abortAll() {
		$this->options->updateOption( 'abort_all', true );

		$this->queue->abort();

		$this->synchronizer->abort();
		$this->synchronizer->touch();
	}

	public function clearAll() {
		$this->abortAll();

		// Clear the current queue
		$this->queue->clear();

		// Remove data from database
		Stats::deleteSync();
		Logs::deleteSync();
		Queue::deleteSync(); // Delete queue only after stats and logs (we need
							 // queue_id's, see INNER JOINs in deleteSync())

		if ( ! $this->isInProgress() ) {
			$this->doNext();
		}
	}

	public function isQueueEmpty() {
		return $this->queue->isEmpty();
	}

	/**
	 * @return bool
	 */
	public function isInProgress() {
		// Use get-method separately. Fixes "Fatal error: Can't use method
		// return value in write context" on PHP 5.3
		$currentItem = $this->getCurrentItem();
		return ( ! empty( $currentItem ) || ! $this->queue->isFinished() );
	}

	/**
	 * @return bool
	 */
	protected function isAborting() {
		return $this->options->getOptionNoCache( 'abort_all', false );
	}

	/**
	 * @param array $roomIds
	 */
	public function sync( $roomIds ) {
		Logs::deleteGhosts();

		// Skip rooms without calendars
		$roomsToSync = array_intersect( $roomIds, MPHB()->getSyncUrlsRepository()->getAllRoomIds() );

		// Skip rooms that are already waiting in queue
		$roomsToSync = array_diff( $roomsToSync, $this->queue->getQueuedRoomIds() );

		if ( ! empty( $roomsToSync ) ) {
			$this->addToQueue( array_values( $roomsToSync ) ); // Reset indexes
			$this->doNext();
		} else {
			$this->synchronizer->touch();
		}
	}

	public function doNext() {
		// Prevent process room queue while background synchronizer is in progress
		if ( $this->synchronizer->isInProgress() ) {
			$this->synchronizer->touch();

			return;
		}

		if ( $this->isAborting() ) {
			$this->options->deleteOption( 'abort_all' );
			$this->options->updateOption( 'current_item', '' );

			$this->synchronizer->reset();

		} else {
			$nextItem = $this->queue->next();

			// Remove current item from the queue and set new current item
			$this->queue->removeItem( $this->getCurrentItem() );
			$this->options->updateOption( 'current_item', $nextItem );

			$this->synchronizer->reset();

			if ( $nextItem ) {
				$this->synchronizer->addPullUrlTask(
					array(
						'roomId' => mphb_parse_queue_room_id( $nextItem ),
					)
				);
			}
		}
	}
}
