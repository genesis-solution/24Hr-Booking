<?php

namespace MPHB\Notifier\Admin\Groups;

use MPHB\Admin\Groups\MetaBoxGroup;
use MPHB\Notifier\PostTypes\NotificationCPT;
use MPHB\Admin\Fields\FieldFactory;
use MPHB\Notifier\Entities\Notification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class NotificationSettingsFieldsGroup extends MetaBoxGroup {


	public function __construct() {

		parent::__construct(
			'mphb_notification_settings',
			__( 'Settings', 'mphb-notifier' ),
			NotificationCPT::NOTIFICATION_POST_TYPE_NAME
		);

		$this->initMetaBoxFields();
	}


	private function initMetaBoxFields() {

		// Add fields to "Settings" metabox
		$notificationTypes = Notification::getAllNotificationTypes();
		$roomTypes         = MPHB()->getRoomTypePersistence()->getIdTitleList( array(), array( 0 => __( 'All', 'mphb-notifier' ) ) );

		$this->addFields(
			array(
				FieldFactory::create(
					'mphb_notification_type',
					array(
						'type'    => 'select',
						'label'   => __( 'Type', 'mphb-notifier' ),
						'list'    => $notificationTypes,
						'default' => 'email',
					)
				),
				FieldFactory::create(
					'mphb_notification_sending_mode',
					array(
						'type'    => 'radio',
						'label'   => __( 'Trigger', 'mphb-notifier' ),
						'list'    => array(
							'automatic' => __( 'Automatic', 'mphb-notifier' ),
							'manual'    => __( 'Manual', 'mphb-notifier' ),
						),
						'default' => 'automatic',
					)
				),
				FieldFactory::create(
					'mphb_notification_trigger',
					array(
						'type'  => 'trigger-date',
						'label' => __( 'Period', 'mphb-notifier' ),
					)
				),
				FieldFactory::create(
					'mphb_is_disabled_for_reservation_after_trigger',
					array(
						'type'        => 'checkbox',
						'inner_label' => __( 'Skip this notification for reservations made later than the set time frame.', 'mphb-notifier' ),
						'default'     => false,
					)
				),
				FieldFactory::create(
					'mphb_notification_accommodation_type_ids',
					array(
						'type'      => 'multiple-checkbox',
						'label'     => __( 'Accommodations', 'mphb-notifier' ),
						'all_value' => 0,
						'default'   => array( 0 ),
						'list'      => $roomTypes,
					)
				),
				FieldFactory::create(
					'mphb_notification_recipients',
					array(
						'type'                => 'multiple-checkbox',
						'label'               => __( 'Recipients', 'mphb-notifier' ),
						'list'                => array(
							'admin'    => __( 'Admin', 'mphb-notifier' ),
							'customer' => __( 'Customer', 'mphb-notifier' ),
							'custom'   => __( 'Custom Email Addresses', 'mphb-notifier' ),
						),
						'allow_group_actions' => false,
						'default'             => array(),
					)
				),
				FieldFactory::create(
					'mphb_notification_custom_emails',
					array(
						'type'        => 'text',
						'label'       => __( 'Custom Email Addresses', 'mphb-notifier' ),
						'description' => __( 'You can use multiple comma-separated email addresses.', 'mphb-notifier' ),
						'size'        => 'large',
					)
				),
			)
		);
	}
}
