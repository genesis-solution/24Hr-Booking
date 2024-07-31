<?php
/**
 * List View Loop
 * This file sets up the structure for the list loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/loop.php
 *
 * @version 4.4
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
} ?>

<?php
global $post;
global $more;
$more = false;
?>
<div class="milenia-entities milenia-entities--style-19 milenia-entities--list">
	<div data-isotope-layout="grid" class="milenia-grid milenia-grid--isotope milenia-grid--cols-1">
		<?php while ( have_posts() ) : the_post(); ?>
			<?php do_action( 'tribe_events_inside_before_loop' ); ?>

			<?php
			$post_parent = '';
			if ( $post->post_parent ) {
				$post_parent = ' data-parent-post-id="' . absint( $post->post_parent ) . '"';
			}
			?>
			<div id="post-<?php the_ID() ?>" class="<?php tribe_events_event_classes() ?> milenia-grid-item" <?php echo esc_attr($post_parent); ?>>
				<?php
				$event_type = tribe( 'tec.featured_events' )->is_featured( $post->ID ) ? 'featured' : 'event';

				/**
				 * Filters the event type used when selecting a template to render
				 *
				 * @param $event_type
				 */
				$event_type = apply_filters( 'tribe_events_list_view_event_type', $event_type );

				tribe_get_template_part( 'list/single', $event_type );
				?>
			</div>


			<?php do_action( 'tribe_events_inside_after_loop' ); ?>
		<?php endwhile; ?>
	</div>
</div>
