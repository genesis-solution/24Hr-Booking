<?php

namespace MPHB\Notifier\Admin\CPTPages;

use MPHB\Admin\EditCPTPages\EditCPTPage;
use MPHB\Notifier\Helpers\NotificationHelper;
use MPHB\Notifier\Entities\Notification;


/**
 * @since 1.0
 */
class EditNotificationPage extends EditCPTPage {


	public function registerMetaBoxes() {

		// Show available macroses below the field
		if ( $this->isCurrentPage() ) {

			$emailGroup   = $this->fieldGroups['email'];
			$messageField = $emailGroup->getFieldByName( 'mphb_notification_email_message' );

			if ( ! is_null( $messageField ) ) {

				$emailTemplater = NotificationHelper::getEmailNotificationsTemplater();

				if ( method_exists( $messageField, 'setDescription2' ) ) {

					$messageField->setDescription2( $emailTemplater->getTagsDescription() );

				} else {

					$newDescription = $messageField->getDescription() . '<br /><br />' . $emailTemplater->getTagsDescription();
					$messageField->setDescription( $newDescription );
				}
			}
		}

		parent::registerMetaBoxes();
	}

	public function customizeMetaBoxes() {

		remove_meta_box( 'submitdiv', $this->getPostType(), 'side' );

		add_meta_box(
			'submitdiv',
			esc_html__( 'Update Notification', 'mphb-notifier' ),
			function( $post ) {
				$this->displaySubmitMetabox( $post );
			},
			$this->getPostType(),
			'side'
		);
	}

	private function displaySubmitMetabox( $post ) {

		$postStatus = get_post_status( $post->ID );

		if ( $postStatus === 'auto-draft' ) {
			$postStatus = Notification::NOTIFICATION_STATUS_DISABLED;
		}

		if ( $this->isCurrentAddNewPage() ) {
			$postStatus = Notification::NOTIFICATION_STATUS_ACTIVE;
		}

		$availableStatuses = Notification::getAllNotificationStatueses();

		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
				</div>
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="mphb_post_status"><?php esc_html_e( 'Status:' ); /* Core text - no textdomain */ ?></label>
						<select name="mphb_post_status" id="mphb_post_status">
							<?php foreach ( $availableStatuses as $statusName => $statusLabel ) { ?>
								<option value="<?php echo esc_attr( $statusName ); ?>" <?php selected( $statusName, $postStatus ); ?>><?php echo esc_html( $statusLabel ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="misc-pub-section">
						<span><?php esc_html_e( 'Created on:', 'mphb-notifier' ); ?></span>
						<strong><?php echo date_i18n( mphb()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), strtotime( $post->post_date ) ); ?></strong>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php if ( current_user_can( 'delete_post', $post->ID ) ) :

						$deleteText = ! EMPTY_TRASH_DAYS ? __( 'Delete Permanently', 'mphb-notifier' ) : __( 'Move to Trash', 'mphb-notifier' );

						?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo esc_html( $deleteText ); ?></a>
					<?php endif; ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update', 'mphb-notifier' ); ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create', 'mphb-notifier' ) : esc_attr_e( 'Update', 'mphb-notifier' ); ?>" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	public function saveMetaBoxes( $postId, $post, $update ) {

		if ( ! parent::saveMetaBoxes( $postId, $post, $update ) ) {
			return false;
		}

		if ( ! current_user_can( 'publish_mphb_notifications' ) ) {
			return;
		}

		// Save custom post status
		$notificationStatus = isset( $_POST['mphb_post_status'] ) ? sanitize_text_field( wp_unslash( $_POST['mphb_post_status'] ) ) : '';

		wp_update_post(
			array(
				'ID'          => $postId,
				'post_status' => $notificationStatus,
			)
		);
	}
}
