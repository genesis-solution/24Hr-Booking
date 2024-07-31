<?php

namespace MPHB\Notifier\PostTypes;

use MPHB\Notifier\Admin\CPTPages\EditNotificationPage;
use MPHB\Notifier\Admin\CPTPages\ManageNotificationsPage;
use MPHB\PostTypes\EditableCPT;
use MPHB\Notifier\Entities\Notification;

/**
 * @since 1.0
 */
class NotificationCPT extends EditableCPT {

	const NOTIFICATION_POST_TYPE_NAME = 'mphb_notification';


	public function __construct() {

		$this->postType = static::NOTIFICATION_POST_TYPE_NAME;

		parent::__construct();
	}

	public function addActions() {

		parent::addActions();

		add_action( 'admin_menu', array( $this, 'moveSubmenu' ), 1000 );
	}

	public function createManagePage() {

		return new ManageNotificationsPage( static::NOTIFICATION_POST_TYPE_NAME );
	}

	protected function createEditPage() {

		return new EditNotificationPage( static::NOTIFICATION_POST_TYPE_NAME, $this->getFieldGroups() );
	}

	public function register() {

		register_post_type(
			static::NOTIFICATION_POST_TYPE_NAME,
			array(
				'labels'               => array(
					'name'                  => esc_html__( 'Notifications', 'mphb-notifier' ),
					'singular_name'         => esc_html__( 'Notification', 'mphb-notifier' ),
					'add_new'               => esc_html_x( 'Add New', 'Add new notification', 'mphb-notifier' ),
					'add_new_item'          => esc_html__( 'Add New Notification', 'mphb-notifier' ),
					'edit_item'             => esc_html__( 'Edit Notification', 'mphb-notifier' ),
					'new_item'              => esc_html__( 'New Notification', 'mphb-notifier' ),
					'view_item'             => esc_html__( 'View Notification', 'mphb-notifier' ),
					'search_items'          => esc_html__( 'Search Notification', 'mphb-notifier' ),
					'not_found'             => esc_html__( 'No notifications found', 'mphb-notifier' ),
					'not_found_in_trash'    => esc_html__( 'No notifications found in Trash', 'mphb-notifier' ),
					'all_items'             => esc_html__( 'Notifications', 'mphb-notifier' ),
					'insert_into_item'      => esc_html__( 'Insert into notification description', 'mphb-notifier' ),
					'uploaded_to_this_item' => esc_html__( 'Uploaded to this notification', 'mphb-notifier' ),
				),
				'public'               => false,
				'show_ui'              => true,
				'show_in_menu'         => mphb()->menus()->getMainMenuSlug(),
				'supports'             => array( 'title' ),
				'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
				'rewrite'              => false,
				'show_in_rest'         => true,
				'map_meta_cap'         => true,
				'capability_type'      => array( 'mphb_notification', 'mphb_notifications' ),
			)
		);
	}

	public function getFieldGroups() {

		return array(
			'settings' => new \MPHB\Notifier\Admin\Groups\NotificationSettingsFieldsGroup(),
			'email'    => new \MPHB\Notifier\Admin\Groups\EmailNotificationFieldsGroup(),
		);
	}

	/**
	 * Callback for action "admin_menu".
	 *
	 * @global array $submenu
	 */
	public function moveSubmenu() {

		global $submenu;

		if ( ! isset( $submenu['mphb_booking_menu'] ) ) {
			return;
		}

		$bookingMenu = &$submenu['mphb_booking_menu'];

		$notificationsIndex = false;
		$syncIndex          = false;

		$currentScreen = 'edit.php?post_type=' . static::NOTIFICATION_POST_TYPE_NAME;

		foreach ( $bookingMenu as $index => $bookingSubmenu ) {
			if ( ! isset( $bookingSubmenu[2] ) ) {
				continue;
			}

			$screen = $bookingSubmenu[2];

			if ( $screen === $currentScreen ) {
				$notificationsIndex = $index;
			} elseif ( $screen === 'mphb_ical' ) {
				$syncIndex = $index;
			}
		}

		if ( $notificationsIndex !== false && $syncIndex !== false ) {
			$notificationSubmenu = array_splice( $bookingMenu, $notificationsIndex, 1 );
			if ( $notificationsIndex < $syncIndex ) {
				$syncIndex--;
			}
			array_splice( $bookingMenu, $syncIndex, 0, $notificationSubmenu );
		}

		unset( $bookingMenu );
	}
}
