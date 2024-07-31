<?php

namespace MPHB\Admin\Fields;

class TotalPriceField extends NumberField {

	const TYPE = 'total-price';

	protected $step      = 0.01;
	protected $min       = 0;
	protected $inputType = 'number';

	public function renderInput() {
		// [MB-684] Prevent excess number of digits
		$this->value = round( $this->value, MPHB()->settings()->currency()->getPriceDecimalsCount() );
		$result      = parent::renderInput();
		$result     .= '<span class="description">' . MPHB()->settings()->currency()->getCurrencySymbol() . '</span>';
		$result     .= ' <button type="button" id="mphb-recalculate-total-price" class="button button-secondary">' . __( 'Recalculate Total Price', 'motopress-hotel-booking' ) . '</button>';
		$result     .= '<span class="mphb-preloader mphb-hide"></span>';
		$result     .= '<div class="mphb-errors-wrapper mphb-hide"></div>';
		return $result;
	}
}
