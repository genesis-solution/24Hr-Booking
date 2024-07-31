<?php

namespace MPHB\Notifier\Admin\Fields;

use MPHB\Admin\Fields\InputField;
use MPHB\Utils\ValidateUtils;

/**
 * @since 1.0
 */
class TriggerDateField extends InputField {

	const TYPE       = 'trigger-date';
	const MAX_PERIOD = 365;

	protected $default = array(
		'period'  => 1,
		'unit'    => 'day',
		'compare' => 'before',
		'field'   => 'check-in',
	);

	public function __construct( $name, $args, $value = '' ) {

		if ( isset( $args['default'] ) ) {

			if ( is_array( $args['default'] ) ) {

				$this->default = array_merge( $this->default, $args['default'] );
			}

			unset( $args['default'] );
		}

		parent::__construct( $name, $args, $value );
	}

	protected function renderInput() {

		$name = $this->getName();
		$id   = mphb()->addPrefix( $name );

		$period  = $this->value['period'];
		$compare = $this->value['compare'];
		$field   = $this->value['field'];

		$comparisons = mphb_notifier_get_trigger_comparisons();
		$fields      = mphb_notifier_get_trigger_fields();

		$output      = '<fieldset>';
			$output .= '<input id="' . esc_attr( $id . '-period' ) . '" name="' . esc_attr( $name . '[period]' ) . '" type="number" value="' . esc_attr( $period ) . '" min="1" max="' . self::MAX_PERIOD . '" step="1" />';

			$output .= '<input id="' . esc_attr( $id . '-unit' ) . '" name="' . esc_attr( $name . '[unit]' ) . '" type="hidden" value="day" />';
			$output .= '&nbsp;' . esc_html_x( 'days', 'X days before some date', 'mphb-notifier' ) . '&nbsp;';

			$output     .= '<select id="' . esc_attr( $id . '-compare' ) . '" name="' . esc_attr( $name . '[compare]' ) . '">';
				$output .= mphb_notifier_tmpl_render_select_options( $comparisons, $compare );
			$output     .= '</select>';

			$output     .= '<select id="' . esc_attr( $id . '-field' ) . '" name="' . esc_attr( $name . '[field]' ) . '">';
				$output .= mphb_notifier_tmpl_render_select_options( $fields, $field );
			$output     .= '</select>';
		$output         .= '</fieldset>';

		return $output;
	}

	public function sanitize( $value ) {
		if ( ! is_array( $value ) ) {
			return $this->default;
		} else {
			$period = isset( $value['period'] ) ? ValidateUtils::parseInt( $value['period'], 1, self::MAX_PERIOD ) : 1;

			$compare = isset( $value['compare'] ) ? sanitize_text_field( $value['compare'] ) : 'before';
			$compare = in_array( $compare, array_keys( mphb_notifier_get_trigger_comparisons() ) ) ? $compare : 'before';

			$field = isset( $value['field'] ) ? sanitize_text_field( $value['field'] ) : 'check-in';
			$field = in_array( $field, array_keys( mphb_notifier_get_trigger_fields() ) ) ? $field : 'check-in';

			return array(
				'period'  => $period,
				'unit'    => 'day',
				'compare' => $compare,
				'field'   => $field,
			);
		}
	}
}
