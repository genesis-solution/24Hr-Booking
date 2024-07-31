<?php
class SocialIconsWidget extends WP_Widget
{
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_social_icons',
			'description' => esc_html__('Social network profiles.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_social_icons', esc_html__('[Milenia] Social Network Icons', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
        global $Milenia;

        $facebook = $Milenia->getThemeOption('milenia-social-links-facebook', '');
        $twitter = $Milenia->getThemeOption('milenia-social-links-twitter', '');
        $tripadvisor = $Milenia->getThemeOption('milenia-social-links-tripadvisor', '');
        $google_plus = $Milenia->getThemeOption('milenia-social-links-google-plus', '');
        $instagram = $Milenia->getThemeOption('milenia-social-links-instagram', '');
        $youtube = $Milenia->getThemeOption('milenia-social-links-youtube', '');
        $flickr = $Milenia->getThemeOption('milenia-social-links-flickr', '');
        $booking = $Milenia->getThemeOption('milenia-social-links-booking', '');
        $airbnb = $Milenia->getThemeOption('milenia-social-links-airbnb', '');
        $whatsapp = $Milenia->getThemeOption('milenia-social-links-whatsapp', '');

        $instance = wp_parse_args((array) $instance, array(
            'title' => '',
            'milenia_social_icons_text' => ''
        ));

        echo $args['before_widget'];
			if(!empty($instance['title']))
			{
	            echo $args['before_title'];
	                echo $instance['title'];
	            echo $args['after_title'];
			}

            if(!empty($instance['milenia_social_icons_text'])) : ?>
                <p><?php echo esc_html($instance['milenia_social_icons_text']); ?></p>
            <?php endif; ?>

                <ul class="milenia-social-icons milenia-list--unstyled">
                    <?php if(!empty($facebook)) : ?>
                        <li><a href="<?php echo esc_url($facebook); ?>"><i class="fab fa-facebook-f"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($twitter)) : ?>
                        <li><a href="<?php echo esc_url($twitter); ?>"><i class="fab fa-twitter"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($google_plus)) : ?>
                        <li><a href="<?php echo esc_url($google_plus); ?>"><i class="fab fa-google-plus-g"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($tripadvisor)) : ?>
                        <li><a href="<?php echo esc_url($tripadvisor); ?>"><i class="fab fa-tripadvisor"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($instagram)) : ?>
                        <li><a href="<?php echo esc_url($instagram); ?>"><i class="fab fa-instagram"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($youtube)) : ?>
                        <li><a href="<?php echo esc_url($youtube); ?>"><i class="fab fa-youtube"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($flickr)) : ?>
                        <li><a href="<?php echo esc_url($flickr); ?>"><i class="fab fa-flickr"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($booking)) : ?>
                        <li><a href="<?php echo esc_url($booking); ?>"><i class="milenia-font-icon-1-icon-booking-icon"></i></a></li>
                    <?php endif; ?>
                    <?php if(!empty($airbnb)) : ?>
                        <li><a href="<?php echo esc_url($airbnb); ?>"><i class="fab fa-airbnb"></i></a></li>
                    <?php endif; ?>
	                <?php if(!empty($whatsapp)) : ?>
						<li><a href="<?php echo esc_url($whatsapp); ?>"><i class="fab fa-whatsapp"></i></a></li>
	                <?php endif; ?>
                </ul>
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
            'title' => esc_html__('Stay Connected', 'milenia-app-textdomain'),
            'milenia_social_icons_text' => esc_html__('Follow us on social media channels', 'milenia-app-textdomain')
        );

        $instance = wp_parse_args((array) $instance, $defaults); ?>
            <small class="milenia-admin-info-message"><?php esc_html_e("Please don't forget to specify your profile links on the theme options page." , 'milenia-app-textdomain'); ?></small>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_social_icons_text') ); ?>"><?php esc_html_e('Text before icons:', 'milenia-app-textdomain'); ?></label>
                <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id('milenia_social_icons_text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_social_icons_text') ); ?>"><?php echo esc_html($instance['milenia_social_icons_text']); ?></textarea>
            </p>
        <?php
    }
}
?>
