<?php

namespace MPHB\UsersAndRoles;

/**
 * @since 4.2.0
 */
class Customer {

	private $id;
	private $userId;
	private $dateCreated;
	private $dateModified;
	private $email;
	private $firstName;
	private $lastName;
	private $fullName;
	private $role;
	private $userName;
	private $phone;
	private $country;
	private $state;
	private $city;
	private $zip;
	private $address1;
	private $bookings;


	/**
	 * @var array
	 */
	protected $default = array(
		'id'            => null,
		'user_id'       => null,
		'date_created'  => null,
		'date_modified' => null,
		'email'         => '',
		'first_name'    => '',
		'last_name'     => '',
		'full_name'     => '',
		'role'          => 'mphb_customer',
		'username'      => '',
		'phone'         => '',
		'country'       => '',
		'state'         => '',
		'city'          => '',
		'zip'           => '',
		'address1'      => '',
		'bookings'      => '',
	);

	/**
	 * @var string
	 */
	protected $password = '';

	public function __construct( $atts = array() ) {

		$atts = array_merge( $this->default, $atts );

		if ( null != $atts['id'] ) {
			$this->id = (int) $atts['id'];
		}

		$this->userId = $atts['user_id'];

		$this->dateCreated = sanitize_text_field( $atts['date_created'] );

		$this->dateModified = sanitize_text_field( $atts['date_modified'] );

		$this->email = sanitize_email( $atts['email'] );

		$this->firstName = sanitize_text_field( $atts['first_name'] );

		$this->lastName = sanitize_text_field( $atts['last_name'] );

		$this->fullName = sprintf( '%s %s', $this->firstName, $this->lastName );

		$this->role = sanitize_text_field( $atts['role'] );

		$this->userName = sanitize_user( $atts['username'] );

		$this->phone = sanitize_text_field( $atts['phone'] );

		$this->country = sanitize_text_field( $atts['country'] );

		$this->state = sanitize_text_field( $atts['state'] );

		$this->city = sanitize_text_field( $atts['city'] );

		$this->zip = sanitize_text_field( $atts['zip'] );

		$this->address1 = sanitize_text_field( $atts['address1'] );

		$this->bookings = (int) $atts['bookings'];
	}

	public function getPassword() {
		return $this->password;
	}

	public function getId() {
		return isset( $this->id ) ? (int) $this->id : null;
	}

	public function setId( $id ) {
		$this->id = (int) $id;
	}

	public function getUserId() {
		return isset( $this->userId ) ? (int) $this->userId : null;
	}

	public function setUserId( $userId ) {
		$this->userId = (int) $userId;
	}

	public function getDateCreated() {
		return $this->dateCreated;
	}

	public function setDateCreated( $date ) {
		$this->dateCreated = sanitize_text_field( $date );
	}

	public function getDateModified() {
		return $this->dateModified;
	}

	public function setDateModified( $date ) {
		$this->dateModified = sanitize_text_field( $date );
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail( $email ) {
		$this->email = sanitize_email( $email );
	}

	public function getFirstName() {
		return $this->firstName;
	}

	public function setFirstName( $firstName ) {
		$this->firstName = sanitize_text_field( $firstName );
	}

	public function getLastName() {
		return $this->lastName;
	}

	public function setLastName( $lastName ) {
		$this->lastName = sanitize_text_field( $lastName );
	}

	public function getFullName() {
		return $this->fullName;
	}

	public function setFullName( $fullName ) {
		$this->fullName = sanitize_text_field( $fullName );
	}

	public function getRole() {
		return $this->role;
	}

	public function setRole( $role ) {
		$this->role = sanitize_text_field( $role );
	}

	public function getUserName() {
		return $this->userName;
	}

	public function setUserName( $username ) {
		$this->userName = sanitize_user( $username );
	}

	public function getPhone() {
		return $this->phone;
	}

	public function setPhone( $phone ) {
		$this->phone = sanitize_text_field( $phone );
	}

	public function getCountry() {
		return $this->country;
	}

	public function setCountry( $country ) {
		$this->country = sanitize_text_field( $country );
	}

	public function getState() {
		return $this->state;
	}

	public function setState( $state ) {
		$this->state = esc_attr( $state );
	}

	public function getCity() {
		return $this->city;
	}

	public function setCity( $city ) {
		$this->city = sanitize_text_field( $city );
	}

	public function getZip() {
		return $this->zip;
	}

	public function setZip( $zip ) {
		$this->zip = sanitize_text_field( $zip );
	}

	public function getAddress1() {
		return $this->address1;
	}

	public function setAddress1( $address ) {
		$this->address1 = sanitize_text_field( $address );
	}

	public function getBookings() {
		return $this->bookings;
	}

	public function setBookings( $bookings ) {
		$this->bookings = (int) $bookings;
	}

}
