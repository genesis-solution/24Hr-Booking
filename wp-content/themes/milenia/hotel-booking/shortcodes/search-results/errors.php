<?php
/**
 * Available variables
 * - array $errors Array of error messages
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

$errors = array_map( function($error) {
	/**
	 * @hooked \MPHB\Views\GlobalView::prependBr - 10
	 */
	return apply_filters( 'mphb_sc_search_results_error', $error );
}, $errors );

$errorsWrapperClass = apply_filters( 'mphb_sc_search_results_errors_wrapper_class', 'mphb-errors-wrapper' );
?>
<!--================ Alert Box ================-->
<div id="milenia-nothing-found-alert-box" role="alert" class="milenia-alert-box milenia-alert-box--error <?php echo esc_attr( $errorsWrapperClass ); ?>">
    <div class="milenia-alert-box-inner">
        <button type="button" class="milenia-alert-box-close"><?php esc_html_e('Close', 'milenia'); ?></button>

		<?php if(!empty($errors)) : ?>
			<ul class="milenia-list--unstyled">
				<?php foreach ( $errors as $error ) : ?>
					<li><?php echo wp_kses($error, array()); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif;?>
    </div>
</div>
<!--================ End of Alert Box ================-->
