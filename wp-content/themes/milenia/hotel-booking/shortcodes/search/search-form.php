<?php
/**
 * Available variables
 * - string $uniqid
 * - string $action Action for search form
 * - string $checkInDate
 * - string $checkOutDate
 * - int $adults
 * - int $children
 * - array $adultsList
 * - array $childrenList
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'milenia_mphb_sc_search_render_form_top' );
$arrivalDate = new DateTime(str_replace('/', '-', $checkInDate));
$departureDate = new DateTime(str_replace('/', '-', $checkOutDate));
$departureDate->add(new DateInterval('P1D'));

$now = new DateTime('now');
if($now > $arrivalDate) {
	$arrivalDate = $now;
	$departureDate = (new DateTime('now'))->add(new DateInterval('P1D'));
}

?>
<!--================ Search form ================-->
<form id="<?php echo esc_attr($uniqid); ?>" class="mphb_sc_search-form milenia-booking-form" method="GET" action="<?php echo esc_attr( $action ); ?>">
    <div class="milenia-booking-form-inner-wrapper">
        <div class="form-group">
            <div class="form-col form-col--arrival-date">
                <div class="form-control">
                    <label for="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqid ); ?>"><?php esc_html_e('Arrival Date', 'milenia'); ?></label>
                    <span class="milenia-field-datepicker milenia-field-datepicker--style-1" data-visible="milenia-booking-form-wrapper--v1">
                        <span class="milenia-field-datepicker-day"><?php echo esc_html($arrivalDate->format('j')); ?></span>
                        <span class="milenia-field-datepicker-others">
                            <span class="milenia-field-datepicker-month-year"><?php echo esc_html($arrivalDate->format('F, Y')); ?></span>
                            <span class="milenia-field-datepicker-dayname"><?php echo esc_html($arrivalDate->format('l')); ?></span>
                        </span>
                    </span>

					<span class="milenia-field-datepicker milenia-field-datepicker--style-2" data-visible="milenia-booking-form-wrapper--v2,milenia-booking-form-wrapper--v3,milenia-booking-form-wrapper--v4"><?php echo esc_html($arrivalDate->format('l jS F, Y')); ?></span>

                    <input
            			id="<?php echo esc_attr( 'mphb_check_in_date-' . $uniqid ); ?>"
            			data-datepick-group="<?php echo esc_attr( $uniqid ); ?>"
            			value="<?php echo esc_attr( $arrivalDate->format('d/m/Y') ); ?>"
            			placeholder="<?php esc_attr_e( 'Arrival Date', 'milenia' ); ?>"
            			required="required"
            			type="text"
            			name="mphb_check_in_date"
            			class="mphb-datepick milenia-datepicker milenia-field-datepicker-invoker"
            			autocomplete="off"
            			/>
                </div>
            </div>
            <div class="form-col form-col--departure-date">
                <div class="form-control">
                    <label for="<?php echo esc_attr( 'mphb_check_out_date-' . $uniqid ); ?>"><?php esc_html_e('Departure Date', 'milenia'); ?></label>

                    <span class="milenia-field-datepicker milenia-field-datepicker--style-1" data-visible="milenia-booking-form-wrapper--v1">
						<span class="milenia-field-datepicker-day"><?php echo esc_html($departureDate->format('j')); ?></span>
                        <span class="milenia-field-datepicker-others">
                            <span class="milenia-field-datepicker-month-year"><?php echo esc_html($departureDate->format('F, Y')); ?></span>
                            <span class="milenia-field-datepicker-dayname"><?php echo esc_html($departureDate->format('l')); ?></span>
                        </span>
                    </span>

					<span class="milenia-field-datepicker milenia-field-datepicker--style-2" data-visible="milenia-booking-form-wrapper--v2,milenia-booking-form-wrapper--v3,milenia-booking-form-wrapper--v4"><?php echo esc_html($departureDate->format('l jS F, Y')); ?></span>

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

        	<?php if(!MPHB()->settings()->main()->isAdultsDisabledOrHidden()) :
		        $maxAdults = MPHB()->settings()->main()->getSearchMaxAdults();
				?>
                <div class="form-col form-col--adults">
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

						<div class="milenia-custom-select" data-visible="milenia-booking-form-wrapper--v2,milenia-booking-form-wrapper--v3,milenia-booking-form-wrapper--v4">
							<select id="<?php echo esc_attr( 'mphb_adults-' . $uniqid ); ?>" name="mphb_adults" data-default-text="<?php echo esc_attr( $adults ); ?>">
								<?php foreach($adultsList as $value) : ?>
									<option value="<?php echo esc_attr($value); ?>" <?php selected($adults, $value, true); ?>><?php echo esc_html($value); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

                        <div class="milenia-field-counter" data-visible="milenia-booking-form-wrapper--v1" data-counter-max="<?php echo esc_attr($maxAdults); ?>">
                            <div class="milenia-field-counter-value"><?php echo esc_html( $adults ); ?></div>
                            <input type="hidden" name="mphb_adults" value="<?php echo esc_attr( $adults ); ?>" class="milenia-field-counter-target">
                            <button type="button" class="milenia-field-counter-control milenia-field-counter-control--decrease"></button>
                            <button type="button" class="milenia-field-counter-control milenia-field-counter-control--increase"></button>
                        </div>
                    </div>
                </div>
        	<?php endif; ?>


            <?php if (!MPHB()->settings()->main()->isChildrenDisabledOrHidden()) :
	            $maxChildren = MPHB()->settings()->main()->getSearchMaxChildren();
				?>

                <div class="form-col form-col--children">
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

						<div class="milenia-custom-select" data-visible="milenia-booking-form-wrapper--v2,milenia-booking-form-wrapper--v3,milenia-booking-form-wrapper--v4">
							<select id="<?php echo esc_attr( 'mphb_children-' . $uniqid ); ?>" name="mphb_children" data-default-text="<?php echo esc_attr( $children ); ?>">
								<?php foreach($childrenList as $value) : ?>
									<option value="<?php echo esc_attr($value); ?>" <?php selected($children, $value, true); ?>><?php echo esc_html($value); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

                        <div class="milenia-field-counter" data-visible="milenia-booking-form-wrapper--v1" data-counter-max="<?php echo esc_attr($maxChildren); ?>">
                            <div class="milenia-field-counter-value"><?php echo esc_html($children); ?></div>
                            <input type="hidden" name="mphb_children" value="<?php echo esc_attr($children); ?>" class="milenia-field-counter-target">
                            <button type="button" class="milenia-field-counter-control milenia-field-counter-control--decrease"></button>
                            <button type="button" class="milenia-field-counter-control milenia-field-counter-control--increase"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php do_action( 'mphb_sc_search_form_before_submit_btn' ); ?>

            <div class="form-col form-col--action">
                <div class="form-control">
                    <button type="submit" class="milenia-btn milenia-btn--huge milenia-btn--scheme-primary" data-visible="milenia-booking-form-wrapper--v1,milenia-booking-form-wrapper--v2,milenia-booking-form-wrapper--v3"><?php esc_html_e('Check Availability', 'milenia'); ?></button>
                    <button type="submit" class="milenia-btn milenia-btn--scheme-dark" data-visible="milenia-booking-form-wrapper--v4"><?php esc_html_e('Check Availability', 'milenia'); ?></button>
                </div>
            </div>

            <?php do_action( 'mphb_sc_search_form_bottom' ); ?>
        </div>

        <?php if($attributes) : ?>
            <div class="form-group form-group--visible">
                <?php do_action( 'mphb_sc_search_form_before_attributes' ); ?>

                <?php foreach ( $attributes as $attributeName => $terms ) { ?>
                    <div class="form-col form-col--column <?php echo esc_attr( 'mphb_sc_search-' . $attributeName ); ?>">
                        <label>
                            <?php echo esc_html( mphb_attribute_title( $attributeName ) ); ?>
                        </label>

                        <div class="milenia-custom-select">
                            <select id="<?php echo esc_attr( 'mphb_' . $attributeName . '-' . $uniqid ); ?>" name="<?php echo esc_attr( 'mphb_attributes[' . $attributeName . ']' ); ?>">
                                <option value=""><?php echo mphb_attribute_default_text( $attributeName ); ?></option>
                                <?php foreach ( $terms as $termId => $termLabel ) { ?>
                                    <option value="<?php echo esc_attr( $termId ); ?>"><?php echo esc_html( $termLabel ); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if(MPHB()->settings()->main()->isAdultsDisabledOrHidden()) : ?>
        <input type="hidden" id="<?php echo esc_attr( 'mphb_adults-' . $uniqid ); ?>" name="mphb_adults" value="<?php echo esc_attr( MPHB()->settings()->main()->getMinAdults() ); ?>" />
    <?php endif; ?>

    <?php if (MPHB()->settings()->main()->isChildrenDisabledOrHidden()) : ?>
		<input type="hidden" id="<?php echo esc_attr( 'mphb_children-' . $uniqid ); ?>" name="mphb_children" value="<?php echo esc_attr( MPHB()->settings()->main()->getMinChildren() ); ?>" />
	<?php endif; ?>
</form>
<script>
	(function($){
		'use strict';

		if(!$) return;

		$(function(){
			var $container = $('#<?php echo esc_attr($uniqid); ?>'),
				$visible;

			if(!$container.length) return;

			$visible = $container.find('[data-visible]');

			if(!$visible.length) return;

			if($container.closest('.milenia-booking-form-wrapper--v1').length) {
				$visible.each(function(index, element){
					var $el = $(element),
						data = $el.data('visible');

					if(data && data.split(',').indexOf('milenia-booking-form-wrapper--v1') == -1) {
						$el.remove();
					}
				});
			}
			else if($container.closest('.milenia-booking-form-wrapper--v2').length) {
				$visible.each(function(index, element){
					var $el = $(element),
						data = $el.data('visible');

					if(data && data.split(',').indexOf('milenia-booking-form-wrapper--v2') == -1) {
						$el.remove();
					}
				});
			}
			else if($container.closest('.milenia-booking-form-wrapper--v3').length) {
				$visible.each(function(index, element){
					var $el = $(element),
						data = $el.data('visible');

					if(data && data.split(',').indexOf('milenia-booking-form-wrapper--v3') == -1) {
						$el.remove();
					}
				});
			}
			else if($container.closest('.milenia-booking-form-wrapper--v4').length) {
				$visible.each(function(index, element){
					var $el = $(element),
						data = $el.data('visible');

					if(data && data.split(',').indexOf('milenia-booking-form-wrapper--v4') == -1) {
						$el.remove();
					}
				});
			}
			else
			{
				$visible.each(function(index, element){
					var $el = $(element),
						data = $el.data('visible');

					if(data && data.split(',').indexOf('milenia-booking-form-wrapper--v1') == -1) {
						$el.remove();
					}
				});
			}
		});
	})(window.jQuery);
</script>
<!--================ End of Search form ================-->
