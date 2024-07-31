<?php

namespace MPHB\Admin\Fields;

class RulesListField extends InputField {

	const TYPE = 'rules-list';

	protected $addLabel     = '';
	protected $emptyLabel   = '';
	protected $editLabel    = '';
	protected $doneLabel    = '';
	protected $deleteLabel  = '';
	protected $actionsLabel = '';

	protected $allText  = '';
	protected $noneText = '';

	protected $isSortable = false;

	/**
	 *
	 * @var \MPHB\Admin\Fields\InputField[]
	 */
	protected $fields = array();

	protected $names = array();

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		$this->isSortable = isset( $details['sortable'] ) ? $details['sortable'] : $this->isSortable;

		$this->addLabel     = isset( $details['add_label'] ) ? $details['add_label'] : __( 'Add', 'motopress-hotel-booking' );
		$this->emptyLabel   = isset( $details['empty_label'] ) ? $details['empty_label'] : $this->emptyLabel;
		$this->editLabel    = isset( $details['edit_label'] ) ? $details['edit_label'] : __( 'Edit', 'motopress-hotel-booking' );
		$this->doneLabel    = isset( $details['done_label'] ) ? $details['done_label'] : __( 'Done', 'motopress-hotel-booking' );
		$this->deleteLabel  = isset( $details['delete_label'] ) ? $details['delete_label'] : __( 'Delete', 'motopress-hotel-booking' );
		$this->actionsLabel = isset( $details['actions_label'] ) ? $details['actions_label'] : __( 'Actions', 'motopress-hotel-booking' );

		$this->allText  = isset( $details['all_text'] ) ? $details['all_text'] : __( 'All', 'motopress-hotel-booking' );
		$this->noneText = isset( $details['none_text'] ) ? $details['none_text'] : __( 'None', 'motopress-hotel-booking' );

		if ( is_array( $details['fields'] ) ) {
			foreach ( $details['fields'] as $field ) {
				if ( is_a( $field, '\MPHB\Admin\Fields\InputField' ) ) {
					$this->fields[] = $field;
					$this->names[]  = $field->getName();
				}
			}
		}
	}

	protected function getCtrlAtts() {
		$atts = parent::getCtrlAtts();
		return $atts . ' data-group="' . $this->getName() . '"';
	}

	protected function renderInput() {
		if ( $this->isSortable ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		$hasItems = ! empty( $this->value );

		$result = '';

		$result .= '<h3>';
		$result .= esc_html( $this->label );
		$result .= '<a class="add-new-h2 mphb-rules-list-add-button">' . esc_html( $this->addLabel ) . '</a>';
		$result .= '</h3>';

		if ( ! empty( $this->emptyLabel ) ) {
			$emptyClass = 'mphb-rules-list-empty-message' . ( $hasItems ? ' mphb-hide' : '' );
			$result    .= '<p class="' . $emptyClass . '">' . esc_html( $this->emptyLabel ) . '</p>';
		}

		$result .= '<table class="widefat striped' . ( $hasItems ? '' : ' mphb-hide' ) . '">';

		$result .= '<thead>';
		$result .= '<tr>';
		foreach ( $this->fields as $field ) {
			$result .= '<th class="row-title">' . wp_kses(
				$field->getLabel(),
				array(
					'span' => array(
						'class'    => array(),
						'data-tip' => array(),
					),
				)
			) . '</th>';
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

	protected function renderActionsHead() {
		return '<th class="row-title">' . esc_html( $this->actionsLabel ) . '</th>';
	}

	protected function renderActions( $editText ) {
		$result = '';

		$result .= '<td>';
		$result .= '<button type="button" class="button mphb-rules-list-edit-button">' . esc_html( $editText ) . '</button>';
		$result .= ' ';
		$result .= '<button type="button" class="button mphb-rules-list-delete-button">' . esc_html( $this->deleteLabel ) . '</button>';
		$result .= '</td>';

		return $result;
	}

	protected function renderPrototype() {
		$result = '';

		$result .= '<tr data-id="$index$" class="mphb-rules-list-prototype mphb-hide">';

		foreach ( $this->fields as $field ) {
			$field = clone $field;
			$field->setValue( $field->getDefault() );
			$field->setName( $this->getName() . '[$index$][' . $field->getName() . ']' );
			$field->setDisabled( true );

			$this->fixDependencies( $field, '$index$', array() );

			$result .= '<td>';
			$result .= '<div class="mphb-rules-list-rendered-value"></div>';
			$result .= $field->render();
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
				$field = clone $field;
				$field->setValue( $values[ $field->getName() ] );
				$field->setName( $this->getName() . '[' . $order . '][' . $field->getName() . ']' );

				$this->fixDependencies( $field, $order, $values );

				$result .= '<td>';
				$result .= '<div class="mphb-rules-list-rendered-value">';
				$result .= $this->renderValue( $field );
				$result .= '</div>';
				$result .= $field->render();
				$result .= '</td>';
			}

			$result .= $this->renderActions( $this->editLabel );

			$result .= '</tr>';
		}

		return $result;
	}

	protected function fixDependencies( $field, $rowIndex, $rowValues ) {
		// Change dependency input name and the list or variants (only if the
		// dependency input is also in this complex field)
		if ( $field instanceof DependentField ) { // "dynamic-select", "amount"
			$dependencyInput = $field->getDependencyInput();

			if ( in_array( $dependencyInput, $this->names ) ) {
				$field->setDependencyInput( $this->getName() . '[' . $rowIndex . '][' . $dependencyInput . ']' );

				if ( isset( $rowValues[ $dependencyInput ] ) ) {
					$field->updateDependency( $rowValues[ $dependencyInput ] );
				}
			}
		}
	}

	/**
	 *
	 * @param \MPHB\Admin\Fields\InputField $field
	 */
	protected function renderValue( $field ) {
		$type  = $field->getType();
		$value = $field->getValue();

		$result = '';

		switch ( $type ) {
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

			case SingleCheckboxField::TYPE:
				$result = SingleCheckboxField::renderValue( $field );
				break;

			// TextField, TextareaField, NumberField, SelectField, AmountField, PlaceholderField
			default:
				$class = '\MPHB\Admin\Fields\\' . ucfirst( $type ) . 'Field';
				if ( method_exists( $class, 'renderValue' ) ) {
					$result = $class::renderValue( $field );
				}
				break;
		}

		return $result;
	}

	public function sanitize( $values ) {
		if ( ! is_array( $values ) ) {
			$values = $this->default;
		} else {
			$values = array_values( $values ); // Reset numeric indexes
			foreach ( $values as $key => &$value ) {
				foreach ( $this->fields as $field ) {
					$valueToSanitize            = ( isset( $value[ $field->getName() ] ) ? $value[ $field->getName() ] : $field->getDefault() );
					$value[ $field->getName() ] = $field->sanitize( $valueToSanitize );
				}
			}
			unset( $value );
		}
		return $values;
	}
}
