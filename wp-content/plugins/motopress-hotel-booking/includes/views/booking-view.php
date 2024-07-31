<?php

namespace MPHB\Views;

use \MPHB\Entities;

class BookingView {

	public static function renderPriceBreakdown( Entities\Booking $booking ) {
		MPHB()->reservationRequest()->setupParameter( 'pricing_strategy', 'base-price' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::generatePriceBreakdown( $booking );
		MPHB()->reservationRequest()->resetDefaults( array( 'pricing_strategy' ) );
	}

	public static function generatePriceBreakdown( Entities\Booking $booking ) {
		$priceBreakdown = $booking->getPriceBreakdown();
		return self::generatePriceBreakdownArray( $priceBreakdown );
	}

	/**
	 * @param array $priceBreakdown
	 * @param array $atts Optional.
	 *
	 * @return string
	 *
	 * @since 3.6.1 added optional parameter $atts.
	 */
	public static function generatePriceBreakdownArray( $priceBreakdown, $atts = array() ) {
		$atts = array_merge(
			array(
				'title_expandable' => true,
				'coupon_removable' => true,
				'force_unfold'     => false,
			),
			$atts
		);

		ob_start();

		if ( ! empty( $priceBreakdown ) ) :

			$hasServices = false !== array_search(
				true,
				array_map(
					function( $roomBreakdown ) {
						return isset( $roomBreakdown['services'] ) && ! empty( $roomBreakdown['services']['list'] );
					},
					$priceBreakdown['rooms']
				)
			);

			$useThreeColumns = $hasServices;

			$unfoldByDefault = MPHB()->settings()->main()->isPriceBreakdownUnfoldedByDefault();
			if ( $atts['force_unfold'] ) {
				$unfoldByDefault = true;
			} elseif ( is_admin() && ! MPHB()->isAjax() ) {
				$unfoldByDefault = false;
			}
			$foldedClass   = $unfoldByDefault ? '' : 'mphb-hide';
			$unfoldedClass = $unfoldByDefault ? 'mphb-hide' : '';
			?>
			<table class="mphb-price-breakdown" cellspacing="0">
				<tbody>
					<?php
						$accommodationTaxesTotal = 0;
						$serviceTaxesTotal       = 0;
						$feeTaxesTotal           = 0;
					foreach ( $priceBreakdown['rooms'] as $key => $roomBreakdown ) :
						?>
						<?php

						if ( isset( $roomBreakdown['room'] ) ) :
							?>
							<tr class="mphb-price-breakdown-booking mphb-price-breakdown-group">
								<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
									<?php
									$title = sprintf( _x( '#%d %s', 'Accommodation type in price breakdown table. Example: #1 Double Room', 'motopress-hotel-booking' ), $key + 1, $roomBreakdown['room']['type'] );

									if ( $atts['title_expandable'] ) {
										$title = '<a href="#" class="mphb-price-breakdown-accommodation mphb-price-breakdown-expand" title="' . __( 'Expand', 'motopress-hotel-booking' ) . '">'
											. '<span class="mphb-inner-icon ' . esc_attr( $unfoldedClass ) . '">&plus;</span>'
											. '<span class="mphb-inner-icon ' . esc_attr( $foldedClass ) . '">&minus;</span>'
											. $title
											. '</a>';
									}
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $title;
									?>
									<div class="mphb-price-breakdown-rate <?php echo esc_attr( $foldedClass ); ?>"><?php echo esc_html( sprintf( __( 'Rate: %s', 'motopress-hotel-booking' ), $roomBreakdown['room']['rate'] ) ); ?></div>
								</td>
								<td class="mphb-table-price-column">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo mphb_format_price( $roomBreakdown['total'] );
								?>
									</td>
							</tr>
							<?php if ( MPHB()->settings()->main()->isAdultsAllowed() ) { ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-" . ( MPHB()->settings()->main()->isChildrenAllowed() ? 'adults' : 'guests' ) ); ?>">
									<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
															<?php
															if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
																esc_html_e( 'Adults', 'motopress-hotel-booking' );
															} else {
																esc_html_e( 'Guests', 'motopress-hotel-booking' );
															}
															?>
									</td>
									<td><?php echo esc_html( $roomBreakdown['room']['adults'] ); ?></td>
								</tr>
							<?php } ?>
							<?php if ( $roomBreakdown['room']['children_capacity'] > 0 && MPHB()->settings()->main()->isChildrenAllowed() ) { ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-children" ); ?>">
									<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Children', 'motopress-hotel-booking' ); ?></td>
									<td><?php echo esc_html( $roomBreakdown['room']['children'] ); ?></td>
								</tr>
							<?php } ?>
							<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-nights" ); ?>">
								<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Nights', 'motopress-hotel-booking' ); ?></td>
								<td><?php echo count( $roomBreakdown['room']['list'] ); ?></td>
							</tr>
							<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-dates" ); ?>">
								<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Dates', 'motopress-hotel-booking' ); ?></th>
								<th class="mphb-table-price-column"><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
							</tr>
							<?php foreach ( $roomBreakdown['room']['list'] as $date => $datePrice ) : ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-date" ); ?>">
									<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php echo esc_html( \MPHB\Utils\DateUtils::formatDateWPFront( \DateTime::createFromFormat( 'Y-m-d', $date ) ) ); ?></td>
									<td class="mphb-table-price-column">
									<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo mphb_format_price( $datePrice );
									?>
										</td>
								</tr>
							<?php endforeach; ?>
							<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-dates-subtotal" ); ?>">
								<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Dates Subtotal', 'motopress-hotel-booking' ); ?></th>
								<th class="mphb-table-price-column">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo mphb_format_price( $roomBreakdown['room']['total'] );
								?>
									</th>
							</tr>
							<?php if ( $roomBreakdown['room']['discount'] > 0 ) { ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-accommodation-discount" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Discount', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-table-price-column">
									<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo mphb_format_price( -$roomBreakdown['room']['discount'] );
									?>
										</th>
								</tr>
							<?php } ?>
							<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-accommodation-subtotal" ); ?>">
								<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Accommodation Subtotal', 'motopress-hotel-booking' ); ?></th>
								<th class="mphb-table-price-column">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo mphb_format_price( $roomBreakdown['room']['discount_total'] );
								?>
									</th>
							</tr>

							<?php if ( isset( $roomBreakdown['taxes']['room'] ) && ! empty( $roomBreakdown['taxes']['room']['list'] ) ) { ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-accommodation-taxes" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Accommodation Taxes', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-table-price-column"><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
								</tr>
								<?php foreach ( $roomBreakdown['taxes']['room']['list'] as $roomTax ) { ?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-accommodation-tax" ); ?>">
										<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php echo esc_html( $roomTax['label'] ); ?></td>
										<td class="mphb-table-price-column">
										<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo mphb_format_price( $roomTax['price'] );
										?>
											</td>
									</tr>
								<?php } ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-accommodation-taxes-subtotal" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Accommodation Taxes Subtotal', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-table-price-column">
									<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo mphb_format_price( $roomBreakdown['taxes']['room']['total'] );
									?>
										</th>
								</tr>
								<?php
								$accommodationTaxesTotal += $roomBreakdown['taxes']['room']['total'];
							}
							?>

							<?php if ( isset( $roomBreakdown['services'] ) && ! empty( $roomBreakdown['services']['list'] ) ) : ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-services" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 3 : 2 ); ?>">
										<?php esc_html_e( 'Services', 'motopress-hotel-booking' ); ?>
									</th>
								</tr>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-services-headers" ); ?>">
									<th class="mphb-price-breakdown-service-name"><?php esc_html_e( 'Service', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-price-breakdown-service-details"><?php esc_html_e( 'Details', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-table-price-column"><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
								</tr>
								<?php foreach ( $roomBreakdown['services']['list'] as $serviceDetails ) : ?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-service" ); ?>">
										<td class="mphb-price-breakdown-service-name"><?php echo esc_html( $serviceDetails['title'] ); ?></td>
										<td class="mphb-price-breakdown-service-details"><?php echo wp_kses_post( $serviceDetails['details'] ); ?></td>
										<td class="mphb-table-price-column">
										<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo mphb_format_price( $serviceDetails['total'] );
										?>
											</td>
									</tr>
								<?php endforeach; ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-services-subtotal" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
										<?php esc_html_e( 'Services Subtotal', 'motopress-hotel-booking' ); ?>
									</th>
									<th class="mphb-table-price-column">
										<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo mphb_format_price( $roomBreakdown['services']['total'] );
										?>
									</th>
								</tr>

								<?php if ( isset( $roomBreakdown['taxes']['services'] ) && ! empty( $roomBreakdown['taxes']['services']['list'] ) ) { ?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-service-taxes" ); ?>">
										<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Service Taxes', 'motopress-hotel-booking' ); ?></th>
										<th class="mphb-table-price-column"><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
									</tr>
									<?php foreach ( $roomBreakdown['taxes']['services']['list'] as $serviceTax ) { ?>
										<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-service-tax" ); ?>">
											<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php echo esc_html( $serviceTax['label'] ); ?></td>
											<td class="mphb-table-price-column">
											<?php
												// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												echo mphb_format_price( $serviceTax['price'] );
											?>
												</td>
										</tr>
									<?php } ?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-service-taxes-subtotal" ); ?>">
										<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Service Taxes Subtotal', 'motopress-hotel-booking' ); ?></th>
										<th class="mphb-table-price-column">
										<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo mphb_format_price( $roomBreakdown['taxes']['services']['total'] );
										?>
											</th>
									</tr>
									<?php
								}
								$serviceTaxesTotal += $roomBreakdown['taxes']['services']['total'];
								?>
							<?php endif; ?>

							<?php if ( isset( $roomBreakdown['fees'] ) && ! empty( $roomBreakdown['fees']['list'] ) ) { ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-fees" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Fees', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-table-price-column"><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
								</tr>
								<?php foreach ( $roomBreakdown['fees']['list'] as $fee ) { ?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-fee" ); ?>">
										<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php echo esc_html( $fee['label'] ); ?></td>
										<td class="mphb-table-price-column">
										<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo mphb_format_price( $fee['price'] );
										?>
											</td>
									</tr>
								<?php } ?>
								<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-fees-subtotal" ); ?>">
									<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Fees Subtotal', 'motopress-hotel-booking' ); ?></th>
									<th class="mphb-table-price-column">
									<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										echo mphb_format_price( $roomBreakdown['fees']['total'] );
									?>
										</th>
								</tr>

								<?php
								if ( isset( $roomBreakdown['taxes']['fees'] ) && ! empty( $roomBreakdown['taxes']['fees']['list'] ) ) {
									?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-fee-taxes" ); ?>">
										<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Fee Taxes', 'motopress-hotel-booking' ); ?></th>
										<th class="mphb-table-price-column"><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
									</tr>
									<?php foreach ( $roomBreakdown['taxes']['fees']['list'] as $feeTax ) { ?>
										<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-fee-tax" ); ?>">
											<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php echo esc_html( $feeTax['label'] ); ?></td>
											<td class="mphb-table-price-column">
											<?php
												// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												echo mphb_format_price( $feeTax['price'] );
											?>
												</td>
										</tr>
									<?php } ?>
									<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-fee-taxes-subtotal" ); ?>">
										<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Fee Taxes Subtotal', 'motopress-hotel-booking' ); ?></th>
										<th class="mphb-table-price-column">
										<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo mphb_format_price( $roomBreakdown['taxes']['fees']['total'] );
										?>
											</th>
									</tr>
									<?php
								}
								$feeTaxesTotal += $roomBreakdown['taxes']['fees']['total'];
								?>
							<?php } ?>

						<?php endif; ?>
						<tr class="<?php echo esc_attr( "{$foldedClass} mphb-price-breakdown-subtotal" ); ?>">
							<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php esc_html_e( 'Subtotal', 'motopress-hotel-booking' ); ?></th>
							<th class="mphb-table-price-column">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo mphb_format_price( $roomBreakdown['discount_total'] );
							?>
								</th>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<?php if ( ! empty( $priceBreakdown['coupon'] ) ) : ?>
						<tr class="mphb-price-breakdown-coupon">
							<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>"><?php echo esc_html( sprintf( __( 'Coupon: %s', 'motopress-hotel-booking' ), $priceBreakdown['coupon']['code'] ) ); ?></th>
							<td class="mphb-table-price-column">
								<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo mphb_format_price( -1 * $priceBreakdown['coupon']['discount'] );
								?>

								<?php if ( $atts['coupon_removable'] ) { ?>
									<a href="#" class="mphb-remove-coupon"><?php esc_html_e( 'Remove', 'motopress-hotel-booking' ); ?></a>
								<?php } ?>
							</td>
						</tr>
						<?php
					endif;
					$taxesBreakdown = '';
					ob_start();
					if ( $accommodationTaxesTotal > 0 || $feeTaxesTotal > 0 || $serviceTaxesTotal ) :
						?>
					<tr class="mphb-price-breakdown-subtotal">
						<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
							<?php esc_html_e( 'Subtotal (excl. taxes)', 'motopress-hotel-booking' ); ?>
						</td>
						<td>
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo mphb_format_price( $priceBreakdown['total'] - $accommodationTaxesTotal - $feeTaxesTotal - $serviceTaxesTotal );
							?>
						</td>
					</tr>
					<tr class="mphb-tax-info-total">
						<td colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
							<?php esc_html_e( 'Taxes', 'motopress-hotel-booking' ); ?>
						</td>
						<td>
							<?php
							$allTaxes = $accommodationTaxesTotal + $feeTaxesTotal + $serviceTaxesTotal;
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo mphb_format_price( $allTaxes );
							?>
						</td>
					</tr>
						<?php
					endif;
					$taxesBreakdown = ob_get_contents();
					ob_end_clean();

					/**
					 * @since 3.9.8
					 *
					 * @param string $taxesBreakdown
					 * @param array $priceBreakdown
					 * @param float $accommodationTaxesTotal
					 * @param float $feeTaxesTotal
					 * @param float $serviceTaxesTotal
					 */
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo apply_filters( 'mphb_get_taxes_breakdown', $taxesBreakdown, $priceBreakdown, $accommodationTaxesTotal, $feeTaxesTotal, $serviceTaxesTotal );
					?>
					<tr class="mphb-price-breakdown-total">
						<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
							<?php esc_html_e( 'Total', 'motopress-hotel-booking' ); ?>
						</th>
						<th class="mphb-table-price-column">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo mphb_format_price( $priceBreakdown['total'] );
							?>
						</th>
					</tr>
					<?php if ( ! empty( $priceBreakdown['deposit'] ) ) : ?>
						<tr class="mphb-price-breakdown-deposit">
							<th colspan="<?php echo ( $useThreeColumns ? 2 : 1 ); ?>">
								<?php esc_html_e( 'Deposit', 'motopress-hotel-booking' ); ?>
							</th>
							<th class="mphb-table-price-column">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo mphb_format_price( $priceBreakdown['deposit'] );
								?>
							</th>
						</tr>
					<?php endif; ?>
				</tfoot>
			</table>
			<?php
		endif;
		return ob_get_clean();
	}

	public static function renderCheckInDateWPFormatted( Entities\Booking $booking ) {
		echo esc_html( date_i18n( MPHB()->settings()->dateTime()->getDateFormatWP(), $booking->getCheckInDate()->getTimestamp() ) );
	}

	public static function renderCheckOutDateWPFormatted( Entities\Booking $booking ) {
		echo esc_html( date_i18n( MPHB()->settings()->dateTime()->getDateFormatWP(), $booking->getCheckOutDate()->getTimestamp() ) );
	}

	public static function renderTotalPriceHTML( Entities\Booking $booking ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo mphb_format_price( $booking->getTotalPrice() );
	}

}
