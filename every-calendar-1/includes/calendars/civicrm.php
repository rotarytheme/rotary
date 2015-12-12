<?php
/**
 * Every Calendar +1 WordPress Plugin
 *
 * CiviCRM Events Backend Proxy. This class enables proxying of the
 * CiviCRM Events through EveryCal+1. For the FullCalendar interface
 * events are proxied as a JSON separate feed.
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Load the abstract class that this implements
require_once( ECP1_DIR . '/includes/calendars/calendar-interface.php' );

// Helper for loading the CiviCRM Events API
// Returns a CiviCRM API object on success or NULL on failure
function _ecp1_load_civievents() {
	global $civicrm_root;
	$ecp1_civiroot = null;
	if ( ! isset( $civicrm_root ) ) {
		// maybe settings haven't been loaded yet
		// This doesn't work for WAMP because CIVICRM_SETTINGS_PATH is redefined
		// by the CiviCRM config file as plugins/civicrm/civicrm/..\ which dirname
		// properly translates to plugins/civicrm/civicrm so we'll just hardcode
		//if ( defined( 'CIVICRM_SETTINGS_PATH' ) ) {
		//	$ecp1_civiroot = dirname( CIVICRM_SETTINGS_PATH ) . DIRECTORY_SEPARATOR . 'civicrm' . DIRECTORY_SEPARATOR;
		//} else {
			// Hard coded (which is what CiviCRM does anyway) as a fallback
			$ecp1_civiroot = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'civicrm' . DIRECTORY_SEPARATOR;
		//}
	} else {
		$ecp1_civiroot = $civicrm_root;
	}

	// Make sure it's a path
	if ( ! is_dir( $ecp1_civiroot ) )
		return null;
	
	// In which case we can now include the CiviCRM Event API
	if ( false === include_once( $ecp1_civiroot . 'civicrm/api/class.api.php' ) )
		return null;

	// Create a new API object
	return new civicrm_api3( array( 'conf_path' => $ecp1_civiroot ) );
}

// CiviCRM Proxy Implementation
class ECP1CiviCRMProxy extends ECP1Calendar {

	// Indicates that this calendar provider does NOT have a URL
	public function has_url() { return false; }

	// Because it's local data there is no need for caching so always expire
	public function cache_expired( $offset ) { return true; }

	// Takes start and end unix timestamps and DateTimeZone object
	// Uses the CiviCRM API to load all events in the time range.
	// Return TRUE on success or FALSE on failure.
	public function fetch( $start, $end, $dtz ) {
		// Validate the start and end params and get objects for formatting
		try {
			$start     = new DateTime( "@$start" );
			$end       = new DateTime( "@$end" );
		} catch( Exception $_e ) {
			return false; // error out NOW
		}

		// Ensure the timezone is valid or error out
		if ( ! $dtz instanceof DateTimeZone )
			return false;
		$start->setTimezone( $dtz );
		$end->setTimezone( $dtz );

		// Database lookup result
		$api = _ecp1_load_civievents();
		if ( is_null( $api ) )
			return false;

		// Load the raw events for the time range
		if ( $api->Event->Get( array( 'is_public' => 1, 'is_template' => 0 ) ) ) {
			$results = $api->lastResult->values; // more API calls later
			foreach ( $results as $event ) {
				$es = strtotime( $event->start_date );
				$ee = strtotime( $event->end_date );
				if ( $es && $ee ) { // if can't parse dates then ignore
					try {
						// Parse the dates and work out if all day event
						$estart = new DateTime( "@$es" );
						$eend = new DateTime( "@$ee" );
						$fd = false; // if times are null then assume full day
						if ( '0000' == $estart->format( 'Hi' ) && 
								'0000' == $eend->format( 'Hi' ) )
							$fd = true;

						// Make sure event starts in the range
						if ( ( $estart >= $start && $estart <= $end ) ||
							 ( $eend >= $start && $eend <= $end ) ):

						// Lookup the location for this event
						$elocation = null; $lat = null; $lng = null;
						if ( '1' == $event->is_show_location ) {
							if ( $api->LocBlock->Get( array( 'id' => $event->loc_block_id ) ) ) {
								// Use the first one
								if ( $api->lastResult->count > 0 ) {
									$address_id = $api->lastResult->values[0]->address_id;
									if ( $api->Address->Get( array( 'id' => $address_id ) ) ) {
										// Again use the first result
										$address = $api->lastResult->values[0];
										if ( isset( $address->street_address ) ) {
											$elocation = $address->street_address;
											if ( isset( $address->supplemental_address_1 ) )
												$elocation .= ', ' . $address->supplemental_address_1;
											if ( isset( $address->supplemental_address_2 ) )
												$elocation .= ', ' . $address->supplemental_address_2;
											if ( isset( $address->city ) )
												$elocation .= ', ' . $address->city;
										}

										// Did a lat/lng get specified
										if ( isset( $address->geo_code_1 ) && isset( $address->geo_code_2 ) ) {
											$lat = $address->geo_code_1;
											$lng = $address->geo_code_2;
										}
									}
								}
							}
						}

						// Use the API to get the event URL
						$url = $api->cfg->userSystem->url( 'civicrm/event/info', 'id=' . $event->id, true );

						// Add the event to the local cache - runtime only
						// NOTE: Deliberately use $es and $ee as that is what add_event expects
						$this->add_event( 'civiproxy-' . $event->id, $es, $ee,
							$fd ? 'Y' : 'N', $event->title, $url, $elocation,
							$event->summary, $event->description );

						// Are maps enabled?
						if ( '1' == $event->is_map ) {
							$meta = array( 'use_maps'=>true );
							if ( ! is_null( $lat ) ) $meta['lat'] = $lat;
							if ( ! is_null( $lng ) ) $meta['lng'] = $lng;
							$this->add_meta( 'civiproxy-' . $event->id, $meta );
						}

						endif; // event is in the range
					} catch( Exception $_ee ) {} // ignore invalid dates
				}
			}
			return true;
		}

		// Failed to return success so error out
		return false;
	}

}

// Don't close the php interpreter
/*?>*/
