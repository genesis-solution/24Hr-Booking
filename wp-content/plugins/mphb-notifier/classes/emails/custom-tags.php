<?php

namespace MPHB\Notifier\Emails;

/**
 * @since 1.0
 */
class CustomTags
{
    protected $tagGroups = [];

    public function __construct()
    {
        $this->setupTags();
        $this->registerTags();
    }

    protected function setupTags()
    {
        $this->tagGroups = [
            // Its "booking" tags, but we need to push them to the top of the list
            'global' => [
                [
                    'name'        => 'accommodation_notice_1',
                    // translators: "Notification Notice 1", "Notification Notice 2" etc.
                    'description' => sprintf(esc_html__('Accommodation Notice %d', 'mphb-notifier'), 1)
                ],
                [
                    'name'        => 'accommodation_notice_2',
                    // translators: "Notification Notice 1", "Notification Notice 2" etc.
                    'description' => sprintf(esc_html__('Accommodation Notice %d', 'mphb-notifier'), 2)
                ]
            ]
        ];
    }

    protected function registerTags()
    {
        // Must be called at least on "plugins_loaded" with priority 9 to work properly.
        // Otherwise it will be too late to add filters "mphb_email_{$groupName}_tags"
        foreach (array_keys($this->tagGroups) as $groupName) {
            add_filter("mphb_email_{$groupName}_tags", [$this, 'addTags']);
        }

        if (!empty($this->tagGroups)) {
            add_filter('mphb_email_replace_tag', [$this, 'replaceTag'], 10, 4);
        }
    }

    /**
     * Callback for filter "mphb_email_{$groupName}_tags".
     *
     * @param array $tags
     * @return array
     */
    public function addTags($tags)
    {
        $filter = current_filter();
        $group = preg_replace('/mphb_email_(\w+)_tags/i', '$1', $filter);

        return $this->addTagsToGroup($tags, $group);
    }

    protected function addTagsToGroup($tags, $group)
    {
        if (array_key_exists($group, $this->tagGroups)) {
            $tags = array_merge($this->tagGroups[$group], $tags);
        }

        return $tags;
    }

    /**
     * Callback for filter "mphb_email_replace_tag".
     *
     * @param string $replaceText
     * @param string $tag
     * @param \MPHB\Entities\Booking|null $booking
     * @param \MPHB\Entities\Payment|null $payment
     * @return string
     */
    public function replaceTag($replaceText, $tag, $booking, $payment)
    {
        switch ($tag) {
            case 'accommodation_notice_1':
            case 'accommodation_notice_2':
                if (!is_null($booking)) {
                    // "mphb_notification_notice_1", "mphb_notification_notice_2"
                    $metaField = str_replace('accommodation', 'mphb_notification', $tag);

                    // Get notice for each booked room
                    $notices = array_map(function ($reservedRoom) use ($metaField, $booking) {
                        $roomTypeId = $reservedRoom->getRoomTypeId();

                        $roomTypeIdTranslated = apply_filters( 'wpml_object_id', $roomTypeId, MPHB()->postTypes()->roomType()->getPostType(), true, $booking->getLanguage() );

                        $notice = get_post_meta($roomTypeIdTranslated, $metaField, true);
                        $notice = nl2br($notice);

                        return $notice;
                    }, $booking->getReservedRooms());

                    $notices = array_filter($notices);
                    $notices = array_unique($notices);

                    $delimeter = apply_filters('mphb_notification_notices_delimeter', '<br />');

                    $replaceText = implode($delimeter, $notices);
                }
                break;
        }

        return $replaceText;
    }
}
