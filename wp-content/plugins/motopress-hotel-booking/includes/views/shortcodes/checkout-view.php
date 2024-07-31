<?php

namespace MPHB\Views\Shortcodes;

use MPHB\Utils\DateUtils;

/**
 * TODO add actions & filters
 * TODO move html to internal templates
 */
class CheckoutView {

	/**
	 *
	 * @since 4.2.0
	 */
	public static function renderCustomerErrors() {

		if ( isset( $_GET['login_failed'] ) && $_GET['login_failed'] == 'error' ) {
			?>
			<p class="mphb-errors-wrapper">
			<?php echo esc_html__( 'Invalid login or password.', 'motopress-hotel-booking' ); ?>
			</p>
			<?php
		}

		if ( isset( $_GET['customer_error'] ) ) {
			if ( $_GET['customer_error'] == 'wp_user_exists' ) {
				?>
				<p class="mphb-errors-wrapper">
					<?php echo esc_html__( 'An account with this email already exists. Please, log in.', 'motopress-hotel-booking' ); ?>
				</p>
				<?php
			}
		}
	}

	/**
	 *
	 * @since 4.2.0
	 */
	public static function renderLoginForm() {
		$showLoginForm = MPHB()->settings()->main()->allowCustomersLogIn();

		if ( ! get_current_user_id() && $showLoginForm ) {
			?>
			<div class="mphb-login-form-wrap">
				<p>
					<?php esc_html_e( 'Returning customer?', 'motopress-hotel-booking' ); ?>
					<a id="mphb-render-checkout-login" href="#"><?php esc_html_e( 'Click here to log in', 'motopress-hotel-booking' ); ?></a>
				</p>
				<div class="mphb-login-form mphb-hide">
					<?php wp_login_form( array( 'redirect' => get_permalink() ) ); ?>
					<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Lost your password?', 'motopress-hotel-booking' ); ?></a>
				</div>
			</div>
			<?php
		} elseif ( get_current_user_id() ) {
			$user = get_user_by( 'id', get_current_user_id() );

			$userDisplayName = $user->data->display_name;
			$logout          = wp_logout_url();
			?>
				<div class="mphb-login-form-wrap">
					<p>
						<?php
						printf(
							wp_kses(
								// translators: 1 - username;
								__( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>).', 'motopress-hotel-booking' ),
								array( 'a' => array( 'href' => array() ) )
							),
							esc_html( $userDisplayName ),
							esc_url( $logout )
						);
						?>
					</p>
				</div>
			<?php
		}
	}

	public static function renderCoupon() {
		if ( ! MPHB()->settings()->main()->isCouponsEnabled() ) {
			return;
		}

		$couponTitle     = apply_filters( 'mphb_sc_checkout_coupon_title', '' );
		$couponLabel     = apply_filters( 'mphb_sc_checkout_coupon_label', __( 'Coupon Code:', 'motopress-hotel-booking' ) );
		$applyCouponText = apply_filters( 'mphb_sc_checkout_coupon_apply_text', __( 'Apply', 'motopress-hotel-booking' ) );
		?>
		<section id="mphb-coupon-details" class="mphb-coupon-code-wrapper mphb-checkout-section">

			<?php do_action( 'mphb_sc_checkout_coupon_top' ); ?>

			<?php if ( ! empty( $couponTitle ) ) { ?>
				<h3>
					<?php echo esc_html( $couponTitle ); ?>
				</h3>
			<?php } ?>

			<?php
			/**
			 * @hooked \MPHB\Views\Shortcodes\CheckoutView::_renderCouponCodeParagraphOpen() - 10
			 */
			do_action( 'mphb_sc_checkout_coupon_before_label' );
			?>

			<?php if ( ! empty( $couponLabel ) ) { ?>
				<label for="mphb_coupon_code" class="mphb-coupon-code-title">
					<?php echo esc_html( $couponLabel ); ?>
				</label>
			<?php } ?>

			<?php do_action( 'mphb_sc_checkout_coupon_after_label' ); ?>

			<?php do_action( 'mphb_sc_checkout_coupon_before_input' ); ?>

			<input type="hidden" id="mphb_applied_coupon_code" name="mphb_applied_coupon_code" />
			<input type="text" id="mphb_coupon_code" name="mphb_coupon_code" />

			<?php
			/**
			 * @hooked \MPHB\Views\Shortcodes\CheckoutView::_renderCouponCodeParagraphClose() - 10
			 */
			do_action( 'mphb_sc_checkout_coupon_after_input' );
			?>

			<?php
			/**
			 * @hooked \MPHB\Views\Shortcodes\CheckoutView::_renderCouponButtonParagraphOpen() - 10
			 */
			do_action( 'mphb_sc_checkout_coupon_before_button' );
			?>

			<button class="button btn mphb-apply-coupon-code-button">
				<?php echo esc_html( $applyCouponText ); ?>
			</button>

			<?php
			/**
			 * @hooked \MPHB\Views\Shortcodes\CheckoutView::_renderCouponButtonParagraphClose() - 10
			 */
			do_action( 'mphb_sc_checkout_coupon_after_button' );
			?>

			<p class="mphb-coupon-message mphb-hide"></p>

			<?php do_action( 'mphb_sc_checkout_coupon_bottom' ); ?>

		</section>
		<?php
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $roomDetails
	 */
	public static function renderBookingDetails( $booking, $roomDetails ) {
		?>
		<section id="mphb-booking-details" class="mphb-booking-details mphb-checkout-section">
			<h3 class="mphb-booking-details-title">
				<?php esc_html_e( 'Booking Details', 'motopress-hotel-booking' ); ?>
			</h3>
			<?php do_action( 'mphb_sc_checkout_form_booking_details', $booking, $roomDetails ); ?>
		</section>
		<?php
	}

	/**
	 * @param \MPHB\Entities\ReservedRoom $reservedRoom
	 * @param int                         $roomIndex
	 * @param \MPHB\Entities\RoomType     $roomType
	 *
	 * @since 3.7.0 added parameter $reservedRoom.
	 * @since 3.7.0 parameter $roomType became third.
	 */
	public static function renderRoomTypeTitle( $reservedRoom, $roomIndex, $roomType ) {
		?>
		<h3 class="mphb-room-number">
			<?php echo esc_html( sprintf( __( 'Accommodation #%d', 'motopress-hotel-booking' ), $roomIndex + 1 ) ); ?>
		</h3>
		<p class="mphb-room-type-title">
			<span>
				<?php esc_html_e( 'Accommodation Type:', 'motopress-hotel-booking' ); ?>
			</span>
			<a href="<?php echo esc_url( $roomType->getLink() ); ?>" target="_blank">
				<?php echo esc_html( $roomType->getTitle() ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * @param \MPHB\Entities\ReservedRoom $reservedRoom
	 * @param string                      $roomIndex
	 * @param \MPHB\Entities\RoomType     $roomType
	 * @param \MPHB\Entities\Booking      $booking
	 *
	 * @since 3.7 added parameter $reservedRoom (become first).
	 * @since 3.7 parameters changed their positions: {$roomType, $roomIndex, $booking} to {$reservedRoom, $roomIndex, $roomType, $booking}.
	 * @since 3.7 added new filters: "mphb_sc_checkout_preset_adults" and "mphb_sc_checkout_preset_children".
	 * @since 3.8 added new filter - "mphb_sc_checkout_preset_guest_name".
	 */
	public static function renderGuestsChooser( $reservedRoom, $roomIndex, $roomType, $booking ) {
		$namePrefix = 'mphb_room_details[' . esc_attr( $roomIndex ) . ']';
		$idPrefix   = 'mphb_room_details-' . esc_attr( $roomIndex );

		// Value -1 means that nothing is selected ("— Select —" option active)
		$adultsCapacity = $roomType->getAdultsCapacity();
		$minAdults      = mphb_get_min_adults();
		$maxAdults      = $adultsCapacity;
		$presetAdults   = apply_filters( 'mphb_sc_checkout_preset_adults', -1, $roomType, $reservedRoom, $booking );

		$childrenCapacity = $roomType->getChildrenCapacity();
		$minChildren      = mphb_get_min_children();
		$maxChildren      = $childrenCapacity;
		$presetChildren   = apply_filters( 'mphb_sc_checkout_preset_children', -1, $roomType, $reservedRoom, $booking );

		$totalCapacity = $roomType->getTotalCapacity();

		if ( ! empty( $totalCapacity ) ) {
			$maxAdults   = max( $minAdults, min( $adultsCapacity, $totalCapacity ) );
			$maxChildren = max( $minChildren, min( $childrenCapacity, $totalCapacity ) );

			if ( $presetAdults + $presetChildren > $totalCapacity ) {
				// Someone misused the filters? Reset values
				$presetAdults   = $maxAdults;
				$presetChildren = -1;
			}
		} else {
			$totalCapacity = $roomType->calcTotalCapacity();
		}

		$childrenAllowed = $maxChildren > 0 && MPHB()->settings()->main()->isChildrenAllowed();

		$presetGuestName = apply_filters( 'mphb_sc_checkout_preset_guest_name', '', $reservedRoom, $booking );

		?>
		<?php if ( MPHB()->settings()->main()->isAdultsAllowed() ) { ?>
			<p class="mphb-adults-chooser">
				<label for="<?php echo esc_attr( $idPrefix ); ?>-adults">
					<?php
					if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
						esc_html_e( 'Adults', 'motopress-hotel-booking' );
					} else {
						esc_html_e( 'Guests', 'motopress-hotel-booking' );
					}
					?>
					<abbr title="<?php esc_html_e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
				</label>
				<select name="<?php echo esc_attr( $namePrefix ); ?>[adults]" id="<?php echo esc_attr( $idPrefix ); ?>-adults" class="mphb_sc_checkout-guests-chooser mphb_checkout-guests-chooser" required="required" data-max-allowed="<?php echo esc_attr( $adultsCapacity ); ?>" data-max-total="<?php echo esc_attr( $totalCapacity ); ?>">
					<option value=""><?php esc_html_e( '— Select —', 'motopress-hotel-booking' ); ?></option>
					<?php
					for ( $i = 1; $i <= $maxAdults; $i++ ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
						<option value="<?php echo $i; ?>" <?php selected( $i, $presetAdults ); ?>>
                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $i;
							?>
						</option>
					<?php } ?>
				</select>
			</p>
		<?php } else { ?>
			<input type="hidden" id="<?php echo esc_attr( $idPrefix ); ?>-adults" name="<?php echo esc_attr( $namePrefix ); ?>[adults]" value="<?php echo esc_attr( $minAdults ); ?>">
		<?php } ?>

		<?php if ( $childrenAllowed ) { ?>
			<p class="mphb-children-chooser">
				<label for="<?php echo esc_attr( $idPrefix ); ?>-children">
					<?php echo esc_html( sprintf( __( 'Children %s', 'motopress-hotel-booking' ), MPHB()->settings()->main()->getChildrenAgeText() ) ); ?>
					<abbr title="<?php esc_html_e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
				</label>
				<select name="<?php echo esc_attr( $namePrefix ); ?>[children]" id="<?php echo esc_attr( $idPrefix ); ?>-children" class="mphb_sc_checkout-guests-chooser mphb_checkout-guests-chooser" required="required" data-max-allowed="<?php echo esc_attr( $childrenCapacity ); ?>" data-max-total="<?php echo esc_attr( $totalCapacity ); ?>">
					<option value=""><?php esc_html_e( '— Select —', 'motopress-hotel-booking' ); ?></option>
					<?php
					for ( $i = 0; $i <= $maxChildren; $i++ ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
						<option value="<?php echo $i; ?>" <?php selected( $i, $presetChildren ); ?>>
                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $i;
							?>
						</option>
					<?php } ?>
				</select>
			</p>
		<?php } else { ?>
			<input type="hidden" id="<?php echo esc_attr( $idPrefix ); ?>-children" name="<?php echo esc_attr( $namePrefix ); ?>[children]" value="<?php echo esc_attr( $minChildren ); ?>">
		<?php } ?>

		<p class="mphb-guest-name-wrapper">
			<label for="<?php echo esc_attr( $idPrefix ); ?>-guest-name">
				<?php esc_html_e( 'Full Guest Name', 'motopress-hotel-booking' ); ?>
			</label>
			<input type="text" name="<?php echo esc_attr( $namePrefix ); ?>[guest_name]" id="<?php echo esc_attr( $idPrefix ); ?>-guest-name" value="<?php echo esc_attr( $presetGuestName ); ?>">
		</p>
		<?php
	}

	/**
	 * @param \MPHB\Entities\ReservedRoom $reservedRoom
	 * @param \MPHB\Entities\RoomType     $roomType
	 * @param int                         $roomIndex
	 * @param \MPHB\Entities\Booking      $booking
	 * @param array                       $roomDetails
	 *
	 * @since 3.7 parameters changed their positions: {$roomType, $roomIndex, $booking, $roomDetails} to {$reservedRoom, $roomIndex, $roomType, $booking, $roomDetails}.
	 * @since 3.8 added new filter - "mphb_sc_checkout_preset_rate_id".
	 */
	public static function renderRateChooser( $reservedRoom, $roomIndex, $roomType, $booking, $roomDetails ) {
		$namePrefix = 'mphb_room_details[' . esc_attr( $roomIndex ) . ']';
		$idPrefix   = 'mphb_room_details-' . esc_attr( $roomIndex );

		$allowedRates   = $roomDetails[ $roomIndex ]['allowed_rates'];
		$defaultRate    = reset( $allowedRates );
		$selectedRateId = $defaultRate->getOriginalId();
		$adults         = $roomDetails[ $roomIndex ]['adults'];
		$children       = $roomDetails[ $roomIndex ]['children'];

		if ( count( $allowedRates ) > 1 ) {
			$selectedRateId = apply_filters( 'mphb_sc_checkout_preset_rate_id', $selectedRateId, $reservedRoom, $booking, $allowedRates );

			?>
			<section class="mphb-rate-chooser mphb-checkout-item-section">
				<h4 class="mphb-room-rate-chooser-title">
					<?php esc_html_e( 'Choose Rate', 'motopress-hotel-booking' ); ?>
				</h4>

				<?php
				foreach ( $allowedRates as $rate ) {
					$rate   = apply_filters( '_mphb_translate_rate', $rate );
					$rateId = $rate->getOriginalId();

					MPHB()->reservationRequest()->setupParameters(
						array(
							'adults'         => $adults,
							'children'       => $children,
							'check_in_date'  => $booking->getCheckInDate(),
							'check_out_date' => $booking->getCheckOutDate(),
						)
					);

					$ratePrice = mphb_format_price( $rate->calcPrice( $booking->getCheckInDate(), $booking->getCheckOutDate() ) );

					$inputId   = $idPrefix . '-rate-id-' . $rateId;
					$inputName = $namePrefix . '[rate_id]';

					?>
					<p class="mphb-room-rate-variant">
						<label for="<?php echo esc_attr( $inputId ); ?>">
							<input type="radio" id="<?php echo esc_attr( $inputId ); ?>" name="<?php echo esc_attr( $inputName ); ?>" class="mphb_sc_checkout-rate mphb_checkout-rate mphb-radio-label" value="<?php echo esc_attr( $rateId ); ?>" <?php checked( $selectedRateId, $rateId ); ?>>
							<strong>
                                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo esc_html( $rate->getTitle() ) . ', ' . $ratePrice;
								?>
							</strong>
						</label>
						<br>
						<?php echo esc_html( $rate->getDescription() ); ?>
					</p>
				<?php } // For each allowed rate ?>
			</section>
		<?php } else { ?>
			<input type="hidden" name="<?php echo esc_attr( $namePrefix ); ?>[rate_id]" value="<?php echo esc_attr( $selectedRateId ); ?>">
			<?php
		}
	}

	/**
	 * @param \MPHB\Entities\ReservedRoom $reservedRoom
	 * @param int                         $roomIndex
	 * @param \MPHB\Entities\RoomType     $roomType
	 * @param \MPHB\Entities\Booking      $booking
	 *
	 * @since 3.7 parameters changed their positions: {$roomType, $roomIndex, $booking} to {$reservedRoom, $roomIndex, $roomType, $booking}.
	 * @since 3.7 added new filter - "mphb_sc_checkout_is_selected_service".
	 * @since 3.8 added new filters: "mphb_sc_checkout_preset_service_adults" and "mphb_sc_checkout_preset_service_quantity".
	 */
	public static function renderServiceChooser( $reservedRoom, $roomIndex, $roomType, $booking ) {
		if ( ! $roomType->hasServices() ) {
			return;
		}

		$services = MPHB()->getServiceRepository()->findAll(
			array(
				'post__in'         => $roomType->getServices(),
				'suppress_filters' => true,
			)
		);

		if ( empty( $services ) ) {
			return; // MB-858 - don't show "Choose Additional Services" when there are no available services
		}

		?>
		<section id="mphb-services-details-<?php echo esc_attr( $roomIndex ); ?>" class="mphb-services-details mphb-checkout-item-section">
			<h4 class="mphb-services-details-title">
				<?php esc_html_e( 'Choose Additional Services', 'motopress-hotel-booking' ); ?>
			</h4>

			<ul class="mphb_sc_checkout-services-list mphb_checkout-services-list">
				<?php
				foreach ( $services as $index => $service ) {
					$serviceId    = $service->getOriginalId();
					$presetAdults = apply_filters( 'mphb_sc_checkout_preset_service_adults', $roomType->getAdultsCapacity(), $service, $reservedRoom, $roomType );

					$namePrefix = 'mphb_room_details[' . esc_attr( $roomIndex ) . '][services][' . esc_attr( $index ) . ']';
					$namePrefix = 'mphb_room_details[' . esc_attr( $roomIndex ) . '][services][' . esc_attr( $index ) . ']';
					$idPrefix   = 'mphb_room_details-' . esc_attr( $roomIndex ) . '-service-' . esc_attr( $serviceId );

					$service    = apply_filters( '_mphb_translate_service', $service );
					$isSelected = apply_filters( 'mphb_sc_checkout_is_selected_service', false, $service, $reservedRoom, $roomType );

					?>
					<li>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<label for="<?php echo $idPrefix; ?>-id" class="mphb-checkbox-label">

							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<input type="checkbox" id="<?php echo $idPrefix; ?>-id" name="<?php echo $namePrefix; ?>[id]" class="mphb_sc_checkout-service mphb_checkout-service" value="<?php echo esc_attr( $serviceId ); ?>" <?php checked( $isSelected ); ?>>
							<?php echo esc_html( $service->getTitle() ); ?>
							<em>(<?php echo wp_kses_post( $service->getPriceWithConditions( false ) ); ?>)</em>
						</label>

						<?php
						if ( $service->isPayPerAdult() && $roomType->getAdultsCapacity() > 1 ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
							<label for="<?php echo $idPrefix; ?>-adults">
								<?php
								esc_html_e( 'for ', 'motopress-hotel-booking' );
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
								<select name="<?php echo $namePrefix; ?>[adults]" id="<?php echo $idPrefix; ?>-adults" class="mphb_sc_checkout-service-adults mphb_checkout-service-adults">
									<?php
									for ( $i = 1; $i <= $roomType->getAdultsCapacity(); $i++ ) {
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
										<option value="<?php echo $i; ?>" <?php selected( $presetAdults, $i ); ?>>
                                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo $i;
											?>
										</option>
									<?php } ?>
								</select>
								<?php echo esc_html_x( ' guest(s)', 'Example: Breakfast for X guest(s)', 'motopress-hotel-booking' ); ?>
							</label>
							<?php
						} else {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<input type="hidden" name="<?php echo $namePrefix; ?>[adults]" value="1">
						<?php } ?>

						<?php if ( $service->isFlexiblePay() ) { ?>
							<?php
								$minQuantity = $service->getMinQuantity();
								$maxQuantity = $service->getMaxQuantityNumber();

							if ( $service->isAutoLimit() ) {
								$maxQuantity = DateUtils::calcNights( $booking->getCheckInDate(), $booking->getCheckOutDate() );
							}

								$maxQuantity = max( $minQuantity, $maxQuantity );

								$presetQuantity = apply_filters( 'mphb_sc_checkout_preset_service_quantity', $minQuantity, $service, $reservedRoom, $roomType );
								$presetQuantity = mphb_limit( $presetQuantity, $minQuantity, $maxQuantity );
							?>
							&#215; <input type="number" name="<?php echo esc_attr( $namePrefix ); ?>[quantity]" class="mphb_sc_checkout-service-quantity mphb_checkout-service-quantity" value="<?php echo esc_attr( $presetQuantity ); ?>" min="<?php echo esc_attr( $minQuantity ); ?>" <?php echo ! $service->isUnlimited() ? 'max="' . esc_attr( $maxQuantity ) . '"' : ''; ?> step="1"> <?php esc_html_e( 'time(s)', 'motopress-hotel-booking' ); ?>
						<?php } // Is flexible pay? ?>
					</li>
				<?php } ?>
			</ul>
		</section>
		<?php
	}

	public static function renderPriceBreakdown( $booking ) {
		?>
		<section id="mphb-price-details" class="mphb-room-price-breakdown-wrapper mphb-checkout-section">
			<h4 class="mphb-price-breakdown-title">
				<?php esc_html_e( 'Price Breakdown', 'motopress-hotel-booking' ); ?>
			</h4>
			<?php \MPHB\Views\BookingView::renderPriceBreakdown( $booking ); ?>
		</section>
		<?php
	}

	public static function renderCheckoutText() {
		$checkoutText = MPHB()->settings()->main()->getCheckoutText();
		if ( ! empty( $checkoutText ) ) {
			?>
			<section class="mphb-checkout-text-wrapper mphb-checkout-section">
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $checkoutText;
				?>
			</section>
			<?php
		}
	}

	public static function renderTermsAndConditions() {

		$termsPageId = MPHB()->settings()->pages()->getTermsAndConditionsPageId();

		if ( ! $termsPageId ) {
			return;
		}

		$isOpenTermsInNewWindow = MPHB()->settings()->pages()->getOpenTermsAndConditionsInNewWindow();

		$termsHtml = '';

		if ( ! $isOpenTermsInNewWindow ) {

			$termsHtml = MPHB()->settings()->main()->getTermsAndConditionsText();
		}

		if ( $isOpenTermsInNewWindow || ! empty( $termsHtml ) ) {
			?>
			<section class="mphb-checkout-terms-wrapper mphb-checkout-section">

				<?php if ( ! $isOpenTermsInNewWindow ) { ?>

					<div class="mphb-terms-and-conditions">
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $termsHtml;
						?>
					</div>

				<?php } ?>

				<p class="mphb-terms-and-conditions-accept">
					<label>
						<input type="checkbox" id="mphb_accept_terms" name="mphb_accept_terms" value="1" required="required" />
						<?php
							$termsPageUrl  = get_permalink( $termsPageId );
							$termsPagelink = '<a class="mphb-terms-and-conditions-link" href="' . esc_url( $termsPageUrl ) . '" target="_blank">' . _x( 'terms & conditions', 'I\'ve read and accept the terms & conditions', 'motopress-hotel-booking' ) . '</a>';

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							printf( _x( 'I\'ve read and accept the %s', 'I\'ve read and accept the <tag>terms & conditions</tag>', 'motopress-hotel-booking' ), $termsPagelink );
						?>
						<abbr title="<?php esc_html_e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
					</label>
				</p>
			</section>
			<?php
		}
	}

	/**
	 *
	 * @since 4.2.0 - \MPHB\UsersAndRoles\Customer $customer added
	 */
	public static function renderCustomerDetails( $booking, $roomDetails, $customer = null ) {

		$firstName = '';
		$lastName  = '';
		$email     = '';

		if ( empty( $customer ) && is_user_logged_in() ) {

			$user = wp_get_current_user();

			$firstName = get_user_meta( $user->ID, 'first_name', true );
			$lastName  = get_user_meta( $user->ID, 'last_name', true );
			$email     = $user->data->user_email;

		} elseif ( ! empty( $customer ) ) {

			$firstName = $customer->getFirstName();
			$lastName  = $customer->getLastName();
			$email     = $customer->getEmail();
		}

		$requiredAttr = 'required="required"';
		$requiredAbbr = '<abbr title="' . esc_attr__( 'Required', 'motopress-hotel-booking' ) . '">*</abbr>';

		if ( is_admin() && ! MPHB()->settings()->main()->isCustomerRequiredOnAdmin() ) {
			$requiredAttr = $requiredAbbr = '';
		}
		?>
		<section id="mphb-customer-details" class="mphb-checkout-section mphb-customer-details">
			<h3 class="mphb-customer-details-title"><?php esc_html_e( 'Your Information', 'motopress-hotel-booking' ); ?></h3>
			<p class="mphb-required-fields-tip">
				<small>
					<?php printf( esc_html__( 'Required fields are followed by %s', 'motopress-hotel-booking' ), '<abbr title="required">*</abbr>' ); ?>
				</small>
			</p>
			<?php do_action( 'mphb_sc_checkout_form_customer_details' ); ?>
			<p class="mphb-customer-name">
				<label for="mphb_first_name">
					<?php esc_html_e( 'First Name', 'motopress-hotel-booking' ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $requiredAbbr;
					?>
				</label>
				<br />
				<input type="text" id="mphb_first_name" name="mphb_first_name" value="<?php echo esc_attr( $firstName ); ?>" 
																								 <?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
																									echo $requiredAttr;
																									?>
					 />
			</p>
			<p class="mphb-customer-last-name">
				<label for="mphb_last_name">
					<?php esc_html_e( 'Last Name', 'motopress-hotel-booking' ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $requiredAbbr;
					?>
				</label>
				<br />
				<input type="text" name="mphb_last_name" id="mphb_last_name" value="<?php echo esc_attr( $lastName ); ?>" 
																							   <?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
																								echo $requiredAttr;
																								?>
					 />
			</p>
			<p class="mphb-customer-email">
				<label for="mphb_email">
					<?php esc_html_e( 'Email', 'motopress-hotel-booking' ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $requiredAbbr;
					?>
				</label>
				<br />
				<input type="email" name="mphb_email" 
				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $requiredAttr;
				?>
					 id="mphb_email" value="<?php echo esc_attr( $email ); ?>" />
			</p>
			<p class="mphb-customer-phone">
				<label for="mphb_phone">
					<?php esc_html_e( 'Phone', 'motopress-hotel-booking' ); ?>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $requiredAbbr;
					?>
				</label>
				<br />
				<input type="text" name="mphb_phone" 
				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $requiredAttr;
				?>
					 id="mphb_phone" value="<?php echo $customer ? $customer->getPhone() : ''; ?>" />
			</p>
			<?php if ( MPHB()->settings()->main()->isRequireCountry() ) : ?>
				<?php $defaultCountry = MPHB()->settings()->main()->getDefaultCountry(); ?>
				<p class="mphb-customer-country">
					<label for="mphb_country">
						<?php esc_html_e( 'Country of residence', 'motopress-hotel-booking' ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAbbr;
						?>
					</label>
					<br />
					<?php $defaultCountry = $customer ? strtoupper( $customer->getCountry() ) : $defaultCountry; ?>
					<select name="mphb_country" 
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAttr;
					?>
						 id="mphb_country">
						<option value="" <?php selected( $defaultCountry, '' ); ?>></option>
						<?php foreach ( MPHB()->settings()->main()->getCountriesBundle()->getCountriesList() as $countryCode => $countryLabel ) { ?>
							<option value="<?php echo esc_attr( $countryCode ); ?>" <?php selected( $defaultCountry, $countryCode ); ?>>
								<?php echo esc_html( $countryLabel ); ?>
							</option>
						<?php } ?>
					</select>
				</p>
			<?php endif; // country ?>
			<?php if ( MPHB()->settings()->main()->isRequireFullAddress() ) : ?>
				<p class="mphb-customer-address1">
					<label for="mphb_address1">
						<?php esc_html_e( 'Address', 'motopress-hotel-booking' ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAbbr;
						?>
					</label>
					<br />
					<input type="text" name="mphb_address1" 
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAttr;
					?>
						 id="mphb_address1" value="<?php echo $customer ? $customer->getAddress1() : ''; ?>" />
				</p>
				<p class="mphb-customer-city">
					<label for="mphb_city">
						<?php esc_html_e( 'City', 'motopress-hotel-booking' ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAbbr;
						?>
					</label>
					<br />
					<input type="text" name="mphb_city" 
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAttr;
					?>
						 id="mphb_city" value="<?php echo $customer ? $customer->getCity() : ''; ?>" />
				</p>
				<p class="mphb-customer-state">
					<label for="mphb_state">
						<?php esc_html_e( 'State / County', 'motopress-hotel-booking' ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAbbr;
						?>
					</label>
					<br />
					<input type="text" name="mphb_state" 
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAttr;
					?>
						 id="mphb_state" value="<?php echo $customer ? $customer->getState() : ''; ?>" />
				</p>
				<p class="mphb-customer-zip">
					<label for="mphb_zip">
						<?php esc_html_e( 'Postcode', 'motopress-hotel-booking' ); ?>
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAbbr;
						?>
					</label>
					<br />
					<input type="text" name="mphb_zip" 
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $requiredAttr;
					?>
						 id="mphb_zip" value="<?php echo $customer ? $customer->getZip() : ''; ?>" />
				</p>
			<?php endif; // full address ?>
			<p class="mphb-customer-note">
				<label for="mphb_note"><?php esc_html_e( 'Notes', 'motopress-hotel-booking' ); ?></label><br />
				<textarea name="mphb_note" id="mphb_note" rows="4"></textarea>
			</p>

			<?php static::echoCreateCustomerAccountCheckbox(); ?>
		</section>
		<?php
	}

	public static function echoCreateCustomerAccountCheckbox() {

		$autoCreateNewAccount = MPHB()->settings()->main()->automaticallyCreateUser();
		$allowToCreateAccount = MPHB()->settings()->main()->allowCustomersCreateAccount();

		$allowToCreateAccount = $allowToCreateAccount && ! $autoCreateNewAccount && ! is_user_logged_in();

		if ( $allowToCreateAccount ) {
			?>
			<p class="mphb-customer-create-account">
				<input type="checkbox" 
				id="mphb_create_new_account"
				name="mphb_create_new_account"
				value="1" />
				<label for="mphb_create_new_account"><?php echo esc_html__( 'Create an account', 'motopress-hotel-booking' ); ?></label>
			</p>
			<?php
		}
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 */
	public static function renderBillingDetails( $booking ) {
		/**
		 * @var \MPHB\Payments\Gateways\Gateway[]
		 */
		$gateways = MPHB()->gatewayManager()->getListActive();
		?>
		<section id="mphb-billing-details" class="mphb-checkout-section">
			<h3 class="mphb-gateway-chooser-title">
				<?php esc_html_e( 'Payment Method', 'motopress-hotel-booking' ); ?>
			</h3>

			<?php if ( empty( $gateways ) ) { ?>
				<p>
					<?php esc_html_e( 'Sorry, it seems that there are no available payment methods.', 'motopress-hotel-booking' ); ?>
				</p>
			<?php } else { ?>
				<?php
				$defaultGateway = MPHB()->settings()->payment()->getDefaultGateway();

				if ( ! array_key_exists( $defaultGateway, $gateways ) ) {
					// Just get the first ID
					$defaultGateway = current( array_keys( $gateways ) );
				}

				$isMultiple = count( $gateways ) > 1;

				$beforeGateways = $isMultiple ? '<ul class="mphb-gateways-list">' : '';
				$afterGateways  = $isMultiple ? '</ul>' : '';
				$afterGateway   = $isMultiple ? '</li>' : '';

				// Display gateways
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $beforeGateways;

				foreach ( $gateways as $gateway ) {
					$gatewayId   = $gateway->getId();
					$description = $gateway->getDescription();

					if ( $isMultiple ) {
						echo '<li class="mphb-gateway mphb-gateway-' . esc_attr( $gatewayId ) . '">';
					}

					?>
					<input type="<?php echo $isMultiple ? 'radio' : 'hidden'; ?>" name="mphb_gateway_id" id="<?php echo esc_attr( "mphb_gateway_{$gatewayId}" ); ?>" value="<?php echo esc_attr( $gatewayId ); ?>" <?php checked( $isMultiple && $gatewayId == $defaultGateway ); ?> />

					<label for="<?php echo esc_attr( "mphb_gateway_{$gatewayId}" ); ?>" class="mphb-gateway-title <?php echo $isMultiple ? 'mphb-radio-label' : ''; ?>">
						<strong><?php echo esc_html( $gateway->getTitle() ); ?></strong>
					</label>

					<?php if ( ! empty( $description ) ) { ?>
						<p class="mphb-gateway-description">
							<?php echo wp_kses_post( $description ); ?>
						</p>
					<?php } ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $afterGateway;
				}
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $afterGateways;

				// Show visible payment fields of the default payment
				$hasVisibleFields = $gateways[ $defaultGateway ]->hasVisiblePaymentFields();
				$fieldsetClasses  = 'mphb-billing-fields' . ( $hasVisibleFields ? '' : ' mphb-billing-fields-hidden' );
				?>

				<fieldset class="<?php echo esc_attr( $fieldsetClasses ); ?>">
					<?php $gateways[ $defaultGateway ]->renderPaymentFields( $booking ); ?>
				</fieldset>
			<?php } // if $gateways not empty ?>
		</section>
		<?php
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public static function renderBillingDetailsHidden( $booking ) {
		$gateways = MPHB()->gatewayManager()->getListActive();
		if ( empty( $gateways ) ) {
			return;
		}
		$gateway = reset( $gateways );
		?>
		<input
			id="mphb_gateway_<?php echo esc_attr( $gateway->getId() ); ?>"
			type="hidden"
			name="mphb_gateway_id"
			value="<?php echo esc_attr( $gateway->getId() ); ?>" />
		<?php
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public static function renderTotalPrice( $booking ) {
		$deposit       = $booking->calcDepositAmount();
		$totalPrice    = $booking->getTotalPrice();
		$isShowDeposit = MPHB()->settings()->main()->getConfirmationMode() === 'payment'
			&& MPHB()->settings()->payment()->getAmountType() === 'deposit'
			&& ! mphb_is_create_booking_page()
			&& $deposit < $totalPrice; // If not in the time frame, then they both will be equal
		?>
		<p class="mphb-total-price">
			<output>
				<?php esc_html_e( 'Total Price:', 'motopress-hotel-booking' ); ?>
				<strong class="mphb-total-price-field">
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo mphb_format_price( $totalPrice );
					?>
				</strong>
				<span class="mphb-preloader mphb-hide"></span>
			</output>
		</p>
		<?php if ( $isShowDeposit ) { ?>
			<p class="mphb-deposit-amount">
				<output>
					<?php esc_html_e( 'Deposit:', 'motopress-hotel-booking' ); ?>
					<strong class="mphb-deposit-amount-field">
						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo mphb_format_price( $deposit );
						?>
					</strong>
				</output>
			</p>
		<?php } ?>
		<p class="mphb-errors-wrapper mphb-hide"></p>
		<?php
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 */
	public static function renderCheckInDate( $booking ) {
		?>
		<p class="mphb-check-in-date">
			<span><?php esc_html_e( 'Check-in:', 'motopress-hotel-booking' ); ?></span>
			<time datetime="<?php echo esc_attr( $booking->getCheckInDate()->format( 'Y-m-d' ) ); ?>">
				<strong>
					<?php echo esc_html( \MPHB\Utils\DateUtils::formatDateWPFront( $booking->getCheckInDate() ) ); ?>
				</strong>
			</time>,
			<span>
				<?php echo esc_html_x( 'from', 'from 10:00 am', 'motopress-hotel-booking' ); ?>
			</span>
			<time datetime="<?php echo esc_attr( MPHB()->settings()->dateTime()->getCheckInTime() ); ?>">
				<?php echo esc_html( MPHB()->settings()->dateTime()->getCheckInTimeWPFormatted() ); ?>
			</time>
		</p>
		<?php
	}

	/**
	 *
	 * @param Entities\Booking $booking
	 */
	public static function renderCheckOutDate( $booking ) {
		?>
		<p class="mphb-check-out-date">
			<span><?php esc_html_e( 'Check-out:', 'motopress-hotel-booking' ); ?></span>
			<time datetime="<?php echo esc_attr( $booking->getCheckOutDate()->format( 'Y-m-d' ) ); ?>">
				<strong>
					<?php echo esc_html( \MPHB\Utils\DateUtils::formatDateWPFront( $booking->getCheckOutDate() ) ); ?>
				</strong>
			</time>,
			<span>
				<?php echo esc_html_x( 'until', 'until 10:00 am', 'motopress-hotel-booking' ); ?>
			</span>
			<time datetime="<?php echo esc_attr( MPHB()->settings()->dateTime()->getCheckOutTime() ); ?>">
				<?php echo esc_html( MPHB()->settings()->dateTime()->getCheckOutTimeWPFormatted() ); ?>
			</time>
		</p>
		<?php
	}

	/**
	 * @param Entities\Booking $booking
	 * @param array            $roomDetails
	 *
	 * @since 3.7.0 added parameter $reservedRoom to action "mphb_sc_checkout_room_details".
	 * @since 3.7.0 parameter $roomType of the action "mphb_sc_checkout_room_details" became third.
	 */
	public static function renderBookingDetailsInner( $booking, $roomDetails ) {
		?>
		<div class="mphb-reserve-rooms-details">
			<?php
			foreach ( $booking->getReservedRooms() as $index => $reservedRoom ) {
				$roomTypeId = apply_filters( '_mphb_translate_post_id', $reservedRoom->getRoomTypeId() );
				$roomType   = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );
				?>
				<div class="mphb-room-details" data-index="<?php echo esc_attr( $index ); ?>">
					<input type="hidden"
						   name="mphb_room_details[<?php echo esc_attr( $index ); ?>][room_type_id]"
						   value="<?php echo esc_attr( $roomType->getOriginalId() ); ?>"
						   />

					<?php do_action( 'mphb_sc_checkout_room_details', $reservedRoom, $index, $roomType, $booking, $roomDetails ); ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $roomDetails
	 */
	public static function renderCheckoutForm( $booking, $roomDetails, $customer = null ) {
		$actionUrl   = add_query_arg( 'step', \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING, MPHB()->settings()->pages()->getCheckoutPageUrl() );
		$checkoutId  = mphb_generate_uuid4();
		$nonceAction = \MPHB\Shortcodes\CheckoutShortcode::NONCE_ACTION_BOOKING . '-' . $checkoutId;
		?>
		<form class="mphb_sc_checkout-form" enctype="<?php echo esc_attr( apply_filters( 'mphb_checkout_form_enctype_data', '' ) ); ?>" method="POST" action="<?php echo esc_url( $actionUrl ); ?>">

			<?php wp_nonce_field( $nonceAction, \MPHB\Shortcodes\CheckoutShortcode::NONCE_NAME ); ?>

			<input type="hidden"
				   name="<?php echo esc_attr( \MPHB\Shortcodes\CheckoutShortcode::BOOKING_CID_NAME ); ?>"
				   value="<?php echo esc_attr( $checkoutId ); ?>"
				   />
			<input type="hidden"
				   name="mphb_check_in_date"
				   value="<?php echo esc_attr( $booking->getCheckInDate()->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) ); ?>"
				   />
			<input type="hidden"
				   name="mphb_check_out_date"
				   value="<?php echo esc_attr( $booking->getCheckOutDate()->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) ); ?>"
				   />
			<input type="hidden"
				   name="mphb_checkout_step"
				   value="
				   <?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo \MPHB\Shortcodes\CheckoutShortcode::STEP_BOOKING;
					?>
						"
				   />

			<?php do_action( 'mphb_sc_checkout_form', $booking, $roomDetails, $customer ); ?>
			
			

			<p class="mphb_sc_checkout-submit-wrapper">
				<input type="submit" class="button" value="<?php esc_attr_e( 'Book Now', 'motopress-hotel-booking' ); ?>"/>
			</p>

		</form>
		<?php
	}

	public static function _renderCouponCodeParagraphOpen() {
		echo '<p>';
	}

	public static function _renderCouponCodeParagraphClose() {
		echo '</p>';
	}

	public static function _renderCouponButtonParagraphOpen() {
		echo '<p>';
	}

	public static function _renderCouponButtonParagraphClose() {
		echo '</p>';
	}

}
