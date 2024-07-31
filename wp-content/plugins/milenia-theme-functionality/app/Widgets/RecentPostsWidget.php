<?php
class RecentPostsWidget extends WP_Widget
{

    /**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'milenia_recent_posts',
			'description' => esc_html__('Your site’s most recent Posts.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_recent_posts', esc_html__('[Milenia] Recent Posts', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
        $results = new WP_Query(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => isset($instance['number']) && !empty($instance['number']) ? intval($instance['number']) : 3,
            'ignore_sticky_posts' => true
        ));

        echo $args['before_widget'];
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];

        if($results->have_posts())
        { ?>
            <!--================ Entities ================-->
            <div class="milenia-entities">
            <?php while($results->have_posts())
            { $results->the_post(); ?>
                <!--================ Entity ================-->
                <article class="milenia-entity">
                    <?php if(has_post_thumbnail()) : ?>
                        <div class="milenia-entity-media">
                            <a href="<?php the_permalink(); ?>" class="milenia-ln--independent">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="milenia-entity-content">
                        <div class="milenia-entity-header">
                            <div class="milenia-entity-meta">
                                <?php if(isset($instance['show_date']) && boolval($instance['show_date'])) : ?>
                                    <div>
        								<time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="milenia-entity-publish-date">
        									<a href="<?php the_permalink(); ?>" class="milenia-ln--independent"><?php echo get_the_date(get_option('date_format')); ?></a>
        								</time>
        							</div>
                                <?php endif; ?>

                                <?php if(function_exists('milenia_has_post_terms') && function_exists('milenia_get_post_terms') && milenia_has_post_terms(get_the_ID())) : ?>
    								<div>
    									<?php esc_html_e('in', 'milenia-app-textdomain'); ?>
    									<?php echo milenia_get_post_terms(get_the_ID()); ?>
    								</div>
    							<?php endif; ?>
                            </div>

                            <h2 class="milenia-entity-title">
                                <a href="<?php the_permalink(); ?>" class="milenia-color--unchangeable"><?php the_title(); ?></a>
                            </h2>
                        </div>
                    </div>
                </article>
                <!--================ End of Entity ================-->
                <?php
            }
            ?>
            </div>
            <!--================ End of Entities ================-->
            <?php
        }
        wp_reset_query();
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
            'title' => esc_html__('Recent Posts', 'milenia-app-textdomain'),
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
                    <label for="<?php echo esc_attr( $this->get_field_id('number') ); ?>"><?php esc_html_e('Number of posts to show:', 'milenia-app-textdomain'); ?></label>
                    <input type="number" class="tiny-text" id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" min="1" step="1" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" value="<?php if(isset($instance['number'])) echo esc_attr($instance['number']); ?>">
                </p>

                <p>
                    <input class="checkbox" type="checkbox" <?php checked(1, $instance['show_date']); ?> id="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_date') ); ?>" value="1">
        		    <label for="<?php echo esc_attr( $this->get_field_id('show_date') ); ?>"><?php esc_html_e('Display post date?', 'milenia-app-textdomain'); ?></label>
                </p>
            </div>
        <?php
    }
}
?>
