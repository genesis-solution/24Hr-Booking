<?php

namespace MPHB\Notifier\Admin\Groups;

use MPHB\Admin\Groups\SettingsGroup;
use MPHB\Admin\Fields\FieldFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class MainSettingsGroup extends SettingsGroup {

	public function __construct( $name, $label, $page, $description = '' ) {

		parent::__construct( $name, $label, $page, $description );

		$this->addFields(
			array(
				FieldFactory::create(
					'mphb_notifier_do_not_send_imported_bookings_to_admin',
					array(
						'type'        => 'checkbox',
						'label'       => esc_html__( 'Imported/external bookings', 'mphb-notifier' ),
						'inner_label' => __( 'Do not send notifications for imported bookings to Administrator.', 'mphb-notifier' ),
						'default'     => mphb_notifier()->settings()->isDoNotSendImportedBookingsToAdmin(),
					)
				),
				FieldFactory::create(
					'mphb_notifier_do_not_send_imported_bookings_to_customer',
					array(
						'type'        => 'checkbox',
						'inner_label' => __( 'Do not send notifications for imported bookings to Customer.', 'mphb-notifier' ),
						'default'     => mphb_notifier()->settings()->isDoNotSendImportedBookingsToCustomer(),
					)
				),
				FieldFactory::create(
					'mphb_notifier_do_not_send_imported_bookings_to_custom_emails',
					array(
						'type'        => 'checkbox',
						'inner_label' => __( 'Do not send notifications for imported bookings to Custom email addresses.', 'mphb-notifier' ),
						'default'     => mphb_notifier()->settings()->isDoNotSendImportedBookingsToCustomEmails(),
					)
				),
			)
		);
	}
}
