<?php

namespace MPHB\Views;

class GlobalView {

	public static function renderRequiredFieldsTip() {
		mphb_get_template_part( 'required-fields-tip' );
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	public static function prependBr( $content ) {
		return '<br/>' . $content;
	}

	/**
	 * Display custom pagination for WP_Query
	 *
	 * @param \WP_Query $wp_query
	 * @return null
	 */
	public static function renderPagination( \WP_Query $wp_query ) {

		if ( $wp_query->max_num_pages == 1 ) {
			return;
		}

		$big          = 999999;
		$search_for   = array( $big, '#038;' );
		$replace_with = array( '%#%', '&' );

		$paginationAtts = array(
			'base'    => str_replace( $search_for, $replace_with, get_pagenum_link( $big ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, mphb_get_paged_query_var() ),
			'total'   => $wp_query->max_num_pages,
		);
		$paginationAtts = apply_filters( 'mphb_pagination_args', $paginationAtts );

		$pagination = paginate_links( $paginationAtts );
		$pagination = apply_filters( 'mphb_pagination_links', $pagination );

		if ( $pagination ) {

			$screenReaderText = '';

			switch ( $wp_query->get( 'post_type' ) ) {
				case MPHB()->postTypes()->roomType()->getPostType():
					$screenReaderText = __( 'Accommodation pagination', 'motopress-hotel-booking' );
					break;
				case MPHB()->postTypes()->service()->getPostType():
					$screenReaderText = __( 'Services pagination', 'motopress-hotel-booking' );
					break;
			}

			$paginationClass = apply_filters( 'mphb_pagination_class', 'mphb-pagination pagination' );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo self::_pagination_markup( $pagination, $paginationClass, $screenReaderText );
		}
	}

	/**
	 * Wraps passed links in pagination markup.
	 *
	 * @param string $links              Navigational links.
	 * @param string $class              Optional. Custom classes string for nav element.
	 * @param string $screen_reader_text Optional. Screen reader text for nav element. Default: ''.
	 * @return string Pagination template tag.
	 */
	protected static function _pagination_markup( $links, $class = '', $screen_reader_text = '' ) {

		$template = '
			<nav class="navigation %1$s" role="navigation">
				<h2 class="screen-reader-text">%2$s</h2>
				<div class="nav-links">%3$s</div>
			</nav>';

		return sprintf( $template, esc_attr( $class ), esc_html( $screen_reader_text ), $links );
	}

}
