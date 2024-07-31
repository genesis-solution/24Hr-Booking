<?php

/**
 * Available variables
 * - WP_Term[] $facilities
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( !empty( $facilities ) ) : ?>

	<?php

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderFacilitiesListItemOpen			- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderFacilitiesTitle					- 20
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderOpen	- 30
	 */
	do_action( 'mphb_render_single_room_type_before_facilities' );

	$facilities = array_map( function( $facility ) {

		$facilityLink = get_term_link( $facility );

		if ( is_wp_error( $facilityLink ) ) {
			$facilityLink = '#';
		}

		return sprintf( '<a href="%s">%s</a>', esc_url( $facilityLink ), $facility->name );
	}, $facilities );

	echo ' ' . join( ', ', $facilities );

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderClose	- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderFacilitiesListItemClose				- 20
	 */
	do_action( 'mphb_render_single_room_type_after_facilities' );
	?>

<?php endif; ?>