<?php

namespace MPHB\Admin\Fields;

class PriceBreakdownField extends InputField {

	const TYPE = 'price-breakdown';

	public function renderInput() {

		$result = '';

		// Input for recalculated price breakdown
		$result .= '<input type="hidden" name="' . esc_attr( $this->getName() ) . '" value="" disabled="disabled" />';

		// Render price breakdown
		$result .= '<div class="mphb-price-breakdown-wrapper">';

		$priceBreakdown = json_decode( mphb_strip_price_breakdown_json( $this->value ), true );

		if ( is_array( $priceBreakdown ) ) {
			$result .= \MPHB\Views\BookingView::generatePriceBreakdownArray( $priceBreakdown );
		}

		$result .= '</div>';

		return $result;
	}
}
