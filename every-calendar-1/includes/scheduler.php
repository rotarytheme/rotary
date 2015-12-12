<?php
/**
 * Every Calendar Scheduler Interface
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// We need to know about calendars and events
require_once( ECP1_DIR . '/includes/data/event-fields.php' );
require_once( ECP1_DIR . '/includes/data/calendar-fields.php' );
require_once( ECP1_DIR . '/includes/data/sql.php' );

// Also need to know about expressions and exceptions
require_once( ECP1_DIR . '/includes/repeat-expression.php' );
require_once( ECP1_DIR . '/includes/repeat-exception.php' );

// We also need the helper functions
require_once( ECP1_DIR . '/functions.php' );

// Sort function for EveryCal_Scheduler::GetEvents
function ecsge_sort( $a, $b ) {
	if ( $a['ecp1_start_ts'] == $b['ecp1_start_ts'] )
		return 0;
	return ( $a['ecp1_start_ts'] < $b['ecp1_start_ts'] ? -1 : 1 );
}

/**
 * EveryCal+1 Scheduler Class
 * Manages the event schedule for once off and repeating events
 * by caching look ups of repeating events and reading forward
 * when the range is requested.
 *
 * You do not need to instantiate this class ALL functions are static.
 */
class EveryCal_Scheduler
{

	/**
	 * CountCache Function
	 * Returns a count of the future cached repeats of the given event.
	 *
	 * @param $event_id The event to could the cache for
	 * @return Number of future cached events (0 if not repeating)
	 */
	public static function CountCache( $event_id )
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'ecp1_cache';
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE start >= CURDATE() AND post_id = %s", $event_id ) );
		if ( ! $count ) $count = 0;
		return $count;
	}
	
	/**
	 * BuildCache Function
	 * Creates cached repeats for all events in the calendar between
	 * the given start and end date range. The input timestamps are
	 * used as if at UTC/GMT/ZULU while this function only really
	 * required a number as the input if you do not use GMT times
	 * the cache range will be out by your locations offset.
	 *
	 * @param $cal_id The Calendar to build the cache for
	 * @param $start The start timestamp (int) to build forward from
	 * @param $end The end timestamp (int) to build forward until
	 * @return True or False indicating success of caching operation
	 */
	public static function BuildCache( $cal_id, $start, $end )
	{
		// We're going to do this using direct database calls to avoid overhead
		global $wpdb, $ecp1_event_fields;
		$post = $wpdb->prefix . 'posts';
		$meta = $wpdb->prefix . 'postmeta';
		$cache = $wpdb->prefix . 'ecp1_cache';

		// Get the calendars timezone for the cache build
		_ecp1_parse_calendar_custom( $cal_id );
		$tz = new DateTimeZone( ecp1_get_calendar_timezone() );
		$is_feature = _ecp1_calendar_show_featured( $cal_id );

		// Convert the timestamps to DateTime objects
		$sdt = null; $edt = null;
		try {
			$sdt = new DateTime( "@$start" ); $sdt->setTimezone( $tz ); // PHP 5.2.0
			$edt = new DateTime( "@$end" ); $edt->setTimezone( $tz );
		} catch( Exception $dex ) {
			error_log( 'WP Every Calendar +1: The start and end cache dates are invalid!' );
			return false;
		}

		// Make sure the parameters make sense before using them
		if ( $sdt > $edt )
			return true; // not an error just no cache to build

		// There is a setting to control maximum build size for a cache
		if ( ( $edt->format( 'U' ) - $sdt->format( 'U' ) > _ecp1_get_option( 'max_repeat_cache_block' ) )
				&& 	_ecp1_get_option( 'enforce_repeat_cache_size' ) ) {
			error_log( 'WP Every Calendar +1: The cache option is too small to build the requested range!' );
			return false; // can't build cache for this date range
		}

		// Because events can be on different calendars cache the timezone -> ids
		$events_to_cache = array();

		// Get all the events that are attached to this calendar which repeat
		$events = $wpdb->get_col( $wpdb->prepare(
			EveryCal_Query::Q( 'REPEATS_ON_CALENDAR' ),
			$cal_id
		), 0 ); // 0 is not necessary but to be explicity
		$events_to_cache[$cal_id] = array( 'tz' => $tz, 'set' => $events );

		// Get all the events that have this as an extra calendar which repeat
		$events = $wpdb->get_results( $wpdb->prepare(
			EveryCal_Query::Q( 'REPEATS_ON_EXTRA' ),
			$cal_id
		), ARRAY_N ); // numerical array indexed
		if ( $events != null && count( $events ) > 0 ) {
			foreach( $events as $row ) {
				_ecp1_parse_calendar_custom( $row[0] );
				$ntz = new DateTimeZone( ecp1_get_calendar_timezone() );
				if ( ! array_key_exists( $row[0], $events_to_cache ) )
					$events_to_cache[$row[0]] = array( 'tz' => $ntz, 'set' => array() );
				$events_to_cache[$row[0]]['set'][] = $row[1];
			}
		}

		// Get all the feature events if this is a feature calendar
		if ( $is_feature ) {
			// WP3.5 made $wpdb->prepare() require a second argument
			// to ensure queries are sanitised; the FEATURED_REPEATS
			// query does NOT require params so removed prepare.
			$events = $wpdb->get_results( /*$wpdb->prepare(*/
				EveryCal_Query::Q( 'FEATURED_REPEATS' )
			/*)*/, ARRAY_N ); // numerical array indexed
			if ( $events != null && count( $events ) > 0 ) {
				foreach( $events as $row ) {
					// Don't add duplicates if the event is featured on this calendar
					if ( $row[0] == $cal_id )
						continue;
					// Parse the calendar meta build timezone and append
					_ecp1_parse_calendar_custom( $row[0] );
					$ntz = new DateTimeZone( ecp1_get_calendar_timezone() );
					if ( ! array_key_exists( $row[0], $events_to_cache ) )
						$events_to_cache[$row[0]] = array( 'tz' => $ntz, 'set' => array() );
					$events_to_cache[$row[0]]['set'][] = $row[1];
				}
			}
		}
		
		// Call BuildEventCache for each of the events in it's calendar timezone
		$success = true;
		foreach( $events_to_cache as $my_cal => $tzevents ) {
			if ( $tzevents['set'] == null || count( $tzevents['set'] ) == 0 ) // no events
				continue;
			foreach( $tzevents['set'] as $event_id )
				$success = self::BuildEventCache( $event_id, $sdt, $edt, $tzevents['tz'] );
		}

		// Restore the real calendar meta data (incase it was already loaded)
		_ecp1_parse_calendar_custom( $cal_id );

		// Return the result of caching each event
		return $success;
	}
	
	/**
	 * BuildCache Function for Event (private)
	 *
	 * @param $event_id The Event to build the cache for
	 * @param $start The start DateTime to build forward from (TZ)
	 * @param $end The end DateTime to build forward until (TZ)
	 * @param $tz The time zone the dates for the event are in
	 * @return True or False indicating success of caching operation
	 */
	private static function BuildEventCache( $event_id, $start, $end, $tz )
	{
		// There are some custom meta values we need for the event
		global $wpdb; $table_name = $wpdb->prefix . 'postmeta';

		// Lookup the event start and end times and the repeat from
		// and repeat to times then use the events repeat expression
		// to get the repeat dates but only if the event repeats...
		// NOTE: In theory the BuildCache function only passes events
		// that repeat - but to avoid any potential overlap check it.
		_ecp1_parse_event_custom( $event_id );
		if ( 'Y' != _ecp1_event_meta( 'ecp1_repeating' ) )
			return true; // not a repeating event so cache built

		// Clone the start and end dates for the range
		$cend   = clone $end;
		$cstart = clone $start;
		$epoch  = new DateTime( '@' . _ecp1_event_meta( 'ecp1_start_ts' ) );
		$epoch->setTimezone( $tz ); // because used @timestamp

		// Make sure that cache will exist for this event 
		if ( $end <= $epoch )  return true; // no cache to build
		if ( $start < $epoch ) $cstart = clone $epoch; // start from epoch

		// Load the event termination parameters 
		$termination = _ecp1_event_meta( 'ecp1_repeat_termination' );
		$terminate_at = _ecp1_event_meta( 'ecp1_repeat_terminate_at' );

		// If this is a repeat until style repeat make not past until
		// also if the end point is past the termination point adjust
		if ( $termination == 'UNTIL' ) {
			try {
				$terminate_at = new DateTime( "@$terminate_at" );
				$terminate_at->setTimezone( $tz );
				if ( $start >= $terminate_at )
					return true; // nothing to cache
				if ( $end > $terminate_at )
					$cend = clone $terminate_at;
			} catch( Exception $uex ) {
				error_log( 'WP Every Calendar +1: Repeat UNTIL terminate date is unknown!' );
				return false; // can't build if unknown
			}
		}

		// Alternatively if this is a repeat X times then the cache
		// is always from epoch to the end parameter because we 
		// need to compute all occurances to count X. Technically
		// this violates the max cache rule but that rule is more
		// to stop people causing infinite loops for 4EVA events.
		if ( $termination == 'XTIMES' ) {
			$cstart = clone $epoch;
		}

		// Examine the existing cache ranges for this event, if any,
		// and build a start and end date that is the largest range
		// which is continuous (which may mean rebuilding some cache)
		//
		// If a range already exists then we don't need to do any building.
		$ranges_data = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value FROM $table_name WHERE post_id = %s AND meta_key = %s",
			$event_id, 'ecp1_repeat_cache_ranges'
		) );
		// Convert the data to an array
		$ranges = $ranges_data != null ? unserialize( $ranges_data ) : array();
		if ( count( $ranges ) > 0 ) {
			$contains_start = $contains_end = -1;
			for( $i=0; $i<count( $ranges ); $i++ ) {
				if ( $ranges[$i]['start'] <= $cstart->format( 'U' ) && $cstart->format( 'U' ) <= $ranges[$i]['end'] )
					$contains_start = $i; // this code ensures only one
				if ( $ranges[$i]['start'] <= $cend->format( 'U' ) && $cend->format( 'U' ) <= $ranges[$i]['end'] )
					$contains_end = $i; // again only one possible
				if ( $contains_start != -1 && $contains_end != -1 )
					break; // no point continuing once found
			}
			// If the dates are wholly contained within an existing range
			// that means the cache already exists and does not need to be
			// constructed so we can simply return a successfuly build.
			if ( $contains_start != -1 && $contains_start == $contains_end )
				return true;

			// Otherwise if an existing set of dates can be used update or merge
			if ( $contains_start != -1 && $contains_end != -1 ) {
				$cstart = new DateTime( '@' . $ranges[$contains_start]['start'] ); $cstart->setTimezone( $tz );
				$cend   = new DateTime( '@' . $ranges[$contains_end]['end'] ); $cend->setTimezone( $tz );
				$new = array( 'start' => $cstart->format( 'U' ), 'end' => $cend->format( 'U' ) );
				$ranges = array_splice( $ranges, $contains_start, 1, $new ); // replace first with new set
				$ranges = array_splice( $ranges, $contains_end, 1 ); // and then remove old (order matters)
			} else if ( $contains_start != -1 ) {
				$cstart = new DateTime( '@' . $ranges[$contains_start]['start'] ); $cstart->setTimezone( $tz );
				$ranges[$contains_start]['end'] = $cend->format( 'U' ); // update to new end point
			} else if ( $contains_end != -1 ) {
				$cend   = new DateTime( '@' . $ranges[$contains_end]['end'] ); $cend->setTimezone( $tz );
				$ranges[$contains_end]['start'] = $cstart->format( 'U' ); // update to new start point
			} else {
				// No covering ranges so create a new one
				$ranges[] = array( 'start' => $cstart->format( 'U' ), 'end' => $cend->format( 'U' ) );
			}
		} else {
			$ranges[] = array( 'start' => $cstart->format( 'U' ), 'end' => $cend->format( 'U' ) );
		}

		// Array of cache points to touch (i.e. create if non-existant)
		$cache_points = array();  // the start date of the point
		
		// Build the cache points
		$bresult = self::BuildCachePoints( $cache_points, $event_id, $cstart, $cend, $tz );

		// Did we successfully build the points arrays?
		if ( ! $bresult ) {
			error_log( 'WP Every Calendar +1: Could not build cache points!' );
			return false;
		}

		// If this is a XTIMES termination event then drop any after X
		if ( $termination == 'XTIMES' ) {
			if ( $terminate_at < count( $cache_points ) )
				$cache_points = array_slice( $cache_points, 0, $terminate_at );
		}

		// If there are any points to be touched do that now
		foreach( $cache_points as $touch_date ) {
			if ( ! self::TouchEventRepeat( $event_id, $touch_date ) ) {
				error_log( sprintf( 'WP Every Calendar +1: Could not touch event repeats for %s at %s', $event_id, $touch_date->format( 'Y-m-d' ) ) );
				$bresult = false;
			}
		}

		// If we haven't successfully updated the database don't change meta
		if ( ! $bresult ) return false;

		// Write the ranges array back to the database
		if ( $ranges_data == null ) {
			$wpdb->insert(
				$table_name,
				array( 'post_id' => $event_id, 'meta_key' => 'ecp1_repeat_cache_ranges', 'meta_value' => serialize( $ranges ) ),
				'%s' // all strings
			);
		} else {
			$wpdb->update(
				$table_name,
				array( 'meta_value' => serialize( $ranges ) ),
				array( 'post_id' => $event_id, 'meta_key' => 'ecp1_repeat_cache_ranges' ),
				'%s', '%s' // all strins
			);
		}
		
		// Caching operation was successful
		return $bresult;
	}

	/**
	 * BuildCachePoints (private)
	 * Constructs an array of start dates for cache points of the event
	 * in the given start and end date range. This function assumes the
	 * parameters make sense. It should only be called appropriately
	 * from BuildEventCache or similar.
	 *
	 * @param $points Reference to an array to put the start dates in.
	 * @param $event_id The ID of the event to cache the points for.
	 * @param $start The start date for the range of the cache.
	 * @param $end The end date for the range of the cache.
	 * @param $tz The timezone the dates are stored in.
	 * @return True if built successfully otherwise false.
	 */
	private static function BuildCachePoints( &$points, $event_id, $start, $end, $tz )
	{
		// This uses meta fields not in the event fields
		global $wpdb;
		$meta = $wpdb->prefix . 'postmeta';

		// Get event epoch date
		$epoch = new DateTime( '@' . _ecp1_event_meta( 'ecp1_start_ts' ) );
		$epoch->setTimezone( $tz ); // because used @timestamp

		// Does the current repeat structure cover the date range?
		$last = null; $dates = null; $repeater = null;
		try {
			$last = new DateTime( '@' . _ecp1_event_meta( 'ecp1_repeat_last_changed' ) );
			$last->setTimezone( $tz ); // because used @timestamp
		} catch( Exception $lex ) { return false; } // could build points
		if ( $last <= $start ) {

			// Can simply use the current repetition expression so build parser
			$repeater = self::GetRepeater( _ecp1_event_meta( 'ecp1_repeat_pattern' ), 
				_ecp1_event_meta( 'ecp1_repeat_pattern_parameters' ), _ecp1_event_meta( 'ecp1_repeat_custom_expression' ),
				$epoch );
			if ( $repeater == null ) {
				error_log( 'WP Every Calendar +1: Could not construct a repeater!' );
				return false;
			}

			// Use the parser to get all the points between the given dates
			$dates = $repeater->GetRepeatsBetween( $epoch, $start, $end );

		} else {

			// Otherwise (i.e. $last > $start)
			// Lookup all repetition parameters which overlap the start and end
			$history = $wpdb->get_col( $wpdb->prepare(
				"SELECT meta_value FROM $meta WHERE post_id = %s AND meta_key = %s",
				$event_id, 'ecp1_repeat_history'
			), 0 ); // get column 0 exlicitly
			$coverage = array(); // start | end | +keys for details
			foreach( $history as $repeat ) {
				$rdtl = unserialize( $repeat );
				// Check this history if valid
				if ( $rdtl['start'] > $rdtl['end'] ) continue;
				if ( !array_key_exists( 'ecp1_start_ts', $rdtl ) || is_null( $rdtl['ecp1_start_ts'] ) ) continue;
				if ( !array_key_exists( 'ecp1_repeat_pattern', $rdtl ) || is_null( $rdtl['ecp1_repeat_pattern'] ) ) continue;

				// Finally if the history covers the range use it
				if ( $rdtl['start'] <= $end->format( 'U' ) || $rdtl['end'] >= $start->format( 'U' ) )
					$coverage[$rdtl['start']] =  $rdtl;
			}
			
			// Go over the covering repetitions in order of starting
			$cstart = $cend = null; $cepoch = null ; $cdates = array();
			$ordered_keys = array_keys( $coverage );
			if ( ! sort( $ordered_keys ) ) return false; // couldn't loop
			foreach( $ordered_keys as $k ) {
				// Get the start point in the cover repeat set
				if ( $coverage[$k]['start'] <= $start->format( 'U' ) ) { // start somewhere in this coverage
					$cstart = clone $start;
				} else { // start at start of the cover
					$cstart = new DateTime( '@' . $coverage[$k]['start'] );
					$cstart->setTimezone( $tz );
				}

				// And now get the cache end point for the cover repeat set
				if ( $coverage[$k]['end'] < $end->format( 'U' ) ) { // end at the end of the cover
					$cend = new DateTime( '@' . $coverage[$k]['end'] );
					$cend->setTimezone( $tz );
				} else { // end somewhere in this coverage
					$cend = clone $end;
				}

				// Get the event epoch from the history coverage
				$cepoch = new DateTime( '@' . $coverage[$k]['ecp1_start_ts'] );
				if ( $cstart < $cepoch )
					$cstart = $cepoch;

				// Sanity checking just in case the database has odd values
				if ( $cend < $cstart )
					continue; // don't error there just are no points for that range

				// Now we have the cover start and end dates build a repeater
				$repeater = self::GetRepeater( $coverage[$k]['ecp1_repeat_pattern'],
					$coverage[$k]['ecp1_repeat_pattern_parameters'], $coverage[$k]['ecp1_repeat_custom_expression'],
					$cepoch ); // make sure we use the history epoch
				if ( $repeater == null ) {
					error_log( 'WP Every Calendar +1: Could not build a repeater!' );
					return false;
				}

				// Use the parser to get all the points for this cover set of dates
				$cdates[] = $repeater->GetRepeatsBetween( $cepoch, $cstart, $cend );
			}

			// Combine the cover dates into one array or null if failed
			if ( is_array( $cdates ) ) {
				$dates = array();
				foreach( $cdates as $cdate ) {
					if ( is_array( $cdate ) ) {
						foreach( $cdate as $date )
							$dates[] = $date;
					}
				}
			} else {
				error_log( 'WP Every Calendar +1: Non-array combined dates found!' );
				$dates = null;
			}
		}

		// If a set of dates was built then output them as the points
		// NOTE: This requires post processing for termination cases
		// and is not done here because we can't be sure of intent.
		if ( is_array( $dates ) ) {
			foreach( $dates as $date ) 
				$points[] = $date;
			return true; // successfully built
		} else {
			error_log( 'WP Every Calendar +1: Non-array date set found as cache points!' );
			return false; // failed
		}
	}

	/**
	 * GetRepeater (private)
	 * Returns an instance of EveryCal_RepeatExpression for the given parameters.
	 *
	 * @param $type The type of expression if using a known type (otherwise null)
	 * @param $params The parameters for the known type expression (or null)
	 * @param $custom A custom expression for the parser
	 * @param $epoch The epoch of the event the repeater is for
	 * @return An instance of EveryCal_RepeatExpression matching parameters
	 */
	private static function GetRepeater( $type, $params, $custom, $epoch )
	{
		try {
			if ( array_key_exists( $type, EveryCal_RepeatExpression::$TYPES ) ) {
				return EveryCal_RepeatExpression::Build( $type, $epoch, $params );
			} else {
				return new EveryCal_RepeatExpression( $custom );
			}
		} catch( Exception $rex ) {
			return null;
		}
	}
	
	/**
	 * TouchEventRepeat Function (private)
	 * Stores a calculated entry in the event repeat cache.
	 *
	 * @param $event_id The event to store this entry for
	 * @param $repeat_start The start DateTime object for the repeot
	 * @return True of False success of the save operation
	 */
	private static function TouchEventRepeat( $event_id, $repeat_start )
	{
		global $wpdb; $cache_table = $wpdb->prefix . 'ecp1_cache';
		// Check if it exists already?
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $cache_table WHERE post_id = %s AND start = %s",
			$event_id, $repeat_start->format( 'Y-m-d' )
		) );
		// Failure to count existing is an error
		if ( $count == null)
			return false;
		// If there are none then insert this one
		if ( $count == 0 ) { 
			$ok = $wpdb->insert(
				$cache_table,
				array( 'post_id' => $event_id, 'is_exception' => 0, 'start' => $repeat_start->format( 'Y-m-d' ) ),
				array( '%d', '%d', '%s' )
			);
			if ( ! $ok )
				return false; // couldn't insert
		}

		// Success by process of elimination
		return true;
	}
	
	/**
	 * GetEvents Function
	 * Looks up the events in the given calendar that run in the time range specified.
	 * The input times should be timestamp (int) values representing a time at UTC.
	 *
	 * The result array contains all the events for this calendar, that includes one
	 * time events, repeating events, featured events and events that are specifically
	 * set to appear on this calendar.
	 *
	 * The array will be ordered by event start date.
	 *
	 * Each entry in the array will be an event fields array (data/event-fields.php)
	 * any exceptions that apply to the event will be in the resultant array.
	 *
	 * You should still call get_posts / the_post for each result.
	 *
	 * @param $cal_id The Calendar to build the cache for
	 * @param $start The start timestamp (int) to build forward from
	 * @param $end The end timestamp (int) to build forward until
	 * @return Array of event arrays with repeats expanded
	 */
	public static function GetEvents( $cal_id, $start, $end )
	{
		// Setup the environment
		global $wpdb; $cache_table = $wpdb->prefix . 'ecp1_cache';
		$fstart = new DateTime( "@$start" );
		$fend   = new DateTime( "@$end" );

		// Load the calendar meta details
		_ecp1_parse_calendar_custom( $cal_id );
		$caltz = new DateTimeZone( ecp1_get_calendar_timezone() );
		$fstart->setTimezone( $caltz );
		$fend->setTimezone( $caltz );

		// Start by fetching all events that are on the calendar in the time range, or
		// specifically on this calendar as an extra in the time range, or is a repeating
		// event on the calendar: two columns post id | repeats
		$myevents = $wpdb->get_results( $wpdb->prepare(
			EveryCal_Query::Q( 'CALENDAR_EVENTS' ),
			$cal_id, $fstart->format( 'U' ), $fend->format( 'U' )
		), ARRAY_N ); // number keyed array of results

		// Is this a feature event calendar: if so get the features (time range as above)
		$features = array();
		if ( _ecp1_calendar_show_featured( $cal_id ) ) {
			$features = $wpdb->get_results( $wpdb->prepare( 
				EveryCal_Query::Q( 'FEATURED_EVENTS' ),
				$fstart->format( 'U' ), $fend->format( 'U' )
			), ARRAY_N ); // number keyed array of results
		}

		// Combine the arrays so we can loop over one of them
		$eventset = array_merge( $myevents, $features );
		$event_fields = array( 'ecp1_summary', 'ecp1_description', 'ecp1_url', 'ecp1_start_ts',
			'ecp1_end_ts', 'ecp1_full_day', 'ecp1_calendar', 'ecp1_location', 'ecp1_coord_lat',
			'ecp1_coord_lng', 'ecp1_map_zoom', 'ecp1_map_placemarker', 'ecp1_showmarker',
			'ecp1_showmap', 'ecp1_featured', 'ecp1_overwrite_color', 'ecp1_local_textcolor',
			'ecp1_local_color', 'ecp1_repeating' );

		// Update the calendar cache for this range of dates
		$cresult = self::BuildCache( $cal_id, $start, $end );
		if ( ! $cresult )  // and if failed error out
			throw new Exception( __( 'Failed to build repeating events cache' ) );

		// Loop over the set of events and translate into event meta
		$eventmeta = array();
		foreach( $eventset as $lookup ) {
			// Just to be ultra careful we only want real results
			if ( ! is_numeric( $lookup[0] ) )
				continue;
			// Create an array of all the fields for the event
			// The DateTime exception class requires the calendar timezone in _meta
			$this_event = array( 'post_id' => $lookup[0], '_meta' => array( 'calendar_tz' => ecp1_get_calendar_timezone() ) );
			_ecp1_parse_event_custom( $lookup[0] );
			foreach( $event_fields as $field )
				$this_event[$field] = _ecp1_event_meta( $field );

			// If this is an extra or feature event then the renders expect the events times to 
			// be in the timezone for the calendar the event is published on; they may change
			// this to calendar local time by using offsets so they also need to know the real
			// timezone of the event.
			$tetz = $caltz;
			if ( $this_event['ecp1_calendar'] != $cal_id ) {
				$this_event['_meta']['calendar_tz'] = _ecp1_event_calendar_timezone();
				$tetz = new DateTimeZone( $this_event['_meta']['calendar_tz'] );
			}
			
			// If not a repeating event then just push the details
			if ( 'Y' != $lookup[1] ) {
				
				// This is not a repeating event so we don't need these
				$this_event['ecp1_next_repeat_ts'] = null; // can be used for permalinks
				$this_event['ecp1_prev_repeat_ts'] = null;

				// Add to the event meta results
				$eventmeta[] = $this_event;

			} else { // repeating event i.e. Y == $lookup[1]
				
				// Get all instances of event in the range (assumes cache is good)
				// by running a custom query on the current event id and range (date only)
				$exceptions = EveryCal_Exception::Find( $lookup[0], $fstart, $fend );
				$occurances = $wpdb->get_results( $wpdb->prepare(
					"SELECT start, changes, is_exception FROM $cache_table WHERE post_id = %s AND start <= %s",
					$lookup[0], $fend->format( 'Y-m-d' )
				), OBJECT ); // numeric array of objects
				if ( $occurances == null || count( $occurances ) == 0)
					continue; // nothing for this event
				foreach( $occurances as $repeat ) {
					// Copy the event defaults
					$this_occurance = $this_event;
					$occurance_cancelled = false;

					// Change the start DATE (ONLY THE DATE) to $repeat->start
					$oldstart = $this_occurance['ecp1_start_ts'];
					$esdate = new DateTime( $repeat->start, $tetz );
					$osdate = new DateTime( '@' . $this_occurance['ecp1_start_ts'] ); $osdate->setTimezone( $tetz );
					$osdate->setDate( $esdate->format( 'Y' ), $esdate->format( 'n' ), $esdate->format( 'j' ) );
					$this_occurance['ecp1_start_ts'] = $osdate->format( 'U' );
					// Update the end date by the same amount
					if ( $oldstart != $this_occurance['ecp1_start_ts'] ) {
						$oedate = new DateTime( '@' . $this_occurance['ecp1_end_ts'] ); $oedate->setTimezone( $tetz );
						$daysdiff = floor( ( $this_occurance['ecp1_start_ts'] - $oldstart ) / 86400 );
						$oedate->modify( "$daysdiff day" );
						$this_occurance['ecp1_end_ts'] = $oedate->format( 'U' );
					}
					
					// Update the event details for the repeat
					if ( $repeat->is_exception ) {
						// Find the exception that needs to be applied
						$ex = null;
						foreach( $exceptions as $exception ) {
							if ( $exception['start'] == $repeat->start ) { // both raw db values
								$ex = $exception;
								break;
							}
						}
						// If we found the exception then apply the changes
						if ( $ex != null ) {
							// If the repeat has been cancelled then skip it
							if ( $ex['is_cancelled'] )
								$occurance_cancelled = true;
							// Apply any changes to the occurance
							foreach( $ex['changes'] as $key=>$value )
								EveryCal_Exception::Update( $key, $this_occurance, $value );
						}
					}

					// For the purposes of permalinks we need the cached start date
					$this_occurance['_meta']['cache_start'] = $esdate->format( 'Y-n-j' );

					// Finally exclude the repeat if it is wholly outside the range of the request
					if ( $this_occurance['ecp1_start_ts'] > $fend->format( 'U' ) || 
							$this_occurance['ecp1_end_ts'] < $fstart->format( 'U' ) )
						$occurance_cancelled = true;

					// Add the occurances fields to the final output array
					if ( ! $occurance_cancelled ) 
						$eventmeta[] = $this_occurance;
				}
			}
		}

		// Sort the results array by the start timestamp
		uasort( $eventmeta, 'ecsge_sort' );
		return $eventmeta;
	}
	
	/**
	 * EventCacheUpdate
	 * Called whenever an events details are changed so that the future cache
	 * can be adjusted appropriately. This is a rather complicated process
	 * especially if changes are made to the repeating expression.
	 *
	 * This function does not return a value - all errors throw exceptions.
	 *
	 * If multiple changes are made they are processed in this order.
	 *
	 * To do this we use the following algorithm:
	 *
	 * NOTES
	 *  - When building XTIMES events only keep the first X.
	 *  - The repeat PATTERN may change build repeats to change boundaries
	 *
	 *  1) START CHANGE: The start date of the event has been updated;
	 *     remove all future (from today) cached repeats UNLESS it is
	 *     an exception (which will contain user data so leave for them
	 *     to update the start date of the exception).
	 *
	 *  2) TERMINATE AT CHANGE: When the parameters for the termination
	 *     point are changed the action is dependent on the termination:
	 *     a) 4EVA - Repeating forever has no parameters so do nothing
	 *     b) XTIMES - Repeats X times; if X has been reduced remove 
	 *        cached items after the Xth item; else do nothing (the
	 *        cache will be built next time the event is requested)
	 *     c) UNTIL - Repeat until; if increased do nothing and the
	 *        cache will be extended as required next request; but if
	 *        decreased remove any cached repeats after the new date.
	 *
	 *  3) TERMINATION CHANGE: When the type of termination is changed
	 *     the sequence matters; the following combinations are coded:
	 *     a) 4EVA -> XTIMES or UNTIL -> XTIMES
	 *        Build XTIMES cache year by year until reaching X then 
	 *        remove any in database after the Xth cached repeat; start
	 *        dates won't change but we can't garuantee the whole cache
	 *        exists which is why it must be built first.
	 *     b) 4EVA -> UNTIL or XTIMES -> UNTIL
	 *        Remove any cached repeats after the new until point.
	 *     c) UNTIL -> 4EVA or XTIMES -> 4EVA
	 *        Do nothing as event can now repeat indefinitely.
	 *
	 *  4) REPEAT PATTERN CHANGED: If the pattern of repeats has changed
	 *     all FUTURE cached events must be considered dirty. But all 
	 *     PREVIOUS cached events or repeats that occur before the change
	 *     should still use the OLD pattern. This means two things:
	 *     a) Store the old pattern as ending at change date
	 *     b) Remove all FUTURE cached events that are NOT exceptions
	 *
	 * The meta values that are about to be saved are passed as a reference
	 * so this function can update them; this is useful for adding extra
	 * meta values to track changes such as ecp1_repeat_last_changed.
	 *
	 * @param $event_id The event that we're build a cache for
	 * @param $meta_group The grouped meta values for the event (ready for save)
	 * @param $meta_alone The standalone meta values for the event (ready for save)
	 * @param $tz The calendar timezone (so it doesn't have to be read)
	 */
	public static function EventCacheUpdate( $event_id, &$meta_group, &$meta_alone, $tz )
	{
		// We need to read the existing meta values from the database
		// because the event is only being saved there is no global
		// array of meta values defined.
		global $wpdb, $ecp1_event_fields;
		$table_name = $wpdb->prefix . 'postmeta';
		$current = unserialize( $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_value FROM $table_name WHERE post_id = %s AND meta_key = %s",
			$event_id, 'ecp1_event'
		) ) );
		if ( $current == null )
			$current = array(); // for new posts

		// List of keys that we need to track the changes in
		$track_changes = array(
			'ecp1_repeat_pattern',
			'ecp1_repeat_custom_expression',
			'ecp1_repeat_pattern_parameters',
			'ecp1_repeat_termination',
			'ecp1_repeat_terminate_at',
			'ecp1_start_ts',
		);
		
		// Get the event epoch
		$epoch = new DateTime( '@' . $meta_alone['ecp1_event_start'] );
		$epoch->setTimezone( $tz );

		// When did the last change occur
		$last_change = array_key_exists( 'ecp1_repeat_last_changed', $current ) ? $current['ecp1_repeat_last_changed'] : null;
		if ( $last_change == null ) {
			$last_change = clone $epoch; // where there is no history (e.g. version update)
		} else {
			$last_change = new DateTime( "@$last_change" );
			$last_change->setTimezone( $tz ); // this is not such a big deal
		}

		// If previously was non-repeating and is now then we don't need to do anything
		if ( ! array_key_exists( 'ecp1_event_repeats', $meta_alone ) || 'Y' != $meta_alone['ecp1_event_repeats'] ) {
			$prepeat = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_value FROM $table_name WHERE post_id = %s AND meta_key = %s",
				$event_id, 'ecp1_event_repeats'
			) );
			// Check if previously repeating
			if ( 'Y' != $prepeat ) {
				$meta_group['ecp1_repeat_last_changed'] = $last_change->format( 'U' );
				return; // wasn't repeating so don't worry
			}
		}

		// For simplicity create a 2D array of change keys new and old values
		$event_changes = array();
		foreach( $track_changes as $change ) {
			$cvalue = $nvalue = null;
			if ( array_key_exists( $change, $ecp1_event_fields['_meta']['standalone'] ) ) {
				$real_key = $ecp1_event_fields['_meta']['standalone'][$change];
				// Get the current database value
				$cvalue = $wpdb->get_var( $wpdb->prepare(
					"SELECT meta_value FROM $table_name WHERE post_id = %s AND meta_key = %s",
					$event_id, $real_key
				) );
				// And now the new value from parameters
				$nvalue = array_key_exists( $real_key, $meta_alone ) ? $meta_alone[$real_key] : null;
			} else {
				// Not a standalone value so get from group
				$cvalue = is_array( $current ) && array_key_exists( $change, $current ) ? $current[$change] : null;
				$nvalue = array_key_exists( $change, $meta_group ) ? $meta_group[$change] : null;
			}
			$event_changes[$change] = array( 'old' => $cvalue, 'new' => $nvalue );
		}

		// Get todays date in the calendar timezone
		$today = new DateTime( null, $tz );
		$cache_table = $wpdb->prefix . 'ecp1_cache';
		$cache_is_dirty = false;

		// Has anything changed (if so continue otherwise do nothing)
		$nochanges = true;
		foreach( $event_changes as $k=>$values ) {
			if ( $values['old'] != $values['new'] )
				$nochanges = false;
		}

		// If no changes have occured maintain the existing last change and finish
		if ( $nochanges ) {
			$meta_group['ecp1_repeat_last_changed'] = $last_change->format( 'U' );
			return;
		} // else process changes and set change date to today at the end

		// ALGORITHM PART 1 - START DATE CHANGED
		// Summary: delete all future cached non-exception events
		if ( $event_changes['ecp1_start_ts']['old'] != $event_changes['ecp1_start_ts']['new'] ) {
			$num = $wpdb->query( $wpdb->prepare(
				"DELETE FROM $cache_table WHERE is_exception = 0 AND post_id = %s AND start >= %s",
				$event_id, $today->format( 'Y-m-d' )
			) );
			if ( $num > 0 )
				$cache_is_dirty = true;
		}

		// ALGORITHM PART 2 - Termination parameters changed
		// Summary: Remove any past the new point if a reduction
		if ( $event_changes['ecp1_repeat_termination']['old'] == $event_changes['ecp1_repeat_termination']['new'] ) {
			$termination = $event_changes['ecp1_repeat_termination']['new'];
			if ( $termination == 'XTIMES' ) {
				// Summary: larger do nothing, smaller remove over limit
				if ( $event_changes['ecp1_repeat_terminate_at']['old'] > $event_changes['ecp1_repeat_terminate_at']['new'] ) {
					// Here we can exploit the fact that all X repeats will be built by the cache on request
					// To make this binary log compatible we'll explicitly list the cache objects to delete
					$cache_ids = $wpdb->get_col( $wpdb->prepare(
						"SELECT cache_id FROM $cache_table WHERE post_id = %s ORDER BY start ASC",
						$event_id
					) );
					if ( count( $cache_ids ) > $event_changes['ecp1_repeat_terminate_at']['new'] ) {
						$delete_ids = array();
						for ( $i=$event_changes['ecp1_repeat_terminate_at']['new']; $i<count( $cache_ids ); $i++ )
							$delete_ids[] = $cache_ids[$i];
						if ( count( $delete_ids ) > 0 ) {
							$num = $wpdb->query( $wpdb->prepare(
								"DELETE FROM $cache_table WHERE is_exception = 0 AND post_id = %s AND cache_id IN (" . implode( ',', $delete_ids ) . ")",
								$event_id
							) );
							if ( $num > 0 )
								$cache_is_dirty = true;
						}
					}
				}
			} else if ( $termination == 'UNTIL' ) {
				// Summary: remove any after the new until point
				if ( $event_changes['ecp1_repeat_terminate_at']['old'] > $event_changes['ecp1_repeat_terminate_at']['new'] ) {
					// Simple delete if start greater than the new date
					$until = new DateTime( '@' . $event_changes['ecp1_repeat_terminate_at']['new'] ); $until->setTimezone( $tz );
					$num = $wpdb->query( $wpdb->prepare(
						"DELETE FROM $cache_table WHERE is_exception = 0 AND post_id = %s AND start > %s",
						$event_id, $until->format( 'Y-m-d' )
					) );
					if ( $num > 0 )
						$cache_is_dirty = true;
				}
			}
			// Do nothing 4EVA there are no parameters
		}

		// ALGORITHM PART 3 - Termination type changed
		// Summary: Remove any after the new termination point
		if ( $event_changes['ecp1_repeat_termination']['old'] != $event_changes['ecp1_repeat_termination']['new'] ) {
			$termination = $event_changes['ecp1_repeat_termination']['new']; // all about what it has become
			if ( $termination == 'XTIMES' ) {
				// Summary: build cache for all X remove any that are not in the cache
				$known_repeats = array();
				$target_x = $event_changes['ecp1_repeat_terminate_at']['new'];
				$repeater = self::GetRepeater( $meta_group['ecp1_repeat_pattern'], $meta_group['ecp1_repeat_pattern_parameters'],
					$meta_group['ecp1_repeat_custom_expression'], $epoch );
				if ( $repeater != null ) {
					$cstart = clone $epoch;
					$cend   = clone $epoch; $cend->modify( '+1 year' );
					while ( count( $known_repeats ) < $target_x ) {
						$newdates = $repeater->GetRepeatsBetween( $epoch, $cstart, $cend );
						foreach( $newdates as $nd )
							$known_repeats[] = $nd;
						$cstart->modify( '+1 year' );
						$cend->modify( '+1 year' );
					}
				}
				// Loop over the first X known repeats vs database records
				$known_repeats = array_slice( $known_repeats, 0, $target_x );
				$in_database = $wpdb->get_results( $wpdb->query(
					"SELECT cache_id, post_id, start FROM $cache_table WHERE post_id = %s",
					$event_id
				) );
				$delete_ids = array();
				foreach( $in_database as $cur ) {
					$safe = false;
					foreach( $known_repeats as $keep ) {
						if ( $cur->start == $keep->format( 'Y-m-d' ) )
							$safe = true;
					}
					if ( ! $safe )
						$delete_ids[] = $cur->cache_id;
				}

				if ( count( $delete_ids ) > 0 ) {
					$num = $wpdb->query( $wpdb->prepare(
						"DELETE FROM $cache_table WHERE is_exception = 0 AND post_id = %s AND cache_id IN (" . implode( ',', $delete_ids ) . ")",
						$event_id
					) );
					if ( $num > 0 )
						$cache_is_dirty = true;
				}
			} else if ( $termination == 'UNTIL' ) {
				// Summary: remove any cached after the new point
				$until = new DateTime( '@' . $event_changes['ecp1_repeat_terminate_at']['new'] ); $until->setTimezone( $tz );
				$num = $wpdb->query( $wpdb->prepare(
					"DELETE FROM $cache_table WHERE is_exception = 0 AND post_id = %s AND start > %s",
					$event_id, $until->format( 'Y-m-d' )
				) );
				if ( $num > 0 )
					$cache_is_dirty = true;
			}
			// Change to 4EVA doesn't require any action
		}

		// ALGORITHM PART 4 - Pattern Changed
		// Summary: future event caching is removed
		if ( ( $event_changes['ecp1_repeat_pattern']['old'] != $event_changes['ecp1_repeat_pattern']['new'] ) ||
			( $event_changes['ecp1_repeat_pattern_parameters']['old'] != $event_changes['ecp1_repeat_pattern_parameters']['new'] ) ||
			( $event_changes['ecp1_repeat_custom_expression']['old'] != $event_changes['ecp1_repeat_custom_expression']['new'] ) ) {
			// Change doesn't matter just clearing future cache
			$num = $wpdb->query( $wpdb->prepare(
				"DELETE FROM $cache_table WHERE is_exception = 0 AND post_id = %s AND start >= %s",
				$event_id, $today->format( 'Y-m-d' )
			) );
			if ( $num > 0 )
				$cache_is_dirty = true;
		}

		// If the cache is dirty we need to do a rebuild over all the ranges in the database
		if ( $cache_is_dirty ) {
			// Remove all record of ranges being cached where the range is in the future
			$ranges_data = $wpdb->get_var( $wpdb->prepare(
				"SELECT meta_value FROM $table_name WHERE post_id = %s AND meta_key = %s",
				$event_id, 'ecp1_repeat_cache_ranges'
			) );
			// Convert the data to an array
			$ranges = $ranges_data != null ? unserialize( $ranges_data ) : array();
			$keep_ranges = array();
			$is_there_a_difference = false;
			foreach( $ranges as $range ) {
				if ( $range['start'] < $today->format( 'U' ) && $range['end'] < $today->format( 'U' ) ) {
					$keep_ranges[] = $range;
				} else {
					$is_there_a_difference = true;
				}
			}

			// If there is any changes write the new ranges to the database
			if ( $is_there_a_difference ) {
				$wpdb->update(
					$table_name,
					array( 'meta_value' => serialize( $keep_ranges ) ),
					array( 'post_id' => $event_id, 'meta_key' => 'ecp1_repeat_cache_ranges' ),
					'%s', '%s'
				);
			}
		}

		// Save the historical repeat parameters for history lookups
		$history = array();
		foreach( $track_changes as $hkey )
			$history[$hkey] = $event_changes[$hkey]['old'];
		// Also keep a record of when this set of parameters applies
		//$yesterday = clone $today; $yesterday->modify( '-1 day' );
		$history['start'] = $last_change->format( 'U' );
		$history['end'] = $today->format( 'U' ) - 10; //$yesterday->format( 'U' );
		self::PushEventRepeatHistory( $event_id, $history );

		// We've made changes so update the last change date
		$meta_group['ecp1_repeat_last_changed'] = $today->format( 'U' );
	}

	/**
	 * PushEventRepeatHistory (private)
	 * Adds or updates the event history meta field with given history.
	 *
	 * History is stored under the meta_key = ecp1_repeat_history
	 *
	 * History is a daily change so make sure there are no duplicates.
	 *
	 * @param $event_id The event to store the history for
	 * @param $history Array of history to store
	 */
	private static function PushEventRepeatHistory( $event_id, $history )
	{
		global $wpdb;
		$meta = $wpdb->prefix . 'postmeta';
		$history_db = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $meta WHERE post_id = %s AND meta_key = %s",
			$event_id, 'ecp1_repeat_history'
		) );
		// Updating or inserting?
		$update_id = null;
		foreach( $history_db as $h ) {
			$thdtl = unserialize( $h->meta_value );
			if ( ! is_array( $thdtl ) )
				continue;
			$dbs = new DateTime( '@' . $thdtl['start'] ); $dbe = new DateTime( '@' . $thdtl['end'] );
			$ns = new DateTime( '@' . $history['start'] ); $ne = new DateTime( '@' . $history['end'] );
			if ( $dbs->format( 'Y-m-d' ) == $ns->format( 'Y-m-d') && $dbe->format( 'Y-m-d' ) == $ne->format( 'Y-m-d' ) ) {
				$update_id = $h->meta_id;
				break;
			}
		}
		// If have an update id then update otherwise create new entry
		if ( $update_id != null ) {
			$wpdb->update(
				$meta,
				array( 'meta_value' => serialize( $history ) ),
				array( 'meta_id' => $update_id, 'post_id' => $event_id ),
				'%s', '%s'
			);
		} else {
			$wpdb->insert(
				$meta,
				array( 'post_id' => $event_id, 'meta_key' => 'ecp1_repeat_history', 'meta_value' => serialize( $history ) ),
				'%s'
			);
		}
	}

}

// Don't close the php interpreter
/*?>*/
