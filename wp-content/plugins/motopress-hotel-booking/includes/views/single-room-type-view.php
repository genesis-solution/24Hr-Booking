<?php

namespace MPHB\Views;

class SingleRoomTypeView extends RoomTypeView {

	const TEMPLATE_CONTEXT = 'single-room-type';

	public static function renderDisabledBookingText() {

		$text = MPHB()->settings()->main()->getDisabledBookingText();

		if ( ! empty( $text ) ) {
			echo wp_kses_post( $text );
		}
	}

	public static function renderReservationForm() {

		if ( ! MPHB()->settings()->main()->isBookingDisabled() ) {
			if ( MPHB()->getRateRepository()->findAllActiveByRoomType( MPHB()->getCurrentRoomType()->getId() ) ) {
				mphb_get_template_part( static::TEMPLATE_CONTEXT . '/reservation-form' );
			}
		} else {
			self::renderDisabledBookingText();
		}
	}

	public static function renderCalendar() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/calendar' );
	}

	public static function renderGallery() {
		$roomType = MPHB()->getCurrentRoomType();
		do_action( 'mphb_render_single_room_type_gallery', $roomType );

		parent::renderGallery();
	}

	public static function renderDefaultOrForDatesPrice() {
		$searchParameters = MPHB()->searchParametersStorage()->get();

		$hasRates = false;

		if ( $searchParameters['mphb_check_in_date'] && $searchParameters['mphb_check_out_date'] ) {
			$rateAtts = array(
				'check_in_date'  => \DateTime::createFromFormat( 'Y-m-d', $searchParameters['mphb_check_in_date'] ),
				'check_out_date' => \DateTime::createFromFormat( 'Y-m-d', $searchParameters['mphb_check_out_date'] ),
			);

			if ( MPHB()->getRateRepository()->isExistsForRoomType( MPHB()->getCurrentRoomType()->getOriginalId(), $rateAtts ) ) {
				$hasRates = true;
			}
		}

		if ( $hasRates ) {
			$checkInDate  = \MPHB\Utils\DateUtils::createCheckInDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $searchParameters['mphb_check_in_date'] );
			$checkOutDate = \MPHB\Utils\DateUtils::createCheckOutDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $searchParameters['mphb_check_out_date'] );
			self::renderPriceForDates( $checkInDate, $checkOutDate );
		} else {
			self::renderPrice();
		}
	}

	public static function _renderPageWrapperStart() {

		$template = get_option( 'template' );

		switch ( $template ) {
			case 'twentyeleven':
				echo '<div id="primary"><div id="content" role="main" class="twentyeleven">';
				break;
			case 'twentytwelve':
				echo '<div id="primary" class="site-content"><div id="content" role="main" class="twentytwelve">';
				break;
			case 'twentythirteen':
				echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
				break;
			case 'twentyfourteen':
				echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwc">';
				break;
			case 'twentyfifteen':
				echo '<div id="primary" role="main" class="content-area twentyfifteen"><div id="main" class="site-main t15wc">';
				break;
			case 'twentysixteen':
				echo '<div id="primary" class="content-area twentysixteen"><main id="main" class="site-main" role="main">';
				break;
			default:
				echo '<div id="container"><div id="content" role="main">';
				break;
		}
	}

	public static function _renderPageWrapperEnd() {

		$template = get_option( 'template' );

		switch ( $template ) {
			case 'twentyeleven':
				echo '</div></div>';
				break;
			case 'twentytwelve':
				echo '</div></div>';
				break;
			case 'twentythirteen':
				echo '</div></div>';
				break;
			case 'twentyfourteen':
				echo '</div></div></div>';
				get_sidebar( 'content' );
				break;
			case 'twentyfifteen':
				echo '</div></div>';
				break;
			case 'twentysixteen':
				echo '</div></main>';
				break;
			default:
				echo '</div></div>';
				break;
		}
	}

	public static function _renderCalendarTitle() {
		echo '<h2 class="mphb-calendar-title">' . esc_html__( 'Availability', 'motopress-hotel-booking' ) . '</h2>';
	}

	public static function _renderAttributesTitle() {
		echo '<h2 class="mphb-details-title">' . esc_html__( 'Details', 'motopress-hotel-booking' ) . '</h2>';
	}

	public static function _renderAttributesListOpen() {
		echo '<ul class="mphb-single-room-type-attributes">';
	}

	public static function _renderAttributesListClose() {
		echo '</ul>';
	}

	public static function _renderCategoriesListItemOpen() {
		echo '<li class="mphb-room-type-categories">';
	}

	public static function _renderCategoriesTitle() {
		echo '<span class="mphb-attribute-title mphb-categories-title">' . esc_html__( 'Categories:', 'motopress-hotel-booking' ) . '</span>';
	}

	public static function _renderCategoriesListItemClose() {
		echo '</li>';
	}

	public static function _renderFacilitiesListItemOpen() {
		echo '<li class="mphb-room-type-facilities">';
	}

	public static function _renderFacilitiesTitle() {
		echo '<span class="mphb-attribute-title mphb-facilities-title">' . esc_html__( 'Amenities:', 'motopress-hotel-booking' ) . '</span>';
	}

	public static function _renderFacilitiesListItemClose() {
		echo '</li>';
	}

	public static function _renderCustomAttributesListItemOpen( $attributeName ) {
		echo '<li class="' . esc_attr( 'mphb-room-type-' . $attributeName . ' mphb-room-type-custom-attribute' ) . '">';
	}

	public static function _renderCustomAttributesTitle( $attributeName ) {
		echo '<span class="mphb-attribute-title ' . esc_attr( 'mphb-' . $attributeName . '-title' ) . '">' . esc_html( mphb_attribute_title( $attributeName ) ) . ':</span>';
	}

	public static function _renderCustomAttributesListItemClose() {
		echo '</li>';
	}

	/**
	 * @since 3.7.2
	 */
	public static function _renderTotalCapacityListItemOpen() {
		echo '<li class="mphb-room-type-total-capacity">';
	}

	/**
	 * @since 3.7.2
	 */
	public static function _renderTotalCapacityTitle() {
		echo '<span class="mphb-attribute-title mphb-total-capacity-title">' . esc_html__( 'Guests:', 'motopress-hotel-booking' ) . '</span>';
	}

	/**
	 * @since 3.7.2
	 */
	public static function _renderTotalCapacityListItemClose() {
		echo '</li>';
	}

	public static function _renderAdultsListItemOpen() {
		echo '<li class="mphb-room-type-adults-capacity">';
	}

	public static function _renderAdultsTitle() {
		if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
			echo '<span class="mphb-attribute-title mphb-adults-title">' . esc_html__( 'Adults:', 'motopress-hotel-booking' ) . '</span>';
		} else {
			echo '<span class="mphb-attribute-title mphb-adults-title">' . esc_html__( 'Guests:', 'motopress-hotel-booking' ) . '</span>';
		}
	}

	public static function _renderAdultsListItemClose() {
		echo '</li>';
	}

	public static function _renderChildrenListItemOpen() {
		echo '<li class="mphb-room-type-children-capacity">';
	}

	public static function _renderChildrenTitle() {
		echo '<span class="mphb-attribute-title mphb-children-title">' . esc_html__( 'Children:', 'motopress-hotel-booking' ) . '</span>';
	}

	public static function _renderChildrenListItemClose() {
		echo '</li>';
	}

	public static function _renderBedTypeListItemOpen() {
		echo '<li class="mphb-room-type-bed-type">';
	}

	public static function _renderBedTypeTitle() {
		echo '<span class="mphb-attribute-title mphb-bed-type-title">' . esc_html__( 'Bed Type:', 'motopress-hotel-booking' ) . '</span>';
	}

	public static function _renderBedTypeListItemClose() {
		echo '</li>';
	}

	public static function _renderSizeListItemOpen() {
		echo '<li class="mphb-room-type-size">';
	}

	public static function _renderSizeTitle() {
		echo '<span class="mphb-attribute-title mphb-size-title">' . esc_html__( 'Size:', 'motopress-hotel-booking' ) . '</span>';
	}

	public static function _renderSizeListItemClose() {
		echo '</li>';
	}

	public static function _renderViewListItemOpen() {
		echo '<li class="mphb-room-type-view">';
	}

	public static function _renderViewTitle() {
		echo '<span class="mphb-attribute-title mphb-view-title">' . esc_html__( 'View:', 'motopress-hotel-booking' ) . '</span>';
	}

	public static function _renderViewListItemClose() {
		echo '</li>';
	}

	public static function _renderFeaturedImageParagraphOpen() {
		echo '<p class="post-thumbnail mphb-single-room-type-post-thumbnail">';
	}

	public static function _renderFeaturedImageParagraphClose() {
		echo '</p>';
	}

	public static function _renderPriceParagraphOpen() {
		echo '<p class="mphb-regular-price">';
	}

	public static function _renderPriceTitle() {
		echo '<strong>' . esc_html__( 'Prices start at:', 'motopress-hotel-booking' ) . '</strong>';
	}

	public static function _renderPriceParagraphClose() {
		echo '</p>';
	}

	public static function _renderTitleHeadingOpen() {
		echo '<h1 class="mphb-room-type-title entry-title">';
	}

	public static function _renderTitleHeadingClose() {
		echo '</h1>';
	}

	public static function _renderReservationFormTitle() {
		echo '<h2 class="mphb-reservation-form-title">' . esc_html__( 'Reservation Form', 'motopress-hotel-booking' ) . '</h2>';
	}

	public static function _renderMetas() {
		if ( ! post_password_required() ) {
			/**
			 * @hooked \MPHB\Views\SingleRoomTypeView::renderGallery                - 10
			 * @hooked \MPHB\Views\SingleRoomTypeView::renderAttributes             - 20
			 * @hooked \MPHB\Views\SingleRoomTypeView::renderDefaultOrForDatesPrice - 30
			 * @hooked \MPHB\Views\SingleRoomTypeView::renderCalendar               - 40
			 * @hooked \MPHB\Views\SingleRoomTypeView::renderReservationForm        - 50
			 */
			do_action( 'mphb_render_single_room_type_metas' );
		}
	}

	public static function _enqueueGalleryScripts() {

		if ( MPHB()->settings()->main()->isUseSingleRoomTypeGalleryMagnific() ) {

			wp_enqueue_script( 'mphb-fancybox' );
			wp_enqueue_style( 'mphb-fancybox-css' );
			?>
			<script type="text/javascript">
				document.addEventListener( "DOMContentLoaded", function( event ) {
					(function( $ ) {
						$( function() {
							var galleryItems = $( ".mphb-single-room-type-gallery-wrapper .gallery-icon>a" );
							if ( galleryItems.length && $.fancybox ) {
								galleryItems.fancybox( {
									selector : '.mphb-single-room-type-gallery-wrapper .gallery-icon>a',
									loop: true,
								} );
							}
						} );
					})( jQuery );
				} );
			</script>
			<?php

		}
	}

	public static function _renderAttributesListItemValueHolderOpen() {
		echo '<span class="mphb-attribute-value">';
	}

	public static function _renderAttributesListItemValueHolderClose() {
		echo '</span>';
	}

}
