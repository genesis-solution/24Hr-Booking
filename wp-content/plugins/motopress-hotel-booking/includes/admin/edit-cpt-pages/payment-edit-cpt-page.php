<?php

namespace MPHB\Admin\EditCPTPages;

class PaymentEditCPTPage extends EditCPTPage {

	public function customizeMetaBoxes() {

		remove_meta_box( 'submitdiv', $this->postType, 'side' );

		add_meta_box( 'submitdiv', __( 'Update Payment', 'motopress-hotel-booking' ), array( $this, 'renderSubmitMetaBox' ), $this->postType, 'side' );
		add_meta_box( 'logs', __( 'Logs', 'motopress-hotel-booking' ), array( $this, 'renderLogMetaBox' ), $this->postType, 'side' );
	}

	public function renderSubmitMetaBox( $post, $metabox ) {
		$postTypeObject = get_post_type_object( $this->postType );
		$can_publish    = current_user_can( $postTypeObject->cap->publish_posts );
		$postStatus     = get_post_status( $post->ID );

		// Select Completed status by default on the payment addition page
		if ( $this->isCurrentAddNewPage() && $postStatus === 'auto-draft' ) {
			$postStatus = \MPHB\PostTypes\PaymentCPT\Statuses::STATUS_COMPLETED;
		}
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
				</div>
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="mphb_post_status">Status:</label>
						<select name="mphb_post_status" id="mphb_post_status">
							<?php foreach ( MPHB()->postTypes()->payment()->statuses()->getStatuses() as $statusName => $statusDetails ) { ?>
								<option value="<?php echo esc_attr( $statusName ); ?>" <?php selected( $statusName, $postStatus ); ?>>
									<?php echo esc_html( mphb_get_status_label( $statusName ) ); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="misc-pub-section">
						<span><?php esc_html_e( 'Created on:', 'motopress-hotel-booking' ); ?></span>
						<strong><?php echo esc_html( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), strtotime( $post->post_date ) ) ); ?></strong>
						<br/>
						<span><?php esc_html_e( 'Modified on:', 'motopress-hotel-booking' ); ?></span>
						<strong><?php echo esc_html( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), strtotime( $post->post_modified ) ) ); ?></strong>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$delete_text = __( 'Delete Permanently', 'motopress-hotel-booking' );
						} else {
							$delete_text = __( 'Move to Trash', 'motopress-hotel-booking' );
						}
						?>
						<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo esc_html( $delete_text ); ?></a>
					<?php } ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update Payment', 'motopress-hotel-booking' ); ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="
					<?php
					in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create Payment', 'motopress-hotel-booking' ) : esc_attr_e( 'Update Payment', 'motopress-hotel-booking' );
					?>
					" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	public function renderLogMetaBox( $post, $metabox ) {
		$payment = MPHB()->getPaymentRepository()->findById( $post->ID );

		echo '<textarea rows="3" name="_mphb_add_log" style="width:100%"></textarea><br/>';

		foreach ( array_reverse( $payment->getLogs() ) as $log ) {
			?>
			<hr/>
			<strong> <?php esc_html_e( 'Date:', 'motopress-hotel-booking' ); ?></strong>
			<span>
				<?php echo esc_html( mysql2date( MPHB()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), $log['date'] ) ); ?>
			</span><br/>
			<strong><?php esc_html_e( 'Message:', 'motopress-hotel-booking' ); ?></strong>
			<span> 
			<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $log['message'];
			?>
				</span>
			<?php
		}
	}

	public function saveMetaBoxes( $postId, $post, $update ) {
		$success = parent::saveMetaBoxes( $postId, $post, $update );

		if ( ! $success ) {
			return false;
		}

		$status = isset( $_POST['mphb_post_status'] ) ? sanitize_text_field( wp_unslash( $_POST['mphb_post_status'] ) ) : '';

		if ( ! array_key_exists( $status, MPHB()->postTypes()->payment()->statuses()->getStatuses() ) ) {
			$status = '';
		}

		$paymentRepository = MPHB()->getPaymentRepository();

		$payment = $paymentRepository->findById( $postId, true );
		$payment->setStatus( $status );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$addLog = isset( $_POST['_mphb_add_log'] ) ? mphb_clean( wp_unslash( $_POST['_mphb_add_log'] ) ) : '';

		if ( ! empty( $addLog ) ) {
			$payment->addLog( $addLog );
		}

		$paymentRepository->save( $payment );
	}
}
