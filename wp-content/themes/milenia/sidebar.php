<?php
/**
* The template file for displaying sidebar
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $MileniaLayout;

if(is_active_sidebar($MileniaLayout->getSidebar())) : ?>
    <div class="milenia-grid">
        <?php dynamic_sidebar($MileniaLayout->getSidebar()); ?>
    </div>
<?php endif; ?>
