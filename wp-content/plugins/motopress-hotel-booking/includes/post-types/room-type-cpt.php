<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\EditCPTPages;
use \MPHB\Admin\ManageCPTPages;
use \MPHB\Admin\ManageTaxPages;

class RoomTypeCPT extends EditableCPT {

	protected $postType      = 'mphb_room_type';
	private $facilityTaxName = 'mphb_room_type_facility';
	private $categoryTaxName = 'mphb_room_type_category';
	private $tagTaxName      = 'mphb_room_type_tag';
	protected $facilityManagePage;

	protected function addActions() {
		parent::addActions();
		add_action( 'after_setup_theme', array( $this, 'addFeaturedImageSupport' ), 11 );

		add_filter( 'single_template', array( $this, 'filterSingleTemplate' ) );

		add_filter( 'post_class', array( $this, 'filterPostClass' ), 20, 3 );
		add_action( 'init', array( $this, 'initTaxManagePages' ) );

		add_filter( 'use_block_editor_for_post_type', array( $this, 'useBlockEditor' ), 10, 2 );
	}

	public function useBlockEditor( $useBlockEditor, $postType ) {
		if ( $postType == $this->postType ) {
			$useBlockEditor = MPHB()->settings()->main()->useBlockEditorForRoomTypes();
		}

		return $useBlockEditor;
	}

	protected function createManagePage() {
		return new ManageCPTPages\RoomTypeManageCPTPage( $this->postType );
	}

	protected function createEditPage() {
		return new EditCPTPages\RoomTypeEditCPTPage( $this->postType, $this->getFieldGroups() );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	public function register() {
		$labels = array(
			'name'                  => __( 'Accommodation Types', 'motopress-hotel-booking' ),
			'singular_name'         => __( 'Accommodation Type', 'motopress-hotel-booking' ),
			'add_new'               => _x( 'Add Accommodation Type', 'Add New Accommodation Type', 'motopress-hotel-booking' ),
			'add_new_item'          => __( 'Add New Accommodation Type', 'motopress-hotel-booking' ),
			'edit_item'             => __( 'Edit Accommodation Type', 'motopress-hotel-booking' ),
			'new_item'              => __( 'New Accommodation Type', 'motopress-hotel-booking' ),
			'view_item'             => __( 'View Accommodation Type', 'motopress-hotel-booking' ),
			'menu_name'             => __( 'Accommodation', 'motopress-hotel-booking' ),
			'search_items'          => __( 'Search Accommodation Type', 'motopress-hotel-booking' ),
			'not_found'             => __( 'No Accommodation types found', 'motopress-hotel-booking' ),
			'not_found_in_trash'    => __( 'No Accommodation types found in Trash', 'motopress-hotel-booking' ),
			'all_items'             => __( 'Accommodation Types', 'motopress-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into accommodation type description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this accommodation type', 'motopress-hotel-booking' ),
		);

		$args = array(
			'labels'               => $labels,
			'public'               => true,
			'publicly_queryable'   => true,
			'show_ui'              => true,
			'capability_type'      => $this->getCapabilityType(),
			'map_meta_cap'         => true,
			'has_archive'          => true,
			'show_in_menu'         => true,
			'supports'             => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'comments' ),
			'hierarchical'         => false,
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'rewrite'              => array(
				// translators: do not translate
				'slug'       => _x( 'accommodation', 'slug', 'motopress-hotel-booking' ),
				'with_front' => false,
				'feeds'      => true,
			),
			'query_var'            => true,
			'show_in_rest'         => true,
		);

		if ( MPHB()->isWPVersion( '4.1', '>=' ) ) {
			$args['menu_icon'] = 'dashicons-building';
		}

		register_post_type( $this->postType, $args );

		$this->registerCategoryTaxonomy();
		$this->registerTagTaxonomy();
		$this->registerFacilityTaxonomy();
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	private function registerCategoryTaxonomy() {
		$labels = array(
			'name'                       => __( 'Accommodation Categories', 'motopress-hotel-booking' ),
			'singular_name'              => __( 'Accommodation Category', 'motopress-hotel-booking' ),
			'search_items'               => __( 'Search Accommodation Categories', 'motopress-hotel-booking' ),
			'popular_items'              => __( 'Popular Accommodation Categories', 'motopress-hotel-booking' ),
			'all_items'                  => __( 'All Accommodation Categories', 'motopress-hotel-booking' ),
			'parent_item'                => __( 'Parent Accommodation Category', 'motopress-hotel-booking' ),
			'parent_item_colon'          => __( 'Parent Accommodation Category:', 'motopress-hotel-booking' ),
			'edit_item'                  => __( 'Edit Accommodation Category', 'motopress-hotel-booking' ),
			'update_item'                => __( 'Update Accommodation Category', 'motopress-hotel-booking' ),
			'add_new_item'               => __( 'Add New Accommodation Category', 'motopress-hotel-booking' ),
			'new_item_name'              => __( 'New Accommodation Category Name', 'motopress-hotel-booking' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'motopress-hotel-booking' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'motopress-hotel-booking' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories', 'motopress-hotel-booking' ),
			'not_found'                  => __( 'No categories found.', 'motopress-hotel-booking' ),
			'menu_name'                  => __( 'Categories', 'motopress-hotel-booking' ),
		);

		list($capabilityType, $capabilityTypes) = $this->getCapabilityType();

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => MPHB()->menus()->getMainMenuSlug(),
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				// translators: do not translate
				'slug'         => _x( 'accommodation-category', 'slug', 'motopress-hotel-booking' ),
				'with_front'   => false,
				'hierarchical' => true,
			),
			'query_var'         => true,
			'show_in_rest'      => true,
			'capabilities'      => array(
				'manage_terms' => "manage_{$capabilityType}_categories",
				'edit_terms'   => "manage_{$capabilityType}_categories",
				'delete_terms' => "manage_{$capabilityType}_categories",
				'assign_terms' => "edit_{$capabilityTypes}",
			),
		);

		register_taxonomy( $this->categoryTaxName, $this->postType, $args );

		register_taxonomy_for_object_type( $this->categoryTaxName, $this->postType );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	private function registerTagTaxonomy() {
		$labels = array(
			'name'                       => __( 'Accommodation Tags', 'motopress-hotel-booking' ),
			'singular_name'              => __( 'Accommodation Tag', 'motopress-hotel-booking' ),
			'search_items'               => __( 'Search Accommodation Tags', 'motopress-hotel-booking' ),
			'popular_items'              => __( 'Popular Accommodation Tags', 'motopress-hotel-booking' ),
			'all_items'                  => __( 'All Accommodation Tags', 'motopress-hotel-booking' ),
			'parent_item'                => __( 'Parent Accommodation Tag', 'motopress-hotel-booking' ),
			'parent_item_colon'          => __( 'Parent Accommodation Tag:', 'motopress-hotel-booking' ),
			'edit_item'                  => __( 'Edit Accommodation Tag', 'motopress-hotel-booking' ),
			'update_item'                => __( 'Update Accommodation Tag', 'motopress-hotel-booking' ),
			'add_new_item'               => __( 'Add New Accommodation Tag', 'motopress-hotel-booking' ),
			'new_item_name'              => __( 'New Accommodation Tag Name', 'motopress-hotel-booking' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'motopress-hotel-booking' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'motopress-hotel-booking' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'motopress-hotel-booking' ),
			'not_found'                  => __( 'No tags found.', 'motopress-hotel-booking' ),
			'menu_name'                  => __( 'Tags', 'motopress-hotel-booking' ),
		);

		list($capabilityType, $capabilityTypes) = $this->getCapabilityType();

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => MPHB()->menus()->getMainMenuSlug(),
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				// translators: do not translate
				'slug'       => _x( 'accommodation-tag', 'slug', 'motopress-hotel-booking' ),
				'with_front' => false,
			),
			'query_var'         => true,
			'show_in_rest'      => true,
			'capabilities'      => array(
				'manage_terms' => "manage_{$capabilityType}_tags",
				'edit_terms'   => "manage_{$capabilityType}_tags",
				'delete_terms' => "manage_{$capabilityType}_tags",
				'assign_terms' => "edit_{$capabilityTypes}",
			),
		);

		register_taxonomy( $this->tagTaxName, $this->postType, $args );

		register_taxonomy_for_object_type( $this->tagTaxName, $this->postType );
	}

	/**
	 *
	 * @since 4.0.0 - Add custom capabilities.
	 */
	private function registerFacilityTaxonomy() {

		$labels = array(
			'name'                       => __( 'Amenities', 'motopress-hotel-booking' ),
			'singular_name'              => __( 'Amenity', 'motopress-hotel-booking' ),
			'search_items'               => __( 'Search Amenities', 'motopress-hotel-booking' ),
			'popular_items'              => __( 'Popular Amenities', 'motopress-hotel-booking' ),
			'all_items'                  => __( 'All Amenities', 'motopress-hotel-booking' ),
			'parent_item'                => __( 'Parent Amenity', 'motopress-hotel-booking' ),
			'parent_item_colon'          => __( 'Parent Amenity:', 'motopress-hotel-booking' ),
			'edit_item'                  => __( 'Edit Amenity', 'motopress-hotel-booking' ),
			'update_item'                => __( 'Update Amenity', 'motopress-hotel-booking' ),
			'add_new_item'               => __( 'Add New Amenity', 'motopress-hotel-booking' ),
			'new_item_name'              => __( 'New Amenity Name', 'motopress-hotel-booking' ),
			'separate_items_with_commas' => __( 'Separate amenities with commas', 'motopress-hotel-booking' ),
			'add_or_remove_items'        => __( 'Add or remove amenities', 'motopress-hotel-booking' ),
			'choose_from_most_used'      => __( 'Choose from the most used amenities', 'motopress-hotel-booking' ),
			'not_found'                  => __( 'No amenities found.', 'motopress-hotel-booking' ),
			'menu_name'                  => __( 'Amenities', 'motopress-hotel-booking' ),
		);

		list($capabilityType, $capabilityTypes) = $this->getCapabilityType();

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_menu'      => MPHB()->menus()->getMainMenuSlug(),
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				// translators: do not translate
				'slug'         => _x( 'accommodation-facility', 'slug', 'motopress-hotel-booking' ),
				'with_front'   => false,
				'hierarchical' => true,
			),
			'query_var'         => true,
			'show_in_rest'      => true,
			'capabilities'      => array(
				'manage_terms' => "manage_{$capabilityType}_facilities",
				'edit_terms'   => "manage_{$capabilityType}_facilities",
				'delete_terms' => "manage_{$capabilityType}_facilities",
				'assign_terms' => "edit_{$capabilityTypes}",
			),
		);

		register_taxonomy( $this->facilityTaxName, $this->postType, $args );

		register_taxonomy_for_object_type( $this->facilityTaxName, $this->postType );
	}

	/**
	 *
	 * @return Groups\MetaBoxGroup[]
	 */
	public function getFieldGroups() {

		$capacityGroup       = new Groups\MetaBoxGroup( 'mphb_capacity', __( 'Capacity', 'motopress-hotel-booking' ), $this->postType );
		$adultsCapacityField = Fields\FieldFactory::create(
			'mphb_adults_capacity',
			array(
				'type'    => 'number',
				'label'   => __( 'Adults', 'motopress-hotel-booking' ),
				'default' => (string) MPHB()->settings()->main()->getMinAdults(),
				'min'     => (string) MPHB()->settings()->main()->getMinAdults(),
			)
		);
		$capacityGroup->addField( $adultsCapacityField );
		$childrenCapacityField = Fields\FieldFactory::create(
			'mphb_children_capacity',
			array(
				'type'        => 'number',
				'label'       => __( 'Children', 'motopress-hotel-booking' ),
				'description' => sprintf( __( 'State the age or disable children in <a href="%s">settings</a>.', 'motopress-hotel-booking' ), admin_url( 'edit.php?post_type=mphb_room_type&page=mphb_settings' ) ),
				'default'     => (string) MPHB()->settings()->main()->getMinChildren(),
				'min'         => (string) MPHB()->settings()->main()->getMinChildren(),
			)
		);
		$capacityGroup->addField( $childrenCapacityField );
		$totalCapacityField = Fields\FieldFactory::create(
			'mphb_total_capacity',
			array(
				'type'        => 'number',
				'label'       => __( 'Capacity', 'motopress-hotel-booking' ),
				'description' => __( 'Leave this option empty to calculate total capacity automatically to meet the exact number of adults AND children set above. This is the default behavior. Configure this option to allow any variations of adults OR children set above at checkout so that in total it meets the limit of manually set "Capacity". For example, configuration "adults:5", "children:4", "capacity:5" means the property can accommodate up to 5 adults, up to 4 children, but up to 5 guests in total (not 9).', 'motopress-hotel-booking' ),
				'default'     => '',
				'min'         => 0,
				'allow_empty' => true,
			)
		);
		$capacityGroup->addField( $totalCapacityField );
		$sizeField = Fields\FieldFactory::create(
			'mphb_size',
			array(
				'type'        => 'number',
				'label'       => sprintf( __( 'Size, %s', 'motopress-hotel-booking' ), MPHB()->settings()->units()->getSquareUnit() ),
				'description' => __( 'Leave blank to hide.', 'motopress-hotel-booking' ),
				'default'     => 0,
				'min'         => 0,
				'step'        => 0.1,
				'size'        => 'small',
			)
		);
		$capacityGroup->addField( $sizeField );

		$otherGroup = new Groups\MetaBoxGroup( 'mphb_other', __( 'Other', 'motopress-hotel-booking' ), $this->postType );
		$viewField  = Fields\FieldFactory::create(
			'mphb_view',
			array(
				'type'         => 'text',
				'label'        => __( 'View', 'motopress-hotel-booking' ),
				'description'  => __( 'City view, seaside, swimming pool etc.', 'motopress-hotel-booking' ),
				'size'         => 'large',
				'translatable' => true,
			)
		);
		$otherGroup->addField( $viewField );

		$bedField = Fields\FieldFactory::create(
			'mphb_bed',
			array(
				'type'         => 'text',
				'label'        => __( 'Bed type', 'motopress-hotel-booking' ),
				'list'         => MPHB()->settings()->main()->getBedTypesList(),
				'description'  => strtr(
					__( 'Set bed types list in <a href="%link%" target="_blank">settings</a>.', 'motopress-hotel-booking' ),
					array(
						'%link%' => MPHB()->getSettingsMenuPage()->getUrl(),
					)
				),
				'translatable' => true,
			)
		);
		$otherGroup->addField( $bedField );

		$galleryGroup = new Groups\MetaBoxGroup( 'mphb_gallery', __( 'Photo Gallery', 'motopress-hotel-booking' ), $this->postType, 'side' );
		$galleryField = Fields\FieldFactory::create(
			'mphb_gallery',
			array(
				'type'           => 'media',
				'thumbnail_size' => 'medium',
				'single'         => false,
			)
		);
		$galleryGroup->addField( $galleryField );

		$servicesGroup = new Groups\MetaBoxGroup( 'mphb_services', __( 'Available Services', 'motopress-hotel-booking' ), $this->postType );
		$servicesField = Fields\FieldFactory::create(
			'mphb_services',
			array(
				'type'         => 'service-chooser',
				'label'        => __( 'Available Services', 'motopress-hotel-booking' ),
				'show_prices'  => true,
				'show_add_new' => true,
			)
		);
		$servicesGroup->addField( $servicesField );

		return array(
			$capacityGroup,
			$otherGroup,
			$galleryGroup,
			$servicesGroup,
		);
	}

	public function addFeaturedImageSupport() {
		$supportedTypes = get_theme_support( 'post-thumbnails' );
		if ( $supportedTypes === false ) {
			add_theme_support( 'post-thumbnails', array( $this->postType ) );
		} elseif ( is_array( $supportedTypes ) ) {
			$supportedTypes[0][] = $this->postType;
			add_theme_support( 'post-thumbnails', $supportedTypes[0] );
		}
	}

	/**
	 *
	 * @param string $template
	 * @return string
	 */
	public function filterSingleTemplate( $template ) {

		if ( get_post_type() === $this->postType ) {
			if ( MPHB()->settings()->main()->isPluginTemplateMode() ) {
				$template = locate_template( MPHB()->getTemplatePath() . 'single-room-type.php' );
				if ( ! $template ) {
					$template = MPHB()->getPluginPath( 'templates/single-room-type.php' );
				}
			} else {
				add_action( 'loop_start', array( $this, 'setupPseudoTemplate' ) );
			}
		}
		return $template;
	}

	/**
	 *
	 * @param \WP_Query $query
	 */
	public function setupPseudoTemplate( $query ) {
		if ( $query->is_main_query() ) {
			$query->set( 'mphb_append_meta', true );
			add_filter( 'the_content', array( $this, 'appendRoomMetas' ) );
			remove_action( 'loop_start', array( $this, 'setupPseudoTemplate' ) );
			add_action( 'loop_end', array( $this, 'stopAppendRoomMetas' ) );
		}
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	public function appendRoomMetas( $content ) {

		if ( is_main_query() &&
			get_query_var( 'mphb_append_meta' ) &&
			get_post_type() === $this->postType
		) {
			// only run once
			remove_filter( 'the_content', array( $this, 'appendRoomMetas' ) );

			ob_start();
			\MPHB\Views\SingleRoomTypeView::_renderMetas();
			$content .= ob_get_clean();
		}

		return $content;
	}

	/**
	 *
	 * @param \WP_Query $query
	 */
	public function stopAppendRoomMetas( $query ) {
		if ( $query->is_main_query() &&
			$query->get( 'mphb_append_meta' )
		) {
			// remove filter if some reason the_content don't used by theme's loop
			remove_filter( 'the_content', array( $this, 'appendRoomMetas' ) );
			remove_filter( 'loop_end', array( $this, 'stopAppendRoomMetas' ) );
		}
	}

	/**
	 *
	 * @param array $classes
	 * @param array $class
	 * @param int   $postId
	 * @return string
	 */
	public function filterPostClass( $classes, $class = '', $postId = '' ) {

		if ( ! $postId || get_post_type( $postId ) !== $this->getPostType() ) {
			return $classes;
		}

		$roomType = MPHB()->getRoomTypeRepository()->findById( $postId );
		if ( ! $roomType ) {
			return $classes;
		}

		$classes[] = 'mphb-room-type-adults-' . $roomType->getAdultsCapacity();
		$classes[] = 'mphb-room-type-children-' . $roomType->getChildrenCapacity();

		$classes[] = $roomType->hasTaxesAndFees() ? 'has-taxes-and-fees' : '';

		// if ( false !== ( $key = array_search( 'hentry', $classes ) ) ) {
		// if ( false !== ( $key = array_search( 'hentry', $classes ) ) && MPHB()->settings()->main()->isPluginTemplateMode() ) {
		// unset( $classes[ $key ] );
		// }

		return $classes;
	}

	public function getFacilityTaxName() {
		return $this->facilityTaxName;
	}

	public function getCategoryTaxName() {
		return $this->categoryTaxName;
	}

	public function getTagTaxName() {
		return $this->tagTaxName;
	}

	public function initTaxManagePages() {
		$this->facilityManagePage = new ManageTaxPages\FacilityManageTaxPage( $this->facilityTaxName );
	}

}
