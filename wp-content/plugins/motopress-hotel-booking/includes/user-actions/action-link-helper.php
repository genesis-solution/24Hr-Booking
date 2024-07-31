<?php

namespace MPHB\UserActions;

class ActionLinkHelper {

	/**
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function generateLink( $args ) {

		$url = add_query_arg( $args, site_url( 'index.php' ) );

		$token = $this->generateToken( add_query_arg( $args, site_url() ) );

		$url = add_query_arg( 'token', $token, $url );

		return $url;
	}

	/**
	 *
	 * @param string $url
	 * @return string
	 */
	private function generateToken( $url ) {

		$args = array();

		$hashAlgorithm = 'sha256';

		$args['secret'] = hash( $hashAlgorithm, wp_salt() );

		// clear token
		$args['token'] = false;

		$url = add_query_arg( $args, $url );

		$parts = parse_url( $url );
		if ( ! isset( $parts['path'] ) ) {
			$parts['path'] = '';
		}

		$token = md5( $parts['path'] . '?' . $parts['query'] );

		return $token;
	}

	/**
	 *
	 * @param array $allowedArgs
	 * @return bool
	 */
	public function isValidToken( $allowedArgs = array() ) {

		$isValidToken = false;

		$parts = parse_url( add_query_arg( array() ) );

		if ( ! isset( $parts['query'] ) ) {
			return false;
		}

		wp_parse_str( $parts['query'], $query_args );
		$url = add_query_arg( $query_args, site_url() );

		$remove = array();

		foreach ( $query_args as $key => $value ) {
			if ( false === in_array( $key, $allowedArgs ) ) {
				$remove[] = $key;
			}
		}

		if ( ! empty( $remove ) ) {
			$url = remove_query_arg( $remove, $url );
		}

		if ( isset( $query_args['token'] ) && $query_args['token'] == $this->generateToken( $url ) ) {
			$isValidToken = true;
		}

		return $isValidToken;
	}

}
