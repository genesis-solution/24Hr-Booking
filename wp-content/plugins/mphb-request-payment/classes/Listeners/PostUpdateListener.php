<?php

namespace MPHB\Addons\RequestPayment\Listeners;

class PostUpdateListener
{
    public function __construct()
    {
        $this->addActions();
    }

    protected function addActions()
    {
        add_action('save_post', array($this, 'onSavePost'), 10, 2);
        add_filter('post_updated_messages', array($this, 'onUpdatePostMessages'));
    }

    public function onSavePost($postId, $post)
    {
        if ($post->post_type == MPHB()->postTypes()->booking()->getPostType()
            && isset($_POST['send_payment_request'])
        ) {
            add_filter('redirect_post_location', array($this, 'onRedirectPostLocation'));
        }
    }

    public function onRedirectPostLocation($location)
    {
        $location = add_query_arg('payment-request', 'sent', $location);
        return $location;
    }

    public function onUpdatePostMessages($messages)
    {
        if (isset($_GET['payment-request']) && $_GET['payment-request'] == 'sent') {
            $messages['post'][4] = esc_html__('Booking updated and payment request email has been sent to customer.', 'mphb-request-payment');
        }

        return $messages;
    }
}
