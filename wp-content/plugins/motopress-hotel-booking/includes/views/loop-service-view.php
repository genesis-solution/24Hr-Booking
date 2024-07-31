<?php

namespace MPHB\Views;

class LoopServiceView {

	const TEMPLATE_CONTEXT = 'loop-service';

	public static function renderTitle() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/title' );
	}

	public static function renderExcerpt() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/excerpt' );
	}

	public static function renderDescription() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/description' );
	}

	public static function renderFeaturedImage() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/featured-image' );
	}

	public static function renderPrice() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/price' );
	}

	public static function _renderFeaturedImageParagraphOpen() {
		echo '<p class="mphb-loop-service-thumbnail">';
	}

	public static function _renderFeaturedImageParagraphClose() {
		echo '</p>';
	}

	public static function _renderPriceParagraphOpen() {
		echo '<p class="mphb-price-wrapper">';
	}

	public static function _renderPriceParagraphClose() {
		echo '</p>';
	}

	public static function _renderPriceTitle() {
		echo '<strong>' . esc_html__( 'Price:', 'motopress-hotel-booking' ) . '</strong>';
	}

	public static function _renderTitleHeadingOpen() {
		echo '<h2 class="mphb-service-title">';
	}

	public static function _renderTitleHeadingClose() {
		echo '</h2>';
	}

}
