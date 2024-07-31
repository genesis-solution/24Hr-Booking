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
 * - array $attributes [%Attribute name% => [%ID% => %Term title%]]
 * - string $view
 * - string $size
 * - string $sizeNumber (since 3.6.1)
 * - string $bedType
 * - string $adults
 * - string $children
 * - int $totalCapacity (since 3.7.2)
 *
 * @version 2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( post_password_required() ) {
	$isShowImage = $isShowDetails = $isShowPrice = $isShowBookButton = false;
}
$wrapperClass = apply_filters( 'mphb_widget_rooms_item_class', join( ' ', mphb_tmpl_get_filtered_post_class( 'mphb-room-type' ) ) );
?>
<div class="<?php echo esc_attr( $wrapperClass ); ?>">

	<?php do_action( 'mphb_widget_rooms_item_top' ); ?>

	<?php if ( $isShowImage && has_post_thumbnail() ) : ?>
		<div class="mphb-widget-room-type-featured-image">
			<a href="<?php esc_url( the_permalink() ); ?>">
				<?php
				the_post_thumbnail(
					apply_filters( 'mphb_widget_rooms_thumbnail_size', 'post-thumbnail' )
				);
				?>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( $isShowTitle ) : ?>
		<div class="mphb-widget-room-type-title">
			<a href="<?php esc_url( the_permalink() ); ?>">
				<?php the_title(); ?>
			</a>
		</div>
	<?php endif; ?>

	<?php if ( $isShowExcerpt && has_excerpt() ) : ?>
		<div class="mphb-widget-room-type-description">
			<?php the_excerpt(); ?>
		</div>
	<?php endif; ?>

	<?php if ( $isShowDetails ) : ?>
		<ul class="mphb-widget-room-type-attributes">
			<?php if ( MPHB()->settings()->main()->isAdultsAllowed() ) : ?>
				<?php if ( ! empty( $totalCapacity ) ) { ?>
					<li class="mphb-room-type-total-capacity">
						<span class="mphb-attribute-title mphb-total-capacity-title"><?php esc_html_e( 'Guests:', 'motopress-hotel-booking' ); ?></span>
						<span class="mphb-attribute-value">
							<?php echo esc_html( $totalCapacity ); ?>
						</span>
					</li>
				<?php } else { ?>
					<li class="mphb-room-type-adults">
						<span class="mphb-attribute-title mphb-adults-title">
						<?php
						if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
							esc_html_e( 'Adults:', 'motopress-hotel-booking' );
						} else {
							esc_html_e( 'Guests:', 'motopress-hotel-booking' );
						}
						?>
						</span>
						<span class="mphb-attribute-value">
							<?php echo esc_html( $adults ); ?>
						</span>
					</li>
				<?php } ?>
			<?php endif; ?>
			<?php if ( $children != 0 && MPHB()->settings()->main()->isChildrenAllowed() && empty( $totalCapacity ) ) : ?>
				<li class="mphb-room-type-children">
					<span class="mphb-attribute-title mphb-children-title"><?php esc_html_e( 'Children:', 'motopress-hotel-booking' ); ?></span>
					<span class="mphb-attribute-value">
						<?php echo esc_html( $children ); ?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( ! empty( $categories ) ) : ?>
				<li class="mphb-room-type-categories">
					<span class="mphb-attribute-title mphb-categories-title"><?php esc_html_e( 'Categories:', 'motopress-hotel-booking' ); ?></span>
					<span class="mphb-attribute-value">
						<?php
						$categories = array_map(
							function( $category ) {

								$categoryLink = get_term_link( $category );

								if ( is_wp_error( $categoryLink ) ) {
									  $categoryLink = '#';
								}

								$categoryLink = sprintf( '<a href="%s">%s</a>', esc_url( $categoryLink ), $category->name );
								$html         = '<span class="' . esc_attr( 'category-' . $category->slug ) . '">' . $categoryLink . '</span>';

								return $html;
							},
							$categories
						);

						$itemsDelimeter = apply_filters( 'mphb_room_type_categories_delimiter', ', ' );

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo ' ' . join( $itemsDelimeter, $categories );
						?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( ! empty( $facilities ) ) : ?>
				<li class="mphb-room-type-facilities">
					<span class="mphb-attribute-title mphb-facilities-title"><?php esc_html_e( 'Amenities:', 'motopress-hotel-booking' ); ?></span>
					<span class="mphb-attribute-value">
						<?php
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
						?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( ! empty( $view ) ) : ?>
				<li class="mphb-room-type-view">
					<span class="mphb-attribute-title mphb-view-title"><?php esc_html_e( 'View:', 'motopress-hotel-booking' ); ?></span>
					<span class="mphb-attribute-value">
						<?php echo esc_html( $view ); ?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( ! empty( $sizeNumber ) ) : ?>
				<li class="mphb-room-type-size">
					<span class="mphb-attribute-title mphb-size-title"><?php esc_html_e( 'Size:', 'motopress-hotel-booking' ); ?></span>
					<span class="mphb-attribute-value">
						<?php echo esc_html( $size ); ?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( ! empty( $bedType ) ) : ?>
				<li class="mphb-room-type-bed-type">
					<span class="mphb-attribute-title mphb-bed-type-title"><?php esc_html_e( 'Bed Type:', 'motopress-hotel-booking' ); ?></span>
					<span class="mphb-attribute-value">
						<?php echo esc_html( $bedType ); ?>
					</span>
				</li>
			<?php endif; ?>
			<?php if ( ! empty( $attributes ) ) : ?>
				<?php foreach ( $attributes as $attributeName => $terms ) { ?>
					<?php
					if ( ! mphb_is_visible_attribute( $attributeName ) ) {
						continue;}
					?>
					<li class="<?php echo esc_attr( 'mphb-room-type-' . $attributeName . ' mphb-room-type-custom-attribute' ); ?>">
						<span class="mphb-attribute-title <?php echo esc_attr( 'mphb-' . $attributeName . '-title' ); ?>"><?php echo esc_html( mphb_attribute_title( $attributeName ) ); ?>:</span>
						<span class="mphb-attribute-value">
							<?php
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
							?>
						</span>
					</li>
				<?php } ?>
			<?php endif; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $isShowPrice && mphb_tmpl_has_room_type_default_price() ) : ?>
		<div class="mphb-widget-room-type-price">
			<span><?php esc_html_e( 'Prices start at:', 'motopress-hotel-booking' ); ?></span>
			<?php mphb_tmpl_the_room_type_default_price(); ?>
		</div>
	<?php endif; ?>

	<?php if ( $isShowBookButton ) : ?>
		<div class="mphb-widget-room-type-book-button">
			<?php mphb_tmpl_the_loop_room_type_book_button_form(); ?>
		</div>
	<?php endif; ?>

	<?php do_action( 'mphb_widget_rooms_item_bottom' ); ?>

</div>
