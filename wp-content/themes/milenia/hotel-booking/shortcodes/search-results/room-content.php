<?php
/**
 *
 * Avaialable variables
 * - DateTime $checkInDate
 * - DateTime $checkOutDate
 * - int $adults
 * - int $children
 * - bool $isShowGallery
 * - bool $isShowImage
 * - bool $isShowTitle
 * - bool $isShowExcerpt
 * - bool $isShowDetails
 * - bool $isShowPrice
 * - bool $isShowViewButton
 * - bool $isShowBookButton
 *
 * @version 2.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$wrapperClass = apply_filters( 'mphb_sc_rooms_item_wrapper_class', join( ' ', mphb_tmpl_get_filtered_post_class( 'mphb-room-type milenia-entity' ) ) );
?>

<div class="<?php echo esc_attr( $wrapperClass ); ?>">
	<?php do_action( 'mphb_sc_search_results_room_top' ); ?>

	<?php
	if ( $isShowGallery && mphb_tmpl_has_room_type_gallery() ) {
		/**
		 * @hooked \MPHB\Views\LoopRoomTypeView::renderGallery - 10
		 */
		do_action( 'mphb_sc_search_results_render_gallery' );
	} else if ( $isShowImage && has_post_thumbnail() ) {
		/**
		 * @hooked \MPHB\Views\LoopRoomTypeView::renderFeaturedImage - 10
		 */
		do_action( 'mphb_sc_search_results_render_image' );
	} ?>

	<div class="milenia-entity-content">
		<header class="milenia-entity-header">
			<?php if ( $isShowPrice ) { ?>
				<div class="milenia-entity-meta">
					<?php
						/**
						 * @hooked \MPHB\Views\LoopRoomTypeView::renderPrice - 10
						 */
						do_action( 'mphb_sc_search_results_render_price', $checkInDate, $checkOutDate );
					} ?>
				</div>

			<?php
				if ( $isShowTitle ) {
					/**
					 * @hooked \MPHB\Views\LoopRoomTypeView::renderTitle - 10
					 */
					do_action( 'mphb_sc_search_results_render_title' );
				}
			?>
		</header>

		<?php
			if ( $isShowExcerpt ) {
				/**
				 * @hooked \MPHB\Views\LoopRoomTypeView::renderExcerpt - 10
				 */
				do_action( 'mphb_sc_search_results_render_excerpt' );
			}

			if ( $isShowDetails ) {
				/**
				 * @hooked \MPHB\Views\LoopRoomTypeView::renderAttributes - 10
				 */
				do_action( 'mphb_sc_search_results_render_details' );
			}
		?>

		<footer class="milenia-entity-footer">
			<?php
				if ( $isShowViewButton ) {
					/**
					 * @hooked \MPHB\Views\LoopRoomTypeView::renderViewDetailsButton - 10
					 */
					do_action( 'mphb_sc_search_results_render_view_button' );
				}

				if ( $isShowBookButton ) {
					/**
					 * @hooked \MPHB\Views\LoopRoomTypeView::renderBookButton - 10
					 */
					do_action( 'mphb_sc_search_results_render_book_button' );
				}

				do_action( 'mphb_sc_search_results_after_info' );
			?>
		</footer>
	</div>
	<?php do_action( 'mphb_sc_search_results_room_bottom' ); ?>
</div>
