<?php

namespace MPHB\Admin\EditCPTPages;

class EditCPTPage {

	const EMPTY_VALUE_PLACEHOLDER = '&#8212;';

	protected $capability;
	protected $postType;

	/**
	 *
	 * @var \MPHB\Admin\Groups\MetaBoxGroup[]
	 */
	protected $fieldGroups = array();

	public function __construct( $postType, $fieldGroups = array(), $atts = array() ) {

		$this->postType = $postType;

		$fieldGroups = apply_filters( 'mphb_edit_page_field_groups', $fieldGroups, $postType );

		$this->fieldGroups = $fieldGroups;

		$defaultsArgs = array(
			'capability' => 'edit_post',
		);

		$atts = array_merge( $defaultsArgs, $atts );

		$this->capability = $atts['capability'];

		$this->addActions();
	}

	protected function addActions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
		add_action( 'save_post', array( $this, 'saveMetaBoxes' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'customizeMetaBoxes' ) );
		add_action( "mphb_register_{$this->postType}_metaboxes", array( $this, 'registerMetaBoxes' ) );
	}

	/**
	 * @since 3.7.3 added new action - "mphb_enqueue_edit_{$postType}_scripts", where $postType is the post type without prefix "mphb_".
	 */
	public function enqueueAdminScripts() {
		if ( $this->isCurrentPage() ) {
			do_action( 'mphb_enqueue_edit_' . mphb_unprefix( $this->postType ) . '_scripts', $this->isCurrentAddNewPage() );

			MPHB()->getAdminScriptManager()->enqueue();
		}
	}

	public function registerMetaBoxes() {
		foreach ( $this->fieldGroups as $group ) {
			$group->register();
		}
	}

	public function customizeMetaBoxes() {
	}

	/**
	 *
	 * @param int      $postId
	 * @param \WP_Post $post
	 * @param bool     $update
	 * @return bool
	 */
	public function saveMetaBoxes( $postId, $post, $update ) {

		if ( ! $this->canSaveMetaBoxes( $postId, $post, $update ) ) {
			return false;
		}

		remove_action( 'save_post', array( $this, 'saveMetaBoxes' ) );

		foreach ( $this->fieldGroups as $metaGroup ) {
			$metaGroup->setPostId( $postId );
			$metaGroup->save();
		}

		return true;
	}

	/**
	 *
	 * @param int     $postId
	 * @param WP_Post $post
	 * @param bool    $update
	 * @return bool
	 */
	protected function canSaveMetaBoxes( $postId, $post, $update ) {

		if ( empty( $postId ) || empty( $post ) ) {
			return false;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return false;
		}

		// Check the post being saved == the $postId to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || intval( $_POST['post_ID'] ) != $postId ) {
			return false;
		}

		if ( ! ( $post->post_type == $this->postType && $this->isCurrentPage() && current_user_can( $this->capability, $postId ) ) ) {
			return false;
		}

		return true;
	}

	public function getPostType() {
		return $this->postType;
	}

	public function getUrl( $atts = array(), $addNew = false ) {

		$url = $addNew ? admin_url( 'post-new.php' ) : admin_url( 'post.php' );

		$defaultAtts = array(
			'post_type' => $this->postType,
		);

		$atts = array_merge( $defaultAtts, $atts );

		return add_query_arg( $atts, $url );
	}

	public function isCurrentAddNewPage() {
		global $typenow, $pagenow;
		return is_admin() && $typenow === $this->postType && $pagenow === 'post-new.php';
	}

	public function isCurrentEditPage() {
		global $typenow, $pagenow;
		return is_admin() && $typenow === $this->postType && $pagenow === 'post.php';
	}

	public function isCurrentPage() {
		return $this->isCurrentAddNewPage() || $this->isCurrentEditPage();
	}

	public function getAttsFromRequest( $request = null ) {
		if ( is_null( $request ) ) {
			$request = $_REQUEST;
		}
		$atts = array();
		foreach ( $this->fieldGroups as $group ) {
			$atts = array_merge( $atts, $group->getAttsFromRequest( $request ) );
		}
		return $atts;
	}
}
