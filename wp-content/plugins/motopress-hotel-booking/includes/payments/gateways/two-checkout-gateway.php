<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 2Checkout
 */
class TwoCheckoutGateway extends Gateway {

	/**
	 *
	 * @var array
	 */
	private $supportedCurrencies = array();

	/**
	 *
	 * @var string
	 */
	private $accountNumber;

	/**
	 *
	 * @var string
	 */
	private $secretWord;

	/**
	 *
	 * @var TwoCheckout\InsListener
	 */
	private $insListener;

	public function __construct() {
		add_filter( 'mphb_gateway_has_instructions', array( $this, 'hideInstructions' ), 10, 2 );
		parent::__construct();
	}

	/**
	 * @param bool   $show
	 * @param string $gatewayId
	 * @return bool
	 *
	 * @since 3.6.1
	 */
	public function hideInstructions( $show, $gatewayId ) {
		if ( $gatewayId == $this->id ) {
			$show = false;
		}
		return $show;
	}

	protected function setupProperties() {
		parent::setupProperties();
		$this->accountNumber = trim( $this->getOption( 'account_number' ) );
		$this->secretWord    = trim( $this->getOption( 'secret_word' ) );
		$this->setupInsListener();
		$this->adminTitle       = __( '2Checkout', 'motopress-hotel-booking' );
		$this->adminDescription = $this->generateAdminDescription();
		$this->setupSuppportedCurrencies();

		if ( $this->isSandbox ) {
			$this->description .= ' ' . sprintf( __( 'Use the card number %1$s with CVC %2$s and a valid expiration date to test a payment.', 'motopress-hotel-booking' ), '4000000000000002', '123' );
			$this->description  = trim( $this->description );
		}
	}

	private function setupInsListener() {

		$insListenerArgs = array(
			'gatewayId'     => $this->getId(),
			'sandbox'       => $this->isSandbox,
			'accountNumber' => $this->accountNumber,
			'secretWord'    => $this->secretWord,
		);

		$this->insListener = new TwoCheckout\InsListener( $insListenerArgs );
	}

	private function generateAdminDescription() {
		$description  = __( 'To setup the callback process for 2Checkout to automatically mark payments completed, you will need to', 'motopress-hotel-booking' );
		$description .= '<ol>';
		$description .= '<li>' . __( 'Login to your 2Checkout account and click the Notifications tab', 'motopress-hotel-booking' ) . '</li>';
		$description .= '<li>' . __( 'Click Enable All Notifications', 'motopress-hotel-booking' ) . '</li>';
		$description .= '<li>' . sprintf( __( 'In the Global URL field, enter the url %s', 'motopress-hotel-booking' ), '<code>' . esc_url( $this->insListener->getNotifyUrl() ) . '</code>' ) . '</li>';
		$description .= '<li>' . __( 'Click Apply', 'motopress-hotel-booking' ) . '</li>';
		$description .= '</ol>';

		return $description;
	}

	protected function initDefaultOptions() {
		$defaults = array(
			'title'          => __( '2Checkout', 'motopress-hotel-booking' ),
			'description'    => 'Pay via 2Checkout.',
			'enabled'        => false,
			'account_number' => '',
			'secret_word'    => '',
		);
		return array_merge( parent::initDefaultOptions(), $defaults );
	}

	protected function initId() {
		return '2checkout';
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {
		$url = $this->getPaymentUrl( $booking, $payment );

		// Redirect to 2checkout checkout
		wp_redirect( $url );
		exit;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	public function getPaymentUrl( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment ) {

		$args = http_build_query( $this->getRequestArgs( $booking, $payment ), '', '&' );

		if ( $this->isSandbox ) {
			$url = 'https://sandbox.2checkout.com/checkout/purchase?' . $args;
		} else {
			$url = 'https://2checkout.com/checkout/purchase?' . $args;
		}

		return $url;
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 * @return string
	 */
	private function getRequestArgs( $booking, $payment ) {

		$returnUrlArgs = array(
			'payment-confirmation' => '2checkout',
			'mphb_payment_status'  => 'auto',
		);

		$returnUrl = MPHB()->settings()->pages()->getReservationReceivedPageUrl( $payment, $returnUrlArgs );

		return array(
			'sid'                => $this->accountNumber,
			'mode'               => '2CO', // Should always be passed as ‘2CO’. @see https://www.2checkout.com/documentation/checkout/parameters
			'x_receipt_link_url' => esc_url( $returnUrl ),
			'currency_code'      => $payment->getCurrency(),
			'merchant_order_id'  => $payment->getId(),
			'li_0_type'          => 'product',
			'li_0_name'          => $this->generateItemName( $booking ),
			'li_0_price'         => $payment->getAmount(),
			'li_0_product_id'    => $payment->getBookingId(),
			'li_0_quantity'      => 1,
			'li_0_tangible'      => 'N',
		);
	}

	/**
	 *
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 */
	public function registerOptionsFields( &$subTab ) {
		parent::registerOptionsFields( $subTab );
		$group = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group2", '', $subTab->getOptionGroupName() );

		$groupFields = array(
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_account_number",
				array(
					'type'    => 'text',
					'label'   => sprintf( __( 'Account Number', 'motopress-hotel-booking' ), $this->title ),
					'default' => $this->getDefaultOption( 'account_number' ),
				)
			),
			Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_secret_word",
				array(
					'type'    => 'text',
					'label'   => sprintf( __( 'Secret Word', 'motopress-hotel-booking' ), $this->title ),
					'default' => $this->getDefaultOption( 'secret_word' ),
				)
			),
		);

		$group->addFields( $groupFields );

		$subTab->addGroup( $group );
	}

	/**
	 *
	 * @return bool
	 */
	public function isActive() {
		return parent::isActive() && $this->isSupportCurrency( MPHB()->settings()->currency()->getCurrencyCode() );
	}

	/**
	 *
	 * @param string $currency Currency code.
	 * @return bool
	 */
	public function isSupportCurrency( $currency ) {
		return in_array( $currency, $this->supportedCurrencies );
	}

	public function setupSuppportedCurrencies() {
		$this->supportedCurrencies = include 'two-checkout/supported-currencies.php';
	}

}
