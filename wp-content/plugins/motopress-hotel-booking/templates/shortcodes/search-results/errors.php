<?php
/**
 * Available variables
 * - array $errors Array of error messages
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$errors = array_map(
	function( $error ) {
		/**
		 * @hooked \MPHB\Views\GlobalView::prependBr - 10
		 */
		return apply_filters( 'mphb_sc_search_results_error', $error );
	},
	$errors
);

$errorsWrapperClass = apply_filters( 'mphb_sc_search_results_errors_wrapper_class', 'mphb-errors-wrapper' );
?>
<div class="<?php echo esc_attr( $errorsWrapperClass ); ?>">
	<?php
	foreach ( $errors as $error ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $error;
	}
	?>
</div>
