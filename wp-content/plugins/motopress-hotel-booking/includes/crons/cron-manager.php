<?php

namespace MPHB\Crons;

class CronManager {

	const INTERVAL_PENDING_USER_APPROVAL = 'mphb_pending_user_approval';
	const INTERVAL_PENDING_PAYMENT       = 'mphb_pending_payment';
	const INTERVAL_AUTODELETE_SYNC_LOGS  = 'mphb_ical_auto_delete';

	const INTERVAL_QUARTER_AN_HOUR = 'mphb_15m';
	const INTERVAL_HALF_AN_HOUR    = 'mphb_30m';

	// Default WordPress intervals
	const INTERVAL_DAILY       = 'daily';
	const INTERVAL_TWICE_DAILY = 'twicedaily';
	const INTERVAL_HOURLY      = 'hourly';

	/**
	 *
	 * @var Cron[]
	 */
	private $crons = array();

	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'createCronIntervals' ) );

		$this->initCrons();
	}

	/**
	 * @since 3.6.1 added new cron - DeleteOldSyncLogsCron.
	 */
	public function initCrons() {

		$crons = array(
			new AbandonBookingPendingUserCron(
				'abandon_booking_pending_user',
				self::INTERVAL_PENDING_USER_APPROVAL
			),
			new AbandonBookingPendingPaymentCron(
				'abandon_booking_pending_payment',
				self::INTERVAL_PENDING_PAYMENT
			),
			new AbandonPaymentPendingCron(
				'abandon_payment_pending',
				self::INTERVAL_PENDING_PAYMENT
			),
			new IcalAutoSynchronizationCron(
				'ical_auto_synchronization',
				get_option( 'mphb_ical_auto_sync_interval', self::INTERVAL_DAILY )
			),
			new DeleteOldSyncLogsCron(
				'ical_auto_delete',
				self::INTERVAL_AUTODELETE_SYNC_LOGS
			),
		);

		foreach ( $crons as $cron ) {
			$this->addCron( $cron );
		}
	}

	/**
	 *
	 * @param Cron $cron
	 */
	public function addCron( $cron ) {
		$this->crons[ $cron->getId() ] = $cron;
	}

	/**
	 *
	 * @param string $id
	 * @return Cron|null
	 */
	public function getCron( $id ) {
		return isset( $this->crons[ $id ] ) ? $this->crons[ $id ] : null;
	}

	/**
	 * @param array $schedules
	 * @return array
	 *
	 * @since 3.6.1 added new interval - "Interval for automatic cleaning of synchronization logs".
	 * @since 3.6.1 added new interval - "Quarter an Hour".
	 * @since 3.6.1 added new interval - "Half an Hour".
	 */
	public function createCronIntervals( $schedules ) {

		$schedules[ self::INTERVAL_QUARTER_AN_HOUR ] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'Quarter an Hour', 'motopress-hotel-booking' ),
		);

		$schedules[ self::INTERVAL_HALF_AN_HOUR ] = array(
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display'  => __( 'Half an Hour', 'motopress-hotel-booking' ),
		);

		$schedules[ self::INTERVAL_PENDING_USER_APPROVAL ] = array(
			'interval' => MPHB()->settings()->main()->getUserApprovalTime() * MINUTE_IN_SECONDS,
			'display'  => __( 'User Approval Time setted in Hotel Booking Settings', 'motopress-hotel-booking' ),
		);

		$schedules[ self::INTERVAL_PENDING_PAYMENT ] = array(
			'interval' => MPHB()->settings()->payment()->getPendingTime() * MINUTE_IN_SECONDS,
			'display'  => __( 'Pending Payment Time set in Hotel Booking Settings', 'motopress-hotel-booking' ),
		);

		$schedules[ self::INTERVAL_AUTODELETE_SYNC_LOGS ] = array(
			'interval' => 6 * HOUR_IN_SECONDS,
			'display'  => __( 'Interval for automatic cleaning of synchronization logs.', 'motopress-hotel-booking' ),
		);

		return $schedules;
	}

	/**
	 *
	 * @param bool   $enable
	 * @param string $clock Time in 12-hour or 24-hour format: "08:15 pm" or "20:15".
	 * @param string $interval Cron interval name.
	 */
	public function rescheduleAutoSynchronization( $enable, $clock = '01:00', $interval = self::INTERVAL_DAILY ) {
		$cron = $this->getCron( 'ical_auto_synchronization' );

		if ( ! $enable ) {
			$cron->unschedule();
			delete_option( 'mphb_ical_auto_sync_previous_clock' );
			delete_option( 'mphb_ical_auto_sync_previous_interval' );
			delete_option( 'mphb_ical_auto_sync_worked_once' );
			return;
		}

		$previousClock    = get_option( 'mphb_ical_auto_sync_previous_clock', false );
		$previousInterval = get_option( 'mphb_ical_auto_sync_previous_interval', false );

		$clockChanged    = ( $previousClock === false || $clock != $previousClock );
		$intervalChanged = ( $previousInterval === false || $interval != $previousInterval );
		$syncWorkedOnce  = (bool) get_option( 'mphb_ical_auto_sync_worked_once', false );

		if ( ! $clockChanged && ! $intervalChanged ) {
			// No changes made to settings
			return;
		}

		if ( $clockChanged ) {
			$scheduledTimestamp = \MPHB\Utils\DateUtils::nextTimestampWithTime( $clock );

		} else { // if ( $intervalChanged )
			$scheduledTimestamp = wp_next_scheduled( $cron->getAction() );

			// Wait less, only if the process was started (worked at least once)
			if ( $scheduledTimestamp !== false && $syncWorkedOnce ) {
				$schedules    = wp_get_schedules();
				$intervalTime = $schedules[ $interval ]['interval'];
				$currentTime  = time();
				$waitTime     = $scheduledTimestamp - $currentTime;
				if ( $waitTime > $intervalTime ) {
					$scheduledTimestamp = $currentTime + $intervalTime;
				}
			} else {
				$scheduledTimestamp = \MPHB\Utils\DateUtils::nextTimestampWithTime( $clock );
			}
		}

		$cron->unschedule();
		$cron->setInterval( $interval );
		$cron->scheduleAt( $scheduledTimestamp );

		update_option( 'mphb_ical_auto_sync_previous_clock', $clock, 'no' );
		update_option( 'mphb_ical_auto_sync_previous_interval', $interval, 'no' );
	}

}
