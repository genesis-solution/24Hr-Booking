<?php

if ( ! function_exists( 'mb_convert_encoding' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 3.7.1
	 */
	function mb_convert_encoding( $s, $toEncoding, $fromEncoding = null ) {
		if ( is_array( $fromEncoding ) || strpos( $fromEncoding, ',' ) !== false ) {
			$fromEncoding = mb_detect_encoding( $s, $fromEncoding );
		} else {
			$fromEncoding = mb_validate_encoding( $fromEncoding );
		}

		$toEncoding = mb_validate_encoding( $toEncoding );

		if ( $fromEncoding == 'BASE64' ) {
			$s            = base64_decode( $s );
			$fromEncoding = $toEncoding;
		}

		if ( $toEncoding == 'BASE64' ) {
			return base64_encode( $s );
		}

		if ( $toEncoding == 'HTML-ENTITIES' || $toEncoding == 'HTML' ) {
			if ( $fromEncoding == 'HTML-ENTITIES' || $fromEncoding == 'HTML' ) {
				$fromEncoding = 'Windows-1252';
			}

			if ( $fromEncoding != 'UTF-8' ) {
				$s = iconv( $fromEncoding, 'UTF-8//IGNORE', $s );
			}

			return preg_replace_callback( '/[\x80-\xFF]+/', 'mb_convert_encoding_callback', $s );
		}

		if ( $fromEncoding == 'HTML-ENTITIES' ) {
			$s            = html_entity_decode( $s, ENT_COMPAT, 'UTF-8' );
			$fromEncoding = 'UTF-8';
		}

		return iconv( $fromEncoding, $toEncoding . '//IGNORE', $s );
	}
}

if ( ! function_exists( 'mb_convert_encoding_callback' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 3.7.1
	 */
	function mb_convert_encoding_callback( $m ) {
		$i        = 1;
		$entities = '';
		$m        = unpack( 'C*', htmlentities( $m[0], ENT_COMPAT, 'UTF-8' ) );

		while ( isset( $m[ $i ] ) ) {
			if ( $m[ $i ] < 0x80 ) {
				$entities .= chr( $m[ $i++ ] );
				continue;
			}

			if ( $m[ $i ] >= 0xF0 ) {
				$c = ( ( $m[ $i++ ] - 0xF0 ) << 18 ) + ( ( $m[ $i++ ] - 0x80 ) << 12 ) + ( ( $m[ $i++ ] - 0x80 ) << 6 ) + $m[ $i++ ] - 0x80;
			} elseif ( $m[ $i ] >= 0xE0 ) {
				$c = ( ( $m[ $i++ ] - 0xE0 ) << 12 ) + ( ( $m[ $i++ ] - 0x80 ) << 6 ) + $m[ $i++ ] - 0x80;
			} else {
				$c = ( ( $m[ $i++ ] - 0xC0 ) << 6 ) + $m[ $i++ ] - 0x80;
			}

			$entities .= '&#' . $c . ';';
		}

		return $entities;
	}
}

if ( ! function_exists( 'mb_detect_encoding' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 3.7.1
	 */
	function mb_detect_encoding( $s, $encodings = null, $strict = false ) {
		if ( is_null( $encodings ) ) {
			$encodings = array( 'ASCII', 'UTF-8' );
		} else {
			if ( ! is_array( $encodings ) ) {
				$encodings = array_map( 'trim', explode( ',', $encodings ) );
			}

			$encodings = array_map( 'strtoupper', $encodings );
		}

		foreach ( $encodings as $encoding ) {
			switch ( $encoding ) {
				case 'ASCII':
					if ( ! preg_match( '/[\x80-\xFF]/', $s ) ) {
						return $encoding;
					}
					break;

				case 'UTF8':
				case 'UTF-8':
					if ( preg_match( '//u', $s ) ) {
						return 'UTF-8';
					}
					break;

				default:
					if ( strncmp( $encoding, 'ISO-8859-', 9 ) == 0 ) {
						return $encoding;
					}
					break;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'mb_validate_encoding' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 3.7.1
	 */
	function mb_validate_encoding( $encoding ) {
		if ( is_null( $encoding ) ) {
			return 'UTF-8';
		}

		$encoding = strtoupper( $encoding );

		if ( $encoding == '8BIT' || $encoding == 'BINARY' ) {
			$encoding = 'CP850';
		} elseif ( $encoding == 'UTF8' ) {
			$encoding = 'UTF-8';
		}

		return $encoding;
	}
}
