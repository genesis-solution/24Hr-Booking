<?php
class ImagesWidget extends WP_Widget
{
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_images',
			'description' => esc_html__('Set of images.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_images', esc_html__('[Milenia] Images', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
		$defaults = array(
            'title' => '',
			'nofollow' => '',
			'target_blank' => '',
			'large_offset' => '',
			'image' => array()
        );

        $instance = wp_parse_args( (array) $instance, $defaults );

        echo $args['before_widget'];
			if(!empty($instance['title']))
			{
	            echo $args['before_title'];
	                echo $instance['title'];
	            echo $args['after_title'];
			}

		if(is_array($instance['image']) && !empty($instance['image'])) : ?>
			<ul class="milenia-sponsors milenia-list--unstyled<?php if($instance['large_offset']) : ?> milenia-sponsors--large<?php endif; ?>">
				<?php foreach($instance['image'] as $image_id => $image_link) : ?>
					<?php if(isset($image_link) && !empty($image_link)) : ?>
						<li><a href="<?php echo esc_url($image_link); ?>" <?php if(intval($instance['nofollow'])) : ?> rel="nofollow"<?php endif; ?> <?php if(intval($instance['target_blank'])) : ?> target="_blank"<?php endif; ?> class="milenia-ln--independent"><?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?></a></li>
					<?php else : ?>
						<li><?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endif;

        echo $args['after_widget'];
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
			'nofollow' => '',
			'target_blank' => '',
			'large_offset' => '',
			'image' => array()
        );

        $instance = wp_parse_args( (array) $instance, $defaults );?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
            </p>

			<?php if(isset($instance['image']) && !empty($instance['image'])) : ?>
				<div class="milenia-upload-btn-container">
					<button type="button" style="display: none;" data-multiple="true" class="button milenia-upload-btn-select"><?php esc_html_e('Select Images', 'milenia-app-textdomain'); ?></button>
					<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('image') ); ?>" class="milenia-upload-btn-input" value="<?php echo esc_attr($instance['image']); ?>">
					<div class="milenia-upload-btn-images"
						data-id="<?php esc_attr_e( $this->get_field_id('image')); ?>"
						data-label="<?php esc_attr_e('Link to:', 'milenia-app-textdomain'); ?>"
						data-name="<?php esc_attr_e( $this->get_field_name('image') ); ?>">
						<?php foreach($instance['image'] as $image_id => $image_link) : ?>
							<figure class="milenia-upload-btn-image">
								<?php echo wp_get_attachment_image(intval($image_id), 'full'); ?>
								<div class="milenia-upload-btn-image-controls">
									<div class="milenia-upload-btn-image-control">
										<label for="<?php printf('%s-%s-href',esc_attr( $this->get_field_id('image')), esc_attr($image_id)); ?>"><?php esc_html_e('Link to:', 'milenia-app-textdomain'); ?></label>
										<input type="text" class="widefat" id="<?php printf('%s-%s-href',esc_attr( $this->get_field_id('image')), esc_attr($image_id)); ?>" name="<?php echo esc_attr( $this->get_field_name('image') ); ?>[<?php echo esc_attr($image_id); ?>]" <?php if(isset($instance['image']) && isset($image_link)) : ?> value="<?php echo esc_attr($image_link); ?>" <?php endif; ?>>
									</div>
								</div>
							</figure>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button milenia-upload-btn-remove"><?php esc_html_e('Remove Images', 'milenia-app-textdomain'); ?></button>
				</div>
			<?php else : ?>
				<div class="milenia-upload-btn-container">
					<button type="button" data-multiple="true" class="button milenia-upload-btn-select"><?php esc_html_e('Select Images', 'milenia-app-textdomain'); ?></button>
					<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('image') ); ?>" class="milenia-upload-btn-input">
					<div class="milenia-upload-btn-images"
						data-id="<?php esc_attr_e( $this->get_field_id('image')); ?>"
						data-label="<?php esc_attr_e('Link to:', 'milenia-app-textdomain'); ?>"
						data-name="<?php esc_attr_e( $this->get_field_name('image') ); ?>"></div>
					<button type="button" class="button milenia-upload-btn-remove" style="display: none;"><?php esc_html_e('Remove Images', 'milenia-app-textdomain'); ?></button>
				</div>
			<?php endif; ?>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['nofollow']); ?> id="<?php echo esc_attr( $this->get_field_id('nofollow') ); ?>" name="<?php echo esc_attr( $this->get_field_name('nofollow') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('nofollow') ); ?>"><?php esc_html_e('Add nofollow option to the links', 'milenia-app-textdomain'); ?></label>
            </p>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['target_blank']); ?> id="<?php echo esc_attr( $this->get_field_id('target_blank') ); ?>" name="<?php echo esc_attr( $this->get_field_name('target_blank') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('target_blank') ); ?>"><?php esc_html_e('Open links in a new window', 'milenia-app-textdomain'); ?></label>
            </p>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['large_offset']); ?> id="<?php echo esc_attr( $this->get_field_id('large_offset') ); ?>" name="<?php echo esc_attr( $this->get_field_name('large_offset') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('large_offset') ); ?>"><?php esc_html_e('Increase x margin between items', 'milenia-app-textdomain'); ?></label>
            </p>
        <?php
    }
}
?>
