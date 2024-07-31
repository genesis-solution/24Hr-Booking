<?php

namespace MPHB\Admin\Fields;

/**
 * @since 3.9.8
 */
class SingleCheckboxField extends CheckboxField {

	const TYPE = 'single-checkbox';

	public static function renderValue( $field ) {

		$value      = $field->getValue();
		$innerLabel = $field->getInnerLabel();

		return $value ? $innerLabel : '';
	}
}
