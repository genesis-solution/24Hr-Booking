<?php

namespace MPHB;

use MPHB\Utils\DateUtils;

class BlocksRender {

	public function renderSearch( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getSearch(), $atts );
	}

	public function renderAvailabilityCalendar( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getAvailabilityCalendar(), $atts );
	}

	public function renderSearchResults( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getSearchResults(), $atts );
	}

	public function renderRooms( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getRooms(), $atts );
	}

	public function renderServices( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getServices(), $atts );
	}

	public function renderRoom( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getRoom(), $atts );
	}

	public function renderCheckout( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getCheckout(), $atts );
	}

	public function renderBookingForm( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getBookingForm(), $atts );
	}

	public function renderRoomRates( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getRoomRates(), $atts );
	}

	public function renderBookingConfirmation( $atts ) {
		return $this->renderShortcode( MPHB()->getShortcodes()->getBookingConfirmation(), $atts );
	}

	/**
	 * @param \MPHB\Shortcodes\AbstractShortcode $shortcode
	 * @param array                              $atts
	 * @return string
	 *
	 * @since 3.8.1 added new filter: "mphb_render_block_attributes".
	 */
	protected function renderShortcode( $shortcode, $atts ) {
		$atts = $this->filterAtts( $atts );

		if ( $this->isAdminRequest() ) {
			$atts = $this->adminAtts( $atts, $shortcode->getName() );
		}

		$atts = apply_filters( 'mphb_render_block_attributes', $atts, $shortcode->getName() );

		mphb_fix_blocks_autop();

		return $shortcode->render( $atts, '', $shortcode->getName() );
	}

	protected function filterAtts( $atts ) {
		$atts = array_filter(
			$atts,
			function ( $value ) {
				return $value !== '';
			}
		);

		$class = '';

		if ( isset( $atts['className'] ) ) {
			$class .= $atts['className'];
			unset( $atts['className'] );
		}

		if ( isset( $atts['alignment'] ) ) {
			$class .= ' align' . $atts['alignment'];
			unset( $atts['alignment'] );
		}

		if ( ! empty( $class ) ) {
			$atts['class'] = trim( $class );
		}

		$dateFormat = MPHB()->settings()->dateTime()->getDateFormat();
		$dateRegex  = DateUtils::dateFormatToRegex( $dateFormat );

		foreach ( array( 'check_in_date', 'check_out_date' ) as $attrName ) {
			if ( ! isset( $atts[ $attrName ] ) ) {
				continue;
			}

			$isValid = (bool) preg_match( $dateRegex, $atts[ $attrName ] );

			if ( ! $isValid ) {
				unset( $atts[ $attrName ] );
			}
		}

		return $atts;
	}

	/**
	 * @since 3.7.1
	 */
	protected function adminAtts( $atts, $shortcodeName ) {
		if ( in_array( $shortcodeName, array( 'mphb_availability_calendar', 'mphb_rooms', 'mphb_room' ) ) ) {
			if ( ! empty( $atts['class'] ) ) {
				$atts['class'] .= ' mphb-gutenberg-reinit';
			} else {
				$atts['class'] = 'mphb-gutenberg-reinit';
			}
		}

		return $atts;
	}

	/**
	 * @since 3.7.1
	 */
	protected function isAdminRequest() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && $_REQUEST['context'] === 'edit';
	}
}
