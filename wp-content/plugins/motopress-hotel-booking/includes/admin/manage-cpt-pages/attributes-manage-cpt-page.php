<?php

namespace MPHB\Admin\ManageCPTPages;

class AttributesManageCPTPage extends ManageCPTPage {

	public function __construct( $postType, $atts = array() ) {
		parent::__construct( $postType, $atts );
		$this->description = __( 'Attributes let you define extra accommodation data, such as location or type. You can use these attributes in the search availability form as advanced search filters.', 'motopress-hotel-booking' );
	}

	protected function addActionsAndFilters() {
		parent::addActionsAndFilters();

		add_filter( 'post_row_actions', array( $this, 'filterRowActions' ) );
	}

	public function filterRowActions( $actions ) {
		// Prevent Quick Edit
		if ( $this->isCurrentPage() ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				unset( $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}

	public function filterColumns( $columns ) {
		$customColumns = array(
			'mphb_slug' => translate( 'Slug' ),
			// translators: Terms are variations for Attributes and bear no relation to the Terms and Conditions Page.
			'terms'     => __( 'Terms', 'motopress-hotel-booking' ),
		);

		// Set custom columns position before "DATE" column
		$offset  = array_search( 'date', array_keys( $columns ) );
		$columns = array_slice( $columns, 0, $offset, true ) + $customColumns + array_slice( $columns, $offset, count( $columns ) - 1, true );

		unset( $columns['date'] );

		return $columns;
	}

	public function renderColumns( $column, $postId ) {
		global $mphbAttributeTaxonomies;

		if ( ! in_array( $column, array( 'mphb_slug', 'terms' ) ) ) {
			return;
		}

		$originalId   = MPHB()->translation()->getOriginalId( $postId, $this->postType, false );
		$originalPost = ( ! is_null( $originalId ) ) ? get_post( $originalId ) : null;

		switch ( $column ) {
			case 'mphb_slug':
				$attributeName = '';

				if ( ! is_null( $originalPost ) ) {
					$attributeName = mphb_clean_attribute_name( $originalPost->post_name );
				}

				if ( ! empty( $attributeName ) ) {
					echo esc_html( $attributeName );

					if ( mphb_is_duplicate_attribute( $attributeName ) ) {
						$duplicateTaxonomy   = mphb_attribute_taxonomy_name( $attributeName );
						$duplicatesAttribute = $mphbAttributeTaxonomies[ $duplicateTaxonomy ]['title'];

						echo '<p class="notice notice-warning">' .
							sprintf(
								esc_html__( 'This attribute refers to non-unique taxonomy - %1$s - which was already registered with attribute %2$s.', 'motopress-hotel-booking' ),
								'<i>"' . esc_html( $duplicateTaxonomy ) . '"</i>',
								'<strong>' . esc_html( $duplicatesAttribute ) . '</strong>'
							) .
							'</p>';
					}
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo static::EMPTY_VALUE_PLACEHOLDER;
				}

				break;

			case 'terms':
				if ( is_null( $originalId ) ) {
					// translators: Terms are variations for Attributes and bear no relation to the Terms and Conditions Page.
					esc_html_e( 'Please add attribute in default language to configure terms.', 'motopress-hotel-booking' );
					break;
				} elseif ( $this->isCurrentTrashPage() ) {
					// translators: Terms are variations for Attributes and bear no relation to the Terms and Conditions Page.
					esc_html_e( 'You cannot manage terms of trashed attributes.', 'motopress-hotel-booking' );
					break;
				}

				$attributeName = mphb_sanitize_attribute_name( $originalPost->post_name );
				$terms         = MPHB()->getAttributesPersistence()->getTermsIdTitleList( $attributeName );

				if ( ! empty( $terms ) ) {
					echo esc_html( implode( ', ', $terms ) );
				} else {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo static::EMPTY_VALUE_PLACEHOLDER;
				}

				// Show "Configure terms" link
				$configureTermsUrl = add_query_arg(
					array(
						'taxonomy'  => mphb_attribute_taxonomy_name( $attributeName ),
						'post_type' => MPHB()->postTypes()->roomType()->getPostType(),
					),
					admin_url( 'edit-tags.php' )
				);

				echo '<br />';
				echo '<a href="' . esc_url( $configureTermsUrl ) . '">' . esc_html__( 'Configure terms', 'motopress-hotel-booking' ) . '</a>';

				break;
		}
	}

}
