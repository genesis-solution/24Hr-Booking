<?php

namespace MPHB\Admin\ManageTaxPages;

use \MPHB\Views;
use \MPHB\Entities;

class ManageTaxPage {

	const EMPTY_VALUE_PLACEHOLDER = '&#8212;';

	protected $taxType;

	/**
	 * Description that output under page heading
	 *
	 * @var string
	 */
	protected $description;

	public function __construct( $taxType, $atts = array() ) {

		$this->taxType = $taxType;

		$this->addActionsAndFilters();
	}

	protected function addActionsAndFilters() {
		add_filter( "manage_edit-{$this->taxType}_columns", array( $this, 'filterColumns' ) );
		add_filter( "manage_edit-{$this->taxType}_sortable_columns", array( $this, 'filterSortableColumns' ) );
		add_action( "manage_{$this->taxType}_custom_column", array( $this, 'renderColumns' ), 10, 3 );

		add_action( 'admin_footer', array( $this, 'addDescriptionScript' ) );
	}

	public function filterColumns( $columns ) {
		return $columns;
	}

	public function filterSortableColumns( $columns ) {
		return $columns;
	}

	public function renderColumns( $content, $column_name, $term_id ) {
		return $content;
	}

	/**
	 *
	 * @param array $views
	 * @return array
	 */
	public function filterViews( $views ) {
		return $views;
	}

	public function isCurrentPage() {
		global $pagenow, $taxonomy;
		return is_admin() && $pagenow === 'edit-tags.php' && $taxonomy === $this->taxType;
	}

	/**
	 *
	 * @param array $atts
	 * @return string
	 */
	public function getUrl( $atts = array() ) {

		$url = admin_url( 'edit-tags.php' );

		$defaultAtts = array(
			'taxonomy' => $this->taxType,
		);

		$atts = array_merge( $defaultAtts, $atts );

		return add_query_arg( $atts, $url );
	}

	public function addDescriptionScript() {
		if ( $this->isCurrentPage() ) {
			if ( ! empty( $this->description ) ) {
				?>
				<script type="text/javascript">
					(function( $ ) {
						$( function() {

							var addDescription = function() {
								var description = $( '<p />', {
									'html': '<?php echo esc_js( $this->description ); ?>',
								} );

								$( '#wpbody-content>.wrap>.search-form' ).first().before( description );
							}

							addDescription();

						} );
					})( jQuery );
				</script>
				<?php
			}
		}
	}

}
