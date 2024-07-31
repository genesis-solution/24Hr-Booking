<?php

namespace MPHB\Addons\Invoice\Utils;

use MPHB\PostTypes\PaymentCPT\Statuses as PaymentStatuses;
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
     * @return float
     */
    public static function getPaidPrice($booking)
    {
        $payments = static::getPayments($booking);

        $paid = array_reduce($payments, function ($paid, $payment) {
            if ($payment->getStatus() == PaymentStatuses::STATUS_COMPLETED) {
                $paid += $payment->getAmount();
            }

            return $paid;
        }, 0.0);

        return $paid;
    }

    /**
     * @param int|\MPHB\Entities\Booking $booking
     * @return MPHB\Entities\Payment[]
     */
    public static function getPayments($booking)
    {
        $bookingId = is_object($booking) ? $booking->getId() : $booking;
        $payments  = mphb()->getPaymentRepository()->findAll(['booking_id' => $bookingId]);

        return $payments;
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     * @param bool $includeFees Optional. TRUE by default.
     * @return float
     */
    public static function getTaxesTotal($booking, $includeFees = true)
    {
        $prices = $booking->getLastPriceBreakdown();

        if (empty($prices) || empty($prices['rooms'])) {
            return 0;
        }

        $taxTotal = 0.0;

        foreach ($prices['rooms'] as $roomPrices) {
            // Add fees
            if ($includeFees && isset($roomPrices['fees'])) {
                $taxTotal += floatval($roomPrices['fees']['total']);
            }

            // Add taxes
            if (isset($roomPrices['taxes'])) {
                foreach ($roomPrices['taxes'] as $taxes) {
                    $taxTotal += floatval($taxes['total']);
                }
            }
        }

        return $taxTotal;
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
     * @param \MPHB\Entities\Booking $booking
     * @return float
     */
    public static function getDiscountTotal($booking)
    {
        $prices = $booking->getLastPriceBreakdown();

        if (empty($prices) || !isset($prices['coupon'])) {
            return 0;
        }

        $discountTotal = floatval($prices['coupon']['discount']);

        return $discountTotal;
    }
}
