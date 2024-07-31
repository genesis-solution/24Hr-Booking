<?php

/*
 * global $mphbAttributes = [%Attribute name% => %Attribute%]
 * global $mphbAttributeTaxonomies = [%Taxonomy name% => %Attribute%]
 *
 * Where %attribute% is an array with fields:
 *	   string "attributeName" - example: "hotel"
 *	   string "taxonomyName"  - attribute name with prefix; for example: "mphb_ra_hotel"
 *	   string "title"		  - post title, like "Hotel"
 *	   bool   "public"		  - enable archives for this attribute; FALSE by default
 *	   bool   "visible"		  - visible in details section of the room type; FALSE by default
 *	   string "orderby"		  - custom|name|numeric|id ("custom" available only if WordPress supports termmetas);
 *								by default - "custom" (but only if WP supports termmetas) or "name" (otherwise)
 *	   string "type"		  - only "select" is available at the moment
 *	   bool   "hasDuplicates" - multiple attributes tries to register an equal taxonomy; FALSE by default
 */

function mphb_add_attribute( $attribute ) {
	global $mphbAttributes, $mphbAttributeTaxonomies;

	if ( ! isset( $mphbAttributes ) ) {
		$mphbAttributes = array();
	}

	if ( ! isset( $mphbAttributeTaxonomies ) ) {
		$mphbAttributeTaxonomies = array();
	}

	$attributeName = $attribute['attributeName'];
	$taxonomyName  = $attribute['taxonomyName'];

	$mphbAttributes[ $attributeName ]         = $attribute;
	$mphbAttributeTaxonomies[ $taxonomyName ] = $attribute;
}

function mphb_attribute_exists( $attributeName ) {
	global $mphbAttributes;
	return isset( $mphbAttributes[ $attributeName ] );
}

/**
 * This function generates taxonomy name or gets the registered one.
 *
 * @param string $attributeName
 *
 * @return string
 */
function mphb_attribute_taxonomy_name( $attributeName ) {
	global $mphbAttributes;

	if ( isset( $mphbAttributes[ $attributeName ] ) ) {
		return $mphbAttributes[ $attributeName ]['taxonomyName'];
	}

	$taxonomyName = mphb_attributes_prefix() . $attributeName;
	$taxonomyName = substr( $taxonomyName, 0, 32 );

	return $taxonomyName;
}

function mphb_taxonomy_attribute_name( $taxonomyName ) {
	global $mphbAttributeTaxonomies;

	if ( isset( $mphbAttributeTaxonomies[ $taxonomyName ] ) ) {
		return $mphbAttributeTaxonomies[ $taxonomyName ]['attributeName'];
	} else {
		return str_replace( mphb_attributes_prefix(), '', $taxonomyName );
	}
}

function mphb_is_attribute_taxonomy( $taxonomyName ) {
	global $mphbAttributeTaxonomies;
	return isset( $mphbAttributeTaxonomies[ $taxonomyName ] );
}

function mphb_is_attribute_taxonomy_edit_page() {
	$screen   = get_current_screen();
	$screenId = ( $screen ) ? $screen->id : '';

	if ( strpos( $screenId, 'edit-' . mphb_attributes_prefix() ) !== false ) {
		return true;
	} elseif ( ! empty( $_GET['taxonomy'] ) && mphb_is_attribute_taxonomy( sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) ) ) {
		return true;
	}

	return false;
}

function mphb_is_duplicate_attribute( $attributeName ) {
	global $mphbAttributes, $mphbAttributeTaxonomies;
	$taxonomyName = mphb_attribute_taxonomy_name( $attributeName );
	return ( ! isset( $mphbAttributes[ $attributeName ] ) && isset( $mphbAttributeTaxonomies[ $taxonomyName ] ) );
}

function mphb_has_duplicate_attributes( $attributeName ) {
	global $mphbAttributes;
	return ( isset( $mphbAttributes[ $attributeName ] ) && $mphbAttributes[ $attributeName ]['hasDuplicates'] );
}

function mphb_attributes_prefix() {
	return 'mphb_ra_';
}

function mphb_get_attribute_names() {
	global $mphbAttributes;

	return ! empty( $mphbAttributes ) ? array_keys( $mphbAttributes ) : array();
}

function mphb_sanitize_attribute_name( $attributeName ) {
	return urldecode( sanitize_title( urldecode( $attributeName ) ) );
}

function mphb_clean_attribute_name( $attributeName ) {
	$attributeName = str_replace( '__trashed', '', $attributeName );
	return mphb_sanitize_attribute_name( $attributeName );
}

function mphb_attribute_title( $attributeName ) {
	global $mphbAttributes;

	if ( isset( $mphbAttributes[ $attributeName ] ) ) {
		return $mphbAttributes[ $attributeName ]['title'];
	} else {
		return '';
	}
}

function mphb_is_public_attribute( $attributeName ) {
	global $mphbAttributes;
	return ( isset( $mphbAttributes[ $attributeName ] ) && $mphbAttributes[ $attributeName ]['public'] );
}

function mphb_is_visible_attribute( $attributeName ) {
	global $mphbAttributes;
	return ( isset( $mphbAttributes[ $attributeName ] ) && $mphbAttributes[ $attributeName ]['visible'] );
}

function mphb_attribute_orderby( $attributeName ) {
	global $mphbAttributes;

	if ( isset( $mphbAttributes[ $attributeName ] ) ) {
		return $mphbAttributes[ $attributeName ]['orderby'];
	} else {
		return ( MPHB()->isWpSupportsTermmeta() ) ? 'custom' : 'name';
	}
}

/**
 * @param string $attributeName
 * @return string
 *
 * @global array $mphbAttributes
 *
 * @since 3.5.0
 */
function mphb_attribute_default_text( $attributeName ) {
	global $mphbAttributes;

	if ( isset( $mphbAttributes[ $attributeName ] ) ) {
		return $mphbAttributes[ $attributeName ]['default_text'];
	} else {
		return _x( '&mdash;', 'Not selected value in the search form.', 'motopress-hotel-booking' );
	}
}

/**
 * Note: this will reset order for all terms in range [1; oo) after first
 * reorder.
 *
 * @param int    $currentId
 * @param int    $nextId
 * @param string $taxonomyName
 */
function mphb_reorder_attributes( $currentId, $nextId, $taxonomyName ) {
	if ( ! MPHB()->isWpSupportsTermmeta() ) {
		return;
	}

	$terms = get_terms( $taxonomyName, 'menu_order=ASC&hide_empty=0' );

	if ( empty( $terms ) ) {
		return;
	}

	$index = 0;

	foreach ( $terms as $_term ) {
		$termId = intval( $_term->term_id );

		// Skip the current term, it has special cases for savings
		if ( $termId == $currentId ) {
			continue;
		}

		// If found $nextId, then use current index for current item and set
		// increased order for next item
		if ( ! is_null( $nextId ) && $termId == $nextId ) {
			$index++;
			mphb_set_attribute_order( $currentId, $index, $taxonomyName );
		}

		// Set new order for current term
		$index++;
		mphb_set_attribute_order( $termId, $index, $taxonomyName );
	}

	// If $nextId = null, then term with $currentId is the last element, don't
	// forget to set new order for it too
	if ( is_null( $nextId ) ) {
		$index++;
		mphb_set_attribute_order( $currentId, $index, $taxonomyName );
	}
}

function mphb_set_attribute_order( $termId, $index, $taxonomyName ) {
	$metaName = 'order_' . esc_attr( $taxonomyName );
	update_term_meta( $termId, $metaName, $index );
}
