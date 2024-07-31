<?php
 if ( ! defined( 'ABSPATH' ) ) {
 	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
 }

/* 	Google fonts url
/* --------------------------------------------------------------------- */
if(!function_exists('milenia_google_fonts_url')) {
	function milenia_google_fonts_url(array $fonts_data = array(), array $charsets = array()) {
		$fonts_url = '';
		$font_family = array();

		if($fonts_data) {
			foreach($fonts_data as $font => $font_weights) {
				/* Translators: If there are characters in your language that are not supported
	            by chosen font(s), translate this to 'off'. Do not translate into your own language. */
				$font_state = sprintf( _x( 'on', '%s font: on or off', 'milenia' ), $font );

				if ( 'off' !== $font_state ) {
	                $font_family[] = sprintf('%s:%s', $font, implode(',', $font_weights));
	            }
			}
		}

		if($font_family) {
			$fonts_url = add_query_arg(array(
				'family' => urlencode(implode('|', $font_family)),
				'subset' => urlencode(implode(',', $charsets))
			), 'https://fonts.googleapis.com/css');
		}

		return esc_url_raw($fonts_url);
	}
}

 /* Returns "portfolio" post type categories as an array.
 /* ---------------------------------------------------------------------- */
if(!function_exists('milenia_get_terms_as_array')) {
    function milenia_get_terms_as_array($taxonomy = 'category') {
        $terms_formatted = array();
        $terms = get_terms($taxonomy);

        if(!is_wp_error($terms) && is_array($terms) && count($terms)) {
            foreach($terms as $term) {
                $terms_formatted[$term->slug] = $term->name;
            }
        }

        return $terms_formatted;
    }
}

/* Returns portfolio projects as an array.
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_get_posts_as_array')) {
   function milenia_get_posts_as_array($post_type = 'post') {

       global $post;
       $posts_formatted = array();

       $posts = get_posts(array(
           'post_type' => $post_type,
           'numberposts' => -1
       ));

       if(!is_wp_error($posts) && is_array($posts) && count($posts)) {
           foreach($posts as $post) {
               setup_postdata($post);
               $posts_formatted[$post->ID] = $post->post_title;
           }
       }

       return $posts_formatted;
   }
}


/*	Blog Entry Categories
/* ---------------------------------------------------------------------- */
if ( !function_exists('milenia_get_post_terms') ) {
    function milenia_get_post_terms($id = null, $taxonomy = 'category', $excepts_ids = array()) {
        if( is_null($id) || !is_numeric($id)) return;

        $terms = wp_get_object_terms(intval($id), $taxonomy);
		$terms_template = array();

		if(is_array($terms)) {
			$terms_template[] = '<div class="milenia-entity-categories milenia-list--unstyled">';

			foreach($terms as $index => $term) {
				if(in_array($term->term_id, $excepts_ids)) continue;

				$term_link = get_term_link( $term );
				if( is_wp_error($term_link) ) continue;

	            $terms_template[] = '<span><a href="' .esc_url( $term_link ). '">' . esc_html( $term->name ) . '</a></span>';
			}

			$terms_template[] = '</div>';
		}

        return implode("\r\t\n", $terms_template);
    }
}

if( !function_exists('milenia_has_post_terms') )
{
	function milenia_has_post_terms($id = null, $taxonomy = 'category', $excepts_ids = array()) {
		if(is_null($id) || !is_numeric($id)) return false;
		$terms = get_the_terms(intval($id), $taxonomy);

		if(!is_array($terms) || !count($terms)) return false;

		foreach($terms as $index => $term) {
			if(in_array($term->cat_ID, $excepts_ids)) unset($terms[$index]);
		}

		return count($terms);
	}
}

/* 	Filter Hook for Comments
/* --------------------------------------------------------------------- */
if ( !function_exists('milenia_review_comment')) {

	function milenia_review_comment($comment, $args, $depth) {
        global $RoomsReviewer;
		$avatar = get_avatar($comment, 70, '', esc_html(get_comment_author()));

		$thread_comments_depth = intval(get_option('thread_comments_depth'));

		$comment_classes = array('comment');
		$author_url = get_comment_author_url();

		if($avatar === false) array_push($comment_classes, 'comment-has-not-avatar');
		if(isset($args['has_children']) && $args['has_children'] == 1 && $depth < $thread_comments_depth) array_push($comment_classes, 'comment-has-children');
	?>

		<li <?php comment_class(implode(' ', $comment_classes)); ?> id="comment-<?php echo comment_ID(); ?>">
			<div class="comment-body">
				<?php if(!empty($avatar)) : ?>
					<div class="comment-author-avatar">
						<?php if(!empty($author_url)) : ?><a href="<?php echo esc_url($author_url); ?>" class="milenia-ln--independent"><?php endif; ?>
							<?php echo wp_kses_post($avatar); ?>
						<?php if(!empty($author_url)) : ?></a><?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="comment-author-info">
					<cite class="fn milenia-text-color--dark">
						<?php if(!empty($author_url)) : ?><a class="comment-author-name" href="<?php echo esc_url($author_url); ?>"><?php endif; ?>
							<?php echo get_comment_author(); ?>
						<?php if(!empty($author_url)) : ?></a><?php endif; ?>
					</cite>
                    <div class="comment-meta row milenia-columns-aligner--edges-md align-items-center">
                        <div class="col-md-8">
                            <time datetime="<?php echo esc_attr(get_comment_date('c',get_comment_ID())); ?>"><?php
    							printf('%s %s %s', get_comment_date('F j, Y', get_comment_ID()), esc_html__('at', 'milenia'), get_comment_date('H:m', get_comment_ID()));
    						?></time>
                        </div>

                        <?php if(isset($RoomsReviewer)) : ?>
                            <div class="col-md-4">
                                <div data-estimate="<?php echo esc_attr($RoomsReviewer->getCommentRating(get_comment_ID())); ?>" class="milenia-rating milenia-rating--independent"></div>
                            </div>
                        <?php endif; ?>
                    </div>

					<div class="comment-content"><?php comment_text(); ?></div>


					<div class="comment-actions">
						<?php if(current_user_can('moderate_comments')) : ?>
							<?php edit_comment_link(' ' . esc_html__('Edit', 'milenia'),'  ','') ?>
						<?php endif; ?>
					</div>

					<?php if($comment->comment_approved == '0') : ?>
						<hr>
						<p><?php esc_html_e('Your comment is awaiting moderation.', 'milenia'); ?></p>
					<?php endif; ?>
				</div>
			</div>

	<?php
	}
}

/* 	Sections
/* --------------------------------------------------------------------- */

if (!function_exists('milenia_mphb_render_sections')) {
	function milenia_mphb_render_sections() {
		return array(
			'description' => array(
				'action' => array('milenia_mphb_render_single_room_type_description', 'milenia_mphb_render_single_room_type_floor_plan'),
				'arg' => array(10, 0),
				'arg2' => array(10, 0),
			),
			'amenities' => array(
				'action' => 'milenia_mphb_render_single_room_type_amenities',
				'classes' => array('milenia-section--py-medium', 'milenia-colorizer--scheme-primary', 'milenia-section--stretched'),
				'arg' => array(3, 8),
				'arg2' => array(2, 8)
			),
			'rates' => array(
				'action' => 'milenia_mphb_render_single_room_type_rates_table',
				'classes' => array('milenia-colorizer--scheme-lightest', 'milenia-section--stretched'),
				'arg' => array(10, 0),
				'arg2' => array(10, 0)
			),
			'reviews' => array(
				'action' => 'milenia_mphb_render_single_room_type_reviews',
				'classes' => array('milenia-section--py-medium'),
				'arg' => array(10, 0),
				'arg2' => array(2),
			),
			'availability' => array(
				'action' => 'milenia_mphb_render_single_room_type_availability',
				'classes' => array('milenia-colorizer--scheme-lightest', 'milenia-form--fields-white', 'milenia-section--stretched'),
				'arg' => array(10, 0),
				'arg2' => array(10, 0),
			),
			'reservation' => array(
				'action' => 'milenia_mphb_render_single_room_type_reservation_form',
				'classes' => array('milenia-section--stretched'),
				'arg' => array(10, 0),
				'arg2' => array(10, 0),
			)
		);
	}
}

 ?>
