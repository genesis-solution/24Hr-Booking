<?php
/*
 * Available variables:
 * - MPHB\Entities\Booking $booking
 * - float $toPay
 */

if (!defined('ABSPATH')) {
    exit;
}

/** @hooked None */
do_action('mphb_sc_payment_request_checkout-before_form_start', $booking, $toPay);

?>

<form class="mphb_sc_payment_request_checkout-form mphb_sc_checkout-form" method="POST" action="">

    <?php
        /**
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printNonce - 10
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printHiddenFields - 20
         */
        do_action('mphb_sc_payment_request_checkout-after_form_start', $booking, $toPay);
    ?>

    <?php
        /**
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printDetails - 10
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printPriceBreakdown - 20
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printPayments - 30
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printPaymentGateways - 40
         * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::printTotalPrice - 50
         */
        do_action('mphb_sc_payment_request_checkout-form', $booking, $toPay);
    ?>

    <?php
        /** @hooked None */
        do_action('mphb_sc_payment_request_checkout-before_submit_button', $booking, $toPay);
    ?>

    <?php if ($toPay > 0) { ?>
        <p class="mphb_sc_payment_request_checkout-submit-wrapper mphb_sc_checkout-submit-wrapper">
            <input type="submit" class="button" value="<?php esc_attr_e('Submit Payment', 'mphb-request-payment'); ?>" />
        </p>
    <?php } ?>

    <?php
        /** @hooked None */
        do_action('mphb_sc_payment_request_checkout-before_form_end', $booking, $toPay);
    ?>

</form>

<?php
    /**
     * @hooked MPHB\Addons\RequestPayment\Shortcodes\CheckoutShortcode::enqueueAssets - 10
     */
    do_action('mphb_sc_payment_request_checkout-after_form_end', $booking, $toPay);
?>
