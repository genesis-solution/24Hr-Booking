<?php

namespace MPHB\Admin\Fields;

class DynamicSelectField extends SelectField implements DependentField {

	const TYPE = 'dynamic-select';

	/**
	 *
	 * @var string Dependency input name.
	 */
	protected $dependencyInput;
	protected $ajaxAction;
	protected $listCallback = null;

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->dependencyInput = $details['dependency_input'];
		$this->ajaxAction      = $details['ajax_action'];
		$this->listCallback    = isset( $details['list_callback'] ) ? $details['list_callback'] : $this->listCallback;
	}

	protected function renderInput() {

		$result = '<select name="' . esc_attr( $this->getName() ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" ' . $this->generateAttrs() . '>';

		foreach ( $this->list as $key => $label ) {
			$result .= '<option value="' . esc_attr( $key ) . '"' . selected( $this->getValue(), $key, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$result .= '</select>';
		$result .= '<span class="mphb-preloader mphb-hide"></span>';
		$result .= '<div class="mphb-errors-wrapper mphb-hide"></div>';
		return $result;
	}

	protected function generateAttrs() {
		$attrs  = parent::generateAttrs();
		$attrs .= ( isset( $this->dependencyInput ) ) ? ' data-dependency="' . $this->dependencyInput . '"' : '';
		$attrs .= ' data-ajax-action="' . $this->ajaxAction . '"';
		$attrs .= ' data-ajax-nonce="' . wp_create_nonce( $this->ajaxAction ) . '"';
		$attrs .= ' data-default="' . $this->default . '"';
		return $attrs;
	}

	public function getDependencyInput() {
		return $this->dependencyInput;
	}

	public function setDependencyInput( $dependencyInput ) {
		$this->dependencyInput = $dependencyInput;
	}

	public function updateDependency( $dependencyValue ) {
		$newList = array();

		if ( isset( $this->list[ $this->default ] ) ) {
			$newList[ $this->default ] = $this->list[ $this->default ];
		} else {
			$newList[ $this->default ] = __( '— Select —', 'motopress-hotel-booking' );
		}

		if ( ! is_null( $this->listCallback ) && $dependencyValue != $this->default ) {
			$moreVariants = call_user_func( $this->listCallback, $dependencyValue );
			$newList      = array_replace( $newList, $moreVariants );
		}

		$this->list = $newList;
	}

	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}

}
