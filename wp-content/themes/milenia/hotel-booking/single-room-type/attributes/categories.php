<?php
/**
 * Available variables
 * - WP_Term[] $categories
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( !empty( $categories ) ) : ?>

	<?php
	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderCategoriesListItemOpen			- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderCategoriesTitle					- 20
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderOpen	- 30
	 */
	do_action( 'mphb_render_single_room_type_before_categories' );

	$categories = array_map( function( $category ) {

		$categoryLink = get_term_link( $category );

		if ( is_wp_error( $categoryLink ) ) {
			$categoryLink = '#';
		}

		return sprintf( '<a href="%s">%s</a>', esc_url( $categoryLink ), $category->name );
	}, $categories );

	echo ' ' . join(', ', $categories);

	/**
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderAttributesListItemValueHolderClose	- 10
	 * @hooked \MPHB\Views\SingleRoomTypeView::_renderCategoriesListItemClose				- 20
	 */
	do_action( 'mphb_render_single_room_type_after_categories' );
	?>

<?php endif; ?>