<?php
namespace MPHB\Notifier\Admin\Groups;

use MPHB\Admin\Groups\MetaBoxGroup;
use MPHB\Notifier\PostTypes\NotificationCPT;
use MPHB\Admin\Fields\FieldFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class EmailNotificationFieldsGroup extends MetaBoxGroup {


	public function __construct() {

		parent::__construct(
			'mphb_notification_email',
			__( 'Email', 'mphb-notifier' ),
			NotificationCPT::NOTIFICATION_POST_TYPE_NAME
		);

		$this->initMetaBoxFields();
	}


	private function initMetaBoxFields() {

		$this->addFields(
			array(
				'email_subject' => FieldFactory::create(
					'mphb_notification_email_subject',
					array(
						'type'         => 'text',
						'label'        => __( 'Subject', 'mphb-notifier' ),
						'size'         => 'large',
						'default'      => mphb_notifier()->settings()->getDefaultSubject(),
						'translatable' => true,
					)
				),
				'email_header'  => FieldFactory::create(
					'mphb_notification_email_header',
					array(
						'type'         => 'text',
						'label'        => __( 'Header', 'mphb-notifier' ),
						'size'         => 'large',
						'default'      => mphb_notifier()->settings()->getDefaultHeader(),
						'translatable' => true,
					)
				),
				'email_message' => FieldFactory::create(
					'mphb_notification_email_message',
					array(
						'type'         => 'rich-editor',
						'label'        => __( 'Message', 'mphb-notifier' ),
						// We will add "Possible tags:" later in EditNotificationPage
						'description'  => __( 'To replace the Accommodation Notice 1/Notice 2 tags you use in the email with custom property information, go to Accommodation types to fill in the respective fields.', 'mphb-notifier' ),
						'rows'         => 21,
						'default'      => mphb_notifier()->settings()->getDefaultMessage(),
						'translatable' => true,
					)
				),
			)
		);
	}
}
