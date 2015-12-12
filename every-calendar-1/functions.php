<?php
/**
 * Every Calendar +1 Plugin Helper Functions
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Custom function for testing default values
function _ecp1_render_default( &$event, $key ) {
	global $ecp1_event_fields;
	return $event[$key] == $ecp1_event_fields[$key][1];
}

// Returns a permalink to the given $ecp1_event_fields event
function ecp1_permalink_event( &$event ) {
	// If a non-repeating event then just return straight permalink
	$postperma = get_permalink( $event['post_id'] );
	if ( 'Y' == $event['ecp1_repeating'] ) {
		// Get the start date of the event 
		$sdte = new DateTime( $event['_meta']['cache_start'], new DateTimeZone( $event['_meta']['calendar_tz'] ) );
		$pstring = sprintf( 'ecp1_repeat=%s', $sdte->format( 'Y-n-j' ) );
		// If there is already ? params in the URL append otherwise create
		if ( false === strpos( '?', $postperma ) )
			$postperma .= '?' . $pstring;
		else
			$postperma .= '&' . $pstring;
	}
	return $postperma;
}

// Function that applies WordPress content filters to the given string
function ecp1_the_content( $content ) {
	// $c = apply_filters( 'the_content', $content );
	// We can't call the apply_filters directly because this function
	// is called from within a filter hooked to 'the_content' which
	// will create an infinite recursive loop and segfaults
	$c = wptexturize( $content );
	$c = convert_smilies( $c );
	$c = convert_chars( $c );
	$c = wpautop( $c );
	$c = shortcode_unautop( $c );
	$c = prepend_attachment( $c );
	$c = str_replace(']]>', ']]&gt;', $c);
	return $c;
}

// Returns all calendars the user can edit
function _ecp1_current_user_calendars() {
	return get_posts( array( 'post_type'=>'ecp1_calendar', 'suppress_filters'=>false, 'numberposts'=>-1, 'nopaging'=>true ) );
}

// Helper to convert a UTC offset to a timezone
// This is a hardcoded map that SHOULD only ever get used if the
// WordPress option 'timezone_string' is empty and the calendar
// is set to use WordPress default timezone.
// 
// http://www.phpbuilder.com/board/showthread.php?t=10359010
function _ecp1_gmt_offset_to_timezone( $offset=0 ) {
	// This is only meant to be rough
	$tzs = array(
		'-12'   => 'Pacific/Kwajalein',
		'-11'   => 'Pacific/Samoa',
		'-10'   => 'Pacific/Honolulu',
		 '-9'   => 'America/Juneau',
		 '-8'   => 'America/Los_Angeles',
		 '-7'   => 'America/Denver',
		 '-6'   => 'America/Mexico_City',
		 '-5'   => 'America/New_York',
		 '-4'   => 'America/Caracas',
		 '-3.5' => 'America/St_Johns',
		 '-3'   => 'America/Argentina/Buenos_Aires',
		 '-2'   => 'America/Noronha', // close enough
		 '-1'   => 'Atlantic/Azores',
		 '0'    => 'Europe/London',
		 '1'    => 'Europe/Paris',
		 '2'    => 'Europe/Helsinki',
		 '3'    => 'Europe/Moscow',
		 '3.5'  => 'Asia/Tehran',
		 '4'    => 'Asia/Baku',
		 '4.5'  => 'Asia/Kabul',
		 '5'    => 'Asia/Karachi',
		 '5.5'  => 'Asia/Calcutta',
		 '6'    => 'Asia/Colombo',
		 '7'    => 'Asia/Bangkok',
		 '8'    => 'Asia/Singapore',
		 '9'    => 'Asia/Tokyo',
		 '9.5'  => 'Australia/Darwin',
		 '10'   => 'Pacific/Guam',
		 '11'   => 'Asia/Magadan',
		 '12'   => 'Asia/Kamchatka'
	);

	// Look for the offset if it doesn't exist e.g. 6.5 round up
	if ( ! array_key_exists( "$offset", $tzs ) )
		$offset = round( (int) $offset, 0, PHP_ROUND_HALF_UP );
	return $tzs["$offset"];
}

// Prettify a raw timezone name string into City +/- Offset
function ecp1_timezone_display( $tz ) {
	try {
		$dtz = new DateTimeZone( $tz );
		$offset = $dtz->getOffset( new DateTime( 'now' ) ); // automatically handles DST
		$offset = 'UTC' . ( $offset < 0 ? ' - ' : ' + ' ) . ( abs( $offset/3600 ) );
		$ex = explode( '/', $dtz->getName() );
		$name = str_replace( '_', ' ', ( isset( $ex[2] ) ? $ex[2] : isset( $ex[1] ) ? $ex[1] : $dtz->getName() ) ); // Continent/Country/City
		return sprintf ( '%s (%s)', $name, $offset );
	} catch( Exception $tzmiss ) {
		// not a valid timezone
		return __( 'Unknown' );
	}
}

// Creates an HTML select of all timezones
// Based on http://neo22s.com/timezone-select-for-php/
function _ecp1_timezone_select( $id, $pick='_', $extra_attrs=null ) {
	$outstr = sprintf( '<select id="%s" name="%s" %s>', $id, $id, $extra_attrs );
	$outstr .= sprintf( '<option value="_"%s>%s</option>', '_' == $pick ? ' selected="selected"' : '', __( 'WordPress Timezone' ) );
	$timezone_identifiers = DateTimeZone::listIdentifiers();
	$continent = '';
	foreach( $timezone_identifiers as $value ) {
		if ( preg_match( '/^(Africa|America|Antartica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)\//', $value ) ){
			$ex = explode( '/', $value ); //obtain continent and city
			if ( $continent != $ex[0] ) {
				if ( '' != $continent ) $outstr .= sprintf( '</optgroup>' );
				$outstr .= sprintf( '<optgroup label="%s">', $ex[0] );
			}

			$city = ecp1_timezone_display( $value );
			$continent = $ex[0]; // for next loop
			$outstr .= sprintf( '<option value="%s"%s>%s</option>', $value, $value == $pick ? ' selected="selected"' : '', $city );
		}
	}
	$outstr .= sprintf( '</optgroup></select>' );
	return $outstr;
}


// Function that returns the timezone string for a calendar
// by looking at the calendar and WordPress settings
function _ecp1_get_calendar_timezone($v) {
	$raw_timezone = 'UTC';
	$timezone = get_option( 'timezone_string' );    // Use the WordPress default if available
	$gmt_offset = get_option( 'gmt_offset' );       // or can use the GMT Offset and map (approximately)
	
	if ( $v != '_' && $v != '' //! _ecp1_calendar_meta_is_default( 'ecp1_timezone' ) // Calendar TZ Set
			&& _ecp1_get_option( 'tz_change' ) )     //   and changes are allowed
		$raw_timezone = $v; //_ecp1_calendar_meta( 'ecp1_timezone', false );
	elseif ( ! empty( $timezone ) )   // Using WordPress city based timezone
		$raw_timezone = $timezone;
	elseif ( ! empty( $gmt_offset ) ) // Using WordPress GMT Offset
		$raw_timezone = _ecp1_gmt_offset_to_timezone( $gmt_offset ); // this is REALLY approximate

	// go back to UTC if null
	if ( is_null( $raw_timezone) )
		$raw_timezone = 'UTC';
	return $raw_timezone;
}

// Wrapper around the above function
function ecp1_get_calendar_timezone() {
	return _ecp1_get_calendar_timezone( _ecp1_calendar_meta( 'ecp1_timezone', false ) );
}

// Return a formatted date range string based on some event details
function ecp1_formatted_date_range( $stimestamp, $etimestamp, $allday, $tzstring ) {
	// Use the default WordPress dateformat timeformat strings
	$datef = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	$dates = $stimestamp;
	$datee = $etimestamp;
	$tz = new DateTimeZone( $tzstring );
	$sameday = null;

	// Handle bad dates by creating a DateTime object for both
	try {
		$dates = new DateTime( "@$dates" );
		$dates->setTimezone( $tz );
	} catch( Exception $serror ) {
		$dates = __( 'Unknown' );
		$sameday = false;
	}
	
	try {
		$datee = new DateTime( "@$datee" );
		$datee->setTimezone( $tz );
	} catch( Exception $eerror ) {
		$datee = __( 'Unknown' );
		$sameday = false;
	}

	return ecp1_formatted_datetime_range( $dates, $datee, $allday, $sameday );
}

function ecp1_formatted_datetime_range( $dates, $datee, $allday, $sameday=null ) {
	// Check if events run on the same day
	$datef = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	if ( null === $sameday ) { // no error occured
		$sameday = $dates->format( 'Ymj' ) == $datee->format( 'Ymj' );
		
		// If this is an all day event and the time is start=00:00 and end=23:59
		// then there is no useful information in the time fields so don't display them
		if ( 'Y' == $allday && 
			( ( '0000' == $dates->format( 'Hi' ) && '2359' == $datee->format( 'Hi' ) ) ||
			  ! _ecp1_get_option( 'show_time_on_all_day' ) ) )
			$datef = get_option( 'date_format' );
	}

	// Format the dates as strings if they're valid
	if ( $dates instanceof DateTime )
		$dates = $dates->format( $datef );
	if ( $datee instanceof DateTime ) {
		if ( true === $sameday ) {
			$datee = $datee->format( get_option( 'time_format' ) );
		} else {
			$datee = $datee->format( $datef );
		}
	}

	// Do we need an (all day) message?
	$all_day = _ecp1_get_option( 'show_all_day_message' ) ? __( '(all day)' ) : '';

	// If the dates are the same and full day just say that
	if ( 'Y' == $allday && $sameday )
		$ecp1_time = sprintf( '%s %s', $dates, $all_day );
	else // else give a range 
		$ecp1_time = sprintf( '%s - %s %s', $dates, $datee, 'Y' == $allday ? $all_day : '' );

	return $ecp1_time;
}

// A shortcut function for erroring out as plaintext
function _ecp1_template_error( $msg=null, $http_code=200, $http_msg='Every Calendar +1 Plugin Error' ) {
	if ( ! is_null( $msg ) ) {
		header( 'Content-Type:text/html' );
		header( sprintf( 'HTTP/1.1 %s %s', $http_code, $http_msg ), 1 );
		header( sprintf( 'Status: %s %s', $http_code, $http_msg ), 1 );
		printf( '<!DOCTYPE html><html><title>%s</title><body><p>%s</p></body></html>', $http_msg, $msg );
		exit(); // finish the stream
	}
}

// Don't close the php interpreter
/*?>*/
