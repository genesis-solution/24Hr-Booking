<?php
/**
 * Available variables
 * - string $uniqid
 * - string $action Action for search form
 * - string $checkInDate
 * - string $checkOutDate
 * - int $adults
 * - int $children
 *  * - array $attributes [%Attribute name% => [%Term ID% => %Term title%]]
 * - array $adultsList
 * - array $childrenList
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if(!isset($adultsList)) $adultsList = MPHB()->settings()->main()->getAdultsListForSearch();
if(!isset($childrenList)) $childrenList = MPHB()->settings()->main()->getChildrenListForSearch();

$arrivalDate = new DateTime(str_replace('/', '-', $checkInDate));
$departureDate = new DateTime(str_replace('/', '-', $checkOutDate));
$departureDate->add(new DateInterval('P1D'));

$now = new DateTime('now');
if($now > $arrivalDate) {
	$arrivalDate = $now;
	$departureDate = (new DateTime('now'))->add(new DateInterval('P1D'));
}

?>
<div class="milenia-mphb-widget-inner">
	<small class="mphb-required-fields-tip form-caption"><?php printf( esc_html__( 'Required fields are followed by %s', 'milenia' ), '<abbr title="required">*</abbr>' ); ?></small>

	<form method="GET" class="mphb_widget_search-form milenia-booking-form milenia-booking-form-wrapper--v3" action="<?php echo esc_attr( $action ); ?>">
		<?php
		/**
		 * @hooked \MPHB\Widgets\SearchAvailabilityWidget::renderHiddenInputs - 10
		 */
		do_action( 'mphb_widget_search_form_top' );
		?>

		<div class="form-group">
			<div class="form-col form-col--arrival-date mphb_widget_search-check-in-date">
				<div class="form-control">
					<label for="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqid ); ?>"><?php esc_html_e('Arrival Date', 'milenia'); ?>
						<abbr title="<?php printf( _x( 'Formatted as %s', 'Date format tip', 'milenia' ), MPHB()->settings()->dateTime()->getDateFormatJS() ); ?>">*</abbr>
					</label>
					<span class="milenia-field-datepicker milenia-field-datepicker--style-2"><?php echo esc_html($arrivalDate->format('l jS F, Y')); ?></span>

					<input
						id="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqid ); ?>"
						data-datepick-group="<?php echo esc_attr( $uniqid ); ?>"
						value="<?php echo esc_attr( $arrivalDate->format('d/m/Y') ); ?>"
						placeholder="<?php esc_attr_e( 'Arrival Date', 'milenia' ); ?>"
						required="required"
						type="text"
						name="mphb_check_in_date"
						class="mphb-datepick milenia-datepicker milenia-field-datepicker-invoker"
						autocomplete="off"/>
				</div>
			</div>
		</div>

		<div class="form-group">
			<div class="form-col form-col--departure-date mphb_widget_search-check-out-date">
				<div class="form-control">
					<label for="<?php echo esc_attr( 'mphb_check_out_date-' . $uniqid ); ?>"><?php esc_html_e('Departure Date', 'milenia'); ?>
						<abbr title="<?php printf( _x( 'Formatted as %s', 'Date format tip', 'milenia' ), MPHB()->settings()->dateTime()->getDateFormatJS() ); ?>">*</abbr>
					</label>

					<span class="milenia-field-datepicker milenia-field-datepicker--style-2"><?php echo esc_html($departureDate->format('l jS F, Y')); ?></span>

					<input
						id="<?php echo esc_attr( 'mphb_check_out_date-' . $uniqid ); ?>"
						data-datepick-group="<?php echo esc_attr( $uniqid ); ?>"
						value="<?php echo esc_attr( $departureDate->format('d/m/Y') ); ?>"
						placeholder="<?php esc_attr_e( 'Departure Date', 'milenia' ); ?>"
						required="required"
						type="text"
						name="mphb_check_out_date"
						class="mphb-datepick milenia-datepicker milenia-field-datepicker-invoker"
						autocomplete="off"
						/>
				</div>
			</div>
		</div>

		<?php if(!MPHB()->settings()->main()->isAdultsDisabledOrHidden()) : ?>
			<div class="form-group">
				<div class="form-col form-col--adults mphb_widget_search-adults">
					<div class="form-control">
						<label for="<?php echo esc_attr( 'mphb_adults-' . $uniqid ); ?>">
							<?php
								if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
									esc_html_e( 'Adults', 'milenia' );
								} else {
									esc_html_e( 'Guests', 'milenia' );
								}
							?>
						</label>

						<select id="<?php echo esc_attr( 'mphb_adults-' . $uniqid ); ?>" name="mphb_adults" data-default-text="<?php echo esc_attr( $adults ); ?>">
							<?php foreach($adultsList as $value) : ?>
								<option value="<?php echo esc_attr($value); ?>" <?php selected($adults, $value, true); ?>><?php echo esc_html($value); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if (!MPHB()->settings()->main()->isChildrenDisabledOrHidden()) : ?>
			<div class="form-group">
				<div class="form-col form-col--children mphb_widget_search-children">
					<div class="form-control">
						<label for="<?php echo esc_attr( 'mphb_children-' . $uniqid ); ?>">
							<?php
								$childrenAge = MPHB()->settings()->main()->getChildrenAgeText();
								if ( empty( $childrenAge ) ) {
									esc_html_e( 'Children', 'milenia' );
								} else {
									printf( esc_html__( 'Children %s', 'milenia' ), $childrenAge );
								}
							?>
						</label>

						<select id="<?php echo esc_attr( 'mphb_children-' . $uniqid ); ?>" name="mphb_children" data-default-text="<?php echo esc_attr( $children ); ?>">
							<?php foreach($childrenList as $value) : ?>
								<option value="<?php echo esc_attr($value); ?>" <?php selected($children, $value, true); ?>><?php echo esc_html($value); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php do_action( 'mphb_widget_search_form_before_attributes' ); ?>

		<?php if ( !empty( $attributes ) ) { ?>
			<?php foreach ( $attributes as $attributeName => $terms ) { ?>
				<p class="<?php echo esc_attr( 'mphb_widget_search-' . $attributeName ); ?>">
					<label for="<?php echo esc_attr( 'mphb_' . $attributeName . '-' . $uniqid ); ?>">
						<?php echo esc_html( mphb_attribute_title( $attributeName ) ); ?>:
					</label>
					<br />
					<select id="<?php echo esc_attr( 'mphb_' . $attributeName . '-' . $uniqid ); ?>" name="<?php echo esc_attr( 'mphb_attributes[' . $attributeName . ']' ); ?>">
						<option value=""><?php echo mphb_attribute_default_text( $attributeName ); ?></option>
						<?php foreach ( $terms as $termId => $termLabel ) { ?>
							<option value="<?php echo esc_attr( $termId ); ?>"><?php echo esc_html( $termLabel ); ?></option>
						<?php } ?>
					</select>
				</p>
			<?php } ?>
		<?php } ?>

		<div class="form-group">
			<div class="form-col form-col--action mphb_widget_search-submit-button-wrapper">
				<div class="form-control">
					<?php do_action( 'mphb_widget_search_form_before_submit_btn' ); ?>
					<button type="submit" class="milenia-btn milenia-btn--huge milenia-btn--scheme-primary"><?php esc_html_e('Check Availability', 'milenia'); ?></button>
				</div>
			</div>
		</div>

		<?php if(MPHB()->settings()->main()->isAdultsDisabledOrHidden()) : ?>
	        <input type="hidden" id="<?php echo esc_attr( 'mphb_adults-' . $uniqid ); ?>" name="mphb_adults" value="<?php echo esc_attr( MPHB()->settings()->main()->getMinAdults() ); ?>" />
	    <?php endif; ?>

	    <?php if (MPHB()->settings()->main()->isChildrenDisabledOrHidden()) : ?>
			<input type="hidden" id="<?php echo esc_attr( 'mphb_children-' . $uniqid ); ?>" name="mphb_children" value="<?php echo esc_attr( MPHB()->settings()->main()->getMinChildren() ); ?>" />
		<?php endif; ?>

		<?php do_action( 'mphb_widget_search_form_bottom' ); ?>
	</form>
</div>
