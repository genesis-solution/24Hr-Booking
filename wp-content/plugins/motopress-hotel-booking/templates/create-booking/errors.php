<?php

/**
 * Available variables
 * - string[] $errors Array of error messages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapperClass = apply_filters( 'mphb_errors_wrapper_class', 'mphb-errors-wrapper' );

$errors = array_map(
	function( $error ) {
		return esc_html( $error );
	},
	$errors
);

?>

<div class="<?php echo esc_attr( $wrapperClass ); ?>">
	<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo join( '<br />', $errors );
	?>
</div>
