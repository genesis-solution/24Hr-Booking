<?php
/**
 * Describes media area of the link post.
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

$milenia_link_text = $Milenia->getThemeOption('milenia-post-link-text', '', array('object_id' => get_the_ID()));
$milenia_link_url = $Milenia->getThemeOption('milenia-post-link-url', '', array('object_id' => get_the_ID()));
$milenia_link_target = $Milenia->getThemeOption('milenia-post-link-target', '1', array('object_id' => get_the_ID()));
$milenia_link_nofollow = $Milenia->getThemeOption('milenia-post-link-nofollow', '0', array('object_id' => get_the_ID()));

if(!empty($milenia_link_text)) : ?>
    <!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
    <div class="milenia-entity-media">
        <a class="milenia-entity-link-element milenia-ln--independent"
           href="<?php echo !empty($milenia_link_url) ? esc_url($milenia_link_url) : '#'; ?>"
           target="<?php echo esc_attr($milenia_link_target == '1' ? '_blank' : '_self'); ?>"
           <?php if($milenia_link_nofollow == '1') : ?>rel="nofollow"<?php endif; ?>>
		    <span class="milenia-entity-link-element-inner">
				<span class="icon icon-link2"></span>
				<?php echo esc_html($milenia_link_text); ?>
			</span>
        </a>
    </div>
    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
<?php endif; ?>
