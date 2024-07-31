<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;

class AttributesCPT extends EditableCPT {

	protected $postType = 'mphb_room_attribute';

	protected function addActions() {

		parent::addActions();

		if ( MPHB()->isWpSupportsTermmeta() ) {
			add_filter( 'terms_clauses', array( $this, 'supportAttributesMenuOrder' ), 99, 3 );
		}

		// Import listener for default WordPress importer
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			add_action( 'import_start', array( $this, 'registerImportingTaxonomy' ) );
		}
		// Import listener for One Click Demo Import
		add_filter( 'wxr_importer.pre_process.term', array( $this, 'registerOcdiTaxonomy' ), 10, 1 );

		add_filter( 'wp_insert_post_data', array( $this, 'handlePostUpdate' ), 10, 2 );
		add_action( 'delete_post', array( $this, 'handlePostDeletion' ), 10, 1 );

		if ( is_admin() ) {
			add_action( 'shutdown', array( $this, 'maybeFlushRewriteRules' ) );
		}
	}

	protected function createManagePage() {
		return new \MPHB\Admin\ManageCPTPages\AttributesManageCPTPage( $this->postType );
	}

	protected function createEditPage() {
		return new \MPHB\Admin\EditCPTPages\AttributesEditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {
		global $mphbAttributes, $mphbAttributeTaxonomies;

		$mphbAttributes          = array();
		$mphbAttributeTaxonomies = array();

		$labels = array(
			'name'               => __( 'Attributes', 'motopress-hotel-booking' ),
			'singular_name'      => __( 'Attribute', 'motopress-hotel-booking' ),
			'add_new'            => _x( 'Add New', 'Add New Attribute', 'motopress-hotel-booking' ),
			'add_new_item'       => __( 'Add New Attribute', 'motopress-hotel-booking' ),
			'edit_item'          => __( 'Edit Attribute', 'motopress-hotel-booking' ),
			'new_item'           => __( 'New Attribute', 'motopress-hotel-booking' ),
			'view_item'          => __( 'View Attribute', 'motopress-hotel-booking' ),
			'menu_name'          => __( 'Attributes', 'motopress-hotel-booking' ),
			'search_items'       => __( 'Search Attribute', 'motopress-hotel-booking' ),
			'not_found'          => __( 'No Attributes found', 'motopress-hotel-booking' ),
			'not_found_in_trash' => __( 'No Attributes found in Trash', 'motopress-hotel-booking' ),
			'all_items'          => __( 'Attributes', 'motopress-hotel-booking' ),
			'insert_into_item'   => __( 'Insert into attribute description', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'public'               => false,
			'show_ui'              => true,
			'show_in_menu'         => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'supports'             => array( 'title', 'page-attributes' ),
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'has_archive'          => false,
			'capability_type'      => $this->getCapabilityType(),
			'map_meta_cap'         => true,
		);

		register_post_type( $this->postType, $args );

		$this->registerTaxonomies();
	}

	private function registerTaxonomies() {
		// Get only post on default language. Also get all default-language-posts
		// that have no translation on current language (and will not be loaded
		// by default)
		MPHB()->translation()->setupDefaultLanguage();
		$attributes = MPHB()->getAttributesPersistence()->getIdTitleList(
			array(
				'orderby' => 'menu_order',
				'order'   => 'ASC',
			)
		);
		MPHB()->translation()->restoreLanguage();

		// Register taxonomies
		foreach ( $attributes as $id => $title ) {
			$post = get_post( $id );

			if ( ! is_null( $post ) ) {
				$this->registerTaxonomy( $post->post_name, $title, $id );
			}
		}
	}

	private function registerTaxonomy( $attributeName, $attributeTitle, $postId ) {
		global $sitepress, $mphbAttributes;

		if ( empty( $attributeName ) ) {
			return;
		}

		$attributeName = mphb_sanitize_attribute_name( $attributeName );
		$taxonomyName  = mphb_attribute_taxonomy_name( $attributeName );

		if ( mphb_is_attribute_taxonomy( $taxonomyName ) ) {
			// Don't register duplicates of the taxonomy. Note: the attribute name
			// can be unique in this case (actual for attributes, which names with
			// prefix are longer than 32 characters). Example:
			// "accommodation-in-accommodation" and "accommodation-in-accommodation-2"
			// will both give us the taxonomy "mphb_ra_accommodation-in-accommo"
			$attributeName                                     = mphb_taxonomy_attribute_name( $taxonomyName );
			$mphbAttributes[ $attributeName ]['hasDuplicates'] = true;

			return;
		}

		$public      = (bool) get_post_meta( $postId, 'mphb_public', true );
		$visible     = (bool) get_post_meta( $postId, 'mphb_visible', true );
		$orderby     = get_post_meta( $postId, 'mphb_orderby', true );
		$orderby     = empty( $orderby ) ? $this->getDefaultOrderby() : $orderby;
		$defaultText = get_post_meta( $postId, 'mphb_default_text', true );
		$defaultText = empty( $defaultText ) ? _x( '&mdash;', 'Not selected value in the search form.', 'motopress-hotel-booking' ) : $defaultText;
		$type        = get_post_meta( $postId, 'mphb_type', true );
		$type        = empty( $type ) ? 'select' : $type;

		// Try to translate title to current language
		$translationId = MPHB()->translation()->getCurrentId( $postId, $this->postType, false );
		if ( ! is_null( $translationId ) ) {
			$translationTitle = get_the_title( $translationId );
			if ( ! empty( $translationTitle ) ) {
				$attributeTitle = $translationTitle;
			}
			$defaultText = get_post_meta( $translationId, 'mphb_default_text', true );
			$defaultText = empty( $defaultText ) ? _x( '&mdash;', 'Not selected value in the search form.', 'motopress-hotel-booking' ) : $defaultText;
		}

		$args = array(
			'labels'            => array(
				'name'          => $attributeTitle,
				'singular_name' => $attributeTitle,
				/* translators: %s: attribute name */
				'search_items'  => sprintf( __( 'Search %s', 'motopress-hotel-booking' ), $attributeTitle ),
				/* translators: %s: attribute name */
				'all_items'     => sprintf( __( 'All %s', 'motopress-hotel-booking' ), $attributeTitle ),
				/* translators: %s: attribute name */
				'edit_item'     => sprintf( __( 'Edit %s', 'motopress-hotel-booking' ), $attributeTitle ),
				/* translators: %s: attribute name */
				'update_item'   => sprintf( __( 'Update %s', 'motopress-hotel-booking' ), $attributeTitle ),
				/* translators: %s: attribute name */
				'add_new_item'  => sprintf( __( 'Add new %s', 'motopress-hotel-booking' ), $attributeTitle ),
				/* translators: %s: attribute name */
				'new_item_name' => sprintf( __( 'New %s', 'motopress-hotel-booking' ), $attributeTitle ),
				/* translators: %s: attribute name */
				'not_found'     => sprintf( __( 'No &quot;%s&quot; found', 'motopress-hotel-booking' ), $attributeTitle ),
			),
			'public'            => $public,
			'show_ui'           => true,
			'meta_box_cb'       => false,
			'show_in_menu'      => false,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'query_var'         => $public,
			'rewrite'           => false,
			'capabilities'      => array(
				'manage_terms' => 'edit_mphb_room_attributes',
				'edit_terms'   => 'edit_mphb_room_attributes',
				'delete_terms' => 'edit_mphb_room_attributes',
				'assign_terms' => 'edit_mphb_room_attributes',
			),
		);

		if ( $public ) {
			$args['rewrite'] = array(
				'slug' => $attributeName,
			);
		}

		register_taxonomy( $taxonomyName, MPHB()->postTypes()->roomType()->getPostType(), $args );
		register_taxonomy_for_object_type( $taxonomyName, MPHB()->postTypes()->roomType()->getPostType() );

		mphb_add_attribute(
			array(
				'attributeName' => $attributeName,
				'taxonomyName'  => $taxonomyName,
				'title'         => $attributeTitle,
				'public'        => $public,
				'visible'       => $visible,
				'orderby'       => $orderby,
				'default_text'  => $defaultText,
				'type'          => $type,
				'hasDuplicates' => false,
			)
		);

		// Make taxonomy translatable
		if ( $sitepress ) {
			$wpmlTaxonomiesSync                  = $sitepress->get_setting( 'taxonomies_sync_option', array() );
			$wpmlTaxonomiesSync[ $taxonomyName ] = true;
			$sitepress->set_setting( 'taxonomies_sync_option', $wpmlTaxonomiesSync, true );
			$sitepress->verify_taxonomy_translations( $taxonomyName );
		}
	}

	/**
	 * Import listener for default WordPress importer.
	 */
	public function registerImportingTaxonomy() {
		if ( empty( $_POST['import_id'] ) || ! class_exists( 'WXR_Parser' ) ) {
			return;
		}

		$importId   = absint( $_POST['import_id'] );
		$importFile = get_attached_file( $importId );
		$parser     = new \WXR_Parser();
		$importData = $parser->parse( $importFile );

		if ( empty( $importData['posts'] ) ) {
			return;
		}

		foreach ( $importData['posts'] as $post ) {
			// Skip non-attribute post types
			if ( $post['post_type'] != $this->postType ) {
				continue;
			}

			// Skip attributes without terms
			if ( empty( $post['terms'] ) ) {
				continue;
			}

			foreach ( $post['terms'] as $term ) {
				$taxonomyName = $term['domain'];

				if ( strstr( $taxonomyName, mphb_attributes_prefix() ) ) {
					$attributeName = mphb_taxonomy_attribute_name( $taxonomyName );

					if ( ! mphb_attribute_exists( $attributeName ) ) {
						$this->registerTaxonomy( $attributeName, $attributeName, 0 );
					}
				}
			} // For each term

		} // For each post
	}

	/**
	 * Import listener for plugin One Click Demo Import. Fixes
	 * "[WARNING] Failed to import mphb_ra_..." warnings in content import.
	 *
	 * @param array  $data The term data to import.
	 * @param int    $data['id']       Original term ID.
	 * @param string $data['taxonomy'] Taxonomy name, like "mphb_ra_hotel".
	 * @param string $data['slug']     Term slug, like "west-hotel".
	 * @param string $data['name']     Term title, like "West Hotel".
	 * @return array The unchanged term data.
	 */
	public function registerOcdiTaxonomy( $data ) {
		$taxonomyName = $data['taxonomy'];

		if ( strstr( $taxonomyName, mphb_attributes_prefix() ) ) {
			$attributeName = mphb_taxonomy_attribute_name( $taxonomyName );

			if ( ! mphb_attribute_exists( $attributeName ) ) {
				$this->registerTaxonomy( $attributeName, $attributeName, 0 );
			}
		}

		return $data;
	}

	private function getDefaultOrderby() {
		return ( MPHB()->isWpSupportsTermmeta() ) ? 'custom' : 'name';
	}

	/**
	 * @return Groups\MetaBoxGroup[]
	 */
	public function getFieldGroups() {
		$orders = array();

		if ( MPHB()->isWpSupportsTermmeta() ) {
			$orders['custom'] = __( 'Custom', 'motopress-hotel-booking' );
		}

		$orders['name']    = __( 'Name', 'motopress-hotel-booking' );
		$orders['numeric'] = __( 'Name (numeric)', 'motopress-hotel-booking' );
		$orders['id']      = __( 'Term ID', 'motopress-hotel-booking' );

		$defaultOrder = $this->getDefaultOrderby();

		$paramsGroup = new Groups\MetaBoxGroup( 'mphb_parameters', __( 'Parameters', 'motopress-hotel-booking' ), $this->postType );
		$paramsGroup->addFields(
			array(
				Fields\FieldFactory::create(
					'mphb_public',
					array(
						'type'         => 'checkbox',
						'label'        => __( 'Enable Archives', 'motopress-hotel-booking' ),
						'inner_label'  => __( 'Link the attribute to an archive page with all accommodation types that have this attribute.', 'motopress-hotel-booking' ),
						'default'      => false,
						'translatable' => true,
					)
				),
				Fields\FieldFactory::create(
					'mphb_visible',
					array(
						'type'         => 'checkbox',
						'label'        => __( 'Visible in Details', 'motopress-hotel-booking' ),
						'inner_label'  => __( 'Display the attribute in details section of an accommodation type.', 'motopress-hotel-booking' ),
						'default'      => false,
						'translatable' => true,
					)
				),
				Fields\FieldFactory::create(
					'mphb_orderby',
					array(
						'type'         => 'select',
						'label'        => __( 'Default Sort Order', 'motopress-hotel-booking' ),
						'list'         => $orders,
						'default'      => $defaultOrder,
						'translatable' => true,
					)
				),
				Fields\FieldFactory::create(
					'mphb_default_text',
					array(
						'type'         => 'text',
						'label'        => __( 'Default Text', 'motopress-hotel-booking' ),
						'default'      => _x( '&mdash;', 'Not selected value in the search form.', 'motopress-hotel-booking' ),
						'translatable' => true,
					)
				),
			)
		);

		$attributeTypes = apply_filters(
			'mphb_room_attributes_type_selector',
			array(
				'select' => __( 'Select', 'motopress-hotel-booking' ),
			)
		);

		if ( count( $attributeTypes ) > 1 || ! array_key_exists( 'select', $attributeTypes ) ) {
			$paramsGroup->addField(
				Fields\FieldFactory::create(
					'mphb_type',
					array(
						'type'    => 'select',
						'label'   => __( 'Type', 'motopress-hotel-booking' ),
						'list'    => $attributeTypes,
						'default' => 'select',
					)
				)
			);
		}

		return array(
			$paramsGroup,
		);
	}

	/**
	 * Add "menu_order" ordering to get_terms() (support a custom order in
	 * attributes).
	 *
	 * @param array $pieces Terms query SQL clauses.
	 * @param array $taxonomies An array of taxonomies.
	 * @param array $args An array of terms query arguments.
	 *
	 * @return array
	 *
	 * @global \wpdb $wpdb
	 */
	public function supportAttributesMenuOrder( $pieces, $taxonomies, $args ) {
		global $wpdb;

		// No sorting when menu_order is disabled (false)
		if ( isset( $args['menu_order'] ) && $args['menu_order'] == false ) {
			return $pieces;
		}

		// No sorting when orderby in non-default
		if ( isset( $args['orderby'] ) && $args['orderby'] != 'name' ) {
			return $pieces;
		}

		// No sorting in admin when sorting by a column
		if ( is_admin() && isset( $_GET['orderby'] ) ) {
			return $pieces;
		}

		// No need to filter counts
		if ( strpos( 'COUNT(*)', $pieces['fields'] ) !== false ) {
			return $pieces;
		}

		// Search for the proper taxonomy ("mphb_ra_*")
		$taxonomyName = null;

		foreach ( (array) $taxonomies as $taxonomy ) {
			if ( mphb_is_attribute_taxonomy( $taxonomy ) ) {
				$taxonomyName = $taxonomy;
				break;
			}
		}

		if ( is_null( $taxonomyName ) ) {
			return $pieces;
		}

		// Order by first found attribute (the only one when fetching by
		// AttributesPersistence::getTermsIdTitleList() or
		// AttributesPersistence::getAttributes()), we will not add LEFT
		// JOIN for each attribute...
		$metaName = 'order_' . esc_attr( $taxonomyName ); // Example: "order_mphb_ra_hotel"

		// Query fields
		$pieces['fields'] .= ', mphb_termmeta.meta_value';

		// Query join
		$pieces['join'] .= " LEFT JOIN {$wpdb->termmeta} AS mphb_termmeta ON (t.term_id = mphb_termmeta.term_id AND mphb_termmeta.meta_key = '" . esc_sql( $metaName ) . "') ";

		// Sanitize value
		if ( ! isset( $args['menu_order'] ) || ! in_array( strtoupper( $args['menu_order'] ), array( 'ASC', 'DESC' ) ) ) {
			$args['menu_order'] = 'ASC';
		}

		// Add custom ordering
		$customOrder = 'ORDER BY mphb_termmeta.meta_value+0 ' . $args['menu_order'];

		if ( $pieces['orderby'] ) {
			// Extend existing ORDER BY, add our meta values on the beginning
			$pieces['orderby'] = str_replace( 'ORDER BY', $customOrder . ',', $pieces['orderby'] );
		} else {
			// ORDER BY was not found, add ours
			$pieces['orderby'] = $customOrder;
		}

		// Add grouping to order
		if ( strpos( $pieces['fields'], 'tr.object_id' ) !== false ) {
			$pieces['orderby'] = ' GROUP BY t.term_id, tr.object_id ' . $pieces['orderby'];
		} else {
			$pieces['orderby'] = ' GROUP BY t.term_id ' . $pieces['orderby'];
		}

		return $pieces;
	}

	/**
	 * Fired when new post created or updated.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/wp_insert_post_data
	 *
	 * @param array $data An array of slashed post data
	 * @param array $postarr An array of sanitized, but otherwise unmodified
	 * post data.
	 *
	 * @return array Updated data array.
	 *
	 * @global \wpdb $wpdb
	 */
	public function handlePostUpdate( $data, $postarr ) {
		global $wpdb;

		if ( $postarr['post_type'] !== $this->postType ) {
			return $data;
		} elseif ( in_array( $postarr['post_status'], array( 'draft', 'pending', 'auto-draft', 'inherit' ), true ) ) {
			// Attribute posts can be only public
			return $data;
		}

		$postId          = $postarr['ID'];
		$postName        = $data['post_name'];
		$postNameChanged = false;

		if ( ! MPHB()->translation()->isOriginalId( $postId, $this->postType ) ) {
			// Don't edit taxonomies and terms when editing translations
			return $data;
		}

		// Avoid reserved names
		if ( mphb_is_reserved_term( $postName ) ) {
			// Add suffix to make unreserved word
			if ( mphb_string_ends_with( $postName, 's' ) ) {
				$postName .= '-2';
			} else {
				$postName .= 's';
			}

			$postName = wp_unique_post_slug( $postName, $postId, $data['post_status'], $this->postType, $data['post_parent'] );

			$postNameChanged = true;
		}

		// Rename taxonomy, if slug changed
		$postBefore = get_post( $postId );

		if ( ! is_null( $postBefore ) && $postBefore->post_status !== 'auto-draft' && $postName !== $postBefore->post_name ) {
			$postNameChanged = true;

			$attributeBefore = sanitize_title( $postBefore->post_name );
			$newAttribute    = sanitize_title( $postName );

			// Leave all terms for another duplicate
			if ( ! mphb_is_duplicate_attribute( $attributeBefore ) && ! mphb_has_duplicate_attributes( $attributeBefore ) ) {
				$taxonomyBefore = mphb_attribute_taxonomy_name( $attributeBefore );
				$newTaxonomy    = mphb_attribute_taxonomy_name( $newAttribute );

				// Rename taxonomies in table wp_term_taxonomy
				$wpdb->update(
					$wpdb->term_taxonomy,
					array( 'taxonomy' => $newTaxonomy ),
					array( 'taxonomy' => $taxonomyBefore )
				);

				// Update ordering
				if ( MPHB()->isWpSupportsTermmeta() ) {
					$wpdb->update(
						$wpdb->termmeta,
						array( 'meta_key' => 'order_' . $newTaxonomy ),
						array( 'meta_key' => 'order_' . $taxonomyBefore )
					);
				}
			} // If not/no duplicate
		} // If post name changed

		// Register new taxonomy with a new name (especially important for Quick
		// Edit to get terms list for updated post)
		if ( $postNameChanged ) {
			$this->registerTaxonomy( $postName, $data['post_title'], $postId );
		}

		// Save new data
		$data['post_name'] = $postName;

		return $data;
	}

	/**
	 * Fired just before delete the post from database.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/delete_post
	 *
	 * @param int $postId
	 *
	 * @global string $post_type Current post type.
	 */
	public function handlePostDeletion( $postId ) {
		global $post_type;

		if ( $post_type !== $this->postType ) {
			return;
		}

		$post = get_post( $postId );

		if ( is_null( $post ) ) {
			return;
		}

		$attributeName = mphb_clean_attribute_name( $post->post_name );

		if ( mphb_is_duplicate_attribute( $attributeName ) || mphb_has_duplicate_attributes( $attributeName ) ) {
			// Leave all terms for another duplicate
			return;
		}

		$taxonomyName = mphb_attribute_taxonomy_name( $attributeName );

		// Register taxonomy to query it's terms later
		if ( ! mphb_is_attribute_taxonomy( $taxonomyName ) ) {
			register_taxonomy(
				$taxonomyName,
				MPHB()->postTypes()->roomType()->getPostType(),
				array(
					'public'    => false,
					'query_var' => false,
				)
			);
			register_taxonomy_for_object_type( $taxonomyName, MPHB()->postTypes()->roomType()->getPostType() );
		}

		MPHB()->translation()->setupAllLanguages();
		$terms = MPHB()->getAttributesPersistence()->getTermsIdTitleList( $attributeName );
		MPHB()->translation()->restoreLanguage();

		foreach ( array_keys( $terms ) as $termId ) {
			wp_delete_term( $termId, $taxonomyName );
		}
	}

	public function maybeFlushRewriteRules() {
		$previouslyRegistered = get_option( 'mphb_registered_attributes', array() );
		$newlyRegistered      = array();

		foreach ( mphb_get_attribute_names() as $attributeName ) {
			if ( mphb_is_public_attribute( $attributeName ) ) {
				$newlyRegistered[] = $attributeName;
			}
		}

		$attributesDifference = mphb_array_disjunction( $previouslyRegistered, $newlyRegistered );

		if ( ! empty( $attributesDifference ) ) {
			flush_rewrite_rules();
		}

		update_option( 'mphb_registered_attributes', $newlyRegistered );
	}

}
