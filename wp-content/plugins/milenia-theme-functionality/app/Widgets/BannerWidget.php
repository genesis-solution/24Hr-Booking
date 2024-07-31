<?php
class BannerWidget extends WP_Widget
{
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_banner',
			'description' => esc_html__('Creates a banner.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_banner', esc_html__('[Milenia] Banner', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
            'banner_title' => '',
            'text' => '',
			'image' => '',
			'link_text' => '',
			'link_url' => '',
			'nofollow' => '',
			'target_blank' => ''
		));

        echo $args['before_widget'];
			if(!empty($instance['title'])) {
	            echo $args['before_title'];
	                echo $instance['title'];
	            echo $args['after_title'];
			}
			?>

			<div class="milenia-banners milenia-banners--style-2">
				<!--================ Banner ================-->
				<article class="milenia-banner">
					<?php if(!empty($instance['image'])) : ?>
						<div data-bg-image-src="<?php echo esc_url(wp_get_attachment_image_url(intval($instance['image']), 'full')); ?>" class="milenia-banner-media">
							<?php echo wp_get_attachment_image(intval($instance['image']), 'full'); ?>
						</div>
					<?php endif; ?>

					<div class="milenia-banner-content milenia-aligner">
						<div class="milenia-aligner-outer">
							<div class="milenia-aligner-inner">
								<?php if(!empty($instance['banner_title']) || !empty($instance['text'])) : ?>
								<div class="milenia-banner-text">
									<?php if(!empty($instance['banner_title'])) : ?>
										<h2 class="milenia-banner-title milenia-color--unchangeable"><?php echo esc_html($instance['banner_title']); ?></h2>
									<?php endif; ?>
									<?php if(!empty($instance['text'])) : ?>
										<p><?php echo esc_html($instance['text']); ?></p>
									<?php endif; ?>
								</div>
								<?php endif; ?>

								<?php if(!empty($instance['link_text']) && !empty($instance['link_url'])) : ?>
								<div class="milenia-banner-actions">
									<a href="<?php echo esc_url($instance['link_url']); ?>" <?php if(!empty($instance['nofollow'])) : ?>rel="nofollow"<?php endif; ?> <?php if(!empty($instance['target_blank'])) : ?>target="_blank"<?php endif; ?> class="milenia-btn milenia-btn--link milenia-btn--scheme-inherit"><?php echo esc_html($instance['link_text']); ?></a>
								</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</article>
				<!--================ End of Banner ================-->
			</div>


        <?php echo $args['after_widget'];
	}

    /**
     * Creates the widget form in the admin panel.
     *
     * @param array $instance
     * @return void
     */
    public function form( $instance )
    {
        $defaults = array(
            'title' => '',
            'banner_title' => '',
            'text' => '',
			'image' => '',
			'link_text' => '',
			'link_url' => '',
			'nofollow' => '',
			'target_blank' => ''
        );

        $instance = wp_parse_args( (array) $instance, $defaults );?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('banner_title') ); ?>"><?php esc_html_e('Banner title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('banner_title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('banner_title') ); ?>" value="<?php if(isset($instance['banner_title'])) echo esc_attr($instance['banner_title']); ?>">
            </p>

			<p>
                <label for="<?php echo esc_attr( $this->get_field_id('text') ); ?>"><?php esc_html_e('Text:', 'milenia-app-textdomain'); ?></label>
				<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id('text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('text') ); ?>" rows="3"><?php echo esc_html($instance['text']); ?></textarea>
            </p>

			<p>
                <label for="<?php echo esc_attr( $this->get_field_id('link_text') ); ?>"><?php esc_html_e('Link Text:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('link_text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('link_text') ); ?>" value="<?php if(isset($instance['link_text'])) echo esc_attr($instance['link_text']); ?>">
            </p>

			<p>
                <label for="<?php echo esc_attr( $this->get_field_id('link_url') ); ?>"><?php esc_html_e('Link URL:', 'milenia-app-textdomain'); ?></label>
                <input type="url" class="widefat" id="<?php echo esc_attr( $this->get_field_id('link_url') ); ?>" name="<?php echo esc_attr( $this->get_field_name('link_url') ); ?>" value="<?php if(isset($instance['link_url'])) echo esc_attr($instance['link_url']); ?>">
            </p>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['nofollow']); ?> id="<?php echo esc_attr( $this->get_field_id('nofollow') ); ?>" name="<?php echo esc_attr( $this->get_field_name('nofollow') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('nofollow') ); ?>"><?php esc_html_e('Nofollow', 'milenia-app-textdomain'); ?></label>
            </p>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['target_blank']); ?> id="<?php echo esc_attr( $this->get_field_id('target_blank') ); ?>" name="<?php echo esc_attr( $this->get_field_name('target_blank') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('target_blank') ); ?>"><?php esc_html_e('Open link in a new window', 'milenia-app-textdomain'); ?></label>
            </p>

            <p>
				<?php if(isset($instance['image']) && !empty($instance['image'])) : ?>
					<div class="milenia-upload-btn-container">
						<button type="button" style="display: none;" data-multiple="false" class="button milenia-upload-btn-select"><?php esc_html_e('Select Image', 'milenia-app-textdomain'); ?></button>
						<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('image') ); ?>" class="milenia-upload-btn-input" value="<?php echo esc_attr($instance['image']); ?>">
						<div class="milenia-upload-btn-images">
							<?php echo wp_get_attachment_image(intval($instance['image']), 'thumbnail'); ?>
						</div>
						<button type="button" class="button milenia-upload-btn-remove"><?php esc_html_e('Remove Image', 'milenia-app-textdomain'); ?></button>
					</div>
				<?php else : ?>
					<div class="milenia-upload-btn-container">
						<button type="button" data-multiple="false" class="button milenia-upload-btn-select"><?php esc_html_e('Select Image', 'milenia-app-textdomain'); ?></button>
						<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('image') ); ?>" class="milenia-upload-btn-input">
						<div class="milenia-upload-btn-images"></div>
						<button type="button" class="button milenia-upload-btn-remove" style="display: none;"><?php esc_html_e('Remove Image', 'milenia-app-textdomain'); ?></button>
					</div>
				<?php endif; ?>
            </p>
        <?php
    }
}
?>
