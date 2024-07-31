<?php

namespace MPHB\Crons;

abstract class AbstractCron {

	const ACTION_PREFIX = 'mphb_cron_';

	/**
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Action hook to execute when cron is run.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * How often the event should recur. Registered WP Interval Name.
	 *
	 * @var string
	 */
	protected $interval;

	public function __construct( $id, $interval ) {
		$this->id       = $id;
		$this->action   = self::ACTION_PREFIX . $this->id;
		$this->interval = $interval;

		add_action( $this->action, array( $this, 'doCronJob' ) );
	}

	/**
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	public function setInterval( $interval ) {
		$this->interval = $interval;
	}

	abstract public function doCronJob();

	public function schedule() {
		if ( ! $this->isScheduled() ) {
			wp_schedule_event( time(), $this->interval, $this->action );
		}
	}

	public function scheduleAt( $timestamp ) {
		if ( ! $this->isScheduled() ) {
			wp_schedule_event( $timestamp, $this->interval, $this->action );
		}
	}

	public function unschedule() {
		// This also work for wp_schedule_single_event()
		wp_clear_scheduled_hook( $this->action );
	}

	public function isScheduled() {
		return (bool) wp_next_scheduled( $this->action );
	}

	/**
	 * @return int|false
	 *
	 * @since 3.7.2
	 */
	public function scheduledAt() {
		return wp_next_scheduled( $this->action );
	}

}
