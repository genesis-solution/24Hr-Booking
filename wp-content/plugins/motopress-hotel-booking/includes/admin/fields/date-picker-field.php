<?php

namespace MPHB\Admin\Fields;

class DatePickerField extends TextField {

	const TYPE = 'datepicker';

	private $format;
	private $datepickFormat;
	protected $readonly = true;

	/**
	 * @var array
	 *
	 * @since 3.9.6
	 */
	protected $fieldDependent = array();

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		if ( ! empty( $details['dependency'] ) ) {
			foreach ( $details['dependency'] as $key => $value ) {
				$this->fieldDependent[ $key ] = $value;
			}
		}

		if ( empty( $this->inputMode ) ) {
			$this->inputMode = 'none';
		}

		$this->detectFormat( isset( $details['format'] ) ? $details['format'] : null );
	}

	public function detectFormat( $format = null ) {
		$this->format         = ! is_null( $format ) ? $format : MPHB()->settings()->dateTime()->getDateFormat();
		$this->datepickFormat = MPHB()->settings()->dateTime()->getDateFormatJS();
	}

	public function getFormattedValue() {
		return ! empty( $this->value ) ? $this->convertToFormat( $this->value ) : $this->value;
	}

	/**
	 *
	 * @param string $date
	 * @return string
	 */
	private function convertToDBFormat( $date ) {
		$dateObj = \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );
		return $dateObj ? $dateObj->format( 'Y-m-d' ) : '';
	}

	/**
	 *
	 * @param string $date
	 * @return string
	 */
	private function convertToFormat( $date ) {
		$dateObj = \DateTime::createFromFormat( 'Y-m-d', $date );
		return $dateObj ? $dateObj->format( $this->format ) : '';
	}

	protected function renderInput() {
		$result  = '<input type="text" name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $this->getFormattedValue() ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" class="' . $this->generateSizeClasses() . '"' . $this->generateAttrs() . '/>';
		$result .= '<input type="hidden" name="' . esc_attr( $this->getName() ) . '" value="' . esc_attr( $this->getValue() ) . '" id="' . MPHB()->addPrefix( $this->getName() . '-hidden' ) . '" />';

		return $result;
	}

	/**
	 *
	 * @since 3.9.6 - generate new 'data-dependent-as-min', 'data-dependent-as-max' attributes
	 */
	protected function generateAttrs() {
		$attrs  = parent::generateAttrs();
		$attrs .= ' data-format="' . esc_attr( $this->datepickFormat ) . '"';
		$attrs .= ! empty( $this->pattern ) ? ' pattern="' . esc_attr( $this->pattern ) . '"' : '';
		$attrs .= isset( $this->fieldDependent['as_min'] ) ? ' data-dependent-as-min="' . esc_attr( $this->fieldDependent['as_min'] ) . '"' : '';
		$attrs .= isset( $this->fieldDependent['as_max'] ) ? ' data-dependent-as-max="' . esc_attr( $this->fieldDependent['as_max'] ) . '"' : '';
		return $attrs;
	}

	/**
	 *
	 * @param string $value
	 * @return string
	 */
	public function sanitize( $value ) {
		return $this->convertToDBFormat( $value );
	}

	public static function renderValue( TextField $field ) {
		return $field->getValue();
	}

}
