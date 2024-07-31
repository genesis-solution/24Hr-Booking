<?php
class AboutWidget extends WP_Widget
{
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_about',
			'description' => esc_html__('Set of images.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_about', esc_html__('[Milenia] About', 'milenia-app-textdomain'), $widget_ops );
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
			'address' => '',
			'phone' => '',
			'fax' => '',
			'email' => '',
            'google_map_link' => '',
            'logo' => ''
        );

        $instance = wp_parse_args( (array) $instance, $defaults );

        echo $args['before_widget'];

            ?>

            <div class="milenia-info-widget">
                <?php if(!empty($instance['logo'])) : ?>
                    <div class="milenia-info-widget-logo">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="milenia-ln--independent">
                            <?php echo wp_get_attachment_image(intval($instance['logo']), 'full'); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="milenia-grid milenia-grid--cols-3">
                    <?php if(!empty($instance['address'])) : ?>
                        <div class="milenia-grid-item">
                            <address>
                                <span class="milenia-text-color--contrast"><?php esc_html_e('Address:', 'milenia-app-textdomain'); ?></span>
                                <?php echo esc_html($instance['address']); ?>
                            </address>

                            <?php if(!empty($instance['google_map_link'])) : ?>
                                <a href="<?php echo esc_url($instance['google_map_link']); ?>" target="_blank" class="milenia-uppercased-link"><?php esc_html_e('Get Direction', 'milenia-app-textdomain'); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($instance['phone']) || !empty($instance['fax'])) : ?>
                        <div class="milenia-grid-item">
                            <address>
                                <span class="milenia-text-color--contrast"><?php esc_html_e('Phone:', 'milenia-app-textdomain'); ?></span>
                                <?php echo esc_html($instance['phone']); ?>
                                <br>
                                <span class="milenia-text-color--contrast"><?php esc_html_e('Fax:', 'milenia-app-textdomain'); ?></span>
                                <?php echo esc_html($instance['fax']); ?>
                            </address>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($instance['email'])) : ?>
                        <div class="milenia-grid-item milenia-widget--email">
                            <address>
                                <span class="milenia-text-color--contrast"><?php esc_html_e('Email:', 'milenia-app-textdomain'); ?> </span>
                                <a href="mailto:<?php echo esc_attr($instance['email']); ?>"><?php echo esc_html($instance['email']); ?></a>
                            </address>

                            <a href="mailto:<?php echo esc_attr($instance['email']); ?>" class="milenia-btn"><?php echo esc_html_e('Submit Request', 'milenia-app-textdomain'); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php
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
			'address' => '',
			'phone' => '',
			'fax' => '',
			'email' => '',
            'google_map_link' => '',
            'logo' => ''
        );

        $instance = wp_parse_args( (array) $instance, $defaults );?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('address') ); ?>"><?php esc_html_e('Address:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('address') ); ?>" name="<?php echo esc_attr( $this->get_field_name('address') ); ?>" value="<?php if(isset($instance['address'])) echo esc_attr($instance['address']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('phone') ); ?>"><?php esc_html_e('Phone:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('phone') ); ?>" name="<?php echo esc_attr( $this->get_field_name('phone') ); ?>" value="<?php if(isset($instance['phone'])) echo esc_attr($instance['phone']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('fax') ); ?>"><?php esc_html_e('Fax:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('fax') ); ?>" name="<?php echo esc_attr( $this->get_field_name('fax') ); ?>" value="<?php if(isset($instance['fax'])) echo esc_attr($instance['fax']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('email') ); ?>"><?php esc_html_e('Email:', 'milenia-app-textdomain'); ?></label>
                <input type="email" class="widefat" id="<?php echo esc_attr( $this->get_field_id('email') ); ?>" name="<?php echo esc_attr( $this->get_field_name('email') ); ?>" value="<?php if(isset($instance['email'])) echo esc_attr($instance['email']); ?>">
            </p>

			<?php if(isset($instance['logo']) && !empty($instance['logo'])) : ?>
				<div class="milenia-upload-btn-container">
					<button type="button" style="display: none;" data-multiple="false" class="button milenia-upload-btn-select"><?php esc_html_e('Select Logo', 'milenia-app-textdomain'); ?></button>
					<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('logo') ); ?>" class="milenia-upload-btn-input" value="<?php echo esc_attr($instance['logo']); ?>">
					<div class="milenia-upload-btn-images">
                        <?php echo wp_get_attachment_image(intval($instance['logo']), 'full'); ?>
					</div>
					<button type="button" class="button milenia-upload-btn-remove"><?php esc_html_e('Remove Logo', 'milenia-app-textdomain'); ?></button>
				</div>
			<?php else : ?>
				<div class="milenia-upload-btn-container">
					<button type="button" data-multiple="false" class="button milenia-upload-btn-select"><?php esc_html_e('Select Logo', 'milenia-app-textdomain'); ?></button>
					<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('logo') ); ?>" class="milenia-upload-btn-input">
					<div class="milenia-upload-btn-images"></div>
					<button type="button" class="button milenia-upload-btn-remove" style="display: none;"><?php esc_html_e('Remove Logo', 'milenia-app-textdomain'); ?></button>
				</div>
			<?php endif; ?>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('google_map_link') ); ?>"><?php esc_html_e('Google map link:', 'milenia-app-textdomain'); ?></label>
                <input type="url" class="widefat" id="<?php echo esc_attr( $this->get_field_id('google_map_link') ); ?>" name="<?php echo esc_attr( $this->get_field_name('google_map_link') ); ?>" value="<?php if(isset($instance['google_map_link'])) echo esc_attr($instance['google_map_link']); ?>">
            </p>
        <?php
    }
}
?>
