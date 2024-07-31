<?php

namespace MPHB\Admin\Fields;

/**
 * @since 3.8
 */
class LinkButtonField extends InputField {

	const TYPE = 'link-button';

	protected $href       = '#';
	protected $target     = '';
	protected $innerClass = '';

	public function __construct( $name, $args, $value = '' ) {
		parent::__construct( $name, $args, $value );

		$this->href       = isset( $args['href'] ) ? $args['href'] : $this->href;
		$this->target     = isset( $args['target'] ) ? $args['target'] : $this->target;
		$this->innerClass = isset( $args['inner_class'] ) ? $args['inner_class'] : $this->innerClass;
	}

	protected function generateAttrs() {
		$atts  = ''; // parent::generateAttrs() - no need in "required", "disabled" and "readonly"
		$atts .= ' href="' . esc_url( $this->href ) . '"';
		$atts .= ' class="' . esc_attr( trim( 'button ' . $this->innerClass ) ) . '"';

		if ( ! empty( $this->target ) ) {
			$atts .= ' target="' . esc_attr( $this->target ) . '"';
		}

		return $atts;
	}

	protected function renderInput() {
		$result = '<a' . $this->generateAttrs() . '>' . esc_html( $this->innerLabel ) . '</a>';
		return $result;
	}

	public function getInnerLabelTag() {
		return '';
	}
}
