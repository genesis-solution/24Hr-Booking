<?php

namespace MPHB\Widgets;

class SearchAvailabilityWidget extends BaseWidget {

	/**
	 *
	 * @var int
	 */
	private $adults;

	/**
	 *
	 * @var int
	 */
	private $children;

	/**
	 *
	 * @var \DateTime
	 */
	private $checkInDate;

	/**
	 *
	 * @var \DateTime
	 */
	private $checkOutDate;

	/**
	 * @var array [%Attribute name% => [%Term ID% => %Term title%]]
	 *
	 * @see \MPHB\Persistences\AttributesPersistence::getAttributes()
	 */
	private $attributes;

	/**
	 *
	 * @var string
	 */
	private $uniqid;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		$baseId = 'mphb_search_availability_widget';
		$name   = __( 'Search Availability', 'motopress-hotel-booking' );

		$widgetOptions = array(
			'description' => __( 'Search Availability Form', 'motopress-hotel-booking' ),
		);

		add_action( 'mphb_widget_search_form_top', array( '\MPHB\Widgets\SearchAvailabilityWidget', 'renderHiddenInputs' ) );
		add_action( 'mphb_widget_search_form_before_submit_btn', array( $this, 'renderDateHiddenInputs' ) );

		parent::__construct( $baseId, $name, $widgetOptions );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see \WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$instance = $this->fixInstanceDateFormat( $instance );

		$this->enqueueStylesScripts();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if ( ! empty( $title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Prepare instance for filling stored parameters. Now date parameters store in "date transfer" format
		if ( ! empty( $instance['check_in_date'] ) ) {
			$instance['check_in_date'] = \MPHB\Utils\DateUtils::convertDateFormat( $instance['check_in_date'], MPHB()->settings()->dateTime()->getDateFormat(), MPHB()->settings()->dateTime()->getDateTransferFormat() );
		}
		if ( ! empty( $instance['check_out_date'] ) ) {
			$instance['check_out_date'] = \MPHB\Utils\DateUtils::convertDateFormat( $instance['check_out_date'], MPHB()->settings()->dateTime()->getDateFormat(), MPHB()->settings()->dateTime()->getDateTransferFormat() );
		}

		$instance = $this->fillStoredSearchParameters( $instance );

		$this->adults   = ! empty( $instance['adults'] ) ? $instance['adults'] : MPHB()->settings()->main()->getMinAdults();
		$this->children = ! empty( $instance['children'] ) ? $instance['children'] : MPHB()->settings()->main()->getMinChildren();

		$checkInDate = ! empty( $instance['check_in_date'] ) ? \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $instance['check_in_date'] ) : false;
		if ( $checkInDate ) {
			$this->checkInDate = $checkInDate;
		}

		$checkOutDate = ! empty( $instance['check_out_date'] ) ? \DateTime::createFromFormat( MPHB()->settings()->dateTime()->getDateTransferFormat(), $instance['check_out_date'] ) : false;
		if ( $checkOutDate ) {
			$this->checkOutDate = $checkOutDate;
		}

		$this->attributes = array();
		if ( ! empty( $instance['attributes'] ) ) {
			$this->attributes = $this->parseAttributes( $instance['attributes'] );
			$this->attributes = MPHB()->getAttributesPersistence()->getAttributes( $this->attributes, true );
		}

		$this->uniqid = uniqid();

		$this->renderMain( $instance );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	private function parseAttributes( $attributes ) {
		$attributes = explode( ',', $attributes );
		$attributes = array_map( 'mphb_sanitize_attribute_name', $attributes );
		$attributes = array_filter( $attributes );
		return $attributes;
	}

	/**
	 * Convert user input date format to date transfer format
	 *
	 * @param array $instance
	 * @return array
	 */
	private function fixInstanceDateFormat( $instance ) {
		$dateFormat         = MPHB()->settings()->dateTime()->getDateFormat();
		$dateTransferFormat = MPHB()->settings()->dateTime()->getDateTransferFormat();

		if ( ! empty( $instance['check_in_date'] ) ) {
			$instance['check_in_date'] = \MPHB\Utils\DateUtils::convertDateFormat( $instance['check_in_date'], $dateFormat, $dateTransferFormat );
		}
		if ( ! empty( $instance['check_out_date'] ) ) {
			$instance['check_out_date'] = \MPHB\Utils\DateUtils::convertDateFormat( $instance['check_out_date'], $dateFormat, $dateTransferFormat );
		}
		return $instance;
	}

	/**
	 * @param array $instance
	 *
	 * @since 3.8.1 added new filter - "{$widget_id}_template_args".
	 */
	private function renderMain( $instance ) {

		$templateArgs = apply_filters(
			"{$this->id_base}_template_args",
			array(
				'adults'       => $this->adults,
				'children'     => $this->children,
				'checkInDate'  => ! is_null( $this->checkInDate ) ? $this->checkInDate->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '',
				'checkOutDate' => ! is_null( $this->checkOutDate ) ? $this->checkOutDate->format( MPHB()->settings()->dateTime()->getDateFormat() ) : '',
				'attributes'   => $this->attributes,
			),
			$instance,
			$this
		);

		$templateAtts = array_merge(
			$templateArgs,
			array(
				'action' => MPHB()->settings()->pages()->getSearchResultsPageUrl(),
				'uniqid' => $this->uniqid,
				'args'   => $templateArgs,
			)
		);

		mphb_get_template_part( 'widgets/search-availability/search-form', $templateAtts );
	}

	/**
	 *
	 * @param array $atts
	 * @return array
	 */
	private function fillStoredSearchParameters( $atts ) {

		$storedParameters = MPHB()->searchParametersStorage()->get();

		if ( ! empty( $storedParameters['mphb_adults'] ) &&
			$storedParameters['mphb_adults'] <= MPHB()->settings()->main()->getSearchMaxAdults() ) {
			$atts['adults'] = (string) $storedParameters['mphb_adults'];
		}

		if ( $storedParameters['mphb_children'] !== '' &&
			$storedParameters['mphb_children'] <= MPHB()->settings()->main()->getSearchMaxChildren() ) {
			$atts['children'] = (string) $storedParameters['mphb_children'];
		}

		if ( ! empty( $storedParameters['mphb_check_in_date'] ) ) {
			$atts['check_in_date'] = (string) $storedParameters['mphb_check_in_date'];
		}

		if ( ! empty( $storedParameters['mphb_check_out_date'] ) ) {
			$atts['check_out_date'] = (string) $storedParameters['mphb_check_out_date'];
		}

		return $atts;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see \WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @since 3.8.1 added new action - "{$widget_id}_after_controls".
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'          => '',
			'adults'         => MPHB()->settings()->main()->getMinAdults(),
			'children'       => MPHB()->settings()->main()->getMinChildren(),
			'check_in_date'  => '',
			'check_out_date' => '',
			'attributes'     => '',
		);

		$instance = wp_parse_args( $instance, $defaults );

		extract( $instance );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'motopress-hotel-booking' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'check_in_date' ) ); ?>"><?php esc_html_e( 'Check-in Date:', 'motopress-hotel-booking' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'check_in_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'check_in_date' ) ); ?>" type="text" value="<?php echo esc_attr( $check_in_date ); ?>"><small><?php echo esc_html( sprintf( _x( 'Preset date. Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormatJS() ) ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'check_out_date' ) ); ?>"><?php esc_html_e( 'Check-out Date:', 'motopress-hotel-booking' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'check_out_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'check_out_date' ) ); ?>" type="text" value="<?php echo esc_attr( $check_out_date ); ?>">
			<small><?php echo esc_html( sprintf( _x( 'Preset date. Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormatJS() ) ); ?></small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'adults' ) ); ?>"><?php esc_html_e( 'Preset Adults:', 'motopress-hotel-booking' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'adults' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'adults' ) ); ?>" >
				<?php foreach ( MPHB()->settings()->main()->getAdultsListForSearch() as $adultsCount => $adultsCountLabel ) : ?>
					<option value="<?php echo esc_attr( $adultsCount ); ?>" <?php selected( $adults, $adultsCount ); ?>><?php echo esc_html( $adultsCountLabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'children' ) ); ?>"><?php esc_html_e( 'Preset Children:', 'motopress-hotel-booking' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'children' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'children' ) ); ?>" >
				<?php foreach ( MPHB()->settings()->main()->getChildrenListForSearch() as $childrenCount => $childrenCountLabel ) : ?>
					<option value="<?php echo esc_attr( $childrenCount ); ?>" <?php selected( $children, $childrenCount ); ?>><?php echo esc_html( $childrenCountLabel ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'attributes' ) ); ?>"><?php esc_html_e( 'Attributes:', 'motopress-hotel-booking' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'attributes' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attributes' ) ); ?>" type="text" value="<?php echo esc_attr( $attributes ); ?>">
		</p>
		<?php
		do_action( "{$this->id_base}_after_controls", $instance, $this );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see \WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 *
	 * @since 3.8.1 added new filter - "{$widget_id}_before_update".
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array(
			'title'          => '',
			'adults'         => '',
			'children'       => '',
			'check_in_date'  => '',
			'check_out_date' => '',
			'attributes'     => '',
		);

		if ( isset( $new_instance['title'] ) && $new_instance['title'] !== '' ) {
			$instance['title'] = strip_tags( $new_instance['title'] );
		}

		if ( isset( $new_instance['adults'] ) && $new_instance['adults'] !== '' ) {
			$minAdults          = MPHB()->settings()->main()->getMinAdults();
			$maxAdults          = MPHB()->settings()->main()->getSearchMaxAdults();
			$instance['adults'] = $this->sanitizeInt( $new_instance['adults'], $minAdults, $maxAdults );
		}

		if ( isset( $new_instance['children'] ) && $new_instance['children'] !== '' ) {
			$minChildren          = MPHB()->settings()->main()->getMinChildren();
			$maxChildren          = MPHB()->settings()->main()->getSearchMaxChildren();
			$instance['children'] = $this->sanitizeInt( $new_instance['children'], $minChildren, $maxChildren );
		}

		if ( isset( $new_instance['check_in_date'] ) && ! empty( $new_instance['check_in_date'] ) ) {
			$instance['check_in_date'] = $this->sanitizeDate( $new_instance['check_in_date'] );
		}

		if ( isset( $new_instance['check_out_date'] ) && ! empty( $new_instance['check_out_date'] ) ) {
			$instance['check_out_date'] = $this->sanitizeDate( $new_instance['check_out_date'] );
		}

		if ( isset( $new_instance['attributes'] ) && ! empty( $new_instance['attributes'] ) ) {
			$instance['attributes'] = $this->sanitizeText( $new_instance['attributes'] );
		}

		$instance = apply_filters( "{$this->id_base}_before_update", $instance, $new_instance, $old_instance, $this );

		return $instance;
	}

	public function enqueueStylesScripts() {
		MPHB()->getPublicScriptManager()->enqueue();
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
