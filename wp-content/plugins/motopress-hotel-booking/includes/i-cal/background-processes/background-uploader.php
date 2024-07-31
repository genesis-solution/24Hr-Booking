<?php

namespace MPHB\iCal\BackgroundProcesses;

use MPHB\iCal\Queue;

class BackgroundUploader extends BackgroundWorker {

	protected $action = 'upload';

	public function getCurrentItem() {
		return 'upload';
	}

	/**
	 * Reset only before new start. On finish you'll reset the stats.
	 */
	public function reset() {
		$queueItem = $this->getCurrentItem();
		Queue::createUploaderItem( $queueItem );

		parent::reset();

		$this->logger->clear();
	}

	/**
	 * Parse new events immediately and add new "import" tasks.
	 *
	 * @param int    $roomId
	 * @param string $calendarUri
	 */
	public function parseCalendar( $roomId, $calendarUri ) {
		$calendarName = $this->retrieveCalendarNameFromSource( $calendarUri );

		$this->taskParse(
			array(
				'roomId'      => $roomId,
				'calendarUri' => $calendarUri,
				'syncId'      => md5( $calendarName ),
				'queueId'     => $this->stats->getQueueId(),
			)
		);
	}

	protected function retrieveCalendarNameFromSource( $calendarUri ) {

		if ( isset( $_FILES['import'] ) && isset( $_FILES['import']['name'] ) ) {
			return sanitize_text_field( wp_unslash( $_FILES['import']['name'] ) );
		} else {
			return $calendarUri;
		}
	}

	/**
	 * @param string $calendarUri
	 * @return string
	 */
	protected function retrieveCalendarContentFromSource( $calendarUri ) {
		$calendarContent = @file_get_contents( $calendarUri );
		if ( $calendarContent === false ) {

			// TODO add context to log
			$this->logger->error( __( 'Cannot read uploaded file', 'motopress-hotel-booking' ) );
			return '';
		} else {
			return $calendarContent;
		}
	}

	protected function taskPullUrls( $task ) {
		// Uploader does not need such task
		return false;
	}

	/**
	 * @param int $skipLogs Optional. How many logs to skip (how many logs
	 *                      already shown). 0 by default.
	 * @return [array "logs", array "stats"]
	 */
	public function getDetails( $skipLogs = 0 ) {
		return array(
			'logs'  => $this->logger->getLogs( $skipLogs ),
			'stats' => $this->stats->getStats(),
		);
	}

}
