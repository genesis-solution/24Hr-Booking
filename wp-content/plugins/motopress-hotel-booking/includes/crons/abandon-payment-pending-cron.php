<?php

namespace MPHB\Crons;

use \MPHB\Entities;

class AbandonPaymentPendingCron extends AbstractCron {

	private $retrievePostsLimit = 10;

	public function doCronJob() {

		$paymentAtts = array(
			'pending_expired' => true,
			'posts_per_page'  => $this->retrievePostsLimit,
			'paged'           => 1,
		);

		$payments = MPHB()->getPaymentRepository()->findAll( $paymentAtts );

		foreach ( $payments as $payment ) {
			$payment->setStatus( \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_ABANDONED );
			MPHB()->getPaymentRepository()->save( $payment );
		}

		if ( count( $payments ) < $this->retrievePostsLimit ) {

			$pendingPaymentsAtts = array(
				'post_status'    => \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_PENDING,
				'posts_per_page' => 1,
			);

			$pendingPayments = MPHB()->getPaymentPersistence()->getPosts( $pendingPaymentsAtts );

			if ( ! count( $pendingPayments ) ) {
				$this->unschedule();
			}
		}
	}

}
