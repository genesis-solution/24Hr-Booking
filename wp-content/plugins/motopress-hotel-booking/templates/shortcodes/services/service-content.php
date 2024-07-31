<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$wrapperClass = apply_filters( 'mphb_sc_services_item_class', join( ' ', mphb_tmpl_get_filtered_post_class( 'mphb-service' ) ) );
?>
<div class="<?php echo esc_attr( $wrapperClass ); ?>">

	<?php
	/**
	 * @hooked \MPHB\Views\LoopServiceView::renderFeaturedImage - 10
	 * @hooked \MPHB\Views\LoopServiceView::renderTitle - 20
	 * @hooked \MPHB\Views\LoopServiceView::renderExcerpt - 30
	 * @hooked \MPHB\Views\LoopServiceView::renderPrice - 40
	 */
	do_action( 'mphb_sc_services_service_details' );
	?>

</div>
