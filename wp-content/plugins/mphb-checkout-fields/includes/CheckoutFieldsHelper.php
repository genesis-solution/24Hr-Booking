<?php

namespace MPHB\CheckoutFields;

use MPHB\CheckoutFields\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class CheckoutFieldsHelper {

	private function __construct() {}
	public function __clone() {}
	public function __wakeup() {}


	public static function getUrlToFile( string $fileRelativePath ): string {

		return \MPHB\CheckoutFields\PLUGIN_URL . $fileRelativePath;
	}

	public static function isFieldRequired( \MPHB\CheckoutFields\Entities\CheckoutField $field ): bool {

		return $field->isRequired &&
			( ! is_admin() ||
			mphb()->settings()->main()->isCustomerRequiredOnAdmin() );
	}

	/**
	 * @param \WP_Post|int $post
	 */
	public static function isCheckoutFieldPost( $post ): bool {

		$postType = is_a( $post, '\WP_Post' ) ? $post->post_type : get_post_type( $post );

		return $postType === Plugin::getInstance()->getCheckoutFieldsPostType()->getPostType();
	}

	/**
	 * @param \WP_Post|int $post
	 */
	public static function isDefaultCheckoutFieldPost( $post ): bool {

		if ( ! self::isCheckoutFieldPost( $post ) ) {
			return false;
		}

		$postId    = is_a( $post, '\WP_Post' ) ? $post->ID : $post;
		$fieldName = get_post_meta( $postId, 'mphb_cf_name', true );

		return mphb_is_default_customer_field( $fieldName );
	}

	/**
	 * @return int[] ids of \MPHB\CheckoutFields\Entities\CheckoutField
	 */
	public static function getCheckoutFieldsIds(): array {

		$foundFields = Plugin::getInstance()->getCheckoutFieldRepository()->findAll(
			array(
				'orderby' => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
			)
		);

		$foundFieldsIds = array();

		if ( ! empty( $foundFields ) ) {

			foreach ( $foundFields as $field ) {

				$foundFieldsIds[] = $field->getId();
			}
		}

		return $foundFieldsIds;
	}

	/**
	 * @return \MPHB\CheckoutFields\Entities\CheckoutField[] [Field name => CheckoutField entity]
	 */
	public static function getEnabledCheckoutFields(): array {

		$enabledFields = Plugin::getInstance()->getCheckoutFieldRepository()->findAll(
			array(
				'orderby'    => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'meta_query' => array(
					array(
						'key'   => 'mphb_cf_enabled',
						'value' => '1',
					),
				),
			)
		);

		$fields = array();

		// old version of this plugin stored default fields to the database per each activation
		// so now we must filter duplicates from the result
		foreach ( $enabledFields as $field ) {

			// Don't show duplicate fields
			if ( ! empty( $field->name ) && ! array_key_exists( $field->name, $fields ) ) {

				$fields[ $field->name ] = $field;
			}
		}

		return $fields;
	}
}
