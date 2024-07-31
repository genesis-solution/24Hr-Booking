<?php

namespace MPHB\AjaxApi;

use Throwable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Any action must not contain any business logic!
 * It just validate request data, directs validated request to the Core API,
 * gets result and send response.
 */
abstract class AbstractAjaxApiAction {

	const AJAX_ACTION_NAME_PREFIX = 'mphb_';
	const REQUEST_DATA_WP_NONCE   = 'mphb_nonce';
	const REQUEST_DATA_IS_ADMIN   = 'mphb_is_admin';
	const REQUEST_DATA_LOCALE     = 'mphb_locale';

	final public static function getAjaxActionName() {
		return self::AJAX_ACTION_NAME_PREFIX . static::getAjaxActionNameWithouPrefix();
	}

	abstract public static function getAjaxActionNameWithouPrefix();

	public static function isActionForLoggedInUser() {
		return true;
	}

	public static function isActionForGuestUser() {
		return true;
	}

	protected static function getIntegerFromRequest( string $requestDataName, bool $isRequired = false, int $defaultValue = 0 ) {

		$result = $defaultValue;

		if ( isset( $_REQUEST[ $requestDataName ] ) && '' !== $_REQUEST[ $requestDataName ] ) {

			$result = intval( wp_unslash( $_REQUEST[ $requestDataName ] ) );

		} elseif ( $isRequired ) {
			throw new \Exception( 'Required integer parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	protected static function getStringFromRequest( string $requestDataName, bool $isRequired = false, string $defaultValue = '' ) {

		$result = $defaultValue;

		if ( ! empty( $_REQUEST[ $requestDataName ] ) ) {

			$result = sanitize_text_field( wp_unslash( $_REQUEST[ $requestDataName ] ) );

		} elseif ( $isRequired ) {
			throw new \Exception( 'Required string parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	/**
	 * Date must be in string Y-m-d fromat
	 *
	 * @return DateTime or null
	 * @throws Exception when request data could not be converted to DateTime
	 */
	protected static function getDateFromRequest( string $requestDataName, bool $isRequired = false, $defaultValue = null ) {

		$result = $defaultValue;

		if ( ! empty( $_REQUEST[ $requestDataName ] ) ) {

			$stringData = sanitize_text_field( wp_unslash( $_REQUEST[ $requestDataName ] ) );
			$result     = \DateTime::createFromFormat( 'Y-m-d', $stringData );

			if ( ! $result instanceof \DateTime ) {

				throw new \Exception( 'Parameter ' . $requestDataName . ' must be a date in Y-m-d string format but (' . $stringData . ') was given.' );
			}
		} elseif ( $isRequired ) {
			throw new \Exception( 'Required DateTime parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	protected static function getBooleanFromRequest( string $requestDataName, bool $isRequired = false, bool $defaultValue = false ) {

		$result = $defaultValue;

		if ( ! empty( $_REQUEST[ $requestDataName ] ) ) {

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$result = rest_sanitize_boolean( wp_unslash( $_REQUEST[ $requestDataName ] ) );

		} elseif ( $isRequired ) {
			throw new \Exception( 'Required boolean parameter ' . $requestDataName . ' is missing in request.' );
		}

		return $result;
	}

	protected static function isValidateWPNonce(): bool {
		return true;
	}

	/**
	 * @throws Exception when validation of request parameters failed
	 */
	protected static function getValidatedRequestData() {

		$requestParameters = array();

		$wpNonce = static::getStringFromRequest( static::REQUEST_DATA_WP_NONCE, true );

		if ( static::isValidateWPNonce() && ! wp_verify_nonce( $wpNonce, static::getAjaxActionName() ) ) {

			throw new \Exception(
				__( 'Request does not pass security verification. Please refresh the page and try one more time.', 'motopress-hotel-booking' )
			);
		}

		$requestParameters[ static::REQUEST_DATA_WP_NONCE ] = $wpNonce;

		$requestParameters[ static::REQUEST_DATA_IS_ADMIN ] = static::getBooleanFromRequest( static::REQUEST_DATA_IS_ADMIN, false, false );

		$requestParameters[ static::REQUEST_DATA_LOCALE ] = static::getStringFromRequest( static::REQUEST_DATA_LOCALE );

		return $requestParameters;
	}

	final public static function processAjaxRequest() {

		$requestData = array();

		try {

			$requestData = static::getValidatedRequestData();

		} catch ( Throwable $e ) {

			error_log( $e );
			wp_send_json_error( array( 'errorMessage' => $e->getMessage() ), 400 );
		}

		try {

			if ( ! empty( $requestData[ static::REQUEST_DATA_LOCALE ] ) ) {

				MPHB()->translation()->switchLanguage( $requestData[ static::REQUEST_DATA_LOCALE ] );
			}

			static::doAction( $requestData );

		} catch ( Throwable $e ) {

			error_log( $e );
			wp_send_json_error( array( 'errorMessage' => $e->getMessage() ), 500 );
		}
	}

	/**
	 * @throws Exception when action processing failed
	 */
	abstract protected static function doAction( array $requestData );
}
