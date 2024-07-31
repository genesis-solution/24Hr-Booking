<?php
/**
* The template for displaying comments
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

// These post types have their own implementation of comments
if(is_singular(array('mphb_room_type'))) return;

?>

<?php if( have_comments() ) : ?>
	<!-- - - - - - - - - - - - - - Comments - - - - - - - - - - - - - - - - -->
	<section id="comments" class="milenia-section milenia-section--py-medium">
		<h3><?php printf( _nx( 'Comment (1)', 'Comments (%1$s)', get_comments_number(), 'comments title', 'milenia' ),
				number_format_i18n( get_comments_number() ), get_the_title() ); ?></h3>

		<ol class="comments-list <?php echo esc_attr(sprintf('comments-list--max-depth-%d', get_option('thread_comments_depth'))); ?>">
		    <?php wp_list_comments(array(
					'short_ping'  => true,
					'avatar_size' => 70,
					'callback' => 'milenia_output_comment'
			)); ?>
		</ol>

		<?php if (get_comment_pages_count() > 1 && get_option('page_comments')): ?>
			<nav class="milenia-pagination milenia-pagination--unlisted">
				<?php paginate_comments_links(array(
					'prev_text' => esc_html__('Previous comments', 'milenia'),
					'next_text' => esc_html__('Next comments', 'milenia')
				)); ?>
			</nav>
		<?php endif; ?>
	</section>
	<!-- - - - - - - - - - - - - - End of Comments - - - - - - - - - - - - - - - - -->
<?php endif; ?>

<div class="milenia-section milenia-medium">
	<?php if(comments_open()) : ?>
	<!-- - - - - - - - - - - - - - Comment Form - - - - - - - - - - - - - - - - -->
	    <?php
	        $commenter = wp_get_current_commenter();
			$req       = get_option( 'require_name_email' );
			$aria_req_safe  = ( $req ? " aria-required='true'" : '' );

			$comment_form_args = array(
				'fields' => apply_filters('comment_form_default_fields', array(
						'author' => '<div class="form-group">
										<div class="form-col">
											<label for="comment-form-author">'.esc_html__('Your Name', 'milenia').' <span class="milenia-required-sign">*</span></label>
											<input id="comment-form-author" name="author" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" required />
										</div>
									</div>',
						'email'  => '<div class="form-group">
										<div class="form-col">
											<label for="comment-form-email">'.esc_html__('Your Email', 'milenia').' <span class="milenia-required-sign">*</span></label>
											<input id="comment-form-email" name="email" class="form-control" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" required />
										</div>
									</div>',
						'website' => '<div class="form-group">
										<div class="form-col">
											<label for="comment-form-url">'.esc_html__('Website', 'milenia').'</label>
											<input id="comment-form-url" name="url" class="form-control" type="text" value="' . esc_attr(  $commenter['comment_author_url'] ) . '" />
										</div>
									</div>'
				)),
				'comment_field' =>  '<div class="form-group">
										<div class="form-col">
											<label for="comment-form-comment">'.esc_html__('Comment', 'milenia').' <span class="milenia-required-sign">*</span></label>
											<textarea id="comment-form-comment" name="comment" rows="5" required></textarea>
										</div>
									</div>',
				'comment_notes_before' => '<small class="form-caption">'.sprintf(esc_html__('Your email address will not be published. Fields marked with an %s are required.', 'milenia'), '<span class="milenia-required-sign">*</span>').'</small>',
				'comment_notes_after'  => '',
				'class_submit'         => 'milenia-btn',
	            'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
				'submit_field' 		   => '<div class="form-group"><div class="form-col">%1$s %2$s</div></div>',
				'title_reply_to'       => esc_html__( 'Leave a reply to %s', 'milenia' ),
				'cancel_reply_link'    => esc_html__( 'Cancel', 'milenia' ),
				'title_reply'          => esc_html__( 'Leave a Comment', 'milenia' ),
				'title_reply_before'   => '<h3>',
				'title_reply_after'    => '</h3>'
			);

	        comment_form($comment_form_args);
	    ?>
	<!-- - - - - - - - - - - - - - End of Comment Form - - - - - - - - - - - - - - - - -->
	<?php elseif(get_comments_number() && post_type_supports( get_post_type(), 'comments' )):  ?>
		<i><?php esc_html_e('Comments are closed.', 'milenia'); ?></i>
	<?php endif; ?>
</div>
