<?php

namespace MPHB\Admin\Fields;

class EmailField extends TextField {

	const TYPE = 'email';

	public function sanitize( $value ) {
		return is_email( $value ) ? $value : $this->default;
	}
}
