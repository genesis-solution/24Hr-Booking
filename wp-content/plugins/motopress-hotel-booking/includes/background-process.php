<?php

namespace MPHB;

use MPHB\Libraries\WP_Background_Processing\WP_Background_Process;

/**
 * @note Action name length must be less than or equal to 19 symbols. Length of
 *       option name is 64. Option name consist of:
 *           + (1-4) blog id
 *           + (8) prefix "mphb_bg" + prefix separator "_"
 *           + (16 <=) action name of background process
 *           + (13) suffix "_process_lock"
 *           + (23) wp's transient prefix "_site_transient_timeout"
 *
 * @since 3.5.0 the class has been completely rewritten.
 */
abstract class BackgroundProcess extends WP_Background_Process {

	protected $prefix = 'mphb_bg';

	protected $tasksCountOption;
	protected $completedTasksOption;

	public function __construct() {
		$blogId = get_current_blog_id();

		if ( $blogId > 1 ) {
			$this->prefix = $this->prefix . $blogId;
		}

		parent::__construct();

		$this->tasksCountOption     = $this->identifier . '_tasks_count';
		$this->completedTasksOption = $this->identifier . '_completed_tasks_count';
	}

	protected function complete() {
		parent::complete();

		$this->afterComplete();
	}

	protected function afterComplete() {
		delete_option( $this->tasksCountOption );
		delete_option( $this->completedTasksOption );

		do_action( $this->identifier . '_complete' );
	}

	/**
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @return bool
	 */
	public function isInProgress() {
		// The main check is is_queue_empty(). But we also need to check if the
		// process actually stopped (unlocked) - is_process_running()
		return $this->is_process_running() || ! $this->is_queue_empty();
	}

	/**
	 * @return int Only the size of the queue left to proceed (exclude completed
	 *             batches).
	 */
	public function getQueueSize() {
		global $wpdb;

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		} else {
			$table  = $wpdb->options;
			$column = 'option_name';
		}

		$search = $this->identifier . '_batch_%';

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE {$column} LIKE %s",
				$search
			)
		);

		return (int) $count;
	}

	/**
	 * @return int
	 */
	public function getTasksCount() {
		return (int) get_option( $this->tasksCountOption, 0 );
	}

	/**
	 * @param int $count
	 */
	protected function setTasksCount( $count ) {
		update_option( $this->tasksCountOption, $count );
	}

	/**
	 * @param int $amount
	 */
	protected function increaseTasksCount( $amount ) {
		$this->setTasksCount( $this->getTasksCount() + $amount );
	}

	/**
	 * @return int
	 */
	public function getCompletedTasksCount() {
		return (int) get_option( $this->completedTasksOption, 0 );
	}

	/**
	 * @param int $count
	 */
	protected function setCompletedTasksCount( $count ) {
		update_option( $this->completedTasksOption, $count );
	}

	/**
	 * @param int $amount Optional. 1 by default.
	 */
	protected function increaseCompletedTasksCount( $amount = 1 ) {
		$this->setCompletedTasksCount( $this->getCompletedTasksCount() + $amount );
	}

	protected function after_task_done() {
		$this->increaseCompletedTasksCount();
	}

	/**
	 * @return float The progress value in range [0; 100].
	 */
	public function getProgress() {
		$total     = $this->getTasksCount();
		$completed = $this->getCompletedTasksCount();

		if ( $total > 0 ) {
			$progress = round( $completed / $total * 100, 2 );
			$progress = max( 0, min( $progress, 100 ) );
		} else {
			$progress = 100;
		}

		return $progress;
	}

	public function save() {
		parent::save();

		if ( ! empty( $this->data ) ) {
			$this->increaseTasksCount( count( $this->data ) );
		}

		return $this;
	}
}
