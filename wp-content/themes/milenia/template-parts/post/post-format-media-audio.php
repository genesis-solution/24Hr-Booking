<?php
/**
 * Describes media area of the audio post.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia;

$milenia_soundcloud_src = $Milenia->getThemeOption('milenia-audio-soundcloud', null, array('object_id' => get_the_ID()));

if(!empty($milenia_soundcloud_src)) : ?>
    <!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
    <div class="milenia-entity-media">
        <div class="milenia-fullwidth-iframe">
          <?php echo wp_kses($milenia_soundcloud_src, array(
			  'iframe' => array(
				  'src' => true
			  )
		  )); ?>
        </div>
    </div>
    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
<?php endif; ?>
