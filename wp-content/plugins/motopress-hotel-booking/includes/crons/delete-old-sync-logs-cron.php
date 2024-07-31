<?php

namespace MPHB\Crons;

/**
 * @since 3.6.1
 */
class DeleteOldSyncLogsCron extends AbstractCron {

	public function doCronJob() {

		$period = MPHB()->settings()->main()->deleteSyncLogsOlderThan();

		if ( 'never' == $period ) {
			return;
		}

		$date = new \DateTime( '-6 months' );

		switch ( $period ) {
			case 'day':
				$date = new \DateTime( '-1 day' );
				break;
			case 'week':
				$date = new \DateTime( '-1 week' );
				break;
			case 'month':
				$date = new \DateTime( '-1 month' );
				break;
			case 'quarter':
				$date = new \DateTime( '-3 months' );
				break;
			case 'half_year':
				$date = new \DateTime( '-6 months' );
				break;
		}

		$timestamp = $date->getTimestamp();

		global $wpdb;

		$queueTable = $wpdb->prefix . \MPHB\iCal\Queue::TABLE_NAME;
		$statTable  = $wpdb->prefix . \MPHB\iCal\Stats::TABLE_NAME;
		$logsTable  = $wpdb->prefix . \MPHB\iCal\Logger::TABLE_NAME;

		$wpdb->query( "DELETE FROM $logsTable WHERE queue_id IN (SELECT queue_id FROM $queueTable WHERE queue_name < '$timestamp')" );
		$wpdb->query( "DELETE FROM $statTable WHERE queue_id IN (SELECT queue_id FROM $queueTable WHERE queue_name < '$timestamp')" );
		$wpdb->query( "DELETE FROM $queueTable WHERE queue_id IN ( SELECT * FROM (SELECT queue_id FROM $queueTable WHERE queue_name < '$timestamp') AS p )" );
	}
}
