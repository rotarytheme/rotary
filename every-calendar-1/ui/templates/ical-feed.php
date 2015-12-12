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

// Lookup the calendar post
$cal = get_page_by_path( $cal, OBJECT, 'ecp1_calendar' );
if ( is_null( $cal ) )
	_ecp1_template_error( __( 'No such calendar.' ), 404, __( 'Calendar Not Found' ) ); // exit on error

// Reset the default WordPress query just in case
wp_reset_query();

// Remove any actions on loop
remove_all_actions( 'loop_start' );
remove_all_actions( 'the_post' );
remove_all_actions( 'loop_end' );

// Encode as a calendar unless in debug mode
if ( ! empty( $wp_query->query_vars[ECP1_TEMPLATE_TEST_ARG] ) &&
		'1' == $wp_query->query_vars[ECP1_TEMPLATE_TEST_ARG] ) {
	header( 'Content-Type: text/plain' );
} else {
	header( 'Content-Type: text/calendar' );
	header( 'Content-Disposition: attachement; filename="events.ics"' );
}


// Ready to start rendering an ical file

_ecp1_parse_calendar_custom( $cal->ID ); // Get the calendar meta data
$tz = ecp1_get_calendar_timezone();	  // and the effective timezone
$dtz = new DateTimeZone( $tz );
$ex_cals = _ecp1_calendar_meta( 'ecp1_external_cals' ); // before loop
$my_id = $cal->ID; // because event meta reparses its calendars meta

// ICAL HEADERS / CALENDAR DETAILS
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//<?php echo get_option( 'blogname' ) . ' - ' . $cal->post_name; ?>//NONSGML EveryCal+1 Events//EN
METHOD:PUBLISH
X-WR-CALNAME:<?php echo $cal->post_title . "\n"; ?>
X-WR-CALDESC:<?php echo _ecp1_calendar_meta( 'ecp1_description' ) . "\n"; ?>
X-WR-TIMEZONE:<?php echo $dtz->getName() . "\n"; ?>
X-ORIGINAL-URL:<?php echo get_permalink( $cal->ID ) . "\n"; ?>
CALSCALE:GREGORIAN
<?php


// Get the max before / max after ranges from the plugin settings
// and use them as timestamps for the event lookup queries
// Need a reference to the current time at UTC
$now = new DateTime( NULL, new DateTimeZone( 'UTC' ) );
$_now = (int) $now->format( 'U' );
$start = $_now - abs( _ecp1_get_option( 'ical_export_start_offset' ) );
$end = $_now + abs( _ecp1_get_option( 'ical_export_end_offset' ) );

// Get the $ecp1_event_fields pseudo arrays
$events = EveryCal_Scheduler::GetEvents( $cal->ID, $start, $end );

// Loop over each event and render an iCal block
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
		$format = 'Ymd\THis';

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

		// The start and end times are expecting YYYYMMDDTHHMMSS
		// this is changed in V1.0 where we've set the timezone
		// above so the format is local time for the event
		$estart = $estart->format( $format );
		$eend   = $eend->format( $format );

		// Create a location string like appears on the event page
		$elocation = strip_tags( $event['ecp1_location'] );

		// Now for the tricky part: if an event only has a URL then set URL to that
		// if event only has a description set URL to the event post page; and if
		// neither then don't set the URL option - remembering that if this is a
		// repeat of an event the URL is different for posts
		$edescription = sprintf( "%s", strip_tags( $event['ecp1_summary'] ) );
		$ecp1_desc = _ecp1_render_default( $event, 'ecp1_description' ) ? null : strip_tags( $event['ecp1_description'] );
		$ecp1_url = _ecp1_render_default( $event, 'ecp1_url' ) ? ecp1_permalink_event( $event ) : urldecode( $event['ecp1_url'] );
		if ( ! is_null( $ecp1_desc ) && ! is_null( $ecp1_url ) )
			$edescription .= "\\n------------------";
		if ( ! is_null( $ecp1_url ) )
			$edescription .= sprintf( "\\n%s", $ecp1_url );
		else
			$edescription .= sprintf( "\\n%s", ecp1_permalink_event( $event ) );
		if ( ! is_null( $ecp1_desc ) )
			$edescription .= sprintf( "\\n%s", $ecp1_desc );
		// Replace all new line combinations will literal \n 
		$edescription = str_replace( array( "\r\n", "\r", "\n" ), array( "\\n", "\\n", "\\n" ), $edescription );

// Output this event in iCal
?>
BEGIN:VEVENT
DTSTART:<?php echo $estart . "\n"; ?>
DTEND:<?php echo $eend . "\n"; ?>
SUMMARY:<?php echo get_the_title( $event['post_id'] ) . "\n"; ?>
LOCATION:<?php echo $elocation . "\n"; ?>
DESCRIPTION:<?php echo $edescription . "\n"; ?>
END:VEVENT
<?php

	// Some form of error occured (probably with the dates)
	} catch( Exception $datex ) {
		continue; // ignore bad timestamps they shouldn't happen
	}

} // END FOREACH EVENT

// If external calendar providers should be syndicated then send them too
if ( '1' == _ecp1_get_option( 'ical_export_include_external' ) ) {

	// Loop over this calendars external calendars
	foreach( $ex_cals as $ex_cal ) {
		$calprov = ecp1_get_calendar_provider_instance( $ex_cal['provider'], $my_id, urldecode( $ex_cal['url'] ) );
		if ( null == $calprov )
			continue; // failed to load
		$continue = true;
		if ( $calprov->cache_expired( _ecp1_get_option( 'ical_export_external_cache_life' ) ) )
			$continue = $calprov->fetch( $start, $end, $dtz );

		if ( $continue ) { // fetched or not but is ok
			$evs = $calprov->get_events();
			foreach( $evs as $eventid=>$event ) {

				try {
					$e  = $event['start'];
					$es = new DateTime( "@$e" ); // requires PHP 5.2.0
					$e  = $event['end'];
					$ee = new DateTime( "@$e" ); // 5.2.0 again

					// The start and end times are expecting YYYYMMDDTHHMMSSZ
					$estart = $es->format( 'Ymd\THis\Z' );
					$eend   = $ee->format( 'Ymd\THis\Z' );

					// The summary and location can be verbatim
					$etitle = $event['title'];
					$elocation = strip_tags( $event['location'] );

					// Now for the tricky part: description needs to have URL and/or local
					// description text depending on what was set in the admin and the sumary
					// should be prefixed in either case
					$edescription = '';
					$ecp1_summary = is_null( $event['summary'] ) ? null : strip_tags( $event['summary'] );
					$ecp1_desc = is_null( $event['description'] ) ? null : strip_tags( $event['description'] );
					$ecp1_url = is_null( $event['url'] ) ? null : urldecode( $event['url'] );
					if ( ! is_null( $ecp1_summary ) && ( ! is_null( $ecp1_desc ) || ! is_null( $ecp1_url ) ) )
						$edescription .= sprintf( "%s\\n------------------", $ecp1_summary );
					if ( ! is_null( $ecp1_desc ) )
						$edescription .= sprintf( "\\n%s", $ecp1_desc );
					if ( ! is_null( $ecp1_url ) )
						$edescription .= sprintf( "\\n%s", $ecp1_url );
					$edescription = str_replace( array( "\r\n", "\r", "\n" ), array( "\\n", "\\n", "\\n" ), $edescription );

// Output the external cached calendars
?>
BEGIN:VEVENT
DTSTART:<?php echo $estart . "\n"; ?>
DTEND:<?php echo $eend . "\n"; ?>
SUMMARY:<?php echo $etitle . "\n"; ?>
LOCATION:<?php echo $elocation . "\n"; ?>
DESCRIPTION:<?php echo $edescription . "\n"; ?>
END:VEVENT
<?php
				} catch( Exception $datex ) {
					continue; // ignore bad timestamps they shouldn't happen
				}

			} // foreach event
		} // events found
	} // foreach external calendar
} // include externals

// Reset the query now the loop is done
wp_reset_query();

?>
END:VCALENDAR
<?php

// Don't close the php interpreter
/*?>*/
