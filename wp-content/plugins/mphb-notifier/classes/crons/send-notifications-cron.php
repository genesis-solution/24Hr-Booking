<?php

namespace MPHB\Notifier\Crons;

use MPHB\Crons\AbstractCron;

class SendNotificationsCron extends AbstractCron {

	public function doCronJob() {

		mphb_notifier()->services()->sendNotifications()->triggerAll();
	}

	/**
	 * @return int/false
	 */
	public function scheduledAt() {
        
		return wp_next_scheduled( $this->action );
	}
}
