<?php

namespace MPHB\Persistences;

class AttributesPersistence extends CPTPersistence {

	/**
	 * @param string $attributeName
	 * @param bool   $hideEmpty Optional. Hide unused terms. FALSE by default.
	 *
	 * @return array [%Term ID% => %Term title%]
	 */
	public function getTermsIdTitleList( $attributeName, $hideEmpty = false ) {

		$taxonomyName = mphb_attribute_taxonomy_name( $attributeName );

		$queryArgs = array(
			'taxonomy'   => $taxonomyName,
			'hide_empty' => $hideEmpty,
		);

		$orderby = mphb_attribute_orderby( $attributeName );

		switch ( $orderby ) {
			case 'custom':
				// See \MPHB\PostTypes\AttributesCPT::supportAttributesMenuOrder()
				$queryArgs['menu_order'] = 'ASC';
				break;

			case 'name':
				$queryArgs['orderby']    = 'name';
				$queryArgs['menu_order'] = false;
				break;

			case 'numeric':
				// Sort manually after we get the terms list, but for now just
				// disable the custom order
				$queryArgs['menu_order'] = false;
				break;

			case 'id':
				$queryArgs['orderby']    = 'id';
				$queryArgs['order']      = 'ASC';
				$queryArgs['menu_order'] = false;
				break;
		}

		$terms = get_terms( $queryArgs );

		if ( is_wp_error( $terms ) ) {
			// Probably tried to get terms of non-existent taxonomy (outdated
			// data in search shortcode parameters
			$terms = array();
		}

		if ( ! empty( $terms ) ) {
			$terms = array_combine( wp_list_pluck( $terms, 'term_id' ), wp_list_pluck( $terms, 'name' ) );
		}

		if ( $orderby == 'numeric' && ! empty( $terms ) ) {
			uasort(
				$terms,
				function ( $a, $b ) {
					$floatA = (float) $a;
					$floatB = (float) $b;

					if ( abs( $floatA - $floatB ) < 0.001 ) {
						return 0;
					}

					return ( $floatA < $floatB ) ? -1 : 1;
				}
			);
		}

		return $terms;
	}

	/**
	 * @param string[] $attributes Attribute names, like "price", "hotel" etc.
	 * @param bool     $hideEmpty Optional. Hide unused terms and attributes without
	 *                            terms. FALSE by default.
	 *
	 * @return array [%Attribute name% => [%Term ID% => %Term title%]]
	 */
	public function getAttributes( $attributes, $hideEmpty = false ) {
		$_attributes = array();

		foreach ( $attributes as $attributeName ) {
			$terms = $this->getTermsIdTitleList( $attributeName, $hideEmpty );

			if ( count( $terms ) > 0 || ! $hideEmpty ) {
				$_attributes[ $attributeName ] = $terms;
			}
		}

		return $_attributes;
	}

}
