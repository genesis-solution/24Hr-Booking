<?php

namespace MPHB\UsersAndRoles;

/**
 * @since 4.2.0
 */
class User {

	public function __construct() {

		add_action( 'profile_update', array( $this, 'updateCustomer' ), 10, 2 );
	}

	public function updateCustomer( $userId, $oldData ) {

		$newUserData = get_userdata( $userId );

		$customer = MPHB()->customers()->findBy( 'user_id', $userId );

		if ( empty( $customer ) || false === $newUserData ) {
			return;
		}

		$is_customer_changed = false;

		if ( isset( $newUserData->user_email ) ) {

			$customer->setEmail( $newUserData->user_email );
			$is_customer_changed = true;
		}

		if ( isset( $newUserData->first_name ) ) {

			$customer->setFirstName( $newUserData->first_name );
			$is_customer_changed = true;
		}

		if ( isset( $newUserData->last_name ) ) {

			$customer->setLastName( $newUserData->last_name );
			$is_customer_changed = true;
		}

		if ( $is_customer_changed ) {

			MPHB()->customers()->updateData( $customer );
		}
	}

	public static function create( $email, $username = '', $password = '', $args = array() ) {

		if ( empty( $email ) || ! is_email( $email ) ) {
			return new \WP_Error( 'invalid-email', __( 'Please provide a valid email address.', 'motopress-hotel-booking' ) );
		}

		if ( email_exists( $email ) ) {
			// Get existing customer by email
			$user = get_user_by( 'email', $email );
			return $user->ID;
		}

		if ( empty( $username ) ) {
			$username = self::createUsername( $email, $args );
			$username = sanitize_user( $username );
		}

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new \WP_Error( 'invalid-username', __( 'Please enter a valid account username.', 'motopress-hotel-booking' ) );
		}

		if ( username_exists( $username ) ) {
			return new \WP_Error( 'username-exists', __( 'An account is already registered with that username. Please choose another.', 'motopress-hotel-booking' ) );
		}

		if ( empty( $password ) ) {
			$password = wp_generate_password();
		}

		if ( empty( $password ) ) {
			return new \WP_Error( 'missing-password', __( 'Please enter an account password.', 'motopress-hotel-booking' ) );
		}

		$errors = new \WP_Error();

		do_action( 'mphb_register_user', $username, $email, $errors );

		$errors = apply_filters( 'mphb_registration_errors', $errors, $username, $email );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		global $wp_roles;

		$role = $wp_roles->is_role( Roles::CUSTOMER ) ? Roles::CUSTOMER : get_option( 'default_role' );

		$newCustomerData = apply_filters(
			'mphb_customer_data',
			array_merge(
				$args,
				array(
					'user_login' => $username,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => $role,
				)
			)
		);

		$customerId = wp_insert_user( $newCustomerData );

		if ( is_wp_error( $customerId ) ) {
			return $customerId;
		}

		do_action( 'mphb_created_user', $customerId, $newCustomerData );

		return $customerId;
	}

	public static function createUsername( $email, $args = array(), $suffix = '' ) {
		$usernameParts = array();

		if ( isset( $args['first_name'] ) ) {
			$usernameParts[] = sanitize_user( $args['first_name'], true );
		}

		if ( isset( $args['last_name'] ) ) {
			$usernameParts[] = sanitize_user( $args['last_name'], true );
		}

		$usernameParts = array_filter( $usernameParts );

		if ( empty( $usernameParts ) ) {
			$emailParts    = explode( '@', $email );
			$emailUsername = $emailParts[0];

			if ( in_array(
				$emailUsername,
				array(
					'sales',
					'hello',
					'mail',
					'contact',
					'info',
				),
				true
			) ) {
				$emailUsername = $emailParts[1];
			}

			$usernameParts[] = sanitize_user( $emailUsername, true );
		}

		$username = strtolower( implode( '.', $usernameParts ) );

		if ( $suffix ) {
			$username .= $suffix;
		}

		$illegalLogins = (array) apply_filters( 'illegal_user_logins', array() );

		if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegalLogins ), true ) ) {
			$newArgs = array();

			$newArgs['first_name'] = apply_filters(
				'mphb_generated_customer_username',
				'motopress_user_' . zeroise( wp_rand( 0, 9999 ), 4 ),
				$email,
				$args,
				$suffix
			);

			return self::createUsername( $email, $newArgs, $suffix );
		}

		if ( username_exists( $username ) ) {
			$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
			return self::createUsername( $email, $args, $suffix );
		}

		return apply_filters( 'mphb_new_customer_username', $username, $email, $args, $suffix );
	}

	public static function update( $data ) {
		$user = wp_update_user( $data );

		do_action( 'mphb_updated_user', $user, $data );

		return $user;
	}

	public static function delete( $userId, $reassign ) {
		$deleted = wp_delete_user( $userId, $reassign );

		do_action( 'mphb_deleted_user', $userId, $reassign, $deleted );

		return $deleted;
	}

	public static function findFreeAccounts() {
		global $wpdb;

		$users     = '`' . DB_NAME . '`.`' . $wpdb->users . '`';
		$customers = $wpdb->prefix . 'mphb_customers';

		$query = "SELECT
            $users.ID, $users.user_login, $users.user_email
            FROM
            $users
            LEFT JOIN
            $customers ON $customers.user_id = $users.ID
            WHERE
            $customers.user_id IS NULL";

		return $wpdb->get_results( $query );
	}

}
