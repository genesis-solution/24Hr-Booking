<?php

namespace MPHBW;

use MPHB\PostTypes\BookingCPT\Statuses as BookingStatuses;

class WoocommerceListener {

	/**
	 *
	 * @var ReservationProduct
	 */
	private $reservationProduct;

	/**
	 *
	 * @param ReservationProduct $reservationProduct
	 *
	 * @since 1.0.6 Set status 'cancelled' for a payment when the order is cancelled
	 */
	public function __construct( $reservationProduct ){
		$this->reservationProduct = $reservationProduct;

		// Fail Payment
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancellPaymentByWooOrder' ), 10, 1 );
		add_action( 'woocommerce_order_status_failed', array( $this, 'failPaymentByWooOrder' ), 10, 1 );
		add_action( 'wp_trash_post', array( $this, 'deleteWooOrder' ), 10, 1 );
		add_action( 'woocommerce_delete_order_items', array($this, 'deleteWooOrder'), 10, 1 );

		// Complete Payment
		add_action( 'woocommerce_order_status_completed', array( $this, 'completePaymentByWooOrder' ), 10, 1 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'completePaymentByWooOrder' ), 10, 1 );

		// Hold Payment
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'holdPaymentByWooOrder' ), 10, 1 );
		add_action( 'woocommerce_order_status_pending', array( $this, 'holdPaymentByWooOrder' ), 10, 1 );

		// Refund Payment
		add_action( 'woocommerce_order_status_refunded', array( $this, 'refundPaymentByWooOrder' ), 10, 1 );

		// Change payment status after order creation.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'processNewOrder' ), 10, 1 );

		// Remove order from cart after cancellation by user
		add_action( 'woocommerce_cancelled_order', array( $this, 'removeCancelledPaymentFromCart' ), 10, 1 );

		// Remove uncompleted payment before search (for unblock accommodation while correcting search parameters)
		add_action( 'mphb_load_search_results_page', array( $this, 'failPendingPayment' ), 10, 0 );
	}

	public function failPendingPayment(){
		$cart = wc()->cart;
		if ( !$cart ) {
			return;
		}

		foreach ( $cart->get_cart_contents() as $cartItemKey => $cartItemData ) {
			if ( !$this->reservationProduct->isReservationProductId( $cartItemData['product_id'] ) ) {
				continue;
			}
			if ( !isset( $cartItemData['_mphb_payment_id'] ) ) {
				continue;
			}
			$payment = MPHB()->getPaymentRepository()->findById( $cartItemData['_mphb_payment_id'] );
			if ( !$payment ) {
				continue;
			}

			add_filter( 'mphb_email_customer_cancelled_booking_prevent', '__return_true' );

			MPHB()->paymentManager()->failPayment( $payment );

			remove_filter( 'mphb_email_customer_cancelled_booking_prevent', '__return_true' );

			$cart->remove_cart_item( $cartItemKey );
		}
	}

	/**
	 *
	 * @param int $orderId
	 */
	public function removeCancelledPaymentFromCart( $orderId ){
		$order = wc_get_order( $orderId );
		if ( !$order ) {
			return;
		}
		$cart = wc()->cart;
		if ( !$cart ) {
			return;
		}
		$paymentId = 0;

		foreach ( $order->get_items() as $orderItem ) {
			if ( $this->reservationProduct->isReservationProductId( $orderItem->get_product_id() ) &&
				!empty( $orderItem->get_meta( '_mphb_payment_id' ) )
			) {
				$paymentId = $orderItem->get_meta( '_mphb_payment_id' );
			}
		}

		foreach ( $cart->get_cart_contents() as $cartItemKey => $cartItem ) {
			if ( $this->reservationProduct->isReservationProductId( $cartItem['product_id'] ) &&
				isset( $orderItem['_mphb_payment_id'] ) &&
				$orderItem['_mphb_payment_id'] == $paymentId
			) {
				$cart->remove_cart_item( $cartItemKey );
			}
		}
	}

	/**
	 *
	 * @param int $orderId
	 */
	public function processNewOrder( $orderId ){
		$order = wc_get_order( $orderId );
		switch ( $order->get_status() ) {
			// new order default status
			case 'pending':
				$this->holdPaymentByWooOrder( $orderId );
				break;
		}
	}

	/**
	 * Checks if trashing or deleting a shop_order post type.
	 *
	 * @param int $orderId
	 *
	 * @since 1.0.6
	 */
	public function deleteWooOrder( $orderId ) {
		$post_type = get_post_type( $orderId );

		if( $post_type == 'shop_order' ) {
			$this->cancellPaymentByWooOrder( $orderId );
		}
	}

	/**
	 * @param int $orderId
	 */
	public function failPaymentByWooOrder( $orderId ){
		$payment = $this->getOrderPayment( $orderId );

		if ( !is_null( $payment ) ) {
			// Don't check the standard rules and fail the previously completed payment
			MPHB()->paymentManager()->failPayment( $payment, '', true );
		}
	}

    /**
     * @param int $orderId
     */
    public function completePaymentByWooOrder( $orderId ){
        $payment = $this->getOrderPayment( $orderId );

        if ( !is_null( $payment ) ) {
            $booking = MPHB()->getBookingRepository()->findById( $payment->getBookingId() );

            $needToRebook = !is_null( $booking ) && !in_array( $booking->getStatus(), MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses() );
            $canRebook = !is_null( $booking ) && BookingUtils::canRebook( $booking );

            if ( !$needToRebook || $canRebook ) {
                // Don't check the standard transition rules. We can set any
                // status and don't get the overbooking
                MPHB()->paymentManager()->completePayment( $payment, '', true );
            } else if ( !is_null( $booking ) ) {
                // Send email to admin
                MPHB()->emails()->getEmail( 'admin_no_booking_renewal' )->trigger( $booking, array( 'payment' => $payment ) );
            }
        }
    }

    /**
     * @param int $orderId
     */
    public function holdPaymentByWooOrder( $orderId ){
        $payment = $this->getOrderPayment( $orderId );

        if ( !is_null( $payment ) ) {
			// Don't check the standard rules and put the payment on hold
            MPHB()->paymentManager()->holdPayment( $payment, '', true );
        }
    }

    /**
     * @param int $orderId
     */
    public function refundPaymentByWooOrder( $orderId ){
        $payment = $this->getOrderPayment( $orderId );

        if ( !is_null( $payment ) ) {
			// Don't check the standard rules and refund the payment
            MPHB()->paymentManager()->refundPayment( $payment, '', true );
        }
    }

	/**
	 * @param int $orderId
	 *
	 * @since 1.0.6
	 */
	public function cancellPaymentByWooOrder( $orderId ) {
		$payment = $this->getOrderPayment( $orderId );

		if ( !is_null( $payment ) ) {
			// Compability with older versions of HB
			if( method_exists( 'MPHB\Payments\PaymentManager', 'cancellPayment' ) ) {
				// Don't check the standard rules and cancell the payment
				MPHB()->paymentManager()->cancellPayment( $payment, '', true );
			} else {
				MPHB()->paymentManager()->failPayment( $payment, '', true );
			}
		}
	}

    /**
     * @param int $orderId
     * @return \MPHB\Entities\Payment|null
     *
     * @since 1.0.5
     */
    protected function getOrderPayment( $orderId ){
        $order = wc_get_order( $orderId );

        if ( !$order ) {
            return null;
        }

        foreach ( $order->get_items() as /*$orderItemId =>*/ $orderItem ) {
            if ( !$this->reservationProduct->isReservationProductId( $orderItem->get_product_id() ) ) {
                continue;
            }

            $paymentId = $orderItem->get_meta( '_mphb_payment_id' );
            $payment = $paymentId ? MPHB()->getPaymentRepository()->findById( $paymentId ) : null;

            if ( !is_null( $payment ) ) {
                return $payment;
            }
        }

        return null;
    }

}
