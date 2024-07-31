<?php

namespace MPHB\Admin\ManageCPTPages;

use \MPHB\Admin\AdminPage;

class ManageCPTPage extends AdminPage {

	const EMPTY_VALUE_PLACEHOLDER = '&#8212;';

	protected $postType;

	/**
	 * Description that output under page heading
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Custom actions that output just after the page heading
	 *
	 * @var array [%URL% => %Label%]
	 */
	protected $pageActions = array();

	public function __construct( $postType, $atts = array() ) {

		parent::__construct();

		$this->postType = $postType;

		$this->addActionsAndFilters();
	}

	protected function addActionsAndFilters() {
		add_filter( "manage_{$this->postType}_posts_columns", array( $this, 'filterColumns' ) );
		add_filter( "manage_edit-{$this->postType}_sortable_columns", array( $this, 'filterSortableColumns' ) );
		add_action( "manage_{$this->postType}_posts_custom_column", array( $this, 'renderColumns' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );

		// views_{screen->id} filter
		add_filter( "views_edit-{$this->postType}", array( $this, 'filterViews' ) );
		add_action( 'admin_footer', array( $this, 'addDescriptionScript' ) );
	}

	/**
	 * @since 3.7.3
	 * @since 3.7.3 added new action - "mphb_enqueue_manage_{$postType}s_scripts", where $postType is the post type without prefix "mphb_".
	 */
	public function enqueueScripts() {
		if ( $this->isCurrentPage() ) {
			do_action( 'mphb_enqueue_manage_' . mphb_unprefix( $this->postType ) . 's_scripts', $this->isCurrentTrashPage() );
		}
	}

	public function filterColumns( $columns ) {
		return $columns;
	}

	public function filterSortableColumns( $columns ) {
		return $columns;
	}

	public function renderColumns( $column, $postId ) {
		// Do nothing.
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
		global $typenow, $pagenow;
		return is_admin() && $pagenow === 'edit.php' && $typenow === $this->postType;
	}

	public function isCurrentTrashPage() {
		return $this->isCurrentPage() && isset( $_GET['post_status'] ) && $_GET['post_status'] == 'trash';
	}

	/**
	 *
	 * @param array $atts
	 * @return string
	 */
	public function getUrl( $atts = array() ) {

		$url = admin_url( 'edit.php' );

		$defaultAtts = array(
			'post_type' => $this->postType,
		);

		$atts = array_merge( $defaultAtts, $atts );

		return add_query_arg( $atts, $url );
	}

	public function addDescriptionScript() {
		if ( $this->isCurrentPage() && ! empty( $this->description ) ) {
			?>
			<script type="text/javascript">
				jQuery(function () {
					var pageSettings = <?php echo json_encode( array( 'description' => $this->description ) ); ?>;
					var description = jQuery('<p />', {'html': pageSettings.description});

					jQuery('#wpbody-content > .wrap > ul.subsubsub').first().before(description);
				});
			</script>
			<?php
		}
	}

}
