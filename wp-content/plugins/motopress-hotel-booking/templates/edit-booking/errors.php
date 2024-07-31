<?php

/**
 * Available variables:
 *     string[] $errors Array of error messages.
 *
 * @since 3.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapperClass = apply_filters( 'mphb_errors_wrapper_class', 'mphb-errors-wrapper' );
?>

<div class="<?php echo esc_attr( $wrapperClass ); ?>">
	<?php echo implode( '<br>', array_map( 'esc_html', $errors ) ); ?>
</div>
