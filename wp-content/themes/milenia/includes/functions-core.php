<?php
// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

// Debugging helper

if ( ! function_exists('t_print_r') ) {
	function t_print_r($arr) {
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
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

if( !function_exists('milenia_has_post_terms') ) {
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

/* 	Isotope filter
/* --------------------------------------------------------------------- */
if( !function_exists('milenia_display_filter') ) {
	function milenia_display_filter($posts = array(), $filter_element_id, $default_caption_for_all_posts = 'All', $taxonomy = 'category', $post_id_key = null) {
		$terms = array();

		foreach($posts as $post) {
			if($post instanceof WP_Post) {
				$post_terms = get_the_terms($post->ID, $taxonomy);
			}
			elseif(is_array($post) && isset($post_id_key)) {
				$post_terms = get_the_terms($post[$post_id_key], $taxonomy);
			}


			if(isset($post_terms) && is_array($post_terms) && count($post_terms)) {
				foreach($post_terms as $term) {
					if(!in_array($term, $terms)) array_push($terms, $term);
				}
			}
		}

		?>

			<nav class="milenia-filter-wrap text-center">
				<ul id="<?php echo esc_attr($filter_element_id); ?>" class="milenia-filter milenia-list--unstyled">
					<?php printf('<li><a href="#" class="milenia-active" data-filter="%s">%s</a></li>', '*', esc_html($default_caption_for_all_posts)); ?>
					<?php
						if(count($terms)) {
							foreach($terms as $item) {
								printf('<li><a href="#" data-filter="%s">%s</a></li>', '.' . $taxonomy . '-' . esc_attr($item->slug), esc_html(ucfirst($item->name)));
							}
						}
					?>
				</ul>
			</nav>
		<?php
	}
}

/* 	Filter Hook for Comments
/* --------------------------------------------------------------------- */
if ( !function_exists('milenia_output_comment')) {

	function milenia_output_comment($comment, $args, $depth) {
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
					<div class="comment-meta milenia-columns-aligner--edges-md align-items-center">
						<time datetime="<?php echo esc_attr(get_comment_date('c',get_comment_ID())); ?>"><?php
							printf('%s %s %s', get_comment_date('F j, Y', get_comment_ID()), esc_html__('at', 'milenia'), get_comment_date('H:m', get_comment_ID()));
						?></time>
					</div>

					<div class="comment-content"><?php comment_text(); ?></div>


					<div class="comment-actions">
						<?php if(current_user_can('moderate_comments')) : ?>
							<?php edit_comment_link(' ' . esc_html__('Edit', 'milenia'),'  ','') ?>
						<?php endif; ?>

						<?php if(current_user_can('moderate_comments') && $depth < $thread_comments_depth) : ?>
							|
						<?php endif; ?>

						<?php echo get_comment_reply_link( array(
							'reply_text' => esc_html__('Reply', 'milenia'),
							'depth' => $depth,
							'max_depth' => $args['max_depth']
						)); ?>
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

/* Reorder fields of the comment form
/* --------------------------------------------------------------------- */
if( !function_exists('milenia_reorder_comment_form_fields') ) {
	function milenia_reorder_comment_form_fields( $fields ) {
		$comment_field = $fields['comment'];
		unset( $fields['comment'] );
		$fields['comment'] = $comment_field;
		return $fields;
	}
}
add_filter( 'comment_form_fields', 'milenia_reorder_comment_form_fields' );


if( !function_exists('milenia_logo') ) {

	/**
	 * Displays the website logo.
	 *
	 * @param  string  $css_prefix
	 * @param  array   $components
	 * @return void
	 */
	function milenia_logo($type = 'vertical', $color_scheme = 'dark') {
		global $Milenia;

		$logo = $Milenia->getThemeOption('milenia-logo', MILENIA_TEMPLATE_DIRECTORY_URI . '/assets/images/logo-brown.png', array(
			'overriden_by' => 'milenia-page-header-logo',
			'depend_on' => array('key' => 'milenia-page-header-state', 'value' => 0)
		));

		$logo_hidpi = $Milenia->getThemeOption('milenia-logo-hidpi', MILENIA_TEMPLATE_DIRECTORY_URI . '/assets/images/logo-brown@2x.png', array(
			'overriden_by' => 'milenia-page-header-logo-hidpi',
			'depend_on' => array('key' => 'milenia-page-header-state', 'value' => 0)
		));

		if (is_array($logo) && isset($logo['full_url'])) {
			$logo = $logo['full_url'];
		} elseif(is_array($logo) && isset($logo['url'])) {
			$logo = $logo['url'];
		}

		if (is_array($logo_hidpi) && isset($logo_hidpi['full_url'])) {
			$logo_hidpi = $logo_hidpi['full_url'];
		} elseif(is_array($logo_hidpi) && isset($logo_hidpi['url'])) {
			$logo_hidpi = $logo_hidpi['url'];
		}

		if( !empty($logo) ) : ?>
			<a href="<?php echo esc_url(home_url('/')); ?>" class="milenia-ln--independent milenia-logo">
				<img src="<?php echo esc_url(str_replace( array( 'http:', 'https:' ), '', $logo)); ?>" srcset="<?php echo esc_url( str_replace( array( 'http:', 'https:' ), '', $logo_hidpi ) ) ?> 2x" alt="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>">
			</a>
		<?php endif;
	}
}

/* 	Page loader
/* --------------------------------------------------------------------- */
if( !function_exists('milenia_show_page_loader') ) {
	function milenia_show_page_loader() {
		global $Milenia;
		$page_loader_state = $Milenia->getThemeOption('page-loader-state', 0);
		if($page_loader_state == 1) : ?>
			<div class="milenia-preloader"></div>
		<?php endif;
	}
}

add_action('milenia_body_prepend', 'milenia_show_page_loader');

/* 	Breadcrumbs
/* --------------------------------------------------------------------- */
if ( !function_exists('milenia_breadcrumbs') ) {
	function milenia_breadcrumbs() {
		if( function_exists( 'bcn_display' ) ) {
			echo '<nav class="milenia-breadcrumb-path">';
			bcn_display();
			echo '</nav>';
		}
	}
}


/* 	Pagination
/* --------------------------------------------------------------------- */
if(!function_exists('milenia_pagination')) {
	function milenia_pagination($args = array(), $additional_classes = array()) {
		if(isset($args['type'])) unset($args['type']);

		$defaults = array(
			'prev_next' => true,
			'type' => 'array',
			'prev_text' => esc_html__('Prev', 'milenia'),
			'next_text' => esc_html__('Next', 'milenia')
		);

		$pagination = paginate_links(array_merge($defaults, $args));

		if(is_array($pagination)) : ?>
			<nav>
				<ul class="milenia-pagination milenia-list--unstyled<?php if(!empty($additional_classes)) : ?> <?php echo implode(' ', array_map('esc_attr', $additional_classes));?><?php endif; ?>">
					<?php foreach($pagination as $page) : ?>
						<li><?php echo wp_kses($page, array(
							'span' => array(
								'style' => true,
								'class' => true
							),
							'a' => array(
								'href' => true,
								'class' => true,
								'style' => true
							)
						)); ?></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif;
	}
}

/*	Social links
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_social_links')) {
	function milenia_social_links($type = 'milenia-style-1', $links = array('facebook', 'twitter', 'instagram', 'youtube', 'flickr')) {
		global $Milenia;

		$links_prepared = array();

		foreach($links as $link) {
			$link_url = $Milenia->getThemeOption(sprintf('milenia-social-links-%s', $link));

			if(empty($link_url)) continue;

			$links_prepared[$link] = array(
				'url' => $link_url,
				'icon-class' => sprintf('fa-%s', $link)
			);
		}

		if(count($links_prepared)) :
		?>
			<ul class="milenia-social-networks <?php echo sanitize_html_class($type); ?>">
				<?php foreach($links_prepared as $link_prepared) : ?>
					<li><a href="<?php echo esc_url($link_prepared['url']) ?>" target="_blank"><i class="fa <?php echo sanitize_html_class($link_prepared['icon-class']); ?>"></i></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif;
	}
}

/*	Navigation
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_navigation')) {
	function milenia_navigation($location = 'primary', $args = array()) {
		$defaults = array(
			'theme_location' => $location,
			'menu' => 'primary',
			'menu_class' => 'milenia-navigation',
			'container' => 'nav',
			'container_class' => 'milenia-navigation-container'
		);

		if(has_nav_menu($location)) {
			wp_nav_menu(array_merge($defaults, $args));
		}
	}
}

/*	Dump helper
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_dump')) {
	function milenia_dump($value) {
		echo "<pre>";
		if(is_array($value)) print_r($value);
		else var_dump($value);
		echo "</pre>";
	}
}

/*	AJAX handlers
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_show_item_info_modal')) {
	function milenia_show_item_info_modal() {
		check_ajax_referer('milenia-ajax-nonce', 'AJAX_token');

		$data = $_GET['data'];

		if(!isset($data['post_type'])) {
			die();
		}

		ob_start();

		get_template_part('template-parts/modals/milenia-modal-item-info', preg_replace('/milenia-/', '', $data['post_type']));

		echo ob_get_clean();
		wp_die();

	}
}

if(wp_doing_ajax()) {
	add_action('wp_ajax_show_item_info_modal', 'milenia_show_item_info_modal');
	add_action('wp_ajax_nopriv_show_item_info_modal', 'milenia_show_item_info_modal');
}


/*  Checks whether post has pingbacks and trackbacks.
/* ---------------------------------------------------------------------- */
if( !function_exists( 'milenia_has_post_pings' ) ) {
	function milenia_has_post_pings( $post_id = null ) {
		if( is_null($post_id) ) return false;

		$comments = get_comments(array(
			'status' => 'approve',
			'type' => 'pings',
			'post_id' => $post_id
		));

		return boolval(count($comments));
	}
}

/*  Language switcher
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_language_switcher')) {
	function milenia_language_switcher() {
		do_action( 'wpml_language_switcher');
	}
}

/* 	RWMB fallback
/* --------------------------------------------------------------------- */
if ( ! function_exists( 'rwmb_get_value' ) ) {
    function rwmb_get_value( $key, $args = '', $post_id = null ) {
        return false;
    }
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

?>
