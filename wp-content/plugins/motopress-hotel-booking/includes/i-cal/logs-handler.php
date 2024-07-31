<?php

namespace MPHB\iCal;

class LogsHandler {

	/**
	 * @param array $processDetails [logs, stats => [total, succeed, ...]]
	 *
	 * @see \MPHB\iCal\Importer::addLog()
	 */
	public function display( $processDetails ) {
		$logs  = $processDetails['logs'];
		$stats = $processDetails['stats'];

		$this->displayTitle();

		$this->displayStats( $stats );

		$this->displayLogs( $logs );
	}

	public function displayTitle() {
		echo '<h3>';
		esc_html_e( 'Process Information', 'motopress-hotel-booking' );
		echo '</h3>';
	}

	/**
	 *
	 * @param array $stats [total, succeed, ...]
	 */
	public function displayStats( $stats ) {
		echo '<p class="mphb-import-stats">';
		echo sprintf( esc_html__( 'Total bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-total">' . esc_html( $stats['total'] ) . '</span>' );
		echo '<br />';
		echo sprintf( esc_html__( 'Success bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-succeed">' . esc_html( $stats['succeed'] ) . '</span>' );
		echo '<br />';
		echo sprintf( esc_html__( 'Skipped bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-skipped">' . esc_html( $stats['skipped'] ) . '</span>' );
		echo '<br />';
		echo sprintf( esc_html__( 'Failed bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-failed">' . esc_html( $stats['failed'] ) . '</span>' );
		echo '<br />';
		echo sprintf( esc_html__( 'Removed bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-removed">' . esc_html( $stats['removed'] ) . '</span>' );
		echo '</p>';
	}

	/**
	 * @param array $logs
	 */
	public function displayLogs( $logs = array() ) {
		echo '<ol class="mphb-logs">';
		foreach ( $logs as $log ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->logToHtml( $log );
		}
		echo '</ol>';
	}

	public function displayProgress() {
		echo '<div class="mphb-progress">';
		echo '<div class="mphb-progress__bar"></div>';
		echo '<div class="mphb-progress__text">0%</div>';
		echo '</div>';
	}

	/**
	 *
	 * @param bool $disabled
	 */
	public function displayAbortButton( $disabled = false ) {
		$disabledAttr = $disabled ? ' disabled="disabled"' : '';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<button class="button mphb-abort-process"' . $disabledAttr . '>' . esc_html__( 'Abort Process', 'motopress-hotel-booking' ) . '</button>';
	}

	/**
	 *
	 * @param bool $disabled
	 */
	public function displayClearButton( $disabled = false ) {
		$disabledAttr = $disabled ? ' disabled="disabled"' : '';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<button class="button mphb-clear-all"' . $disabledAttr . '>' . esc_html__( 'Delete All Logs', 'motopress-hotel-booking' ) . '</button>';
	}

	public function displayExpandAllButton() {
		echo '<button class="button-link mphb-expand-all">' . esc_html__( 'Expand All', 'motopress-hotel-booking' ) . '</button>';
	}

	public function displayCollapseAllButton() {
		echo '<button class="button-link mphb-collapse-all">' . esc_html__( 'Collapse All', 'motopress-hotel-booking' ) . '</button>';
	}

	/**
	 * @param array $log Log entry ["status", "message"].
	 * @param bool  $inline
	 * @return string
	 */
	public function logToHtml( $log, $inline = false ) {
		$log += array(
			'status'  => 'info',
			'message' => '',
		);

		$html = '';

		if ( ! empty( $log['message'] ) && ! $inline ) {
			$html         .= '<li>';
				$html     .= '<p class="notice notice-' . esc_attr( $log['status'] ) . '">';
					$html .= $log['message'];
				$html     .= '</p>';
			$html         .= '</li>';

		} else {
			$html .= $log['message'];
		}

		return $html;
	}

	/**
	 * Build HTML for each log.
	 *
	 * @param array $logs
	 * @param bool  $inline
	 *
	 * @return array
	 */
	public function logsToHtml( $logs, $inline = false ) {
		$logsHtml = array();
		foreach ( $logs as $log ) {
			$logsHtml[] = $this->logToHtml( $log, $inline );
		}
		return $logsHtml;
	}

	public function buildNotice( $succeedCount, $failedCount ) {
		$message  = _n( 'All done! %1$d booking was successfully added.', 'All done! %1$d bookings were successfully added.', $succeedCount, 'motopress-hotel-booking' );
		$message .= _n( ' There was %2$d failure.', ' There were %2$d failures.', $failedCount, 'motopress-hotel-booking' );
		$message  = sprintf( $message, $succeedCount, $failedCount );

		$notice  = '<div class="updated notice notice-success is-dismissible">';
		$notice .= '<p>' . $message . '</p>';
		$notice .= '</div>';

		return $notice;
	}

}
