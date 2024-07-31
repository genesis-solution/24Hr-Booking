<?php
class WeatherWidget extends WP_Widget
{

    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_weather',
			'description' => esc_html__('Displays current weather.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_weather', esc_html__('[Milenia] Weather', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
        global $MileniaWeatherForecaster;

        $instance = wp_parse_args((array) $instance, array(
            'title' => '',
            'style' => 'milenia-weather-indicator--style-3',
            'type' => 'celsius',
            'show_location' => '',
			'link_url' => '',
			'nofollow' => '',
			'target_blank' => ''
        ));

        echo $args['before_widget'];

			if(!empty($instance['title']))
			{
	            echo $args['before_title'];
	                echo $instance['title'];
	            echo $args['after_title'];
			}

            if(!isset($MileniaWeatherForecaster)) return;
            ?>

            <?php if(boolval($instance['show_location'])) : ?>
                <p><?php echo esc_html(sprintf('%s, %s', $MileniaWeatherForecaster->getName(), $MileniaWeatherForecaster->getCountry())); ?></p>
            <?php endif; ?>

            <div class="milenia-weather-indicator <?php echo esc_attr($instance['style']); ?>">
                <div class="milenia-weather-indicator-celsius">
                    <span class="icon <?php echo esc_attr($MileniaWeatherForecaster->getIconClass()); ?>"></span><?php echo esc_html($MileniaWeatherForecaster->getCelsiusValue()); ?><sup>&#176;C<span class="milenia-weather-indicator-btn">/&#176;F</span></sup>
                </div>
                <div class="milenia-weather-indicator-fahrenheit">
                    <span class="icon <?php echo esc_attr($MileniaWeatherForecaster->getIconClass()); ?>"></span><?php echo esc_html($MileniaWeatherForecaster->getFahrenheitValue()); ?><sup>&#176;F<span class="milenia-weather-indicator-btn">/&#176;C</span></sup>
                </div>
            </div>

			<?php if($instance['style'] == 'milenia-weather-indicator--style-2' && !empty($instance['link_url'])) : ?>
				<a href="<?php echo esc_url($instance['link_url']); ?>" <?php if(!empty($instance['nofollow'])) : ?>rel="nofollow"<?php endif; ?> <?php if(!empty($instance['target_blank'])) : ?>target="_blank"<?php endif; ?> class="milenia-icon-btn milenia-ln--independent"><span class="icon icon-surveillance2"></span></a>
            <?php endif;
        echo $args['after_widget'];
	}

    /**
     * Creates the widget form in the admin panel.
     *
     * @param array $instance
     * @return void
     */
    public function form( $instance ) {

        $defaults = array(
            'title' => esc_html__('Current Weather', 'milenia-app-textdomain'),
            'style' => 'milenia-weather-indicator--style-3',
            'type' => 'celsius',
            'show_location' => '',
			'link_url' => '',
			'nofollow' => '',
			'target_blank' => ''
        );

        $instance = wp_parse_args((array) $instance, $defaults);?>
            <small class="milenia-admin-info-message"><?php esc_html_e("Please don't forget to specify APIXU API key on the theme options page." , 'milenia-app-textdomain'); ?></small>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('style') ); ?>"><?php esc_html_e('Style:', 'milenia-app-textdomain'); ?></label>
                <select class="widefat" id="<?php echo esc_attr( $this->get_field_id('style') ); ?>" name="<?php echo esc_attr( $this->get_field_name('style') ); ?>">
                    <option <?php selected('milenia-weather-indicator--style-2', $instance['style'], true); ?> value="milenia-weather-indicator--style-2"><?php esc_html_e('Style 1', 'milenia-app-textdomain'); ?></option>
                    <option <?php selected('milenia-weather-indicator--style-3', $instance['style'], true); ?> value="milenia-weather-indicator--style-3"><?php esc_html_e('Style 2', 'milenia-app-textdomain'); ?></option>
                </select>
            </p>

            <p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['show_location']); ?> id="<?php echo esc_attr( $this->get_field_id('show_location') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_location') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('show_location') ); ?>"><?php esc_html_e('Show location', 'milenia-app-textdomain'); ?></label>
            </p>

			<p>
                <label for="<?php echo esc_attr( $this->get_field_id('link_url') ); ?>"><?php esc_html_e('Webcam link (Style 1 only):', 'milenia-app-textdomain'); ?></label>
                <input type="url" class="widefat" id="<?php echo esc_attr( $this->get_field_id('link_url') ); ?>" name="<?php echo esc_attr( $this->get_field_name('link_url') ); ?>" value="<?php if(isset($instance['link_url'])) echo esc_attr($instance['link_url']); ?>">
            </p>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['nofollow']); ?> id="<?php echo esc_attr( $this->get_field_id('nofollow') ); ?>" name="<?php echo esc_attr( $this->get_field_name('nofollow') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('nofollow') ); ?>"><?php esc_html_e('Nofollow (Style 1 only)', 'milenia-app-textdomain'); ?></label>
            </p>

			<p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['target_blank']); ?> id="<?php echo esc_attr( $this->get_field_id('target_blank') ); ?>" name="<?php echo esc_attr( $this->get_field_name('target_blank') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('target_blank') ); ?>"><?php esc_html_e('Open link in a new window (Style 1 only)', 'milenia-app-textdomain'); ?></label>
            </p>
        <?php
    }
}
?>
