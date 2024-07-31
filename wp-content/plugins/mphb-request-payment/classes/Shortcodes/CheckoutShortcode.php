<?php

namespace MPHB\Addons\RequestPayment\Shortcodes;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;
use MPHB\Addons\RequestPayment\Utils\PaymentUtils;
use MPHB\Entities\Payment;
use MPHB\Shortcodes\AbstractShortcode;
use MPHB\Views\Shortcodes\CheckoutView;
use MPHB\Views\BookingView;

class CheckoutShortcode extends AbstractShortcode
{
    /** @var string */
    protected $name = 'mphb_payment_request_checkout';

    /** @var string */
    protected $bookingKey = '';

    /** @var \MPHB\Entities\Booking|null */
    protected $booking = null;

    /** @var bool */
    protected $isSubmit = false;

    /** @var string[] */
    protected $errors = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function addActions()
    {
        parent::addActions();

        add_action('wp', array($this, 'setup'), 10);
        add_action('wp', array($this, 'handleSubmit'), 20);
    }

    public function setup()
    {
        if (isset($_GET['key'])) {
            $this->bookingKey = mphb_clean($_GET['key']);
        }
    }

    public function handleSubmit()
    {
        if (!isset($_POST['is_checkout_requested'])) {
            return;
        }

        $this->isSubmit = true;

        $gateway = $this->parseGateway();
        $booking = $this->getBooking();
        $payment = $this->createPayment($gateway, $booking);

        if (!is_null($payment)) {
            PaymentUtils::waitForTransition($payment);

            // This must redirect us to a proper page
            $gateway->processPayment($booking, $payment);
        }
    }

    protected function parseGateway()
    {
        $gatewayId = isset($_POST['mphb_gateway_id']) ? mphb_clean($_POST['mphb_gateway_id']) : '';
        $gateway = !empty($gatewayId) ? MPHB()->gatewayManager()->getGateway($gatewayId) : null;

        if (is_null($gateway) || !$gateway->isActive()) {
            $this->errors[] = __('Payment method is not valid.', 'mphb-request-payment');
            return null;
        }

        $errors = array();
        $gateway->parsePaymentFields($_POST, $errors);

        if (!empty($errors)) {
            $this->errors = array_merge($this->errors, $errors);
            return null;
        } else {
            return $gateway;
        }
    }

    protected function createPayment($gateway, $booking)
    {
        if (is_null($gateway) || is_null($booking)) {
            return null;
        }

        $payment = Payment::create(array(
            'gatewayId'   => $gateway->getId(),
            'gatewayMode' => $gateway->getMode(),
            'bookingId'   => $booking->getId(),
            'amount'      => BookingUtils::getToPayPrice($booking),
            'currency'    => MPHB()->settings()->currency()->getCurrencyCode()
        ));

        $isCreated = MPHB()->getPaymentRepository()->save($payment);

        if ($isCreated) {
            $gateway->storePaymentFields($payment);

            // Re-get payment. Some gateways may update metadata without entity update
            $payment = MPHB()->getPaymentRepository()->findById($payment->getId(), true);
        }

        return ($isCreated) ? $payment : null;
    }

    protected function getBooking()
    {
        if (is_null($this->booking) && !empty($this->bookingKey)) {
            $this->booking = BookingUtils::getBookingByKey($this->bookingKey);
        }

        return $this->booking;
    }

    /**
     * @param array $atts
     * @param string $content
     * @param string $name Shortcode name.
     * @return string
     */
    public function render($atts, $content, $name)
    {
        $atts = shortcode_atts(array(
            'class' => ''
        ), $atts, $name);

        $booking = $this->getBooking();
        $toPay = !is_null($booking) ? BookingUtils::getToPayPrice($booking) : 0;

        $wrapperClass = apply_filters('mphb_sc_payment_request_checkout-wrapper_class', 'mphb_sc_payment_request_checkout-wrapper');
        $wrapperClass = trim($wrapperClass . $atts['class']);

        $output = '<div class="' . esc_attr($wrapperClass) . '">';

        if (!empty($this->errors)) {
            $output .= $this->renderErrors();
        } else if (is_null($booking)) {
            $output .= $this->renderNotFound();
        } else if (!$this->isSubmit) {
            $output .= $this->renderCheckout($booking, $toPay);
        }

        $output .= '</div>';

        return $output;
    }

    protected function renderErrors()
    {
        $output = '';

        foreach ($this->errors as $error) {
            $output .= '<p>' . esc_html($error) . '</p>';
        }

        return $output;
    }

    protected function renderNotFound()
    {
        return '<p>' . esc_html__('Sorry, but no booking was found.', 'mphb-request-payment') . '</p>';
    }

    protected function renderCheckout($booking, $toPay)
    {
        $this->registerActions();

        // Enable billing on the page when confirmation mode is "By customer via
        // email" or "By admin manually"
        if (MPHB()->settings()->main()->getConfirmationMode() != 'payment' && $toPay > 0) {
            add_filter('mphb_use_billing_on_page', '__return_true', 10, 1);
        }

        ob_start();
        mphb_get_template_part('shortcodes/payment-request-checkout/checkout-form', array('booking' => $booking, 'toPay' => $toPay));
        $output = ob_get_clean();

        $this->deregisterActions();

        return $output;
    }

    protected function registerActions()
    {
        add_action('mphb_sc_payment_request_checkout-after_form_start', array($this, 'printNonce'), 10);
        add_action('mphb_sc_payment_request_checkout-after_form_start', array($this, 'printHiddenFields'), 20, 1);

        add_action('mphb_sc_payment_request_checkout-form', array($this, 'printDetails'), 10, 1);
        add_action('mphb_sc_payment_request_checkout-form', array($this, 'printPriceBreakdown'), 20, 1);
        add_action('mphb_sc_payment_request_checkout-form', array($this, 'printPayments'), 30, 2);
        add_action('mphb_sc_payment_request_checkout-form', array($this, 'printPaymentGateways'), 40, 2);
        add_action('mphb_sc_payment_request_checkout-form', array($this, 'printTotalPrice'), 50, 2);

        add_action('mphb_sc_payment_request_checkout-after_form_end', array($this, 'enqueueAssets'), 10, 1);
    }

    protected function deregisterActions()
    {
        remove_action('mphb_sc_payment_request_checkout-after_form_start', array($this, 'printNonce'), 10);
        remove_action('mphb_sc_payment_request_checkout-after_form_start', array($this, 'printHiddenFields'), 20);

        remove_action('mphb_sc_payment_request_checkout-form', array($this, 'printDetails'), 10);
        remove_action('mphb_sc_payment_request_checkout-form', array($this, 'printPriceBreakdown'), 20);
        remove_action('mphb_sc_payment_request_checkout-form', array($this, 'printPayments'), 30);
        remove_action('mphb_sc_payment_request_checkout-form', array($this, 'printPaymentGateways'), 40);
        remove_action('mphb_sc_payment_request_checkout-form', array($this, 'printTotalPrice'), 50);

        remove_action('mphb_sc_payment_request_checkout-after_form_end', array($this, 'enqueueAssets'), 10);
    }

    public function printNonce()
    {
        wp_nonce_field($this->name, 'checkout-requested');
    }

    public function printHiddenFields($booking)
    {
        echo '<input type="hidden" name="is_checkout_requested" value="yes" />';
        echo '<input type="hidden" name="mphb_key" value="' . esc_attr($booking->getKey()) . '" />';
    }

    public function printDetails($booking)
    {
		?>
        <section id="mphb-booking-details" class="mphb-booking-details mphb-checkout-section">
			<h3 class="mphb-booking-details-title"><?php esc_html_e('Booking Details', 'mphb-request-payment'); ?></h3>
			<?php
				CheckoutView::renderCheckInDate($booking);
				CheckoutView::renderCheckOutDate($booking);
			?>
		</section>
		<?php
    }

    public function printPriceBreakdown($booking)
    {
        ?>
		<section id="mphb-price-details" class="mphb-room-price-breakdown-wrapper mphb-checkout-section">
			<h3 class="mphb-price-breakdown-title"><?php esc_html_e('Price Breakdown', 'mphb-request-payment'); ?></h3>
			<?php
                if (method_exists($booking, 'getLastPriceBreakdown')) {
                    // Since Hotel Booking 3.5.1
                    echo BookingView::generatePriceBreakdownArray($booking->getLastPriceBreakdown());
                } else {
                    // Before Hotel Booking 3.5.1
                    $prices = get_post_meta($booking->getId(), '_mphb_booking_price_breakdown', true);
                    $prices = json_decode($prices, true);

                    echo BookingView::generatePriceBreakdownArray($prices);
                }
			?>
		</section>
		<?php
    }

    public function printPayments($booking, $toPay)
    {
        $payments = BookingUtils::getPayments($booking);

        $total = $booking->getTotalPrice();
        $paid  = $total - $toPay;

        $placeholder = '&#8212;';

        $dateFormat = get_option('date_format', MPHB()->settings()->dateTime()->getDateFormat());

        ?>
		<section id="mphb-payment-history" class="mphb-payment-history mphb-checkout-section">
			<h3 class="mphb-payment-details-title"><?php esc_html_e('Payment History', 'mphb-request-payment'); ?></h3>

			<table class="mphb-payments-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Date', 'mphb-request-payment'); ?></th>
						<th><?php esc_html_e('Payment', 'mphb-request-payment'); ?></th>
						<th><?php esc_html_e('Status', 'mphb-request-payment'); ?></th>
						<th><?php esc_html_e('Amount', 'mphb-request-payment'); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php if (empty($payments)) { ?>
						<tr>
							<td><?php echo $placeholder; ?></td>
							<td><?php echo $placeholder; ?></td>
							<td><?php echo $placeholder; ?></td>
							<td><?php echo $placeholder; ?></td>
						</tr>
					<?php } else { ?>
						<?php foreach ($payments as $payment) { ?>
							<tr class="<?php echo esc_attr('mphb-payment mphb-payment-status-' . $payment->getStatus()); ?>">
								<td>
									<time datetime="<?php echo esc_attr($payment->getDate()->format('c')); ?>"><?php echo esc_html($payment->getDate()->format($dateFormat)); ?></time>
								</td>
								<td>
									<?php echo '#' . $payment->getId(); ?>
									<br /><small>
									<?php // translators: "via Payment Method" ?>
									<?php printf(esc_html__('via %s', 'mphb-request-payment'), PaymentUtils::getGatewayTitle($payment)); ?>
									</small>
								</td>
								<td><?php echo mphb_get_status_label($payment->getStatus()); ?></td>
								<td><?php echo mphb_format_price($payment->getAmount()); ?></td>
							</tr>
						<?php } // foreach ?>
					<?php } // else ?>
				</tbody>

				<tfoot>
					<tr>
						<th class="mphb-total-label" colspan="3"><?php esc_html_e('Total Paid', 'mphb-request-payment'); ?></th>
						<th><?php echo mphb_format_price($paid); ?></th>
					</tr>
					<tr>
						<th class="mphb-to-pay-label" colspan="3"><?php esc_html_e('Balance Due', 'mphb-request-payment'); ?></th>
						<th><?php echo mphb_format_price($toPay); ?></th>
					</tr>
				</tfoot>
			</table>
		</section>
        <?php
    }

    public function printPaymentGateways($booking, $toPay)
    {
        if ($toPay > 0) {
            CheckoutView::renderBillingDetails($booking);
        }
    }

    public function printTotalPrice($booking, $toPay)
    {
        if ($toPay == 0) {
            return;
        }

        ?>
        <p class="mphb-total-price">
            <output>
                <?php esc_html_e('Total:', 'mphb-request-payment'); ?>
                <strong class="mphb-total-price-field">
                    <?php echo mphb_format_price(BookingUtils::getToPayPrice($booking)); ?>
                </strong>
                <span class="mphb-preloader mphb-hide"></span>
            </output>
        </p>
        <p class="mphb-errors-wrapper mphb-hide"></p>
        <?php
    }

    public function enqueueAssets($booking)
    {
        MPHBRP()->assets()->addCheckoutData($booking);
        MPHBRP()->assets()->enqueueFront();
    }
}
