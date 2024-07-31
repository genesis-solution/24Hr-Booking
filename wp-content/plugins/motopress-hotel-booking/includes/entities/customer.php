<?php

namespace MPHB\Entities;

class Customer {
	/**
	 *
	 * @var int
	 */
	private $customerId;

	/**
	 *
	 * @var string
	 */
	private $email;

	/**
	 *
	 * @var string
	 */
	private $firstName;

	/**
	 *
	 * @var string
	 */
	private $lastName;

	/**
	 *
	 * @var string
	 */
	private $phone;

	/**
	 *
	 * @var string
	 */
	private $country;

	/**
	 *
	 * @var string
	 */
	private $state;

	/**
	 *
	 * @var string
	 */
	private $city;

	/**
	 *
	 * @var string
	 */
	private $zip;

	/**
	 *
	 * @var string
	 */
	private $address1;

	/**
	 * @var array
	 * @since 3.7.2
	 */
	private $customFields;

	/**
	 * @param array  $atts
	 * @param string $atts['id'] - @since 4.2.0
	 * @param string $atts['email']
	 * @param string $atts['first_name']
	 * @param string $atts['last_name'] Optional.
	 * @param string $atts['country'] Optional.
	 * @param string $atts['state'] Optional.
	 * @param string $atts['city'] Optional.
	 * @param string $atts['zip'] Optional.
	 * @param string $atts['address1'] Optional.
	 * @param string $atts['phone']
	 */
	public function __construct( $atts = array() ) {
		$defaultAtts = array(
			'customer_id' => null,
			'email'       => '',
			'last_name'   => '',
			'first_name'  => '',
			'phone'       => '',
			'country'     => '',
			'state'       => '',
			'city'        => '',
			'zip'         => '',
			'address1'    => '',
		);

		$customAtts = array_diff_key( $atts, $defaultAtts );

		if ( isset( $customAtts['note'] ) ) {
			unset( $customAtts['note'] ); // We save the note in the booking
		}

		$atts = array_merge( $defaultAtts, $atts );

		// check is object because when customer did not created we stored WP_Error instead of id to the post_meta
		// TODO: remove return WP_Error instead of customer id at all from code or throw an Exception
		$this->customerId   = is_object( $atts['customer_id'] ) ? 0 : absint( $atts['customer_id'] );
		$this->email        = $atts['email'];
		$this->firstName    = $atts['first_name'];
		$this->lastName     = $atts['last_name'];
		$this->phone        = $atts['phone'];
		$this->country      = $atts['country'];
		$this->state        = $atts['state'];
		$this->city         = $atts['city'];
		$this->zip          = $atts['zip'];
		$this->address1     = $atts['address1'];
		$this->customFields = $customAtts;
	}

	/**
	 *
	 * @since 4.2.0
	 *
	 * @return int
	 */
	public function getCustomerId() {
		return $this->customerId;
	}

	/**
	 *
	 * @param int $id
	 *
	 * @since 4.2.0
	 */
	public function setCustomerId( $id ) {
		$this->customerId = (int) $id;
	}

	/**
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $email
	 *
	 * @since 3.7.2
	 */
	public function setEmail( $email ) {
		$this->email = $email;
	}

	/**
	 * @return bool
	 *
	 * @since 3.7.2
	 */
	public function hasEmail() {
		return ! empty( $this->email );
	}

	/**
	 * @return string
	 *
	 * @since 3.7.2
	 */
	public function getName() {
		return trim( $this->firstName . ' ' . $this->lastName );
	}

	/**
	 *
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 *
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 *
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 *
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 *
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 *
	 * @return string
	 */
	public function getZip() {
		return $this->zip;
	}

	/**
	 *
	 * @return string
	 */
	public function getAddress1() {
		return $this->address1;
	}

	/**
	 * @return array
	 *
	 * @since 3.7.2
	 */
	public function getCustomFields() {
		return $this->customFields;
	}

	/**
	 * @param string $fieldName
	 * @return mixed
	 *
	 * @since 3.7.2
	 */
	public function getCustomField( $fieldName ) {
		if ( isset( $this->customFields[ $fieldName ] ) ) {
			return $this->customFields[ $fieldName ];
		} else {
			return null;
		}
	}
}
