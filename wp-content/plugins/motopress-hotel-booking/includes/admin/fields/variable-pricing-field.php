<?php

namespace MPHB\Admin\Fields;

class VariablePricingField extends InputField {

	const TYPE       = 'variable-pricing';
	const MIN_PERIOD = 2; // See also MPHB.VariablePricingCtrl.MIN_PERIOD
	const MIN_PRICE  = 0;

	protected $id = '';

	public function __construct( $name, $details, $value = '' ) {
		parent::__construct( $name, $details, $value );

		$this->id      = uniqid( $this->getName() . '-' );
		$this->default = mphb_normilize_season_price( 0 );

		$this->setValue( $value );
	}

	protected function getCtrlClasses() {
		return parent::getCtrlClasses() . ' mphb-left';
	}

	public function setValue( $value ) {
		$this->value = mphb_normilize_season_price( $value );
	}

	protected function renderInput() {
		$result = '';

		// Hack for complex fields. Complex fields will create new fields from prototype and replace
		// %key_...% with a real value, which we later use to easily determine the field name (prefix
		// for new inputs, created in JS)
		$result .= '<input type="hidden" class="mphb-pricing-name-holder" name="' . esc_attr( $this->getName() ) . '" value="" disabled="disabled" />';

		$result .= $this->renderPeriods();
		$result .= $this->renderCheckbox();
		$result .= $this->renderVariations();

		return $result;
	}

	protected function renderPeriods() {
		$periods      = $this->value['periods'];
		$prices       = $this->value['prices'];
		$roomType     = $this->getDependentRoomType();
		$baseAdults   = is_null( $roomType ) ? MPHB()->settings()->main()->getMinAdults() : $roomType->getAdultsCapacity();
		$baseChildren = is_null( $roomType ) ? MPHB()->settings()->main()->getMinChildren() : $roomType->getChildrenCapacity();
		$result       = '';

		$result     .= '<table class="mphb-pricing-periods-table mphb-pricing-table widefat">';
			$result .= '<tbody>';

				// Render periods
				$result     .= '<tr>';
					$result .= '<th>&nbsp;</th>';
					$result .= '<th>' . __( 'Nights', 'motopress-hotel-booking' ) . '</th>';

		for ( $i = 0, $count = count( $periods ); $i < $count; $i++ ) {
			$result .= '<td data-period-index="' . esc_attr( $i ) . '">';
			if ( $periods[ $i ] == 1 ) {
						$result .= $this->renderPeriod( '[periods][]', 1, 'disabled="disabled"', 'mphb-keep-disabled' );
			} else {
							$result .= $this->renderPeriod( '[periods][]', $periods[ $i ] );
							$result .= '<span class="mphb-pricing-period-description">' . esc_html__( 'and more', 'motopress-hotel-booking' ) . '</span><span class="dashicons dashicons-trash mphb-pricing-action mphb-pricing-remove-period" title="' . esc_attr__( 'Remove', 'motopress-hotel-booking' ) . '"></span>';
			}
									$result .= '</td>';
		}

					$result .= '<td><span class="dashicons dashicons-plus mphb-pricing-action mphb-pricing-add-period" title="' . esc_attr__( 'Add length of stay', 'motopress-hotel-booking' ) . '"></span></td>';
				$result     .= '</tr>';

				$result     .= '<tr class="mphb-pricing-headers">';
					$result .= '<th>' . __( 'Adults', 'motopress-hotel-booking' ) . '</th>';
					$result .= '<th>' . __( 'Children', 'motopress-hotel-booking' ) . '</th>';
					$result .= '<th class="mphb-pricing-price-per-night" colspan="' . count( $periods ) . '">' . __( 'Price per night', 'motopress-hotel-booking' ) . '</th>';
					$result .= '<th>&nbsp;</th>';
				$result     .= '</tr>';

				// Render base prices
				$result     .= '<tr>';
					$result .= '<td>' . $this->renderAdults( '[adults]', $baseAdults, 'disabled="disabled"', 'mphb-keep-disabled' ) . '</td>';
					$result .= '<td>' . $this->renderChildren( '[children]', $baseChildren, 'disabled="disabled"', 'mphb-keep-disabled' ) . '</td>';

		for ( $i = 0, $count = count( $prices ); $i < $count; $i++ ) {
			$isBasePrice = ( $i == 0 );
			$result     .= '<td data-period-index="' . esc_attr( $i ) . '"' . ( $isBasePrice ? ' class="mphb-required"' : '' ) . '>';
			if ( $isBasePrice ) {
						$result .= $this->renderPrice( '[prices][]', $prices[ $i ], 'required="required"' );
			} else {
							$result .= $this->renderPrice( '[prices][]', $prices[ $i ] );
			}
									$result .= '</td>';
		}

					$result .= '<td>&nbsp;</td>';
				$result     .= '</tr>';

			$result .= '</tbody>';
		$result     .= '</table>';

		return $result;
	}

	protected function renderCheckbox() {
		$result = '';

		$result     .= '<input name="' . esc_attr( $this->getName() . '[enable_variations]' ) . '" value="0" type="hidden" />';
		$result     .= '<label class="mphb-pricing-enable-variations-label">';
			$result .= '<input name="' . esc_attr( $this->getName() . '[enable_variations]' ) . '" value="1" type="checkbox" ' . checked( true, $this->value['enable_variations'], false ) . ' class="mphb-pricing-enable-variations" />';
			$result .= ' ' . __( 'Enable variable pricing', 'motopress-hotel-booking' );
		$result     .= '</label>';

		return $result;
	}

	protected function renderVariations() {
		$result = '';

		$result .= '<input type="hidden" name="' . esc_attr( $this->getName() . '[variations]' ) . '" value="" />';
		$result .= '<table class="mphb-pricing-variations-table mphb-pricing-table widefat ' . ( ! $this->value['enable_variations'] ? 'mphb-hide' : '' ) . '">';

			$result         .= '<thead class="mphb-pricing-headers">';
				$result     .= '<th>' . __( 'Adults', 'motopress-hotel-booking' ) . '</th>';
				$result     .= '<th>' . __( 'Children', 'motopress-hotel-boking' ) . '</th>';
				$result     .= '<th class="mphb-pricing-price-per-night" colspan="' . esc_attr( count( $this->value['periods'] ) ) . '">';
					$result .= __( 'Price per night', 'motopress-hotel-booking' );
				$result     .= '</th>';
				$result     .= '<th>&nbsp;</th>';
			$result         .= '</thead>';

			// Variations list
			$result     .= '<tbody>';
				$result .= $this->generateTemplate();

		foreach ( $this->value['variations'] as $index => $variation ) {
			$result .= $this->generateVariation( $index, $variation );
		}

			$result .= '</tbody>';

			// "Add Variation" button
			$result             .= '<tfoot>';
				$result         .= '<tr>';
					$result     .= '<td colspan="' . ( count( $this->value['periods'] ) + 3 ) . '">';
						$result .= '<button type="button" class="button mphb-pricing-add-variation">' . __( 'Add Variation', 'motopress-hotel-booking' ) . '</button>';
					$result     .= '</td>';
				$result         .= '</tr>';
			$result             .= '</tfoot>';

		$result .= '</table>';

		return $result;
	}

	protected function generateTemplate() {
		$index  = '$index$';
		$prefix = '[variations][' . $index . ']';

		$result = '';

		$result     .= '<tr class="mphb-pricing-variation-template mphb-hide" data-index="' . esc_attr( $index ) . '">';
			$result .= '<td>' . $this->renderAdults( $prefix . '[adults]', '', 'disabled="disabled"' ) . '</td>';
			$result .= '<td>' . $this->renderChildren( $prefix . '[children]', '', 'disabled="disabled"' ) . '</td>';

		for ( $i = 0, $count = count( $this->value['periods'] ); $i < $count; $i++ ) {
			$priceInput = $this->renderPrice( $prefix . '[prices][]', '', 'disabled="disabled"' );
			$result    .= '<td data-period-index="' . esc_attr( $i ) . '">' . $priceInput . '</td>';
		}

			$result     .= '<td>';
				$result .= '<span class="dashicons dashicons-trash mphb-pricing-action mphb-pricing-remove-variation" title="' . esc_attr__( 'Remove variation', 'motopress-hotel-booking' ) . '"></span>';
			$result     .= '</td>';
		$result         .= '</tr>';

		return $result;
	}

	protected function generateVariation( $index, $values ) {
		$prefix = '[variations][' . $index . ']';
		$result = '';

		$result     .= '<tr data-index="' . esc_attr( $index ) . '">';
			$result .= '<td>' . $this->renderAdults( $prefix . '[adults]', $values['adults'] ) . '</td>';
			$result .= '<td>' . $this->renderChildren( $prefix . '[children]', $values['children'] ) . '</td>';

		foreach ( $values['prices'] as $i => $price ) {
			$priceInput = $this->renderPrice( $prefix . '[prices][]', $price );
			$result    .= '<td data-period-index="' . esc_attr( $i ) . '">' . $priceInput . '</td>';
		}

			$result     .= '<td>';
				$result .= '<span class="dashicons dashicons-trash mphb-pricing-action mphb-pricing-remove-variation" title="' . esc_attr__( 'Remove variation', 'motopress-hotel-booking' ) . '"></span>';
			$result     .= '</td>';
		$result         .= '</tr>';

		return $result;
	}

	protected function renderPeriod( $name, $value, $atts = '', $class = '' ) {
		return '<input type="number" name="' . esc_attr( $this->getName() . $name ) . '" class="' . esc_attr( 'small-text ' . $class ) . '" value="' . esc_attr( $value ) . '" min="' . esc_attr( self::MIN_PERIOD ) . '" step="1" ' . $atts . ' />';
	}

	protected function renderPrice( $name, $value, $atts = '', $class = '' ) {
		/**
		 * Use text field instead of number to increase the number of digits
		 * after decimal point.
		 *
		 * @see MB-639
		 */
		return '<input type="text" name="' . esc_attr( $this->getName() . $name ) . '" class="' . esc_attr( 'mphb-price-text ' . $class ) . '" value="' . esc_attr( $value ) . '" ' . $atts . ' />';
	}


	protected function renderAdults( $name, $value, $atts = '', $class = '' ) {

		$min = MPHB()->settings()->main()->getMinAdults();

		return '<input type="number" name="' . esc_attr( $this->getName() . $name ) . '" class="' . esc_attr( 'small-text ' . $class ) . '" value="' . esc_attr( $value ) . '" min="' . esc_attr( $min ) . '" step="1" ' . $atts . ' />';
	}

	protected function renderChildren( $name, $value, $atts = '', $class = '' ) {

		$min = MPHB()->settings()->main()->getMinChildren();

		return '<input type="number" name="' . esc_attr( $this->getName() . $name ) . '" class="' . esc_attr( 'small-text ' . $class ) . '" value="' . esc_attr( $value ) . '" min="' . esc_attr( $min ) . '" step="1" ' . $atts . ' />';
	}

	/**
	 * @return \MPHB\Entities\RoomType|null
	 */
	protected function getDependentRoomType() {
		$postId = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$typeId = ( $postId > 0 ) ? get_post_meta( $postId, 'mphb_room_type_id', true ) : '';

		if ( $typeId !== '' ) {
			return MPHB()->getRoomTypeRepository()->findById( $typeId );
		} else {
			return null;
		}
	}

	public function sanitize( $value ) {
		$pricing = $this->default;

		$pricing['periods']           = $this->sanitizePeriods( $value );
		$pricing['enable_variations'] = $this->sanitizeEnableVariations( $value );
		$pricing['prices']            = $this->sanitizePrices( $value );
		$pricing['variations']        = $this->sanitizeVariations( $value );

		$this->checkPricesCount( $pricing['prices'], $pricing['periods'] );

		if ( $pricing['prices'][0] === '' ) {
			$pricing['prices'][0] = self::MIN_PRICE;
		}

		foreach ( $pricing['variations'] as &$variation ) {
			$this->checkPricesCount( $variation['prices'], $pricing['periods'] );
		}
		unset( $variation );

		return $pricing;
	}

	protected function sanitizeVariations( $value ) {
		$variations = array();

		if ( ! isset( $value['variations'] ) || ! is_array( $value['variations'] ) ) {
			return $variations;
		}

		// Use array_values() to reset numeric indexes
		foreach ( array_values( $value['variations'] ) as $index => $variation ) {
			$adults   = $this->sanitizeAdults( $variation );
			$children = $this->sanitizeChildren( $variation );
			$prices   = $this->sanitizePrices( $variation );

			$variations[] = array(
				'adults'   => $adults,
				'children' => $children,
				'prices'   => $prices,
			);
		}

		return $variations;
	}

	protected function sanitizePeriods( $value ) {
		$periods = $this->default['periods']; // $periods = [1]

		if ( ! isset( $value['periods'] ) ) {
			return $periods;
		}

		foreach ( $value['periods'] as $period ) {
			$period = intval( $period );

			if ( $period >= self::MIN_PERIOD ) {
				$periods[] = $period;
			}
		}

		return $periods;
	}

	protected function sanitizePrices( $value ) {
		$prices = array();

		if ( ! isset( $value['prices'] ) ) {
			return $prices;
		}

		foreach ( $value['prices'] as $price ) {
			$price = floatval( $price );

			if ( $price >= self::MIN_PRICE ) {
				$prices[] = $price;
			} else {
				$prices[] = '';
			}
		}

		return $prices;
	}

	protected function sanitizeEnableVariations( $value ) {
		if ( isset( $value['enable_variations'] ) ) {
			return \MPHB\Utils\ValidateUtils::validateBool( $value['enable_variations'] );
		} else {
			return $this->default['enable_variations'];
		}
	}

	protected function sanitizeAdults( $value ) {
		if ( ! isset( $value['adults'] ) ) {
			return '';
		}

		$adults    = absint( $value['adults'] );
		$minAdults = MPHB()->settings()->main()->getMinAdults();
		$maxAdults = MPHB()->settings()->main()->getSearchMaxAdults();

		return max( $minAdults, min( $adults, $maxAdults ) );
	}

	protected function sanitizeChildren( $value ) {
		if ( ! isset( $value['children'] ) ) {
			return '';
		}

		$children    = absint( $value['children'] );
		$minChildren = MPHB()->settings()->main()->getMinChildren();
		$maxChildren = MPHB()->settings()->main()->getSearchMaxChildren();

		return max( $minChildren, min( $children, $maxChildren ) );
	}

	/**
	 * Makes periods array and prices array equal by length.
	 *
	 * @param array $prices
	 * @param array $periods
	 */
	protected function checkPricesCount( &$prices, $periods ) {
		$pricesCount  = count( $prices );
		$periodsCount = count( $periods );

		if ( $pricesCount > $periodsCount ) {
			$prices = array_slice( $prices, 0, $periodsCount );
		} elseif ( $pricesCount < $periodsCount ) {
			$prices = array_merge( $prices, array_fill( $pricesCount, $periodsCount - $pricesCount, '' ) );
		}
	}

}
