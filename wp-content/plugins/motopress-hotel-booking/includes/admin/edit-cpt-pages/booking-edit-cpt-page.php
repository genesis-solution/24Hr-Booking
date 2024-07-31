<?php

namespace MPHB\Admin\EditCPTPages;

class BookingEditCPTPage extends EditCPTPage {

	protected function addActions() {
		parent::addActions();
		add_action( '_mphb_admin_after_field_render', array( $this, 'renderPaymentsDetails' ) );
		add_action( 'admin_init', array( $this, 'resendConfirmationEmail' ) );
		add_filter( 'post_updated_messages', array( $this, 'onResendEmailMessage' ) );
		add_action( 'add_meta_boxes', array( $this, 'addResendEmailMetabox' ), 10, 2 );
	}

	public function customizeMetaBoxes() {
		remove_meta_box( 'submitdiv', $this->postType, 'side' );
		remove_meta_box( 'commentsdiv', $this->postType, 'normal' );
		remove_meta_box( 'commentstatusdiv', $this->postType, 'normal' );

		add_meta_box( 'mphb_rooms', __( 'Reserved Accommodations', 'motopress-hotel-booking' ), array( $this, 'renderRoomsDetailsMetaBox' ), $this->postType, 'advanced' );
		add_meta_box( 'submitdiv', __( 'Update Booking', 'motopress-hotel-booking' ), array( $this, 'renderSubmitMetaBox' ), $this->postType, 'side' );
		add_meta_box( 'logs', __( 'Logs', 'motopress-hotel-booking' ), array( $this, 'renderLogMetaBox' ), $this->postType, 'side' );
	}

	public function renderSubmitMetaBox( $post, $metabox ) {
		$postTypeObject = get_post_type_object( $this->postType );
		$can_publish    = current_user_can( $postTypeObject->cap->publish_posts );
		$postStatus     = get_post_status( $post->ID );
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
				</div>
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="mphb_post_status">Status:</label>
						<select name="mphb_post_status" id="mphb_post_status">
							<?php foreach ( MPHB()->postTypes()->booking()->statuses()->getStatuses() as $statusName => $statusDetails ) { ?>
								<option value="<?php echo esc_attr( $statusName ); ?>" <?php selected( $statusName, $postStatus ); ?>>
									<?php echo esc_html( mphb_get_status_label( $statusName ) ); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="misc-pub-section">
						<span><?php esc_html_e( 'Created on:', 'motopress-hotel-booking' ); ?></span>
						<strong><?php echo esc_html( date_i18n( MPHB()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), strtotime( $post->post_date ) ) ); ?></strong>
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
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $delete_text ); ?></a>
					<?php } ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update Booking', 'motopress-hotel-booking' ); ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="
					<?php
					in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create Booking', 'motopress-hotel-booking' ) : esc_attr_e( 'Update Booking', 'motopress-hotel-booking' );
					?>
					" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/**
	 *
	 * @since 3.9.3
	 */
	public function addResendEmailMetabox( $post_type, $post ) {
		if ( $post->post_status == 'confirmed' ) {
			add_meta_box( 'resendconfirm', __( 'Resend Email', 'motopress-hotel-booking' ), array( $this, 'renderResendMetaBox' ), $this->postType, 'side', 'low' );
		}
	}

	/**
	 *
	 * @since 3.9.3
	 */
	public function renderResendMetaBox( $post, $metabox ) {
		wp_nonce_field( 'mphb_resend_confirm', 'mphb_resend_confirm_nonce' );
		?>
		<p>
			<input type="hidden" name="mphb_post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
			<input type="submit" name="mphb_resend_confirm" class="button button-primary button-large" value="<?php esc_attr_e( 'Resend Email', 'motopress-hotel-booking' ); ?>" />
		</p>
		<p><?php esc_html_e( 'Send a copy of the Approved Booking email to the customer`s email address.', 'motopress-hotel-booking' ); ?></p>
		<?php
	}

	public function renderRoomsDetailsMetaBox( $post, $metabox ) {

		// TODO add possibility of manage and edit reserved rooms

		$reservedRooms = MPHB()->getReservedRoomRepository()->findAllByBooking( $post->ID );

		mphb_tmpl_the_reserved_rooms_details( $reservedRooms );

		$booking = mphb_get_booking( $post->ID );

		if ( ! is_null( $booking ) && ! $booking->isImported() ) {
			$editBookingUrl = MPHB()->getEditBookingMenuPage()->getUrl( array( 'booking_id' => $post->ID ) );
			echo '<a href="' . esc_url( $editBookingUrl ) . '" class="button">', esc_html__( 'Edit Accommodations', 'motopress-hotel-booking' ), '</a>';
		}
	}

	public function renderLogMetaBox( $post, $metabox ) {
		$booking = MPHB()->getBookingRepository()->findById( $post->ID );

		foreach ( array_reverse( $booking->getLogs() ) as $log ) {
			?>
			<strong> <?php esc_html_e( 'Date:', 'motopress-hotel-booking' ); ?></strong>
			<span>
				<?php comment_date( MPHB()->settings()->dateTime()->getDateTimeFormatWP( ' @ ' ), $log->comment_ID ); ?>
			</span>
			<br/>
			<strong><?php esc_html_e( 'Author:', 'motopress-hotel-booking' ); ?></strong>
			<?php
			if ( ! empty( $log->user_id ) ) {
				$userInfo = get_userdata( $log->user_id );
				$userName = $userInfo ? $userInfo->display_name : ( $log->comment_author ?: 'DELETED' );

				$restApiKeyId = get_comment_meta( $log->comment_ID, 'api_key_id', true );
				if ( $restApiKeyId ) {
					$url        = admin_url( 'admin.php?page=mphb_settings&tab=advanced&edit-key=' . $restApiKeyId );
					$authorName = sprintf( '<a target="_blank" href="%s">%s</a> by REST API', esc_url( $url ), $userName );
				} else {
					$url        = get_edit_user_link( $log->user_id );
					$authorName = sprintf( '<a target="_blank" href="%s">%s</a>', esc_url( $url ), $userName );
				}
			} else {
				$authorName = '<i>' . __( 'Auto', 'motopress-hotel-booking' ) . '</i>';
			}
			?>
			<span>
			<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $authorName;
			?>
				</span>
			<br/>
			<strong><?php esc_html_e( 'Message:', 'motopress-hotel-booking' ); ?></strong>
			<span> 
			<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $log->comment_content;
			?>
				</span>
			<hr/>
			<?php
		}
	}

	public function renderPaymentsDetails( $fieldName ) {

		// Show payments only for existing bookings
		if ( ! $this->isCurrentEditPage() ) {
			return;
		}

		// Show payments after total price
		if ( $fieldName !== 'mphb_total_price' ) {
			return;
		}

		$booking = MPHB()->getBookingRepository()->findById( get_the_ID() );

		echo '<br/>';

		mphb_tmpl_the_payments_table( $booking );
	}

	/**
	 *
	 * @since 3.9.3
	 */
	public function resendConfirmationEmail() {

		if ( ! isset( $_REQUEST['mphb_resend_confirm_nonce'] ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mphb_resend_confirm_nonce'] ) ), 'mphb_resend_confirm' )
			) {
			return;
		}

		if ( isset( $_REQUEST['mphb_resend_confirm'] ) && isset( $_REQUEST['mphb_post_id'] ) ) {
			$postID  = absint( $_REQUEST['mphb_post_id'] );
			$booking = MPHB()->getBookingRepository()->findById( $postID );

			/**
			 * Triggs when user clicks button.
			 *
			 * @since 3.9.3
			 */
			do_action( 'mphb_resend_confirm', $booking );

			add_filter( 'redirect_post_location', array( $this, 'onRedirectPostLocation' ) );
		}
	}

	/**
	 *
	 * @since 3.9.3
	 */
	function onRedirectPostLocation( $loc ) {
		return add_query_arg( 'confirmation_resend', 'true', $loc );
	}

	/**
	 *
	 * @since 3.9.3
	 */
	function onResendEmailMessage( $messages ) {
		if ( ! empty( $_GET['confirmation_resend'] ) ) {
			$messages['post'][4] = esc_html__( 'Confirmation email has been sent to customer.', 'motopress-hotel-booking' );
		}
		return $messages;
	}

	public function saveMetaBoxes( $postId, $post, $update ) {
		$success = parent::saveMetaBoxes( $postId, $post, $update );

		if ( ! $success ) {
			return false;
		}

		$status = isset( $_POST['mphb_post_status'] ) ? sanitize_text_field( wp_unslash( $_POST['mphb_post_status'] ) ) : '';

		if ( ! array_key_exists( $status, MPHB()->postTypes()->booking()->statuses()->getStatuses() ) ) {
			$status = '';
		}

		$bookingRepository = MPHB()->getBookingRepository();

		$booking = $bookingRepository->findById( $postId, true );
		$booking->setStatus( $status );
		$bookingRepository->save( $booking );
	}

	public function enqueueAdminScripts() {
		parent::enqueueAdminScripts();
		if ( $this->isCurrentPage() ) {
			wp_enqueue_script( 'mphb-jquery-serialize-json' );
		}
	}

}
