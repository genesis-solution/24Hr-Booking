<?php
class UpcomingEventsListWidget extends WP_Widget
{
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct()
    {
		$widget_ops = array(
			'classname' => 'milenia_upcoming_events_list',
			'description' => esc_html__('A widget that displays the next upcoming x events.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_upcoming_events_list', esc_html__('[Milenia] Upcoming Events', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
        global $post;

        $posts = tribe_get_events( array(
            'posts_per_page' => isset($instance['number']) && !empty($instance['number']) ? intval($instance['number']) : 3,
            'start_date' => date('Y-m-d H:i:s')
        ) );

        echo $args['before_widget'];
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];

            if($posts) : ?>
                <!--================ Entities ================-->
                <div class="milenia-events">
                <?php foreach($posts as $post) : setup_postdata($post); ?>
                    <!--================ Entity ================-->
                    <?php
            			$milenia_start_day = tribe_get_start_date($post, false, 'j');
            			$milenia_start_month = tribe_get_start_date($post, false, 'F');
            			$milenia_start_year = tribe_get_start_date($post, false, 'Y');

            			$milenia_end_day = tribe_get_end_date($post, false, 'j');
            			$milenia_end_month = tribe_get_end_date($post, false, 'F');
            			$milenia_end_year = tribe_get_end_date($post, false, 'Y');

            			$milenia_same_day = $milenia_start_day == $milenia_end_day;
            			$milenia_same_month = $milenia_start_month == $milenia_end_month;
            			$milenia_same_year = $milenia_start_year == $milenia_end_year;
            		?>
                    <article class="milenia-event">
                        <div class="milenia-event-date">
                            <div class="milenia-event-date-date">
                    			<?php if(!$milenia_same_day || !$milenia_same_month || !$milenia_same_year) : ?>
                    				     <?php echo esc_html(sprintf('%s-%s', $milenia_start_day, $milenia_end_day)); ?>
                    			<?php else : ?>
                    				<?php echo esc_html($milenia_start_day); ?>
                    			<?php endif; ?>
                            </div>

                            <div class="milenia-event-date-month-year">
                                <?php if(!$milenia_same_month || !$milenia_same_year) : ?>
                        			<?php echo esc_html(sprintf('%s-%s', $milenia_start_month, $milenia_end_month)); ?>,
                    			<?php else : ?>
                    				<?php echo esc_html($milenia_start_month); ?>,
                    			<?php endif; ?>
                    			<?php if(!$milenia_same_year) : ?>
                    				<?php echo esc_html(sprintf('%s-%s', $milenia_start_year, $milenia_end_year)); ?>
                    			<?php else : ?>
                    				<?php echo esc_html($milenia_start_year); ?>
                    			<?php endif; ?>
                            </div>
                		</div>

                        <div class="milenia-event-body">
                            <h2 class="milenia-event-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <div class="milenia-event-meta">
                                <div>
                                    <time datetime="<?php echo esc_attr(tribe_get_start_date($post, false, 'c')); ?>"><?php printf('%s-%s', tribe_get_start_date($post, false, 'g:i A'), tribe_get_end_date($post, false, 'g:i A')); ?></time>
                                </div>

                                <?php if(!empty(tribe_get_address()) && !empty(tribe_get_city())) : ?>
                                    <div class="milenia-event-address"><?php printf('%s, %s', tribe_get_address(), tribe_get_city()); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                    <!--================ End of Entity ================-->
                    <?php endforeach; ?>
                </div>
                <!--================ End of Entities ================-->
            <?php endif;
        echo $args['after_widget'];
        wp_reset_postdata();
	}

    /**
     * Creates the widget form in the admin panel.
     *
     * @param array $instance
     * @return void
     */
    public function form( $instance ) {

        $defaults = array(
            'title' => esc_html__('Upcoming Events', 'milenia-app-textdomain'),
            'number' => 3,
            'show_date' => ''
        );

        $instance = wp_parse_args( (array) $instance, $defaults );?>
            <div class="widget-content">
                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                    <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
                </p>

                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id('number') ); ?>"><?php esc_html_e('Number of events to show:', 'milenia-app-textdomain'); ?></label>
                    <input type="number" class="tiny-text" id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" min="1" step="1" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" value="<?php if(isset($instance['number'])) echo esc_attr($instance['number']); ?>">
                </p>
            </div>
        <?php
    }
}
?>
