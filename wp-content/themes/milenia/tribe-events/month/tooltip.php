<?php
/**
 * Please see single-event.php in this directory for detailed instructions on how to use and modify these templates.
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe-events/month/tooltip.php
 * @version 4.6.21
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	die( esc_html__('You cannot access this file directly', 'milenia') );
 }
?>

<script type="text/html" id="tribe_tmpl_tooltip">
	<div id="tribe-events-tooltip-[[=eventId]]" class="tribe-events-tooltip milenia-events-tooltip">
		<h4 class="milenia-entry-title">[[=title]]<\/h4>
			<div class="milenia-events-event-body">
				<time datetime="2019-03-02T12:00:00Z" class="milenia-event-duration">[[=dateDisplay]]<\/time>
				[[ if(imageTooltipSrc.length) { ]]
					<div class="milenia-events-event-thumb"><img src="[[=imageTooltipSrc]]" alt="[[=title]]" \/><\/div>
				[[ } ]]
				[[ if(excerpt.length) { ]]
					<div class="milenia-event-description">[[=raw excerpt]]<\/div>
				[[ } ]]
			<\/div>
	<\/div>
</script>

<script type="text/html" id="tribe_tmpl_tooltip_featured">
    <div id="tribe-events-tooltip-[[=eventId]]" class="tribe-events-tooltip milenia-events-tooltip">
		<h4 class="milenia-entry-title">[[=title]]<\/h4>
			<div class="milenia-events-event-body">
				<time datetime="2019-03-02T12:00:00Z" class="milenia-event-duration">[[=dateDisplay]]<\/time>
				[[ if(imageTooltipSrc.length) { ]]
					<div class="milenia-events-event-thumb"><img src="[[=imageTooltipSrc]]" alt="[[=title]]" \/><\/div>
				[[ } ]]
				[[ if(excerpt.length) { ]]
					<div class="milenia-event-description">[[=raw excerpt]]<\/div>
				[[ } ]]
			<\/div>
	<\/div>
</script>
