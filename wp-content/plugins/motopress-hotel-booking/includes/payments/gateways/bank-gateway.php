<?php

namespace MPHB\Payments\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 3.6.1
 */
class BankGateway extends Gateway {

	const PAYMENT_GATEWAY_ID = 'bank';


	public function __construct() {

		add_filter(
			'mphb_gateway_has_sandbox',
			function( bool $isShow, string $gatewayId ) {
				return $gatewayId === $this->getId() ? false : $isShow;
			},
			10,
			2
		);

		parent::__construct();

		add_action(
			'plugins_loaded',
			function() {

				$emailTemplater = \MPHB\Emails\Templaters\EmailTemplater::create(
					array(
						'booking'         => true,
						'booking_details' => true,
						'payment'         => true,
					)
				);

				$emailTemplater->setupTags();

				MPHB()->emails()->addEmail(
					new \MPHB\Emails\Booking\Customer\DirectBankTransferEmail(
						array(
							'id' => \MPHB\Emails\Booking\Customer\DirectBankTransferEmail::EMAIL_ID,
						),
						$emailTemplater
					)
				);
			},
			100
		);
	}

	protected function initId() {
		return static::PAYMENT_GATEWAY_ID;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		return static::MODE_LIVE;
	}

	/**
	 * @return bool
	 */
	public function isSandbox() {
		return false;
	}


	protected function setupProperties() {

		parent::setupProperties();

		$this->adminTitle = __( 'Direct Bank Transfer', 'motopress-hotel-booking' );
	}


	protected function initDefaultOptions() {

		return array_merge(
			parent::initDefaultOptions(),
			array(
				'title'                            => __( 'Direct Bank Transfer', 'motopress-hotel-booking' ),
				'description'                      => __( 'Make your payment directly into our bank account. Please use your Booking ID as the payment reference.', 'motopress-hotel-booking' ),
				'enabled'                          => false,
				'is_auto_abandon_bookings'         => false,
				'payment_and_booking_pending_time' => 120,
			)
		);
	}

	/**
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function registerOptionsFields( &$subTab ) {

		parent::registerOptionsFields( $subTab );

		$group = new \MPHB\Admin\Groups\SettingsGroup(
			"mphb_payments_{$this->getId()}_group2",
			'',
			$subTab->getOptionGroupName()
		);

		$groupFields = array(
			\MPHB\Admin\Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->getId()}_is_auto_abandon_bookings",
				array(
					'type'        => 'checkbox',
					'inner_label' => __( 'Enable Auto-Abandonment', 'motopress-hotel-booking' ),
					'description' => __( 'Automatically abandon bookings and release reserved slots if payment is not received within a specified time period. You need to manually set the status of paid payments to Completed to avoid automatic abandonment.', 'motopress-hotel-booking' ),
					'default'     => $this->getDefaultOption( 'is_auto_abandon_bookings' ),
				)
			),
			\MPHB\Admin\Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->getId()}_payment_and_booking_pending_time",
				array(
					'type'        => 'number',
					'label'       => __( 'Pending Payment Time', 'motopress-hotel-booking' ),
					'description' => __( 'Period of time in hours a user has to pay for a booking. Unpaid bookings become abandoned, and accommodations become available for others.', 'motopress-hotel-booking' ),
					'min'         => 1,
					'step'        => 1,
					'default'     => $this->getDefaultOption( 'payment_and_booking_pending_time' ),
				)
			),
		);

		$group->addFields( $groupFields );
		$subTab->addGroup( $group );

		MPHB()->emails()->getEmail( \MPHB\Emails\Booking\Customer\DirectBankTransferEmail::EMAIL_ID )->generateSettingsFields( $subTab );
	}


	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {

		$redirectUrl = MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment );

		if ( $this->getOption( 'is_auto_abandon_bookings' ) ) {

			$pendingTimeInHours = $this->getOption( 'payment_and_booking_pending_time' );

			$payment->updateExpiration( time() + $pendingTimeInHours * HOUR_IN_SECONDS );
			$booking->updateExpiration( 'payment', time() + $pendingTimeInHours * HOUR_IN_SECONDS );

		} else {

			$isHolded = $this->paymentOnHold( $payment );

			if ( $isHolded ) {

				$booking = MPHB()->getBookingRepository()->findById( $payment->getBookingId(), true );

				if ( $booking ) {

					$booking->deleteExpiration( 'payment' );
				}
			} else {

				$redirectUrl = MPHB()->settings()->pages()->getPaymentFailedPageUrl( $payment );
			}
		}

		MPHB()->emails()->getEmail( 'admin_pending_booking' )->trigger( $booking );

		if ( \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING == $payment->getStatus() ||
			\MPHB\PostTypes\PaymentCPT\Statuses::STATUS_ON_HOLD == $payment->getStatus() ) {

			MPHB()->emails()->getEmail( \MPHB\Emails\Booking\Customer\DirectBankTransferEmail::EMAIL_ID )->trigger(
				$booking,
				array(
					'payment' => $payment,
				)
			);
		}

		wp_redirect( $redirectUrl );
		exit;
	}
}
