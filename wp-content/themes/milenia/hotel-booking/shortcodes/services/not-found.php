<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="mphb-not-found">
	<?php _e( 'No services matched criteria.', 'milenia' ); ?>
</p>
<div id="milenia-nothing-found-alert-box" role="alert" class="milenia-alert-box milenia-alert-box--info">
    <div class="milenia-alert-box-inner">
        <button type="button" class="milenia-alert-box-close"><?php esc_html_e('Close', 'milenia'); ?></button>
        <p><?php esc_html_e('Nothing found.', 'milenia'); ?></p>
		<?php esc_html_e( 'No services matched criteria.', 'milenia' ); ?>
    </div>
</div>
