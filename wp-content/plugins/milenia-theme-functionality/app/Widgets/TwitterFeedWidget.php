<?php
class TwitterFeedWidget extends WP_Widget
{

    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_twitter_feed',
			'description' => esc_html__('Your latest tweets.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_twitter_feed', esc_html__('[Milenia] Twitter Feed', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
        require_once(MILENIA_FUNCTIONALITY_ROOT . 'vendor/tweetie/api/php/tweetie.php');

        $api = new Tweetie();
        $tweets = $api->fetch('timeline', array(
            'count' => $instance['number']
        ));
        $tweets_decoded = json_decode($tweets);

        echo $args['before_widget'];
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];

            if(!empty($tweets_decoded)) :
        ?>
            <!--================ Twitter Feed ================-->
            <ul class="milenia-twitter-feed milenia-list--unstyled">
                <?php foreach($tweets_decoded as $tweet) : ?>
                    <li>
                        <div class="milenia-tweet">
                            <div class="milenia-tweet-content"><?php echo $this->prepareText($tweet); ?></div>
                            <footer class="milenia-tweet-footer"><?php echo $this->prepareDate($tweet); ?></footer>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!--================ End of Twitter Feed ================-->

            <?php if(isset($instance['show_follow_button']) && boolval($instance['show_follow_button'])) : ?>
                <a href="https://twitter.com/<?php echo esc_attr($tweets_decoded[0]->user->screen_name); ?>" target="_blank" class="milenia-btn milenia-btn--icon milenia-btn--scheme-primary milenia-btn--link"><span class="fab fa-twitter"></span><?php esc_html_e('Follow Us On Twitter', 'milenia-app-textdomain'); ?></a>
            <?php endif; ?>
        <?php
        endif;
        echo $args['after_widget'];
	}


    protected function prepareText($tweet)
    {

        if(isset($tweet->entities) && isset($tweet->entities->urls))
        {
            foreach($tweet->entities->urls as $url)
            {
                $tweet->text = str_replace($url->url, '<a href="' .$url->expanded_url. '">' . $url->display_url . '</a>', $tweet->text);
            }
        }

        if(isset($tweet->entities) && !isset($tweet->entities->hashtags))
        {
            foreach($tweet->entities->hashtags as $hashtag)
            {
                $tweet->text = str_replace('#' . $hashtag->text, '<a href="https://twitter.com/hashtag/'.$hashtag->text.'?src=hash">' . $hashtag->text . '</a>', $tweet->text);
            }
        }


        return $tweet->text;
    }

    protected function prepareDate($tweet)
    {
        return (new DateTime($tweet->created_at))->format('F d, Y');
    }

    /**
     * Creates the widget form in the admin panel.
     *
     * @param array $instance
     * @return void
     */
    public function form( $instance ) {

        $defaults = array(
            'title' => esc_html__('Latest Tweets', 'milenia-app-textdomain'),
            'number' => 3,
            'show_follow_button' => ''
        );

        $instance = wp_parse_args( (array) $instance, $defaults );?>
            <small class="milenia-admin-info-message"><?php esc_html_e("Please don't forget to specify Twitter API keys on the theme options page." , 'milenia-app-textdomain'); ?></small>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('number') ); ?>"><?php esc_html_e('Tweets amount:', 'milenia-app-textdomain'); ?></label>
                <input type="number" class="tiny-text" id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" min="1" step="1" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" value="<?php if(isset($instance['number'])) echo esc_attr($instance['number']); ?>">
            </p>

            <p>
                <input class="checkbox" type="checkbox" <?php checked(1, $instance['show_follow_button']); ?> id="<?php echo esc_attr( $this->get_field_id('show_follow_button') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_follow_button') ); ?>" value="1">
    		    <label for="<?php echo esc_attr( $this->get_field_id('show_follow_button') ); ?>"><?php esc_html_e('Display follow button?', 'milenia-app-textdomain'); ?></label>
            </p>
        <?php
    }
}
?>
