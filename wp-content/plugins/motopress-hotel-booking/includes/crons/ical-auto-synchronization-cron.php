<?php

namespace MPHB\Crons;

class IcalAutoSynchronizationCron extends AbstractCron {

	public function doCronJob() {

		$ids = MPHB()->getRoomPersistence()->getPosts(
			array(
				'orderby' => 'ID',
				'order'   => 'ASC',
			)
		);

		MPHB()->getQueuedSynchronizer()->sync( $ids );
		update_option( 'mphb_ical_auto_sync_worked_once', true, 'no' );
	}
}
