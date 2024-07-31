(function ($) {
    $(function () {
        var checkoutForm = new MPHB.CheckoutForm($('.mphb_sc_payment_request_checkout-form'));

        $('.mphb-gateways-list input[type="radio"][name="mphb_gateway_id"]').change(function () {
            checkoutForm.hideErrors();
        });
    });
})(jQuery);
