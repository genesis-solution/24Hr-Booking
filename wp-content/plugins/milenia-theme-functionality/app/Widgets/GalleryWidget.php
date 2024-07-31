<?php
class GalleryWidget extends WP_Widget
{
    /**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
        if(!class_exists('MileniaGalleryRepository'))
        {
            return;
        }

		$widget_ops = array(
			'classname' => 'milenia_gallery',
			'description' => esc_html__('Shows photos of the specified gallery.', 'milenia-app-textdomain'),
		);
		parent::__construct( 'milenia_gallery', esc_html__('[Milenia] Gallery', 'milenia-app-textdomain'), $widget_ops );
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
    {
        $instance = array_merge(array(
            'title' => '',
            'gallery_id' => '',
            'number_of_posts' => 6,
            'columns' => 'gallery-columns-3'
        ), $instance);

        $MileniaPostsRepository = new MileniaGalleryRepository('milenia-galleries');

        $milenia_items = $MileniaPostsRepository->in(array($instance['gallery_id']))
        										->limit($instance['number_of_posts'])
        										->get();

        echo $args['before_widget'];
            echo $args['before_title'];
                echo $instance['title'];
            echo $args['after_title'];

        if(!empty($milenia_items)) : ?>
            <div id="gallery-1" class="gallery <?php echo esc_attr($instance['columns']); ?>">
                <?php foreach($milenia_items as $item) : ?>
                    <figure class="gallery-item">
            			<div class="gallery-icon landscape">
            				<a href="<?php echo wp_get_attachment_image_url($item['attach_id'], 'full'); ?>" class="milenia-ln--independent" data-fancybox="<?php echo esc_attr($this->id_base); ?>">
                                <?php echo wp_get_attachment_image($item['attach_id'], 'thumbnail'); ?>
                            </a>
            			</div>
                    </figure>
                <?php endforeach; ?>
            </div>

            <a href="<?php echo esc_url(get_the_permalink($instance['gallery_id'])); ?>" class="milenia-btn milenia-btn--scheme-primary milenia-btn--link"><?php esc_html_e('More Photos', 'milenia-app-textdomain'); ?></a>
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
        global $post;

        $defaults = array(
            'title' => esc_html__('From Gallery', 'milenia-app-textdomain'),
            'gallery_id' => '',
            'number_of_posts' => 6,
            'columns' => 'gallery-columns-3'
        );

        $galleries = get_posts(array(
            'post_type' => 'milenia-galleries',
            'numberposts' => -1,
            'post_status' => 'publish'
        ));

        $instance = wp_parse_args( (array) $instance, $defaults ); ?>
            <div class="widget-content">
                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:', 'milenia-app-textdomain'); ?></label>
                    <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>">
                </p>

                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id('number') ); ?>"><?php esc_html_e('Number of posts to show:', 'milenia-app-textdomain'); ?></label>
                    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id('gallery_id') ); ?>" name="<?php echo esc_attr( $this->get_field_name('gallery_id') ); ?>">
                        <?php if(count($galleries)) : ?>
                            <?php foreach ($galleries as $key => $post) : setup_postdata($post); ?>
                                <option <?php selected(get_the_ID(), $instance['gallery_id'], true); ?> value="<?php the_ID(); ?>"><?php the_title(); ?></option>
                            <?php endforeach; wp_reset_postdata(); ?>
                        <?php endif; ?>
                    </select>
                </p>

                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id('number_of_posts') ); ?>"><?php esc_html_e('Items amount:', 'milenia-app-textdomain'); ?></label>
                    <input type="number" class="tiny-text" id="<?php echo esc_attr( $this->get_field_id('number_of_posts') ); ?>" min="1" step="1" name="<?php echo esc_attr( $this->get_field_name('number_of_posts') ); ?>" value="<?php if(isset($instance['number_of_posts'])) echo esc_attr($instance['number_of_posts']); ?>">
                </p>

                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id('columns') ); ?>"><?php esc_html_e('Columns:', 'milenia-app-textdomain'); ?></label>
                    <select id="<?php echo esc_attr( $this->get_field_id('columns') ); ?>" name="<?php echo esc_attr( $this->get_field_name('columns') ); ?>">
                        <option <?php selected('gallery-columns-2', $instance['columns'], true); ?> value="gallery-columns-2">2</option>
                        <option <?php selected('gallery-columns-3', $instance['columns'], true); ?> value="gallery-columns-3">3</option>
                    </select>
                </p>
            </div>
        <?php
    }
}
?>
