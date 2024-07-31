<?php

namespace MPHB\Entities;

class Payment {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $status;

	/**
	 * @var DateTime
	 */
	private $date;

	/**
	 * @var DateTime
	 */
	private $modifiedDate;

	/**
	 * @var float
	 */
	private $amount;

	/**
	 * @var string
	 */
	private $currency;

	/**
	 * @var int
	 */
	private $bookingId;

	/**
	 * @var string "test", "cash", "paypal" etc.
	 */
	private $gatewayId;

	/**
	 * @var string live|sandbox
	 */
	private $gatewayMode;

	/**
	 * @var string
	 */
	private $transactionId;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @param array $atts
	 */
	function __construct( $atts ) {

		if ( isset( $atts['id'] ) ) {
			$this->id = $atts['id'];
		}

		$this->date         = isset( $atts['date'] ) ? $atts['date'] : new \DateTime( current_time( 'mysql' ) );
		$this->modifiedDate = isset( $atts['modifiedDate'] ) ? $atts['modifiedDate'] : new \DateTime( current_time( 'mysql' ) );
		$this->status       = isset( $atts['status'] ) ? $atts['status'] : \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING;

		// Gateway Info
		$this->gatewayId     = $atts['gatewayId'];
		$this->gatewayMode   = $atts['gatewayMode'];
		$this->transactionId = isset( $atts['transactionId'] ) ? $atts['transactionId'] : '';

		// Payment Info
		$this->amount    = $atts['amount'];
		$this->currency  = $atts['currency'];
		$this->bookingId = $atts['bookingId'];

		// Billing Fields
		$this->email = ! empty( $atts['email'] ) ? $atts['email'] : '';
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getKey() {

		return get_post_meta( $this->id, '_mphb_key', true );
	}

	/**
	 * @return string
	 */
	public function generateKey() {

		$key = uniqid( "payment_{$this->id}_", true );
		update_post_meta( $this->id, '_mphb_key', $key );
		return $key;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return float
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @return string
	 */
	public function getGatewayId() {
		return $this->gatewayId;
	}

	/**
	 * @return DateTime
	 */
	function getModifiedDate() {
		return $this->modifiedDate;
	}

	/**
	 * @return string
	 */
	function getCurrency() {
		return $this->currency;
	}

	/**
	 * @return int
	 */
	function getBookingId() {
		return $this->bookingId;
	}

	/**
	 * @return string
	 */
	function getGatewayMode() {
		return $this->gatewayMode;
	}

	/**
	 * @return string
	 */
	function getTransactionId() {
		return $this->transactionId;
	}

	/**
	 * @param int $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * @param string $status
	 */
	public function setStatus( $status ) {
		$this->status = $status;
	}

	/**
	 * @param string $id
	 */
	public function setTransactionId( $id ) {
		$this->transactionId = $id;
	}

	/**
	 * @param array $paymentData
	 * @return Payment
	 */
	public static function create( $paymentData ) {
		return new self( $paymentData );
	}

	/**
	 * Set expiration time of pending confirmation for payment
	 *
	 * @param int $expirationTime
	 */
	public function updateExpiration( $expirationTime ) {

		update_post_meta( $this->id, '_mphb_pending_expired', $expirationTime );
	}

	/**
	 * Retrieve expiration time of pending confirmation for payment in UTC
	 *
	 * @return int
	 */
	public function retrieveExpiration() {

		return intval( get_post_meta( $this->id, '_mphb_pending_expired', true ) );
	}

	/**
	 * Delete expiration time of pending confirmation for payment
	 */
	public function deleteExpiration() {

		delete_post_meta( $this->id, '_mphb_pending_expired' );
	}

	/**
	 * @param string $message
	 */
	public function addLog( $message ) {

		$logs   = $this->getLogs();
		$logs[] = array(
			'date'    => mphb_current_time( 'mysql' ),
			'message' => $message,
		);

		update_post_meta( $this->id, '_mphb_logs', $logs );
	}

	/**
	 * @return array
	 */
	public function getLogs() {

		$logs = get_post_meta( $this->id, '_mphb_logs', true );
		return is_array( $logs ) ? $logs : array();
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return bool
	 * @since 4.2.2
	 */
	public function isFinished() {

		return in_array( $this->status, MPHB()->postTypes()->payment()->statuses()->getFinishedStatuses() );
	}

	/**
	 * The customer went to the payment page (redirect) and authorized the
	 * payment. Used for payment gateways with redirects: Stripe, PayPal, WooCommerce.
	 *
	 * @since 4.2.2
	 */
	public function setAuthorized() {

		update_post_meta( $this->id, '_mphb_is_authorized', true );
	}

	/**
	 * @return bool
	 * @since 4.2.2
	 */
	public function isAuthorized() {

		$isAuthorized = get_post_meta( $this->id, '_mphb_is_authorized', true );

		return (bool) $isAuthorized;
	}
}
