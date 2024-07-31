<?php

use MPHB\PostTypes\PaymentCPT\Statuses as PaymentStatuses;

/**
 * Display the room type default (average minimal) price for min days stay
 *
 * @param int $id Optional. Current room type by default.
 */
function mphb_tmpl_the_room_type_default_price( $id = null ) {
	$roomType = $id ? MPHB()->getRoomTypeRepository()->findById( $id ) : MPHB()->getCurrentRoomType();
	$nights   = MPHB()->getRulesChecker()->reservationRules()->getMinDaysAllSeason( $roomType->getOriginalId() );
	$price    = mphb_get_room_type_base_price( $roomType );

	$defaultPriceForNights = $price * $nights;

	$taxesAndFees = $roomType->getTaxesAndFees();
	$taxesAndFees->setRoomPrice( $defaultPriceForNights );
	$taxesAndFees->setupParams(
		array(
			'period_nights' => $nights,
			'defined'       => false,
		)
	);

	$title = __( 'Choose dates to see relevant prices', 'motopress-hotel-booking' );

	$priceFortmatAtts = array(
		'period'        => true,
		'period_nights' => $nights,
		'period_title'  => $title,
	);

	/**
	 * @since 3.9.8
	 *
	 * @param float $defaultPriceForNights
	 * @param \MPHB\TaxesAndFees\TaxesAndFees
	 * @param array $priceFortmatAtts
	 * @param float $defaultPriceForNights
	 */
	$formattedPrice = apply_filters(
		'mphb_tmpl_the_room_type_price_for_dates',
		mphb_format_price( $defaultPriceForNights, $priceFortmatAtts ),
		$taxesAndFees,
		$priceFortmatAtts,
		$defaultPriceForNights
	);
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $formattedPrice;
}

/**
 *
 * @param int $id Optional. Current room type by default.
 * @return bool
 */
function mphb_tmpl_has_room_type_default_price( $id = null ) {
	$roomType = $id ? MPHB()->getRoomTypeRepository()->findById( $id ) : MPHB()->getCurrentRoomType();
	return mphb_get_room_type_base_price( $roomType ) > 0;
}

/**
 * Display the room type minimal price for dates
 *
 * @param \DateTime $checkInDate
 * @param \DateTime $checkOutDate
 * @return string
 */
function mphb_tmpl_the_room_type_price_for_dates( \DateTime $checkInDate, \DateTime $checkOutDate ) {
	$roomType = MPHB()->getCurrentRoomType();
	$price    = mphb_get_room_type_period_price( $checkInDate, $checkOutDate );
	$nights   = \MPHB\Utils\DateUtils::calcNights( $checkInDate, $checkOutDate );

	$taxesAndFees = $roomType->getTaxesAndFees();
	$taxesAndFees->setRoomPrice( $price );
	$taxesAndFees->setupParams(
		array(
			'period_nights' => $nights,
		)
	);

	$title = __( 'Based on your search parameters', 'motopress-hotel-booking' );

	$priceFortmatAtts = array(
		'period'        => true,
		'period_nights' => $nights,
		'period_title'  => $title,
	);

	/** This filter is documented in template-functions.php */
	$formattedPrice = apply_filters(
		'mphb_tmpl_the_room_type_price_for_dates',
		mphb_format_price( $price, $priceFortmatAtts ),
		$taxesAndFees,
		$priceFortmatAtts,
		$price
	);
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $formattedPrice;
}

/**
 * Retrieve dayname for key
 *
 * @param string|int $key number from 0 to 6
 * @return string
 */
function mphb_tmpl_get_day_by_key( $key ) {
	return \MPHB\Utils\DateUtils::getDayByKey( $key );
}

/**
 * @return int
 *
 * @since 3.7.2
 */
function mphb_tmpl_get_room_type_total_capacity() {
	 return MPHB()->getCurrentRoomType()->getTotalCapacity();
}

/**
 * Retrieve the room type adults capacity
 *
 * @return int
 */
function mphb_tmpl_get_room_type_adults_capacity() {
	return MPHB()->getCurrentRoomType()->getAdultsCapacity();
}

/**
 * Retrieve the room type children capacity
 *
 * @return int
 */
function mphb_tmpl_get_room_type_children_capacity() {
	return MPHB()->getCurrentRoomType()->getChildrenCapacity();
}

/**
 * Retrieve the room type bed type
 *
 * @return string
 */
function mphb_tmpl_get_room_type_bed_type() {
	return MPHB()->getCurrentRoomType()->getBedType();
}

/**
 * Retrieve the room type facilities
 *
 * @return array
 */
function mphb_tmpl_get_room_type_facilities() {
	return MPHB()->getCurrentRoomType()->getFacilities();
}

/**
 * Retrieve the room type attributes
 *
 * @return array [%Attribute name% => [%ID% => %Term title%]]
 */
function mphb_tmpl_get_room_type_attributes() {
	return MPHB()->getCurrentRoomType()->getAttributes();
}

/**
 * Retrieve the room type size
 *
 * @param $withUnits Optional. Add units to size. TRUE by default.
 * @return string
 *
 * @since 3.6.1 added optional parameter $withUnits.
 */
function mphb_tmpl_get_room_type_size( $withUnits = true ) {
	return MPHB()->getCurrentRoomType()->getSize( $withUnits );
}

/**
 * Retrieve the room type categories
 *
 * @return array
 */
function mphb_tmpl_get_room_type_categories() {
	return MPHB()->getCurrentRoomType()->getCategories();
}

/**
 * Retrieve the room type view
 *
 * @return string
 */
function mphb_tmpl_get_room_type_view() {
	return MPHB()->getCurrentRoomType()->getView();
}

/**
 * Check is current room type has gallery.
 *
 * @return bool
 */
function mphb_tmpl_has_room_type_gallery() {
	return MPHB()->getCurrentRoomType()->hasGallery();
}

/**
 *
 * @param bool $withFeaturedImage
 * @return array
 */
function mphb_tmpl_get_room_type_gallery_ids( $withFeaturedImage = false ) {
	$roomType   = MPHB()->getCurrentRoomType();
	$galleryIds = $roomType->getGalleryIds();

	if ( $withFeaturedImage && $roomType->hasFeaturedImage() ) {
		array_unshift( $galleryIds, $roomType->getFeaturedImageId() );
	}

	return $galleryIds;
}

/**
 *
 * @param array $atts @see gallery_shortcode
 */
function mphb_tmpl_the_room_type_galery( $atts = array() ) {

	$defaultAtts = array(
		'ids' => join( ',', mphb_tmpl_get_room_type_gallery_ids() ),
	);

	$atts = array_merge( $defaultAtts, $atts );

	if ( isset( $atts['link'] ) && $atts['link'] === 'post' ) {
		$forceLinkToPost = true;
		$atts['link']    = '';
	} else {
		$forceLinkToPost = false;
	}

	$wrapperClass = 'mphb-room-type-gallery-wrapper';
	if ( isset( $atts['mphb_wrapper_class'] ) ) {
		$wrapperClass .= ' ' . $atts['mphb_wrapper_class'];
		unset( $atts['mphb_wrapper_class'] );
	}

	$galleryContent = gallery_shortcode( $atts );

	if ( $forceLinkToPost ) {
		$linkToAttachmentRegExp = join(
			'|',
			array_map(
				function( $id ) {
					return preg_quote( get_the_permalink( $id ), '/' );
				},
				explode( ',', $atts['ids'] )
			)
		);
		$linkToPost             = get_the_permalink();

		if ( ! empty( $linkToAttachmentRegExp ) ) {
			$galleryContent = preg_replace(
				'/href=["|\'](' . $linkToAttachmentRegExp . ')["|\']/',
				'href="' . esc_url( $linkToPost ) . '"',
				$galleryContent
			);
		}
	}

	$result  = sprintf( '<div class="%s">', esc_attr( $wrapperClass ) );
	$result .= $galleryContent;
	$result .= '</div>';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $result;
}

function mphb_tmpl_the_single_room_type_gallery() {

	$galleryAtts = array(
		'link'               => apply_filters( 'mphb_single_room_type_gallery_image_link', 'file' ),
		'columns'            => apply_filters( 'mphb_single_room_type_gallery_columns', '4' ),
		'size'               => apply_filters( 'mphb_single_room_type_gallery_image_size', 'thumbnail' ),
		'mphb_wrapper_class' => apply_filters( 'mphb_single_room_type_gallery_wrapper_class', 'mphb-single-room-type-gallery-wrapper' ),
	);

	mphb_tmpl_the_room_type_galery( $galleryAtts );
}

function mphb_tmpl_the_room_type_flexslider_gallery() {

	$uniqid = uniqid();

	$galleryIds = mphb_tmpl_get_room_type_gallery_ids();

	$sliderUniqueClass    = 'mphb-gallery-main-slider-' . $uniqid;
	$navSliderUniqueClass = 'mphb-gallery-thumbnail-slider-' . $uniqid;

	$mainGalleryAtts = array(
		'link'               => apply_filters( 'mphb_loop_room_type_gallery_main_slider_image_link', 'post' ),
		'columns'            => apply_filters( 'mphb_loop_room_type_gallery_main_slider_columns', '1' ),
		'size'               => apply_filters( 'mphb_loop_room_type_gallery_main_slider_image_size', 'large' ),
		'mphb_wrapper_class' => apply_filters( 'mphb_loop_room_type_gallery_main_slider_wrapper_class', 'mphb-gallery-main-slider mphb-flexslider-gallery-wrapper mphb-room-type-gallery-wrapper ' . $sliderUniqueClass ),
		'group_id'           => $uniqid,
	);

	$mainGalleryFlexsliderOptions = array(
		'animation'     => 'slide',
		'controlNav'    => false,
		'animationLoop' => true,
		'smoothHeight'  => true,
		'slideshow'     => false,
	);

	$mainGalleryFlexsliderOptions = apply_filters( 'mphb_loop_room_type_gallery_main_slider_flexslider_options', $mainGalleryFlexsliderOptions );

	do_action( 'mphb_loop_room_type_gallery_main_slider_flexslider_before' );

	mphb_the_flexslider_gallery( $galleryIds, $mainGalleryAtts, $mainGalleryFlexsliderOptions );

	do_action( 'mphb_loop_room_type_gallery_main_slider_flexslider_after' );

	if ( apply_filters( 'mphb_loop_room_type_gallery_use_nav_slider', true ) ) {

		$navGalleryAtts = array(
			'link'               => apply_filters( 'mphb_loop_room_type_gallery_nav_slider_image_size', 'large' ),
			'columns'            => apply_filters( 'mphb_loop_room_type_gallery_nav_slider_columns', '4' ),
			'size'               => apply_filters( 'mphb_loop_room_type_gallery_nav_slider_image_size', 'thumbnail' ),
			'mphb_wrapper_class' => apply_filters( 'mphb_loop_room_type_gallery_main_slider_wrapper_class', 'mphb-gallery-thumbnail-slider mphb-flexslider-gallery-wrapper mphb-room-type-gallery-wrapper ' . $navSliderUniqueClass ),
			'group_id'           => $uniqid,
		);

		$navGalleryFlexsliderOptions = array(
			'animation'     => 'slide',
			'controlNav'    => false,
			'animationLoop' => true,
			'slideshow'     => false,
			'itemMargin'    => 5,
		);

		$navGalleryFlexsliderOptions = apply_filters( 'mphb_loop_room_type_gallery_nav_slider_flexslider_options', $navGalleryFlexsliderOptions );

		do_action( 'mphb_loop_room_type_gallery_nav_slider_flexslider_before' );

		mphb_the_flexslider_gallery( $galleryIds, $navGalleryAtts, $navGalleryFlexsliderOptions );

		do_action( 'mphb_loop_room_type_gallery_nav_slider_flexslider_after' );
	}

	MPHB()->getPublicScriptManager()->enqueue();
}

/**
 *
 * @param int[]  $ids Id of attachments
 * @param array  $atts
 * @param string $atts['order'] Default 'ASC';
 * @param string $atts['orderby'] Default 'post__in';
 * @param string $atts['columns'] Default 3;
 * @param string $atts['include'] Default 'thumbnail';
 * @param string $atts['exclude']
 * @param string $atts['link'] Optional. Possible values 'post', 'file', 'none', ''. Default '';
 * @param array  $flexsliderAtts
 */
function mphb_the_flexslider_gallery( $ids, $atts, $flexsliderAtts = array() ) {

	static $instance = 0;
	$instance++;

	$defaultAtts = array(
		'order'              => 'ASC',
		'orderby'            => 'post__in',
		'columns'            => 3,
		'size'               => 'thumbnail',
		'exclude'            => '',
		'link'               => '',
		'mphb_wrapper_class' => '',
		'group_id'           => '',
	);

	$atts = array_merge( $defaultAtts, $atts );

	$atts['include'] = $ids;

	if ( empty( $ids ) ) {
		return '';
	}

	$flexsliderDefaultAtts = array();
	$flexsliderAtts        = array_merge( $flexsliderDefaultAtts, $flexsliderAtts );

	$attachmentsArgs = array(
		'include'        => $ids,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'post__in',
	);

	$attachments = array();
	foreach ( get_posts( $attachmentsArgs ) as $key => $val ) {
		$attachments[ $val->ID ] = $val;
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	$columns   = intval( $atts['columns'] );
	$itemwidth = $columns > 0 ? floor( 100 / $columns ) : 100;
	$float     = is_rtl() ? 'right' : 'left';

	$selector = "mphb-flexslider-gallery-{$instance}";

	$sizeClass = sanitize_html_class( $atts['size'] );

	$flexsliderAttsData = json_encode( $flexsliderAtts );

	$dataAtts = "data-flexslider-atts='{$flexsliderAttsData}'";
	if ( ! empty( $atts['group_id'] ) ) {
		$dataAtts .= " data-group='{$atts['group_id']}'";
	}

	$output = "<div id='$selector' class='gallery-columns-{$columns} gallery-size-{$sizeClass} {$atts['mphb_wrapper_class']}' {$dataAtts}>";

	$i       = 0;
	$output .= '<ul class="slides">';
	foreach ( $attachments as $id => $attachment ) {

		$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : array();
		// $attr['loading'] = 'eager';

		/**
		 * Disable lazy loading for gallery images
		 *
		 * @see https://developer.jetpack.com/hooks/lazyload_is_enabled/
		 */
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'lazy-images' ) ) {
			$attr['class'] = "attachment-{$sizeClass} size-{$sizeClass} skip-lazy";
		}

		if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
			$imageOutput = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
		} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
			$imageOutput = wp_get_attachment_image( $id, $atts['size'], false, $attr );
		} elseif ( ! empty( $atts['link'] ) && 'post' === $atts['link'] ) {
			$imageOutput  = '<a href="' . esc_url( get_the_permalink() ) . '">';
			$imageOutput .= wp_get_attachment_image( $id, $atts['size'], false, $attr );
			$imageOutput .= '</a>';
		} else {
			$imageOutput = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
		}
		$imageMeta = wp_get_attachment_metadata( $id );

		$orientation = '';
		if ( isset( $imageMeta['height'], $imageMeta['width'] ) ) {
			$orientation = ( $imageMeta['height'] > $imageMeta['width'] ) ? 'portrait' : 'landscape';
		}
		$output .= "<li class='gallery-item'>";
		$output .= "<span class='gallery-icon {$orientation}'>
				$imageOutput
			</span>";
		$output .= '</li>';
	}
	$output .= '</ul>';

	$output .= "
		</div>\n";
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $output;
}

function mphb_tmpl_the_room_type_featured_image() {
	$imageExcerpt = get_post_field( 'post_excerpt', get_post_thumbnail_id() );
	$imageLink    = wp_get_attachment_url( get_post_thumbnail_id() );
	$image        = mphb_tmpl_get_room_type_image();

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	printf( '<a href="%s" class="mphb-lightbox" title="%s" data-fancybox>%s</a>', esc_url( $imageLink ), esc_attr( $imageExcerpt ), $image );
}

/**
 * Retrieve single room type featured image
 *
 * @param int    $id Optional. ID of post.
 * @param string $size Optional. Size of image.
 * @return string HTML img element or empty string on failure.
 */
function mphb_tmpl_get_room_type_image( $postID = null, $size = null ) {
	if ( is_null( $postID ) ) {
		$postID = get_the_ID();
	}
	if ( is_null( $size ) ) {
		$size = apply_filters( 'mphb_single_room_type_image_size', 'large' );
	}
	$imageTitle = get_the_title( get_post_thumbnail_id( $postID ) );
	return get_the_post_thumbnail(
		$postID,
		$size,
		array(
			'title' => $imageTitle,
		)
	);
}

/**
 * Retrieve in-loop room type thumbnail
 *
 * @param string $size
 */
function mphb_tmpl_the_loop_room_type_thumbnail( $size = null ) {
	if ( is_null( $size ) ) {
		$size = apply_filters( 'mphb_loop_room_type_thumbnail_size', 'post-thumbnail' );
	}
	the_post_thumbnail( $size );
}

/**
 *
 * @param string $buttonText
 */
function mphb_tmpl_the_loop_room_type_book_button( $buttonText = null ) {
	if ( is_null( $buttonText ) ) {
		// translators: Verb. To book an accommodation.
		$buttonText = __( 'Book', 'motopress-hotel-booking' );
	}
	// phpcs:ignore
	echo '<a class="button mphb-book-button" href="' . esc_url( get_the_permalink() ) . '#booking-form-' . get_the_ID() . '">' . /* do not escape */ $buttonText . '</a>';
}

/**
 *
 * @param string $buttonText
 */
function mphb_tmpl_the_loop_room_type_book_button_form( $buttonText = null ) {
	if ( is_null( $buttonText ) ) {
		// translators: Verb. To book an accommodation.
		$buttonText = __( 'Book', 'motopress-hotel-booking' );
	}
	$actionUrl = get_the_permalink();
	$queryArgs = mphb_get_query_args( $actionUrl );
	echo '<form action="' . esc_url( $actionUrl ) . '#booking-form-' . get_the_ID() . '" method="get" >';
	foreach ( $queryArgs as $name => $value ) {
		echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
	}
	// phpcs:ignore
	echo '<button type="submit" class="button mphb-book-button" >' . /* do not escape */  $buttonText . '</button>';
	echo '</form>';
}

/**
 *
 * @param string $buttonText
 */
function mphb_tmpl_the_loop_room_type_view_details_button( $buttonText = null ) {
	if ( is_null( $buttonText ) ) {
		$buttonText = __( 'View Details', 'motopress-hotel-booking' );
	}
	// a.button causes promlems on some themes, when text color = background color
	// phpcs:ignore
	echo '<a class="button mphb-view-details-button" href="' . esc_url( get_the_permalink() ) . '" >' . /* do not escape */ $buttonText . '</a>';
}

/**
 * Display room type calendar
 *
 * @param \MPHB\Entities\RoomType $roomType Optional. Use current room type by default.
 * @param string                  $atts Optional. Additional attributes.
 */
function mphb_tmpl_the_room_type_calendar( $roomTypeId = 0, $monthsToShow = '',
	$isShowPrices = false, $isTruncatePrices = true, $isShowPricesCurrency = false ) {

	$roomType = null;

	if ( 0 < $roomTypeId ) {

		$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

	} else {

		$roomType = MPHB()->getCurrentRoomType();
	}

	if ( null == $roomType || 'publish' != $roomType->getStatus() ) {

		$errorMessage = sprintf(
			__( 'Accommodation %s not found.', 'motopress-hotel-booking' ),
			$roomTypeId
		);

		echo '<p class="mphb-calendar__error">' . esc_html( $errorMessage ) . '</p>';
		return;
	}

	$roomTypeId = $roomType->getId();

	$calendarDirectBookingClass = '';

	if ( MPHB()->settings()->main()->isDirectRoomBooking() ) {
		$calendarDirectBookingClass = 'mphb-calendar--direct-booking';
	}

	$dataString = '';

	if ( ! empty( $monthsToShow ) ) {

		$dataString .= ' data-monthstoshow="' . esc_attr( $monthsToShow ) . '"';
	}

	$firstAvailableCheckInDate = MPHB()->getCoreAPI()->getFirstAvailableCheckInDate(
		$roomType->getOriginalId(),
		MPHB()->settings()->main()->isBookingRulesForAdminDisabled()
	)->format( 'Y-m-d' );

	$dataString .= ' data-is_show_prices="' . ( $isShowPrices ? 1 : 0 ) . '"' .
		' data-is_truncate_prices="' . ( $isTruncatePrices ? 1 : 0 ) . '"' .
		' data-is_show_prices_currency="' . ( $isShowPricesCurrency ? 1 : 0 ) . '"' .
		' data-first_available_check_in_date="' . esc_attr( $firstAvailableCheckInDate ) . '"';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<div class="mphb-calendar mphb-datepick inlinePicker <?php echo esc_attr( $calendarDirectBookingClass ); ?>" data-room-type-id="<?php echo esc_attr( $roomTypeId ); ?>"<?php echo $dataString; ?>></div>
	<?php
}

/**
 * Display room type reservation form
 *
 * @param \MPHB\Entities\RoomType $roomType Optional. Use current room type by default.
 */
function mphb_tmpl_the_room_reservation_form( $roomTypeId = 0 ) {

	$roomType = null;

	if ( 0 < $roomTypeId ) {

		$roomType = MPHB()->getRoomTypeRepository()->findById( $roomTypeId );

	} else {

		$roomType = MPHB()->getCurrentRoomType();
	}

	if ( null == $roomType || 'publish' != $roomType->getStatus() ) {

		$errorMessage = sprintf(
			__( 'Accommodation %s not found.', 'motopress-hotel-booking' ),
			$roomTypeId
		);

		echo '<p class="mphb-errors-wrapper">' . esc_html( $errorMessage ) . '</p>';
		return;
	}

	$uniqueSuffix         = uniqid();
	$isDirectBooking      = MPHB()->settings()->main()->isDirectRoomBooking();
	$directBookingPricing = MPHB()->settings()->main()->getDirectBookingPricing();

	if ( $isDirectBooking ) {
		$searchParameters = MPHB()->searchParametersStorage()->getForRoomType( $roomType );
	} else {
		$searchParameters = MPHB()->searchParametersStorage()->get();
	}

	$checkInDate           = $searchParameters['mphb_check_in_date'];
	$checkInDateFormatted  = \MPHB\Utils\DateUtils::convertDateFormat( $checkInDate, MPHB()->settings()->dateTime()->getDateTransferFormat(), MPHB()->settings()->dateTime()->getDateFormat() );
	$checkOutDate          = $searchParameters['mphb_check_out_date'];
	$checkOutDateFormatted = \MPHB\Utils\DateUtils::convertDateFormat( $checkOutDate, MPHB()->settings()->dateTime()->getDateTransferFormat(), MPHB()->settings()->dateTime()->getDateFormat() );

	// $selectedAdults   = $searchParameters['mphb_adults'] !== '' ? (int)$searchParameters['mphb_adults'] : -1;
	// $selectedChildren = $searchParameters['mphb_children'] !== '' ? (int)$searchParameters['mphb_children'] : -1;

	$actionUrl              = MPHB()->settings()->pages()->getSearchResultsPageUrl();
	$formMethod             = 'GET';
	$formDirectBookingClass = '';

	if ( $isDirectBooking ) {

		$actionUrl              = MPHB()->settings()->pages()->getCheckoutPageUrl();
		$formMethod             = 'POST';
		$formDirectBookingClass = 'mphb-booking-form--direct-booking';
	}

	$firstAvailableCheckInDate = MPHB()->getCoreAPI()->getFirstAvailableCheckInDate(
			$isDirectBooking ? $roomType->getOriginalId() : 0,
			MPHB()->settings()->main()->isBookingRulesForAdminDisabled()
		)->format( 'Y-m-d' );
	?>
	<form method="<?php echo esc_attr( $formMethod ); ?>" action="<?php echo esc_url( $actionUrl ); ?>" class="mphb-booking-form <?php echo esc_attr( $formDirectBookingClass ); ?>" id="<?php echo esc_attr( 'booking-form-' . $roomType->getId() ); ?>" data-first_available_check_in_date="<?php echo esc_attr( $firstAvailableCheckInDate ); ?>">

		<p class="mphb-required-fields-tip"><small><?php printf( esc_html__( 'Required fields are followed by %s', 'motopress-hotel-booking' ), '<abbr title="required">*</abbr>' ); ?></small></p>
		<?php wp_nonce_field( \MPHB\Shortcodes\CheckoutShortcode::NONCE_ACTION_CHECKOUT, \MPHB\Shortcodes\CheckoutShortcode::NONCE_NAME ); ?>
		<?php
		foreach ( mphb_get_query_args( $actionUrl ) as $paramName => $paramValue ) {
			printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $paramName ), esc_attr( $paramValue ) );
		}
		?>
		<input type="hidden" name="mphb_room_type_id" value="<?php echo esc_attr( $roomType->getId() ); ?>" />
		<p class="mphb-check-in-date-wrapper">
			<label for="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqueSuffix ); ?>">
				<?php esc_html_e( 'Check-in Date', 'motopress-hotel-booking' ); ?>
				<abbr title="<?php echo esc_html( sprintf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormatJS() ) ); ?>">*</abbr>
			</label>
			<br />
			<input id="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqueSuffix ); ?>" type="text" class="mphb-datepick" value="<?php echo esc_attr( $checkInDateFormatted ); ?>" required="required" autocomplete="off" placeholder="<?php esc_attr_e( 'Check-in Date', 'motopress-hotel-booking' ); ?>" inputmode="none" />
			<input id="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqueSuffix . '-hidden' ); ?>" type="hidden" name="mphb_check_in_date" value="<?php echo esc_attr( $checkInDate ); ?>" />
		</p>
		<p class="mphb-check-out-date-wrapper">
			<label for="<?php echo esc_attr( 'mphb_check_out_date-' . $uniqueSuffix ); ?>">
				<?php esc_html_e( 'Check-out Date', 'motopress-hotel-booking' ); ?>
				<abbr title="<?php echo esc_html( sprintf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormatJS() ) ); ?>">*</abbr>
			</label>
			<br />
			<input id="<?php echo esc_attr( 'mphb_check_out_date-' . $uniqueSuffix ); ?>" type="text" class="mphb-datepick" value="<?php echo esc_attr( $checkOutDateFormatted ); ?>" required="required" autocomplete="off" placeholder="<?php esc_attr_e( 'Check-out Date', 'motopress-hotel-booking' ); ?>" inputmode="none" />
			<input id="<?php echo esc_attr( 'mphb_check_out_date-' . $uniqueSuffix . '-hidden' ); ?>" type="hidden" name="mphb_check_out_date" value="<?php echo esc_attr( $checkOutDate ); ?>" />
		</p>
		<?php
		if ( ! $isDirectBooking || $directBookingPricing == 'capacity' ) {

				$maxAdultsCount      = MPHB()->settings()->main()->getSearchMaxAdults();
				$maxChildrenCount    = MPHB()->settings()->main()->getSearchMaxChildren();
				$maxTotalGuestsCount = null;

			if ( $isDirectBooking ) {

				$maxAdultsCount   = max(
					$roomType->getAdultsCapacity(),
					MPHB()->settings()->main()->getMinAdults()
				);
				$maxChildrenCount = max(
					$roomType->getChildrenCapacity(),
					MPHB()->settings()->main()->getMinChildren()
				);

				if ( ! empty( $roomType->getTotalCapacity() ) ) {

					$maxTotalGuestsCount = $roomType->getTotalCapacity();

					$maxAdultsCount   = min( $maxAdultsCount, $maxTotalGuestsCount );
					$maxChildrenCount = min(
						$maxChildrenCount,
						$maxTotalGuestsCount - MPHB()->settings()->main()->getMinAdults()
					);
					$maxChildrenCount = 0 > $maxChildrenCount ? 0 : $maxChildrenCount;
				}
			}

			?>

			<?php if ( MPHB()->settings()->main()->isAdultsDisabledOrHidden() ) { ?>

				<input type="hidden" id="<?php echo esc_attr( 'mphb_adults-' . $uniqueSuffix ); ?>" name="mphb_adults" value="<?php echo esc_attr( MPHB()->settings()->main()->getMinAdults() ); ?>" />
			
			<?php } else { ?>

				<p class="mphb-adults-wrapper mphb-capacity-wrapper">
					<label for="<?php echo esc_attr( 'mphb_adults-' . $uniqueSuffix ); ?>">
						<?php
						if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
							esc_html_e( 'Adults', 'motopress-hotel-booking' );
						} else {
							esc_html_e( 'Guests', 'motopress-hotel-booking' );
						}
						?>
					</label>
					<br />
					<select id="<?php echo esc_attr( 'mphb_adults-' . $uniqueSuffix ); ?>" name="mphb_adults">

						<?php foreach ( range( MPHB()->settings()->main()->getMinAdults(), $maxAdultsCount ) as $value ) { ?>

							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (string) esc_attr( $searchParameters['mphb_adults'] ), (string) $value ); ?>><?php echo esc_html( $value ); ?></option>
						
						<?php } ?>
					</select>
				</p>
			<?php } ?>

			<?php

			if ( MPHB()->settings()->main()->isChildrenDisabledOrHidden() || 0 >= $maxChildrenCount ) {
				?>

				<input type="hidden" id="<?php echo esc_attr( 'mphb_children-' . $uniqueSuffix ); ?>" name="mphb_children" value="<?php echo esc_attr( MPHB()->settings()->main()->getMinChildren() ); ?>" />

			<?php } else { ?>

				<p class="mphb-children-wrapper mphb-capacity-wrapper">
					<label for="<?php echo esc_attr( 'mphb_children-' . $uniqueSuffix ); ?>">
						<?php echo esc_html( sprintf( __( 'Children %s', 'motopress-hotel-booking' ), MPHB()->settings()->main()->getChildrenAgeText() ) ); ?>
					</label>
					<br />

					<?php

					$selectDataAttributes = '';

					if ( null !== $maxTotalGuestsCount ) {

						$selectDataAttributes = 'data-min-allowed="' . esc_attr( MPHB()->settings()->main()->getMinChildren() ) .
							'" data-max-allowed="' . esc_attr( $maxChildrenCount ) .
							'" data-max-total="' . esc_attr( $maxTotalGuestsCount ) . '"';
					}

					// phpcs:ignore	?>
					<select id="<?php echo esc_attr( 'mphb_children-' . $uniqueSuffix ); ?>" name="mphb_children" <?php echo $selectDataAttributes; ?>>

						<?php foreach ( range( MPHB()->settings()->main()->getMinChildren(), $maxChildrenCount ) as $value ) { ?>

							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( esc_attr( (string) $searchParameters['mphb_children'] ), (string) $value ); ?>><?php echo esc_html( $value ); ?></option>

						<?php } ?>
					</select>

				</p>
			<?php } ?>
		<?php } ?>
		<?php if ( $isDirectBooking ) { ?>
			<div class="mphb-reserve-room-section mphb-hide">
				<p class="mphb-rooms-quantity-wrapper mphb-rooms-quantity-multiple mphb-hide">
				<?php
					$select  = '<select class="mphb-rooms-quantity" id="' . esc_attr( 'mphb-rooms-quantity-' . $roomType->getId() ) . '" name="' . esc_attr( 'mphb_rooms_details[' . $roomType->getId() . ']' ) . '">';
					$select .= '<option value="1" selected="selected">1</option>';
					$select .= '</select>';

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					printf( esc_html__( 'Reserve %1$s of %2$s available accommodations.', 'motopress-hotel-booking' ), $select, '<span class="mphb-available-rooms-count">1</span>' );
				?>
				</p>
				<p class="mphb-rooms-quantity-wrapper mphb-rooms-quantity-single mphb-hide">
					<?php printf( esc_html__( '%s is available for selected dates.', 'motopress-hotel-booking' ), esc_html( $roomType->getTitle() ) ); ?>
				</p>
				<?php if ( $directBookingPricing != 'disabled' ) { ?>
					<p class="mphb-period-price mphb-regular-price mphb-hide">
						<strong><?php esc_html_e( 'Prices start at:', 'motopress-hotel-booking' ); ?></strong>
					</p>
				<?php } ?>
				<input type="hidden" name="mphb_is_direct_booking" value="1" />
				<input class="button mphb-button mphb-confirm-reservation" type="submit" value="<?php esc_attr_e( 'Confirm Reservation', 'motopress-hotel-booking' ); ?>" />
			</div>
		<?php } ?>

		<div class="mphb-errors-wrapper mphb-hide"></div>
		<p class="mphb-reserve-btn-wrapper">
			<input class="mphb-reserve-btn button" type="submit" value="<?php esc_attr_e( 'Check Availability', 'motopress-hotel-booking' ); ?>" />
		</p>
	</form>
	<?php
}

/**
 * Retrieve in-loop service thumbnail
 *
 * @param string $size
 */
function mphb_tmpl_the_loop_service_thumbnail( $size = null ) {
	if ( is_null( $size ) ) {
		$size = apply_filters( 'mphb_loop_service_thumbnail_size', 'post-thumbnail' );
	}
	the_post_thumbnail( $size );
}

function mphb_tmpl_the_service_price() {
	$service = MPHB()->getServiceRepository()->findById( get_the_ID() );
	echo $service ? wp_kses_post( $service->getPriceWithConditions() ) : '';
}

/**
 * Retrieve the classes for the post div as an array.
 *
 * @param string|array $class   One or more classes to add to the class list.
 * @param int|WP_Post  $post_id Optional. Post ID or post object.
 * @return array Array of classes.
 */
function mphb_tmpl_get_filtered_post_class( $class = '', $postId = null ) {
	$classes = get_post_class( $class, $postId );
	if ( false !== ( $key = array_search( 'hentry', $classes ) ) ) {
		unset( $classes[ $key ] );
	}
	return $classes;
}

/**
 * @param \MPHB\Entities\ReservedRoom[] $reservedRooms
 */
function mphb_tmpl_the_reserved_rooms_details( $reservedRooms ) {
	foreach ( $reservedRooms as $reservedRoom ) {
		$room             = MPHB()->getRoomRepository()->findById( $reservedRoom->getRoomId() );
		$rate             = MPHB()->getRateRepository()->findById( $reservedRoom->getRateId() );
		$reservedServices = $reservedRoom->getReservedServices();
		$guestName        = $reservedRoom->getGuestName();
		$placeholder      = ' &#8212;';

		esc_html_e( 'Accommodation:', 'motopress-hotel-booking' );
		if ( $room ) {
			echo ' <a href="' . esc_url( get_edit_post_link( $room->getId() ) ) . '">' . wp_kses_post( $room->getTitle() ) . '</a>';
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder;
		}

		echo '<br />';

		esc_html_e( 'Rate:', 'motopress-hotel-booking' );
		if ( $rate ) {
			echo ' <a href="' . esc_url( get_edit_post_link( $rate->getOriginalId() ) ) . '">' . esc_html( $rate->getTitle() ) . '</a>';
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder;
		}

		echo '<br />';

		esc_html_e( 'Adults:', 'motopress-hotel-booking' );
		echo ' ' . esc_html( $reservedRoom->getAdults() );

		echo '<br />';

		esc_html_e( 'Children:', 'motopress-hotel-booking' );
		echo ' ' . esc_html( $reservedRoom->getChildren() );

		echo '<br />';

		esc_html_e( 'Services:', 'motopress-hotel-booking' );
		if ( ! empty( $reservedServices ) ) {
			echo '<ol>';
			foreach ( $reservedServices as $reservedService ) {
				echo '<li>';
				echo '<a href="' . esc_url( get_edit_post_link( $reservedService->getOriginalId() ) ) . '">' . esc_html( $reservedService->getTitle() ) . '</a>';
				if ( $reservedService->isPayPerAdult() ) {
					echo ' <em>' . esc_html( sprintf( _n( 'x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking' ), $reservedService->getAdults() ) ) . '</em>';
				}
				if ( $reservedService->isFlexiblePay() ) {
					echo ' <em>' . esc_html( sprintf( _n( 'x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking' ), $reservedService->getQuantity() ) ) . '</em>';
				}
				echo '</li>';
			}
			echo '</ol>';
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $placeholder;
		}

		if ( ! empty( $guestName ) ) {
			echo '<br />';

			esc_html_e( 'Guest:', 'motopress-hotel-booking' );
			echo ' ' . esc_html( $guestName );
		}

		echo '<hr />';
	}
}

/**
 * @param \MPHB\Entities\Booking $booking
 */
function mphb_tmpl_the_payments_table( $booking ) {
	/**
	 * @var \MPHB\Entities\Payment[]
	 */
	$payments = MPHB()->getPaymentRepository()->findAll( array( 'booking_id' => $booking->getId() ) );

	$totalPaid = 0.0;

	?>
	<table class="mphb-payments-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Payment ID', 'motopress-hotel-booking' ); ?></th>
				<th><?php esc_html_e( 'Status', 'motopress-hotel-booking' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'motopress-hotel-booking' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $payments ) ) { ?>
				<tr>
					<td>&#8212;</td>
					<td>&#8212;</td>
					<td>&#8212;</td>
				</tr>
			<?php } else { ?>
				<?php
				foreach ( $payments as $payment ) {
					if ( $payment->getStatus() == PaymentStatuses::STATUS_COMPLETED ) {
						$totalPaid += $payment->getAmount();
					}

					printf( '<tr class="%s">', esc_attr( 'mphb-payment mphb-payment-status-' . $payment->getStatus() ) );
					echo '<td>', sprintf( '<a href="%1$s">#%2$s</a>', esc_url( get_edit_post_link( $payment->getId() ) ), esc_html( $payment->getId() ) ), '</td>';

					echo '<td>', esc_html( mphb_get_status_label( $payment->getStatus() ) ), '</td>';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<td>', mphb_format_price( $payment->getAmount() ), '</td>';
					echo '</tr>';
				}
				?>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<th class="mphb-total-label" colspan="2"><?php esc_html_e( 'Total Paid', 'motopress-hotel-booking' ); ?></th>
				<th>
				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo mphb_format_price( $totalPaid );
				?>
					</th>
			</tr>
			<tr>
				<th class="mphb-to-pay-label" colspan="2"><?php esc_html_e( 'To Pay', 'motopress-hotel-booking' ); ?></th>
				<th>
					<?php
					$needToPay = $booking->getTotalPrice() - $totalPaid;
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo mphb_format_price( $needToPay );
					?>
				</th>
			</tr>
		</tfoot>
	</table>
	<?php

	$createManualPaymentUrl = MPHB()->postTypes()->payment()->getEditPage()->getUrl(
		array(
			'mphb_defaults' => array(
				'_mphb_booking_id'   => $booking->getId(),
				'_mphb_gateway'      => 'manual',
				'_mphb_gateway_mode' => 'live',
				'_mphb_amount'       => $needToPay,
			),
		),
		true
	);

	printf( '<a href="%1$s" class="button button-primary">%2$s</a>', esc_url( $createManualPaymentUrl ), esc_html__( 'Add Payment Manually', 'motopress-hotel-booking' ) );
}

/**
 * @since 3.5.0
 */
function mphb_tmpl_select_html( $args, $options, $selected ) {
	$args = array_map(
		function ( $attribute, $value ) {
			return $attribute . '="' . esc_attr( $value ) . '"';
		},
		array_keys( $args ),
		$args
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<select ' . implode( ' ', $args ) . '>';

	foreach ( $options as $value => $label ) {
		echo '<option value="' . esc_attr( $value ) . '"' . selected( $selected, $value, false ) . '>';
		echo esc_html( $label );
		echo '</option>';
	}

	echo '</select>';
}

/**
 * @since 3.5.0
 */
function mphb_tmpl_multicheck_html( $name, $options, $selected ) {
	if ( substr( $name, -2 ) != '[]' ) {
		$name .= '[]';
	}

	foreach ( $options as $value => $label ) {
		$isChecked = in_array( $value, $selected );

		echo '<label>';
		echo '<input name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" type="checkbox"' . checked( true, $isChecked, false ) . ' />';
		echo ' ' . esc_html( $label );
		echo '</label>';

		echo '<br />';
	}

	echo '<button class="button-link mphb-checkbox-select-all">' . esc_html__( 'Select all', 'motopress-hotel-booking' ) . '</button>';
	echo ' - ';
	echo '<button class="button-link mphb-checkbox-unselect-all">' . esc_html__( 'Unselect all', 'motopress-hotel-booking' ) . '</button>';
}

/**
 * @param array $options
 * @param mixed $selected
 * @param array $atts Optional.
 * @return string
 *
 * @since 3.7.2
 */
function mphb_tmpl_render_select( $options, $selected, $atts = array() ) {
	$output      = '<select' . mphb_tmpl_render_atts( $atts ) . '>';
		$output .= mphb_tmpl_render_select_options( $options, $selected );
	$output     .= '</select>';

	return $output;
}

/**
 * @param array $options
 * @param mixed $selected
 * @return string
 *
 * @since 3.7.2
 */
function mphb_tmpl_render_select_options( $options, $selected ) {
	$output = '';

	foreach ( $options as $value => $label ) {
		$output     .= '<option value="' . esc_attr( $value ) . '"' . selected( $selected, $value, false ) . '>';
			$output .= esc_html( $label );
		$output     .= '</option>';
	}

	return $output;
}

/**
 * @param array $atts
 * @return string
 *
 * @since 3.7.2
 */
function mphb_tmpl_render_atts( $atts ) {
	$output = '';

	foreach ( $atts as $name => $value ) {
		$output .= ' ' . $name . '="' . esc_attr( $value ) . '"';
	}

	return $output;
}

add_filter( 'mphb_tmpl_the_room_type_price_for_dates', 'mphb_tmpl_the_room_type_price_for_dates_output', 10, 3 );

/**
 * @since 3.9.8
 */
function mphb_tmpl_the_room_type_price_for_dates_output( $price, $taxesAndFees, $atts ) {
	$output  = mphb_format_price( $taxesAndFees->getPriceWithTaxesAndFees(), $atts );
	$output .= renderTaxesAndFeesOutput( $taxesAndFees, false );

	return $output;
}

add_filter( 'mphb_tmpl_the_total_recommended_price_for_dates', 'mphb_tmpl_the_total_recommended_price_for_dates_output', 10, 3 );

/**
 * @since 3.9.8
 */
function mphb_tmpl_the_total_recommended_price_for_dates_output( $totalHtml, $taxesAndFees, $atts ) {
	$output  = $totalHtml;
	$output .= renderTaxesAndFeesOutput( $taxesAndFees, false );

	return $output;
}

add_filter( 'mphb_recommended_room_types_items_for_dates', 'mphb_recommended_room_types_items_for_dates_output', 10, 2 );

/**
 * @since 3.9.8
 */
function mphb_recommended_room_types_items_for_dates_output( $price, $taxesAndFees ) {
	return $taxesAndFees->getPriceWithTaxesAndFees();
}

/**
 * @since 3.9.8
 */
function renderTaxesAndFeesOutput( $taxesAndFees, $paragraph = true ) {
	if ( $taxesAndFees->hasTaxesAndFees() ) {
		if ( ! $taxesAndFees->areTaxesAndFeesDefined() ) {
			return \MPHB\Utils\TaxesAndFeesUtils::textTaxesAndFeesUndefined( $paragraph );
		} elseif ( $taxesAndFees->hasExcludedTaxesAndFees() ) {
			$taxesExcluded = $taxesAndFees->getExcludedTaxesAndFees();
			add_filter( 'mphb_price_classes', 'mphb_tax_price_classes', 10 );
			$output = \MPHB\Utils\TaxesAndFeesUtils::textTaxesAndFeesExcluded( $taxesExcluded, $paragraph );
			remove_filter( 'mphb_price_classes', 'mphb_tax_price_classes', 10 );
			return $output;
		} elseif ( $taxesAndFees->hasIncludedTaxesAndFees() ) {
			return \MPHB\Utils\TaxesAndFeesUtils::textTaxesAndFeesIncluded( $paragraph );
		}
	}
}

/**
 * @since 3.9.8
 */
function mphb_tax_price_classes( $classes ) {
	return array( 'mphb-taxes-amount' );
}
