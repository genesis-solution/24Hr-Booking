<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="milenia-nothing-found-alert-box" role="alert" class="milenia-alert-box milenia-alert-box--info">
    <div class="milenia-alert-box-inner">
        <button type="button" class="milenia-alert-box-close"><?php esc_html_e('Close', 'milenia'); ?></button>
        <p><?php esc_html_e('Nothing found.', 'milenia'); ?></p>
		<?php esc_html_e( 'No accommodations matching criteria.', 'milenia' ); ?>
    </div>
</div>
