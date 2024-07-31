<?php

namespace MPHB\Shortcodes;

class SearchShortcode extends AbstractShortcode {

	protected $name = 'mphb_availability_search';
	private $uniqid = '';
	private $checkInDate;
	private $checkOutDate;
	private $adults;
	private $children;
	private $attributes;

	public function __construct() {
		parent::__construct();
		add_action( 'mphb_sc_search_before_form', array( MPHB()->getPublicScriptManager(), 'enqueue' ) );
		add_action( 'mphb_sc_search_render_form_top', array( '\MPHB\Shortcodes\SearchShortcode', 'renderHiddenInputs' ) );
		add_action( 'mphb_sc_search_render_form_top', array( '\MPHB\Views\GlobalView', 'renderRequiredFieldsTip' ) );

		add_action( 'mphb_sc_search_form_before_submit_btn', array( $this, 'renderDateHiddenInputs' ) );
	}

	/**
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $shortcodeName
	 * @return string
	 */
	public function render( $atts, $content, $shortcodeName ) {

		$atts = $this->fixAttsDateFormat( $atts );

		$defaultAtts = array(
			'adults'         => MPHB()->settings()->main()->getMinAdults(),
			'children'       => MPHB()->settings()->main()->getMinChildren(),
			'check_in_date'  => '',
			'check_out_date' => '',
			'attributes'     => '',
			'uniqid'         => uniqid( 'mphb-search-form-' ),
			'class'          => '',
		);

		if ( empty( $atts ) ) {
			$atts = array();
		}

		$atts = $this->fillStoredSearchParameters( $atts );
		$atts = shortcode_atts( $defaultAtts, $atts, $shortcodeName );

		$this->setup( $atts );

		ob_start();

		$this->renderMain();

		$content = ob_get_clean();

		$wrapperClass  = apply_filters( 'mphb_sc_search_wrapper_class', 'mphb_sc_search-wrapper' );
		$wrapperClass .= empty( $wrapperClass ) ? $atts['class'] : ' ' . $atts['class'];
		return '<div class="' . esc_attr( $wrapperClass ) . '">' . $content . '</div>';
	}

	/**
	 * Convert user input date format to date transfer format
	 *
	 * @param array $atts
	 * @return array
	 */
	private function fixAttsDateFormat( $atts ) {
		$dateFormat         = MPHB()->settings()->dateTime()->getDateFormat();
		$dateTransferFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

		if ( ! empty( $atts['check_in_date'] ) ) {
			$atts['check_in_date'] = \MPHB\Utils\DateUtils::convertDateFormat( $atts['check_in_date'], $dateFormat, $dateTransferFormat );
		}
		if ( ! empty( $atts['check_out_date'] ) ) {
			$atts['check_out_date'] = \MPHB\Utils\DateUtils::convertDateFormat( $atts['check_out_date'], $dateFormat, $dateTransferFormat );
		}
		return $atts;
	}

	private function renderMain() {
		do_action( 'mphb_sc_search_before_form' );

		$templateAtts = array(
			'uniqid'       => $this->uniqid,
			'action'       => MPHB()->settings()->pages()->getSearchResultsPageUrl(),
			'checkInDate'  => isset( $this->checkInDate ) ? $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '',
			'checkOutDate' => isset( $this->checkOutDate ) ? $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '',
			'adults'       => $this->adults,
			'children'     => $this->children,
			'adultsList'   => MPHB()->settings()->main()->getAdultsListForSearch(),
			'childrenList' => MPHB()->settings()->main()->getChildrenListForSearch(),
			'attributes'   => $this->attributes,
		);
		mphb_get_template_part( 'shortcodes/search/search-form', $templateAtts );

		do_action( 'mphb_sc_search_after_form' );
	}

	/**
	 *
	 * @param array $atts
	 * @return array
	 */
	public function fillStoredSearchParameters( $atts ) {

		$storedParameters = MPHB()->searchParametersStorage()->get();

		if ( empty( $atts['adults'] ) &&
			! empty( $storedParameters['mphb_adults'] ) &&
			$storedParameters['mphb_adults'] <= MPHB()->settings()->main()->getSearchMaxAdults() ) {
			$atts['adults'] = (string) $storedParameters['mphb_adults'];
		}

		if ( empty( $atts['children'] ) &&
			! empty( $storedParameters['mphb_children'] ) &&
			$storedParameters['mphb_children'] <= MPHB()->settings()->main()->getSearchMaxChildren() ) {
			$atts['children'] = (string) $storedParameters['mphb_children'];
		}

		if ( empty( $atts['check_in_date'] ) &&
			! empty( $storedParameters['mphb_check_in_date'] ) ) {
			$atts['check_in_date'] = (string) $storedParameters['mphb_check_in_date'];
		}

		if ( empty( $atts['check_out_date'] ) &&
			! empty( $storedParameters['mphb_check_out_date'] ) ) {
			$atts['check_out_date'] = (string) $storedParameters['mphb_check_out_date'];
		}

		return $atts;
	}

	private function setup( $atts ) {
		$this->uniqid       = $atts['uniqid'];
		$this->adults       = $this->sanitizeAdults( $atts['adults'] );
		$this->children     = $this->sanitizeChildren( $atts['children'] );
		$this->checkInDate  = $this->sanitizeCheckInDate( $atts['check_in_date'] );
		$this->checkOutDate = $this->sanitizeCheckOutDate( $atts['check_out_date'] );
		$this->attributes   = $this->sanitizeAttributes( $atts['attributes'] );
		$this->attributes   = MPHB()->getAttributesPersistence()->getAttributes( $this->attributes, true );
	}

	private function sanitizeAdults( $adults ) {
		$adults = absint( $adults );
		return $adults >= MPHB()->settings()->main()->getMinAdults() && $adults <= MPHB()->settings()->main()->getSearchMaxAdults() ? $adults : MPHB()->settings()->main()->getMinAdults();
	}

	private function sanitizeChildren( $children ) {
		$children = absint( $children );
		return $children >= MPHB()->settings()->main()->getMinChildren() && $children <= MPHB()->settings()->main()->getSearchMaxChildren() ? $children : 0;
	}

	/**
	 *
	 * @param string $date
	 * @return \DateTime|null
	 */
	private function sanitizeCheckInDate( $date ) {
		$checkInDateObj = \MPHB\Utils\DateUtils::createCheckInDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );
		$todayDate      = \MPHB\Utils\DateUtils::createCheckInDate( 'Y-m-d', mphb_current_time( 'Y-m-d' ) );
		return $checkInDateObj && \MPHB\Utils\DateUtils::calcNights( $todayDate, $checkInDateObj ) >= 0 ? $checkInDateObj : null;
	}

	/**
	 *
	 * @param string $date
	 * @return \DateTime|null
	 */
	private function sanitizeCheckOutDate( $date ) {
		$checkOutDateObj = \MPHB\Utils\DateUtils::createCheckOutDate( MPHB()->settings()->dateTime()->getDateTransferFormat(), $date );
		return $checkOutDateObj && ( isset( $this->checkInDate ) && \MPHB\Utils\DateUtils::calcNights( $this->checkInDate, $checkOutDateObj ) >= 1 ) ? $checkOutDateObj : null;
	}

	private function sanitizeAttributes( $attributes ) {
		$attributes = sanitize_text_field( $attributes );
		$attributes = explode( ',', $attributes );
		$attributes = array_map( 'mphb_sanitize_attribute_name', $attributes );
		$attributes = array_filter( $attributes );
		return $attributes;
	}

	public static function renderHiddenInputs() {
		$parameters = mphb_get_query_args( MPHB()->settings()->pages()->getSearchResultsPageUrl() );
		foreach ( $parameters as $paramName => $paramValue ) {
			printf( '<input type="hidden" name="%s" value="%s" />', esc_attr( $paramName ), esc_attr( $paramValue ) );
		}
	}

	public function renderDateHiddenInputs() {
		$checkInDate  = isset( $this->checkInDate ) ? $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) : '';
		$checkOutDate = isset( $this->checkOutDate ) ? $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateTransferFormat() ) : '';

		echo '<input id="' . esc_attr( 'mphb_check_in_date-' . $this->uniqid . '-hidden' ) . '" value="' . esc_attr( $checkInDate ) . '" type="hidden" name="mphb_check_in_date" />';
		echo '<input id="' . esc_attr( 'mphb_check_out_date-' . $this->uniqid . '-hidden' ) . '" value="' . esc_attr( $checkOutDate ) . '" type="hidden" name="mphb_check_out_date" />';
	}

}
