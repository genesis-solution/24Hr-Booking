<?php

namespace MPHB\Admin\Fields;

abstract class InputField {

	protected $name;
	protected $details;
	protected $required     = false;
	protected $disabled     = false;
	protected $readonly     = false;
	protected $translatable = false;
	protected $default      = '';
	protected $value;
	protected $label             = '';
	protected $innerLabel        = '';
	protected $description       = '';
	protected $description2      = '';
	protected $additionalClasses = '';

	const TYPE = '';

	/**
	 *
	 * @param string $name
	 * @param array  $details
	 * @param string $value
	 */
	public function __construct( $name, $details, $value = ''/* , $model */ ) {
		$this->details           = $details;
		$this->name              = $name;
		$this->required          = ( isset( $details['required'] ) ) ? $details['required'] : $this->required;
		$this->disabled          = ( isset( $details['disabled'] ) ) ? $details['disabled'] : $this->disabled;
		$this->readonly          = ( isset( $details['readonly'] ) ) ? $details['readonly'] : $this->readonly;
		$this->default           = ( isset( $details['default'] ) ) ? $details['default'] : $this->default;
		$this->value             = ( ! empty( $value ) ) ? $value : $this->default;
		$this->label             = ( isset( $details['label'] ) ) ? $details['label'] : $this->label;
		$this->innerLabel        = ( isset( $details['inner_label'] ) ) ? $details['inner_label'] : $this->innerLabel;
		$this->description       = ( isset( $details['description'] ) ) ? $details['description'] : $this->description;
		$this->description2      = ( isset( $details['description2'] ) ) ? $details['description2'] : $this->description2;
		$this->translatable      = ( isset( $details['translatable'] ) ) ? $details['translatable'] : $this->translatable;
		$this->additionalClasses = ( isset( $details['classes'] ) ) ? $details['classes'] : $this->additionalClasses;
	}

	protected function getCtrlClasses() {
		$classes = 'mphb-ctrl mphb-ctrl-' . static::TYPE;
		if ( ! empty( $this->additionalClasses ) ) {
			$classes .= ' ' . $this->additionalClasses;
		}
		return $classes;
	}

	public function addClass( $class ) {
		if ( strpos( $this->additionalClasses, $class ) === false ) {
			$this->additionalClasses .= ' ' . $class;
		}
	}

	public function removeClass( $class ) {
		$this->additionalClasses = str_replace( ' ' . $class, '', $this->additionalClasses );
	}

	protected function getCtrlAtts() {
		return ' data-type="' . static::TYPE . '"';
	}

	protected function generateAttrs() {
		$attrs  = '';
		$attrs .= ( $this->required ) ? ' required="required"' : '';
		$attrs .= ( $this->disabled ) ? ' disabled="disabled"' : '';
		$attrs .= ( $this->readonly ) ? ' readonly="readonly"' : '';
		return $attrs;
	}

	public function setValue( $value ) {
		$this->value = ( $value !== '' ) ? $value : $this->default;
	}

	public function getValue() {
		return $this->value;
	}

	public function getLabel() {
		return $this->label;
	}

	public function getInnerLabel() {
		return $this->innerLabel;
	}

	public function getInnerLabelTag() {
		return ! empty( $this->innerLabel ) ?
			'&nbsp;<label for="mphb-' . esc_attr( $this->name ) . '">' . esc_html( $this->innerLabel ) . '</label>' : '';
	}

	public function getLabelTag() {

		$labelTag = '';

		if ( ! empty( $this->label ) ) {
			$labelTag = '<label for="mphb-' . esc_attr( $this->name ) . '">' . esc_html( $this->label ) . '</label>';

			if ( $this->required ) {
				$labelTag .= ' <strong><abbr title="required">*</abbr></strong>';
			}
		}

		return $labelTag;
	}

	public function hasLabel() {
		return $this->label !== false;
	}

	/**
	 *
	 * @param bool $disabled
	 */
	public function setDisabled( $disabled ) {
		$this->disabled = $disabled;
	}

	/**
	 *
	 * @param bool $required
	 */
	public function setRequired( $required ) {
		$this->required = $required;
	}

	/**
	 *
	 * @param bool $readonly
	 */
	public function setReadonly( $readonly ) {
		$this->readonly = $readonly;
	}

	public function getName() {
		return $this->name;
	}

	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 *
	 * @since 3.5.1
	 */
	public function getDescription() {
		return $this->description;
	}

	public function setDescription( $description ) {
		$this->description = $description;
	}

	public function getDescription2() {
		return $this->description2;
	}

	public function setDescription2( $description ) {
		$this->description2 = $description;
	}

	public function render() {

		ob_start();

		do_action( '_mphb_admin_before_field_render', $this->name );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mphb-ctrl-wrapper ' . esc_attr( $this->getCtrlClasses() ) . '" ' . $this->getCtrlAtts() . '>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->renderInput();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->getInnerLabelTag();

		if ( ! empty( $this->description ) ) {
			echo '<p class="description">' . wp_kses_post( $this->description ) . '</p>';
		}

		if ( ! empty( $this->description2 ) ) {
			echo '<div class="mphb-ctrl-description">' . wp_kses_post( $this->description2 ) . '</div>';
		}

		echo '</div>';

		do_action( '_mphb_admin_after_field_render', $this->name );

		$result = ob_get_contents();

		ob_end_clean();

		return $result;
	}

	public function output() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->render();
	}

	public function getDefault() {
		return $this->default;
	}

	abstract protected function renderInput();

	public function sanitize( $value ) {
		return $value;
	}

	public function getType() {
		return static::TYPE;
	}

	/**
	 *
	 * @return bool
	 */
	public function isTranslatable() {
		return $this->translatable;
	}

	public function isReadonly() {
		return $this->readonly;
	}

	/**
	 * @since 3.9.5
	 *
	 * @return bool
	 */
	public function isDisabled() {
		return $this->disabled;
	}
}
