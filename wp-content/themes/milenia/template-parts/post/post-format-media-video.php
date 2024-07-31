<?php
/**
 * Describes media area of the video post.
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

$milenia_is_selfhosted = $Milenia->getThemeOption('milenia-video-selfhosted-state', 0, array('object_id' => get_the_ID()));
$milenia_src_outer = $Milenia->getThemeOption('milenia-video-src-outer', null, array('object_id' => get_the_ID()));
$milenia_src_selfhosted = $Milenia->getThemeOption('milenia-video-src-selfhosted', null, array('object_id' => get_the_ID()));

if(!empty($milenia_src_outer) || !empty($milenia_src_selfhosted)) : ?>
    <!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
    <div class="milenia-entity-media">
        <?php if($milenia_is_selfhosted && !empty($milenia_src_selfhosted)) : ?>
            <?php foreach($milenia_src_selfhosted as $id => $video) : ?>
                <div class="milenia-selfhosted-video">
                    <video src="<?php echo esc_attr($video['src']); ?>" style="max-width:100%" class="mejs__player" data-mejsoptions='{"pluginPath": "<?php echo esc_attr(MILENIA_TEMPLATE_DIRECTORY_URI . '/assets/vendors/mediaelement/'); ?>","poster": "<?php echo esc_attr(get_the_post_thumbnail_url(get_the_ID())); ?>","hideVideoControlsOnLoad": true,"showPosterWhenPaused": true,"controlsTimeoutMouseEnter": 1000}'></video>
                </div>
            <?php endforeach; ?>
        <?php elseif(!$milenia_is_selfhosted && !empty($milenia_src_outer)) : ?>
            <div class="milenia-responsive-iframe">
              <?php echo wp_kses($milenia_src_outer, array(
				  'iframe' => array(
					  'src' => true
				  )
			  )); ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
<?php endif; ?>
