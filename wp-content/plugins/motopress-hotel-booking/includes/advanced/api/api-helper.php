<?php
/**
 * @package MPHB\Advanced\Api
 * @since 4.1.0
 */

namespace MPHB\Advanced\Api;

class ApiHelper {

	const DATETIME_FORMAT_ISO8601 = 'Y-m-d\TH:i:s';

	/**
	 * This value will be the first in the endpoint URLs.
	 *
	 * @return string
	 */
	public static function getNamespace() {
		$version = substr( Api::VERSION, 0, 1 );

		return Api::VENDOR . '/' . 'v' . $version;
	}

	/**
	 * Get the URL to the REST API.
	 *
	 * @param  string $path  an endpoint to include in the URL.
	 *
	 * @return string the URL.
	 */
	public static function getApiUrl( string $path ) {

		$url = get_home_url( null, self::getNamespace() . '/', is_ssl() ? 'https' : 'http' );

		if ( ! empty( $path ) && is_string( $path ) ) {
			$url .= ltrim( $path, '/' );
		}

		return $url;
	}

	/**
	 * Generate a rand hash.
	 *
	 * @return string
	 */
	public static function randHash() {
		if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return sha1( wp_rand() );
		}

		return bin2hex( openssl_random_pseudo_bytes( 20 ) ); // @codingStandardsIgnoreLine
	}

	/**
	 * API - Hash.
	 *
	 * @param  string $data  Message to be hashed.
	 *
	 * @return string
	 */
	public static function apiHash( string $data ) {
		return hash_hmac( 'sha256', $data, 'mphb' );
	}


	/**
	 * Check permissions of posts on REST API.
	 *
	 * @param  string $postType  Post type.
	 * @param  string $context  Request context.
	 * @param  int    $objectId  Post ID.
	 *
	 * @return bool
	 */
	public static function checkPostPermissions( $postType, $context = 'read', $objectId = 0 ) {
		$contexts = array(
			'read'   => 'read_private_posts',
			'create' => 'publish_posts',
			'edit'   => 'edit_post',
			'delete' => 'delete_post',
			'batch'  => 'edit_others_posts',
		);

		if ( 'revision' === $postType ) {
			$permission = false;
		} else {
			$cap            = $contexts[ $context ];
			$postTypeObject = get_post_type_object( $postType );
			$permission     = current_user_can( $postTypeObject->cap->$cap, $objectId );
		}

		return apply_filters( 'mphb_rest_check_permissions', $permission, $context, $objectId, $postType );
	}

	/**
	 * Check permissions of terms on REST API.
	 *
	 * @param string $taxonomy  Taxonomy.
	 * @param string $context   Request context.
	 * @param int    $object_id Post ID.
	 * @return bool
	 */
	public static function checkTermPermissions( $taxonomy, $context = 'read', $objectId = 0 ) {
		$contexts = array(
			'read'   => 'manage_terms',
			'create' => 'edit_terms',
			'edit'   => 'edit_terms',
			'delete' => 'delete_terms',
			'batch'  => 'edit_terms',
		);

		$cap            = $contexts[ $context ];
		$taxonomyObject = get_taxonomy( $taxonomy );
		$permission     = current_user_can( $taxonomyObject->cap->$cap, $objectId );

		return apply_filters( 'mphb_rest_check_permissions', $permission, $context, $objectId, $taxonomy );
	}

	/**
	 * Encodes a value according to RFC 3986.
	 * Supports multidimensional arrays.
	 *
	 * @param  string|array $value  The value to encode.
	 *
	 * @return string|array       Encoded values.
	 */
	public static function urlencodeRfc3986( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'self::urlencodeRfc3986', $value );
		}

		return str_replace( array( '+', '%7E' ), array( ' ', '~' ), rawurlencode( $value ) );
	}

	/**
	 * @param  \DateTime                                      $dateTime
	 * @param  string PHP timezone string or a ±HH:MM offset., by default UTC
	 *
	 * @return string formatted datetime in ISO8601
	 */
	public static function prepareDateTimeResponse( \DateTime $dateTime, $timezoneString = 'UTC' ) {
		$timezone = new \DateTimeZone( $timezoneString );

		return $dateTime->setTimezone( $timezone )->format( self::DATETIME_FORMAT_ISO8601 );
	}

	/**
	 * @param  \DateTime|null $dateTime
	 *
	 * @return string|null formatted date in ISO8601
	 */
	public static function prepareDateResponse( $dateTime ) {
		if ( ! is_a( $dateTime, 'DateTime' ) ) {
			return null;
		}
		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

		return $dateTime->format( $dateFormat );
	}

	/**
	 * @param  string $dateString  Y-m-d formatted datetime
	 *
	 * @return \DateTime  $dateTime
	 * @throws \Exception
	 */
	public static function prepareDateRequest( string $dateString ) {
		$dateFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();
		$dateTime   = \DateTime::createFromFormat( $dateFormat, $dateString, wp_timezone() );

		if ( false === $dateTime ) {
			throw new \Exception( sprintf( 'Invalid date format. Expected: %s', $dateFormat ) );
		}

		return $dateTime;
	}

	/**
	 * @param  string $snakeString
	 *
	 * @return string
	 */
	public static function convertSnakeToCamelString( string $snakeString ) {
		return str_replace( ' ', '', ucwords( str_replace( '_', ' ', $snakeString ) ) );
	}
}
