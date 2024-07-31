<?php
/**
 * Available variables
 * - WP_Term[] $facilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( ! empty( $facilities ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderFacilitiesListItemOpen              - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderFacilitiesTitle                     - 20
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderOpen   - 30
	 */
	do_action( 'mphb_render_loop_room_type_before_facilities' );

	$facilities = array_map(
		function( $facility ) {

			$facilityLink = get_term_link( $facility );

			if ( is_wp_error( $facilityLink ) ) {
				  $facilityLink = '#';
			}

			$facilityLink = sprintf( '<a href="%s">%s</a>', esc_url( $facilityLink ), $facility->name );
			$html         = '<span class="' . esc_attr( 'facility-' . $facility->slug ) . '">' . $facilityLink . '</span>';

			return $html;
		},
		$facilities
	);

	$itemsDelimeter = apply_filters( 'mphb_room_type_facilities_delimiter', ', ' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo ' ' . join( $itemsDelimeter, $facilities );

	/**
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAttributesListItemValueHolderClose  - 10
	 * @hooked \MPHB\Views\LoopRoomTypeView::_renderAdultsListItemClose                 - 20
	 */
	do_action( 'mphb_render_loop_room_type_after_facilities' );
	?>

	<?php
endif;
