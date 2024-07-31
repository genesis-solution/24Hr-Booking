<?php

namespace MPHB\Notifier\Admin\MetaBoxes;

use MPHB\Admin\Fields\FieldFactory;

/**
 * @since 1.0
 */
class NoticesMetaBox extends CustomMetaBox
{
    /**
     * @return array
     */
    protected function generateFields()
    {
        return [
            'notice_1' => FieldFactory::create('mphb_notification_notice_1', [
                'type'              => 'textarea',
                // translators: "Notice 1", "Notice 2" etc.
                'label'             => sprintf(esc_html__('Notice %d', 'mphb-notifier'), 1),
                'rows'              => 2,
                'translatable'      => true
            ]),
            'notice_2' => FieldFactory::create('mphb_notification_notice_2', [
                'type'              => 'textarea',
                // translators: "Notice 1", "Notice 2" etc.
                'label'             => sprintf(esc_html__('Notice %d', 'mphb-notifier'), 2),
                'rows'              => 2,
                'translatable'      => true
            ])
        ];
    }
}
