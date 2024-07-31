<?php

namespace MPHB\Payments;

use \MPHB\PostTypes\PaymentCPT\Statuses;

class PaymentManager {

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 *
	 * @since 3.7.6 added second parameter to filter "mphb_can_payment_be_completed" - $payment.
	 */
	public function canBeCompleted( $payment ) {

		$allowedStatuses = array(
			Statuses::STATUS_ON_HOLD,
			Statuses::STATUS_PENDING,
		);

		return (bool) apply_filters( 'mphb_can_payment_be_completed', in_array( $payment->getStatus(), $allowedStatuses ), $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 *
	 * @since 3.7.6 added second parameter to filter "mphb_can_payment_be_refunded" - $payment.
	 */
	public function canBeRefunded( $payment ) {
		$allowedStatuses = array(
			Statuses::STATUS_ON_HOLD,
			Statuses::STATUS_PENDING,
			Statuses::STATUS_COMPLETED,
		);
		return (bool) apply_filters( 'mphb_can_payment_be_refunded', in_array( $payment->getStatus(), $allowedStatuses ), $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 *
	 * @since 3.7.6 added second parameter to filter "mphb_can_payment_be_failed" - $payment.
	 */
	public function canBeFailed( $payment ) {
		$allowedStatuses = array(
			Statuses::STATUS_ON_HOLD,
			Statuses::STATUS_PENDING,
		);
		return (bool) apply_filters( 'mphb_can_payment_be_failed', in_array( $payment->getStatus(), $allowedStatuses ), $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 *
	 * @since 3.7.6 added second parameter to filter "mphb_can_payment_be_on_hold" - $payment.
	 */
	public function canBeOnHold( $payment ) {
		$allowedStatuses = array(
			Statuses::STATUS_PENDING,
		);
		return (bool) apply_filters( 'mphb_can_payment_be_on_hold', in_array( $payment->getStatus(), $allowedStatuses ), $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 *
	 * @since 3.7.6 added second parameter to filter "mphb_can_payment_be_abandoned" - $payment.
	 */
	public function canBeAbandoned( $payment ) {
		return (bool) apply_filters( 'mphb_can_payment_be_abandoned', true, $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 *
	 * @since 3.9.6
	 */
	public function canBeCancelled( $payment ) {
		$allowedStatuses = array(
			Statuses::STATUS_ON_HOLD,
			Statuses::STATUS_PENDING,
			Statuses::STATUS_COMPLETED,
		);

		/**
		 * @since 3.9.6
		 */
		return (bool) apply_filters( 'mphb_can_payment_be_cancelled', in_array( $payment->getStatus(), $allowedStatuses ), $payment );
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $log Optional. Default empty string
	 * @param bool                   $skipCheck Skip check of possibility
	 * @return boolean
	 */
	public function failPayment( &$payment, $log = '', $skipCheck = false ) {

		if ( ! ( $skipCheck || $this->canBeFailed( $payment ) ) ) {
			return false;
		}

		do_action( 'mphb_payment_before_failed', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$payment->setStatus( Statuses::STATUS_FAILED );
		$failed = (bool) MPHB()->getPaymentRepository()->save( $payment );
		if ( $failed ) {
			do_action( 'mphb_payment_failed', $payment );
		}

		return $failed;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $log Optional. Default empty string
	 * @param bool                   $skipCheck Skip check of possibility
	 * @return boolean
	 */
	public function completePayment( &$payment, $log = '', $skipCheck = false ) {
		if ( ! ( $skipCheck || $this->canBeCompleted( $payment ) ) ) {
			return false;
		}

		do_action( 'mphb_payment_before_completed', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$payment->setStatus( Statuses::STATUS_COMPLETED );
		$completed = (bool) MPHB()->getPaymentRepository()->save( $payment );
		if ( $completed ) {
			do_action( 'mphb_payment_completed', $payment );
		}

		return $completed;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $log Optional. Default empty string
	 * @param bool                   $skipCheck Skip check of possibility
	 * @return boolean
	 */
	public function abandonPayment( &$payment, $log = '', $skipCheck = false ) {

		if ( ! ( $skipCheck || $this->canBeAbandoned( $payment ) ) ) {
			return false;
		}

		do_action( 'mphb_payment_before_abandoned', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$payment->setStatus( Statuses::STATUS_ABANDONED );
		$abandoned = (bool) MPHB()->getPaymentRepository()->save( $payment );
		if ( $abandoned ) {
			do_action( 'mphb_payment_abandoned', $payment );
		}

		return $abandoned;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $log Optional. Default empty string
	 * @param bool                   $skipCheck Skip check of possibility
	 * @return boolean
	 */
	public function holdPayment( &$payment, $log = '', $skipCheck = false ) {
		if ( ! ( $skipCheck || $this->canBeOnHold( $payment ) ) ) {
			return false;
		}

		do_action( 'mphb_payment_before_on_hold', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$payment->setStatus( Statuses::STATUS_ON_HOLD );
		$onHold = (bool) MPHB()->getPaymentRepository()->save( $payment );
		if ( $onHold ) {
			do_action( 'mphb_payment_on_hold', $payment );
		}

		return $onHold;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $log Optional. Default empty string
	 * @param bool                   $skipCheck Skip check of possibility
	 * @return boolean
	 */
	public function refundPayment( &$payment, $log = '', $skipCheck = false ) {
		if ( ! ( $skipCheck || $this->canBeRefunded( $payment ) ) ) {
			return false;
		}

		do_action( 'mphb_payment_before_refunded', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$payment->setStatus( Statuses::STATUS_REFUNDED );
		$refunded = (bool) MPHB()->getPaymentRepository()->save( $payment );
		if ( $refunded ) {
			do_action( 'mphb_payment_refunded', $payment );
		}

		return $refunded;
	}

	/**
	 *
	 * @param \MPHB\Entities\Payment $payment
	 * @param string                 $log Optional. Default empty string
	 * @param bool                   $skipCheck Skip check of possibility
	 * @return boolean
	 *
	 * @since 3.9.6
	 */
	public function cancellPayment( &$payment, $log = '', $skipCheck = false ) {
		if ( ! ( $skipCheck || $this->canBeCancelled( $payment ) ) ) {
			return false;
		}

		/**
		 * @since 3.9.6
		 */
		do_action( 'mphb_payment_before_cancelled', $payment );

		if ( ! empty( $log ) ) {
			$payment->addLog( $log );
		}

		$payment->setStatus( Statuses::STATUS_CANCELLED );
		$cancelled = (bool) MPHB()->getPaymentRepository()->save( $payment );
		if ( $cancelled ) {
			/**
			 * @since 3.9.6
			 */
			do_action( 'mphb_payment_cancelled', $payment );
		}

		return $cancelled;
	}

}
