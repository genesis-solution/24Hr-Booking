<?php

namespace MPHB\Admin\EditCPTPages;

use \MPHB\Entities;
use \MPHB\Utils\ThirdPartyPluginsUtils;

class RoomTypeEditCPTPage extends EditCPTPage {

	public function customizeMetaBoxes() {
		if ( ! MPHB()->translation()->isTranslationPage() ) {
			add_meta_box( 'rooms', __( 'Generate Accommodations', 'motopress-hotel-booking' ), array( $this, 'renderRoomMetaBox' ), $this->postType, 'normal' );
		}

		add_meta_box( 'attributes', __( 'Attributes', 'motopress-hotel-booking' ), array( $this, 'renderAttributesMetaBox' ), $this->postType, 'side' );

		if ( MPHB()->settings()->main()->showExtensionLinks() && ! ThirdPartyPluginsUtils::isActiveMphbReviews() ) {
			add_meta_box( 'reviews', __( 'Accommodation Reviews', 'motopress-hotel-booking' ), array( $this, 'renderReviewsMetaBox' ), $this->postType, 'side' );
		}
	}

	public function renderReviewsMetaBox( $post, $metabox ) {
		?>
		<p>
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( __( 'Allow guests to <a href="%s" target="_blank">submit star ratings and reviews</a> evaluating your accommodations.', 'motopress-hotel-booking' ), 'https://motopress.com/products/hotel-booking-reviews/?utm_source=customer_website&utm_medium=hb_reviews_addon' );
		?>
		</p>
		<?php
	}

	public function renderAttributesMetaBox( $post, $metabox ) {
		$allAttributes = mphb_get_attribute_names();
		$allAttributes = MPHB()->getAttributesPersistence()->getAttributes( $allAttributes );

		$roomType       = MPHB()->getRoomTypeRepository()->findById( $post->ID );
		$roomAttributes = $roomType->getAttributes();

		?>
		<div id="mphb_room_type_attributes" class="categorydiv">
			<input type="hidden" name="mphb_attributes" value="" />

			<?php foreach ( $allAttributes as $attributeName => $terms ) { ?>
				<?php $roomTerms = isset( $roomAttributes[ $attributeName ] ) ? array_keys( $roomAttributes[ $attributeName ] ) : array(); ?>
				<strong><?php echo esc_html( mphb_attribute_title( $attributeName ) ); ?></strong>
				<ul class="categorychecklist">
					<?php foreach ( $terms as $termId => $termTitle ) { ?>
						<li>
							<label class="selectit">
								<input type="checkbox" name="<?php echo esc_attr( 'mphb_attributes[' . $attributeName . '][]' ); ?>" value="<?php echo esc_attr( $termId ); ?>" <?php checked( in_array( $termId, $roomTerms ) ); ?> /> <?php echo esc_html( $termTitle ); ?>
							</label>
						</li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
		<?php
	}

	public function renderRoomMetaBox( $post, $metabox ) {
		$roomType = MPHB()->getRoomTypeRepository()->findById( $post->ID );
		?>
		<table class="form-table">
			<tbody>
				<?php if ( $this->isCurrentAddNewPage() ) { ?>
					<tr>
						<th>
							<label for="mphb_generate_rooms_count"><?php esc_html_e( 'Number of Accommodations:', 'motopress-hotel-booking' ); ?></label>
						</th>
						<td>
							<div>
								<input type="number" required="required" name="mphb_generate_rooms_count" min="0" step="1" value="1" class="small-text"/>
								<p class="description"><?php esc_html_e( 'Count of real accommodations of this type in your hotel.', 'motopress-hotel-booking' ); ?></p>
							</div>
						</td>
					</tr>
					<?php
				} else {

					$roomTypeOriginalId = $roomType->getOriginalId();

					$allRoomsLink = MPHB()->postTypes()->room()->getManagePage()->getUrl(
						array(
							'mphb_room_type_id' => $roomTypeOriginalId,
						)
					);

					$activeRoomsLink = MPHB()->postTypes()->room()->getManagePage()->getUrl(
						array(
							'mphb_room_type_id' => $roomTypeOriginalId,
							'post_status'       => 'publish',
						)
					);

					$generateRoomsLink = MPHB()->getRoomsGeneratorMenuPage()->getUrl(
						array(
							'mphb_room_type_id' => $roomTypeOriginalId,
						)
					);

					$totalRoomsCount = MPHB()->getRoomPersistence()->getCount(
						array(
							'room_type_id' => $roomTypeOriginalId,
							'post_status'  => 'all',
						)
					);

					$activeRoomsCount = MPHB()->getRoomPersistence()->getCount(
						array(
							'room_type_id' => $roomTypeOriginalId,
							'post_status'  => 'publish',
						)
					);
					?>
					<tr>
						<th>
							<label><?php esc_html_e( 'Total Accommodations:', 'motopress-hotel-booking' ); ?></label>
						</th>
						<td>
							<div>
								<span>
									<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $totalRoomsCount;
									?>
								</span>
								<span class="description">
									<a href="<?php echo esc_url( $allRoomsLink ); ?>" target="_blank">
										<?php esc_html_e( 'Show Accommodations', 'motopress-hotel-booking' ); ?>
									</a>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Active Accommodations:', 'motopress-hotel-booking' ); ?></label>
						</th>
						<td>
							<div>
								<span>
									<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $activeRoomsCount;
									?>
								</span>
								<span class="description">
									<a href="<?php echo esc_url( $activeRoomsLink ); ?>" target="_blank">
										<?php esc_html_e( 'Show Accommodations', 'motopress-hotel-booking' ); ?>
									</a>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<div>
								<a href="<?php echo esc_url( $generateRoomsLink ); ?>">
									<?php esc_html_e( 'Generate Accommodations', 'motopress-hotel-booking' ); ?>
								</a>
							</div>
						</td>
					</tr>
				<?php } ?>

			</tbody>
		</table>
		<?php
	}

	public function saveMetaBoxes( $postId, $post, $update ) {
		if ( ! parent::saveMetaBoxes( $postId, $post, $update ) ) {
			return false;
		}

		$roomsCount = ! empty( $_POST['mphb_generate_rooms_count'] ) ? absint( $_POST['mphb_generate_rooms_count'] ) : 0;
		if ( $roomsCount > 0 ) {
			$roomType = MPHB()->getRoomTypeRepository()->findById( $postId );
			if ( $roomType ) {
				MPHB()->getRoomRepository()->generateRooms( $roomType, $roomsCount );
			}
		}

		// Save attributes
		foreach ( mphb_get_attribute_names() as $attributeName ) {
			/**
			 * @var int[] Must be an array of integers, see example notes of
			 * wp_set_post_terms().
			 *
			 * @see https://codex.wordpress.org/Function_Reference/wp_set_post_terms#Examples
			 */
			$terms = array();

			if ( isset( $_POST['mphb_attributes'][ $attributeName ] ) ) {
				$terms = array_filter( array_map( 'absint', $_POST['mphb_attributes'][ $attributeName ] ) );
			}

			$taxonomyName = mphb_attribute_taxonomy_name( $attributeName );

			wp_set_post_terms( $postId, $terms, $taxonomyName );
		}

		return true;
	}

}
