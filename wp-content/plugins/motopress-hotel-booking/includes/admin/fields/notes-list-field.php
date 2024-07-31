<?php

namespace MPHB\Admin\Fields;

use \MPHB\Admin\Fields;

/**
 * @since 3.9.3
 */
class NotesListField extends RulesListField {

	const TYPE = 'notes-list';

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		if ( is_array( $details['fields'] ) ) {

			$this->fields[] = Fields\FieldFactory::create(
				'date',
				array(
					'type'    => 'text',
					'size'    => 'small',
					'default' => time(),
					'label'   => __( 'Date', 'motopress-hotel-booking' ),
				)
			);

			$this->fields[] = Fields\FieldFactory::create(
				'user',
				array(
					'type'    => 'text',
					'default' => get_current_user_id(),
					'size'    => 'small',
					'label'   => __( 'Author', 'motopress-hotel-booking' ),
				)
			);
		}
	}

	protected function renderInput() {
		if ( $this->isSortable ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		$hasItems = ! empty( $this->value );

		$result = '';

		$result .= '<h3>';
		$result .= esc_html( $this->label );
		$result .= '<a class="add-new-h2 mphb-notes-list-add-button">' . esc_html( $this->addLabel ) . '</a>';
		$result .= '</h3>';

		if ( ! empty( $this->emptyLabel ) ) {
			$emptyClass = 'mphb-notes-list-empty-message' . ( $hasItems ? ' mphb-hide' : '' );
			$result    .= '<p class="' . $emptyClass . '">' . esc_html( $this->emptyLabel ) . '</p>';
		}

		$result .= '<table class="widefat striped' . ( $hasItems ? '' : ' mphb-hide' ) . '">';

		$result .= '<thead>';
		$result .= '<tr>';
		foreach ( $this->fields as $field ) {
			$result .= '<th class="row-title">' . esc_html( $field->getLabel() ) . '</th>';
		}
		$result .= $this->renderActionsHead();
		$result .= '</tr>';
		$result .= '</thead>';

		$result .= '<tbody class="' . ( $this->isSortable ? 'mphb-sortable' : '' ) . '">';
		$result .= $this->renderPrototype();
		$result .= $this->renderRules();
		$result .= '</tbody>';

		$result .= '</table>';

		return $result;
	}

	protected function renderActions( $editText ) {
		$result = '';

		$result .= '<td>';
		$result .= '<button type="button" class="button mphb-notes-list-edit-button">' . esc_html( $editText ) . '</button>';
		$result .= ' ';
		$result .= '<button type="button" class="button mphb-notes-list-delete-button">' . esc_html( $this->deleteLabel ) . '</button>';
		$result .= '</td>';

		return $result;
	}

	protected function renderPrototype() {
		$result = '';

		$result .= '<tr data-id="$index$" class="mphb-notes-list-prototype mphb-hide">';

		foreach ( $this->fields as $field ) {
			$field     = clone $field;
			$fieldName = $field->getName();
			$field->setValue( $field->getDefault() );
			$field->setName( $this->getName() . '[$index$][' . $field->getName() . ']' );
			$field->setDisabled( true );

			$this->fixDependencies( $field, '$index$', array() );

			$result .= '<td>';
			$result .= '<div class="mphb-notes-list-rendered-value"></div>';
			$result .= $this->renderField( $field, $fieldName );
			$result .= '</td>';
		}

		$result .= $this->renderActions( $this->doneLabel );

		$result .= '</tr>';

		return $result;
	}

	protected function renderRules() {
		$result = '';

		foreach ( $this->value as $order => $values ) {
			$result .= '<tr data-id="' . $order . '">';

			foreach ( $this->fields as $field ) {
				$field     = clone $field;
				$fieldName = $field->getName();
				$field->setValue( $values[ $field->getName() ] );

				$field->setName( $this->getName() . '[' . $order . '][' . $field->getName() . ']' );

				$this->fixDependencies( $field, $order, $values );

				$result .= '<td>';
				$result .= '<div class="mphb-notes-list-rendered-value">';
				$result .= $this->renderValue( $field, $fieldName );
				$result .= '</div>';
				$result .= $this->renderField( $field, $fieldName );
				$result .= '</td>';
			}

			$result .= $this->renderActions( $this->editLabel );

			$result .= '</tr>';
		}

		return $result;
	}

	protected function renderValue( $field, $name = '' ) {
		$type  = $field->getType();
		$value = $field->getValue();

		$result = '';

		switch ( $type ) {
			case TextField::TYPE:
				if ( $name == 'user' ) {
					$result = $this->renderUserValue( $field );
				} elseif ( $name == 'date' ) {
					$result = $this->renderDatePickerValue( $field );
				} else {
					$result = TextField::renderValue( $field );
				}
				break;

			case DatePickerField::TYPE:
				$result = DatePickerField::renderValue( $field );
				break;

			case DynamicSelectField::TYPE:
				$result = DynamicSelectField::renderValue( $field );
				break;

			case MultipleCheckboxField::TYPE:
				$valueAll = $field->getAllValue();

				if ( ! is_null( $valueAll ) && in_array( $valueAll, $value ) ) {
					$result = $this->allText;
				} else {
					$list = $field->getList();

					if ( empty( $value ) ) {
						$result = $this->noneText;
					} elseif ( count( $value ) == count( $list ) ) {
						$result = $this->allText;
					} else {
						$result = MultipleCheckboxField::renderValue( $field );
					}
				}
				break;

			// TextareaField, NumberField, SelectField, AmountField, PlaceholderField
			default:
				$class = '\MPHB\Admin\Fields\\' . ucfirst( $type ) . 'Field';
				if ( method_exists( $class, 'renderValue' ) ) {
					$result = $class::renderValue( $field );
				}
				break;
		}

		return $result;
	}

	protected function renderField( $field, $name ) {
		switch ( $name ) {
			case 'user':
				return $this->renderUserField( $field );
				break;
			case 'date':
				return $this->renderDateField( $field );
				break;
			default:
				return $field->render();
				break;
		}
	}

	protected function renderDateField( $field ) {

		ob_start();

		echo '<div class="mphb-ctrl-wrapper ' . esc_attr( $field->getCtrlClasses() ) . '" data-type="timestamp">';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->renderDateInput( $field );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field->getInnerLabelTag();

		echo '</div>';

		$result = ob_get_contents();

		ob_end_clean();

		return $result;

	}

	protected function renderUserField( $field ) {
		ob_start();

		echo '<div class="mphb-ctrl-wrapper ' . esc_attr( $field->getCtrlClasses() ) . '" data-type="username">';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->renderUserInput( $field );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $field->getInnerLabelTag();

		echo '</div>';

		$result = ob_get_contents();

		ob_end_clean();

		return $result;
	}

	protected function renderDateInput( $field ) {
		$result  = '<span class="mphb-ctrl-date-val">' . wp_date( get_option( 'date_format' ), $field->getValue() ) . '</span>';
		$result .= '<input name="' . esc_attr( $field->getName() ) . '" value="' . esc_attr( $field->getValue() ) . '" id="' . MPHB()->addPrefix( $field->getName() ) . '" type="hidden" />';

		return $result;
	}

	protected function renderUserInput( $field ) {
		$displayName = '';
		$value       = ! empty( $field->value ) ? (int) $field->value : get_current_user_id();
		$user        = get_user_by( 'id', $value );
		$displayName = $user ? $user->display_name : '';

		$result  = '<input name="' . esc_attr( $field->getName() ) . '" value="' . esc_attr( $field->value ) . '" id="' . MPHB()->addPrefix( $field->getName() ) . '" type="hidden" />';
		$result .= '<span class="mphb-ctrl-user-name">' . $displayName . '</span>';

		return $result;
	}

	protected function renderDatePickerValue( $field ) {
		return wp_date( get_option( 'date_format' ), $field->getValue() );
	}

	protected function renderUserValue( $field ) {
		if ( ! empty( $field->value ) ) {
			$user = get_user_by( 'id', $field->value );
			return $user ? $user->display_name : '';
		}

		return '';
	}

}


