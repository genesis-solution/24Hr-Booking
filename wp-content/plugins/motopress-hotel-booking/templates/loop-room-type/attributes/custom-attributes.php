<?php

/**
 * Available variables
 * - array $attributes [%Attribute name% => [%ID% => %Term title%]]
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $attributes ) ) {

	foreach ( $attributes as $attributeName => $terms ) {

		if ( ! mphb_is_visible_attribute( $attributeName ) ) {
			continue;
		}

		/**
		 * @hooked \MPHB\Views\LoopRoomTypeView::_renderCustomAttributesListItemOpen        - 10
		 * @hooked \MPHB\Views\LoopRoomTypeView::_renderCustomAttributesTitle               - 20
		 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen   - 30
		 */
		do_action( 'mphb_render_loop_room_type_before_custom_attribute', $attributeName );

		$isPublic     = mphb_is_public_attribute( $attributeName );
		$taxonomyName = mphb_attribute_taxonomy_name( $attributeName );

		$items = array();

		foreach ( $terms as $termId => $termTitle ) {
			$term = get_term( $termId, $taxonomyName );
			// In some cases $term->slug != sanitize_title( $termTitle )
			$termSlug = ( $term && ! is_wp_error( $term ) ) ? $term->slug : urldecode( sanitize_title( $termTitle ) );
			$termUrl  = ( $isPublic ) ? get_term_link( $termId, $taxonomyName ) : '';

			if ( $termUrl && ! is_wp_error( $termUrl ) ) {
				$termHtml = '<a href="' . esc_url( $termUrl ) . '">' . esc_html( $termTitle ) . '</a>';
			} else {
				$termHtml = esc_html( $termTitle );
			}

			$termHtml = '<span class="' . esc_attr( $attributeName . '-' . $termSlug ) . '">' . $termHtml . '</span>';

			$items[] = $termHtml;
		}

		$itemsDelimeter = apply_filters( 'mphb_room_type_user_attributes_delimiter', ', ' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ' ', join( $itemsDelimeter, $items );

		/**
		 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose  - 10
		 * @hooked \MPHB\Views\LoopRoomTypeView::_renderCustomAttributesListItemClose       - 20
		 */
		do_action( 'mphb_render_loop_room_type_after_custom_attribute' );

	}
}
