<?php
/**
 * Describes a base markup of the post.
 *
 * @package WordPress
 * @subpackage Milenia
 * @since Milenia 1.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

$milenia_post_archive_style = get_query_var('milenia-post-archive-style', 'milenia-entities--style-9');
$post_format = get_post_format( get_the_ID() );
$post_format = $post_format ? $post_format : 'standard';

?>
<div <?php post_class('milenia-grid-item'); ?>>
    <!-- - - - - - - - - - - - - - Entity - - - - - - - - - - - - - - - - -->
    <article <?php post_class(!has_post_thumbnail() ? 'milenia-entity milenia-entity--without-thumb' : 'milenia-entity'); ?>>

		<?php if(strpos($milenia_post_archive_style, 'milenia-entities--without-media') == false) : ?>
			<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
				<?php get_template_part('template-parts/post/post-format-media'); ?>
			<?php else : ?>
	        	<?php get_template_part('template-parts/post/post-format-media', $post_format); ?>
			<?php endif; ?>
		<?php endif; ?>

        <!-- - - - - - - - - - - - - - Entity Content - - - - - - - - - - - - - - - - -->
        <div class="milenia-entity-content milenia-aligner">
            <div class="milenia-aligner-outer">
                <div class="milenia-aligner-inner">
                    <header class="milenia-entity-header">
						<?php if($milenia_post_archive_style == 'milenia-entities--style-8' && in_array($post_format, array('quote', 'link', 'gallery', 'video', 'audio'))) : ?>
							<div class="milenia-entity-icon">
								<?php
									switch($post_format) {
										case 'quote' : ?>
											<i class="icon icon-quote-open"></i>
										<?php break;
										case 'link' : ?>
											<i class="icon icon-link2"></i>
										<?php break;
										case 'gallery' : ?>
											<i class="icon icon-pictures"></i>
										<?php break;
										case 'video' : ?>
											<i class="icon icon-film-play"></i>
										<?php break;
										case 'audio' : ?>
											<i class="icon icon-music-note3"></i>
										<?php break;
									}
								?>
							</div>
						<?php endif; ?>

						<div class="milenia-entity-meta">
							<div>
								<time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="milenia-entity-publish-date">
									<a href="<?php the_permalink(); ?>" class="milenia-ln--independent"><?php echo get_the_date(get_option('date_format')); ?></a>
								</time>
							</div>
							<div>
								<?php esc_html_e('by', 'milenia'); ?>
								<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author(); ?></a>
							</div>
							<?php if(milenia_has_post_terms(get_the_ID())) : ?>
								<div>
									<?php esc_html_e('in', 'milenia'); ?>
									<?php echo milenia_get_post_terms(get_the_ID()); ?>
								</div>
							<?php endif; ?>

							<?php if(is_user_logged_in()) : ?>
								<div><?php edit_post_link(__('Edit', 'milenia'), null, null, get_the_ID(), 'milenia-entity-edit-link'); ?></div>
							<?php endif; ?>
						</div>

						<?php if(is_sticky() || post_password_required()) : ?>
							<div class="milenia-entity-labels">
								<?php if(is_sticky() && in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
									<span class="milenia-entity-label"><?php esc_html_e('Sticky', 'milenia'); ?></span>
								<?php endif; if(post_password_required() && in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
									<span class="milenia-entity-label"><?php esc_html_e('Password protected', 'milenia'); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php //  if(!(strpos($milenia_post_archive_style, 'milenia-entities--without-media') == true && in_array(get_post_format(), array('link', 'audio', 'quote')))) : ?>
	                        <h2 class="milenia-entity-title">
								<?php if(is_sticky() && !in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
									<span class="milenia-entity-label"><?php esc_html_e('Sticky', 'milenia'); ?></span>
								<?php endif; if(post_password_required() && !in_array($milenia_post_archive_style, array('milenia-entities--style-7', 'milenia-entities--style-8'))) : ?>
									<span class="milenia-entity-label"><?php esc_html_e('Password protected', 'milenia'); ?></span>
								<?php endif; ?>
	                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	                        </h2>
						<?php // endif; ?>
                    </header>

					<?php if($milenia_post_archive_style != 'milenia-entities--style-8' && !(strpos($milenia_post_archive_style, 'milenia-entities--without-media') == true && in_array($post_format, array('link', 'audio', 'quote')))) : ?>
						<?php if(has_excerpt() || is_search() || strpos($milenia_post_archive_style, 'milenia-entities--list') != false ): ?>
							<div class="milenia-entity-body"><?php the_excerpt(); ?></div>
						<?php else : ?>
							<div class="milenia-entity-body"><?php the_content(''); ?></div>
							<?php wp_link_pages( array(
								'before' => '<div class="milenia-page-links"><span class="milenia-page-links-title">' . esc_html__( 'Pages:', 'milenia' ) . '</span>',
								'after' => '</div>',
								'link_before' => '<span>',
								'link_after' => '</span>'
							) ); ?>
						<?php endif; ?>
					<?php endif; ?>

					<?php if(strpos($milenia_post_archive_style, 'milenia-entities--without-media') == true && in_array($post_format, array('link', 'audio', 'quote'))) : ?>
						<?php get_template_part('template-parts/post/post-format-media', $post_format); ?>
					<?php endif; ?>

					<?php if((strpos($milenia_post_archive_style, 'milenia-entities--without-media') == true && in_array($post_format, array('', 'video'))) || strpos($milenia_post_archive_style, 'milenia-entities--without-media') == false) : ?>
	                    <footer class="milenia-entity-footer">
							<?php if($milenia_post_archive_style == 'milenia-entities--style-8') : ?>
								<a href="<?php the_permalink(); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-white"><?php esc_html_e('Read More', 'milenia'); ?></a>
							<?php else : ?>
								<a href="<?php the_permalink(); ?>" class="milenia-btn milenia-btn--link milenia-btn--scheme-primary"><?php esc_html_e('Read More', 'milenia'); ?></a>
							<?php endif; ?>
	                    </footer>
					<?php endif; ?>
                </div>
        	</div>
		</div>
		<!-- - - - - - - - - - - - - - End of Entity Content - - - - - - - - - - - - - - - - -->
	</article>
    <!-- - - - - - - - - - - - - - End of Entry - - - - - - - - - - - - - - - - -->
</div>
