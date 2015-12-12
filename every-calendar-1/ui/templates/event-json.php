<?php
/**
 * File that loads a list of events based on parameters and then
 * returns a JSON data set in FullCalendar format of those events.
 *
 * V1.0 UPDATE
 * This file now uses the EveryCal_Scheduler::GetEvents abstracted function
 * to get an array of $ecp1_event_fields style data; all the timezones are
 * assumed to be the timezone for the calendar the event is published on.
 * So if this is not the source calendar time rewriting maybe required.
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// We need the Every Calendar settings
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );

// We need to know about the event post type meta/custom fields
require_once( ECP1_DIR . '/includes/data/event-fields.php' );

// Load the repeating calendar scheduler
require_once( ECP1_DIR . '/includes/scheduler.php' );

// Load the helper functions
require_once( ECP1_DIR . '/functions.php' );

// WordPress will only pass ecp1_cal as a query_var if it passes
// the registered regex (letters, numbers, _ and -) this is loosely
// consistent with slugs as in sanitize_title_with_dashes but will
// break if someone changes the calendar slug manually
$cal = $wp_query->query_vars['ecp1_cal'];

// Get and validate the input parameters
if ( ! isset( $wp_query->query_vars['ecp1_start'] ) || ! isset( $wp_query->query_vars['ecp1_end'] ) )
	_ecp1_template_error( __( 'Please specify a start and end timestamp for the lookup range' ),
						412, __( 'Missing Parameters' ) ); // exits the interpreter

// If the variables are given then check they're validity
$start = preg_match( '/^\-?[0-9]+$/', $wp_query->query_vars['ecp1_start'] ) ? 
		(int) $wp_query->query_vars['ecp1_start'] : null;
$end   = preg_match( '/^\-?[0-9]+$/', $wp_query->query_vars['ecp1_end'] ) ?
		(int) $wp_query->query_vars['ecp1_end'] : null;

if ( is_null( $start ) || is_null( $end ) ) {
	_ecp1_template_error( __( 'Please specify the start and end as timestamps' ),
						412, __( 'Incorrect Parameter Format' ) ); // exits the interpreter
} elseif ( $end < $start ) {
	_ecp1_template_error( __( 'The end date must be after the start date' ),
						412, __( 'Incorrect Parameter Format' ) ); // exits the interpreter
}

// Lookup the calendar post and exit if non-existant
$cal = get_page_by_path( $cal, OBJECT, 'ecp1_calendar' );
if ( is_null( $cal ) )
	_ecp1_template_error( __( 'No such calendar.' ), 404, __( 'Calendar Not Found' ) ); // exits the interpreter


// All checkes passed so start rendering JSON

// Encode as JSON (unless ECP1_TEMPLATE_TEST_ARG is '1')
$plain = false;
if ( ! empty( $wp_query->query_vars[ECP1_TEMPLATE_TEST_ARG] ) &&
		'1' == $wp_query->query_vars[ECP1_TEMPLATE_TEST_ARG] ) {
	header( 'Content-Type:text/plain' );
	$plain = true;
} else {
	header( 'Content-Type:application/json' );
}

// Reset the default WordPress query just in case
wp_reset_query();

// Remove any actions on loop
remove_all_actions( 'loop_start' );
remove_all_actions( 'the_post' );
remove_all_actions( 'loop_end' );


// Start processing the calendar
_ecp1_parse_calendar_custom( $cal->ID ); // Get the calendar meta data
$tz = ecp1_get_calendar_timezone();      // and the effective timezone
$dtz = new DateTimeZone( $tz );

// Need a reference to the current time at UTC
$now = new DateTime( NULL, new DateTimeZone( 'UTC' ) );

// What are the feature event colors for this calendar
$feature_color = $feature_textcolor = '#000000';
if ( _ecp1_calendar_show_featured( $cal->ID ) ) {
	$feature_color = _ecp1_calendar_meta( 'ecp1_feature_event_color' );
	$feature_textcolor = _ecp1_calendar_meta( 'ecp1_feature_event_textcolor' );
}

// Get the $ecp1_event_fields pseudo arrays
$events = EveryCal_Scheduler::GetEvents( $cal->ID, $start, $end );

// An array of JSON parameters for the output
$events_json = array();

// Loop over all of the events
$loop_counter = 0;
foreach( $events as $event ) {

	// Make sure there are start and end times
	if ( _ecp1_render_default( $event, 'ecp1_start_ts' ) || _ecp1_render_default( $event, 'ecp1_end_ts' ) )
		continue;
	
	// The events timestamps will be a unix timestamp at the localtime of the
	// calendar that event is published on. If this event is published on a 
	// diferent calendar then the timezone may need to be adjusted.
	try {
		// Build UTC DateTime objects to begin with
		$estart = new DateTime( '@' . $event['ecp1_start_ts'] );
		$eend   = new DateTime( '@' . $event['ecp1_end_ts'] );

		// Is this event on this calendar?
		if ( $event['ecp1_calendar'] == $cal->ID ) { // YES SAME CALENDAR
			$estart->setTimezone( $dtz );
			$eend->setTimezone( $dtz );
		} else { // NO DIFFERENT CALENDAR
			
			// Get the source calendar timezone and check it's different
			$scaltz = new DateTimeZone( $event['_meta']['calendar_tz'] );
			if ( $dtz->getOffset( $now ) == $scaltz->getOffset( $now ) ) {
				$estart->setTimezone( $scaltz );
				$eend->setTimezone( $scaltz );
			} else { // OFFSET IS DIFFERENT
			
				// If this is a featured event then an option controls if we rewrite the time
				// if it's just a regular event then we always rewrite the time to local zone
				if ( 'Y' == $event['ecp1_featured'] ) { // feature event
					if ( '1' == _ecp1_get_option( 'base_featured_local_to_event' ) ) {
						// User has requested timezone to be rebased to local
						$estart->setTimezone( $scaltz );
						$eend->setTimezone( $scaltz );
					} else {
						// Times should be shown at this calendars timezone
						$estart->setTimezone( $dtz );
						$eend->setTimezone( $dtz );
					}
				} else { // non-featured event
					// Always use the events publish calendar timezone
					$estart->setTimezone( $dtz );
					$eend->setTimezone( $dtz );
				}

			}

		}

		// Create an entry in the JSON array and write the some summary details
		$events_json[$loop_counter] = array(
			// The JS escapes HTML entities but doesn't check if &amp; is already there
			// but we want to filter the title so that means we have to undo the &amp;'s
			'title'  => str_replace( '&amp;', '&', get_the_title( $event['post_id'] ) ),
			'start'  => $estart->format( 'c' ),     // ISO8601 automatically handling DST and
			'end'    => $eend->format( 'c' ),       // the other seasonal variations in offset
			'allDay' => 'Y' == $event['ecp1_full_day']
		);


		// If custom colors were specified for this event show them
		// NOTE: feature event colors will overwrite these if they're set
		if ( 'Y' == $event['ecp1_overwrite_color'] ) {
			if ( ! _ecp1_render_default( $event, 'ecp1_local_textcolor' ) )
				$events_json[$loop_counter]['textColor'] = $event['ecp1_local_textcolor'];
			if ( ! _ecp1_render_default( $event, 'ecp1_local_color' ) )
				$events_json[$loop_counter]['color'] = $event['ecp1_local_color'];
		}

		// If this is a feature event (not from this calendar) then give it the feature colors
		if ( $event['ecp1_calendar'] != $cal->ID && 'Y' == $event['ecp1_featured'] ) {
			$events_json[$loop_counter]['color'] = $feature_color;
			$events_json[$loop_counter]['textColor'] = $feature_textcolor;
		}

		// If the event has a summary then put it in
		if ( ! _ecp1_render_default( $event, 'ecp1_summary' ) )
			$events_json[$loop_counter]['description'] = esc_html( $event['ecp1_summary'] );

		// Create a location string like appears on the event page
		if ( ! _ecp1_render_default( $event, 'ecp1_location' ) )
			$events_json[$loop_counter]['location'] = esc_html( $event['ecp1_location'] );

		// Enable map visibility
		$events_json[$loop_counter]['showmap'] = 'Y' == $event['ecp1_showmap'];
		if ( $events_json[$loop_counter]['showmap'] ) { // only send map zoom and marker if show map
			if ( ! _ecp1_render_default( $event, 'ecp1_map_zoom' ) )
				$events_json[$loop_counter]['zoom'] = (int) $event['ecp1_map_zoom'];
			if ( 'Y' == $event['ecp1_showmarker'] ) {
				if ( ! _ecp1_render_default( $event, 'ecp1_map_placemarker' ) && file_exists( ECP1_DIR . '/img/mapicons/' . $event['ecp1_map_placemarker'] ) )
					$events_json[$loop_counter]['mark'] = plugins_url( '/img/mapicons/' . $event['ecp1_map_placemarker'], dirname( dirname( __FILE__ ) ) );
				else
					$events_json[$loop_counter]['mark'] = true;
			} else {
				$events_json[$loop_counter]['mark'] = false;
			}
		}

		// If there are Lat/Lng send them for the event
		if ( ! _ecp1_render_default( $event, 'ecp1_coord_lat' ) && ! _ecp1_render_default( $event, 'ecp1_coord_lng' ) ) {
			$events_json[$loop_counter]['lat'] = (float) $event['ecp1_coord_lat'];
			$events_json[$loop_counter]['lng'] = (float) $event['ecp1_coord_lng'];
		}

		
		// Now for the tricky part: if an event only has a URL then set URL to that
		// if event only has a description set URL to the event post page; and if
		// neither then don't set the URL option - remembering that if this is a
		// repeat of an event the URL is different for posts
		$ecp1_desc = _ecp1_render_default( $event, 'ecp1_description' ) ? null : ecp1_permalink_event( $event );
		$ecp1_url = _ecp1_render_default( $event, 'ecp1_url' ) ? null : urldecode( $event['ecp1_url'] );
		if ( ! is_null( $ecp1_desc ) && ! is_null( $ecp1_url ) ) {
			// Both given so render as link to post page
			$events_json[$loop_counter]['url'] = $ecp1_desc;
		} elseif ( ! is_null( $ecp1_desc ) ) {
			// Only a description: link to post page
			$events_json[$loop_counter]['url'] = $ecp1_desc;
		} elseif ( ! is_null( $ecp1_url ) ) {
			// Only a URL: link straight to it
			$events_json[$loop_counter]['url'] = $ecp1_url;
		}

		// If feature images are enabled by the them (aka Post Thumbnails) then show if there is one
		if ( function_exists( 'add_theme_support' ) && function_exists( 'get_the_post_thumbnail' ) ) {
			$attrs = array( 'title' => get_the_title( $event['post_id'] ), 'alt' => __( 'Event Logo' ) );
			if ( has_post_thumbnail( $event['post_id'] ) )
				$events_json[$loop_counter]['imageelem'] = get_the_post_thumbnail( $event['post_id'], 'thumbnail', $attrs );
		}
					
		// Successfully added an event increment the counter
		$loop_counter += 1;
	} catch( Exception $datex ) {
		continue; // ignore bad timestamps they shouldn't happen
	}

} // end foreach event

// Reset the query now the loop is done
wp_reset_query();

// JSON Encode the results
if ( ! $plain ) {
	print( json_encode( $events_json ) );
} else {
	print_r( $events_json );
	print( json_encode( $events_json ) );
}

// Don't close the php interpreter
/*?>*/
