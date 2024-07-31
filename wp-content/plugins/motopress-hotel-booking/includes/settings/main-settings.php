<?php

namespace MPHB\Settings;

class MainSettings {

	private $defaultUserApprovalTime = 20;
	private $defaultConfirmationMode = 'auto';
	private $countriesBundle;
	private $customerBundle = null;

	/**
	 *
	 * @var array
	 */
	private $datepickThemes;

	/**
	 * @since 3.9
	 */
	private $adminDatepickThemes;

	public function __construct() {
		$this->datepickThemes      = array(
			''               => __( 'Default', 'motopress-hotel-booking' ),
			'dark-blue'      => __( 'Dark Blue', 'motopress-hotel-booking' ),
			'dark-green'     => __( 'Dark Green', 'motopress-hotel-booking' ),
			'dark-red'       => __( 'Dark Red', 'motopress-hotel-booking' ),
			'grayscale'      => __( 'Grayscale', 'motopress-hotel-booking' ),
			'light-blue'     => __( 'Light Blue', 'motopress-hotel-booking' ),
			'light-coral'    => __( 'Light Coral', 'motopress-hotel-booking' ),
			'light-green'    => __( 'Light Green', 'motopress-hotel-booking' ),
			'light-yellow'   => __( 'Light Yellow', 'motopress-hotel-booking' ),
			'minimal-blue'   => __( 'Minimal Blue', 'motopress-hotel-booking' ),
			'minimal-orange' => __( 'Minimal Orange', 'motopress-hotel-booking' ),
			'minimal'        => __( 'Minimal', 'motopress-hotel-booking' ),
			'peru'           => __( 'Peru', 'motopress-hotel-booking' ),
			'sky-blue'       => __( 'Sky Blue', 'motopress-hotel-booking' ),
			'slate-blue'     => __( 'Slate Blue', 'motopress-hotel-booking' ),
			'turquoise'      => __( 'Turquoise', 'motopress-hotel-booking' ),
		);
		$this->adminDatepickThemes = array(
			'admin' => __( 'Default', 'motopress-hotel-booking' ),
		);
		$this->countriesBundle     = new \MPHB\Bundles\CountriesBundle();
	}

	function getDefaultUserApprovalTime() {
		return $this->defaultUserApprovalTime;
	}

	function getDefaultConfirmationMode() {
		return $this->defaultConfirmationMode;
	}

	/**
	 *
	 * @return int Minutes
	 */
	public function getUserApprovalTime() {
		return (int) get_option( 'mphb_user_approval_time', $this->defaultUserApprovalTime );
	}

	/**
	 *
	 * @return array
	 */
	public function getBedTypesList() {
		$bedsList = array();
		$beds     = get_option( 'mphb_bed_types', array() );

		if ( ! is_array( $beds ) ) {
			return $bedsList;
		}

		foreach ( $beds as $bed ) {
			if ( ! empty( $bed['type'] ) ) {
				$bedsList[ $bed['type'] ] = $bed['type'];
			}
		}

		return $bedsList;
	}

	/**
	 * Retrieve confirmation mode. Possible values 'manual', 'auto', 'payment'.
	 *
	 * @return string
	 */
	public function getConfirmationMode() {
		$mode = get_option( 'mphb_confirmation_mode', $this->defaultConfirmationMode );
		return $mode;
	}

	/**
	 *
	 * @return int
	 */
	public function getMinAdults() {
		return 1;
	}

	/**
	 *
	 * @return int
	 */
	public function getMinChildren() {
		return 0;
	}

	/**
	 *
	 * @return array
	 */
	public function getAdultsListForSearch() {
		$values = array_map( 'strval', range( $this->getMinAdults(), $this->getSearchMaxAdults() ) );
		return array_combine( $values, $values );
	}

	/**
	 *
	 * @return array
	 */
	public function getChildrenListForSearch() {
		$values = array_map( 'strval', range( 0, $this->getSearchMaxChildren() ) );
		return array_combine( $values, $values );
	}

	/**
	 *
	 * @return int
	 */
	public function getSearchMaxAdults() {
		$maxAdults = get_option( 'mphb_search_max_adults', 30 );
		return intval( $maxAdults );
	}

	/**
	 *
	 * @return int
	 */
	public function getSearchMaxChildren() {
		$maxChildren = get_option( 'mphb_search_max_children', 10 );
		return intval( $maxChildren );
	}

	/**
	 *
	 * @return string
	 */
	public function getChildrenAgeText() {
		$text = get_option( 'mphb_children_age', '' );
		$text = apply_filters( 'mphb_translate_string', $text, 'mphb_children_age' );
		return $text;
	}

	/**
	 * @return bool
	 */
	public function isDirectBooking() {
		return $this->isDirectRoomBooking() || $this->isDirectSearchResultsBooking();
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.3
	 */
	public function isDirectRoomBooking() {
		 return (bool) get_option( 'mphb_direct_booking', false );
	}

	/**
	 * @return string "disabled"|"enabled"|"capacity"
	 *
	 * @since 3.8.3
	 */
	public function getDirectBookingPricing() {
		 return get_option( 'mphb_direct_booking_price', 'disabled' );
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.3
	 */
	public function isDirectSearchResultsBooking() {
		return (bool) get_option( 'mphb_direct_search_results', false );
	}

	/**
	 * Check whether to use templates from plugin
	 *
	 * @return bool
	 */
	public function isPluginTemplateMode() {
		return $this->getTemplateMode() === 'plugin';
	}

	/**
	 * Retrieve template mode. Possible values: plugin, theme.
	 *
	 * @return string
	 */
	public function getTemplateMode() {
		return current_theme_supports( 'motopress-hotel-booking' ) ? 'plugin' : get_option( 'mphb_template_mode', 'theme' );
	}

	/**
	 *
	 * @return bool
	 */
	public function isBookingDisabled() {
		$disabled = get_option( 'mphb_booking_disabled', false );
		return (bool) $disabled;
	}

	/**
	 *
	 * @return string
	 */
	public function getDisabledBookingText() {
		return $this->getTranslatedOption( 'mphb_disabled_booking_text' );
	}

	/**
	 *
	 * @return bool
	 */
	public function canUserCancelBooking() {
		$canUserCancel = get_option( 'mphb_user_can_cancel_booking', false );
		return (bool) $canUserCancel;
	}

	/**
	 *
	 * @return string containing html
	 */
	public function getTermsAndConditionsText() {
		$pageId = MPHB()->settings()->pages()->getTermsAndConditionsPageId();
		$page   = ( $pageId > 0 ) ? get_post( $pageId ) : null;

		$content = '';

		if ( $page ) {
			$content = apply_filters( 'the_content', $page->post_content );

			// Fix the filter "wpautop" for future blocks. Otherwise the filter
			// will break the markup of those blocks
			mphb_fix_blocks_autop();
		}

		return $content;
	}

	/**
	 *
	 * @return string containing html
	 */
	public function getCheckoutText() {
		return $this->getTranslatedOption( 'mphb_checkout_text' );
	}

	/**
	 *
	 * @return int
	 */
	public function getAveragePricePeriod() {
		return (int) get_option( 'mphb_average_price_period', 7 );
	}

	public function isUseSingleRoomTypeGalleryMagnific() {
		$useMagnific = get_option( 'mphb_single_room_type_gallery_use_magnific', true );
		return (bool) apply_filters( 'mphb_single_room_type_gallery_use_magnific', $useMagnific );
	}

	public function isRoomTypeCalendarShowPrices() {
		return (bool) get_option( 'mphb_room_type_calendar_show_prices', false );
	}

	public function isRoomTypeCalendarTruncatePrices() {
		return (bool) get_option( 'mphb_room_type_calendar_truncate_prices', true );
	}

	public function isRoomTypeCalendarShowPricesCurrency() {
		return (bool) get_option( 'mphb_room_type_calendar_show_prices_currency', false );
	}

	public function isEnabledRecommendation() {
		return (bool) get_option( 'mphb_enable_recommendation', true );
	}

	/**
	 *
	 * @return string
	 */
	public function getDatepickerTheme() {
		return get_option( 'mphb_datepicker_theme', '' );
	}

	public function getDatepickerAdminTheme() {
		return apply_filters( 'mphb_dashboard_default_datepick_theme', 'admin' );
	}

	public function getDatepickerCurrentTheme() {
		return is_admin() ? $this->getDatepickerAdminTheme() : $this->getDatepickerTheme();
	}

	/**
	 *
	 * @return string
	 */
	public function getDatepickerThemeClass() {
		$theme = $this->getDatepickerCurrentTheme();

		if (
			$theme === '' // Default theme
			|| ! array_key_exists( $theme, $this->datepickThemes ) && ! array_key_exists( $theme, $this->adminDatepickThemes )
		) {
			return '';
		}

		return "mphb-datepicker-{$theme}";
	}

	/**
	 *
	 * @return array
	 */
	public function getDatepickerThemesList() {
		return $this->datepickThemes;
	}

	/**
	 * @since 3.9
	 *
	 * @return array
	 */
	public function getAdminDatepickerThemesList() {
		return $this->adminDatepickThemes;
	}

	/**
	 *
	 * @return bool
	 */
	public function isRequireCountry() {
		return (bool) get_option( 'mphb_require_country', true ) || $this->isRequireFullAddress();
	}

	/**
	 *
	 * @return bool
	 */
	public function isRequireFullAddress() {
		return (bool) get_option( 'mphb_require_full_address', false );
	}

	/**
	 * @return bool
	 *
	 * @since 3.6.1
	 */
	public function isCustomerRequiredOnAdmin() {
		return (bool) get_option( 'mphb_require_customer_on_admin', false );
	}

	/**
	 *
	 * @return bool
	 */
	public function isCouponsEnabled() {
		return (bool) get_option( 'mphb_enable_coupons', false );
	}

	/**
	 *
	 * @return \MPHB\Bundles\CountriesBundle
	 */
	public function getCountriesBundle() {
		return $this->countriesBundle;
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultCountry() {
		return get_option( 'mphb_default_country', '' );
	}

	/**
	 * @return \MPHB\Bundles\CustomerBundle
	 *
	 * @since 3.7.2
	 */
	public function getCustomerBundle() {
		if ( is_null( $this->customerBundle ) ) {
			$this->customerBundle = new \MPHB\Bundles\CustomerBundle();
		}

		return $this->customerBundle;
	}

	/**
	 *
	 * @param string $name
	 * @param mixed  $default Optional.
	 * @return mixed
	 */
	private function getTranslatedOption( $name, $default = '' ) {
		$value = get_option( $name, $default );

		// allow wpml translate string
		$value = apply_filters( 'mphb_translate_string', $value, $name );

		return $value;
	}

	/**
	 *
	 * @return string
	 */
	public function getGuestManagementStatus() {
		return get_option( 'mphb_guest_management', 'allow-all' );
	}

	/**
	 *
	 * @return bool
	 */
	public function isAdultsAllowed() {
		return $this->getGuestManagementStatus() !== 'disable-all';
	}

	/**
	 *
	 * @return bool
	 */
	public function isChildrenAllowed() {
		return $this->getGuestManagementStatus() === 'allow-all';
	}

	/**
	 *
	 * @return bool
	 */
	public function isGuestsHiddenInSearch() {
		return (bool) get_option( 'mphb_hide_guests_on_search', false );
	}

	/**
	 *
	 * @return bool
	 */
	public function isAdultsDisabledOrHidden() {
		return ! $this->isAdultsAllowed() || $this->isGuestsHiddenInSearch();
	}

	/**
	 *
	 * @return bool
	 */
	public function isChildrenDisabledOrHidden() {
		return ! $this->isChildrenAllowed() || $this->isGuestsHiddenInSearch();
	}

	public function useBlockEditorForRoomTypes() {
		return (bool) get_option( 'mphb_use_block_editor_for_room_types', false );
	}

	public function useBlockEditorForServices() {
		return (bool) get_option( 'mphb_use_block_editor_for_services', false );
	}

	public function isPriceBreakdownUnfoldedByDefault() {
		return (bool) get_option( 'mphb_unfold_price_breakdown', false );
	}

	/**
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public function automaticallyCreateUser() {
		return (bool) get_option( 'mphb_automatically_create_user', false );
	}

	/**
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public function allowCustomersCreateAccount() {
		return (bool) get_option( 'mphb_allow_customers_create_account', false );
	}

	/**
	 *
	 * @since 4.2.0
	 *
	 * @return bool
	 */
	public function allowCustomersLogIn() {
		return (bool) get_option( 'mphb_allow_customers_log_in', false );
	}

	/**
	 * @return bool
	 */
	public function showExtensionLinks() {
		return apply_filters( 'mphb_show_extension_links', true );
	}

	public function exportBlockedAccommodations() {
		 return (bool) get_option( 'mphb_ical_export_blocks', false );
	}

	public function exportImportedBookings() {
		return ! (bool) get_option( 'mphb_ical_dont_export_imports', true );
	}

	/**
	 * @since 4.2.2
	 *
	 * @return boolean
	 */
	public function isMinimizedSyncLogs() {
		 return (bool) get_option( 'mphb_ical_minimize_logs', true );
	}

	/**
	 * @param null|int  $userId Optional. Current user by default.
	 * @param null|bool $setValue Optional. If set, will update the value.
	 * @return bool
	 */
	public function displayImportedBookings( $userId = null, $setValue = null ) {
		if ( is_null( $userId ) ) {
			$userId = get_current_user_id();
		}

		$displayImport = get_user_meta( $userId, 'mphb_display_imported_bookings', true );

		if ( is_null( $setValue ) ) {
			return (bool) $displayImport;
		} else {
			update_user_meta( $userId, 'mphb_display_imported_bookings', $setValue, $displayImport );
			return $setValue;
		}
	}

	/**
	 * @return string
	 *
	 * @since 3.6.1
	 */
	public function deleteSyncLogsOlderThan() {
		 return get_option( 'mphb_ical_auto_delete_period', 'quarter' );
	}

	/**
	 *
	 * @return bool
	 *
	 * @since 3.9.9
	 */
	public function isBookingRulesForAdminDisabled() {

		// TODO: refactore booking rules and check REQUEST DATA outside of core api (in ajax actions and pages)
		return get_option( 'mphb_do_not_apply_booking_rules_for_admin', false ) &&
			(
				( isset( $_REQUEST[ \MPHB\AjaxApi\AbstractAjaxApiAction::REQUEST_DATA_IS_ADMIN ] ) &&
					filter_var( $_REQUEST[ \MPHB\AjaxApi\AbstractAjaxApiAction::REQUEST_DATA_IS_ADMIN ], FILTER_VALIDATE_BOOLEAN )
				) ||
				( is_admin() &&
					isset( $_REQUEST['page'] ) &&
					in_array( $_REQUEST['page'], array( 'mphb_add_new_booking', 'mphb_edit_booking' ) )
				)
		 	);
	}

}
