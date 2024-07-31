<?php

namespace MPHB\Addons\RequestPayment\MetaBoxes;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;
use MPHB\Addons\RequestPayment\Utils\RequestUtils;
use MPHB\Addons\RequestPayment\Settings;
use MPHB\Admin\Fields\FieldFactory;
use MPHB\Utils\ValidateUtils;

class RequestPaymentMetaBox extends CustomMetaBox
{
    /**
     * @param \MPHB\Admin\Groups\MetaBoxGroup[] $metaBoxes
     * @param string $postType
     * @return \MPHB\Admin\Groups\MetaBoxGroup[]
     */
    public function registerInMphb($metaBoxes, $postType)
    {
        if ($postType == $this->postType) {
            $booking = BookingUtils::getEditingBooking();

            // No booking - no metabox
            if (!is_null($booking)) {
                // Catch new status on "Update Booking" action
                $bookingStatus = isset($_POST['mphb_post_status']) ? $_POST['mphb_post_status'] : $booking->getStatus();

                // Don't show the metabox at all for cancelled and abandoned bookings
                if (BookingUtils::isRequestAvailableForStatus($bookingStatus)) {
                    $metaBoxes[] = $this;
                }
            }
        }

        return $metaBoxes;
    }

    protected function registerFields()
    {
        $this->addFields(array(
            FieldFactory::create('_disable_payment_request', array(
                'type'        => 'checkbox',
                'inner_label' => esc_html__('Disable automatic payment requests for this booking', 'mphb-request-payment'),
                'default'     => false
            ))
        ));
    }

    public function render()
    {
        // Render all fields first
        parent::render();

        // Now render the additional information
        $booking = BookingUtils::getEditingBooking();

        if (is_null($booking) || BookingUtils::isRequestDisabled($booking)) {
            // But not for bookings with disabled payment requests
            return;
        }

        $requestUrl  = RequestUtils::buildLink($booking);
        $requestLink = '<a href="' . esc_url($requestUrl) . '">' . esc_url($requestUrl) . '</a>';

        echo '<p class="mphbrp-request-link">';
            echo esc_html__('Payment request link to send manually', 'mphb-request-payment') . '<br/>';
            echo $requestLink;
        echo '</p>';

        echo '<p class="mphbrp-send-request">';
            echo '<input name="send_payment_request" type="submit" class="button button-primary button-large" value="' . esc_attr__('Send Payment Request Now', 'mphb-request-payment') . '" />';
        echo '</p>';

        MPHBRP()->assets()->enqueueAdmin();
    }

    public function save()
    {
        parent::save();

        // Mayme send the payment request (manually)
        if (isset($_POST['send_payment_request']) && $this->isValidRequest()) {
            RequestUtils::sendRequest(BookingUtils::getEditingBooking());
        }
    }
}
