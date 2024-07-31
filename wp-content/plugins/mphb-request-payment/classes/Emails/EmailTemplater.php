<?php

namespace MPHB\Addons\RequestPayment\Emails;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;
use MPHB\Addons\RequestPayment\Settings;

class EmailTemplater extends \MPHB\Emails\Templaters\EmailTemplater
{
    /** @var bool */
    protected $bookingGroupEnabled = false;

    /** @var \MPHB\Entities\Booking|null */
    private $booking = null;

    /** @var \MPHB\Entities\Payment|null */
    private $payment = null;

    public function setTagGroups($groups) {
        parent::setTagGroups($groups);

        $this->bookingGroupEnabled = (isset($groups['booking']) && $groups['booking']);
    }

    public function setupTags()
    {
        parent::setupTags();

        // Add new tags only if group "booking" was enabled
        if ($this->bookingGroupEnabled) {
            $this->tags = MPHBRP()->tags()->addTags($this->tags, 'booking');
        }
    }

    public function replaceTag($regexMatch)
    {
        $replacement = parent::replaceTag($regexMatch);

        if (empty($replacement)) {
            $tag = str_replace('%', '', $regexMatch[0]);

            // Replace with booking instance
            $replacement = MPHBRP()->tags()->replaceTag($replacement, $tag, $this->booking);
        }

        return $replacement;
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     */
    public function setupBooking($booking)
    {
        $this->booking = $booking;

        // Setup booking in parent (private field)
        parent::setupBooking($booking);
    }

    /**
     * @param \MPHB\Entities\Payment $payment
     */
    public function setupPayment($payment)
    {
        $this->payment = $payment;

        // Setup payment in parent (private field)
        parent::setupPayment($payment);
    }
}
