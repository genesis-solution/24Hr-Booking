<?php

namespace MPHB\Admin;

class Menus {

	private $mainMenuSlug = 'mphb_booking_menu';
	private $mainMenuHookSuffix;
	private $mainMenuCapability;

	/**
	 *
	 * @var array
	 */
	protected $subMenus = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'createMainMenu' ), 10 );
		add_action( 'admin_menu', array( $this, 'addMenuSeparator' ), 10 );
		add_action( 'wp_loaded', array( $this, 'addSubMenus' ), 11 );
		add_filter( 'menu_order', array( $this, 'reorderMenu' ) );
		add_filter( 'custom_menu_order', '__return_true' );
	}

	/**
	 *
	 * @param int      $order
	 * @param string   $parent_slug The slug name for the parent menu (or the file name of a standard WordPress admin page).
	 * @param string   $page_title  The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string   $menu_title  The text to be used for the menu.
	 * @param string   $capability  The capability required for this menu to be displayed to the user.
	 * @param string   $menu_slug   The slug name to refer to this menu by (should be unique for this menu).
	 * @param callable $function    The function to be called to output the content for this page.
	 */
	public function registerSubMenu( $order, $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {

		if ( ! isset( $this->subMenus[ $order ] ) ) {
			$this->subMenus[ $order ] = array();
			add_action( 'admin_menu', array( $this, 'addSubMenus_' . $order ), $order );
		}

		$this->subMenus[ $order ][] = array( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	public function __call( $name, $arguments ) {
		if ( preg_match( '/^addSubMenus_(\d+)$/', $name, $matches ) ) {
			$order = $matches[1];
			if ( isset( $this->subMenus[ $order ] ) ) {
				foreach ( $this->subMenus[ $order ] as $subMenuAtts ) {
					call_user_func_array( 'add_submenu_page', $subMenuAtts );
				}
			}
		}
	}

	/**
	 * Add menu separator.
	 */
	public function addMenuSeparator() {
		global $menu;

		if ( current_user_can( $this->mainMenuCapability ) ) {
			$menu[] = array( '', 'read', 'separator-mphb', '', 'wp-menu-separator mphb' );
		}
	}

	public function createMainMenu() {
		$this->mainMenuCapability = apply_filters( 'mphb_main_menu_capability', 'edit_mphb_bookings' );
		$mainMenuPosition         = apply_filters( 'mphb_main_menu_position', '57.5' );

		$this->mainMenuHookSuffix = add_menu_page(
			__( 'Bookings', 'motopress-hotel-booking' ),
			__( 'Bookings', 'motopress-hotel-booking' ),
			$this->mainMenuCapability,
			$this->mainMenuSlug,
			'__return_false',
			MPHB()->isWPVersion( '4.0', '>=' ) ? 'dashicons-calendar-alt' : null,
			$mainMenuPosition
		);
	}

	/**
	 *
	 * @since 4.0.0 Custom capabilities to access menu added.
	 */
	public function addSubMenus() {

		// Booking Page
		// $bookingPostType     = MPHB()->postTypes()->booking()->getPostType();
		// $bookingPostTypeObj  = get_post_type_object( $bookingPostType );
		// $bookingPageTitle    = $bookingPostTypeObj->labels->add_new_item;
		// $bookingMenuTitle    = $bookingPostTypeObj->labels->add_new;
		// $bookingMenuSlug     = add_query_arg( 'post_type', $bookingPostType, 'post-new.php' );

		// temporary hide add booking page
		// $this->registerSubMenu( 10, $this->mainMenuSlug, $bookingMenuTitle, $bookingMenuTitle, 'edit_posts', $bookingMenuSlug );

		// Payment Page
		$paymentPostType    = MPHB()->postTypes()->payment()->getPostType();
		$paymentPostTypeObj = get_post_type_object( $paymentPostType );
		$paymentPageTitle   = $paymentPostTypeObj->labels->name;
		$paymentMenuTitle   = $paymentPostTypeObj->labels->name;
		$paymentMenuSlug    = add_query_arg( 'post_type', $paymentPostType, 'edit.php' );

		$this->registerSubMenu( 20, $this->mainMenuSlug, $paymentPageTitle, $paymentMenuTitle, 'edit_mphb_payments', $paymentMenuSlug );

		$couponPostType    = MPHB()->postTypes()->coupon()->getPostType();
		$couponPostTypeObj = get_post_type_object( $couponPostType );
		$couponPageTitle   = $couponPostTypeObj->labels->name;
		$couponMenuTitle   = $couponPostTypeObj->labels->name;
		$couponMenuSlug    = add_query_arg( 'post_type', $couponPostType, 'edit.php' );

		$this->registerSubMenu(
			60,
			$this->mainMenuSlug,
			$couponPageTitle,
			$couponMenuTitle,
			'edit_mphb_coupons',
			$couponMenuSlug
		);
	}

	public function getMainMenuSlug() {
		return $this->mainMenuSlug;
	}

	/**
	 * Reorder menu items in admin.
	 *
	 * @param array $menuOrder
	 * @return array
	 */
	public function reorderMenu( $menuOrder ) {

		$customMenuOrder = array();

		$mphbSeparatorMenu = 'separator-mphb';
		$mphbSeparatorKey  = array_search( $mphbSeparatorMenu, $menuOrder );

		$roomTypeMenu    = add_query_arg( 'post_type', MPHB()->postTypes()->roomType()->getPostType(), 'edit.php' );
		$roomTypeMenuKey = array_search( $roomTypeMenu, $menuOrder );

		if ( ! empty( $mphbSeparatorKey ) ) {
			unset( $menuOrder[ $mphbSeparatorKey ] );
		}
		if ( ! empty( $roomTypeMenuKey ) ) {
			unset( $menuOrder[ $roomTypeMenuKey ] );
		}

		foreach ( $menuOrder as $index => $item ) {

			if ( $this->mainMenuSlug == $item ) {
				$customMenuOrder[] = $mphbSeparatorMenu;
				$customMenuOrder[] = $roomTypeMenu;
				$customMenuOrder[] = $item;
			} else {
				$customMenuOrder[] = $item;
			}
		}

		return $customMenuOrder;
	}

}
