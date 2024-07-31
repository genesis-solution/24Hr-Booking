<?php
/**
* The template file that shows "Nothing found" message in case when the query
* returns nothing.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}
?>

<!-- - - - - - - - - - - - - - Alert Box - - - - - - - - - - - - - - - - -->
<div id="milenia-nothing-found-alert-box" role="alert" class="milenia-alert-box milenia-alert-box--info">
    <div class="milenia-alert-box-inner">
        <button type="button" class="milenia-alert-box-close"><?php esc_html_e('Close', 'milenia'); ?></button>
        <p><?php esc_html_e('Nothing found.', 'milenia'); ?></p>
    </div>
</div>
<!-- - - - - - - - - - - - - - End of Alert Box - - - - - - - - - - - - - - - - -->
