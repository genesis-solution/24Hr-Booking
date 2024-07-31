<?php

namespace MPHB\iCal\BackgroundProcesses;

use \MPHB\Exceptions\NoEnoughExecutionTimeException;
use \MPHB\Exceptions\RequestException;
use \MPHB\iCal\ImportStatus;
use \MPHB\iCal\Stats;
use \MPHB\iCal\Queue;
use \MPHB\Libraries\WP_Background_Processing\WP_Background_Process;

abstract class BackgroundWorker extends WP_Background_Process {

	const BATCH_SIZE = 1000;

	const ACTION_PULL_URLS = 'pull-urls'; // Only for synchronization
	const ACTION_PARSE     = 'parse';
	const ACTION_IMPORT    = 'import';
	const ACTION_CLEAN     = 'clean';

	const MAX_REQUEST_TIMEOUT = 30; // 30 seconds

	/**
	 * @var string
	 */
	protected $prefix = 'mphb_ical';

	/**
	 * @var \MPHB\iCal\Importer
	 */
	protected $importer = null;

	/**
	 * @var \MPHB\iCal\Logger
	 */
	protected $logger = null;

	/**
	 * @var \MPHB\iCal\Stats
	 */
	protected $stats = null;

	/**
	 * @var \MPHB\iCal\OptionsHandler
	 */
	protected $options = null;

	/**
	 * @var int
	 */
	protected $maxExecutionTime = 0;

	public function __construct() {
		// Add blog ID to the prefix (only for multisites and only for IDs 2, 3 and so on)
		$blogId = get_current_blog_id();
		if ( $blogId > 1 ) {
			$this->prefix .= '_' . $blogId;
		}

		parent::__construct();

		// We'll need options to get current item from wp_option in
		// background-synchronizer.php
		$this->options = new \MPHB\iCal\OptionsHandler( $this->identifier );

		$currentItem = $this->getCurrentItem();
		$queueId     = ! empty( $currentItem ) ? Queue::findId( $currentItem ) : 0;

		$this->logger = MPHB()->settings()->main()->isMinimizedSyncLogs()
			? new \MPHB\iCal\MinimizedLogger( $queueId )
			: new \MPHB\iCal\Logger( $queueId );

		$this->importer         = new \MPHB\iCal\Importer( $this->logger );
		$this->stats            = new Stats( $queueId );
		$this->maxExecutionTime = intval( ini_get( 'max_execution_time' ) );
	}

	/**
	 * @return bool
	 */
	public function isInProgress() {
		// The main check is is_queue_empty(). But we also need to check if the
		// process actually stopped (unlocked) - is_process_running()
		return $this->is_process_running() || ! $this->is_queue_empty();
	}

	public function isAborting() {
		return $this->options->getOptionNoCache( 'abort_current', false );
	}

	public function touch() {
		if ( ! $this->is_process_running() && ! $this->is_queue_empty() ) {
			// Background process down, but was not finished. Restart it
			$this->dispatch();
		}
	}

	public function abort() {
		if ( $this->isInProgress() ) {
			$this->options->updateOption( 'abort_current', true );
		}
	}

	/**
	 * Reset only before new start. On finish you'll reset the stats.
	 */
	public function reset() {
		$this->clearOptions();

		$queueItem = $this->getCurrentItem();
		$queueId   = Queue::findId( $queueItem );

		$this->logger->setQueueId( $queueId );
		$this->stats->setQueueId( $queueId );

		if ( ! empty( $queueId ) ) {
			Stats::resetStats( $queueId );
		}
	}

	/**
	 * Clear options on start and finish.
	 */
	public function clearOptions() {
		$this->options->deleteOption( 'abort_current' );
	}

	protected function complete() {
		parent::complete();

		$this->clearOptions();

		do_action( $this->identifier . '_complete' );
	}

	protected function timeLeft() {
		if ( $this->maxExecutionTime > 0 ) {
			return $this->start_time + $this->maxExecutionTime - time();
		} else {
			return self::MAX_REQUEST_TIMEOUT;
		}
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	public function getOptions() {
		return $this->options;
	}

	public function getCurrentItem() {
		return '';
	}

	public function getProgress() {
		 $stats = $this->stats->getStats();

		$total     = $stats['total'];
		$processed = $stats['succeed'] + $stats['skipped'] + $stats['failed'] + $stats['removed'];

		if ( $total == 0 ) {
			return $this->isInProgress() ? 0 : 100;
		} else {
			return min( round( $processed / $total * 100 ), 100 );
		}
	}

	/**
	 * @param array $task
	 * @return array|false
	 */
	protected function task( $task ) {
		// See the structure of all the tasks below, in methods add*Tasks()

		if ( $this->isAborting() ) {
			$this->cancel_process();
			return false;
		}

		if ( ! isset( $task['action'] ) ) {
			return false;
		}

		// ugly fix of bug: Logger and Stat keep old value of queueId because of wp hooks in wp-async-request
		// TODO: better fix this is to remove queueId from Logger and Stat and get it from options each time!
		if ( ! empty( $task['queueId'] ) ) {

			$this->logger->setQueueId( $task['queueId'] );
			$this->stats->setQueueId( $task['queueId'] );
		}

		switch ( $task['action'] ) {
			case self::ACTION_PARSE:
				$task = $this->taskParse( $task );
				break;
			case self::ACTION_IMPORT:
				$task = $this->taskImport( $task );
				break;
			case self::ACTION_PULL_URLS:
				$task = $this->taskPullUrls( $task );
				break;
			case self::ACTION_CLEAN:
				$task = $this->taskClean( $task );
				break;
		}

		return $task;
	}

	/**
	 * @param array $workload [roomId]
	 */
	public function addPullUrlTask( $workload ) {
		$tasks = array(
			array_merge(
				$workload,
				array(
					'action'  => self::ACTION_PULL_URLS,
					'queueId' => $this->stats->getQueueId(),
				)
			),
		);

		$this->addTasks( $tasks );
	}

	/**
	 * @param array $workloads [[roomId, calendarUri, syncId, queueId], ...]
	 */
	public function addParseTasks( $workloads ) {
		$tasks = array_map(
			function ( $workload ) {
				$workload['action'] = BackgroundWorker::ACTION_PARSE;
				return $workload;
			},
			$workloads
		);

		$this->addTasks( $tasks );
	}

	/**
	 * @param array $workloads [[event, syncId, queueId], ...]
	 */
	public function addImportTasks( $workloads ) {
		$tasks = array_map(
			function ( $workload ) {
				$workload['action'] = BackgroundWorker::ACTION_IMPORT;
				return $workload;
			},
			$workloads
		);

		$this->addTasks( $tasks );
	}

	public function addCleanTasks( $workloads ) {
		$tasks = array_map(
			function ( $workload ) {
				$workload['action'] = BackgroundWorker::ACTION_CLEAN;
				return $workload;
			},
			$workloads
		);

		$this->addTasks( $tasks );
	}

	/**
	 * @param array $tasks
	 *
	 * @since 3.7.0 added new filter - "{identifier}_batch_size".
	 */
	protected function addTasks( $tasks ) {
		// Save new batches
		$batchSize = apply_filters( "{$this->identifier}_batch_size", self::BATCH_SIZE );
		$batches   = array_chunk( $tasks, $batchSize );

		foreach ( $batches as $batch ) {
			$this->data( $batch )->save();
		}

		$this->touch();
	}

	/**
	 * Mainly required for uploader: returns real file name instead of tmp name,
	 * like "/tmp/phpPRrGqo".
	 */
	abstract protected function retrieveCalendarNameFromSource( $calendarUri );

	/**
	 * @throws \MPHB\Exceptions\NoEnoughExecutionTimeException
	 * @throws \MPHB\Exceptions\RequestException
	 */
	abstract protected function retrieveCalendarContentFromSource( $calendarUri );

	/**
	 * @param array $task [roomId, calendarUri, syncId, queueId]
	 * @return array|false
	 */
	protected function taskParse( $task ) {
		$roomId       = $task['roomId'];
		$calendarUri  = $task['calendarUri'];
		$calendarName = $this->retrieveCalendarNameFromSource( $calendarUri );

		try {
			/**
			 * @throws \MPHB\Exceptions\NoEnoughExecutionTimeException
			 * @throws \MPHB\Exceptions\RequestException
			 */
			$calendarContent = $this->retrieveCalendarContentFromSource( $calendarUri );
			/**
			 * @throws \Exception
			 */
			$ical        = new \MPHB\iCal\iCal( $calendarContent );
			$events      = $ical->getEventsData( $roomId );
			$eventsCount = count( $events );

			if ( 0 < $eventsCount ) {

				// This info can replace some messages from background process if log it after the process starts
				$this->logger->info(
					sprintf(
						_nx(
							'%1$d event found in calendar %2$s',
							'%1$d events found in calendar %2$s',
							$eventsCount,
							'%s - calendar URI or calendar filename',
							'motopress-hotel-booking'
						),
						$eventsCount,
						$calendarName
					)
				);

				$importTasks = array_map(
					function ( $event ) use ( $task ) {
						return array(
							'event'   => $event,
							'syncId'  => $task['syncId'],
							'queueId' => $task['queueId'],
						);
					},
					$events
				);

				$this->addImportTasks( $importTasks );
				$this->stats->increaseImportsTotal( $eventsCount );

			} elseif ( empty( $calendarContent ) ) {

				$this->logger->info(
					sprintf(
						_x(
							'Calendar source is empty (%s)',
							'%s - calendar URI or calendar filename',
							'motopress-hotel-booking'
						),
						$calendarName
					)
				);

			} else {

				$this->logger->info(
					sprintf(
						_x(
							'Calendar file is not empty, but there are no events in %s',
							'%s - calendar URI or calendar filename',
							'motopress-hotel-booking'
						),
						$calendarName
					)
				);
			}

			// Remove all canceled bookings (which are not in import anymore )
			$oldBookingIds = MPHB()->getBookingRepository()->findAllByCalendar( $task['syncId'] );

			if ( ! empty( $oldBookingIds ) ) {

				$cleanTasks = array_map(
					function ( $bookingId ) use ( $task, $roomId ) {
						return array(
							'bookingId' => $bookingId,
							'syncId'    => $task['syncId'],
							'queueId'   => $task['queueId'],
							'roomId'    => $roomId,
						);
					},
					$oldBookingIds
				);

				$this->addCleanTasks( $cleanTasks );

				$tasksCount = count( $cleanTasks );
				$this->stats->increaseCleansTotal( $tasksCount );

				$logMessage = sprintf(
					_n( 'We will need to check %d previous booking after importing and remove it if the booking is outdated.', 'We will need to check %d previous bookings after importing and remove the outdated ones.', $tasksCount, 'motopress-hotel-booking' ),
					$tasksCount
				);

				$this->logger->info( $logMessage );
			}
		} catch ( NoEnoughExecutionTimeException $e ) {
			// Stop executing ACTION_PARSE taks, restart the process and give
			// more time to request files
			add_filter( $this->identifier . '_time_exceeded', '__return_true' );

			// Here can be problems on hosts with low max_execution_time:
			// - WP Background Processing library does not check the execution
			// time option and always schedule 20 seconds for every handle
			// cycle; so the process can fall and restart only by cron (only
			// every 5 minutes);
			// - process can go into an infinite loop, restarting every time
			// because of negative timeout.

			return $task;

		} catch ( RequestException $e ) {
			$this->logger->error( sprintf( __( 'Error while loading calendar (%1$s): %2$s', 'motopress-hotel-booking' ), $calendarUri, $e->getMessage() ) );
		} catch ( \Exception $e ) {
			$this->logger->error( sprintf( _x( 'Parse error. %s', '%s - error description', 'motopress-hotel-booking' ), $e->getMessage() ) );
		}

		return false;
	}

	/**
	 * @param array $task [event, syncId, queueId]
	 * @return array|false
	 */
	protected function taskImport( $task ) {
		$importStatus = $this->importer->import( $task['event'], $task['syncId'], $task['queueId'] );

		switch ( $importStatus ) {
			case ImportStatus::SUCCESS:
				$this->stats->increaseSucceedImports( 1 );
				break;

			case ImportStatus::SKIPPED:
				$this->stats->increaseSkippedImports( 1 );
				break;

			case ImportStatus::FAILED:
				$this->stats->increaseFailedImports( 1 );
				break;
		}

		return false;
	}

	/**
	 * @param array $task [bookingId, syncId, queueId, roomId]
	 * @return array|false
	 */
	protected function taskClean( $task ) {

		// Stored booking may have outdated information. Get updated meta fields
		$booking = MPHB()->getBookingRepository()->findById( $task['bookingId'], true );

		if ( null === $booking ) {
			// The booking was removed by "import" task
			$this->logger->info( sprintf( __( 'Skipped. Outdated booking #%d already removed.', 'motopress-hotel-booking' ), $task['bookingId'] ) );
			$this->stats->increaseSkippedCleans( 1 );
			return false;
		}

		if ( $booking->getSyncQueueId() === absint( $task['queueId'] ) ) {

			$this->logger->info( sprintf( __( 'Skipped. Booking #%d updated with new data.', 'motopress-hotel-booking' ), $task['bookingId'] ) );
			$this->stats->increaseSkippedCleans( 1 );
			return false;
		}

		if ( \MPHB\iCal\Importer::isBookingTooOldForImport( $booking->getCheckInDate() ) ) {

			$this->stats->increaseSkippedCleans( 1 );
			return false;
		}

		// Remove the outdated booking (booking exists, but its queue_id is too old)
		$reservedRooms = $booking->getReservedRooms();

		foreach ( $reservedRooms as $reservedRoom ) {
			MPHB()->getReservedRoomRepository()->delete( $reservedRoom );
		}

		MPHB()->getBookingRepository()->delete( $booking );

		$this->stats->increaseDoneCleans( 1 );
		$this->logger->success(
			sprintf(
				__( 'The outdated booking #%d has been removed.', 'motopress-hotel-booking' ),
				$task['bookingId']
			)
		);

		return false;
	}

	abstract protected function taskPullUrls( $task );

}
