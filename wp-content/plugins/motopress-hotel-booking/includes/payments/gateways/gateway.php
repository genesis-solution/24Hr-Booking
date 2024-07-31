<?php

namespace MPHB\Payments\Gateways;

use \MPHB\Admin\Groups;
use \MPHB\Admin\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Gateway implements GatewayInterface {

	const MODE_LIVE    = 'live';
	const MODE_SANDBOX = 'sandbox';

	/**
	 * @var string
	 */
	protected $id = '';

	/**
	 * @var string
	 */
	protected $adminTitle = '';

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $adminDescription = '';

	/**
	 * @var string
	 * @since 3.6.1
	 */
	protected $instructions = '';

	/**
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * @var boolean
	 */
	protected $isSandbox = false;

	/**
	 * @var array
	 */
	protected $paymentFieldsErrors = array();

	/**
	 * @var array
	 */
	protected $postedPaymentFields = array();

	/**
	 * @var array
	 */
	protected $paymentFields;

	/**
	 * @var type
	 */
	protected $showOptions = true;

	private $defaultOptions;

	/**
	 * @since 4.2.4
	 * @var bool
	 */
	protected $isSuspended = false;

	public function __construct() {

		$this->id             = $this->initId();
		$this->defaultOptions = $this->initDefaultOptions();
		$this->setupProperties();
		$this->setupPaymentFields();

		add_action( 'mphb_register_gateways', array( $this, 'preRegister' ) );
		add_action( 'mphb_init_gateways', array( $this, 'register' ) );
	}

	public function isShowOptions() {
		return $this->showOptions;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getAdminTitle() {
		return $this->adminTitle;
	}

	/**
	 * @return strings
	 */
	public function getAdminDescription() {
		return $this->adminDescription;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return string
	 * @since 3.6.1
	 */
	public function getInstructions() {
		return $this->instructions;
	}

	/**
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * Whether is Gateway Eanbled and support current plugin settings (currency, etc.)
	 *
	 * @return boolean
	 */
	public function isActive() {
		return $this->enabled && ! $this->isSuspended;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return array
	 */
	protected function initDefaultOptions() {
		return array(
			'title'        => $this->id,
			'description'  => '',
			'instructions' => '',
			'enabled'      => false,
			'is_sandbox'   => false,
		);
	}

	protected function setupProperties() {

		$this->title        = $this->getOption( 'title' );
		$this->description  = $this->getOption( 'description' );
		$this->instructions = $this->getOption( 'instructions' );
		$this->enabled      = $this->getOption( 'enable' );
		$this->isSandbox    = $this->getOption( 'is_sandbox' );
	}

	/**
	 * @param string $optionName
	 * @return mixed
	 */
	protected function getOption( $optionName ) {

		$fullOptionName = "mphb_payment_gateway_{$this->id}_{$optionName}";

		$optionValue = get_option( $fullOptionName, $this->getDefaultOption( $optionName ) );

		$translatableOptions = array( 'title', 'description', 'instructions' );
		if ( in_array( $optionName, $translatableOptions ) ) {
			$optionValue = apply_filters( 'mphb_translate_string', $optionValue, $fullOptionName );
		}

		return $optionValue;
	}

	/**
	 * @param string $optionName
	 * @return mixed
	 */
	protected function getDefaultOption( $optionName ) {

		return isset( $this->defaultOptions[ $optionName ] ) ? $this->defaultOptions[ $optionName ] : '';
	}

	abstract protected function initId();

	public function setupPaymentFields() {

		$fields = $this->initPaymentFields();

		foreach ( $fields as $key => &$field ) {
			$field['type']     = isset( $field['type'] ) ? $field['type'] : 'text';
			$field['required'] = isset( $field['required'] ) ? $field['required'] : false;
			$field['meta_id']  = isset( $field['meta_id'] ) ? $field['meta_id'] : $key;
			$field['label']    = isset( $field['label'] ) ? $field['label'] : '';
		}
		$this->paymentFields = $fields;
	}

	/**
	 * @return array
	 */
	public function initPaymentFields() {
		return array();
	}

	/**
	 * @since 4.2.4
	 * @param string[] $suspendPayments
	 */
	public function preRegister( $suspendPayments ) {

		$this->isSuspended = in_array( $this->id, $suspendPayments );
	}

	/**
	 * @param \MPHB\Payments\Gateways\GatewayManager $gatewayManager
	 */
	public function register( GatewayManager $gatewayManager ) {

		if ( ! $this->isSuspended ) {
			$gatewayManager->addGateway( $this );
		}
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param \MPHB\Entities\Payment $payment
	 */
	abstract public function processPayment( \MPHB\Entities\Booking $booking, \MPHB\Entities\Payment $payment );

	/**
	 * @return string
	 */
	public function getMode() {

		return $this->isSandbox ? self::MODE_SANDBOX : self::MODE_LIVE;
	}

	/**
	 * @param array $input
	 * @param array $errors
	 * @return boolean
	 */
	public function parsePaymentFields( $input, &$errors ) {

		foreach ( $this->paymentFields as $key => $field ) {

			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}

			// Get Value
			switch ( $field['type'] ) {
				case 'checkbox':
					$this->postedPaymentFields[ $key ] = isset( $_POST[ $key ] ) ? 1 : 0;
					break;
				case 'textarea':
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->postedPaymentFields[ $key ] = isset( $_POST[ $key ] ) ? wp_strip_all_tags( wp_check_invalid_utf8( wp_unslash( $_POST[ $key ] ) ) ) : '';
					break;
				case 'email':
					$this->postedPaymentFields[ $key ] = isset( $_POST[ $key ] ) ? sanitize_email( wp_unslash( $_POST[ $key ] ) ) : '';
					break;
				case 'select':
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->postedPaymentFields[ $key ] = isset( $_POST[ $key ] ) && array_key_exists( mphb_clean( wp_unslash( $_POST[ $key ] ) ), $field['list'] ) ? mphb_clean( $_POST[ $key ] ) : '';
					break;
				default:
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->postedPaymentFields[ $key ] = isset( $_POST[ $key ] ) ? mphb_clean( wp_unslash( $_POST[ $key ] ) ) : '';
					break;
			}

			// Validation: Required fields
			if ( $field['required'] && ( ! isset( $this->postedPaymentFields[ $key ] ) || '' === $this->postedPaymentFields[ $key ] ) ) {
				$this->paymentFieldsErrors[] = sprintf( __( '%s is a required field.', 'motopress-hotel-booking' ), $field['label'] );
			}

			if ( ! empty( $this->postedPaymentFields[ $key ] ) ) {

				// Validation rules
				if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
					foreach ( $field['validate'] as $rule ) {
						switch ( $rule ) {
							case 'email':
								$this->postedPaymentFields[ $key ] = strtolower( $this->postedPaymentFields[ $key ] );

								if ( ! is_email( $this->postedPaymentFields[ $key ] ) ) {
									$this->paymentFieldsErrors[] = sprintf( __( '%s is not a valid email address.', 'motopress-hotel-booking' ), $field['label'] );
								}
						}
					}
				}
			}
		}

		$errors = array_merge( $errors, $this->paymentFieldsErrors );

		return empty( $this->paymentFieldsErrors );
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function renderPaymentFields( $booking ) {

		foreach ( $this->paymentFields as $key => $field ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo str_replace( '%field_placeholder%', $this->renderField( $key, $field ), $this->renderFieldWrapper( $key, $field ) );
		}
	}

	/**
	 * @param string $fieldName
	 * @param array  $fieldDetails
	 * @return string
	 */
	private function renderFieldWrapper( $fieldName, $fieldDetails ) {

		$fieldPlaceholder = '%field_placeholder%';
		ob_start();
		if ( $fieldDetails['type'] === 'hidden' ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $fieldPlaceholder;
		} else {
			$labelClass = 'mphb-billing-field-label';
			switch ( $fieldDetails['type'] ) {
				case 'checkbox':
					$labelClass .= ' mphb-checkbox-label';
					break;
				case 'radio':
					$labelClass .= ' mphb-radio-label';
					break;
			}
			?>
			<p class="<?php echo esc_attr( $fieldName ); ?>">
				<label for="<?php echo esc_attr( $fieldName ); ?>" class="<?php echo esc_attr( $labelClass ); ?>">
					<?php echo esc_html( $fieldDetails['label'] ); ?>
					<?php if ( $fieldDetails['required'] ) { ?>
						<abbr title="<?php esc_attr_e( 'Required', 'motopress-hotel-booking' ); ?>">*</abbr>
					<?php } ?>
				</label>
				<br />
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $fieldPlaceholder;
				?>
			</p>
			<?php
		}
		return ob_get_clean();
	}

	/**
	 * @param string $fieldName
	 * @param array  $fieldDetails
	 * @return string
	 */
	private function renderField( $fieldName, $fieldDetails ) {

		ob_start();
		switch ( $fieldDetails['type'] ) {
			case 'hidden':
				echo '<input type="hidden" id="' . esc_attr( $fieldName ) . '" name="' . esc_attr( $fieldName ) . '" />';
				break;
			case 'select':
				$list = ! empty( $fieldDetails['list'] ) ? $fieldDetails['list'] : array();
				echo '<select id="' . esc_attr( $fieldName ) . '" name="' . esc_attr( $fieldName ) . '" ' . ( $fieldDetails['required'] ? 'required="required"' : '' ) . '>';
				foreach ( $list as $id => $label ) {
					echo '<option value="' . esc_attr( $id ) . '"> ' . esc_html( $label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'text':
			default:
				echo '<input type="text" id="' . esc_attr( $fieldName ) . '" name="' . esc_attr( $fieldName ) . '" ' . ( $fieldDetails['required'] ? 'required="required"' : '' ) . ' />';
				break;
		}
		return ob_get_clean();
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return bool
	 */
	public function storePaymentFields( $payment ) {

		$success = true;
		foreach ( $this->postedPaymentFields as $fieldName => $fieldValue ) {
			$updated = update_post_meta( $payment->getId(), $this->paymentFields[ $fieldName ]['meta_id'], $fieldValue );
			$success = $success && $updated;
		}
		return $success;
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	protected function paymentCompleted( $payment ) {

		return MPHB()->paymentManager()->completePayment( $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	protected function paymentFailed( $payment ) {

		return MPHB()->paymentManager()->failPayment( $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	protected function paymentOnHold( $payment ) {

		return MPHB()->paymentManager()->holdPayment( $payment );
	}

	/**
	 * @param \MPHB\Entities\Payment $payment
	 * @return boolean
	 */
	protected function paymentRefunded( $payment ) {

		return MPHB()->paymentManager()->refundPayment( $payment );
	}

	/**
	 * @param \MPHB\Admin\Tabs\SettingsSubTab $subTab
	 * @since 3.6.1 added new filter - "mphb_gateway_has_instructions".
	 */
	public function registerOptionsFields( &$subTab ) {

		$mainGroup = new Groups\SettingsGroup( "mphb_payments_{$this->id}_group", '', $subTab->getOptionGroupName() );

		$mainGroupFields = array();
		// Braintree gateway disables this field if something goes wrong
		$mainGroupFields[] = Fields\FieldFactory::create(
			"mphb_payment_gateway_{$this->id}_enable",
			array(
				'type'        => 'checkbox',
				// translators: %s is the payment gateway title.
				'inner_label' => sprintf( __( 'Enable "%s"', 'motopress-hotel-booking' ), $this->title ),
				'default'     => $this->getDefaultOption( 'enable' ),
			)
		);

		if ( apply_filters( 'mphb_gateway_has_sandbox', true, $this->getId() ) ) {
			$mainGroupFields[] = Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_is_sandbox",
				array(
					'type'        => 'checkbox',
					'label'       => __( 'Test Mode', 'motopress-hotel-booking' ),
					'inner_label' => __( 'Enable Sandbox Mode', 'motopress-hotel-booking' ),
					'default'     => $this->getDefaultOption( 'is_sandbox' ),
					'description' => __( 'Sandbox can be used to test payments.', 'motopress-hotel-booking' ),
				)
			);
		}

		$mainGroupFields[] = Fields\FieldFactory::create(
			"mphb_payment_gateway_{$this->id}_title",
			array(
				'type'         => 'text',
				'label'        => __( 'Title', 'motopress-hotel-booking' ),
				'default'      => $this->getDefaultOption( 'title' ),
				'description'  => __( 'Payment method title that the customer will see on your website.', 'motopress-hotel-booking' ),
				'translatable' => true,
			)
		);
		$mainGroupFields[] = Fields\FieldFactory::create(
			"mphb_payment_gateway_{$this->id}_description",
			array(
				'type'         => 'textarea',
				'label'        => __( 'Description', 'motopress-hotel-booking' ),
				'default'      => $this->getDefaultOption( 'description' ),
				'description'  => __( 'Payment method description that the customer will see on your website.', 'motopress-hotel-booking' ),
				'translatable' => true,
			)
		);

		if ( apply_filters( 'mphb_gateway_has_instructions', true, $this->getId() ) ) {
			$mainGroupFields[] = Fields\FieldFactory::create(
				"mphb_payment_gateway_{$this->id}_instructions",
				array(
					'type'         => 'textarea',
					'label'        => __( 'Instructions', 'motopress-hotel-booking' ),
					'default'      => $this->getDefaultOption( 'instructions' ),
					'description'  => __( 'Instructions for a customer on how to complete the payment.', 'motopress-hotel-booking' ),
					'translatable' => true,
				)
			);
		}

		$mainGroup->addFields( $mainGroupFields );

		$subTab->addGroup( $mainGroup );
	}

	/**
	 * @return bool
	 */
	public function isSandbox() {
		return $this->isSandbox;
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @return string
	 */
	public function generateItemName( $booking ) {

		if ( $booking->getId() > 0 ) {
			return sprintf( __( 'Reservation #%d', 'motopress-hotel-booking' ), $booking->getId() );
		} else {
			return __( 'Accommodation(s) reservation', 'motopress-hotel-booking' );
		}
	}

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @return array
	 */
	public function getCheckoutData( $booking ) {

		return array(
			'amount'             => $booking->calcDepositAmount(),
			'paymentDescription' => $this->generateItemName( $booking ),
		);
	}

	/**
	 * @return bool
	 */
	public function hasPaymentFields() {

		return empty( $this->paymentFields );
	}

	/**
	 * @return bool
	 */
	public function hasVisiblePaymentFields() {

		$visibleFields = array_filter(
			$this->paymentFields,
			function( $field ) {
				return $field['type'] !== 'hidden';
			}
		);
		return ! empty( $visibleFields );
	}
}
