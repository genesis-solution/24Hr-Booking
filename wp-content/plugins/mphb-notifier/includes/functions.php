<?php

/**
 * @return \MPHB\Notifier\Plugin
 *
 * @since 1.0
 */
function mphb_notifier() {
	return \MPHB\Notifier\Plugin::getInstance();
}

/**
 * @param int    $period
 * @param string $compare before|after
 * @return \DateTime[]
 *
 * @since 1.0
 */
function mphb_notifier_get_trigger_dates( $period, $compare ) {
	switch ( $compare ) {
		case 'before':
			$offset = "+{$period} days"; // "+2 days" for "2 days before check-in"
			$bound  = '+' . ( $period - 1 ) . ' day'; // "+1 day"
			break;

		case 'after':
			$offset = "-{$period} day"; // "-1 day" for "1 day after check-out"
			$bound  = '-' . ( $period + 1 ) . ' days'; // "-2 days"
			break;
	}

	$fromDate = new \DateTime( $bound );
	$toDate   = new \DateTime( $offset );

	return array(
		'from' => $fromDate,
		'to'   => $toDate,
	);
}

/**
 * @return array
 *
 * @since 1.0
 */
function mphb_notifier_get_trigger_comparisons() {
	return apply_filters(
		'mphb_notification_trigger_comparisons',
		array(
			'before' => esc_html_x( 'before', 'Before some day', 'mphb-notifier' ),
			'after'  => esc_html_x( 'after', 'After some day', 'mphb-notifier' ),
		)
	);
}

/**
 * @return array
 *
 * @since 1.0
 */
function mphb_notifier_get_trigger_fields() {
	return apply_filters(
		'mphb_notification_trigger_fields',
		array(
			'check-in'  => esc_html__( 'check-in', 'mphb-notifier' ),
			'check-out' => esc_html__( 'check-out', 'mphb-notifier' ),
		)
	);
}

/**
 * @param array $trigger [period, operator, date]
 * @return string String like "2 days before check-in".
 *
 * @since 1.0
 */
function mphb_notifier_convert_trigger_to_text( $trigger ) {
	$days = esc_html( _n( 'day', 'days', $trigger['period'], 'mphb-notifier' ) );

	$comparisons = mphb_notifier_get_trigger_comparisons();
	$compare     = array_key_exists( $trigger['compare'], $comparisons ) ? $comparisons[ $trigger['compare'] ] : '';

	$fields = mphb_notifier_get_trigger_fields();
	$field  = array_key_exists( $trigger['field'], $fields ) ? $fields[ $trigger['field'] ] : '';

	return sprintf( '%d %s %s %s', $trigger['period'], $days, $compare, $field );
}

/**
 * @return bool
 *
 * @since 1.0
 */
function mphb_notifier_use_edd_license() {
	return (bool) apply_filters( 'mphb_notifier_use_edd_license', true );
}
