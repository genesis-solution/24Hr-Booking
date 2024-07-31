<?php
class InstagramWidget extends WP_Widget
{

    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_instagram',
			'description' => esc_html__('Instagram feed.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_instagram', esc_html__('[Milenia] Instagram', 'milenia-app-textdomain'), $widget_ops );
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
            'milenia_instagram_user_id' => '',
            'milenia_instagram_user_access_token' => '',
            'milenia_instagram_user_client_id' => '',
            'milenia_instagram_items_per_page' => 1,
            'milenia_instagram_columns' => 'milenia-grid--cols-1',
            'milenia_instagram_no_gutters' => '',
            'milenia_instagram_profile_link' => ''
        ));

        echo $args['before_widget'];
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];

            echo do_shortcode(
                sprintf(
                        '[vc_milenia_instagram milenia_instagram_user_id="%s" milenia_instagram_user_access_token="%s" milenia_instagram_user_client_id="%s" milenia_instagram_items_per_page="%s" milenia_instagram_columns="%s" milenia_instagram_no_gutters="%s"]',
                        $instance['milenia_instagram_user_id'],
                        $instance['milenia_instagram_user_access_token'],
                        $instance['milenia_instagram_user_client_id'],
                        $instance['milenia_instagram_items_per_page'],
                        $instance['milenia_instagram_columns'],
                        $instance['milenia_instagram_no_gutters']
                    )
                );

        if(!empty($instance['milenia_instagram_profile_link'])) : ?>
            <a href="<?php echo esc_url($instance['milenia_instagram_profile_link']); ?>" target="_blank" class="milenia-btn milenia-btn--icon milenia-btn--scheme-primary milenia-btn--link"><span class="fab fa-instagram"></span><?php esc_html_e('Follow Us On Instagram', 'milenia-app-textdomain'); ?></a>
        <?php
        endif;
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
            'title' => esc_html__('Instagram', 'milenia-app-textdomain'),
            'milenia_instagram_user_id' => '',
            'milenia_instagram_user_access_token' => '',
            'milenia_instagram_user_client_id' => '',
            'milenia_instagram_items_per_page' => 1,
            'milenia_instagram_columns' => 'milenia-grid--cols-1',
            'milenia_instagram_no_gutters' => '',
            'milenia_instagram_profile_link' => ''
        );

        $instance = wp_parse_args((array) $instance, $defaults);?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_user_id') ); ?>"><?php esc_html_e('User ID:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_user_id') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_user_id') ); ?>" value="<?php if(isset($instance['milenia_instagram_user_id'])) echo esc_attr($instance['milenia_instagram_user_id']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_user_access_token') ); ?>"><?php esc_html_e('Access Token:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_user_access_token') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_user_access_token') ); ?>" value="<?php if(isset($instance['milenia_instagram_user_access_token'])) echo esc_attr($instance['milenia_instagram_user_access_token']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_user_client_id') ); ?>"><?php esc_html_e('Client ID:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_user_client_id') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_user_client_id') ); ?>" value="<?php if(isset($instance['milenia_instagram_user_client_id'])) echo esc_attr($instance['milenia_instagram_user_client_id']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_items_per_page') ); ?>"><?php esc_html_e('Items amount:', 'milenia-app-textdomain'); ?></label>
                <input type="number" class="tiny-text" id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_items_per_page') ); ?>" min="1" step="1" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_items_per_page') ); ?>" value="<?php if(isset($instance['milenia_instagram_items_per_page'])) echo esc_attr($instance['milenia_instagram_items_per_page']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_columns') ); ?>"><?php esc_html_e('Columns:', 'milenia-app-textdomain'); ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_columns') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_columns') ); ?>">
                    <option <?php selected('milenia-grid--cols-1', $instance['milenia_instagram_columns'], true); ?> value="milenia-grid--cols-1">1</option>
                    <option <?php selected('milenia-grid--cols-2', $instance['milenia_instagram_columns'], true); ?> value="milenia-grid--cols-2">2</option>
                    <option <?php selected('milenia-grid--cols-3', $instance['milenia_instagram_columns'], true); ?> value="milenia-grid--cols-3">3</option>
                </select>
            </p>

            <p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['milenia_instagram_no_gutters']); ?> id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_no_gutters') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_no_gutters') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_no_gutters') ); ?>"><?php esc_html_e('No gutters', 'milenia-app-textdomain'); ?></label>
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('milenia_instagram_profile_link') ); ?>"><?php esc_html_e('Profile link:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('milenia_instagram_profile_link') ); ?>" name="<?php echo esc_attr( $this->get_field_name('milenia_instagram_profile_link') ); ?>" value="<?php if(isset($instance['milenia_instagram_profile_link'])) echo esc_attr($instance['milenia_instagram_profile_link']); ?>">
            </p>
        <?php
    }
}
?>
