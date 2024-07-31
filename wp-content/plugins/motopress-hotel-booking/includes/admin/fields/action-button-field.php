<?php

namespace MPHB\Admin\Fields;

/**
 * @since 3.7.0
 */
class ActionButtonField extends InputField {

	const TYPE = 'action-button';

	protected $checkInterval = 1000;
	protected $reloadAfter   = false; // true|false
	protected $redirectAfter = ''; // Redirect URL or empty string ""
	protected $buttonClasses = 'button';
	protected $inProgress    = false;

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		$this->checkInterval = isset( $details['check_interval'] ) ? $details['check_interval'] : $this->checkInterval;
		$this->reloadAfter   = isset( $details['reload_after'] ) ? $details['reload_after'] : $this->reloadAfter;
		$this->redirectAfter = isset( $details['redirect_after'] ) ? $details['redirect_after'] : $this->redirectAfter;
		$this->buttonClasses = isset( $details['button_classes'] ) ? $details['button_classes'] : $this->buttonClasses;
		$this->inProgress    = isset( $details['in_progress'] ) ? $details['in_progress'] : $this->inProgress;

		// The field does not support "required" or "readonly" parameters yet
		$this->required = false;
		$this->readonly = false;

		add_filter( 'mphb_custom_admin_nonces', array( $this, 'addNonce' ) );
	}

	public function addNonce( $customNonces ) {
		$action = $this->getName();

		if ( ! mphb_string_starts_with( $action, 'mphb' ) ) {
			$action = 'mphb_' . $action;
		}

		$customNonces[ $action ] = wp_create_nonce( $action );

		return $customNonces;
	}

	protected function generateAttrs() {
		// disabled="disabled"
		$atts = parent::generateAttrs();

		$atts .= ' data-check-interval="' . esc_attr( $this->checkInterval ) . '"';
		$atts .= ' data-reload-after="' . esc_attr( $this->reloadAfter ? 'yes' : 'no' ) . '"';
		$atts .= ' data-redirect-after="' . esc_attr( $this->redirectAfter ) . '"';
		$atts .= ' data-is-in-progress="' . esc_attr( $this->inProgress ? 'yes' : 'no' ) . '"';

		return $atts;
	}

	protected function renderInput() {
		$output = '<div class="button-row">';

		$output .= '<button name="' . esc_attr( $this->getName() ) . '" id="' . MPHB()->addPrefix( $this->getName() ) . '" class="' . esc_attr( $this->buttonClasses ) . '"' . $this->generateAttrs() . '>';
		$output .= esc_html( $this->innerLabel );
		$output .= '</button>';

		$output .= '<span class="mphb-preloader mphb-hide"></span>';
		$output .= ' <span class="status-text"></span>';

		$output .= '</div>';

		return $output;
	}

	public function getInnerLabelTag() {
		// Don't show the inner label after the field body. We used it as a
		// button label
		return '';
	}
}
