<?php
/**
 * Defines the plugin settings defaults and helper functions
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Define the database option name the plugin uses
define( 'ECP1_OPTIONS_GROUP', 'ecp1_options' );
define( 'ECP1_GLOBAL_OPTIONS', 'ecp1_global' );

// Define some templates
define( 'ECP1_DEFAULT_CALENDAR_TEMPLATE', file_get_contents( ECP1_DIR . '/includes/templates/calendar.html.tpl' ) );
define( 'ECP1_DEFAULT_EVENT_TEMPLATE', file_get_contents( ECP1_DIR . '/includes/templates/event.html.tpl' ) );

// The defaults array of settings
$_ecp1_settings = array(

	// Meta setting that tells if the db options have been loaded
	'_db' => false,

	// Should a map be available for the event location: default true
	'use_maps' => array( 'default' => 1 ),

	// Should FullCalendar come from CDNJS or local files
	'cdnjs' => array( 'default' => 1 ),
	
	// Which Map Provider should be used: default none
	// Should be set to a key out of the Map Providers array
	'map_provider' => array( 'default' => 'none' ),

	// Which Map Provider should be used for geocoding: default none
	// Should be set to a key out of the Map Providers array
	'map_geocoder' => array( 'default' => 'none' ),
	
	// Allow calendars to change the timezone they're for from WordPress: default true
	'tz_change' => array( 'default' => 1 ),
	
	// 3rd party / external calendar plugins for Full Calendar
	'use_external_cals' => array( 'default' => 1 ),
	
	// Which External Providers should be used (comma separated list)
	// List should come from the External Calendar Providers array
	'_external_cal_providers' => array( 'default' => 'google' ),

	// Which calendar post IDs should show featured events (comma separated)
	'_show_featured_on' => array( 'default' => '' ),

	// Should feature events on other calendars be based in the calendar timezone
	// or in the the event local timezone? By default we say the event local one
	// e.g. Event starts are 10am Australia/Melbourne and is displayed on a 
	//      calendar in timezone Europe/London (ignoring DST) if this is
	//      event local: will show start as 10am on calendar (with note)
	//	calendar local: will show start as midnight on calendar
	'base_featured_local_to_event' => array( 'default' => 1 ),
	'base_featured_local_note' => array( 
		'default' => __( 'Featured events occur at location local time.' ) ),

	// The number of seconds in the past to look back when exporting
	// feeds (iCal/RSS) of calendars; and the corresponding number to
	// look forward by when exporting feeds.
	'export_start_offset' => array( 'default' => '86400' ), // one day
	'export_end_offset' => array( 'default' => '15811200' ), // 6 months

	// The number of seconds into the future to publish events in the RSS feed
	'rss_pubdate_prequel_range' => array( 'default' => '2592000' ), // 30 days

	// The next two relate to if external calendars should be cached
	// locally, and syndicated in the calendar feeds, or not. And if
	// so how long the local cache should be considered valid for.
	'export_include_external' => array( 'default' => 1 ), // yes
	'export_external_cache_life' => array( 'default' => '604800' ), // one week 

	// Settings related to template and layout
	'export_icon' => array( 'default' => 'date.png' ), // FAMFAMFAM: date.png
	'show_export_icon' => array( 'default' => 1 ), // yes
	'calendar_template' => array( 'default' => ECP1_DEFAULT_CALENDAR_TEMPLATE ),
	'event_template' => array( 'default' => ECP1_DEFAULT_EVENT_TEMPLATE ),
	'show_time_on_all_day' => array( 'default' => 0 ), // don't show time on all day events
	'show_all_day_message' => array( 'default' => 0 ), // don't show the all day message
	'popup_on_click' => array( 'default' => 1 ), // show a popup or go to page on click 
	'week_time_format' => array( 'default' => 'h:mmtt( - h:mmtt )' ), // Time format for agenda
	'month_time_format' => array( 'default' => 'h(:mm)tt' ), // Time format for month/other

	// Repeating cache size and built-in expressions
	'max_repeat_cache_block' => array( 'default' => '15811200' ), // 6 months
	'enforce_repeat_cache_size' => array( 'default' => 0 ), // don't enforce by default
	'allow_custom_repeats' => array( 'default' => 0 ), // can't write own cron expression
	'_disable_builtin_repeats' => array( 'default' => '' ), // keys from EveryCal_Expression::TYPES

	// Finally create synonyms effectively this is so we can create a
	// setting that contains the value of another setting to maintain
	// setting names going forward.
	//	$key => $value
	// $key is the old name of the setting
	// $value is the new name of the setting
	'_synonyms' => array(
		'ical_export_start_offset' => 'export_start_offset',
		'ical_export_end_offset' => 'export_end_offset',
		'ical_export_include_external' => 'export_include_external',
		'ical_export_external_cache_life' => 'export_external_cache_life',
	),
);

// Helper function that returns the real key name for an option
// will be the given name or out of _synonyms if exists in it
function _ecp1_real_option_key( $option_key ) {
	global $_ecp1_settings;

	// If the requested key is a synonym then replace with real key
	if ( ! is_null( $option_key ) && array_key_exists( $option_key, $_ecp1_settings['_synonyms'] ) )
		$option_key = $_ecp1_settings['_synonyms'][$option_key];

	return $option_key;
}


// Helper function that returns the whole options array or just the 
// value if a key specified - where the key is not in the database
// the default value is returned.
function _ecp1_get_option( $option_key, $reload_from_db=false ) { return _ecp1_get_options( $option_key, $reload_from_db ); }
function _ecp1_get_options( $option_key=null, $reload_from_db=false ) {
	global $_ecp1_settings;
	
	// Read the database settings if they haven't been or are needed again
	if ( ! $_ecp1_settings['_db'] || $reload_from_db ) {
		$dbopts = get_option( ECP1_GLOBAL_OPTIONS );

		// Loop over the database keys and rename any where the synonym is still used
		if ( is_array( $dbopts ) ) {
			foreach( $dbopts as $key=>$value ) {
				if ( _ecp1_real_option_key( $key ) !== $key ) {
					$dbopts[_ecp1_real_option_key( $key )] = $dbopts[$key];
					unset( $dbopts[$key] );
				}
			}
		}
		
		// Loop over the default settings and load values where appropriate
		foreach( $_ecp1_settings as $key=>$defaults ) {
			if ( isset( $dbopts[$key] ) )
				$_ecp1_settings[$key]['value'] = $dbopts[$key];
		}

		// Mark as having been read from DB
		$_ecp1_settings['_db'] = true;
	}

	// Handle synonyms
	$option_key = _ecp1_real_option_key( $option_key );
	
	// Do they just want the value of the keyed option?
	// This is done here for a minor efficiency boost
	if ( ! is_null( $option_key ) ) {
		if ( ! array_key_exists( $option_key, $_ecp1_settings ) )
			return null;
		return isset( $_ecp1_settings[$option_key]['value'] ) ? $_ecp1_settings[$option_key]['value'] : $_ecp1_settings[$option_key]['default'] ;
	}
		
	// Build an array of the actual values
	$real_settings = array();
	foreach( $_ecp1_settings as $key=>$values ) {
		if ( $key == '_synonyms' )
			continue; // skip it
		if ( is_array( $values ) )
			$real_settings[$key] = isset( $values['value'] ) ? $values['value'] : $values['default'];
	}
	
	// Finally return the whole array
	return $real_settings;
}

// Tests if the option is at it's default value
function _ecp1_option_is_default( $key ) {
	global $_ecp1_settings;
	$key = _ecp1_real_option_key( $key );
	if ( ! array_key_exists( $key, $_ecp1_settings ) )
		return false;	// Unknown key
	if ( ! isset( $_ecp1_settings[$key]['value'] ) )
		return true;	// No value MUST BE default
	return $_ecp1_settings[$key]['default'] == $_ecp1_settings[$key]['value'];
}

// Returns the default value for the given option
function _ecp1_option_get_default( $key ) {
	global $_ecp1_settings;
	$key = _ecp1_real_option_key( $key );
	if ( ! array_key_exists( $key, $_ecp1_settings ) )
		return null;
	return $_ecp1_settings[$key]['default'];
}

// Returns true or false if the given external calendar provider is enabled
function _ecp1_calendar_provider_enabled( $provider ) {
	$option = _ecp1_get_option( 'use_external_cals' );
	if ( $option ) {
		$option = _ecp1_get_option( '_external_cal_providers' );
		$providers = explode( ',', $option );
		return in_array( $provider, $providers );
	}
	return false;
}

// Returns true or false if the given calendar post id should display featured events
function _ecp1_calendar_show_featured( $post_id ) {
	$cals = explode( ',', _ecp1_get_option( '_show_featured_on' ) );
	return is_array( $cals ) && in_array( $post_id, $cals );
}

// Returns true or false if the given expression key is disabled
function _ecp1_scheduler_expression_is_disabled( $expr_key ) {
	$set = explode( ',', _ecp1_get_option( '_disable_builtin_repeats' ) );
	return is_array( $set ) && in_array( $expr_key, $set );
}

// Don't close the php interpreter
/*?>*/
