<?php

namespace MPHB;

/**
 * @since 3.7.0
 */
class Notices {

	protected $notices = array(
		// Notice ID => Render callback
		'force_upgrade'                 => 'forceUpgradeNotice',
		'update_confirmation_endpoints' => 'updateEndpointsNotice',
	);

	protected $capabilities = array(
		// Notice ID => Required capabilities
		'force_upgrade'                 => array(),
		'update_confirmation_endpoints' => array( 'manage_options', 'edit_pages' ),
	);

	protected $passedNotices = array();

	public function __construct() {
		$this->addActions();

		// Load notices even on AJAX calls. Otherwise we will show all notices
		// on each upgrade
		$this->loadPassedNotices();
	}

	public function addActions() {
		add_action( 'admin_notices', array( $this, 'displayNotices' ) );
	}

	protected function loadPassedNotices() {
		// Use ["force_upgrade"] when there is no value and don't accidentally
		// show upgrade message (not so important after version 3.7.0)
		$passedNotices       = get_option( 'mphb_passed_notices', array( 'force_upgrade' ) );
		$this->passedNotices = is_array( $passedNotices ) ? $passedNotices : array();
	}

	public function displayNotices() {
		foreach ( $this->notices as $noticeId => $renderMethod ) {
			// Skip passed or not actual notices
			if ( in_array( $noticeId, $this->passedNotices ) ) {
				continue;
			} elseif ( ! $this->isActualNotice( $noticeId ) ) {
				$this->hideNotice( $noticeId );
				continue;
			}

			// Skip if user can't perform the action
			if ( ! $this->checkCapabilities( $noticeId ) ) {
				continue;
			}

			// Display the notice
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->displayNotice( $noticeId, $renderMethod );
		}
	}

	public function displayNotice( $noticeId, $renderMethod ) {
		$actionUrl = wp_nonce_url(
			add_query_arg(
				array(
					'mphb_action' => $noticeId,
				)
			),
			$noticeId,
			'mphb_notice_nonce'
		);

		$cancelUrl = wp_nonce_url(
			add_query_arg(
				array(
					'mphb_action' => 'hide_notice',
					'notice_id'   => $noticeId,
				)
			),
			'hide_notice',
			'mphb_notice_nonce'
		);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->$renderMethod( $actionUrl, $cancelUrl );
	}

	public function isActualNotice( $noticeId ) {
		switch ( $noticeId ) {
			case 'update_confirmation_endpoints':
				return MPHB()->settings()->pages()->getBookingConfirmedPageId() != 0
					|| MPHB()->settings()->pages()->getReservationReceivedPageId() != 0;
				break;
		}

		return true;
	}

	public function checkCapabilities( $noticeId ) {
		if ( ! array_key_exists( $noticeId, $this->capabilities ) ) {
			return false;
		}

		foreach ( $this->capabilities[ $noticeId ] as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				return false;
			}
		}

		return true;
	}

	public function showNotice( $noticeId ) {
		$itemIndex = array_search( $noticeId, $this->passedNotices );

		if ( $itemIndex !== false ) {
			unset( $this->passedNotices[ $itemIndex ] );
			update_option( 'mphb_passed_notices', $this->passedNotices );
		}
	}

	public function hideNotice( $noticeId ) {
		if ( ! in_array( $noticeId, $this->passedNotices ) ) {
			$this->passedNotices[] = $noticeId;
			update_option( 'mphb_passed_notices', $this->passedNotices );
		}
	}

	/**
	 * @param string $actionUrl
	 * @param string $cancelUrl
	 * @return string Notice HTML.
	 */
	public function forceUpgradeNotice( $actionUrl, $cancelUrl ) {
		$output      = '<div class="updated mphb-admin-notice">';
			$output .= '<p><strong>' . __( 'Hotel Booking Plugin', 'motopress-hotel-booking' ) . '</strong></p>';
			$output .= '<p>' . __( 'Your database is being updated in the background.', 'motopress-hotel-booking' ) . '</p>';
			$output .= '<p>'
				. '<a href="' . esc_url( $actionUrl ) . '">' . __( 'Taking a while? Click here to run it now.', 'motopress-hotel-booking' ) . '</a>'
				. ' (' . MPHB()->upgrader()->getProgress() . '%)'
				. '</p>';
		$output     .= '</div>';

		return $output;
	}

	/**
	 * @param string $actionUrl
	 * @param string $cancelUrl
	 * @return string Notice HTML.
	 */
	public function updateEndpointsNotice( $actionUrl, $cancelUrl ) {
		$output      = '<div class="updated">';
			$output .= '<p><strong>' . __( 'Hotel Booking Plugin', 'motopress-hotel-booking' ) . '</strong></p>';
			$output .= '<p>' . __( 'Add "Booking Confirmation" shortcode to your "Booking Confirmed" and "Reservation Received" pages to show more details about booking or payment.<br/>Click "Update Pages" to apply all changes automatically or skip this notice and add "Booking Confirmation" shortcode manually.<br/><b><em>This action will replace the whole content of the pages.</em></b>', 'motopress-hotel-booking' ) . '</p>';
			$output .= '<p>'
				. '<a href="' . esc_url( $actionUrl ) . '" class="button-primary">' . esc_html__( 'Update Pages', 'motopress-hotel-booking' ) . '</a>'
				. '&nbsp;'
				. '<a href="' . esc_url( $cancelUrl ) . '" class="button-secondary">' . esc_html__( 'Skip', 'motopress-hotel-booking' ) . '</a>'
				. '</p>';
		$output     .= '</div>';

		return $output;
	}
}
