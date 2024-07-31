<?php

namespace MPHB\Addons\RequestPayment\Utils;

use MPHB\Addons\RequestPayment\Settings;
use MPHB\Entities\Booking;
use MPHB\PostTypes\BookingCPT\Statuses as BookingStatuses;
use MPHB\Utils\DateUtils;

class RequestUtils
{
    /**
     * @param \MPHB\Entities\Booking|null $booking
     * @return string
     */
    public static function buildLink($booking)
    {
        $link = Settings::getCheckoutPageUrl();

        if (!is_null($booking)) {
            $orderKey = BookingUtils::getOrderKey($booking);
            $link = add_query_arg('key', $orderKey, $link);
        }

        return $link;
    }

    public static function getUnrequestedIds()
    {
        global $wpdb;

        $bookingType = MPHB()->postTypes()->booking()->getPostType();

        $lastSkippedId = Settings::getLastSkippedBookingId();

        $requestableStatuses = BookingUtils::getRequestableStatuses();
        $requestableStatusesString = "'" . implode("', '", $requestableStatuses) . "'";

        $query = "SELECT posts.ID AS ids"
            . " FROM {$wpdb->posts} AS posts"
            . " LEFT JOIN {$wpdb->postmeta} AS ready_meta"
                . " ON posts.ID = ready_meta.post_id AND ready_meta.meta_key = '_ready_for_payment_request'"
            . " LEFT JOIN {$wpdb->postmeta} AS disable_meta"
                . " ON posts.ID = disable_meta.post_id AND (disable_meta.meta_key = '_disable_payment_request' OR disable_meta.meta_key = '_payment_request_sent')"
            . " WHERE posts.post_type = '{$bookingType}' AND posts.ID > {$lastSkippedId} AND posts.post_status IN ({$requestableStatusesString})"
                . " AND (ready_meta.post_id IS NULL OR ready_meta.meta_value = '1')"
                . " AND (disable_meta.post_id IS NULL OR disable_meta.meta_value != '1')"
            . " GROUP BY ids";

        $ids = $wpdb->get_col($query);
        $ids = array_map('absint', $ids);

        return $ids;
    }

    /**
     * @param \MPHB\Entity\Booking $booking
     * @return bool
     */
    public static function isTimeForRequest($booking)
    {
        $itsTime = false;

        // Check the dates
        $checkIn = $booking->getCheckInDate();
        $checkInTime = MPHB()->settings()->dateTime()->getCheckInTime(true);
        $checkIn->setTime($checkInTime[0], $checkInTime[1], $checkInTime[2]);

        $now = new \DateTime('now');

        $daysToCheckIn     = DateUtils::calcNights($now, $checkIn);
        $daysBeforeCheckIn = Settings::getDaysBeforeCheckIn();

        if ($daysToCheckIn <= $daysBeforeCheckIn) {
            $itsTime = true;
        }

        return $itsTime;
    }

    /**
     * @param \MPHB\Entities\Booking|null $booking
     * @rapam bool $isAuto Optional. False by default.
     */
    public static function sendRequest($booking, $isAuto = false)
    {
        if (is_null($booking)) {
            return;
        }

        MPHB()->emails()->getEmail('customer_request_payment')->trigger($booking, array(), $isAuto);

        update_post_meta($booking->getId(), '_payment_request_sent', true);
        update_post_meta($booking->getId(), '_ready_for_payment_request', false);
    }
}
