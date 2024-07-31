<?php

namespace MPHB\Addons\RequestPayment;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;

class Ajax
{
    protected $nonceName = 'mphb_nonce';
    protected $actionPrefix = 'mphb_';

    /**
     * Add more handlers for action "get_billing_fields", that will work for a
     * default and ours checkout forms.
     */
    public function redefineActions()
    {
        $action = "wp_ajax_{$this->actionPrefix}get_billing_fields";
        $nopriv = "wp_ajax_nopriv_{$this->actionPrefix}get_billing_fields";

        $mphbAjax = MPHB()->getAjax();

        remove_action($action, array($mphbAjax, 'get_billing_fields'));
        remove_action($nopriv, array($mphbAjax, 'get_billing_fields'));

        add_action($action, array($this, 'getBillingFields'));
        add_action($nopriv, array($this, 'getBillingFields'));

        $action = "wp_ajax_{$this->actionPrefix}update_checkout_info";
        $nopriv = "wp_ajax_nopriv_{$this->actionPrefix}update_checkout_info";

        remove_action($action, array($mphbAjax, 'update_checkout_info'));
        remove_action($nopriv, array($mphbAjax, 'update_checkout_info'));

        add_action($action, array($this, 'updateCheckoutInfo'));
        add_action($nopriv, array($this, 'updateCheckoutInfo'));
    }

    public function getBillingFields()
    {
        $action = 'get_billing_fields';
        $input  = $_GET;

        // Maybe use a default handler
        if (!isset($input['formValues']['is_checkout_requested'])) {
            MPHB()->getAjax()->get_billing_fields();
        }

        $this->verifyNonce($action, $input);

        $gatewayId  = !empty($input['mphb_gateway_id']) ? mphb_clean($input['mphb_gateway_id']) : '';
        $bookingKey = !empty($input['formValues']['mphb_key']) ? mphb_clean($input['formValues']['mphb_key']) : '';
        $booking    = !empty($bookingKey) ? BookingUtils::getBookingByKey($bookingKey) : null;

        if (!array_key_exists($gatewayId, MPHB()->gatewayManager()->getListActive())) {
            wp_send_json_error(array(
                'message' => esc_html__('Selected payment method is not available. Refresh the page and try again.', 'mphb-request-payment')
            ));
        }

        if (is_null($booking)) {
            wp_send_json_error(array(
                'message' => esc_html__('Sorry, but no booking was found.', 'mphb-request-payment')
            ));
        }

        $gateway = MPHB()->gatewayManager()->getGateway($gatewayId);

        ob_start();
        $gateway->renderPaymentFields($booking);
        $fields = ob_get_clean();

        wp_send_json_success(array(
            'fields'           => $fields,
            'hasVisibleFields' => $gateway->hasVisiblePaymentFields()
        ));
    }

    public function updateCheckoutInfo()
    {
        $input  = $_GET;
        
        // Maybe use a default handler
        if (!isset($input['formValues']['is_checkout_requested'])) {
            MPHB()->getAjax()->update_checkout_info();
        }

        wp_send_json_success();
    }

    protected function verifyNonce($action, $input)
    {
        $nonce = isset($input[$this->nonceName]) ? $input[$this->nonceName] : '';

        if (!wp_verify_nonce($nonce, $this->actionPrefix . $action)) {
            wp_send_json_error(array(
                'message' => esc_html__('Request did not pass security verification.', 'mphb-request-payment')
            ));
        }
    }
}
