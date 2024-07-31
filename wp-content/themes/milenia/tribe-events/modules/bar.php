<?php
/**
 * Events Navigation Bar Module Template
 * Renders our events navigation bar used across our views
 *
 * $filters and $views variables are loaded in and coming from
 * the show funcion in: lib/Bar.php
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe-events/modules/bar.php
 *
 * @package  TribeEventsCalendar
 * @version 4.6.19
 */
 if ( ! defined( 'ABSPATH' ) ) {
 	die( esc_html__('You cannot access this file directly', 'milenia') );
 }

?>

<?php

$filters = tribe_events_get_filters();
$views   = tribe_events_get_views();

$current_url = tribe_events_get_current_filter_url();

?>

<?php do_action( 'tribe_events_bar_before_template' ) ?>
<div id="tribe-events-bar" class="milenia-section milenia-section--py-medium">

	<h2 class="tribe-events-visuallyhidden"><?php printf( esc_html__( '%s Search and Views Navigation', 'milenia' ), tribe_get_event_label_plural() ); ?></h2>

	<form id="tribe-bar-form" name="tribe-bar-form" method="post" action="<?php echo esc_attr( $current_url ); ?>">
		<div class="form-group from-group--main-events">
			<?php if(!empty($filters) && isset($filters['tribe-bar-search']) && isset($filters['tribe-bar-date'])) : ?>
				<div class="form-col form-col--events-from <?php echo esc_attr( $filters['tribe-bar-date']['name'] ) ?>-filter">
					<label class="label-<?php echo esc_attr( $filters['tribe-bar-date']['name'] ) ?>" for="<?php echo esc_attr( $filters['tribe-bar-date']['name'] ) ?>"><?php printf('%s', $filters['tribe-bar-date']['caption']); ?></label>
					<?php printf('%s', $filters['tribe-bar-date']['html']); ?>
				</div>
			<?php endif; ?>

			<?php if(!empty($filters) && isset($filters['tribe-bar-search'])) : ?>
				<div class="form-col form-col--events-search <?php echo esc_attr( $filters['tribe-bar-search']['name'] ) ?>-filter">
					<label class="label-<?php echo esc_attr( $filters['tribe-bar-search']['name'] ) ?>" for="<?php echo esc_attr( $filters['tribe-bar-search']['name'] ) ?>"><?php printf('%s', $filters['tribe-bar-search']['caption']); ?></label>
					<?php printf('%s', $filters['tribe-bar-search']['html']); ?>
				</div>
			<?php endif; ?>

			<?php if(isset($filters['tribe-bar-search'])) : ?>
				<div class="form-col form-col--events-search-btn">
					<button type="submit" name="submit-bar" class="milenia-btn milenia-btn--scheme-dark milenia-btn--icon milenia-btn--icon-medium">
						<i class="icon icon-magnifier"></i><?php printf( esc_attr__( 'Find %s', 'milenia' ), tribe_get_event_label_plural() ); ?>
					</button>
				</div>
			<?php endif; ?>

			<?php if ( count( $views ) > 1 ) : ?>
				<div class="form-col form-col--events-view">
					<label><?php esc_html_e( 'View As', 'milenia' ); ?></label>
					<div class="milenia-dropdown">
						<?php foreach ( $views as $view ) : ?>
							<?php if(tribe_is_view($view['displaying'])) : ?>
								<div role="button" aria-expanded="false" aria-controls="milenia-dropdown-views" class="milenia-dropdown-title">
									<?php
										switch($view['displaying']) {
                                            case 'list' :
                                            case 'day' : ?>
                                                <i class="fa fa-list"></i>
                                            <?php break;
                                            case 'month' :
                                            case 'week' : ?>
                                                <i class="fa fa-calendar-alt"></i>
                                            <?php break;
                                            case 'photo' : ?>
                                                <i class="fa fa-th"></i>
                                            <?php break;
                                            case 'map' : ?>
                                                <i class="fa fa-map-marker"></i>
                                            <?php break;
										}
									?>
									<?php printf('%s', $view['anchor']); ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>

						<ul id="milenia-dropdown-views" aria-hidden="true" class="milenia-dropdown-element milenia-list--unstyled milenia-dropdown-element--icons">
							<?php foreach ( $views as $view ) : ?>
								<li>
									<a href="<?php echo esc_attr( $view['url'] ); ?>" data-view="<?php echo esc_attr( $view['displaying'] ); ?>" class="milenia-ln--independent">
										<?php
											switch($view['displaying']) {
												case 'list' :
                                                case 'day' : ?>
													<i class="fa fa-list"></i>
												<?php break;
												case 'month' :
                                                case 'week' : ?>
													<i class="fa fa-calendar-alt"></i>
												<?php break;
												case 'photo' : ?>
													<i class="fa fa-th"></i>
												<?php break;
												case 'map' : ?>
													<i class="fa fa-map-marker"></i>
												<?php break;
											}
										?>
										<?php printf('%s', $view['anchor']); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</form>
	<!-- #tribe-bar-form -->
</div><!-- #tribe-events-bar -->
<?php
do_action( 'tribe_events_bar_after_template' );
