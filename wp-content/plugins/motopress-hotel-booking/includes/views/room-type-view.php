<?php

namespace MPHB\Views;

class RoomTypeView {

	const TEMPLATE_CONTEXT = '';

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

	public static function renderGallery() {
		$templateAtts = array(
			'galleryIds' => MPHB()->getCurrentRoomType()->getGalleryIds(),
		);
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/gallery', $templateAtts );
	}

	public static function renderBedType() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/bedType' );
	}

	public static function renderCategories() {

		$templateAtts = array(
			'categories' => MPHB()->getCurrentRoomType()->getCategories(),
		);

		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/categories', $templateAtts );
	}

	public static function renderFacilities() {
		$templateAtts = array(
			'facilities' => MPHB()->getCurrentRoomType()->getFacilities(),
		);
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/facilities', $templateAtts );
	}

	public static function renderCustomAttributes() {
		$templateAtts = array(
			'attributes' => mphb_tmpl_get_room_type_attributes(),
		);
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/custom-attributes', $templateAtts );
	}

	public static function renderView() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/view' );
	}

	public static function renderSize() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/size' );
	}

	/**
	 * @since 3.7.2
	 */
	public static function renderTotalCapacity() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/total-capacity' );
	}

	public static function renderAdults() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/adults' );
	}

	public static function renderChildren() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes/children' );
	}

	public static function renderPrice() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/price' );
	}

	/**
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 */
	public static function renderPriceForDates( \DateTime $checkInDate, \DateTime $checkOutDate ) {
		$templateAtts = array(
			'check_in_date'  => $checkInDate,
			'check_out_date' => $checkOutDate,
		);

		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/price-for-dates', $templateAtts );
	}

	public static function renderAttributes() {
		mphb_get_template_part( static::TEMPLATE_CONTEXT . '/attributes' );
	}

}
