<?php


/* ---------------------------------------------------------------------- */
/*	Overwrite catalog ordering
/* ---------------------------------------------------------------------- */


function milenia_overwrite_catalog_ordering( $args ) {

	global $milenia_config;

	$keys = array( 'product_order', 'product_count' );
	if ( empty( $milenia_config['woocommerce'] ) ) {
		$milenia_config['woocommerce'] = array();
	}

	foreach ( $keys as $key ) {
		if ( isset( $_GET[ $key ] ) ) {
			$_SESSION['Milenia_WooCommerce'][ $key ] = esc_attr( $_GET[ $key ] );
		}
		if ( isset( $_SESSION['Milenia_WooCommerce'][ $key ] ) ) {
			$milenia_config['woocommerce'][ $key ] = $_SESSION['Milenia_WooCommerce'][ $key ];
		}
	}

	extract( $milenia_config['woocommerce'] );

	if ( isset( $product_order ) && ! empty( $product_order ) ) {
		switch ( $product_order ) {
			case 'date'  :
				$orderby  = 'date';
				$order    = 'desc';
				$meta_key = '';
				break;
			case 'price' :
				$orderby  = 'meta_value_num';
				$order    = 'asc';
				$meta_key = '_price';
				break;
			case 'popularity' :
				$orderby  = 'meta_value_num';
				$order    = 'desc';
				$meta_key = 'total_sales';
				break;
			case 'title' :
				$orderby  = 'title';
				$order    = 'asc';
				$meta_key = '';
				break;
			case 'default':
			default :
				$orderby  = 'menu_order title';
				$order    = 'asc';
				$meta_key = '';
				break;
		}
	}

	if ( isset( $orderby ) ) {
		$args['orderby'] = $orderby;
	}
	if ( isset( $order ) ) {
		$args['order'] = $order;
	}

	if ( ! empty( $meta_key ) ) {
		$args['meta_key'] = $meta_key;
	}

	return $args;
}

add_action( 'woocommerce_get_catalog_ordering_args', 'milenia_overwrite_catalog_ordering' );
