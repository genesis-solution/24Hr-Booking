<?php

namespace MPHB\Upgrades;

use \MPHB\Entities;

class BackgroundUpgrader extends \MPHB\BackgroundPausableProcess {

	protected $action = 'upgrader';

	protected function task( $callback ) {

		if ( method_exists( MPHB()->upgrader(), $callback ) ) {
			return call_user_func( array( MPHB()->upgrader(), $callback ) );
		}
		return false;
	}
}
