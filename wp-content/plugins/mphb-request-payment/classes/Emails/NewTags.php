<?php

namespace MPHB\Addons\RequestPayment\Emails;

use MPHB\Addons\RequestPayment\Utils\BookingUtils;
use MPHB\Addons\RequestPayment\Utils\RequestUtils;

class NewTags
{
    public function __construct()
    {
        $this->addActions();
    }

    protected function addActions()
    {
        // See MPHB\Emails\Templaters\EmailTemplater::_fill*Tags();
        add_filter('mphb_email_booking_tags', array($this, 'addLinkTag'));
        add_filter('mphb_email_booking_tags', array($this, 'addPriceTag'));

        add_filter('mphb_email_replace_tag', array($this, 'replaceTag'), 10, 3);
    }

    public function addLinkTag($tags)
    {
        $tag = array(
            'name'             => 'booking_payment_request_link',
            'description'      => esc_html__('Booking Payment Request Link', 'mphb-request-payment'),
            'deprecated'       => false,
            'deprecated_title' => '',
            'inner_tags'       => array()
        );

        $this->insertTagAfter($tag, $tags, 'booking_edit_link');

        return $tags;
    }

    public function addPriceTag($tags)
    {
        $tag = array(
            'name'             => 'booking_balance_due',
            'description'      => esc_html__('Booking Balance Due', 'mphb-request-payment'),
            'deprecated'       => false,
            'deprecated_title' => '',
            'inner_tags'       => array()
        );

        $this->insertTagAfter($tag, $tags, 'booking_total_price');

        return $tags;
    }

    /**
     * @param array $tags Only associative array [Name => Tag].
     * @param string $group
     */
    public function addTags($tags, $group)
    {
        if ($group == 'booking') {
            if (!isset($tags['booking_payment_request_link'])) {
                $tags = $this->addLinkTag($tags);
            }

            if (!isset($tags['booking_balance_due'])) {
                $tags = $this->addPriceTag($tags);
            }
        }

        return $tags;
    }

    /**
     * @param array $tag Single tag.
     * @param array $tags An array of tags.
     * @param string $afterTag
     */
    protected function insertTagAfter($tag, &$tags, $afterTag)
    {
        $index  = array_search($afterTag, array_keys($tags));
        $insert = array($tag['name'] => $tag);

        if ($index !== false) {
            $tags = array_slice($tags, 0, $index + 1, true)
                + $insert
                + array_slice($tags, $index + 1, count($tags), true);
        } else {
            $tags = $tags + $insert;
        }
    }

    /**
     * @param string $replacement
     * @param string $tag
     * @param \MPHB\Entities\Booking $booking
     * @return string
     */
    public function replaceTag($replacement, $tag, $booking = null)
    {
        if (empty($replacement) && !is_null($booking)) { // Nothing to do here without booking
            switch ($tag) {
                case 'booking_payment_request_link':
                    $replacement = RequestUtils::buildLink($booking);
                    break;

                case 'booking_balance_due':
                    $replacement = BookingUtils::renderToPayPrice($booking);
                    break;
            }
        }

        return $replacement;
    }
}
