<?php

namespace MPHB\ScriptManagers;

class BlockScriptManager extends ScriptManager {

	public function __construct() {

		parent::__construct();

		if ( class_exists( 'WP_Block_Editor_Context' ) ) {
			add_filter( 'block_categories_all', array( $this, 'registerBlockCategory' ) );
		} else {
			add_filter( 'block_categories', array( $this, 'registerBlockCategory' ) );
		}

		add_action( 'init', array( $this, 'register' ) );
	}

	public function registerBlockCategory( $categories ) {
		$categories = array_merge(
			$categories,
			array(
				array(
					'slug'  => 'hotel-booking',
					/* translators: Name of the plugin, do not translate */
					'title' => __( 'Hotel Booking', 'motopress-hotel-booking' ),
				),
			)
		);

		return $categories;
	}

	/**
	 * @param string $blockName
	 * @param string $renderFunction
	 * @param array  $attributes
	 *
	 * @since 3.8.1
	 */
	protected function registerBlock( $blockName, $renderFunction, $attributes ) {
		register_block_type(
			$blockName,
			array(
				'editor_script'   => 'mphb-blocks',
				'render_callback' => array( MPHB()->getBlocksRender(), $renderFunction ),
				'attributes'      => apply_filters( 'mphb_block_attributes', $attributes, $blockName ),
			)
		);
	}

	public function register() {
		wp_register_script( 'mphb-blocks', $this->scriptUrl( 'assets/blocks/blocks.min.js' ), array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor', 'jquery' ), MPHB()->getVersion(), true );

		$roomTypeIds = MPHB()->getRoomTypePersistence()->getPosts(
			array(
				'fields'      => 'ids',
				'post_status' => mphb_readable_post_statuses(),
			)
		);

		wp_localize_script(
			'mphb-blocks',
			'MPHBBlockEditor',
			array(
				'minAdults'   => MPHB()->settings()->main()->getMinAdults(),
				'maxAdults'   => MPHB()->settings()->main()->getSearchMaxAdults(),
				'minChildren' => MPHB()->settings()->main()->getMinChildren(),
				'maxChildren' => MPHB()->settings()->main()->getSearchMaxChildren(),
				'dateFormat'  => MPHB()->settings()->dateTime()->getDateFormatJS(),
				'roomTypeIds' => $roomTypeIds,
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/availability-search',
			'renderSearch',
			array(
				'adults'         => array(
					'type'    => 'number',
					'default' => '',
				),
				'children'       => array(
					'type'    => 'number',
					'default' => '',
				),
				'check_in_date'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'check_out_date' => array(
					'type'    => 'string',
					'default' => '',
				),
				'attributes'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'alignment'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'      => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/availability-calendar',
			'renderAvailabilityCalendar',
			array(
				'id'               => array(
					'type'    => 'string',
					'default' => '',
				),
				'monthstoshow'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'display_price'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'truncate_price'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'display_currency' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'alignment'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'        => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/search-results',
			'renderSearchResults',
			array(
				'title'          => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'featured_image' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'gallery'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'excerpt'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'details'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'price'          => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'view_button'    => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'orderby'        => array(
					'type'    => 'string',
					'default' => 'menu_order',
				),
				'order'          => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'meta_key'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'meta_type'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'alignment'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'      => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/rooms',
			'renderRooms',
			array(
				'title'          => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'featured_image' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'gallery'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'excerpt'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'details'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'price'          => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'view_button'    => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'book_button'    => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'ids'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'posts_per_page' => array(
					'type'    => 'string',
					'default' => '',
				),
				'category'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'tags'           => array(
					'type'    => 'string',
					'default' => '',
				),
				'relation'       => array(
					'type'    => 'string',
					'default' => 'OR',
				),
				'orderby'        => array(
					'type'    => 'string',
					'default' => 'menu_order',
				),
				'order'          => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'meta_key'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'meta_type'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'alignment'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'      => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/services',
			'renderServices',
			array(
				'ids'            => array(
					'type'    => 'string',
					'default' => '',
				),
				'posts_per_page' => array(
					'type'    => 'string',
					'default' => '',
				),
				'orderby'        => array(
					'type'    => 'string',
					'default' => 'menu_order',
				),
				'order'          => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'meta_key'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'meta_type'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'alignment'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'      => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/room',
			'renderRoom',
			array(
				'id'             => array(
					'type'    => 'string',
					'default' => '',
				),
				'title'          => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'featured_image' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'gallery'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'excerpt'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'details'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'price'          => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'view_button'    => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'book_button'    => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'alignment'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'className'      => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/checkout',
			'renderCheckout',
			array(
				'alignment' => array(
					'type'    => 'string',
					'default' => '',
				),
				'className' => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/availability',
			'renderBookingForm',
			array(
				'id'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'alignment' => array(
					'type'    => 'string',
					'default' => '',
				),
				'className' => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/rates',
			'renderRoomRates',
			array(
				'id'        => array(
					'type'    => 'string',
					'default' => '',
				),
				'alignment' => array(
					'type'    => 'string',
					'default' => '',
				),
				'className' => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);

		$this->registerBlock(
			'motopress-hotel-booking/booking-confirmation',
			'renderBookingConfirmation',
			array(
				'alignment' => array(
					'type'    => 'string',
					'default' => '',
				),
				'className' => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);
	}

	public function enqueue() {}
}
