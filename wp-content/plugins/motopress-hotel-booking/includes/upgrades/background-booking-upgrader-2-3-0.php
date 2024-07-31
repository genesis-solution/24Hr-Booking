<?php

namespace MPHB\Upgrades;

class BackgroundBookingUpgrader_2_3_0 extends \MPHB\BackgroundPausableProcess {

	/**
	 *
	 * @var string
	 */
	protected $action = '2_3_0';

	/**
	 *
	 * @param array   $oldRule
	 * @param string  $oldRule['title']
	 * @param string  $oldRule['description']
	 * @param string  $oldRule['date_from']
	 * @param string  $oldRule['date_to']
	 * @param boolean $oldRule['not_check_in']
	 * @param boolean $oldRule['not_check_out']
	 * @param boolean $oldRule['not_stay_in']
	 *
	 * @return boolean
	 */
	protected function task( $oldRule ) {
		$customRule = array(
			'room_type_id' => 0, // All
			'room_id'      => 0, // All
			'date_from'    => $oldRule['date_from'],
			'date_to'      => $oldRule['date_to'],
			'restrictions' => array(),
			'comment'      => $oldRule['description'],
		);

		if ( $oldRule['not_check_in'] ) {
			$customRule['restrictions'][] = 'check-in';
		}
		if ( $oldRule['not_check_out'] ) {
			$customRule['restrictions'][] = 'check-out';
		}
		if ( $oldRule['not_stay_in'] ) {
			$customRule['restrictions'][] = 'stay-in';
		}

		$customRules   = MPHB()->settings()->bookingRules()->getCustomRules();
		$customRules[] = $customRule;
		update_option( 'mphb_booking_rules_custom', $customRules, 'no' );

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
