<?php

namespace MPHB\Views;

class SingleServiceView {

	const TEMPLATE_CONTEXT = 'single-service';

	public static function renderPrice() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/price' );
	}

	public static function _renderMetas() {
		self::renderPrice();
	}

	public static function _renderPriceTitle() {
		echo '<h2 class="mphb-price-title">' . esc_html__( 'Price', 'motopress-hotel-booking' ) . '</h2>';
	}

	public static function _renderPriceParagraphOpen() {
		echo '<p class="mphb-price-wrapper">';
	}

	public static function _renderPriceParagraphClose() {
		echo '</p>';
	}
}
