<?php

namespace MPHB\Addons\RequestPayment;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;

class Assets
{
    protected $isDebug = false;

    public function __construct()
    {
        $this->isDebug = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);

        add_action('wp_enqueue_scripts', array($this, 'register'));
        add_action('admin_enqueue_scripts', array($this, 'register'));
    }

    public function register()
    {
        $frontScriptDependencies = array('mphb', 'mphb-jquery-serialize-json');

        if (MPHB()->gatewayManager()->getGateway('stripe')->isActive()) {
            $frontScriptDependencies[] = 'mphb-vendor-stripe-library';
        }

        if (MPHB()->gatewayManager()->getGateway('braintree')->isActive()) {
            $frontScriptDependencies[] = 'mphb-vendor-braintree-client-sdk';
        }

        wp_register_script('mphbrp-front', $this->scriptUrl('assets/scripts/frontend.min.js'), $frontScriptDependencies, MPHBRP()->getVersion(), true);
    }

    public function enqueueFront()
    {
        wp_enqueue_script('mphbrp-front');
    }

    public function enqueueAdmin()
    {
    }

    public function addCheckoutData($booking)
    {
        $toPay = BookingUtils::getToPayPrice($booking);

        // Add price
        MPHB()->getPublicScriptManager()->addCheckoutData(array('total' => $toPay));

        // Add gateways
        foreach (MPHB()->gatewayManager()->getListActive() as $gateway) {
            $checkoutData = $gateway->getCheckoutData($booking);

            // Don't use the deposit amount for requests, use the real "to pay"
            // price instead
            $checkoutData['amount'] = $toPay;

            MPHB()->getPublicScriptManager()->addGatewayData($gateway->getId(), $checkoutData);
        }
    }

    protected function scriptUrl($relativePath)
    {
        if ($this->isDebug) {
            $relativePath = str_replace(array('.min.js', '.min.css'), array('.js', '.css'), $relativePath);
        }

        return MPHBRP()->urlTo($relativePath);
    }
}
