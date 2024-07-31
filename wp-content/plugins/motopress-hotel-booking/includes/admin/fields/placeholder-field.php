<?php

namespace MPHB\Admin\Fields;

class PlaceholderField extends InputField {

	const TYPE = 'placeholder';

	protected function renderInput() {
		$result = '<label>' . $this->default . '</label>';
		return $result;
	}

	public function sanitize( $value ) {
		return $value;
	}

	public static function renderValue( self $field ) {
		return '-';
	}
}
