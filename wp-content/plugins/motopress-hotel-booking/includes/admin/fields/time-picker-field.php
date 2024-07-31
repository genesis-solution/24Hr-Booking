<?php

namespace MPHB\Admin\Fields;

class TimePickerField extends TextField {

	const TYPE = 'timepicker';

	private $afterLabel = '';

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );
		$this->afterLabel = ( isset( $details['after_label'] ) ) ? $details['after_label'] : __( 'HH:MM', 'motopress-hotel-booking' );
	}

	protected function renderInput() {

		$time = \MPHB\Utils\DateUtils::parseTime( $this->value );

		$selectedHour   = (int) $time['hours'];
		$selectedMinute = (int) $time['minutes'];

		$result = '<div id="' . MPHB()->addPrefix( $this->getName() ) . '">';

		// Hours
		$result .= '<label>';
		$result .= '<select name="' . esc_attr( $this->getName() . '[hours]' ) . '" ' . $this->generateAttrs() . '>';
		for ( $hour = 0; $hour <= 23; $hour++ ) {
			$hourString = str_pad( $hour, 2, '0', \STR_PAD_LEFT );
			$result    .= sprintf( '<option value="%s" ' . selected( $selectedHour, $hour, false ) . '>%s</option>', $hourString, $hourString );
		}
		$result .= '</select>';
		$result .= '</label>';

		// Minutes
		$result .= '<label>';
		$result .= '<select name="' . esc_attr( $this->getName() . '[minutes]' ) . '" ' . $this->generateAttrs() . '>';
		for ( $minute = 0; $minute <= 59; $minute++ ) {
			$minuteString = str_pad( $minute, 2, '0', \STR_PAD_LEFT );
			$result      .= sprintf( '<option value="%s" ' . selected( $selectedMinute, $minute, false ) . '>%s</option>', $minuteString, $minuteString );
		}
		$result .= '</select>';
		$result .= '</label>';

		if ( ! empty( $this->afterLabel ) ) {
			$result .= '&nbsp;' . $this->afterLabel;
		}

		$result .= '</div>';

		return $result;
	}

	public function sanitize( $values ) {
		if ( is_array( $values ) ) {
			return $values['hours'] . ':' . $values['minutes'];
		} else {
			return $this->default;
		}
	}

}
