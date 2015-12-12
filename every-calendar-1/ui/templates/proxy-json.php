<?php
/**
 * File that loads a list of events based on parameters and then
 * returns a JSON data set in FullCalendar format of those events.
 * This file proxies data for an external calendar provider.
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
// break if someone changes the calendar slug manually AND for the
// ecp1_proxy query param the regex is [a-z]+ which should match
// the array keys for the external providers.
$cal = $wp_query->query_vars['ecp1_cal'];
$pkey = $wp_query->query_vars['ecp1_proxy'];

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

// Lookup the provider and make sure it is active for this calendar
$provurl = null;
_ecp1_parse_calendar_custom( $cal->ID ); // load the calendar 
$ecp1_cals = _ecp1_calendar_meta( 'ecp1_external_cals' );
foreach ( $ecp1_cals as $_e_id => $line ) {
	if ( $line['provider'] == $pkey )
		$provurl = '' == $line['url'] ? '' : urldecode( $line['url'] );
}

// And error out if the provider was not located
if ( is_null( $provurl ) )
	_ecp1_template_error( __( 'Calendar does not include data for this external proxy.' ), 404, __( 'Calendar proxy not found' ) );


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

// An array of JSON parameters for the output
$events_json = array();

// Start processing the calendar
$provider = ecp1_get_calendar_provider_instance( $pkey, $cal->ID, $provurl );
$tz = ecp1_get_calendar_timezone();
$dtz = new DateTimeZone( $tz );
$now = new DateTime( 'now', $dtz );

// Load the events (from cache if required)
$continue = true;
if ( $provider->cache_expired( _ecp1_get_option( 'ical_export_external_cache_life' ) ) ) {
	if ( ! $provider->fetch( $start, $end, $dtz ) )
		_ecp1_template_error( __( 'Could not load events from source.' ), 502, __( 'Event source failure' ) ); // exits the interpreter
}

// Extract the events from the provider
$loop_counter = 0;
$events = $provider->get_events();
foreach( $events as $keyid => $event ) {
	// Load the meta for this event
	$emeta = $provider->get_meta( $keyid, array() );

	// The events timestamps will be a unix timestamp at the localtime of the
	// calendar that event is published on. If this event is published on a 
	// diferent calendar then the timezone may need to be adjusted.
	try {
		// Build UTC DateTime objects to begin with
		$estart = new DateTime( '@' . $event['start'] );
		$eend   = new DateTime( '@' . $event['end'] );
		$estart->setTimezone( $dtz );
		$eend->setTimezone( $dtz );

		// If the timezone has an offset then move in opposite direction
		$start_offset = $dtz->getOffset( $now );
		$end_offset = $dtz->getOffset( $now );
		if ( $start_offset < 0 ) $estart->modify( '+' . abs( $start_offset ) . ' second' );
		if ( $end_offset < 0 ) $eend->modify( '+' . abs( $end_offset ) . ' second' );
		if ( $start_offset > 0 ) $estart->modify( '-' . abs( $start_offset ) . ' second' );
		if ( $end_offset > 0 ) $eend->modify( '-' . abs( $end_offset ) . ' second' );

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
		if ( ! is_null( $ecp1_summary ) )
			$edescription .= sprintf( "%s\n", $ecp1_summary );
		if ( ! is_null( $ecp1_desc ) )
			$edescription .= sprintf( "\n%s", $ecp1_desc );
		$edescription = str_replace( array( "\r\n", "\r", "\n" ), "\n", $edescription );

		// Create an entry in the JSON array and write the some summary details
		$events_json[$loop_counter] = array(
			// The JS escapes HTML entities but doesn't check if &amp; is already there
			// and so that means we have to make sure there are no &amp;'s here
			'title'  => str_replace( '&amp;', '&', $etitle ),
			'start'  => $estart->format( 'c' ),	 // ISO8601 automatically handling DST and
			'end'	=> $eend->format( 'c' ),	   // the other seasonal variations in offset
			'allDay' => 'Y' == $event['all_day'],
			'description' => $edescription, 
			'location' => $elocation,
		);

		// Set the URL if one is given
		// As with the title revert &amp;'s
		if ( ! is_null( $ecp1_url ) )
			$events_json[$loop_counter]['url'] = str_replace( '&amp;', '&', $ecp1_url );

		// Enable map visibility if enabled and have a location
		if ( '1' == _ecp1_get_option( 'use_maps' ) ) {
			if ( ( array_key_exists( 'use_maps', $emeta ) && $emeta['use_maps'] ) ) {
				$events_json[$loop_counter]['showmap'] = true;
				$events_json[$loop_counter]['mark'] = true;
				if ( array_key_exists( 'lat', $emeta ) && array_key_exists( 'lng', $emeta ) &&
						! is_null( $emeta['lat'] ) && ! is_null( $emeta['lng'] ) ) {
					$events_json[$loop_counter]['lat'] = (float) $emeta['lat'];
					$events_json[$loop_counter]['lng'] = (float) $emeta['lng'];
				}
			}
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
