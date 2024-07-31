<?php

namespace MPHB;

use \MPHB\Libraries\WP_Meta_Query;

class Fixes {

	const FIX_META_QUERY_FLAG = 'mphb_fix_meta_query';

	public function __construct() {
		add_filter( 'posts_clauses', array( $this, 'fixOldWPMetaQuery' ), 10, 2 );

		// Jetpack lightbox conflict fix
		add_filter( 'mphb_single_room_type_gallery_use_magnific', array( $this, 'disableLightboxWhenJetpackCarousel' ) );
	}

	/**
	 * Allow old WordPress (<4.1) support complex meta query
	 *
	 * @global WPDB $wpdb
	 * @param array     $pieces
	 * @param \WP_Query $wp_query
	 * @return array
	 */
	public function fixOldWPMetaQuery( $pieces, $wp_query ) {
		global $wpdb;
		if ( $wp_query->get( self::FIX_META_QUERY_FLAG ) ) {
			$metaQuery = $wp_query->get( 'mphb_meta_query' );
			if ( ! empty( $metaQuery ) ) {

				$metaQueryObj = new WP_Meta_Query\WP_Meta_Query( $metaQuery );

				$clauses = $metaQueryObj->get_sql( 'post', $wpdb->posts, 'ID', $wp_query );

				$pieces['join']   .= $clauses['join'];
				$pieces['where']  .= $clauses['where'];
				$pieces['groupby'] = "{$wpdb->posts}.ID";
			}
		}

		return $pieces;
	}

	/**
	 *
	 * @param bool $useMagnific
	 * @return bool
	 */
	public function disableLightboxWhenJetpackCarousel( $useMagnific ) {

		if ( class_exists( 'Jetpack' ) && class_exists( 'Jetpack_Carousel' ) && in_array( 'carousel', \Jetpack::get_active_modules() ) ) {
			return false;
		}

		return $useMagnific;
	}
}
