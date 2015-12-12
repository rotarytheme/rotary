<?php
/**
 * An associative array of map providers and the relevant JS to include to provide a map UI
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Define the calendar providers
// Array key must be a-z only to match in rewrite rules
$_ecp1_calendars = array(
	'google' => array( 'name' => __( 'Google Calendar'),
				'fullcal_plugin' => 'gcal.js', 'fullcal_datatype' => 'gcal',
				'ecp1_ical_plugin' => 'google.php', 'ecp1_ical_class'=>'ECP1GoogleCalendar' ),
	'civicrm' => array( 'name' => __( 'CiviCRM Proxy no URL required' ),
				'ecp1_ical_plugin' => 'civicrm.php', 'ecp1_ical_class'=>'ECP1CiviCRMProxy' ),
);

// Function that shamelessly returns the above array so we don't have to global it
// Eventually the plan is to create a more dynamic array using this function
function ecp1_calendar_providers() {
	global $_ecp1_calendars;
	return $_ecp1_calendars;
}

// Function that returns an instance of the nominated calendar provider or
// null if the instance can not be created. This function does not check 
// that external calendars can be used it assumes they can be.
//
// The other parameters are Calendar Post ID and meta external URL.
function ecp1_get_calendar_provider_instance( $provkey, $cal_id, $url ) {
	$providers = ecp1_calendar_providers();
	if ( ! array_key_exists( $provkey, $providers ) )
		return null; // no such provider

	$provider = $providers[$provkey];
	if ( ! array_key_exists( 'ecp1_ical_plugin', $provider ) || ! array_key_exists( 'ecp1_ical_class', $provider ) )
		return null; // no plugin parameters
	if ( ! file_exists( ECP1_DIR . '/includes/calendars/' . $provider['ecp1_ical_plugin'] ) )
		return null; // no plugin file

	// Load the PHP class script and return an instance
	require_once( ECP1_DIR . '/includes/calendars/' . $provider['ecp1_ical_plugin'] );
	return new $provider['ecp1_ical_class']( $cal_id, $url );
}

// Don't close the php interpreter
/*?>*/
