<?php
/**
 * Defines the meta fields for the event post type
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// The Event needs to know about calendars
require_once( ECP1_DIR . '/includes/data/calendar-fields.php' );

// An array of meta field names and default values
$ecp1_event_fields = array( 
	'ecp1_summary' => array( '', '' ), // value, default
	'ecp1_description' => array( '', '' ),
	'ecp1_url' => array( '', '' ),
	'ecp1_start_ts' => array( '', '' ),
	'ecp1_end_ts' => array( '', '' ),
	'ecp1_full_day' => array( '', 'N' ),
	'ecp1_calendar' => array( '', '' ),
	'ecp1_location' => array( '', '' ),
	'ecp1_coord_lat' => array( '', '' ),
	'ecp1_coord_lng' => array( '', '' ),
	'ecp1_map_zoom' => array( '', 1 ),
	'ecp1_map_placemarker' => array( '', '' ),
	'ecp1_showmarker' => array( '', '' ),
	'ecp1_showmap' => array( '', '' ),
	'ecp1_featured' => array( '', '' ),
	'ecp1_extra_cals' => array( array(), array() ),
	'ecp1_overwrite_color' => array( '', 'N' ),
	'ecp1_local_textcolor' => array( '', '' ),
	'ecp1_local_color' => array( '', '' ),

	// support for gravity forms custom post type plugin
	'gravity_ignore' => array( '', 'N' ),

	// repeating events support - uses a separate cache table
	'ecp1_repeating' => array( '', 'N' ),
	'ecp1_repeat_pattern' => array( '', '' ), // EveryCal_RepeatExpression::$TYPES
	'ecp1_repeat_custom_expression' => array( '', ''),
	'ecp1_repeat_pattern_parameters' => array( array(), array() ),
	'ecp1_repeat_termination' => array( '', '' ), // 4EVA | XTIMES | UNTIL
	'ecp1_repeat_terminate_at' => array( '', '' ), // X or UTC time for UNTIL
	'ecp1_repeat_last_changed' => array( '', 0 ), // TS when last changed
	
	// meta fields that describe the database structure
	'_meta' => array(
		'standalone' => array(	// $ecp1_event_fields key => postmeta table key
			'ecp1_start_ts' => 'ecp1_event_start',
			'ecp1_end_ts' => 'ecp1_event_end',
			'ecp1_calendar' => 'ecp1_event_calendar',
			'ecp1_featured' => 'ecp1_event_is_featured',
			'gravity_ignore' => 'ecp1_ignore_gravity',
			'ecp1_repeating' => 'ecp1_event_repeats',
		),
		'multiple_keys' => array( // $ecp1_event_fields key => postmeta table key which repeats for each value
			'ecp1_extra_cals' => 'ecp1_extra_calendar',
		),
		'calendar_tz' => 'UTC', // the TZ of the parent calendar
		'_loaded' => false, // custom fields not yet loaded
		'_id' => null, // the event ID

		// Names of custom fields to create with Gravity Forms
		'_gravity_fields' => array(
			'gravity_summary', 'gravity_description', 'gravity_url',
			'gravity_all_day', 'gravity_calendar', 'gravity_location',
			'gravity_start_date', 'gravity_start_date_format',
			'gravity_start_time', 'gravity_start_time_format',
			'gravity_end_date', 'gravity_end_date_format',
			'gravity_end_time', 'gravity_end_time_format',
		)
	)
);

// Function to parse the custom post fields into the fields above
function _ecp1_parse_event_custom( $post_id=-1 ) {
	global $post, $ecp1_event_fields;
	
	// Determine if we're using the global post or a parameter post
	// Parameter will take precedence over the global post
	if ( $post_id < 0 )
		$post_id = $post->ID;

	// For efficiency sake only do this if not loaded or loading a new one
	if ( $ecp1_event_fields['_meta']['_loaded'] && $post_id == $ecp1_event_fields['_meta']['_id'] )
		return;
	
	// Load the basic meta for this event post
	$custom = get_post_meta( $post_id, 'ecp1_event', true ); // all except standalone / multiple keys

	// parse the custom meta fields into the value keys
	if ( is_array( $custom ) ) {
		// load the remaining meta fields from standalone into $custom
		foreach( $ecp1_event_fields['_meta']['standalone'] as $field_key=>$table_key )
			$custom[$field_key] = get_post_meta( $post_id, $table_key, true );
	
		// load the multiple key values from their standalone keys
		foreach( $ecp1_event_fields['_meta']['multiple_keys'] as $field_key=>$table_key ) {
			$t = get_post_meta( $post_id, $table_key, false); // get all in an array
			if ( is_array( $t ) )
				$custom[$field_key] = $t;
		}
		
		// look at all the non-meta keys and copy the database value in or use defaults
		foreach( array_keys( $ecp1_event_fields ) as $key ) {
			if ( '_meta' != $key ) {
				if ( isset( $custom[$key] ) )
					$ecp1_event_fields[$key][0] = $custom[$key];
				else
					$ecp1_event_fields[$key][0] = $ecp1_event_fields[$key][1];
			}
		}

		// Now lookup the calendar for this event and store calendar timezone
		if ( $ecp1_event_fields['ecp1_calendar'][1] != $ecp1_event_fields['ecp1_calendar'][0] ) {
			_ecp1_parse_calendar_custom( $ecp1_event_fields['ecp1_calendar'][0] );
			$ecp1_event_fields['_meta']['calendar_tz'] = ecp1_get_calendar_timezone();
		} // otherwise use UTC default

		// Flag the settings as loaded
		$ecp1_event_fields['_meta']['_loaded'] = true;
		$ecp1_event_fields['_meta']['_id'] = $post_id;
	} elseif ( '' == $custom ) { // it does not exist yet (reset to defaults so empty settings don't display previous events details)
		foreach( $ecp1_event_fields as $key=>$values ) {
			if ( '_meta' != $key )
				$ecp1_event_fields[$key][0] = $ecp1_event_fields[$key][1];
		}
		// Flag as loaded
		$ecp1_event_fields['_meta']['_loaded'] = true;
		$ecp1_event_fields['_meta']['_id'] = $post_id;
	} else { // if the setting exists but is something else
		printf( '<pre>%s</pre>', __( 'Every Calendar +1 plugin found non-array meta fields for this event.' ) );
	} 
}

// Function that returns true if value is default
function _ecp1_event_meta_is_default( $meta ) {
	global $ecp1_event_fields;
	if ( ! isset( $ecp1_event_fields[$meta] ) )
		return false; // unknown meta can't be at default
	if ( ! $ecp1_event_fields['_meta']['_loaded'] )
		return true; // if not loaded then treat as default
	
	return $ecp1_event_fields[$meta][1] == $ecp1_event_fields[$meta][0];
}

// Returns the events calendar timezone
function _ecp1_event_calendar_timezone() {
	global $ecp1_event_fields;
	if ( ! $ecp1_event_fields['_meta']['_loaded'] )
		return null;
	return $ecp1_event_fields['_meta']['calendar_tz'];
}

// Function that gets the meta value the get_default parameter
// controls what to do if settings are not yet loaded. If it is
// false and not loaded NULL will be returned, else the default.
function _ecp1_event_meta( $meta, $get_default=true ) {
	global $ecp1_event_fields;
	if ( ! isset( $ecp1_event_fields[$meta] ) )
		return null; // unknown meta is always NULL

	// if loaded then return value
	if ( $ecp1_event_fields['_meta']['_loaded'] )
		return $ecp1_event_fields[$meta][0];
	elseif ( $get_default ) // not loaded but want defaults
		return $ecp1_event_fields[$meta][1];
	else // not loaded and want NULL if so
		return null;
}

// Returns the ID of the event the meta is for
function _ecp1_event_meta_id() {
	global $ecp1_event_fields;
	if ( ! isset( $ecp1_event_fields['_meta'] ) || ! $ecp1_event_fields['_meta']['_loaded'] )
		return -1; // not loaded
	return $ecp1_event_fields['_meta']['_id'];
}

// Determines if any Gravity Forms data exists for this event
function _ecp1_event_gravity_meta_exists() {
	global $ecp1_event_fields;
	$gfields = $ecp1_event_fields['_meta']['_gravity_fields'];
	foreach( $gfields as $gfield ) {
		$meta = get_post_meta( _ecp1_event_meta_id(), $gfield, true );
		if ( '' !== $meta )
			return true;
	}
	return false;
}

// Does this event want to ignore any Gravity Forms meta?
function _ecp1_event_ignore_gravity_meta() {
	return 'Y' == _ecp1_event_meta( 'gravity_ignore' );
}

// Converts the Gravity Forms fields to Every Calendar fields
// tz can be a string name of a timezone for this event or null
// if the calendar will be used for the timezone.
function _ecp1_event_gravity2ecp1( $tz=null ) {
	global $ecp1_event_fields;
	$event_id = _ecp1_event_meta_id();

	// Load all the Gravity meta values
	$gfields = $ecp1_event_fields['_meta']['_gravity_fields'];
	foreach( $gfields as $gfield )
		$$gfield = get_post_meta( _ecp1_event_meta_id(), $gfield, true );
	
	//Added PAO 6 Jan 15 to default the start dates and date formats
	$gravity_start_date_format = $gravity_start_date_format ? $gravity_start_date_format : 'm/d/Y';
	$gravity_end_date_format = $gravity_end_date_format ? $gravity_end_date_format : 'm/d/Y';
	$gravity_end_date = $gravity_end_date ? $gravity_end_date : $gravity_start_date;

	// If calendar tz is loaded (no calendar) and no tz parameter and no gravity
	// calendar then we have to assume TZ=UTC so check for it now
	if ( '' == $gravity_calendar && is_null( $tz ) )
		$tz = new DateTimeZone( $ecp1_event_fields['_meta']['calendar_tz'] );
	else if ( ! is_null( $tz ) )
		$tz = new DateTimeZone( $tz );
	else if ( '' != $gravity_calendar ) {
		_ecp1_parse_calendar_custom( $gravity_calendar );
		$tz = new DateTimeZone( ecp1_get_calendar_timezone() );
	}

	/*
		gravity_summary		ecp1_summary
		gravity_description	ecp1_description
		gravity_url		ecp1_url
		gravity_location	ecp1_location
		gravity_all_day		ecp1_full_day

		gravity_calendar	ecp1_event_calendar
		
		gravity_start_date	ecp1_event_start (using format)
		gravity_start_time
		gravity_end_date	ecp1_event_end (using format)
		gravity_end_time
	 */
	$save_fields_group = array();
	$save_fields_alone = array();

	// Summary
	$save_fields_group['ecp1_summary'] = _ecp1_event_meta( 'ecp1_summary' );
	if ( '' != $gravity_summary )
		$save_fields_group['ecp1_summary'] = $gravity_summary;

	// Description
	$save_fields_group['ecp1_description'] = _ecp1_event_meta( 'ecp1_description' );
	if ( '' != $gravity_description )
		$save_fields_group['ecp1_description'] = $gravity_description;

	// Event URL
	$save_fields_group['ecp1_url'] = _ecp1_event_meta( 'ecp1_url' );
	if ( '' != $gravity_url )
		$save_fields_group['ecp1_url'] = $gravity_url;

	// Location
	$save_fields_group['ecp1_location'] = _ecp1_event_meta( 'ecp1_location' );
	if ( '' != $gravity_location )
		$save_fields_group['ecp1_location'] = $gravity_location;

	// Does the event run all day?
	$save_fields_group['ecp1_full_day'] = _ecp1_event_meta( 'ecp1_full_day' );
	if ( '' != $gravity_all_day )
		$save_fields_group['ecp1_full_day'] = $gravity_all_day;


	// Calendar - ALONE
	$save_fields_alone['ecp1_event_calendar'] = _ecp1_event_meta( 'ecp1_calendar' );
	if ( '' != $gravity_calendar )
		$save_fields_alone['ecp1_event_calendar'] = $gravity_calendar;


	// Parse the start date / time with the formats
	// This requires PHP 5.3.0
	$start = null;
	if ( ECP1_PHP5 >= 3 ) {
		if ( '' != $gravity_start_date && '' != $gravity_start_time &&
				'' != $gravity_start_date_format && '' != $gravity_start_time_format ) {
			$start = DateTime::createFromFormat( $gravity_start_date_format . ' ' . $gravity_start_time_format,
					$gravity_start_date . ' ' . $gravity_start_time, $tz );
		} else if ( '' != $gravity_start_date && '' != $gravity_start_date_format ) {
			$start = DateTime::createFromFormat( $gravity_start_date_format, $gravity_start_date, $tz );
			$start->setTime( 0, 0, 0 );
		}

		if ( ! is_null( $start ) )
			$save_fields_alone['ecp1_event_start'] = $start->getTimestamp();
	}

	// Parse the end date / time with the formats
	// This requires PHP 5.3.0
	$end = null;
	if ( ECP1_PHP5 >= 3 ) {
		if ( '' != $gravity_end_date && '' != $gravity_end_time &&
				'' != $gravity_end_date_format && '' != $gravity_end_time_format ) {
			$end = DateTime::createFromFormat( $gravity_end_date_format . ' ' . $gravity_end_time_format,
					$gravity_end_date . ' ' . $gravity_end_time, $tz );
		} else if ( '' != $gravity_end_date && '' != $gravity_end_date_format ) {
			$end = DateTime::createFromFormat( $gravity_end_date_format, $gravity_end_date, $tz );
		}

		if ( ! is_null( $end ) )
			$save_fields_alone['ecp1_event_end'] = $end->getTimestamp();
	}

	// Ignore Gravity meta from now on
	$save_fields_alone['ecp1_ignore_gravity'] = 'Y';

	// Keep the remaining meta values as are set
	foreach( array( 'ecp1_coord_lat', 'ecp1_coord_lng', 'ecp1_map_zoom',
		'ecp1_map_placemarker', 'ecp1_showmarker', 'ecp1_showmap', 'ecp1_featured' ) as $key ) {
		$save_fields_group[$key] = _ecp1_event_meta( $key );
	}


	// Save the post meta information
	update_post_meta( $event_id, 'ecp1_event', $save_fields_group );
	foreach( $save_fields_alone as $key=>$value )
		update_post_meta( $event_id, $key, $value );
}

// Don't close the php interpreter
/*?>*/
