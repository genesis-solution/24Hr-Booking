<?php
if ( ! defined( 'ABSPATH' ) ) {
   die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

/* Adding share buttons to the end of the single post content.
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_add_share_buttons_to_single_post')) {
    function milenia_add_share_buttons_to_single_post($post, $check_option = true, $layout = 'inline') {
        if(!($post instanceof WP_Post)) return;
        if($check_option) $share_buttons_state = get_post_meta($post->ID, 'milenia-post-share-buttons-state', true);

        if(!$check_option || (isset($share_buttons_state) && $share_buttons_state == 'show' && $check_option)) :
        ?>
            <div class="milenia-section milenia-section--py-small">
                <div class="milenia-share<?php echo esc_attr($layout == 'inline' ? ' milenia-share--inline-sm' : ''); ?>">
                    <div class="milenia-share-caption"><?php esc_html_e('Share This', 'milenia-app-textdomain'); ?>:</div>
                    <div class="milenia-share-buttons">
                        <a href="#" target="_blank" title="<?php esc_attr_e('Facebook', 'milenia-app-textdomain'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-facebook milenia-sharer--facebook"
                            data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>"
                            data-sharer-thumbnail="<?php echo esc_url(wp_get_attachment_url(get_post_thumbnail_id($post->ID))); ?>"
                            data-sharer-title="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                            <span class="fab fa-facebook-f"></span> <?php esc_html_e('Facebook', 'milenia-app-textdomain'); ?>
                        </a>
                        <a href="#" target="_blank" title="<?php esc_attr_e('Twitter', 'milenia-app-textdomain'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-twitter milenia-sharer--twitter"
                            data-sharer-text="<?php echo esc_attr(get_the_title($post->ID)); ?>"
                            data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>">
                            <span class="fab fa-twitter"></span> <?php esc_html_e('Twitter', 'milenia-app-textdomain'); ?>
                        </a>
                        <a href="#" target="_blank" title="<?php esc_attr_e('Google+', 'milenia-app-textdomain'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-google-plus milenia-sharer--google-plus"
                            data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>">
                            <span class="fab fa-google-plus-g"></span> <?php esc_html_e('Google +', 'milenia-app-textdomain'); ?>
                        </a>
                        <a href="#" target="_blank" title="<?php esc_attr_e('Pinterest', 'milenia-app-textdomain') ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-pinterest milenia-sharer--pinterest"
                            data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>"
                            data-sharer-media="<?php echo esc_url(wp_get_attachment_url( get_post_thumbnail_id($post->ID) )); ?>"
                            data-sharer-description="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                            <span class="fab fa-pinterest-p"></span> <?php esc_html_e('Pinterest', 'milenia-app-textdomain'); ?>
                        </a>
                        <a href="mailto:#&subject=<?php echo urlencode(get_the_title($post->ID)); ?>&body=<?php echo esc_url(get_the_permalink($post->ID)); ?>" class="milenia-btn milenia-btn--icon">
                            <span class="fas fa-envelope"></span> <?php esc_html_e('Email to a Friend', 'milenia-app-textdomain'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif;
    }
}
add_action('milenia_single_post_after_content', 'milenia_add_share_buttons_to_single_post', 10, 3);

if(!function_exists('milenia_add_share_modal_to_single_blog_post')) {
    function milenia_add_share_modal_to_single_blog_post($post) {
        if(!($post instanceof WP_Post)) return;
        $share_buttons_state = get_post_meta($post->ID, 'milenia-post-share-buttons-state', true);
        if($share_buttons_state == 'show') : ?>
            <a href="#" data-arctic-modal="#share-modal-post-<?php echo esc_attr($post->ID); ?>" aria-haspopup="true" aria-controls="share-modal-post-<?php echo esc_attr($post->ID); ?>" aria-expanded="false" class="milenia-icon-link">
                <?php esc_html_e('Share This', 'milenia-app-textdomain'); ?><i class="icon icon-share2"></i>
            </a>

            <!-- - - - - - - - - - - - - - Share Modal - - - - - - - - - - - - - -->
            <div class="milenia-d-none">
                <div id="share-modal-post-<?php echo esc_attr($post->ID); ?>" aria-hidden="true" class="milenia-modal milenia-modal--share">
                    <button type="button" class="milenia-icon-btn arcticmodal-close">
                        <i class="icon icon-cross"></i>
                    </button>

                    <h3><?php esc_html_e('Share On:', 'milenia-app-textdomain'); ?></h3>

                    <div class="milenia-share-buttons">
                        <div class="milenia-share-buttons">
                            <a href="#" target="_blank" title="<?php esc_attr_e('Facebook', 'milenia-app-textdomain'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-facebook milenia-sharer--facebook"
                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>"
                                data-sharer-thumbnail="<?php echo esc_url(wp_get_attachment_url(get_post_thumbnail_id($post->ID))); ?>"
                                data-sharer-title="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                                <span class="fab fa-facebook-f"></span> <?php esc_html_e('Facebook', 'milenia-app-textdomain'); ?>
                            </a>
                            <a href="#" target="_blank" title="<?php esc_attr_e('Twitter', 'milenia-app-textdomain'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-twitter milenia-sharer--twitter"
                                data-sharer-text="<?php echo esc_attr(get_the_title($post->ID)); ?>"
                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>">
                                <span class="fab fa-twitter"></span> <?php esc_html_e('Twitter', 'milenia-app-textdomain'); ?>
                            </a>
                            <a href="#" target="_blank" title="<?php esc_attr_e('Google+', 'milenia-app-textdomain'); ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-google-plus milenia-sharer--google-plus"
                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>">
                                <span class="fab fa-google-plus-g"></span> <?php esc_html_e('Google +', 'milenia-app-textdomain'); ?>
                            </a>
                            <a href="#" target="_blank" title="<?php esc_attr_e('Pinterest', 'milenia-app-textdomain') ?>" class="milenia-btn milenia-btn--icon milenia-btn--scheme-pinterest milenia-sharer--pinterest"
                                data-sharer-url="<?php echo esc_url(get_the_permalink($post->ID)); ?>"
                                data-sharer-media="<?php echo esc_url(wp_get_attachment_url( get_post_thumbnail_id($post->ID) )); ?>"
                                data-sharer-description="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                                <span class="fab fa-pinterest-p"></span> <?php esc_html_e('Pinterest', 'milenia-app-textdomain'); ?>
                            </a>
                            <a href="mailto:#&subject=<?php echo urlencode(get_the_title($post->ID)); ?>&body=<?php echo esc_url(get_the_permalink($post->ID)); ?>" class="milenia-btn milenia-btn--icon">
                                <span class="fas fa-envelope"></span> <?php esc_html_e('Email to a Friend', 'milenia-app-textdomain'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- - - - - - - - - - - - - - End of Share Modal - - - - - - - - - - - - - -->
        <?php endif;
    }
}
add_action('milenia_single_post_footer_right_col', 'milenia_add_share_modal_to_single_blog_post', 10, 1);

/*	AJAX handlers
/* ---------------------------------------------------------------------- */
if(!function_exists('milenia_increment_item_likes')) {
	function milenia_increment_item_likes() {
		check_ajax_referer('milenia-functionality-ajax-nonce', 'AJAX_token');

		$response = array(
			'status' => 0,
			'likes' => null,
			'state' => null
		);

		if(isset($_POST['data']) && isset($_POST['data']['item_id']) && isset($_POST['data']['post_type'])) {


			switch($_POST['data']['post_type']) {
				case 'milenia-portfolio' :
					$MileniaPostRepository = new MileniaPostRepository('milenia-portfolio');
				break;

				case 'milenia-galleries' :
					$MileniaPostRepository = new MileniaGalleryRepository();
				break;
			}

			if(isset($MileniaPostRepository)) {

				$MileniaPostLiker = new MileniaPostLiker($MileniaPostRepository);

				if(!$MileniaPostLiker->isLiked($_POST['data']['item_id'])) {
					if($MileniaPostLiker->like($_POST['data']['item_id'])) {
						$response['status'] = 1;
						$response['likes'] = $MileniaPostLiker->getLikesCount($_POST['data']['item_id']);
						$response['state'] = 'liked';
					}
				}
				else {
					if($MileniaPostLiker->unlike($_POST['data']['item_id'])) {
						$response['status'] = 1;
						$response['likes'] = $MileniaPostLiker->getLikesCount($_POST['data']['item_id']);
						$response['state'] = 'unliked';
					}
				}
			}
		}

		echo wp_json_encode($response);
		wp_die();
	}
}

if(wp_doing_ajax()) {
	add_action('wp_ajax_increment_item_likes', 'milenia_increment_item_likes');
	add_action('wp_ajax_nopriv_increment_item_likes', 'milenia_increment_item_likes');
}

?>
