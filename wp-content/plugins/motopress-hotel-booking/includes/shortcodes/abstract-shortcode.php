<?php

namespace MPHB\Shortcodes;

abstract class AbstractShortcode {

	protected $name;

	public function __construct() {
		$this->addActions();
	}

	public function addActions() {
		add_action( 'init', array( $this, 'register' ), 5 );
	}

	public function register() {
		add_shortcode( $this->name, array( $this, 'render' ) );
	}

	abstract public function render( $atts, $content, $shortcodeName );

	/**
	 *
	 * @param array $attrs Attributes of shortcode
	 * @return string
	 */
	public function generateShortcode( $attrs = array() ) {
		$shortcode = '[' . $this->name;
		foreach ( $attrs as $attrName => $attrValue ) {
			$shortcode .= sprintf( ' %s="%s"', $attrName, $attrValue );
		}
		$shortcode .= ']';

		return $shortcode;
	}

	/**
	 * @param array $atts Shortcode attributes.
	 * @param array $defaults Default values of "orderby" and "order" attributes.
	 *
	 * @return array
	 *
	 * @see https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters
	 */
	protected function buildOrderQuery( $atts, $defaults ) {
		$atts = array_merge(
			array(
				'orderby'   => 'date',
				'order'     => 'DESC',
				'meta_key'  => '',
				'meta_type' => '',
			),
			$atts
		);

		$orderby        = mphb_clean( $atts['orderby'] );
		$order          = \MPHB\Utils\ValidateUtils::validateOrder( $atts['order'] );
		$metaKey        = mphb_clean( $atts['meta_key'] );
		$metaType       = strtoupper( mphb_clean( $atts['meta_type'] ) );
		$isSearchByMeta = ( strpos( $orderby, 'meta_value' ) === 0 );

		if ( $isSearchByMeta && empty( $metaKey ) ) {
			// "meta_key" must be present in atts to order by "meta_value",
			// "meta_value_num" or "meta_value_*"
			$orderby        = $defaults['orderby'];
			$order          = $defaults['order'];
			$isSearchByMeta = false;
		}

		if ( $orderby == 'id' ) {
			$orderby = 'ID';
		}

		$query = array(
			'orderby' => $orderby,
			'order'   => $order,
		);

		if ( $isSearchByMeta ) {
			$query['meta_key'] = $metaKey;
		}

		if ( $orderby == 'meta_value' && ! empty( $metaType ) ) {
			$query['meta_type'] = $metaType;
		}

		return $query;
	}

	public function getName() {
		return $this->name;
	}

}
