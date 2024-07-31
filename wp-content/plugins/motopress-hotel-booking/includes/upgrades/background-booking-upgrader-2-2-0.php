<?php

namespace MPHB\Upgrades;

use \MPHB\Entities;

class BackgroundBookingUpgrader_2_2_0 extends \MPHB\BackgroundPausableProcess {

	const BATCH_SIZE = 500;

	/**
	 * @var string
	 */
	protected $action = '2_2_0';

	protected function task( $roomId ) {
		update_post_meta( $roomId, '_mphb_uid', mphb_generate_uid(), '' );
		return false;
	}

	protected function get_batch() {
		$batch = parent::get_batch();

		if ( ! empty( $batch ) && property_exists( $batch, 'data' ) && ! empty( $batch->data ) ) {
			// Fill bookings meta
			update_postmeta_cache( $batch->data );
		}

		return $batch;
	}
}
