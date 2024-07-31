<?php

namespace MPHB\Admin\Fields;

class FieldFactory {

	/**
	 * @param string $name
	 * @param array  $details
	 * @param mixed  $value
	 * @return \MPHB\Admin\Fields\InputField
	 *
	 * @since 3.7.0 added new filter - "mphb_create_{$type}_field".
	 */
	public static function create( $name, $details, $value = null ) {

		switch ( $details['type'] ) {
			case 'text':
				return new TextField( $name, $details, $value );
				break;
			case 'number':
				return new NumberField( $name, $details, $value );
				break;
			case 'email':
				return new EmailField( $name, $details, $value );
				break;
			case 'textarea':
				return new TextareaField( $name, $details, $value );
				break;
			case 'rich-editor':
				return new RichEditorField( $name, $details, $value );
				break;
			case 'radio':
				return new RadioField( $name, $details, $value );
				break;
			case 'select':
				return new SelectField( $name, $details, $value );
				break;
			case 'page-select':
				return new PageSelectField( $name, $details, $value );
				break;
			case 'dynamic-select':
				return new DynamicSelectField( $name, $details, $value );
				break;
			case 'multiple-select':
				return new MultipleSelectField( $name, $details, $value );
				break;
			case 'media':
				return new MediaField( $name, $details, $value );
				break;
			case 'datepicker':
				return new DatePickerField( $name, $details, $value );
				break;
			case 'timepicker':
				return new TimePickerField( $name, $details, $value );
				break;
			case 'complex':
				return new ComplexHorizontalField( $name, $details, $value );
				break;
			case 'complex-vertical':
				return new ComplexVerticalField( $name, $details, $value );
				break;
			case 'total-price':
				return new TotalPriceField( $name, $details, $value );
				break;
			case 'price-breakdown':
				return new PriceBreakdownField( $name, $details, $value );
				break;
			case 'amount':
				return new AmountField( $name, $details, $value );
				break;
			case 'service-chooser':
				return new ServiceChooserField( $name, $details, $value );
				break;
			case 'checkbox':
				return new CheckboxField( $name, $details, $value );
				break;
			case 'single-checkbox':
				return new SingleCheckboxField( $name, $details, $value );
				break;
			case 'multiple-checkbox':
				return new MultipleCheckboxField( $name, $details, $value );
				break;
			case 'color-picker':
				return new ColorPickerField( $name, $details, $value );
				break;
			case 'post-id':
				return new PostIdField( $name, $details, $value );
				break;
			case 'placeholder':
				return new PlaceholderField( $name, $details, $value );
				break;
			case 'rules-list':
				return new RulesListField( $name, $details, $value );
				break;
			case 'notes-list':
				return new NotesListField( $name, $details, $value );
				break;
			case 'variable-pricing':
				return new VariablePricingField( $name, $details, $value );
				break;
			case 'action-button':
				return new ActionButtonField( $name, $details, $value );
				break;
			case 'link-button':
				return new LinkButtonField( $name, $details, $value );
				break;
			case 'install-plugin':
				return new InstallPluginField( $name, $details, $value );
				break;
			default:
				$type = str_replace( '-', '_', $details['type'] );
				return apply_filters( "mphb_create_{$type}_field", null, $name, $details, $value );
				break;
		}

		return null;
	}

}
