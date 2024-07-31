<?php

use MPHB\Utils\DateUtils;

/**
 * Get template part.
 *
 * @param string $slug
 * @param string $name Optional. Default ''.
 */
function mphb_get_template_part( $slug, $atts = array() ) {

	$template = '';

	// Look in %theme_dir%/%template_path%/slug.php
	$template = locate_template( MPHB()->getTemplatePath() . "{$slug}.php" );

	// Get default template from plugin
	if ( empty( $template ) && file_exists( MPHB()->getPluginPath( "templates/{$slug}.php" ) ) ) {
		$template = MPHB()->getPluginPath( "templates/{$slug}.php" );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'mphb_get_template_part', $template, $slug, $atts );

	if ( ! empty( $template ) ) {
		mphb_load_template( $template, $atts );
	}
}

function mphb_load_template( $template, $templateArgs = array() ) {
	if ( $templateArgs && is_array( $templateArgs ) ) {
		extract( $templateArgs );
	}
	require $template;
}

/**
 *
 * @global string $wp_version
 * @param string $type
 * @param bool   $gmt
 * @return string
 */
function mphb_current_time( $type, $gmt = 0 ) {
	global $wp_version;
	if ( version_compare( $wp_version, '3.9', '<=' ) && ! in_array(
		$type,
		array(
			'timestmap',
			'mysql',
		)
	) ) {
		$timestamp = current_time( 'timestamp', $gmt );
		return date( $type, $timestamp );
	} else {
		return current_time( $type, $gmt );
	}
}

/**
 * Retrieve a post status label by name
 *
 * @param string $status
 * @return string
 */
function mphb_get_status_label( $status ) {
	switch ( $status ) {
		case 'new':
			$label = _x( 'New', 'Post Status', 'motopress-hotel-booking' );
			break;
		case 'auto-draft':
			$label = _x( 'Auto Draft', 'Post Status', 'motopress-hotel-booking' );
			break;
		default:
			$statusObj = get_post_status_object( $status );
			$label     = ! is_null( $statusObj ) && property_exists( $statusObj, 'label' ) ? $statusObj->label : '';
			break;
	}

	return $label;
}

/**
 *
 * @param string $name
 * @param string $value
 * @param int    $expire
 */
function mphb_set_cookie( $name, $value, $expire = 0 ) {
	setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN );
	if ( COOKIEPATH != SITECOOKIEPATH ) {
		setcookie( $name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
	}
}

/**
 *
 * @param string $name
 * @return mixed|null Cookie value or null if not exists.
 */
function mphb_get_cookie( $name ) {
	return ( isset( $_COOKIE[ $name ] ) ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) : null;
}

/**
 *
 * @param string $name
 * @return bool
 */
function mphb_has_cookie( $name ) {
	return isset( $_COOKIE[ $name ] );
}

/**
 *
 * @param string $name
 *
 * @since 4.2.0
 */
function mphb_unset_cookie( $name ) {
	if ( isset( $_COOKIE[ $name ] ) ) {
		unset( $_COOKIE[ $name ] );
	}
}

function mphb_is_checkout_page() {
	$checkoutPageId = MPHB()->settings()->pages()->getCheckoutPageId();
	return $checkoutPageId && is_page( $checkoutPageId );
}

function mphb_is_search_results_page() {
	$searchResultsPageId = MPHB()->settings()->pages()->getSearchResultsPageId();
	return $searchResultsPageId && is_page( $searchResultsPageId );
}

function mphb_is_single_room_type_page() {
	return is_singular( MPHB()->postTypes()->roomType()->getPostType() );
}

function mphb_is_create_booking_page() {
	return MPHB()->getCreateBookingMenuPage()->isCurrentPage();
}

function mphb_get_thumbnail_width() {
	$width = 150;

	$imageSizes = get_intermediate_image_sizes();
	if ( in_array( 'thumbnail', $imageSizes ) ) {
		$width = (int) get_option( 'thumbnail_size_w', $width );
	}

	return $width;
}

/**
 *
 * @param float  $price
 * @param array  $atts
 * @param string $atts['decimal_separator']
 * @param string $atts['thousand_separator']
 * @param int    $atts['decimals'] Number of decimals
 * @param string $atts['currency_position'] Possible values: after, before, after_space, before_space
 * @param string $atts['currency_symbol']
 * @param bool   $atts['literal_free'] Use "Free" text instead of 0 price.
 * @param bool   $atts['trim_zeros'] Trim decimals zeros.
 * @return string
 */
function mphb_format_price( $price, $atts = array() ) {

	$defaultAtts = array(
		'decimal_separator'  => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
		'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
		'decimals'           => MPHB()->settings()->currency()->getPriceDecimalsCount(),
		'is_truncate_price'  => false,
		'currency_position'  => MPHB()->settings()->currency()->getCurrencyPosition(),
		'currency_symbol'    => MPHB()->settings()->currency()->getCurrencySymbol(),
		'literal_free'       => false,
		'trim_zeros'         => true,
		'period'             => false,
		'period_title'       => '',
		'period_nights'      => 1,
		'as_html'            => true,
	);

	$atts = wp_parse_args( $atts, $defaultAtts );

	$price_and_atts = apply_filters(
		'mphb_format_price_parameters',
		array(
			'price'      => $price,
			'attributes' => $atts,
		)
	);
	$price          = $price_and_atts['price'];
	$atts           = $price_and_atts['attributes'];

	if ( $atts['literal_free'] && $price == 0 ) {

		$formattedPrice = apply_filters( 'mphb_free_literal', _x( 'Free', 'Zero price', 'motopress-hotel-booking' ) );
		$priceClasses[] = 'mphb-price-free';

	} else {

		$negative = $price < 0;
		$price    = abs( $price );

		if ( $atts['is_truncate_price'] ) {

			$priceSuffix = '';

			if ( 900 > $price ) { // 0 - 900

				$price = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );

			} elseif ( 900000 > $price ) { // 0.9k-850k

				$price       = number_format( $price / 1000, 1, $atts['decimal_separator'], $atts['thousand_separator'] );
				$priceSuffix = 'K';

			} elseif ( 900000000 > $price ) { // 0.9m-850m

				$price       = number_format( $price / 1000000, 1, $atts['decimal_separator'], $atts['thousand_separator'] );
				$priceSuffix = 'M';

			} elseif ( 900000000000 > $price ) { // 0.9b-850b

				$price       = number_format( $price / 1000000000, 1, $atts['decimal_separator'], $atts['thousand_separator'] );
				$priceSuffix = 'B';

			} else { // 0.9t+

				$price       = number_format( $price / 1000000000000, 1, $atts['decimal_separator'], $atts['thousand_separator'] );
				$priceSuffix = 'T';
			}

			if ( $atts['trim_zeros'] ) {
				$price = mphb_trim_zeros( $price );
			}

			$price = $price . $priceSuffix;

		} else {

			$price = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );

			if ( $atts['trim_zeros'] ) {
				$price = mphb_trim_zeros( $price );
			}
		}

		$formattedPrice = ( $negative ? '-' : '' ) . $price;

		if ( ! empty( $atts['currency_symbol'] ) ) {

			$priceFormat    = MPHB()->settings()->currency()->getPriceFormat( $atts['currency_symbol'], $atts['currency_position'], $atts['as_html'] );
			$formattedPrice = ( $negative ? '-' : '' ) . sprintf( $priceFormat, $price );
		}
	}

	$priceClasses = array( 'mphb-price' );

	/**
	 * @since 3.9.8
	 *
	 * @param array $priceClasses
	 * @param float $price
	 * @param string $formattedPrice
	 * @param array $atts
	 */
	$priceClasses = apply_filters( 'mphb_price_classes', $priceClasses, $price, $formattedPrice, $atts );

	if ( $atts['as_html'] ) {

		$priceClassesStr = join( ' ', $priceClasses );
		$price           = sprintf( '<span class="%s">%s</span>', esc_attr( $priceClassesStr ), $formattedPrice );

	} else {
		$price = $formattedPrice;
	}

	if ( $atts['period'] ) {

		if ( 1 === $atts['period_nights'] ) {

			// translators: Price per one night. Example: $99 per night
			$priceDescription = _x( 'per night', 'Price per one night. Example: $99 per night', 'motopress-hotel-booking' );

		} else {

			/*
			 * Translation will be used with numbers:
			 *     21, 31, 41, 51, 61, 71, 81...
			 *     2-4, 22-24, 32-34, 42-44, 52-54, 62...
			 *     0, 5-19, 100, 1000, 10000...
			 */

			// translators: Price for X nights. Example: $99 for 2 nights, $99 for 21 nights
			$priceDescription = _nx(
				'for %d nights',
				'for %d nights',
				$atts['period_nights'],
				'Price for X nights. Example: $99 for 2 nights, $99 for 21 nights',
				'motopress-hotel-booking'
			);
		}

		$priceDescription = sprintf( $priceDescription, $atts['period_nights'] );
		$priceDescription = apply_filters( 'mphb_price_period_description', $priceDescription, $atts['period_nights'] );

		if ( $atts['as_html'] ) {
			$priceDescription = sprintf( '<span class="mphb-price-period" title="%1$s">%2$s</span>', esc_attr( $atts['period_title'] ), $priceDescription );
		}

		$price = sprintf( '%1$s %2$s', $price, $priceDescription );
	}

	return $price;
}

/**
 *
 * @param float  $price
 * @param array  $atts
 * @param string $atts['decimal_separator']
 * @param string $atts['thousand_separator']
 * @param int    $atts['decimals'] Number of decimals
 * @return string
 */
function mphb_format_percentage( $price, $atts = array() ) {

	$defaultAtts = array(
		'decimal_separator'  => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
		'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
		'decimals'           => MPHB()->settings()->currency()->getPriceDecimalsCount(),
	);

	$atts = wp_parse_args( $atts, $defaultAtts );

	$isNegative     = $price < 0;
	$price          = abs( $price );
	$price          = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );
	$formattedPrice = ( $isNegative ? '-' : '' ) . $price;

	return '<span class="mphb-percentage">' . $formattedPrice . '%</span>';
}

/**
 * Trim trailing zeros off prices.
 *
 * @param mixed $price
 * @return string
 */
function mphb_trim_zeros( $price ) {
	return preg_replace( '/' . preg_quote( MPHB()->settings()->currency()->getPriceDecimalsSeparator(), '/' ) . '0++$/', '', $price );
}

/**
 * @since 3.2.0
 */
function mphb_trim_decimal_zeros( $price ) {
	$separator = preg_quote( MPHB()->settings()->currency()->getPriceDecimalsSeparator() );

	$price = preg_replace( "/{$separator}0++$/", '', $price );
	$price = preg_replace( "/({$separator}[^0]++)0++$/", '$1', $price );

	return $price;
}

/**
 * Get WP Query paged var
 *
 * @return int
 */
function mphb_get_paged_query_var() {
	if ( get_query_var( 'paged' ) ) {
		$paged = absint( get_query_var( 'paged' ) );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = absint( get_query_var( 'page' ) );
	} else {
		$paged = 1;
	}
	return $paged;
}

/**
 *
 * @param array      $queryPart
 * @param array|null $metaQuery
 * @return array
 */
function mphb_add_to_meta_query( $queryPart, $metaQuery ) {

	if ( is_null( $metaQuery ) ) {

		if ( mphb_meta_query_is_first_order_clause( $queryPart ) ) {
			$metaQuery = array( $queryPart );
		} else {
			$metaQuery = $queryPart;
		}

		return $metaQuery;
	}

	if ( ! empty( $metaQuery ) && ! isset( $metaQuery['relation'] ) ) {
		$metaQuery['relation'] = 'AND';
	}

	if ( isset( $metaQuery['relation'] ) && strtoupper( $metaQuery['relation'] ) === 'AND' ) {

		if ( mphb_meta_query_is_first_order_clause( $queryPart ) ||
			( isset( $queryPart['relation'] ) && strtoupper( $queryPart['relation'] ) === 'OR' )
		) {
			$metaQuery[] = $queryPart;
		} else {
			$metaQuery = array_merge( $metaQuery, $queryPart );
		}
	} else {
		$metaQuery = array(
			'relation' => 'AND',
			$queryPart,
			$metaQuery,
		);
	}

	return $metaQuery;
}

/**
 *
 * @param array $query
 * @return bool
 */
function mphb_meta_query_is_first_order_clause( $query ) {
	return isset( $query['key'] ) || isset( $query['value'] );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 * @return string|array
 */
function mphb_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'mphb_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * @see https://github.com/symfony/polyfill-php56
 *
 * @param string $knownString
 * @param string $userInput
 * @return boolean
 */
function mphb_hash_equals( $knownString, $userInput ) {

	if ( ! is_string( $knownString ) ) {
		return false;
	}

	if ( ! is_string( $userInput ) ) {
		return false;
	}

	$knownLen = mphb_strlen( $knownString );
	$userLen  = mphb_strlen( $userInput );

	if ( $knownLen !== $userLen ) {
		return false;
	}

	$result = 0;

	for ( $i = 0; $i < $knownLen; ++$i ) {
		$result |= ord( $knownString[ $i ] ) ^ ord( $userInput[ $i ] );
	}

	return 0 === $result;
}

/**
 *
 * @param string $s
 * @return string
 */
function mphb_strlen( $s ) {
	return ( extension_loaded( 'mbstring' ) ) ? mb_strlen( $s, '8bit' ) : strlen( $s );
}

/**
 * TODO add support for arrays
 *
 * @param string $url
 * @return array
 */
function mphb_get_query_args( $url ) {

	$queryArgs = array();

	$queryStr = parse_url( $url, PHP_URL_QUERY );

	if ( $queryStr ) {
		parse_str( $queryStr, $queryArgs );
	}

	return $queryArgs;
}

/**
 * Wrapper function for wp_dropdown_pages
 *
 * @see wp_dropdown_pages
 *
 * @param array $atts
 * @return string
 */
function mphb_wp_dropdown_pages( $atts = array() ) {

	do_action( '_mphb_before_dropdown_pages' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$dropdown = wp_dropdown_pages( $atts );

	do_action( '_mphb_after_dropdown_pages' );

	return $dropdown;
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @param int $limit The maximum execution time, in seconds. If set to zero, no time limit is imposed.
 */
function mphb_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

function mphb_error_log( $message ) {
	if ( ! is_string( $message ) ) {
		$message = print_r( $message, true );
	}
	error_log( $message );
}

/**
 *
 * @return string
 */
function mphb_current_domain() {
	$homeHost = parse_url( home_url(), PHP_URL_HOST ); // www.booking.coms
	return preg_replace( '/^www\./', '', $homeHost );  // booking.com
}

/**
 * For local usage only. For global IDs it's better to use function
 * mphb_generate_uid().
 *
 * @return string
 */
function mphb_generate_uuid4() {
	// Source: http://php.net/manual/ru/function.uniqid.php#94959
	$uuid4 = sprintf(
		'%04x%04x%04x%04x%04x%04x%04x%04x',
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff )
	);
	return $uuid4;
}

function mphb_generate_uid() {
	return mphb_generate_uuid4() . '@' . mphb_current_domain();
}

/**
 * Retrieves the edit post link for post regardless current user capabilities
 *
 * @param int|string $id
 * @return string
 */
function mphb_get_edit_post_link_for_everyone( $id, $context = 'display' ) {

	if ( ! $post = get_post( $id ) ) {
		return '';
	}

	if ( 'revision' === $post->post_type ) {
		$action = '';
	} elseif ( 'display' == $context ) {
		$action = '&amp;action=edit';
	} else {
		$action = '&action=edit';
	}

	$post_type_object = get_post_type_object( $post->post_type );
	if ( ! $post_type_object ) {
		return '';
	}

	if ( $post_type_object->_edit_link ) {
		$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
	} else {
		$link = '';
	}

	/**
	 * Filters the post edit link.
	 *
	 * @since 2.3.0
	 *
	 * @param string $link The edit link.
	 * @param int $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands
	 * are encoded.
	 */
	return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );

	return $link;
}

/**
 *
 * @param int $typeId Room type ID.
 *
 * @return array [%Room ID% => %Room Number%].
 */
function mphb_get_rooms_select_list( $typeId ) {
	$rooms = MPHB()->getRoomPersistence()->getIdTitleList(
		array(
			'room_type_id' => $typeId,
			'post_status'  => 'all',
		)
	);

	$roomType  = MPHB()->getRoomTypeRepository()->findById( $typeId );
	$typeTitle = ( $roomType ? $roomType->getTitle() : '' );

	if ( ! empty( $typeTitle ) ) {
		foreach ( $rooms as &$room ) {
			$room = str_replace( $typeTitle, '', $room );
			$room = trim( $room );
		}
		unset( $room );
	}

	return $rooms;
}

function mphb_show_multiple_instances_notice() {
	/* translators: %s: URL to plugins.php page */
	$message = __( 'You are using two instances of Hotel Booking plugin at the same time, please <a href="%s">deactivate one of them</a>.', 'motopress-hotel-booking' );
	$message = sprintf( $message, esc_url( admin_url( 'plugins.php' ) ) );

	$html_message = sprintf( '<div class="notice notice-warning is-dismissible">%s</div>', wpautop( $message ) );

	echo wp_kses_post( $html_message );
}

/**
 * @param string $wrapper Optional. Wrapper tag - "span" or "div". "span" by
 *     default. Pass the empty value to remove the wrapper
 * @param string $wrapperClass Optional. "description" by default.
 * @return string "Upgrade to Premium..." HTML.
 *
 * @since 3.5.1 parameters $before and $after was replaced with $wrapper and $wrapperClass.
 */
function mphb_upgrade_to_premium_message( $wrapper = 'span', $wrapperClass = 'description' ) {
	$message = __( '<a href="%s">Upgrade to Premium</a> to enable this feature.', 'motopress-hotel-booking' );
	$message = sprintf( $message, esc_url( admin_url( 'admin.php?page=mphb_premium' ) ) );

	if ( ! empty( $wrapper ) ) {
		if ( $wrapper === 'div' ) {
			$message = '<div class="' . esc_attr( $wrapperClass ) . '">' . $message . '</div>';
		} else {
			$message = '<span class="' . esc_attr( $wrapperClass ) . '">' . $message . '</span>';
		}
	}

	return $message;
}

/**
 * Season price format history:
 * v2.6.0- - single number.
 * v2.7.1- - ["base", "enable_variations" => "0"|"1", "variations" => ""|[["adults", "children", "price"]]].
 * v2.7.2+ - ["periods", "prices", "enable_variations" => true/false, "variations" => [["adults", "children", "prices"]]].
 *
 * @param mixed $price Price in any format.
 *
 * @return array Price in format 2.7.2+.
 */
function mphb_normilize_season_price( $price ) {
	$value = array(
		'periods'           => array( 1 ),
		'prices'            => array( 0 ),
		'enable_variations' => false,
		'variations'        => array(),
	);

	if ( ! is_numeric( $price ) && ! is_array( $price ) ) {
		return $value;
	}

	if ( is_numeric( $price ) ) {
		// Convert v2.6.0- into v2.7.2+
		$value['prices'][0] = $price;

	} elseif ( isset( $price['base'] ) ) {
		// Convert v2.7.1- into v2.7.2+
		$value['prices'][0]         = $price['base'];
		$value['enable_variations'] = \MPHB\Utils\ValidateUtils::validateBool( $price['enable_variations'] );

	} else {
		// Merge values from v2.7.2+
		$value['periods']           = $price['periods'];
		$value['prices']            = $price['prices'];
		$value['enable_variations'] = $price['enable_variations'];
	}

	// Merge variations
	if ( isset( $price['variations'] ) && is_array( $price['variations'] ) ) {
		foreach ( $price['variations'] as $variation ) {
			if ( isset( $variation['price'] ) ) {
				// Convert v2.7.1- into v2.7.2+
				$prices = array( $variation['price'] );
			} else {
				// Copy prices from v2.7.2+
				$prices = $variation['prices'];
			}

			$value['variations'][] = array(
				'adults'   => intval( $variation['adults'] ),
				'children' => intval( $variation['children'] ),
				'prices'   => $prices,
			);
		}
	}

	return $value;
}

/**
 * Check if term name is reserved.
 *
 * @param  string $termName Term name.
 *
 * @return bool
 *
 * @see https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
 */
function mphb_is_reserved_term( $termName ) {
	$reservedTerms = array(
		'attachment',
		'attachment_id',
		'author',
		'author_name',
		'calendar',
		'cat',
		'category',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comments_per_page',
		'comments_popup',
		'customize_messenger_channel',
		'customized',
		'cpage',
		'day',
		'debug',
		'error',
		'exact',
		'feed',
		'fields',
		'hour',
		'link_category',
		'm',
		'minute',
		'monthnum',
		'more',
		'name',
		'nav_menu',
		'nonce',
		'nopaging',
		'offset',
		'order',
		'orderby',
		'p',
		'page',
		'page_id',
		'paged',
		'pagename',
		'pb',
		'perm',
		'post',
		'post__in',
		'post__not_in',
		'post_format',
		'post_mime_type',
		'post_status',
		'post_tag',
		'post_type',
		'posts',
		'posts_per_archive_page',
		'posts_per_page',
		'preview',
		'robots',
		's',
		'search',
		'second',
		'sentence',
		'showposts',
		'static',
		'subpost',
		'subpost_id',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'taxonomy',
		'tb',
		'term',
		'theme',
		'type',
		'w',
		'withcomments',
		'withoutcomments',
		'year',
	);

	return in_array( $termName, $reservedTerms, true );
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 *
 * @since 3.7.0
 */
function mphb_string_starts_with( $haystack, $needle ) {
	$length = strlen( $needle );
	return ( substr( $haystack, 0, $length ) === $needle );
}

/**
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
function mphb_string_ends_with( $haystack, $needle ) {
	$length = strlen( $needle );

	if ( $length == 0 ) {
		return true;
	}

	return ( substr( $haystack, -$length ) === $needle );
}

/**
 * @since 3.0
 */
function mphb_array_disjunction( $a, $b ) {
	return array_merge( array_diff( $a, $b ), array_diff( $b, $a ) );
}

/**
 * @return array "publish", and maybe "private", if current user can read
 * private posts.
 *
 * @since 3.0.1
 */
function mphb_readable_post_statuses() {
	if ( current_user_can( 'read_private_posts' ) ) {
		return array( 'publish', 'private' );
	} else {
		return array( 'publish' );
	}
}

/**
 * @since 3.0.2
 */
function mphb_db_version() {
	// Min version "1.0.1" can be found in the upgrader constants
	return get_option( 'mphb_db_version', '1.0.1' );
}

/**
 * @since 3.0.2
 */
function mphb_db_version_at_least( $requiredVersion ) {
	$dbVersion = mphb_db_version();
	return version_compare( $dbVersion, $requiredVersion, '>=' );
}

/**
 * @since 3.0.3
 */
function mphb_version_at_least( $requiredVersion ) {
	$actualVersion = MPHB()->getVersion();
	return version_compare( $actualVersion, $requiredVersion, '>=' );
}

/**
 * @param string $requiredVersion
 * @return bool
 *
 * @global string $wp_version
 *
 * @since 3.7.4
 */
function mphb_wordpress_at_least( $requiredVersion ) {
	global $wp_version;

	return version_compare( $wp_version, $requiredVersion, '>=' );
}

/**
 * @see Issue ticket in WordPress Trac: https://core.trac.wordpress.org/ticket/45495
 *
 * @since 3.3.0
 */
function mphb_fix_blocks_autop() {
	if ( mphb_wordpress_at_least( '5.2' ) ) {
		// The bug was fixed since WP 5.2
		return;

	} elseif ( mphb_wordpress_at_least( '5.0' ) && has_filter( 'the_content', 'wpautop' ) !== false ) {
		remove_filter( 'the_content', 'wpautop' );
		add_filter(
			'the_content',
			function ( $content ) {
				if ( has_blocks() ) {
					return $content;
				}

				return wpautop( $content );
			}
		);
	}
}

/**
 * @param string $json JSON string with possibly escaped Unicode symbols (\uXXXX).
 * @return string JSON string with escaped Unicode symbols (\\uXXXX).
 *
 * @since 3.5.0
 */
function mphb_escape_json_unicodes( $json ) {
	return preg_replace( '/(\\\\u[0-9a-f]{4})/i', '\\\\$1', $json );
}

/**
 * @param string $json JSON string with possibly escaped symbol '.
 * @return string JSON string, ready to json_decode().
 *
 * @since 1.x
 */
function mphb_strip_price_breakdown_json( $json ) {
	if ( strpos( $json, "\\'" ) !== false ) {
		// Unslash, not breaking the Unicode symbols
		return wp_unslash( mphb_escape_json_unicodes( $json ) );
	} else {
		return $json;
	}
}

/**
 * @return string "/path/to/wordpress/wp-content/uploads/mphb/"
 *
 * @since 3.5.0
 */
function mphb_uploads_dir() {
	$uploads = wp_upload_dir();
	return trailingslashit( $uploads['basedir'] ) . 'mphb/';
}

/**
 * @since 3.5.0
 */
function mphb_create_uploads_dir() {
	$dir = mphb_uploads_dir();

	if ( file_exists( $dir ) ) {
		return;
	}

	// Create .../uploads/mphb/
	wp_mkdir_p( $dir );

	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

	if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
		return;
	}

	$fileSystemDirect = new WP_Filesystem_Direct( null );

	$source = MPHB()->getPluginDir() . '/assets/others/index.php';

	$destination = $dir . 'index.php';

	$fileSystemDirect->copy( $source, $destination, true );

	$source = MPHB()->getPluginDir() . '/assets/others/.htaccess';

	$destination = $dir . '.htaccess';

	$fileSystemDirect->copy( $source, $destination, true );
}

/**
 * @since 3.6.0
 */
function mphb_verify_nonce( $action, $nonceName = 'mphb_nonce' ) {
	if ( ! isset( $_REQUEST[ $nonceName ] ) ) {
		return false;
	}

	$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $nonceName ] ) );

	return wp_verify_nonce( $nonce, $action );
}

/**
 * @since 3.7.1
 */
function mphb_get_polyfill_for( $function ) {
	switch ( $function ) {
		case 'mb_convert_encoding':
			require_once MPHB()->getPluginPath( 'includes/polyfills/mbstring.php' );
			break;
	}
}

/**
 * @return int
 *
 * @since 3.7.2
 */
function mphb_current_year() {
	return intval( strftime( '%Y' ) );
}

/**
 * @param int $month
 * @param int $year
 * @return int
 *
 * @since 3.7.2
 */
function mphb_days_in_month( $month, $year ) {
	return cal_days_in_month( CAL_GREGORIAN, $month, $year );
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_customer_fields() {
	return MPHB()->settings()->main()->getCustomerBundle()->getCustomerFields();
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_default_customer_fields() {
	return MPHB()->settings()->main()->getCustomerBundle()->getDefaultFields();
}

/**
 * @param string $fieldName
 * @return bool
 *
 * @since 3.7.2
 */
function mphb_is_default_customer_field( $fieldName ) {
	return MPHB()->settings()->main()->getCustomerBundle()->isDefaultField( $fieldName );
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_custom_customer_fields() {
	return MPHB()->settings()->main()->getCustomerBundle()->getCustomFields();
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_admin_checkout_customer_fields() {
	return MPHB()->settings()->main()->getCustomerBundle()->getAdminCheckoutFields();
}

/**
 * @return int
 *
 * @since 3.7.2
 */
function mphb_get_editing_post_id() {
	$postId = 0;

	if ( is_admin() ) {
		if ( isset( $_REQUEST['post_ID'] ) && is_numeric( $_REQUEST['post_ID'] ) ) {
			$postId = intval( $_REQUEST['post_ID'] ); // On post update ($_POST)
		} elseif ( isset( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] ) ) {
			$postId = intval( $_REQUEST['post'] ); // On post edit page ($_GET)
		}
	}

	return $postId;
}

/**
 * @param int|float $value
 * @param int|float $min
 * @param int|float $max
 * @return int|float
 *
 * @since 3.7.2
 */
function mphb_limit( $value, $min, $max ) {
	return max( $min, min( $value, $max ) );
}

/**
 * Add an array after the specified position.
 *
 * @param array $array Subject array.
 * @param int   $position
 * @param array $insert Array to insert.
 * @return array Result array with inserted items.
 *
 * @since 3.7.2
 */
function mphb_array_insert_after( $array, $position, $insert ) {
	if ( $position < 0 ) {
		return array_merge( $insert, $array );
	} elseif ( $position >= count( $array ) ) {
		return array_merge( $array, $insert );
	} else {
		return array_merge(
			array_slice( $array, 0, $position + 1, true ),
			$insert,
			array_slice( $array, $position + 1, count( $array ), true )
		);
	}
}

/**
 * Add an array after the specified key in the associative array.
 *
 * @param array $array Subject array.
 * @param mixed $searchKey
 * @param array $insert Array to insert.
 * @return array Result array with inserted items.
 *
 * @since 3.7.2
 */
function mphb_array_insert_after_key( $array, $searchKey, $insert ) {
	$position = array_search( $searchKey, array_keys( $array ) );

	if ( $position !== false ) {
		return mphb_array_insert_after( $array, $position, $insert );
	} else {
		return mphb_array_insert_after( $array, count( $array ), $insert );
	}
}

/**
 * @param array    $haystack
 * @param callable $checkCallback The callback check function. The function must
 *     return TRUE if the proper element was found or FALSE otherwise. Gets the
 *     value of the element as the first argument and the key as second.
 * @return mixed The key for searched element or FALSE.
 *
 * @since 3.7.2
 */
function mphb_array_usearch( array $haystack, callable $checkCallback ) {
	foreach ( $haystack as $key => $value ) {
		if ( $checkCallback( $value, $key ) ) {
			return $key;
		}
	}

	return false;
}

/**
 * @param string $str
 * @param string $separator Optional. "_" by default.
 * @return string
 *
 * @since 3.7.3
 */
function mphb_prefix( $str, $separator = '_' ) {
	return MPHB()->addPrefix( $str, $separator );
}

/**
 * @param string $str
 * @param string $separator Optional. "_" by default.
 * @return string
 *
 * @since 3.7.3
 */
function mphb_unprefix( $str, $separator = '_' ) {
	$prefix = MPHB()->getPrefix() . $separator;
	return str_replace( $prefix, '', $str );
}

/**
 * @param int  $bookingId
 * @param bool $force Optional. FALSE by default.
 * @return \MPHB\Entities\Booking|null
 *
 * @since 3.7.3
 */
function mphb_get_booking( $bookingId, $force = false ) {
	return MPHB()->getBookingRepository()->findById( $bookingId, $force );
}

/**
 * @param int $bookingId
 * @return \MPHB\Entities\Customer|null
 *
 * @since 3.7.3
 */
function mphb_get_customer( $bookingId ) {
	$booking = mphb_get_booking( $bookingId );

	if ( ! is_null( $booking ) ) {
		return $booking->getCustomer();
	} else {
		return null;
	}
}

/**
 * @param int  $roomTypeId
 * @param bool $force Optional. FALSE by default.
 * @return \MPHB\Entities\RoomType|null
 *
 * @since 3.8
 */
function mphb_get_room_type( $roomTypeId, $force = false ) {
	return MPHB()->getRoomTypeRepository()->findById( $roomTypeId, $force );
}

/**
 * @param int  $seasonId
 * @param bool $force Optional. False by default.
 * @return \MPHB\Entities\Season|null
 *
 * @since 3.9
 */
function mphb_get_season( $seasonId, $force = false ) {
	return MPHB()->getSeasonRepository()->findById( $seasonId, $force );
}

/**
 * Determine if the current view is the "All" view.
 *
 * @see \WP_Posts_List_Table::is_base_request()
 *
 * @param string|null $postType Optional. NULL by default.
 * @return bool
 *
 * @global string $typenow
 *
 * @since 3.7.3
 */
function mphb_is_base_request( $postType = null ) {
	global $typenow;

	$allowedVars = array(
		'post_type' => true,
		'paged'     => true,
		'all_posts' => true,
	);

	$unallowedVars = array_diff_key( $_GET, $allowedVars );

	$isBase = count( $unallowedVars ) == 0;

	// Add additional check of the post type
	if ( ! is_null( $postType ) && $isBase ) {
		$isBase = $postType === $typenow;
	}

	return $isBase;
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_complete_booking( $booking ) {
	$bookedStatuses = MPHB()->postTypes()->booking()->statuses()->getBookedRoomStatuses();
	return in_array( $booking->getStatus(), $bookedStatuses );
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_pending_booking( $booking ) {
	$pendingStatuses = MPHB()->postTypes()->booking()->statuses()->getPendingRoomStatuses();
	return in_array( $booking->getStatus(), $pendingStatuses );
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_locking_booking( $booking ) {
	$lockingStatuses = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
	return in_array( $booking->getStatus(), $lockingStatuses );
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_failed_booking( $booking ) {
	$failedStatuses = MPHB()->postTypes()->booking()->statuses()->getFailedStatuses();
	return in_array( $booking->getStatus(), $failedStatuses );
}

/**
 * @param \DateTime $from Start date, like check-in date.
 * @param \DateTime $to End date, like check-out date.
 * @param array     $atts Optional.
 *     @param int       $atts['room_type_id'] Optional. 0 by default (any room type).
 *     @param int|int[] $atts['exclude_bookings'] Optional. One or more booking IDs.
 * @return array [Room type ID => [Rooms IDs]] (all IDs - original)
 *
 * @since 3.8
 */
function mphb_get_available_rooms( $from, $to, $atts = array() ) {
		$roomTypeId = isset( $atts['room_type_id'] ) ? $atts['room_type_id'] : 0;
		$searchAtts = array();

	if ( isset( $atts['exclude_bookings'] ) ) {
			$searchAtts['exclude_bookings'] = $atts['exclude_bookings'];
	}

		$searchAtts['skip_buffer_rules'] = false;

		return MPHB()->getRoomRepository()->getAvailableRooms( $from, $to, $roomTypeId, $searchAtts );
}

/**
 * @param int|string $value
 * @return int The number in range [0; oo)
 *
 * @since 3.8
 */
function mphb_posint( $value ) {
	return max( 0, intval( $value ) );
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_min_adults() {
	return MPHB()->settings()->main()->getMinAdults();
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_min_children() {
	return MPHB()->settings()->main()->getMinChildren();
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_max_adults() {
	return MPHB()->settings()->main()->getSearchMaxAdults();
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_max_children() {
	return MPHB()->settings()->main()->getSearchMaxChildren();
}

/**
 * @param array $array Array to flip.
 * @param bool  $arraySingle Optional. Convert single value into array. FALSE by default.
 * @return array
 *
 * @since 3.8
 */
function mphb_array_flip_duplicates( $array, $arraySingle = false ) {
	$values = array_unique( $array );
	$flip   = array();

	foreach ( $values as $value ) {
		$keys = array_keys( $array, $value );

		if ( $arraySingle || count( $keys ) > 1 ) {
			$flip[ $value ] = $keys;
		} else {
			$flip[ $value ] = reset( $keys );
		}
	}

	return $flip;
}

/**
 * @param \MPHB\Entities\RoomType|int|null Optional. Room type or it's ID.
 *     Current room type by default.
 * @return float
 *
 * @since 3.8.3
 */
function mphb_get_room_type_base_price( $roomType = null, $startDate = null, $endDate = null ) {

	if ( is_null( $roomType ) ) {
		$roomType = MPHB()->getCurrentRoomType();
	} elseif ( is_int( $roomType ) ) {
		$roomType = mphb_get_room_type( $roomType );
	}

	if ( is_null( $roomType ) ) {
		return 0.0;
	}

	$price = 0.0;
	$rates = MPHB()->getCoreAPI()->getRoomTypeActiveRates( $roomType->getOriginalId() );

	if ( ! empty( $rates ) ) {

		if ( null == $startDate ) {
			$startDate = new \DateTime();
		}
		if ( null == $endDate ) {
			$endDate = DateUtils::cloneModify(
				$startDate,
				sprintf(
					'+%d days',
					MPHB()->settings()->main()->getAveragePricePeriod()
				)
			);
		}

		$prices = array_map(
			function ( $rate ) use ( $startDate, $endDate ) {
				return $rate->getMinBasePrice( $startDate, $endDate );
			},
			$rates
		);
		$prices = array_filter( $prices );

		if ( ! empty( $prices ) ) {
			$price = min( $prices );
		}
	}

	return $price;
}

/**
 * @param \DateTime                                 $startDate
 * @param \DateTime                                 $endDate
 * @param \MPHB\Entities\RoomType|int|null Optional. Room type or it's ID.
 *     Current room type by default.
 * @param array                                     $args Optional.
 * @param int                                       $args['adults']
 * @param int                                       $args['children']
 * @return float
 *
 * @since 3.8.3
 */
function mphb_get_room_type_period_price( $startDate, $endDate, $roomType = null, $args = array() ) {
	if ( is_null( $roomType ) ) {
		$roomType = MPHB()->getCurrentRoomType();
	} elseif ( is_int( $roomType ) ) {
		$roomType = mphb_get_room_type( $roomType );
	}

	if ( is_null( $roomType ) ) {
		return 0.0;
	}

	$price = 0.0;

	$rates = MPHB()->getRateRepository()->findAllActiveByRoomType(
		$roomType->getOriginalId(),
		array(
			'check_in_date'  => $startDate,
			'check_out_date' => $endDate,
		)
	);

	if ( ! empty( $rates ) ) {
		$searchArgs = array(
			'check_in_date'  => $startDate,
			'check_out_date' => $endDate,
		);

		if ( isset( $args['adults'] ) || isset( $args['children'] ) ) {
			$searchArgs['adults']   = isset( $args['adults'] ) ? $args['adults'] : MPHB()->settings()->main()->getMinAdults();
			$searchArgs['children'] = isset( $args['children'] ) ? $args['children'] : MPHB()->settings()->main()->getMinChildren();
		}

		MPHB()->reservationRequest()->setupSearchParameters()->setupParameters( $searchArgs );

		$prices = array_map(
			function ( $rate ) use ( $startDate, $endDate ) {
				return $rate->calcPrice( $startDate, $endDate );
			},
			$rates
		);
		$prices = array_filter( $prices );

		if ( ! empty( $prices ) ) {
			$price = min( $prices );
		}

		MPHB()->reservationRequest()->resetDefaults();
	}

	return $price;
}

/**
 * @param string $language 'any'|'original' Optional. 'any' by default.
 * @param array  $atts Additional atts.
 * @return int[]
 *
 * @since 3.9
 */
function mphb_get_room_type_ids( $language = 'any', $atts = array() ) {
	if ( $language == 'original' ) {
		// Multilingual support
		$atts['mphb_language'] = 'original';
	}

	$atts['fields'] = 'ids'; // Force IDs

	return MPHB()->getRoomTypePersistence()->getPosts( $atts );
}

/**
 * @param string $modifier Optional. Modifier like '+1 day'. Empty by default.
 * @return DateTime
 *
 * @since 3.9
 */
function mphb_today( $modifier = '' ) {
	$date = new DateTime( 'today' );

	if ( ! empty( $modifier ) ) {
		$date->modify( $modifier );
	}

	return $date;
}

/**
 * @return bool
 *
 * @since 3.9
 */
function mphb_has_buffer_days() {
	return MPHB()->getRulesChecker()->bufferRules()->hasRules();
}

/**
 * @param DateTime $date
 * @param int      $roomTypeId
 * @return int Buffer days amount.
 *
 * @since 3.9
 */
function mphb_get_buffer_days( $date, $roomTypeId = 0 ) {

	$actualRule = MPHB()->getRulesChecker()->bufferRules()->findActualRule( $date, $roomTypeId );
	return $actualRule->getBufferDays();
}

/**
 * @param DateTime $startDate
 * @param DateTime $endDate
 * @param int      $bufferDays
 * @return DateTime[] [0 => Modified start date, 1 => Modified end date]
 *
 * @since 3.9
 */
function mphb_modify_buffer_period( $startDate, $endDate, $bufferDays = 0 ) {
	if ( $bufferDays > 0 ) {
		$beforeStart = DateUtils::cloneModify( $startDate, "-{$bufferDays} days" );
		$afterEnd    = DateUtils::cloneModify( $endDate, "+{$bufferDays} days" );

		return array( $beforeStart, $afterEnd );
	}

	return array( $startDate, $endDate );
}

/**
 * @param MPHB\Entities\Booking $booking
 * @param int                   $bufferDays
 * @param bool                  $extendDates Optional. Extend the final result with check-in and
 *                      next to the check-out dates. Useful for Booking Calendar, for example.
 *                      False by default.
 * @return DateTime[] [Date string ('Y-m-d') => Date object] - only the dates of
 *     the buffer.
 *
 * @since 3.9
 */
function mphb_modify_booking_buffer_period( $booking, $bufferDays, $extendDates = false ) {
	$checkInDate  = $booking->getCheckInDate();
	$checkOutDate = $booking->getCheckOutDate();

	$offsetDays = $extendDates ? $bufferDays + 1 : $bufferDays;

	// Find the buffer range
	list($beforeCheckIn, $afterCheckOut) = mphb_modify_buffer_period( $checkInDate, $checkOutDate, $bufferDays );

	if ( $extendDates ) {
		$afterCheckOut->modify( '+1 day' );
	}

	// Build the full period
	$fullPeriod = DateUtils::createDatePeriod( $beforeCheckIn, $afterCheckOut );

	// Split period to dates
	$bufferDates = iterator_to_array( $fullPeriod );
	array_splice( $bufferDates, $offsetDays, -$offsetDays ); // Remove booking inner dates

	$dateFormat  = MPHB()->settings()->dateTime()->getDateTransferFormat();
	$dateStrings = array_map(
		function ( $date ) use ( $dateFormat ) {
			return $date->format( $dateFormat );
		},
		$bufferDates
	);

	return array_combine( $dateStrings, $bufferDates );
}

/**
 * @param array $bookingPeriod
 * @param int   $roomTypeId
 *
 * @return DateTime[] [0 => Modified start date, 1 => Modified end date]
 *
 * @since 3.9
 */
function mphb_modify_booking_period_with_booking_buffer( $bookingPeriod, $roomTypeId ) {
		$bufferDays = mphb_get_buffer_days( $bookingPeriod[0], $roomTypeId );

		return mphb_modify_buffer_period( $bookingPeriod[0], $bookingPeriod[1], $bufferDays );
}
add_filter( 'mphb_modify_booking_period', 'mphb_modify_booking_period_with_booking_buffer', 10, 2 );

/**
 * @param array $atts
 *
 * @return array
 *
 * @since 3.9
 */
function mphb_is_rooms_free_query_atts_with_buffer( $atts ) {
		$atts['skip_buffer_rules'] = false;

		return $atts;
}
add_filter( 'mphb_is_rooms_free_query_atts', 'mphb_is_rooms_free_query_atts_with_buffer' );


/**
 * Display a help tip.
 *
 * @param  string $tip        Help tip text.
 * @param  bool   $allow_html Allow sanitized HTML if true or escape.
 * @return string
 *
 * @since  3.9.8
 */
function mphb_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = htmlspecialchars(
			wp_kses(
				html_entity_decode( $tip ),
				array(
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'small'  => array(),
					'span'   => array(),
					'ul'     => array(),
					'li'     => array(),
					'ol'     => array(),
					'p'      => array(),
				)
			)
		);
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="mphb-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 *
 * @param  string $endpoint  Endpoint slug.
 * @param  string $value     Query param value.
 * @param  string $permalink Permalink.
 *
 * @since 4.2.0
 *
 * @return string
 */
function mphb_create_url( $endpoint, $value = '', $permalink = '' ) {

	global $wp;

	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	$query_vars = $wp->query_vars;
	$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink );

		if ( $value ) {
			$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
		} else {
			$url .= user_trailingslashit( $endpoint );
		}

		$url .= $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return $url;
}

/**
 * @since 4.2.2
 *
 * @param string $queueItem Sync queue item, like "%Timestamp%_%Room ID%".
 * @return int Room ID.
 */
function mphb_parse_queue_room_id( $queueItem ) {
	return (int) preg_replace( '/^\d+_(\d+)/', '$1', $queueItem );
}
