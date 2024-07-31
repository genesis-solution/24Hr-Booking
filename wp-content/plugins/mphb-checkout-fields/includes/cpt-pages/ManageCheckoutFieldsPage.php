<?php

namespace MPHB\CheckoutFields\CPTPages;

use MPHB\Admin\ManageCPTPages\ManageCPTPage;
use MPHB\CheckoutFields\CheckoutFieldsHelper;
use MPHB\CheckoutFields\Plugin;

/**
 * @since 1.0
 */
class ManageCheckoutFieldsPage extends ManageCPTPage {

	const PLACEHOLDER = '<span class="mphb-placeholder" aria-hidden="true">&#8212;</span>';

	/**
	 * The current view is "All" and there no filters or other manipulations in
	 * the query.
	 *
	 * @var bool
	 */
	protected $isBaseRequest = true;

	protected function addActionsAndFilters() {

		parent::addActionsAndFilters();
		add_action( 'load-edit.php', array( $this, 'onLoad' ) );
	}

	public function onLoad() {

		global $typenow;

		if ( $typenow === $this->postType ) {

			$this->isBaseRequest = $this->isBaseRequest();

			add_filter( 'pre_get_posts', array( $this, 'filterPostsOrder' ) );
			add_filter( 'post_class', array( $this, 'filterRowClasses' ), 10, 3 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );

			do_action(
				'mphb_manage_checkout_fields_page_loaded',
				array(
					'is_base_request' => $this->isBaseRequest,
				)
			);
		}
	}

	/**
	 * Determine if the current view is the "All" view.
	 *
	 * @see \WP_Posts_List_Table::is_base_request()
	 *
	 * @param string|null $postType Optional. NULL by default (the type of checkout fields).
	 */
	private function isBaseRequest( $postType = null ): bool {

		global $typenow;

		$unallowedArgs = array(
			'filter_action' => true, // "Filter" button clicked
			'author'        => true, // Filter by post author
			'm'             => true, // Filter by date
			's'             => true,  // Custom search
		);

		$unallowedVars = array_intersect_key( $_GET, $unallowedArgs );

		$isBase = count( $unallowedVars ) == 0;

		// Filter by post status
		if ( isset( $_GET['post_status'] ) && $_GET['post_status'] !== 'all' ) {
			$isBase = false;
		}

		// It's not a base request anymore when requesting posts for all languages
		if ( isset( $_GET['lang'] ) && $_GET['lang'] === 'all' ) {
			$isBase = false;
		}

		// Add additional check of the post type
		if ( $isBase ) {
			if ( is_null( $postType ) ) {
				$isBase = Plugin::getInstance()->getCheckoutFieldsPostType()->getPostType() === $typenow;
			} else {
				$isBase = $postType === $typenow;
			}
		}

		return $isBase;
	}

	/**
	 * @param \WP_Query $query
	 * @return \WP_Query
	 */
	public function filterPostsOrder( $query ) {

		if ( $this->isBaseRequest ) {
			// 'ID' => 'ASC' is important for sites with WPML. With "DESC" order
			// translated fields may override the default ones
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				)
			);
		}

		return $query;
	}

	/**
	 * @param string[] $classes An array of post class names.
	 * @param string[] $_ An array of additional class names added to the post.
	 * @param int      $postId The post ID.
	 * @return string[]
	 */
	public function filterRowClasses( $classes, $_, $postId ) {

		$enabled = get_post_meta( $postId, 'mphb_cf_enabled', true );

		if ( $enabled === '1' ) {
			$classes[] = 'mphb-enabled-checkout-field';
		} else {
			$classes[] = 'mphb-disabled-checkout-field';
		}

		return $classes;
	}

	public function enqueueAssets() {

		if ( $this->isBaseRequest ) {

			wp_enqueue_style( 'mphb-admin-css' );

			wp_enqueue_style(
				'mphb-manage-checkout-fields-styles',
				CheckoutFieldsHelper::getUrlToFile( 'assets/css/manage-checkout-fields-page.css' ),
				array(),
				Plugin::getInstance()->getPluginVersion()
			);

			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_script(
				'mphb-manage-checkout-fields-scripts',
				CheckoutFieldsHelper::getUrlToFile( 'assets/js/manage-checkout-fields-page.js' ),
				array( 'jquery' ),
				Plugin::getInstance()->getPluginVersion(),
				true
			);

			wp_localize_script(
				'mphb-manage-checkout-fields-scripts',
				'MPHBCheckoutFields',
				array(
					'nonces' => array(
						'reorder_posts' => wp_create_nonce( 'mphb_cf_reorder_posts' ),
					),
				)
			);
		}
	}

	public function filterColumns( $columns ) {

		$newColumns = array( 'cb' => $columns['cb'] );

		if ( $this->isBaseRequest ) {

			$newColumns['column-handle'] = esc_html__( 'Order', 'mphb-checkout-fields' );
		}

		$newColumns += array(
			'title'       => $columns['title'],
			'name'        => esc_html__( 'Name', 'mphb-checkout-fields' ),
			'type'        => esc_html__( 'Type', 'mphb-checkout-fields' ),
			'placeholder' => esc_html__( 'Placeholder', 'mphb-checkout-fields' ),
			'required'    => esc_html__( 'Required', 'mphb-checkout-fields' ),
			'enabled'     => esc_html__( 'Enabled', 'mphb-checkout-fields' ),
			'email-tag'   => esc_html__( 'Email Tag', 'mphb-checkout-fields' ),
		);

		return $newColumns;
	}

	public function renderColumns( $column, $postId ) {

		$entity = Plugin::getInstance()->getCheckoutFieldRepository()->findById( $postId );

		if ( is_null( $entity ) ) {

			echo self::PLACEHOLDER;
			return;
		}

		switch ( $column ) {

			case 'column-handle':
				break;

			case 'name':
			case 'type':
			case 'placeholder':
				$value = $entity->$column;

				if ( empty( $value ) ) {
					echo self::PLACEHOLDER;
				} else {
					echo esc_html( $value );
				}

				break;

			case 'enabled':
			case 'required':
				$value = $column == 'enabled' ? $entity->isEnabled : $entity->isRequired;
				echo $value ? '<span class="dashicons dashicons-yes"></span>' : self::PLACEHOLDER;

				break;

			case 'email-tag':
				echo ! empty( $entity->name ) ? "%customer_{$entity->name}%" : self::PLACEHOLDER;
				break;
		}
	}
}
