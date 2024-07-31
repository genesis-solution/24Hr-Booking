<?php

namespace MPHB\Addons\RequestPayment\Utils;

use MPHB\Addons\RequestPayment\Settings;
use MPHB\Entities\Booking;
use MPHB\PostTypes\BookingCPT\Statuses as BookingStatuses;

class BookingUtils
{
    /**
     * @return \MPHB\Entities\Booking|null
     */
    public static function getEditingBooking()
    {
        $postId = 0;

        if (isset($_REQUEST['post_ID']) && is_numeric($_REQUEST['post_ID'])) {
            $postId = intval($_REQUEST['post_ID']); // On post update ($_POST)

        } else if (isset($_REQUEST['post']) && is_numeric($_REQUEST['post'])) {
            $postId = intval($_REQUEST['post']); // On post edit page ($_GET)
        }

        return ($postId > 0) ? MPHB()->getBookingRepository()->findById($postId) : null;
    }

    /**
     * @param string $bookingKey
     * @return \MPHB\Entities\Booking|null
     */
    public static function getBookingByKey($bookingKey)
    {
        return MPHB()->getBookingRepository()->findByMeta('mphb_key', $bookingKey);
    }

    /**
     * Will generate and save new key if the booking does not have it.
     *
     * @param \MPHB\Entities\Booking $booking
     * @return string
     */
    public static function getOrderKey($booking)
    {
        $key = $booking->getKey();

        if (empty($key)) {
            // This will also automatically save the new key
            $key = $booking->generateKey();
        }

        return $key;
    }

    /**
     * Will generate and save new ID if the booking does not have it.
     *
     * @param \MPHB\Entities\Booking $booking
     * @return string
     */
    public static function getCheckoutId($booking)
    {
        $checkoutId = $booking->getCheckoutId();

        if (empty($checkoutId)) {
            $checkoutId = mphb_generate_uuid4();

            // Save new checkout ID
            update_post_meta($booking->getId(), '_mphb_checkout_id', $checkoutId);
        }

        return $checkoutId;
    }

    /**
     * @param int|\MPHB\Entities\Booking $booking Booking entity or it's ID.
     * @return bool
     */
    public static function isRequestDisabled($booking)
    {
        $bookingId = is_object($booking) ? $booking->getId() : $booking;

        // "" (no post meta, or has empty value - not enabled) or "1" (enabled)
        $requestDisabled = get_post_meta($bookingId, '_disable_payment_request', true);

        return (bool)$requestDisabled;
    }

    /**
     * @param string $bookingStatus
     * @return bool
     */
    public static function isRequestAvailableForStatus($bookingStatus)
    {
        $requestableStatuses = static::getRequestableStatuses();

        return in_array($bookingStatus, $requestableStatuses);
    }

    public static function getRequestableStatuses()
    {
        return apply_filters('mphb_requestable_booking_statuses', array(BookingStatuses::STATUS_CONFIRMED));
    }

    /**
     * @param int|\MPHB\Entities\Booking $booking Booking entity or it's ID.
     * @return bool
     */
    public static function isRequestSent($booking)
    {
        $bookingId = is_object($booking) ? $booking->getId() : $booking;

        // "" (no post meta, or has empty value - not sent) or "1" (sent)
        $requestSent = get_post_meta($bookingId, '_payment_request_sent', true);

        return (bool)$requestSent;
    }

    /**
     * @param int|\MPHB\Entities\Booking $booking Booking entity or it's ID.
     * @return bool
     */
    public static function isRequestReady($booking)
    {
        $bookingId = is_object($booking) ? $booking->getId() : $booking;

        // [] (no post meta), [""] (not ready) or ["1"] (ready)
        $metaValue = get_post_meta($bookingId, '_ready_for_payment_request');

        if (empty($metaValue)) {
            // No meta value, but != [""], so the booking is ready
            return true;
        } else {
            $requestReady = reset($metaValue);
            return (bool)($requestReady);
        }
    }

    /**
     * @param int|\MPHB\Entities\Booking $booking
     * @return MPHB\Entities\Payment[]
     */
    public static function getPayments($booking)
    {
        $bookingId = is_object($booking) ? $booking->getId() : $booking;
        $payments  = MPHB()->getPaymentRepository()->findAll(array('booking_id' => $bookingId));

        return $payments;
    }

    public static function getPaidPrice($booking)
    {
        $payments = static::getPayments($booking);
        $paid = PaymentUtils::getPaidPrice($payments);

        return $paid;
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     * @return float
     */
    public static function getToPayPrice($booking)
    {
        $total = $booking->getTotalPrice();
        $paid  = static::getPaidPrice($booking);

        return $total - $paid;
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     * @return string
     */
    public static function renderToPayPrice($booking)
    {
        $toPay = static::getToPayPrice($booking);

        return mphb_format_price($toPay);
    }
}
