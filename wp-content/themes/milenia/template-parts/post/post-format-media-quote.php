<?php
/**
 * Describes media area of the quote post.
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

$milenia_quote = $Milenia->getThemeOption('milenia-post-quote', '', array('object_id' => get_the_ID()));
$milenia_quote_author = $Milenia->getThemeOption('milenia-post-quote-author', '', array('object_id' => get_the_ID()));
$milenia_quote_author_link = $Milenia->getThemeOption('milenia-post-quote-author-link', '', array('object_id' => get_the_ID()));
$milenia_quote_author_link_target = $Milenia->getThemeOption('milenia-post-quote-author-link-target', '1', array('object_id' => get_the_ID()));
$milenia_quote_author_link_nofollow = $Milenia->getThemeOption('milenia-post-quote-author-link-nofollow', '0', array('object_id' => get_the_ID()));

if(!empty($milenia_quote) || !empty($milenia_quote_author)) : ?>
    <!-- - - - - - - - - - - - - - Entity Media - - - - - - - - - - - - - - - - -->
    <div class="milenia-entity-media">
        <blockquote>
			<div class="milenia-entity-quote-inner">
				<?php echo wp_kses_post(wpautop($milenia_quote)); ?>

				<?php if(!empty($milenia_quote_author)) : ?>
	            	<cite>
						<?php if(!empty($milenia_quote_author_link)) : ?>
							<a href="<?php echo esc_url($milenia_quote_author_link); ?>"
							   target="<?php echo esc_attr( $milenia_quote_author_link_target == '1' ? '_blank' : '_self' ); ?>"
							   <?php if($milenia_quote_author_link_nofollow == '1'): ?>rel="nofollow"<?php endif;?>>
						<?php endif; ?>
							<?php echo wp_kses_post($milenia_quote_author); ?>
						<?php if(!empty($milenia_quote_author_link)) : ?>
							</a>
						<?php endif; ?>
					</cite>
				<?php endif; ?>
			</div>
        </blockquote>
    </div>
    <!-- - - - - - - - - - - - - - End of Entry Media - - - - - - - - - - - - - - - - -->
<?php endif; ?>
