<?php

namespace MPHB\CheckoutFields\Views;

use MPHB\CheckoutFields\Fields\DateOfBirthField;
use MPHB\CheckoutFields\Fields\FileUploadField;
use MPHB\CheckoutFields\CheckoutFieldsHelper;

/**
 * @since 1.0
 */
class CheckoutView {

	/**
	 * @param \MPHB\Entities\Booking $booking
	 * @param array                  $details Array of [room_type_id, rate_id, allowed_rates,
	 *                      adults, children] (also "room_id" on admin page).
	 *
	 * @since 1.0
	 */
	public static function renderCustomerDetails( $booking, $details, $customer = null ) {

		$customerFirstName = null;
		$customerLastName = null;
		$customerEmail = null;
		$customerPhone = null;
		
		if ( empty( $customer ) && is_user_logged_in() ) {

			$user = wp_get_current_user();
			
			$customerFirstName = get_user_meta( $user->ID, 'first_name', true );
			$customerLastName = get_user_meta( $user->ID, 'last_name', true );
			$customerEmail = $user->data->user_email;

		} elseif( ! empty( $customer ) ) {

			$customerFirstName = $customer->getFirstName();
			$customerLastName = $customer->getLastName();
			$customerEmail = $customer->getEmail();
			$customerPhone = $customer->getPhone();
		}
        
		$fields = CheckoutFieldsHelper::getEnabledCheckoutFields();

		?>
		<section id="mphb-customer-details" class="mphb-checkout-section mphb-customer-details">
			<h3 class="mphb-customer-details-title"><?php esc_html_e( 'Your Information', 'mphb-checkout-fields' ); ?></h3>

			<p class="mphb-required-fields-tip">
				<small>
					<?php
						// Translators: "Required fields are followed by <abbr>*</abbr>"
						printf( esc_html__( 'Required fields are followed by %s', 'mphb-checkout-fields' ), '<abbr title="' . esc_html__( 'Required', 'mphb-checkout-fields' ) . '">*</abbr>' );
					?>
				</small>
			</p>

			<?php do_action( 'mphb_sc_checkout_form_customer_details' ); ?>

			<?php
			foreach ( $fields as $field ) {

				$wrapperClass = '';

				if ( ! empty( $field->name ) ) {

					$wrapperClass .= 'mphb-customer-' . str_replace( '_', '-', $field->name );

					// Backward compatibility: "First Name" field had the class "mphb-customer-name"
					if ( $field->name == 'first_name' ) {
						$wrapperClass .= ' mphb-customer-name';
					}
				}

				$wrapperClass .= " mphb-{$field->type}-control";

				if ( ! empty( $field->cssClass ) ) {
					$wrapperClass .= ' ' . $field->cssClass;
				}

				$wrapperClass = trim( $wrapperClass );

				if ( 'heading' == $field->type ) { ?>
				
					<h4 class="<?php echo esc_attr( $wrapperClass ); ?>"><?php echo esc_html( $field->title ); ?></h4>

				<?php } elseif ( 'paragraph' == $field->type ) { ?>

					<p class="<?php echo esc_attr( $wrapperClass ); ?>"><?php echo wp_kses( $field->textContent, array(
						'a' => array(
							'href' => array(),
							'title' => array(),
							'target' => array()
						),
						'br' => array(),
						'em' => array(),
						'strong' => array(),
					) ); ?></p>

				<?php
				} elseif ( ! empty( $field->name ) ) {

					if ( defined('\MPHB\Bundles\CustomerBundle::CUSTOMER_FIELD_NAME_FIRST_NAME') ) {

						if ( ! empty( $customerFirstName ) && 
							\MPHB\Bundles\CustomerBundle::CUSTOMER_FIELD_NAME_FIRST_NAME === $field->name ) {

							$field->value = $customerFirstName;

						} elseif ( ! empty( $customerLastName ) &&
							\MPHB\Bundles\CustomerBundle::CUSTOMER_FIELD_NAME_LAST_NAME === $field->name ) {

							$field->value = $customerLastName;

						} elseif ( ! empty( $customerEmail ) &&
							\MPHB\Bundles\CustomerBundle::CUSTOMER_FIELD_NAME_EMAIL === $field->name ) {

							$field->value = $customerEmail;

						} elseif ( ! empty( $customerPhone ) &&
							\MPHB\Bundles\CustomerBundle::CUSTOMER_FIELD_NAME_PHONE === $field->name ) {

							$field->value = $customerPhone;
						}
					}
					?>

					<p class="<?php echo esc_attr( $wrapperClass ); ?>">
						<?php
						static::renderBeforeField( $field );
						static::renderField( $field );
						static::renderAfterField( $field );
						?>
					</p>

				<?php } ?>
			<?php } ?>
			<?php
			if ( method_exists( \MPHB\Views\Shortcodes\CheckoutView::class, 'echoCreateCustomerAccountCheckbox' )) {
				\MPHB\Views\Shortcodes\CheckoutView::echoCreateCustomerAccountCheckbox();
			}
			?>
		</section>
		<?php
	}

	/**
	 * @param \MPHB\CheckoutFields\Entities\CheckoutField $field
	 *
	 * @since 1.0
	 */
	protected static function renderBeforeField( $field ) {

		if ( $field->type != 'checkbox' ) {
			static::renderLabel( $field );
			echo '<br />';
		}
	}

	/**
	 * @param \MPHB\CheckoutFields\Entities\CheckoutField $field
	 *
	 * @since 1.0
	 */
	protected static function renderField( $field ) {

		$inputId    = $inputName = 'mphb_' . $field->name; // "mphb_birth_date"
		$isRequired = CheckoutFieldsHelper::isFieldRequired( $field );

		$fileTypes  = $field->fileTypes;
		$uploadSize = $field->uploadSize;

		$atts = array(
			'name' => $inputName,
			'id'   => $inputId,
		);

		if ( null !== $field->value ) {
			$atts['value'] = $field->value;
		}

		if ( $isRequired ) {
			$atts['required'] = 'required';
		}

		if ( ! empty( $field->placeholder ) ) {
			$atts['placeholder'] = $field->placeholder;
		}

		$output = '';

		switch ( $field->type ) {
			case 'text':
			case 'email':
			case 'phone':
				if ( $field->type == 'phone' ) {
					$atts['type'] = 'tel';
				} else {
					$atts['type'] = $field->type;
				}

				if ( $field->type != 'email' && ! empty( $field->pattern ) ) {
					$atts['pattern'] = $field->pattern;
				}

				$output .= '<input' . mphb_tmpl_render_atts( $atts ) . ' />';
				break;

			case 'textarea':
				$atts['rows'] = apply_filters( 'mphb_cf_textarea_rows_count', 4 );

				$output .= '<textarea' . mphb_tmpl_render_atts( $atts ) . '></textarea>';
				break;

			case 'checkbox':
				$atts['type']  = $field->type;
				$atts['value'] = '1';

				if ( $field->isChecked ) {
					$atts['checked'] = 'checked';
				}

				$output .= '<input type="hidden" name="' . esc_attr( $inputName ) . '" value="0" />';
				$output .= '<input' . mphb_tmpl_render_atts( $atts ) . ' />';
				break;

			case 'select':
				$output .= mphb_tmpl_render_select( $field->options, '', $atts );
				break;

			case 'country':
				$countries      = array( '' => esc_html__( '— Select —', 'mphb-checkout-fields' ) ) + mphb()->settings()->main()->getCountriesBundle()->getCountriesList();
				$defaultCountry = mphb()->settings()->main()->getDefaultCountry();

				$output .= mphb_tmpl_render_select( $countries, $defaultCountry, $atts );
				break;

			case 'date_of_birth':
				$field = new DateOfBirthField( $inputName, array( 'required' => $isRequired ) );

				$output .= $field->renderFields();
				break;

			case 'file_upload':
				$field = new FileUploadField(
					$inputName,
					array(
						'required'    => $isRequired,
						'file_types'  => $fileTypes,
						'upload_size' => $uploadSize,
					)
				);

				$output .= $field->renderFields();
				break;
		}

		echo $output;
	}

	/**
	 * @param \MPHB\CheckoutFields\Entities\CheckoutField $field
	 *
	 * @since 1.0
	 */
	protected static function renderAfterField( $field ) {

		if ( $field->type == 'checkbox' ) {
			static::renderLabel( $field );
		}

		if ( ! empty( $field->description ) ) {
			echo '<span class="mphb-control-description">', wp_kses( $field->description, array(
				'a' => array(
					'href' => array(),
					'title' => array(),
					'target' => array()
				),
				'br' => array(),
				'em' => array(),
				'strong' => array(),
			) ), '</span>';
		}
	}

	/**
	 * @param \MPHB\CheckoutFields\Entities\CheckoutField $field
	 *
	 * @since 1.0
	 */
	protected static function renderLabel( $field ) {

		$inputId = 'mphb_' . $field->name; // "mphb_birth_date"

		if ( $field->type == 'date_of_birth' ) {
			$inputId .= '-year'; // "mphb_birth_date-year"
		}

		$output      = '<label for="' . esc_attr( $inputId ) . '">';
			$output .= $field->type != 'checkbox' ? esc_html( $field->title ) : esc_html( $field->innerLabel );

		if ( CheckoutFieldsHelper::isFieldRequired( $field ) ) {
			$output .= '&nbsp;';
			$output .= '<abbr title="' . esc_html__( 'Required', 'mphb-checkout-fields' ) . '">*</abbr>';
		}
		$output .= '</label>';

		echo $output;
	}
}
