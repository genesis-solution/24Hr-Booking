<?php

/**
 * @since 4.0.0
 */

namespace MPHB\UsersAndRoles;

class CapabilitiesAndRoles {

	const MANAGE_SETTINGS       = 'mphb_manage_settings';
	const MANAGE_RULES          = 'mphb_manage_booking_rules';
	const MANAGE_TAXES_AND_FEES = 'mphb_manage_taxes_and_fees';
	const VIEW_CALENDAR         = 'mphb_view_calendar';
	const VIEW_REPORTS          = 'mphb_view_reports';
	const EXPORT_REPORTS        = 'mphb_export_reports';
	const SYNC_ICAL             = 'mphb_sync_ical';
	const IMPORT_ICAL           = 'mphb_import_ical';
	const VIEW_CUSTOMERS        = 'mphb_view_customers';
	const EDIT_CUSTOMER         = 'mphb_edit_customer';
	const DELETE_CUSTOMER       = 'mphb_delete_customer';

	/**
	 * @var array
	 */
	public $capabilities;

	/**
	 * @var array
	 */
	public $roles;

	public function __construct() {
		$this->mapCapabilitiesToRoles();
		$this->mapRolesToCapabilities();
	}

	public static function setup() {
		$wpRoles = wp_roles();

		// create empty roles if they do not exist
		$customRoles = MPHB()->roles()->getRoles();

		foreach ( $customRoles as $role ) {

			if ( ! $wpRoles->is_role( $role->name ) ) {
				$role->add();
			}
		}

		// update role capabilities if current version of the roles code > roles version in DB
		// we just add our caps and do not erase already set caps by admin
		if ( Roles::getCurrentVersion() > \HotelBookingPlugin::getCustomRolesVersion() ) {

			$capabilitiesToRoles = MPHB()->capabilitiesAndRoles()->getRoles();

			if ( ! empty( $capabilitiesToRoles ) ) {

				foreach ( $capabilitiesToRoles as $role => $capabilities ) {

					if ( ! empty( $capabilities ) ) {

						foreach ( $capabilities as $cap ) {

							$wpRoles->add_cap( $role, $cap, true );
						}
					}
				}
			}

			\HotelBookingPlugin::setCustomRolesVersion( Roles::getCurrentVersion() );
		}
	}

	/**
	 * Maps custom capabilities to WP Roles.
	 */
	public function mapCapabilitiesToRoles() {
		$this->capabilities[ self::MANAGE_SETTINGS ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::MANAGE_RULES ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::VIEW_CALENDAR ] = array(
			'administrator',
			Roles::MANAGER,
			Roles::WORKER,
		);

		$this->capabilities[ self::MANAGE_TAXES_AND_FEES ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::VIEW_REPORTS ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::EXPORT_REPORTS ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::SYNC_ICAL ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::IMPORT_ICAL ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::VIEW_CUSTOMERS ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::EDIT_CUSTOMER ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities[ self::DELETE_CUSTOMER ] = array(
			'administrator',
			Roles::MANAGER,
		);

		$editor       = get_role( 'editor' ); // Get capabilities from Editor's \WP_Role
		$capabilities = null != $editor ? array_keys( $editor->capabilities ) : array();

		if ( ! empty( $capabilities ) ) {
			foreach ( $capabilities as $cap ) {
				if ( ! isset( $this->capabilities[ $cap ] ) ) {
					$this->capabilities[ $cap ] = array();
				}
				array_push( $this->capabilities[ $cap ], Roles::MANAGER );
			}
		}

		$contributor  = get_role( 'subscriber' ); // Get capabilities for Contributor's \WP_Role
		$capabilities = null != $contributor ? array_keys( $contributor->capabilities ) : array();

		if ( ! empty( $capabilities ) ) {
			foreach ( $capabilities as $cap ) {
				if ( ! isset( $this->capabilities[ $cap ] ) ) {
					$this->capabilities[ $cap ] = array();
				}
				array_push( $this->capabilities[ $cap ], Roles::WORKER );
			}
		}

		$cpts = MPHB()->postTypes()->getPostTypes();

		if ( ! empty( $cpts ) ) {
			foreach ( $cpts as $cpt ) {
				list($singular, $plural) = $cpt->getCapabilityType();

				$caps = array(
					"edit_{$plural}",
					"edit_private_{$plural}",
					"edit_others_{$plural}",
					"edit_published_{$plural}",
					"delete_{$plural}",
					"delete_private_{$plural}",
					"delete_others_{$plural}",
					"delete_published_{$plural}",
					"read_{$plural}",
					"read_private_{$plural}",
					"publish_{$plural}",
				);

				foreach ( $caps as $cap ) {
					if ( ! isset( $this->capabilities[ $cap ] ) ) {
						$this->capabilities[ $cap ] = array();
					}
					array_push( $this->capabilities[ $cap ], 'administrator' );
					array_push( $this->capabilities[ $cap ], Roles::MANAGER );
				}
			}
		}

		$this->capabilities['manage_mphb_room_type_categories'] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities['manage_mphb_room_type_tags'] = array(
			'administrator',
			Roles::MANAGER,
		);

		$this->capabilities['manage_mphb_room_type_facilities'] = array(
			'administrator',
			Roles::MANAGER,
		);
	}

	/**
	 * Maps Wp Roles to capabilities.
	 */
	public function mapRolesToCapabilities() {
		if ( ! empty( $this->capabilities ) ) {
			foreach ( $this->capabilities as $capability => $roles ) {
				array_map(
					function ( $role ) use ( $capability ) {
						if ( ! isset( $this->roles[ $role ] ) ) {
							$this->roles[ $role ] = array();
						}
						if ( ! in_array( $capability, $this->roles[ $role ] ) ) {
							array_push( $this->roles[ $role ], $capability );
						}
					},
					$roles
				);
			}
		}
	}

	/**
	 * @param string $role Wp Role name.
	 *
	 * @return bool
	 */
	public function getCapabilitiesByRole( $role ) {
		return array_filter(
			$this->capabilities,
			function ( $roles ) use ( $role ) {
				return in_array( $role, $roles );
			}
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getCapabilities() {
		return $this->capabilities;
	}

	/**
	 *
	 * @return array
	 */
	public function getRoles() {
		return $this->roles;
	}
}
