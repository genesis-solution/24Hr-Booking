<?php

namespace MPHB\UsersAndRoles;

/**
 * @since 4.2.0
 */
class Customers {

	/**
	 * @param string     $field
	 * @param int|string $param
	 * @param bool       $all
	 *
	 * @return int|\MPHB\UsersAndRoles\Customer
	 */
	public function findBy( $field, $param, $all = true ) {

		switch ( $field ) {
			case 'id':
				return self::findById( $param );
				break;
			case 'email':
				return self::findByEmail( $param, $all );
				break;
			case 'user_id':
				return self::findByUserId( $param, $all );
				break;
		}
	}

	/**
	 *
	 * @param string $email
	 * @param bool   $all
	 *
	 * @return int|\MPHB\UsersAndRoles\Customer
	 */
	public static function findByEmail( $email, $all = true ) {
		if ( empty( $email ) ) {
			return null;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';
		$users = '`' . DB_NAME . '`.`' . $wpdb->users . '`';

		if ( $all ) {
			$customer = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table as customers LEFT JOIN $users as users ON customers.user_id = users.ID WHERE customers.email=%s", $email ), ARRAY_A );

			if ( ! $customer ) {
				return null;
			}

			$customer = self::mapToCustomer( $customer );
		} else {
			$customer = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $table WHERE email=%s", $email ) );
		}

		return $customer;
	}

	/**
	 *
	 * @param int  $userId
	 * @param bool $all
	 *
	 * @return int|\MPHB\UsersAndRoles\Customer
	 */
	public static function findByUserId( $userId, $all = true ) {
		if ( empty( $userId ) ) {
			return null;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';
		$users = '`' . DB_NAME . '`.`' . $wpdb->users . '`';

		if ( $all ) {
			$customer = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table as customers LEFT JOIN $users as users ON customers.user_id = users.ID WHERE customers.user_id=%d", $userId ), ARRAY_A );

			if ( ! $customer ) {
				return null;
			}

			$customer = self::mapToCustomer( $customer );
		} else {
			$customer = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $table WHERE user_id=%d", $userId ) );
		}

		return $customer;
	}

	/**
	 *
	 * @param int $id
	 *
	 * @return null|\MPHB\UsersAndRoles\Customer
	 */
	public static function findById( $id ) {
		if ( empty( $id ) ) {
			return null;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';
		$users = '`' . DB_NAME . '`.`' . $wpdb->users . '`';

		$customer = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table as customers LEFT JOIN $users as users ON customers.user_id = users.ID WHERE customers.customer_id=%d", $id ), ARRAY_A );

		if ( ! $customer ) {
			return null;
		}

		$customer = self::mapToCustomer( $customer );

		return $customer;
	}

	/**
	 *
	 * @param int   $customerId
	 * @param array $atts
	 * @param bool  $all
	 *
	 * @return array
	 */
	public static function findBookings( $customerId, $atts = array(), $all = true ) {
		return MPHB()->getBookingRepository()->findAllByCustomer( $customerId, $atts, $all );
	}

	/**
	 *
	 * @param \MPHB\Entities\Customer $customer
	 *
	 * @return \MPHB\UsersAndRoles\Customer
	 */
	public function convertFromEntity( $customer ) {
		$customerAtts['email']      = $customer->getEmail();
		$customerAtts['first_name'] = $customer->getFirstName();
		$customerAtts['last_name']  = $customer->getLastName();
		$customerAtts['phone']      = $customer->getPhone();
		$customerAtts['country']    = $customer->getCountry();
		$customerAtts['state']      = $customer->getState();
		$customerAtts['city']       = $customer->getCity();
		$customerAtts['zip']        = $customer->getZip();
		$customerAtts['address1']   = $customer->getAddress1();

		return new Customer( $customerAtts );
	}

	/**
	 *
	 * @param array $customerAtts
	 *
	 * @return \MPHB\UsersAndRoles\Customer
	 */
	public function customer( $customerAtts ) {
		return self::mapToCustomer( $customerAtts );
	}

	public static function mapToCustomer( $customer ) {

		$customerAtts['id']            = isset( $customer['customer_id'] ) && ! empty( $customer['customer_id'] ) ? (int) $customer['customer_id'] : null;
		$customerAtts['user_id']       = isset( $customer['user_id'] ) && ! empty( $customer['user_id'] ) ? (int) $customer['user_id'] : null;
		$customerAtts['username']      = isset( $customer['user_login'] ) ? esc_attr( $customer['user_login'] ) : '';
		$customerAtts['email']         = isset( $customer['email'] ) ? $customer['email'] : '';
		$customerAtts['first_name']    = isset( $customer['first_name'] ) ? $customer['first_name'] : '';
		$customerAtts['last_name']     = isset( $customer['last_name'] ) ? $customer['last_name'] : '';
		$customerAtts['phone']         = isset( $customer['phone'] ) ? $customer['phone'] : '';
		$customerAtts['country']       = isset( $customer['country'] ) ? $customer['country'] : '';
		$customerAtts['state']         = isset( $customer['state'] ) ? $customer['state'] : '';
		$customerAtts['city']          = isset( $customer['city'] ) ? $customer['city'] : '';
		$customerAtts['zip']           = isset( $customer['zip'] ) ? $customer['zip'] : '';
		$customerAtts['address1']      = isset( $customer['address1'] ) ? $customer['address1'] : '';
		$customerAtts['date_created']  = isset( $customer['date_registered'] ) ? $customer['date_registered'] : \MPHB\Utils\DateUtils::formatDateTimeDB( new \DateTime() );
		$customerAtts['date_modified'] = isset( $customer['last_active'] ) ? $customer['last_active'] : $customerAtts['date_created'];
		$customerAtts['bookings']      = isset( $customer['bookings'] ) ? (int) $customer['bookings'] : '';

		return new Customer( $customerAtts );
	}

	public static function countCustomers( $atts = array() ) {
		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';

		$sql = "SELECT COUNT(*) FROM $table";

		return $wpdb->get_var( $sql );
	}

	public static function findCustomers( $atts = array() ) {
		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';

		$order = isset( $atts['order'] ) ? $atts['order'] : 'DESC';

		$orderby = '';

		if ( isset( $atts['orderby'] ) ) {
			if ( $atts['orderby'] == 'full_name' ) {
				$orderby = $wpdb->prepare( 'ORDER BY last_name %1s, first_name %2s', $order, $order );
			} else {
				$orderby = $wpdb->prepare( 'ORDER BY %1s %1s', $atts['orderby'], $order );
			}
		}

		$paged = isset( $atts['paged'] ) ? (int) $atts['paged'] : 1;

		$per_page = isset( $atts['per_page'] ) ? (int) $atts['per_page'] : 99999;

		$offset = ( $paged - 1 ) * $per_page;

		$limit = $wpdb->prepare( 'LIMIT %d,%d', $offset, $per_page );

		$sql = "SELECT * FROM $table $orderby $limit";

		$result = $wpdb->get_results( $sql, ARRAY_A );

		if ( null == $result ) {
			return array();
		}

		foreach ( $result as $customer ) {
			$customers[] = self::mapToCustomer( $customer );
		}

		return $customers;
	}

	/**
	 *
	 * @param int $customerId
	 */
	public function updateBookings( $customerId ) {
		return self::updateBookingsByCustomerId( $customerId );
	}

	public static function updateBookingsByCustomerId( $customerId ) {
		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';
		$posts = $wpdb->posts;
		$meta  = $wpdb->postmeta;

		return $wpdb->query(
			$wpdb->prepare(
				"UPDATE $table as t SET bookings = (SELECT COUNT(*) FROM $posts as p INNER JOIN $meta as m ON p.ID = m.post_id WHERE p.post_type = 'mphb_booking' AND p.post_status NOT IN ('trash') AND m.meta_key = 'mphb_customer_id' AND m.meta_value = %d) WHERE t.customer_id = %d",
				$customerId,
				$customerId
			)
		);
	}

	/**
	 *
	 * @param string $customerEmail
	 */
	public static function updateBookingsByCustomer( $customerEmail ) {
		if ( empty( $customerEmail ) ) {
			return;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';
		$posts = $wpdb->posts;
		$meta  = $wpdb->postmeta;

		$q = $wpdb->prepare( "UPDATE $table as t SET bookings = (SELECT COUNT(*) FROM $posts as p INNER JOIN $meta as m ON p.ID = m.post_id WHERE p.post_type = 'mphb_booking' AND p.post_status NOT IN ('trash') AND m.meta_key = 'mphb_email' AND m.meta_value = %s) WHERE t.email = %s", $customerEmail, $customerEmail );

		return $wpdb->query( $q );
	}

	/**
	 *
	 * @param \MPHB\UsersAndRoles\Customer
	 *
	 * @return \WP_Error|true
	 */
	public static function validateCustomer( $customer ) {
		global $wpdb;

		if ( ! $customer->getEmail() ) {
			return new \WP_Error( 'empty_email', __( 'Please, provide a valid email.', 'motopress-hotel-booking' ) );
		}

		return true;
	}

	/**
	 *
	 * @param \MPHB\UsersAndRoles\Customer
	 *
	 * @return int|null|\WP_Error
	 */
	public function create( $customer ) {
		$validateCustomer = self::validateCustomer( $customer );

		if ( is_wp_error( $validateCustomer ) ) {
			return $validateCustomer;
		}

		if ( ! $customer->getDateCreated() ) {
			$customer->setDateCreated( \MPHB\Utils\DateUtils::formatDateTimeDB( new \DateTime() ) );
		}

		$customer->setDateModified( $customer->getDateCreated() );

		$customerId = self::insert( $customer );

		if ( null == $customerId ) {
			return new \WP_Error( 'customer_not_created', __( 'Could not create a customer.', 'motopress-hotel-booking' ) );
		}

		return $customerId;
	}

	/**
	 *
	 * @param int                          $userId
	 * @param \MPHB\UsersAndRoles\Customer $customer
	 *
	 * @return int|\WP_Error
	 */
	public function updateLinkedUser( $userId, $customer ) {
		$userAtts = array(
			'ID'         => $userId,
			'user_email' => $customer->getEmail(),
			'first_name' => $customer->getFirstName(),
			'last_name'  => $customer->getLastName(),
		);

		return User::update( $userAtts );
	}

	/**
	 *
	 * @param int                          $customerId
	 * @param \MPHB\UsersAndRoles\Customer
	 *
	 * @return int|false|\WP_Error
	 */
	public function updateData( $customer ) {
		$validateCustomer = self::validateCustomer( $customer );

		if ( is_wp_error( $validateCustomer ) ) {
			return $validateCustomer;
		}

		$customerId = $customer->getId();

		$updated = self::update( $customerId, $customer );

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return $customerId;
	}

	/**
	 *
	 * @deprecated 4.2.5 \MPHB\Entities\Customer $bookingCustomer
	 * @param \MPHB\Entities\Booking $booking
	 * @param bool                   $isAdmin Is admin booking or not
	 *
	 * @return int|\WP_Error
	 */
	public function createCustomerOnBooking( $booking, $isAdmin = false ) {
		$bookingCustomer = $booking->getCustomer();

		if ( ! $bookingCustomer ) {
			return new \WP_Error( 'invalid_customer_object', __( 'Could not retrieve a customer.', 'motopress-hotel-booking' ) );
		}

		if ( empty( $bookingCustomer->getEmail() ) ) {
			return new \WP_Error( 'invalid_email', __( 'Please, provide a valid email.', 'motopress-hotel-booking' ) );
		}

		if ( ! $isAdmin ) {
			return $this->createCustomerOnFrontend( $bookingCustomer, $booking );
		}

		return $this->createdCustomerFromAdmin( $bookingCustomer, $booking );
	}

	/**
	 *
	 * @param \MPHB\Entities\Customer $bookingCustomer
	 *
	 * @return int|\WP_Error
	 */
	protected function createdCustomerFromAdmin( $bookingCustomer, $booking = null ) {
		$email = $bookingCustomer->getEmail();

		$customer = MPHB()->customers()->findBy( 'email', $email );

		if ( $customer ) {
			return $customer->getId();
		}

		$customer   = MPHB()->customers()->convertFromEntity( $bookingCustomer );
		$customerId = MPHB()->customers()->create( $customer );

		return $customerId;
	}

	/**
	 *
	 * @param \MPHB\Entities\Customer $bookingCustomer
	 *
	 * @return int|\WP_Error
	 */
	protected function createCustomerOnFrontend( $bookingCustomer, $booking = null ) {
		$email = $bookingCustomer->getEmail();

		$user   = wp_get_current_user();
		$userId = $user->ID ? $user->ID : null;

		if ( $userId ) {
			$customer = MPHB()->customers()->findBy( 'user_id', $userId ); // Check if there is a customer

			if ( $customer ) {
				$customer->setDateModified( \MPHB\Utils\DateUtils::formatDateTimeDB( new \DateTime() ) );

				MPHB()->customers()->updateData( $customer );

				return $customer->getId();
			}
		}

		$customerUserId       = null;
		$autoCreateNewAccount = MPHB()->settings()->main()->automaticallyCreateUser(); // Create new \WP_User
		$allowToCreateAccount = ! $autoCreateNewAccount && MPHB()->settings()->main()->allowCustomersCreateAccount();
		$createAccount        = $allowToCreateAccount && isset( $_POST['mphb_create_new_account'] ) && $_POST['mphb_create_new_account'] == 1;

		$customer = MPHB()->customers()->findBy( 'email', $email );

		if ( $customer ) {

			if ( ! $customer->getUserId() && ( $autoCreateNewAccount || $createAccount ) ) {
				$customerUserId = $this->assignAccountOnBooking( $user, $email, $customer, $booking );

				if ( $customerUserId ) {
					$customer->setUserId( $customerUserId );
				}
			}

			$customer->setDateModified( \MPHB\Utils\DateUtils::formatDateTimeDB( new \DateTime() ) );

			MPHB()->customers()->updateData( $customer );

			return $customer->getId();
		}

		$customer = MPHB()->customers()->convertFromEntity( $bookingCustomer );

		if ( $autoCreateNewAccount || $createAccount ) {
			$customerUserId = $this->assignAccountOnBooking( $user, $email, $customer, $booking );

			if ( $customerUserId ) {
				$customer->setUserId( $customerUserId );
			}
		}

		$customerId = MPHB()->customers()->create( $customer );

		return $customerId;
	}

	/**
	 *
	 * @param \MPHB\UsersAndRoles\Customer $customer
	 *
	 * @return int|null
	 */
	public static function insert( $customer ) {
		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';

		if ( $customer->getUserId() ) {
			$data['user_id'] = $customer->getUserId();
			$formats[]       = '%d';
		}

		$data['email']           = $customer->getEmail();
		$data['first_name']      = $customer->getFirstName();
		$data['last_name']       = $customer->getLastName();
		$data['phone']           = $customer->getPhone();
		$data['country']         = $customer->getCountry();
		$data['state']           = $customer->getState();
		$data['city']            = $customer->getCity();
		$data['address1']        = $customer->getAddress1();
		$data['zip']             = $customer->getZip();
		$data['date_registered'] = $customer->getDateCreated();
		$data['last_active']     = $customer->getDateModified();

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		$wpdb->insert(
			$table,
			$data,
			$formats
		);

		$customerId = $wpdb->insert_id;

		if ( ! $customerId ) {
			return null;
		}

		return $customerId;
	}

	/**
	 *
	 * @param int                          $customerId
	 * @param \MPHB\UsersAndRoles\Customer $customer
	 *
	 * @return int|null
	 */
	public static function update( $customerId, $customer ) {
		if ( empty( $customerId ) ) {
			return new \WP_Error( 'empty_customer_id', __( 'Please, provide a valid Customer ID.', 'motopress-hotel-booking' ) );
		}

		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';

		$data['user_id']     = $customer->getUserId();
		$data['first_name']  = $customer->getFirstName();
		$data['last_name']   = $customer->getLastName();
		$data['email']       = $customer->getEmail();
		$data['phone']       = $customer->getPhone();
		$data['country']     = $customer->getCountry();
		$data['state']       = $customer->getState();
		$data['city']        = $customer->getCity();
		$data['address1']    = $customer->getAddress1();
		$data['zip']         = $customer->getZip();
		$data['last_active'] = $customer->getDateModified();

		$formats = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		$result = $wpdb->update(
			$table,
			$data,
			array(
				'customer_id' => $customerId,
			),
			$formats,
			array( '%d' )
		);

		if ( $result === false ) {
			return new \WP_Error( 'db_error', __( 'A database error.', 'motopress-hotel-booking' ) );
		} else {
			return $customerId;
		}
	}

	/**
	 *
	 * @param int $customerId
	 *
	 * @return int|false|\WP_Error
	 */
	public static function delete( $customerId ) {
		if ( ! $customerId ) {
			return new \WP_Error( 'empty_customer_id', __( 'Please, provide a valid Customer ID.', 'motopress-hotel-booking' ) );
		}

		$deleted = self::deleteFromDb( $customerId );

		if ( ! $deleted ) {
			return new \WP_Error( 'not_deleted_customer', __( 'No customer was deleted.', 'motopress-hotel-booking' ) );
		}

		$errors = new \WP_Error();

		do_action( 'mphb_deleted_customer', $customerId, $errors );

		$errors = apply_filters( 'mphb_delete_customer_errors', $errors, $customerId );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		return $deleted;
	}

	/**
	 *
	 * @param int $customerId
	 *
	 * @return int|false
	 */
	public static function deleteFromDb( $customerId ) {
		global $wpdb;

		$table = $wpdb->prefix . 'mphb_customers';

		return $wpdb->delete(
			$table,
			array(
				'customer_id' => $customerId,
			),
			array( '%d' )
		);
	}

	/**
	 *
	 * @param int|\WP_User                 $user
	 * @param string                       $email
	 * @param \MPHB\UsersAndRoles\Customer $customer
	 *
	 * @return int|null
	 */
	public function assignAccountOnBooking( $user, $email, $customer, $booking = null ) {
		if ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		}

		if ( $user->ID && $user->data->user_email == $email ) {
			return $user->ID;
		} elseif ( ! $user->ID ) {
			$userAtts = MPHB()->customers()->createAccount( $email, $customer );

			if ( ! is_wp_error( $userAtts ) ) {
				/**
				 *
				 * @param \MPHB\UsersAndRoles\Customer $customer
				 * @param array $userAtts
				 *
				 * @since 4.2.0
				 */
				do_action( 'mphb_send_customer_registration_email', $customer, $userAtts, array(), $booking );

				return (int) $userAtts['user_id'];
			}
		}

		return null;
	}

	/**
	 *
	 * @param string                       $email
	 * @param \MPHB\UsersAndRoles\Customer
	 */
	public function createAccount( $email, $customer ) {
		if ( ! email_exists( $email ) ) { // Check if a \WP_User exists
			$username = User::createUsername(
				$email,
				array(
					'first_name' => $customer->getFirstName(),
					'last_name'  => $customer->getLastName(),
				)
			);
			$username = sanitize_user( $username );

			$password = wp_generate_password();

			$userId = User::create(
				$email,
				$username,
				$password,
				array(
					'first_name' => $customer->getFirstName(),
					'last_name'  => $customer->getLastName(),
				)
			);

			if ( is_wp_error( $userId ) ) {
				return $userId;
			}

			$userAtts = array(
				'user_id'    => $userId,
				'user_pass'  => $password,
				'user_login' => $username,
			);

			return $userAtts;
		} else {
			return new \WP_Error( 'wp_user_exists', __( 'An account with this email already exists. Please, log in.', 'motopress-hotel-booking' ) );
		}
	}
}
