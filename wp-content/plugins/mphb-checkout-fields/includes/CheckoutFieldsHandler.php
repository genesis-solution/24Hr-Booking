<?php

namespace MPHB\CheckoutFields;

use MPHB\CheckoutFields\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds actions and filters hooks of the plugin.
 */
class CheckoutFieldsHandler {

	public function __construct() {

		add_filter(
			'mphb_customer_fields',
			function( array $customerFields ): array {
				return $this->filterCustomerFields( $customerFields );
			},
			10,
			3
		);

		add_filter(
			'mphb_checkout_form_enctype_data',
			function( string $enctypeValue ) {
				return $this->filterCheckoutFormEnctypeAttribute( $enctypeValue );
			},
			10
		);

		add_filter(
			'mphb_parse_customer_data',
			function( array $customerData, array $rawData, array $customerFields ) {
				return $this->parseCustomerDataForCustomFields( $customerData, $rawData, $customerFields );
			},
			10,
			3
		);

		// Checkout Fields List Page
		add_filter(
			'edit_mphb_checkout_field_per_page',
			function( $checkoutFieldsPerPage ) {
				return 999; // show all fields on a single page
			},
			10,
			1
		);

		// Edit actions of Checkout Fields List row
		add_filter(
			'post_row_actions',
			function( array $actions, \WP_Post $wpPost ): array {
				return $this->filterCheckoutFieldsListRowActions( $actions, $wpPost );
			},
			10,
			2
		);

		add_filter(
			'pre_trash_post',
			function( $check, \WP_Post $wpPost ) {
				return $this->preventRemovalOfDefaultCheckoutFields( $check, $wpPost );
			},
			10,
			2
		);

		add_filter(
			'pre_delete_post',
			function( $check, \WP_Post $wpPost ) {
				return $this->preventRemovalOfDefaultCheckoutFields( $check, $wpPost );
			},
			10, // if you change this priority then make sure you do not forbid uninstall fields deletion in PluginLifecycleHandler
			2
		);

		add_action(
			'before_delete_post',
			function( int $wpPostId ) {
				Fields\FileUploadField::deleteUploadedCheckoutFieldsFilesOfBooking( $wpPostId );
			},
			10,
			1
		);

		// Edit Booking page
		add_filter(
			'mphb_edit_page_field_groups',
			function( array $groups, string $postType ): array {
				return $this->addCustomCheckoutFieldsToCustomerOnBookingEditPage( $groups, $postType );
			},
			10,
			2
		);

		// Date of Birth field
		add_filter(
			'mphb_create_date_of_birth_field',
			/**
			 * @param \MPHB\Admin\Fields\InputField|null $instance
			 * @param mixed $value
			 */
			function( $instance, string $name, array $args, $value ): \MPHB\Admin\Fields\InputField {
				return is_null( $instance ) ? new \MPHB\CheckoutFields\Fields\DateOfBirthField( $name, $args, $value ) : $instance;
			},
			10,
			4
		);

		add_filter(
			'mphb_sanitize_customer_field',
			/**
			* @param string|null $result
			* @param mixed $value
			* @return string|null
			*/
			function( $result, $value, string $type ) {
				if ( $type == 'date_of_birth' ) {
					$result = ( new \MPHB\CheckoutFields\Fields\DateOfBirthField( '', array() ) )->sanitize( $value );
				}
				return $result;
			},
			10,
			3
		);

		// Enqueue script on Edit Booking page
		add_action(
			'admin_enqueue_scripts',
			function() {
				if ( MPHB()->postTypes()->booking()->getEditPage()->isCurrentPage() ) {
					wp_enqueue_script(
						'mphb-cf-date-of-birth',
						CheckoutFieldsHelper::getUrlToFile( 'assets/js/date-of-birth-control.js' ),
						array( 'jquery' ),
						Plugin::getInstance()->getPluginVersion(),
						true
					);
				}
			}
		);

		add_action( 'init', array( '\MPHB\CheckoutFields\Fields\FileUploadField', 'processViewUploadedFileRequest' ) );
	}

	/**
	 * Adds custom checkout fields to the built-inBooking Hotel plugin customer fields
	 */
	private function filterCustomerFields( array $customerFields ): array {

		$allCustomFields = CheckoutFieldsHelper::getEnabledCheckoutFields();
		$checkoutFields  = array();

		foreach ( $allCustomFields as $name => $field ) {

			// We don't need abstract fields in the array for parsing and validation
			if ( in_array( $field->type, array( 'heading', 'paragraph' ) ) ) {
				continue;
			}

			if ( isset( $customerFields[ $name ] ) ) {
				// Copy labels and type, but update "enabled" and "required"
				$checkoutFields[ $name ] = $customerFields[ $name ];

				$checkoutFields[ $name ]['enabled']  = $field->isEnabled;
				$checkoutFields[ $name ]['required'] = $field->isRequired;

			} else {

				// we set any file upload field as optional for admin booking creation
				$isRequired = 'file_upload' == $field->type && is_admin() ? false : $field->isRequired;

				$checkoutFields[ $name ] = array(
					'label'       => $field->title,
					'type'        => $field->type,
					'enabled'     => $field->isEnabled,
					'required'    => $isRequired,
					'labels'      => array(
						// Translators: %s is a field name like "birth_date".
						'required_error' => sprintf( esc_html__( 'The field "%s" is required.', 'mphb-checkout-fields' ), $name ),
					),
					'file_types'  => $field->fileTypes,
					'upload_size' => $field->uploadSize,
				);
			}
		}

		return $checkoutFields;
	}

	/**
	 * Change enctype for checkout form when it contains File Upload field
	 */
	private function filterCheckoutFormEnctypeAttribute( string $enctypeValue = '' ): string {

		$fields = CheckoutFieldsHelper::getEnabledCheckoutFields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( 'file_upload' == $field->type ) {
					$enctypeValue = 'multipart/form-data';
				}
			}
		}
		return $enctypeValue;
	}

	/**
	 * Parses customer data for custom fields of this plugin.
	 */
	private function parseCustomerDataForCustomFields( array $customerData, array $rawData, array $customerFields ): array {

		$errors = array();

		foreach ( $customerFields as $fieldName => $field ) {

			$fullName = MPHB()->addPrefix( $fieldName, '_' );

			if ( isset( $rawData[ $fullName ] ) && 'file_upload' == $field['type'] ) {

				$value = '';

				try {

					$value = Fields\FileUploadField::uploadFile( $fieldName, $rawData[ $fullName ], $field );

				} catch ( \Throwable $e ) {

					error_log( 'ERROR: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
					$errors[] = $e->getMessage();
				}

				$customerData[ $fieldName ] = $value;
			}
		}

		if ( ! empty( $errors ) ) {
			add_filter(
				'mphb_parse_customer_errors',
				function ( $e ) use ( $errors ) {
					return array_merge( $e, $errors );
				}
			);
		}

		return $customerData;
	}

	/**
	 * Removes "Quick Edit" and maybe the "Trash" actions on the Checkout Fields List page
	 */
	private function filterCheckoutFieldsListRowActions( array $actions, \WP_Post $wpPost ): array {

		if ( ! CheckoutFieldsHelper::isCheckoutFieldPost( $wpPost ) ) {
			return $actions;
		}

		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		if ( isset( $actions['trash'] ) && CheckoutFieldsHelper::isDefaultCheckoutFieldPost( $wpPost ) ) {
			unset( $actions['trash'] );
		}

		return $actions;
	}

	/**
	 * @param mixed $check NULL by default
	 * @return mixed bool or NULL
	 */
	private function preventRemovalOfDefaultCheckoutFields( $check, \WP_Post $wpPost ) {

		return CheckoutFieldsHelper::isDefaultCheckoutFieldPost( $wpPost ) ? true : $check;
	}

	/**
	 * @param \MPHB\Admin\Groups\MetaBoxGroup[] $groups
	 * @param string                            $postType
	 * @return \MPHB\Admin\Groups\MetaBoxGroup[]
	 */
	private function addCustomCheckoutFieldsToCustomerOnBookingEditPage( array $groups, string $postType ): array {

		if ( $postType != MPHB()->postTypes()->booking()->getPostType() ) {
			return $groups;
		}

		$postId = mphb_get_editing_post_id();

		if ( ! $postId ) {
			return $groups;
		}

		$booking  = mphb()->getBookingRepository()->findById( $postId );
		$customer = ! is_null( $booking ) ? $booking->getCustomer() : null;

		if ( is_null( $customer ) ) {
			return $groups;
		}

		$customFields = mphb_get_custom_customer_fields();

		if ( empty( $customFields ) ) {
			return $groups;
		}

		// Add fields to Customer Information
		foreach ( $groups as $group ) {

			if ( 'mphb_customer' != $group->getName() ) {
				continue;
			}

			foreach ( $customFields as $fieldName => $customField ) {

				$value          = $customer->getCustomField( $fieldName );
				$innerFieldName = MPHB()->addPrefix( $fieldName, '_' );

				$fieldAtts = array();

				switch ( $customField['type'] ) {

					case 'checkbox':
						$checkoutField = Plugin::getInstance()->getCheckoutFieldRepository()->findOne(
							array(
								'meta_query' => array(
									array(
										'key'   => 'mphb_cf_name',
										'value' => $fieldName,
									),
								),
							)
						);
						$fieldAtts     = array(
							'type'        => 'checkbox',
							'label'       => $customField['label'],
							'inner_label' => ! is_null( $checkoutField ) ? $checkoutField->innerLabel : '',
							'default'     => false,
						);
						break;

					case 'country':
						$fieldAtts = array(
							'type'  => 'select',
							'label' => $customField['label'],
							'list'  => array( '' => esc_html__( '— Select —', 'mphb-checkout-fields' ) ) + mphb()->settings()->main()->getCountriesBundle()->getCountriesList(),
						);
						break;

					case 'date_of_birth':
						$fieldAtts = array(
							'type'  => 'date-of-birth',
							'label' => $customField['label'],
						);
						break;

					case 'select':
						$checkoutField = Plugin::getInstance()->getCheckoutFieldRepository()->findOne(
							array(
								'meta_query' => array(
									array(
										'key'   => 'mphb_cf_name',
										'value' => $fieldName,
									),
								),
							)
						);

						$fieldAtts = array(
							'type'  => 'select',
							'label' => $customField['label'],
							'list'  => $checkoutField->options,
						);
						break;

					case 'textarea':
						$fieldAtts = array(
							'type'  => 'textarea',
							'label' => $customField['label'],
							'rows'  => apply_filters( 'mphb_cf_textarea_rows_count', 4 ),
						);
						break;

					case 'file_upload':
						$link = \MPHB\CheckoutFields\Fields\FileUploadField::getUploadedFileLink( $postId, $innerFieldName );

						if ( empty( $link ) ) {
							
							$fieldAtts = array(
								'type'        => 'placeholder',
								'label'       => $customField['label'],
								'default' => esc_html__( 'File is not uploaded', 'mphb-checkout-fields' ),
							);
						} else {

							$fieldAtts = array(
								'type'        => 'link-button',
								'label'       => $customField['label'],
								'href'        => $link,
								'target'      => '_blank',
								'inner_label' => esc_html__( 'View file', 'mphb-checkout-fields' ),
							);
						}

						break;

					default:
						$fieldAtts = array(
							'type'  => 'text',
							'label' => $customField['label'],
						);
						break;
				}

				$group->addField( \MPHB\Admin\Fields\FieldFactory::create( $innerFieldName, $fieldAtts, $value ) );
			}

			break;
		}

		return $groups;
	}
}
