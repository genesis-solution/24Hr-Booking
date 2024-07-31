<?php
/**
 * Available varialbes
 * - bool $isShowTitle
 * - bool $isShowImage
 * - bool $isShowExcerpt
 * - bool $isShowDetails
 * - bool $isShowPrice
 * - bool $isShowBookButton
 * - string $price
 * - WP_Term[] $categories
 * - WP_Term[] $facilities
 * - string $view
 * - string $size
 * - string $bedType
 * - string $adults
 * - string $children
 *
 * @version 2.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
$wrapperClass = apply_filters( 'mphb_widget_rooms_item_class', join( ' ', mphb_tmpl_get_filtered_post_class( 'mphb-room-type milenia-entity' ) ) );
?>

<article class="<?php echo esc_attr( $wrapperClass ); ?>">
	<?php do_action( 'mphb_widget_rooms_item_top' ); ?>

	<?php if ( $isShowImage && has_post_thumbnail() ) : ?>
		<div class="milenia-entity-media">
			<a href="<?php esc_url( the_permalink() ); ?>" class="milenia-ln--independent">
				<?php
				the_post_thumbnail('thumbnail');
				?>
			</a>
		</div>
	<?php endif; ?>

	<div class="milenia-entity-content">
		<div class="milenia-entity-header">
			<?php if ( $isShowPrice && mphb_tmpl_has_room_type_default_price() ) : ?>
				<div class="milenia-entity-meta">
					<div class="mphb-widget-room-type-price">
						<?php esc_html_e('From ', 'milenia'); mphb_tmpl_the_room_type_default_price(); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $isShowTitle ) : ?>
				<h2 class="milenia-entity-title">
					<a href="<?php esc_url( the_permalink() ); ?>">
						<?php the_title(); ?>
					</a>
				</h2>
			<?php endif; ?>
		</div>

		<?php if ( $isShowExcerpt && has_excerpt() ) : ?>
			<div class="milenia-entity-body">
				<?php the_excerpt(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $isShowDetails ) : ?>
			<ul class="mphb-widget-room-type-attributes">
				<?php if ( MPHB()->settings()->main()->isAdultsAllowed() ) : ?>
					<li class="mphb-room-type-adults">
						<span class="mphb-attribute-title mphb-adults-title"><?php
							if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
								_e( 'Adults:', 'milenia' );
							} else {
								_e( 'Guests:', 'milenia' );
							}
						?></span>
						<span class="mphb-attribute-value">
							<?php echo esc_html($adults); ?>
						</span>
					</li>
				<?php endif; ?>
				<?php if ( $children != 0 && MPHB()->settings()->main()->isChildrenAllowed() ) : ?>
					<li class="mphb-room-type-children">
						<span class="mphb-attribute-title mphb-children-title"><?php _e( 'Children:', 'milenia' ); ?></span>
						<span class="mphb-attribute-value">
							<?php echo esc_html($children); ?>
						</span>
					</li>
				<?php endif; ?>
				<?php if ( !empty( $categories ) ) : ?>
					<li class="mphb-room-type-categories">
						<span class="mphb-attribute-title mphb-categories-title"><?php _e( 'Categories:', 'milenia' ); ?></span>
						<span class="mphb-attribute-value">
							<?php
							$categories = array_map( function( $category ) {

								$categoryLink = get_term_link( $category );

								if ( is_wp_error( $categoryLink ) ) {
									$categoryLink = '#';
								}

								return sprintf( '<a href="%s">%s</a>', $categoryLink, $category->name );
							}, $categories );

							echo ' ' . join( ', ', $categories );
							?>
						</span>
					</li>
				<?php endif; ?>
				<?php if ( !empty( $facilities ) ) : ?>
					<li class="mphb-room-type-facilities">
						<span class="mphb-attribute-title mphb-facilities-title"><?php _e( 'Amenities:', 'milenia' ); ?></span>
						<span class="mphb-attribute-value">
							<?php
							$facilities = array_map( function( $facility ) {

								$facilityLink = get_term_link( $facility );

								if ( is_wp_error( $facilityLink ) ) {
									$facilityLink = '#';
								}

								return sprintf( '<a href="%s">%s</a>', $facilityLink, $facility->name );
							}, $facilities );

							echo ' ' . join( ', ', $facilities );
							?>
						</span>
					</li>
				<?php endif; ?>
				<?php if ( !empty( $view ) ) : ?>
					<li class="mphb-room-type-view">
						<span class="mphb-attribute-title mphb-view-title"><?php _e( 'View:', 'milenia' ); ?></span>
						<span class="mphb-attribute-value">
							<?php echo esc_html($view); ?>
						</span>
					</li>
				<?php endif; ?>
				<?php if ( !empty( $size ) ) : ?>
					<li class="mphb-room-type-size">
						<span class="mphb-attribute-title mphb-size-title"><?php _e( 'Size:', 'milenia' ); ?></span>
						<span class="mphb-attribute-value">
							<?php echo esc_html($size); ?>
						</span>
					</li>
				<?php endif; ?>
				<?php if ( !empty( $bedType ) ) : ?>
					<li class="mphb-room-type-bed-type">
						<span class="mphb-attribute-title mphb-bed-type-title"><?php _e( 'Bed Type:', 'milenia' ); ?></span>
						<span class="mphb-attribute-value">
							<?php echo esc_html($bedType); ?>
						</span>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $isShowBookButton ) : ?>
			<div class="mphb-widget-room-type-book-button">
				<?php mphb_tmpl_the_loop_room_type_book_button_form(esc_html__('Book Now', 'milenia')); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'mphb_widget_rooms_item_bottom' ); ?>
</article>
