<?php

namespace MPHB\Admin\MenuPages;

class ShortcodesMenuPage extends AbstractMenuPage {

	private $shortcodes = array();

	public function addActions() {
		parent::addActions();
		add_action( 'admin_init', array( $this, 'initShortcodes' ) );
	}

	/**
	 *
	 * @since 3.9.6 - new option 'price' added for 'orderby' attribute.
	 */
	public function initShortcodes() {

		$this->shortcodes[ MPHB()->getShortcodes()->getSearch()->getName() ] = array(
			'label'       => __( 'Availability Search Form', 'motopress-hotel-booking' ),
			'description' => __( 'Display search form.', 'motopress-hotel-booking' ),
			'parameters'  => array(
				'adults'         => array(
					'label'   => __( 'The number of adults presetted in the search form.', 'motopress-hotel-booking' ),
					'values'  => sprintf( '%d...%d', MPHB()->settings()->main()->getMinAdults(), MPHB()->settings()->main()->getSearchMaxAdults() ),
					'default' => strval( MPHB()->settings()->main()->getMinAdults() ),
				),
				'children'       => array(
					'label'   => __( 'The number of children presetted in the search form.', 'motopress-hotel-booking' ),
					'values'  => sprintf( '%d...%d', 0, MPHB()->settings()->main()->getSearchMaxChildren() ),
					'default' => strval( 0 ),
				),
				'check_in_date'  => array(
					'label'   => __( 'Check-in date presetted in the search form.', 'motopress-hotel-booking' ),
					'values'  => sprintf( __( 'date in format %s', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormat() ),
					'default' => '',
				),
				'check_out_date' => array(
					'label'   => __( 'Check-out date presetted in the search form.', 'motopress-hotel-booking' ),
					'values'  => sprintf( __( 'date in format %s', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormat() ),
					'default' => '',
				),
				'attributes'     => array(
					'label'   => __( 'Custom attributes for advanced search.', 'motopress-hotel-booking' ),
					'values'  => __( 'Comma-separated slugs of attributes.', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'class'          => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'     => array(
				'shortcode' => MPHB()->getShortcodes()->getSearch()->generateShortcode(),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getAvailabilityCalendar()->getName() ] = array(
			'label'       => __( 'Availability Calendar', 'motopress-hotel-booking' ),
			'description' => '',
			'parameters'  => array(
				'id'               => array(
					'label'       => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
					'description' => __( 'ID of Accommodation Type to check availability.', 'motopress-hotel-booking' ) . $this->getOptionalLabel(),
					'values'      => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'monthstoshow'     => array(
					'label'       => __( 'How many months to show.', 'motopress-hotel-booking' ),
					'description' => '',
					'values'      => __( 'Set the number of columns or the number of rows and columns separated by comma. Example: "3" or "2,3"', 'motopress-hotel-booking' ),
					'default'     => '2',
				),
				'display_price'    => array(
					'label'       => __( 'Display per-night prices in the availability calendar.', 'motopress-hotel-booking' ),
					'description' => '',
					'values'      => 'true | false',
					'default'     => 'false',
				),
				'truncate_price'   => array(
					'label'       => __( 'Truncate per-night prices in the availability calendar.', 'motopress-hotel-booking' ),
					'description' => '',
					'values'      => 'true | false',
					'default'     => 'true',
				),
				'display_currency' => array(
					'label'       => __( 'Display the currency sign in the availability calendar.', 'motopress-hotel-booking' ),
					'description' => '',
					'values'      => 'true | false',
					'default'     => 'false',
				),
				'class'            => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'     => array(
				'shortcode'   => MPHB()->getShortcodes()->getAvailabilityCalendar()->generateShortcode(
					array(
						'id'           => '999',
						'monthstoshow' => '2,3',
					)
				),
				'description' => __( 'Display availability calendar of the current accommodation type or by ID.', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getSearchResults()->getName() ] = array(
			'label'       => __( 'Availability Search Results', 'motopress-hotel-booking' ),
			'description' => __( 'Display listing of accommodation types that meet the search criteria.', 'motopress-hotel-booking' ),
			'parameters'  => array(
				'title'           => array(
					'label'   => __( 'Whether to display title of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'featured_image'  => array(
					'label'   => __( 'Whether to display featured image of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'gallery'         => array(
					'label'   => __( 'Whether to display gallery of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'excerpt'         => array(
					'label'   => __( 'Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'details'         => array(
					'label'   => __( 'Whether to display details of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'price'           => array(
					'label'   => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'view_button'     => array(
					'label'       => __( 'Show View Details button', 'motopress-hotel-booking' ),
					'description' => __( 'Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking' ),
					'values'      => 'true | false (yes,1,on | no,0,off)',
					'default'     => 'true',
				),
				'orderby'         => array(
					'label'   => __( 'Sort by.', 'motopress-hotel-booking' ),
					'values'  => sprintf(
						__( '%1$s. See the <a href="%2$s" target="_blank">full list</a>.', 'motopress-hotel-booking' ),
						'price, order, ID, title, date, menu_order',
						'https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters'
					),
					'default' => 'menu_order',
				),
				'order'           => array(
					'label'       => __( 'Designates the ascending or descending order of sorting.', 'motopress-hotel-booking' ),
					'values'      => 'ASC, DESC',
					'description' => __( 'ASC - from lowest to highest values (1, 2, 3). DESC - from highest to lowest values (3, 2, 1).', 'motopress-hotel-booking' ),
					'default'     => 'DESC',
				),
				'meta_key'        => array(
					'label'   => __( 'Custom field name. Required if "orderby" is one of the "meta_value", "meta_value_num" or "meta_value_*".', 'motopress-hotel-booking' ),
					'values'  => __( 'custom field name', 'motopress-hotel-booking' ),
					'default' => __( 'empty string', 'motopress-hotel-booking' ),
				),
				'meta_type'       => array(
					'label'   => __( 'Specified type of the custom field. Can be used in conjunction with orderby="meta_value".', 'motopress-hotel-booking' ),
					'values'  => sprintf(
						__( '%1$s. See the <a href="%2$s" target="_blank">full list</a>.', 'motopress-hotel-booking' ),
						'NUMERIC, CHAR, DATETIME',
						'https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters'
					),
					'default' => __( 'empty string', 'motopress-hotel-booking' ),
				),
				'class'           => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'default_sorting' => array(
					'label'      => __( 'Sort by. Use "orderby" insted.', 'motopress-hotel-booking' ),
					'values'     => 'order, price',
					'default'    => 'order',
					'deprecated' => '2.7.5',
				),
				'book_button'     => array(
					'label'       => __( 'Show Book button', 'motopress-hotel-booking' ),
					'description' => __( 'Whether to display Book button.', 'motopress-hotel-booking' ),
					'values'      => 'true | false (yes,1,on | no,0,off)',
					'default'     => 'true',
					'deprecated'  => '2.0.0',
				),
			),
			'example'     => array(
				'shortcode'   => MPHB()->getShortcodes()->getSearchResults()->generateShortcode(
					array(
						'orderby' => 'price',
					)
				),
				'description' => __( 'Search Results sorting by price.' ) . '<br/>' . '<strong>' . __( 'NOTE:', 'motopress-hotel-booking' ) . '</strong>&nbsp;' . sprintf( __( 'Use only on page that you set as Search Results Page in <a href="%s">Settings</a>', 'motopress-hotel-booking' ), MPHB()->getSettingsMenuPage()->getUrl() ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getRooms()->getName() ] = array(
			'label'      => __( 'Accommodation Types Listing', 'motopress-hotel-booking' ),
			'parameters' => array(
				'title'          => array(
					'label'   => __( 'Whether to display title of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'featured_image' => array(
					'label'   => __( 'Whether to display featured image of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'gallery'        => array(
					'label'   => __( 'Whether to display gallery of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'excerpt'        => array(
					'label'   => __( 'Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'details'        => array(
					'label'   => __( 'Whether to display details of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'price'          => array(
					'label'   => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'view_button'    => array(
					'label'       => __( 'Show View Details button', 'motopress-hotel-booking' ),
					'description' => __( 'Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking' ),
					'values'      => 'true | false (yes,1,on | no,0,off)',
					'default'     => 'true',
				),
				'book_button'    => array(
					'label'       => __( 'Show Book button', 'motopress-hotel-booking' ),
					'description' => __( 'Whether to display Book button.', 'motopress-hotel-booking' ),
					'values'      => 'true | false (yes,1,on | no,0,off)',
					'default'     => 'true',
				),
				'posts_per_page' => array(
					'label'   => __( 'Count per page', 'motopress-hotel-booking' ),
					'values'  => __( 'integer, -1 to display all, default: "Blog pages show at most"', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'class'          => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'category'       => array(
					'label'   => __( 'IDs of categories that will be shown.', 'motopress-hotel-booking' ),
					'values'  => __( 'Comma-separated IDs.', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'tags'           => array(
					'label'   => __( 'IDs of tags that will be shown.', 'motopress-hotel-booking' ),
					'values'  => __( 'Comma-separated IDs.', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'ids'            => array(
					'label'   => __( 'IDs of accommodations that will be shown.', 'motopress-hotel-booking' ),
					'values'  => __( 'Comma-separated IDs.', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'relation'       => array(
					'label'   => __( 'Logical relationship between each taxonomy when there is more than one.', 'motopress-hotel-booking' ),
					'values'  => 'AND, OR',
					'default' => 'OR',
				),
				'orderby'        => array(
					'label'   => __( 'Sort by.', 'motopress-hotel-booking' ),
					'values'  => sprintf(
						__( '%1$s. See the <a href="%2$s" target="_blank">full list</a>.', 'motopress-hotel-booking' ),
						'ID, title, date, menu_order, price',
						'https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters'
					),
					'default' => 'date',
				),
				'order'          => array(
					'label'       => __( 'Designates the ascending or descending order of sorting.', 'motopress-hotel-booking' ),
					'values'      => 'ASC, DESC',
					'description' => __( 'ASC - from lowest to highest values (1, 2, 3). DESC - from highest to lowest values (3, 2, 1).', 'motopress-hotel-booking' ),
					'default'     => 'DESC',
				),
				'meta_key'       => array(
					'label'   => __( 'Custom field name. Required if "orderby" is one of the "meta_value", "meta_value_num" or "meta_value_*".', 'motopress-hotel-booking' ),
					'values'  => __( 'custom field name', 'motopress-hotel-booking' ),
					'default' => __( 'empty string', 'motopress-hotel-booking' ),
				),
				'meta_type'      => array(
					'label'   => __( 'Specified type of the custom field. Can be used in conjunction with orderby="meta_value".', 'motopress-hotel-booking' ),
					'values'  => sprintf(
						__( '%1$s. See the <a href="%2$s" target="_blank">full list</a>.', 'motopress-hotel-booking' ),
						'NUMERIC, CHAR, DATETIME',
						'https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters'
					),
					'default' => __( 'empty string', 'motopress-hotel-booking' ),
				),
			),
			'example'    => array(
				'shortcode' => MPHB()->getShortcodes()->getRooms()->generateShortcode(),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getServices()->getName() ] = array(
			'label'      => __( 'Services Listing', 'motopress-hotel-booking' ),
			'parameters' => array(
				'ids'            => array(
					'label'       => __( 'IDs', 'motopress-hotel-booking' ),
					'values'      => __( 'Comma-separated IDs.', 'motopress-hotel-booking' ),
					'description' => __( 'IDs of services that will be shown. ', 'motopress-hotel-booking' ),
				),
				'posts_per_page' => array(
					'label'   => __( 'Count per page', 'motopress-hotel-booking' ),
					'values'  => 'integer, -1 to display all, default: "Blog pages show at most"',
					'default' => '',
				),
				'orderby'        => array(
					'label'   => __( 'Sort by.', 'motopress-hotel-booking' ),
					'values'  => sprintf(
						__( '%1$s. See the <a href="%2$s" target="_blank">full list</a>.', 'motopress-hotel-booking' ),
						'ID, title, date, menu_order',
						'https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters'
					),
					'default' => 'menu_order',
				),
				'order'          => array(
					'label'       => __( 'Designates the ascending or descending order of sorting.', 'motopress-hotel-booking' ),
					'values'      => 'ASC, DESC',
					'description' => __( 'ASC - from lowest to highest values (1, 2, 3). DESC - from highest to lowest values (3, 2, 1).', 'motopress-hotel-booking' ),
					'default'     => 'DESC',
				),
				'meta_key'       => array(
					'label'   => __( 'Custom field name. Required if "orderby" is one of the "meta_value", "meta_value_num" or "meta_value_*".', 'motopress-hotel-booking' ),
					'values'  => __( 'custom field name', 'motopress-hotel-booking' ),
					'default' => __( 'empty string', 'motopress-hotel-booking' ),
				),
				'meta_type'      => array(
					'label'   => __( 'Specified type of the custom field. Can be used in conjunction with orderby="meta_value".', 'motopress-hotel-booking' ),
					'values'  => sprintf(
						__( '%1$s. See the <a href="%2$s" target="_blank">full list</a>.', 'motopress-hotel-booking' ),
						'NUMERIC, CHAR, DATETIME',
						'https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters'
					),
					'default' => __( 'empty string', 'motopress-hotel-booking' ),
				),
				'class'          => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'    => array(
				'shortcode'   => MPHB()->getShortcodes()->getServices()->generateShortcode(),
				'description' => __( 'Show All Services', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getRoom()->getName() ] = array(
			'label'      => __( 'Single Accommodation Type', 'motopress-hotel-booking' ),
			'parameters' => array(
				'id'              => array(
					'label'       => __( 'ID', 'motopress-hotel-booking' ),
					'description' => __( 'ID of accommodation type to display.', 'motopress-hotel-booking' ) . $this->getRequiredLabel(),
					'values'      => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'title'           => array(
					'label'   => __( 'Whether to display title of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'featured_image'  => array(
					'label'   => __( 'Whether to display featured image of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'gallery'         => array(
					'label'   => __( 'Whether to display gallery of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'excerpt'         => array(
					'label'   => __( 'Whether to display excerpt (short description) of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'details'         => array(
					'label'   => __( 'Whether to display details of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'price'           => array(
					'label'   => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'view_button'     => array(
					'label'       => __( 'Show View Details button', 'motopress-hotel-booking' ),
					'description' => __( 'Whether to display "View Details" button with the link to accommodation type.', 'motopress-hotel-booking' ),
					'values'      => 'true | false (yes,1,on | no,0,off)',
					'default'     => 'false',
				),
				'book_button'     => array(
					'label'   => __( 'Whether to display Book button.', 'motopress-hotel-booking' ),
					'values'  => 'true | false (yes,1,on | no,0,off)',
					'default' => 'true',
				),
				'class'           => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
				'price_per_night' => array(
					'label'      => __( 'Whether to display price of the accommodation type.', 'motopress-hotel-booking' ),
					'values'     => 'true | false (yes,1,on | no,0,off)',
					'default'    => 'false',
					'deprecated' => '1.2.0',
				),
			),
			'example'    => array(
				'shortcode'   => MPHB()->getShortcodes()->getRoom()->generateShortcode(
					array(
						'id'             => '777',
						'title'          => 'true',
						'featured_image' => 'true',
					)
				),
				'description' => __( 'Display accommodation type with title and image.', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getCheckout()->getName() ] = array(
			'label'       => __( 'Checkout Form', 'motopress-hotel-booking' ),
			'description' => __( 'Display checkout form.', 'motopress-hotel-booking' ),
			'parameters'  => array(
				'class' => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'     => array(
				'shortcode'   => MPHB()->getShortcodes()->getCheckout()->generateShortcode(),
				'description' => '<strong>' . __( 'NOTE:', 'motopress-hotel-booking' ) . '</strong>&nbsp;' . sprintf( __( 'Use only on page that you set as Checkout Page in <a href="%s">Settings</a>', 'motopress-hotel-booking' ), MPHB()->getSettingsMenuPage()->getUrl() ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getBookingForm()->getName() ] = array(
			'label'      => __( 'Booking Form', 'motopress-hotel-booking' ),
			'parameters' => array(
				'id'    => array(
					'label'       => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
					'description' => __( 'ID of Accommodation Type to check availability.', 'motopress-hotel-booking' ) . $this->getOptionalLabel(),
					'values'      => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'class' => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'    => array(
				'shortcode'   => MPHB()->getShortcodes()->getBookingForm()->generateShortcode(
					array(
						'id' => '777',
					)
				),
				'description' => __( 'Show Booking Form for Accommodation Type with id 777', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getRoomRates()->getName() ] = array(
			'label'      => __( 'Accommodation Rates List', 'motopress-hotel-booking' ),
			'parameters' => array(
				'id'    => array(
					'label'       => __( 'Accommodation Type ID', 'motopress-hotel-booking' ),
					'description' => __( 'ID of accommodation type.', 'motopress-hotel-booking' ) . $this->getOptionalLabel(),
					'values'      => __( 'integer number', 'motopress-hotel-booking' ),
				),
				'class' => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'    => array(
				'shortcode'   => MPHB()->getShortcodes()->getRoomRates()->generateShortcode(
					array(
						'id' => '777',
					)
				),
				'description' => __( 'Show Accommodation Rates List for accommodation type with id 777', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getBookingConfirmation()->getName() ] = array(
			'label'       => __( 'Booking Confirmation', 'motopress-hotel-booking' ),
			'description' => __( 'Display booking and payment details.', 'motopress-hotel-booking' ),
			'parameters'  => array(
				'class' => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'     => array(
				'shortcode'   => MPHB()->getShortcodes()->getBookingConfirmation()->generateShortcode(),
				'description' => __( 'Use this shortcode on Booking Confirmed and Reservation Received pages', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getBookingCancellation()->getName() ] = array(
			'label'       => __( 'Booking Cancelation', 'motopress-hotel-booking' ),
			'description' => __( 'Display booking cancelation details.', 'motopress-hotel-booking' ),
			'parameters'  => array(
				'class' => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'     => array(
				'shortcode'   => MPHB()->getShortcodes()->getBookingCancellation()->generateShortcode(),
				'description' => __( 'Use this shortcode on the Booking Cancelation page', 'motopress-hotel-booking' ),
			),
		);

		$this->shortcodes[ MPHB()->getShortcodes()->getAccount()->getName() ] = array(
			'label'       => __( 'Customer Account', 'motopress-hotel-booking' ),
			'description' => __( 'Display log in form or customer account area.', 'motopress-hotel-booking' ),
			'parameters'  => array(
				'class' => array(
					'label'   => __( 'Custom CSS class for shortcode wrapper', 'motopress-hotel-booking' ),
					'values'  => __( 'whitespace separated css classes', 'motopress-hotel-booking' ),
					'default' => '',
				),
			),
			'example'     => array(
				'shortcode'   => MPHB()->getShortcodes()->getAccount()->generateShortcode(),
				'description' => __( 'Use this shortcode to create the My Account page.', 'motopress-hotel-booking' ),
			),
		);

		$customShortcodes = apply_filters( 'mphb_display_custom_shortcodes', array() );

		$this->shortcodes = array_merge( $this->shortcodes, $customShortcodes );
	}

	public function render() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Shortcodes', 'motopress-hotel-booking' ); ?></h1>
			<table class="widefat striped">
				<thead>
					<tr class="">
						<td><?php esc_html_e( 'Shortcode', 'motopress-hotel-booking' ); ?></td>
						<td><?php esc_html_e( 'Parameters', 'motopress-hotel-booking' ); ?></td>
						<td><?php esc_html_e( 'Example', 'motopress-hotel-booking' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $this->shortcodes as $name => $details ) { ?>
						<tr valign="top" >
							<th scope="row">
								<?php $this->renderShortcodeCell( $name, $details ); ?>
							</th>
							<td scope="row">
								<?php $this->renderParametersCell( $name, $details ); ?>
							</td>
							<td scope="row">
								<?php $this->renderExampleCell( $name, $details ); ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * @since 3.7.0 Added the field "deprecated" to parameter $details.
	 */
	private function renderShortcodeCell( $name, $details ) {
		?>
		<strong>
			<?php echo esc_html( $details['label'] ); ?>
		</strong>
		<?php if ( ! empty( $details['deprecated'] ) ) { ?>
			<em><?php echo esc_html( sprintf( __( 'Deprecated since %s', 'motopress-hotel-booking' ), $details['deprecated'] ) ); ?></em>
		<?php } ?>
		<p>
			<code>[<?php echo esc_html( $name ); ?>]</code>
		</p>
		<?php if ( isset( $details['description'] ) ) { ?>
			<p class="description">
				<?php echo wp_kses_post( $details['description'] ); ?>
			</p>
		<?php } ?>
		<?php
	}

	private function renderParametersCell( $name, $details ) {
		if ( empty( $details['parameters'] ) ) {
			?>
			<span aria-hidden="true">&#8212;</span>
		<?php } else { ?>
			<?php foreach ( $details['parameters'] as $paramName => $paramDetails ) { ?>
				<p>
					<code><?php echo esc_html( $paramName ); ?></code>
					<?php if ( isset( $paramDetails['deprecated'] ) && $paramDetails['deprecated'] ) { ?>
						<strong><?php echo esc_html( sprintf( __( 'Deprecated since %s', 'motopress-hotel-booking' ), $paramDetails['deprecated'] ) ); ?></strong>
					<?php } ?>
					<em><?php echo esc_html( $paramDetails['label'] ); ?></em>
				</p>
				<?php if ( isset( $paramDetails['description'] ) ) { ?>
					<p class="description">
						<?php echo wp_kses_post( $paramDetails['description'] ); ?>
					</p>
				<?php } ?>
				<p>
					<em><?php esc_html_e( 'Values:', 'motopress-hotel-booking' ); ?></em>
					<?php echo wp_kses_post( $paramDetails['values'] ); ?>
				</p>
				<?php if ( isset( $paramDetails['default'] ) ) { ?>
					<p>
						<em><?php esc_html_e( 'Default:', 'motopress-hotel-booking' ); ?></em>

						<?php
						switch ( $paramDetails['default'] ) {
							case '':
								esc_html_e( 'empty string', 'motopress-hotel-booking' );
								break;
							default:
								echo esc_html( $paramDetails['default'] );
								break;
						}
						?>

					</p>
				<?php } ?>
				<hr/>
			<?php } ?>
			<?php
		}
	}

	private function renderExampleCell( $name, $details ) {
		?>
		<p>
			<code>
				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $details['example']['shortcode'];
				?>
			</code>
		</p>
		<?php if ( isset( $details['example']['description'] ) ) { ?>
			<p class="description">
				<?php echo wp_kses_post( $details['example']['description'] ); ?>
			</p>
		<?php } ?>
		<?php
	}

	public function onLoad() {

	}

	/**
	 *
	 * @return string
	 */
	private function getOptionalLabel() {
		return '<em>' . __( 'Optional.', 'motopress-hotel-booking' ) . '</em>';
	}

	/**
	 *
	 * @return string
	 */
	private function getRequiredLabel() {
		return '<strong>' . __( 'Required', 'motopress-hotel-booking' ) . '</strong>';
	}

	protected function getMenuTitle() {
		return __( 'Shortcodes', 'motopress-hotel-booking' );
	}

	protected function getPageTitle() {
		return __( 'Shortcodes', 'motopress-hotel-booking' );
	}

}
