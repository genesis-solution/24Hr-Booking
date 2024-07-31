<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api\Data;

use MPHB\Advanced\Api\ApiHelper;
use MPHB\Entities\Payment;

class PaymentData extends AbstractPostData {
	/**
	 * @var Payment
	 */
	public $entity;

	const STATUS_PREFIX = 'mphb-p-';

	/**
	 * @return array [$dbStatus => $apiStatus]
	 */
	private static function getAvailablePaymentStatuses() {
		$apiStatuses = array();
		$dbStatuses  = MPHB()->postTypes()->payment()->statuses()->getStatuses();
		foreach ( $dbStatuses as $dbStatus => $item ) {
			$apiStatus     = str_replace( self::STATUS_PREFIX, '', $dbStatus );
			$apiStatuses[] = $apiStatus;
		}

		return $apiStatuses;
	}

	private static function getDefaultPaymentStatus() {
		$defaultStatus = \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING;

		return str_replace( self::STATUS_PREFIX, '', $defaultStatus );
	}

	private static function getAvailableGatewayIds() {
		$manualGatewayId  = array( 'manual' ); // all times active for admins
		$activeGatewayIds = array_keys( MPHB()->gatewayManager()->getListActive() );

		return array_merge( $manualGatewayId, $activeGatewayIds );
	}

	public static function getRepository() {
		return MPHB()->getPaymentRepository();
	}

	public static function getProperties() {
		return array(
			'id'                  => array(
				'description' => 'Unique identifier for the resource.',
				'type'        => 'integer',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'status'              => array(
				'description' => 'Payment status.',
				'type'        => 'string',
				'default'     => self::getDefaultPaymentStatus(),
				'enum'        => self::getAvailablePaymentStatuses(),
				'context'     => array( 'embed', 'view', 'edit' ),
			),
			'gateway_id'          => array(
				'description' => 'Gateway.',
				'type'        => 'string',
				'enum'        => self::getAvailableGatewayIds(),
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'key'                 => array(
				'description' => 'Payment key.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'transaction_id'      => array(
				'description' => 'Transaction id.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_created'        => array(
				'description' => 'The date the payment was created.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_created_utc'    => array(
				'description' => 'The date the payment was created, as UTC.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_modified'       => array(
				'description' => 'The date the payment was modified.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_modified_utc'   => array(
				'description' => 'The date the payment was modified, as UTC.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_expiration'     => array(
				'description' => 'Time to expiration pending of payment.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_expiration_utc' => array(
				'description' => 'Time to expiration pending of payment, as UTC.',
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'booking_id'          => array(
				'description' => 'Identifier of booking resource.',
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'required'    => true,
			),
			'gateway_mode'        => array(
				'description' => 'Gateway mode.',
				'type'        => 'string',
				'context'     => array( 'embed', 'view', 'edit' ),
				'readonly'    => true,
			),
			'amount'              => array(
				'description' => 'Amount.',
				'type'        => 'number',
				'context'     => array( 'embed', 'view', 'edit' ),
				'required'    => true,
			),
			'currency'            => array(
				'description' => 'Payment currency in ISO format.',
				'type'        => 'string',
				'enum'        => array_keys( MPHB()->settings()->currency()->getBundle()->getLabels() ),
				'context'     => array( 'view', 'edit' ),
				'default'     => MPHB()->settings()->currency()->getDefaultCurrency(),
			),
			'billing_info'        => array(
				'description'          => 'Billing info.',
				'type'                 => 'object',
				'context'              => array( 'view', 'edit' ),
				'properties'           => CustomerData::getProperties(),
				'additionalProperties' => false,
			),
		);
	}

	private function getBillingInfoField( $field ) {
		return get_post_meta( $this->entity->getId(), '_' . mphb_prefix( $field, '_' ), true );
	}

	protected function getStatus() {
		$status = $this->entity->getStatus();

		return str_replace( self::STATUS_PREFIX, '', $status );
	}

	protected function setStatus( $status ) {
		$status = self::STATUS_PREFIX . $status;
		$this->entity->setStatus( $status );
	}

	protected function getBillingInfo() {
		if ( isset( $this->billing_info ) ) {
			return $this->billing_info;
		}
		$billingInfo = array();
		foreach ( CustomerData::getFields() as $field ) {
			$billingInfo[ $field ] = $this->getBillingInfoField( $field );
		}

		return $billingInfo;
	}

	private function getDateExpirationValue() {
		$retrieveExpiration = $this->entity->retrieveExpiration();
		if ( ! $retrieveExpiration ) {
			return false;
		}
		$utcTimezone = new \DateTimeZone( 'UTC' );

		return \DateTime::createFromFormat( 'U', $retrieveExpiration, $utcTimezone );
	}

	protected function getDateExpiration() {
		$dateExpiration = $this->getDateExpirationValue();

		return $dateExpiration ? ApiHelper::prepareDateTimeResponse( $dateExpiration, wp_timezone_string() ) : '';
	}

	protected function getDateExpirationUtc() {
		$dateExpiration = $this->getDateExpirationValue();

		return $dateExpiration ? ApiHelper::prepareDateTimeResponse( $dateExpiration ) : '';
	}

	/**
	 * @param array $billingInfoRaw
	 */
	protected function setBillingInfo( $billingInfoRaw ) {
		foreach ( CustomerData::getFields() as $field ) {
			$billingInfo[ $field ] = $billingInfoRaw[ $field ] ?? $this->getBillingInfoField( $field );
		}
		$this->billing_info = $billingInfo;
	}

	/**
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function validate() {
		$booking = MPHB()->getBookingRepository()->findById( $this->booking_id );
		if ( is_null( $booking ) ) {
			throw new \Exception( sprintf( 'Invalid %s: %d.', 'booking_id', $this->booking_id ) );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function isBookingPaid() {
		$booking   = MPHB()->getBookingRepository()->findById( $this->booking_id );
		$payments  = MPHB()->getPaymentRepository()->findAll( array( 'booking_id' => $this->booking_id ) );
		$totalPaid = 0.0;
		foreach ( $payments as $payment ) {
			if ( $payment->getStatus() == \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED ) {
				$totalPaid += $payment->getAmount();
			}
		}

		return ! ( ( $booking->getTotalPrice() - $totalPaid ) > 0 );
	}

	/**
	 * @return bool
	 */
	private function isNeedChangeBookingStatusByPayment() {
		if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' &&
			 $this->status === \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED ) {
			return true;
		}

		return false;
	}

	private function changeBookingStatusToConfirmedByPayment() {
		$booking = MPHB()->getBookingRepository()->findById( $this->booking_id );
		$booking->setStatus( \MPHB\PostTypes\BookingCPT\Statuses::STATUS_CONFIRMED );
		MPHB()->getBookingRepository()->save( $booking );
	}

	/**
	 * @return string
	 */
	protected function getGatewayMode() {
		if ( $this->id !== 0 ) {
			return $this->entity->getGatewayMode();
		}

		$gateway_id = $this->gateway_id;
		$gateway    = MPHB()->gatewayManager()->getGateway( $gateway_id );

		return $gateway->getMode();
	}

	private function setDataToEntity() {
		$atts   = array(
			'id'          => $this->id,
			'gatewayMode' => $this->getGatewayMode(),
			'date'        => $this->entity->getDate(),
		);
		$fields = static::getWritableFieldKeys();
		foreach ( $fields as $field ) {
			$keyCamelCase = lcfirst( ApiHelper::convertSnakeToCamelString( $field ) );
			switch ( $field ) {
				case 'status':
					$atts[ $keyCamelCase ] = $this->entity->getStatus();
					break;
				default:
					$atts[ $keyCamelCase ] = $this->{$field};
			}
		}
		$this->entity = Payment::create( $atts );
	}

	private function saveBillingInfo() {
		if ( ! isset( $this->billing_info ) ) {
			return;
		}

		foreach ( $this->billing_info as $key => $value ) {
			$metaKey = '_' . MPHB()->getPrefix() . '_' . $key;
			update_post_meta( $this->id, $metaKey, $value );
		}
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function save() {
		$this->setDataToEntity();

		if ( $this->isDataChanged() && $this->validate() ) {
			parent::save();
			$this->saveBillingInfo();
		}

		if ( $this->isNeedChangeBookingStatusByPayment() && $this->isBookingPaid() ) {
			$this->changeBookingStatusToConfirmedByPayment();
		}

		return true;
	}
}
