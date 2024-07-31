<?php

namespace MPHB\Bundles;

/**
 * @since 3.7.2
 */
class CustomerBundle {

	const CUSTOMER_FIELD_NAME_FIRST_NAME = 'first_name';
	const CUSTOMER_FIELD_NAME_LAST_NAME  = 'last_name';
	const CUSTOMER_FIELD_NAME_EMAIL      = 'email';
	const CUSTOMER_FIELD_NAME_PHONE      = 'phone';
	const CUSTOMER_FIELD_NAME_COUNTRY    = 'country';
	const CUSTOMER_FIELD_NAME_ADDRESS_1  = 'address1';
	const CUSTOMER_FIELD_NAME_CITY       = 'city';
	const CUSTOMER_FIELD_NAME_STATE      = 'state';
	const CUSTOMER_FIELD_NAME_ZIP        = 'zip';
	const CUSTOMER_FIELD_NAME_NOTE       = 'note';

	protected $allFields     = null;
	protected $defaultFields = array();

	public function __construct() {
		$this->initDefaults();
	}

	/**
	 * @param string $fieldName
	 * @return bool
	 */
	public function isDefaultField( $fieldName ) {
		return array_key_exists( $fieldName, $this->defaultFields );
	}

	/**
	 * @return array
	 */
	public function getDefaultFields() {
		return $this->defaultFields;
	}

	/**
	 * @return array
	 */
	public function getCustomerFields() {
		if ( is_null( $this->allFields ) ) {
			$this->allFields = apply_filters( 'mphb_customer_fields', $this->defaultFields );
		}

		return $this->allFields;
	}

	/**
	 * @return array
	 */
	public function getCustomFields() {
		$customerFields = $this->getCustomerFields();
		$customFields   = array_diff_key( $customerFields, $this->defaultFields );

		return $customFields;
	}

	/**
	 * @return array
	 */
	public function getAdminCheckoutFields() {

		$customerFields = $this->getCustomerFields();

		if ( ! MPHB()->settings()->main()->isCustomerRequiredOnAdmin() ) {
			// Make all customer fields optional
			array_walk(
				$customerFields,
				function ( &$field ) {
					$field['required'] = false;
				}
			);
		}

		return apply_filters( 'mphb_admin_checkout_customer_fields', $customerFields );
	}

	protected function initDefaults() {

		$primaryFieldsRequired = true;
		$addressFieldsRequired = MPHB()->settings()->main()->isRequireFullAddress();
		$countryFieldRequired  = $addressFieldsRequired || MPHB()->settings()->main()->isRequireCountry();

		$this->defaultFields = array(
			static::CUSTOMER_FIELD_NAME_FIRST_NAME => array(
				'label'    => __( 'First Name', 'motopress-hotel-booking' ),
				'type'     => 'text',
				'enabled'  => $primaryFieldsRequired,
				'required' => $primaryFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'First name is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_LAST_NAME  => array(
				'label'    => __( 'Last Name', 'motopress-hotel-booking' ),
				'type'     => 'text',
				'enabled'  => $primaryFieldsRequired,
				'required' => $primaryFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'Last name is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_EMAIL      => array(
				'label'    => __( 'Email', 'motopress-hotel-booking' ),
				'type'     => 'email',
				'enabled'  => $primaryFieldsRequired,
				'required' => $primaryFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'Email is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_PHONE      => array(
				'label'    => __( 'Phone', 'motopress-hotel-booking' ),
				'type'     => 'phone',
				'enabled'  => $primaryFieldsRequired,
				'required' => $primaryFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'Phone is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_COUNTRY    => array(
				'label'    => __( 'Country of residence', 'motopress-hotel-booking' ),
				'type'     => 'country',
				'enabled'  => $countryFieldRequired,
				'required' => $countryFieldRequired,
				'labels'   => array(
					'required_error' => __( 'Country is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_ADDRESS_1  => array(
				'label'    => __( 'Address', 'motopress-hotel-booking' ),
				'type'     => 'text',
				'enabled'  => $addressFieldsRequired,
				'required' => $addressFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'Address is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_CITY       => array(
				'label'    => __( 'City', 'motopress-hotel-booking' ),
				'type'     => 'text',
				'enabled'  => $addressFieldsRequired,
				'required' => $addressFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'City is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_STATE      => array(
				'label'    => __( 'State / County', 'motopress-hotel-booking' ),
				'type'     => 'text',
				'enabled'  => $addressFieldsRequired,
				'required' => $addressFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'State is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_ZIP        => array(
				'label'    => __( 'Postcode', 'motopress-hotel-booking' ),
				'type'     => 'text',
				'enabled'  => $addressFieldsRequired,
				'required' => $addressFieldsRequired,
				'labels'   => array(
					'required_error' => __( 'Postcode is required.', 'motopress-hotel-booking' ),
				),
			),
			static::CUSTOMER_FIELD_NAME_NOTE       => array(
				'label'    => __( 'Notes', 'motopress-hotel-booking' ),
				'type'     => 'textarea',
				'enabled'  => true,
				'required' => false,
				'labels'   => array(
					'required_error' => __( 'Note is required.', 'motopress-hotel-booking' ),
				),
			),
		);
	}
}
