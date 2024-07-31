<?php

namespace MPHB;

/**
 * @note Action name length must be less than or equal to 19 symbols. Length of
 *       option name is 64. Option name consist of:
 *           + (1-4) blog id
 *           + (8) prefix "mphb_bg" + prefix separator "_"
 *           + (16 <=) action name of background process
 *           + (13) suffix "_process_lock"
 *           + (23) wp's transient prefix "_site_transient_timeout"
 *
 * @since 3.5.0
 */
abstract class BackgroundPausableProcess extends BackgroundProcess {

	/**
	 * @var bool
	 */
	protected $paused = false;

	/**
	 * @var string
	 */
	protected $waitActionsOption;

	public function __construct() {
		parent::__construct();

		$this->waitActionsOption = $this->identifier . '_wait_actions';

		foreach ( $this->getWaitingActions() as $action ) {
			add_action( $action, array( $this, 'handleWaitAction' ) );
		}
	}

	/**
	 * @param string $action
	 */
	public function waitAction( $action ) {
		$actions = $this->getWaitingActions();

		if ( ! in_array( $action, $actions ) ) {
			$actions[] = $action;
			update_option( $this->waitActionsOption, $actions, 'no' );
		}
	}

	public function handleWaitAction() {
		$actions = array_filter(
			$this->getWaitingActions(),
			function ( $action ) {
				return ! doing_action( $action ) && ! did_action( $action );
			}
		);

		if ( empty( $actions ) ) {
			delete_option( $this->waitActionsOption );
			$this->dispatch();
		} else {
			update_option( $this->waitActionsOption, $actions, 'no' );
		}
	}

	public function pause() {
		$this->paused = true;

		$this->unlock_process();
		$this->schedule_event();
	}

	/**
	 * @return bool
	 */
	protected function is_process_running() {
		$actions = $this->getWaitingActions();
		return parent::is_process_running() || ! empty( $actions );
	}

	/**
	 * @return array
	 */
	protected function getWaitingActions() {
		return get_option( $this->waitActionsOption, array() );
	}

	/**
	 * Restart the background process if not already running and data exists in
	 * the queue.
	 *
	 * Override parent method to replace each exit() with return statement.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running
		} elseif ( $this->is_queue_empty() ) {
			// No data to process
			$this->clear_scheduled_event();
			$this->afterComplete();
		} else {
			$this->handle();
		}
	}

	/**
	 * Override parent method for make possible handle without wp_die in the
	 * end. Also add $this->paused to handle().
	 *
	 * @return bool
	 */
	protected function handle() {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( $task !== false ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
					$this->after_task_done( $value );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached
					break;
				}

				if ( $this->paused ) {
					// Process is paused
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() && ! $this->paused );

		$this->unlock_process();

		// Start next batch or complete process
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}

		return;
	}
}
