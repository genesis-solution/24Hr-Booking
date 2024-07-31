<?php

namespace MPHBW;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class ReservationProduct {

	/**
	 * Selected in settings product ID (not necessary that this is an ID for
	 * default language).
	 *
	 * @var int
	 */
	private $originalId = 0;

	/**
	 * Product ID for current language.
	 *
	 * @var int
	 */
	private $currentId = 0;

	/**
	 * All unique IDs for all languages (including original ID).
	 *
	 * @var array
	 */
	private $uniqueIds = array();

	/**
	 * WPML filters are ready for calls.
	 *
	 * @var boolean
	 */
	private $filtersReady = false;

	/**
	 * All required data loaded and no need to init it anymore.
	 *
	 * @var boolean
	 */
	private $isLoaded = false;

	public function __construct(){
		$this->originalId = (int)MPHBW()->getSettings()->main()->getProductId();

		add_action( 'init', array( $this, 'onInit' ) );
	}

	public function onInit(){
		$this->filtersReady = true;
	}

	/**
	 * Must be called not earlier than hook "init", otherwise filters like
	 * "wpml_object_id" will not return proper values.
	 */
	private function lazyLoad(){
		if ( $this->isLoaded || !$this->filtersReady ) {
			return;
		}

		$productId = (int)MPHBW()->getSettings()->main()->getProductId();

		if ( empty( $productId ) ) {
			// The product is not selected in gateway settings
			$this->isLoaded = true;
			return;
		}

		/**
		 * @var array [%Language code% => %Product ID%]. For example:
		 * ["en" => 503, "uk" => 662].
		 */
		$translatedIds = array();

		// Get list of languages
		$languages = apply_filters( 'wpml_active_languages', array() );
		$languages = wp_list_pluck( $languages, 'language_code' );

		// Get translated IDs
		foreach ( $languages as $language ) {
			$translatedIds[$language] = (int)apply_filters( 'wpml_object_id', $productId, 'product', true, $language );
		}

		// $translatedIds will be empty if WPML not installed/active
		if ( empty( $translatedIds ) ) {
			$translatedIds[] = $productId;
		}

		// Get current ID (for current language)
		$currentId = $productId;
		$currentLanguage = apply_filters( 'wpml_current_language', null );
		if ( !is_null( $currentLanguage ) && isset( $translatedIds[$currentLanguage] ) ) {
			$currentId = $translatedIds[$currentLanguage];
		}

		$this->originalId = $productId;
		$this->currentId = $currentId;
		$this->uniqueIds = array_values( array_unique( $translatedIds ) );

		$this->isLoaded = true;
	}

	public function isReservationProductId( $productId ){
		$this->lazyLoad();
		return in_array( $productId, $this->uniqueIds );
	}

	public function isSelected(){
		// No load here, constructor actions are enough for this check
		return !empty( $this->originalId );
	}

	/**
	 * Returns reservation product ID for default language.
	 *
	 * @return int
	 */
	public function getOriginalId(){
		$this->lazyLoad();
		return $this->originalId;
	}

	/**
	 * Returns reservation product ID for current language.
	 *
	 * @return int
	 */
	public function getCurrentId(){
		$this->lazyLoad();
		return $this->currentId;
	}

	public function getIds(){
		$this->lazyLoad();
		return $this->uniqueIds;
	}

}
