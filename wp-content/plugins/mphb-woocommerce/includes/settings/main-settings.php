<?php

namespace MPHBW\Settings;

class MainSettings {

	/**
	 *
	 * @return int
	 */
	public function getProductId(){
		return (int) get_option( 'mphbw_product_id', 0 );
	}

	/**
	 * @since 1.0.6
	 *
	 * @return string
	 */
	public function getProductLabel(){
		$productLabel = get_option( 'mphbw_product_label_string' );
		if( empty( $productLabel ) ) {
			$productLabel = $this->getDefaultProductLabel();
		}
		return $productLabel;
	}

	/**
	 * @since 1.0.6
	 *
	 * @return string
	 */
	public function getDefaultProductLabel(){

		/**
		 * @since 1.0.6
		 */
		return apply_filters( 'mphbw_filter_default_product_label', '' );
	}

	/**
	 *
	 * @return false
	 */
	public function isUseRedirect(){
		return (bool) get_option( 'mphbw_use_redirect' );
	}

	/**
	 *
	 * @return boolean
	 */
	public function isHideIfOnlyOne(){
		return (bool) get_option( 'mphbw_hide_only_one', true );
	}

}
