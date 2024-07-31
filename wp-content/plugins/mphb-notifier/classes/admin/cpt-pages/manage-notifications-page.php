<?php

namespace MPHB\Notifier\Admin\CPTPages;

use MPHB\Admin\ManageCPTPages\ManageCPTPage;

/**
 * @since 1.0
 */
class ManageNotificationsPage extends ManageCPTPage {

	const PLACEHOLDER = '<span aria-hidden="true">&#8212;</span>';


	public function filterColumns( $columns ) {

		unset( $columns['date'] );

		$columns += array(
			'status'     => __( 'Status', 'mphb-notifier' ),
			'recipients' => __( 'Recipients', 'mphb-notifier' ),
			'trigger'    => __( 'Condition', 'mphb-notifier' ),
		);

		return $columns; // [cb, title, recipients, trigger]
	}

	public function renderColumns( $column, $postId ) {

		$notification = mphb_notifier()->repositories()->notification()->findById( $postId );

		if ( is_null( $notification ) ) {

			echo wp_kses_post( static::PLACEHOLDER );
			return;
		}

		switch ( $column ) {

			case 'status':
				echo esc_html( $notification->getStatusLabel() );
				break;

			case 'recipients':
				$recipients = array();

				if ( in_array( 'admin', $notification->getRecipients() ) ) {

					$recipients[] = esc_html__( 'Admin', 'mphb-notifier' );
				}

				if ( in_array( 'customer', $notification->getRecipients() ) ) {

					$recipients[] = esc_html__( 'Customer', 'mphb-notifier' );
				}

				if ( in_array( 'custom', $notification->getRecipients() ) ) {

					$customEmails = array_map(
						function ( $customEmail ) {
							return '<code>' . $customEmail . '</code>';
						},
						$notification->getCustomEmails()
					);

					$recipients = array_merge( $recipients, $customEmails );
				}

				if ( ! empty( $recipients ) ) {

					echo esc_html( implode( ', ', $recipients ) );

				} else {

					echo wp_kses_post( static::PLACEHOLDER );
				}
				break;

			case 'trigger':
				if ( $notification->isSendingAutomatic() ) {
					echo esc_html( mphb_notifier_convert_trigger_to_text( $notification->getTrigger() ) );
				} else {
					echo esc_html__( 'Manual', 'mphb-notifier' );
				}
				break;

			default:
				echo wp_kses_post( static::PLACEHOLDER );
				break;
		}
	}

	public function addDescriptionScript() {

		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$gmtOffset  = (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$dateFormat = get_option( 'time_format' ) . ', ' . get_option( 'date_format' ); // 3:27 pm, December 4, 2019

		$cron     = mphb_notifier()->cron();
		$nextTime = $cron->scheduledAt() + $gmtOffset;

		$lastExecution     = get_option( 'mphb_notifier_last_execution', false );
		$lastExecutionText = '';

		if ( mphb_notifier()->services()->sendNotifications()->isInProgress() ) {

			$lastExecutionText = esc_html__( 'Last execution — just now. Sending emails...', 'mphb-notifier' );

		} elseif ( false !== $lastExecution ) {

			$lastTime     = $lastExecution['execution_time'] + $gmtOffset;
			$lastTimeText = date_i18n( $dateFormat, $lastTime );

			$triggers = $lastExecution['notifications_triggered'];
			$emails   = $lastExecution['emails_sent'];

			if ( $emails == 0 ) {

				// translators: %s is a time
				$lastExecutionText = sprintf( esc_html__( 'Last execution was at %s. No new emails were sent for any of the notifications.', 'mphb-notifier' ), $lastTimeText );

			} else {

				// translators: %d is count of notifications
				$triggersText = sprintf( esc_html( _n( '%d notification', '%d notifications', $triggers, 'mphb-notifier' ) ), $triggers );
				// translators: %d is count of emails
				$emailsText = sprintf( esc_html( _n( '%d email', '%d emails', $emails, 'mphb-notifier' ) ), $emails );

				// translators: Example: "Last execution was at 3:27 pm. Was sent X emails for Y notifications."
				$lastExecutionText = sprintf( esc_html__( 'Last execution was at %1$s. %2$s for %3$s were sent.', 'mphb-notifier' ), $lastTimeText, $emailsText, $triggersText );
			}
		}

		$cronInfo = '';

		if ( ! empty( $lastExecutionText ) ) {

			$cronInfo .= '<p class="mphb-posts-additional-info">' . $lastExecutionText . '</p>';
		}

		if ( false !== $nextTime ) {

			$nextTimeText = date_i18n( $dateFormat, $nextTime );
			// translators: %s is time
			$cronInfo .= '<p class="mphb-posts-additional-info">' . sprintf( esc_html__( 'Next execution is scheduled for — %s.', 'mphb-notifier' ), $nextTimeText ) . '</p>';

		} else {

			$cronInfo .= '<p class="mphb-posts-additional-info">' . esc_html__( 'Failed to schedule next execution in your WordPress installation.', 'mphb-notifier' ) . '</p>';
		}

		// Add cront task notice. We don't really need the "action" argument in
		// the URL, but just mark our "requests"
		$url      = add_query_arg( 'action', $cron->getAction(), home_url( 'wp-cron.php' ) );
		$cronTask = '0 */6 * * * ' . $url;

		$cronInfo .= '<p class="mphb-posts-additional-info">';
		// translators: %s is a code
		$cronInfo .= sprintf( esc_html__( 'You can set up a real cron task in your server admin panel: %s', 'mphb-notifier' ), '<code>' . $cronTask . '</code>' );
		$cronInfo .= '</p>';

		?>
		<script type="text/javascript">
			jQuery(function () {
				var pageSettings = <?php echo json_encode( array( 'cron_info' => $cronInfo ) ); ?>;
				var cronInfo = jQuery(pageSettings.cron_info);

				jQuery('#posts-filter > .wp-list-table').first().before(cronInfo);
			});
		</script>
		<?php
	}
}
