<?php
/**
 * Registers hooks to enqueue styles and scripts for the client UI
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// We need the calendar providers for script enqueueing
require_once( ECP1_DIR . '/includes/external-calendar-providers.php' );
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );
require_once( ECP1_DIR . '/includes/scheduler.php' );

// Define a global variable for the dynamic FullCalendar load script
$_ecp1_dynamic_calendar_script = null;

// Define a global variable for the dynamic load script for events (e.g. Maps)
$_ecp1_dynamic_event_script = null;

// Function that will return the necessary HTML blocks and queue some static
// JS for the document load event to render a FullCalendar instance
function ecp1_render_calendar( $calendar ) {
	global $_ecp1_dynamic_calendar_script;
	
	// Make sure the calendar provided is valid
	if ( ! is_array( $calendar ) )
		return sprintf( '<div id="ecp1_calendar" class="ecp1_error">%s</div>', __( 'Invalid calendar cannot display.' ) );

	// The parameter NEEDS to contain post slug
	if ( ! isset( $calendar['slug'] ) || empty( $calendar['slug'] ) )
		return sprintf( '<div id="ecp1_calendar" class="ecp1_error">%s</div>', __( 'No calendar slug provided cannot fetch events.' ) );
	
	// Extract the calendar meta data or go to renderer defaults
	
	// First day of the week
	$first_day = get_option( 'start_of_week ' );	// 0=Sunday 6=Saturday (uses WordPress)
	if ( ! _ecp1_calendar_meta_is_default( 'ecp1_first_day' ) && is_numeric( $calendar['ecp1_first_day'][0] ) &&
			( 0 <= $calendar['ecp1_first_day'][0] && $calendar['ecp1_first_day'][0] <= 6 ) ) {
		$first_day = $calendar['ecp1_first_day'][0];
	}
	
	// Text based description make sure it's escaped
	$description = $calendar['ecp1_description'][1];
	if ( ! _ecp1_calendar_meta_is_default( 'ecp1_description' ) )
		// 21/DEC/2012: Description is cleaned on save sanitize for output
		$description = esc_html( $calendar['ecp1_description'][0] );
	
	// Timezone events in this calendar occur in
	$raw_timezone = ecp1_get_calendar_timezone();
	$timezone = ecp1_timezone_display( $raw_timezone );
	
	$default_view = 'month';	// How the calendar displays by default
	if ( ! _ecp1_calendar_meta_is_default( 'ecp1_default_view' ) &&
			in_array( $calendar['ecp1_default_view'][0], array( 'month', 'week' ) ) ) {
		$default_view = $calendar['ecp1_default_view'][0];
		if ( 'week' == $default_view )
			$default_view = 'agendaWeek'; // change to the Full Calendar string
	}

	// Default parameters for event sources
	$event_source_params = array(
		'_defaults' => array( // Note values ARE NOT quoted automatically do it HERE
			'startParam' => "'ecp1_start'",  # default is start but plugin uses ecp1_start
			'endParam'   => "'ecp1_end'",	#  as above but for end
			'ignoreTimezone' => 'false',	 # don't ignore ISO8601 timezone details
			'color'	 => "'#3366cc'",	  # the event background and border colours
			'textColor' => "'#ffffff'",	  #  and the text colour (any css format)
		)
	);
	
	// Create a URL and event parameter array for local event posts
	$event_source_params['local'] = $event_source_params['_defaults'];
	$event_source_params['local']['url'] = sprintf( "'%s/ecp1/%s/events.json'", home_url(), $calendar['slug'] );
	$event_source_params['local']['color'] = sprintf( "'%s'", _ecp1_calendar_meta( 'ecp1_local_event_color' ) );
	$event_source_params['local']['textColor'] = sprintf( "'%s'", _ecp1_calendar_meta( 'ecp1_local_event_textcolor' ) );
	$event_source_params['local']['ignoreTimezone'] = 'true'; # show events at time at event location

	// Test if there are external URLs and create source params as needed
	$providers = ecp1_calendar_providers();
	$ecp1_cals = _ecp1_calendar_meta( 'ecp1_external_cals' );
	foreach( $ecp1_cals as $id=>$cal ) {
		if ( array_key_exists( $cal['provider'], $providers ) ) {
			$provider = $providers[$cal['provider']];
			$provinst = ecp1_get_calendar_provider_instance( $cal['provider'], _ecp1_calendar_meta_id(), urldecode( $cal['url'] ) );
			
			// eventSources array items
			$ns = $event_source_params['_defaults']; // New Source
			
			// If the provider instance has a valid URL then use it
			if ( $provinst->has_url() ) {
				$ns['url'] = sprintf( "'%s'", urldecode( $cal['url'] ) );
				unset( $ns['startParam'] ); // because we assume a FullCalendar plugin
				unset( $ns['endParam'] ); // supports this so knows the correct ones
			} else {
				// The events are proxied through EveryCal+1 so build proxy URL
				$ns['url'] = sprintf( "'%s/ecp1proxy/%s/%s/events.json'", home_url(), $calendar['slug'], $cal['provider'] );
				$ns['ignoreTimezone'] = 'true'; # show events at their specified time on calendar
			}

			// Peg to the calendar timezone so don't get funny date/times
			$ns['currentTimezone'] = "'$raw_timezone'"; # Quoted see _defaults
			$ns['color'] = sprintf( "'%s'", $cal['color'] );
			$ns['textColor'] = sprintf( "'%s'", $cal['text'] );

			// Data type for FullCalendar helps to pick the plugin
			if ( array_key_exists( 'fullcal_datatype', $provider ) ) {
				$ns['dataType'] = sprintf( "'%s'", $provider['fullcal_datatype'] );
			}
		
			// Add to the event sources array
			$event_source_params['external' + $id] = $ns;
		}
	}

	// Get rid of the defaults and write out an event sources array
	unset( $event_source_params['_defaults'] );
	$separator = '';
	$event_sources = '[';
	foreach( $event_source_params as $skey=>$params ) {
		$event_sources .= sprintf( "%s { _ecp1sn: '%s'", $separator, $skey );
		foreach( $params as $key=>$value )
			// The value IS NOT automatically quoted to allow for ANY param value
			// The parameters MUST be quoted above when added to the array
			$event_sources .= sprintf( ", %s: %s", $key, $value );
		$event_sources .= ' }';
		$separator = ',';
	}
	$event_sources .= ']';

	// Register a hook to print the static JS to load FullCalendar on #ecp1_calendar
	add_action( 'wp_print_footer_scripts', 'ecp1_print_fullcalendar_load' );

	// A string for i18n-ing the read more links
	$_read_more = htmlspecialchars( __( 'Read more...' ) );
	$_show_map = htmlspecialchars( __( 'Location Map' ) );
	$_load_map = htmlspecialchars( __( 'Loading map...' ) );
	$_back_to_event = htmlspecialchars( __( 'Back to Event' ) );
	$_large_map = htmlspecialchars( __( 'Large Map' ) );
	$_geocode_addresses = 'false';
	$_use_maps = 'false';
	$_map_provider = 'false';
	$_geocoder_enabled = 'false';
	$_geocoder_service = 'false';
	$_show_time_on_all_day = '1' == _ecp1_get_option( 'show_time_on_all_day' ) ? 'true' : 'false';

	// If maps are enabled then update the client settings
	if ( ECP1Mapstraction::MapsEnabled() ) {
		$_use_maps = 'true';
		$provider = ECP1Mapstraction::GetProviderKey();
		if ( ECP1Mapstraction::ValidProvider( $provider ) ) {
			// Get the map provider string
			$_map_provider = ECP1Mapstraction::ProviderData( $provider, 'mxnid' );
			// Get the geocoding status
			$geocoder = ECP1Mapstraction::GetGeocoderKey();
			if ( ! is_null( $geocoder ) ) {
				// Geocoding is enabled
				$_geocoder_enabled = 'true';
				$_geocoder_service = ECP1Mapstraction::ProviderData( $geocoder, 'mxnid' );
			}
		}
	}

	// Show popups or go to event pages?
	$event_click = '';
	if ( '1' == _ecp1_get_option( 'popup_on_click' ) )
		$event_click = 'eventClick: ecp1_onclick,';

	// Get the time formats for the calendar
	$agenda_format = _ecp1_get_option( 'week_time_format' );
	$month_format = _ecp1_get_option( 'month_time_format' );

	// Now build the actual JS that will be loaded
	$_ecp1_dynamic_calendar_script = <<<ENDOFSCRIPT
jQuery(document).ready(function($) {
	// $() will work as an alias for jQuery() inside of this function
	$('#ecp1_calendar div.fullcal').empty().fullCalendar({
		header: { left: 'prev,next today', center: 'title', right: 'month,agendaWeek' },
		timeFormat: { agenda: '$agenda_format', '': '$month_format' },
		firstDay: $first_day,
		weekends: true,
		defaultView: '$default_view',
		eventSources: $event_sources,
		$event_click
		eventRender: ecp1_onrender
	});

	// Assign the global Read More / Show Map link variable for i18n
	_readMore = '$_read_more';
	_showMapStr = '$_show_map';
	_loadMapStr = '$_load_map';
	_showEventDetails = '$_back_to_event';
	_showLargeMap = '$_large_map';
	_geocodeAddr = $_geocode_addresses;
	_showMap = $_use_maps;
	_mapProvider = '$_map_provider';
	_geocoderEnabled = $_geocoder_enabled;
	_geocoderServer = '$_geocoder_service';
	_showTimeOnAllDay = $_show_time_on_all_day;
});

ENDOFSCRIPT;
	
	// Now return HTML that the above script will use
	$rss_addr = home_url() . '/ecp1/' . urlencode( $calendar['slug'] ) . '/events.rss';
	$ical_addr = home_url() . '/ecp1/' . urlencode( $calendar['slug'] ) . '/events.ics';
	$_close_feed_popup = htmlspecialchars( __( 'Back to Calendar' ) ); // strings for i18n
	$_feed_addrs = array(
		__( 'iCal / ICS' ) => $ical_addr,
		__( 'Outlook WebCal' ) => preg_replace( '/http[s]?:\/\//', 'webcal://', $ical_addr ),
		__( 'RSS' ) => $rss_addr,
	);
	$_feed_addrs_js = '{';
	foreach( $_feed_addrs as $title=>$link )
		$_feed_addrs_js .= sprintf( "'%s':'%s',", htmlspecialchars( $title ), $link );
	$_feed_addrs_js = trim( $_feed_addrs_js, ',' ) . '}';

	$_ecp1_dynamic_calendar_script .= <<<ENDOFSCRIPT
var _feedLinks = $_feed_addrs_js;
jQuery(document).ready(function($) {
	// $() will work as an alias for jQuery() inside of this function
	$('#ecp1_show_feeds').click(function() {
		var popup = $( '<div></div>' )
				.attr( { id:'_ecp1-feed-popup' } ).css( { display:'none', 'z-index':9999 } );
		var pw = $( window ).width();
		var ph = $( document ).height();
		var ps = $( document ).scrollTop(); ps = ( ps+175 ) + 'px auto 0 auto';

		var fL = $( '<ul></ul>' );
		for ( key in _feedLinks ) {
			fL.append( $( '<li></li>' )
					.append( $( '<span></span>' )
						.text( key ) )
					.append( $( '<a></a>' )
						.attr( { href:_feedLinks[key], title:key } )
						.text( _feedLinks[key] ) ) );
		}

		popup.css( { width:pw, height:ph, display:'block' } )
			.append( $( '<div></div>' )
				.addClass( 'inner' )
				.css( { background:'#ffffff', padding:'1em', width:800, height:200, margin:ps } )
				.append( jQuery( '<div></div>' )
					.css( { textAlign:'right' } )
					.append( jQuery( '<a></a>' )
						.css( { cursor:'pointer' } )
						.text( '$_close_feed_popup' )
						.click( function( event ) {
							event.stopPropagation();
							jQuery( '#_ecp1-feed-popup' ).remove();
						} ) ) )
				.append( jQuery( '<div></div>' )
					.css( { textAlign:'left', width:800, height:150, paddingTop:25 } )
					.append( fL ) ) );

		$('body').append(popup);
		return false;
	} );
} );

ENDOFSCRIPT;

	// Load the template and replace the placeholders
	$outstr = _ecp1_get_option( 'calendar_template' );

	// Is the export icon enabled?
	if ( '1' == _ecp1_get_option( 'show_export_icon' ) ) {
		$outstr = str_replace( array( '+FEEDS+', '+ENDFEEDS+' ), '', $outstr );
		$outstr = str_replace( array( '+FEED_LINK+', '+FEED_ICON+' ),
			array( $ical_addr, plugins_url( '/img/famfamfam/' . _ecp1_get_option( 'export_icon' ), dirname( __FILE__ ) ) ), $outstr );
	} else {
		$start = strpos( $outstr, '+FEEDS+' );
		$finish = strpos( $outstr, '+ENDFEEDS+' );
		$outstr = substr( $outstr, 0, $start ) . substr( $outstr, $finish + 12 );
	}

	// Do simple string replacements for placeholders
	$outstr = str_replace(
		array( '+DESCRIPTION_TEXT+', '+CALENDAR_LOADING+', '+TIMEZONE_DISCLAIMER+',
			'+FEATURE_TEXT_COLOR+', '+FEATURE_BACKGROUND+', '+FEATURE_EVENT_NOTICE+' ),
		array( $description, __( 'Loading...' ), sprintf( 'Events occur at %s local time.', $timezone ),
			_ecp1_calendar_meta( 'ecp1_feature_event_textcolor' ),
			_ecp1_calendar_meta( 'ecp1_feature_event_color' ),
			htmlspecialchars( _ecp1_get_option( 'base_featured_local_note' ) ) ),
		$outstr );
	return $outstr;
}

// Function to print the dynamic calendar load script 
function ecp1_print_fullcalendar_load() {
	global $_ecp1_dynamic_calendar_script;
	if ( null != $_ecp1_dynamic_calendar_script ) {
		printf( '%s<!-- Every Calendar +1 Init -->%s<script type="text/javascript">/* <![CDATA[ */%s%s%s/* ]]> */</script>%s', "\n", "\n", "\n", $_ecp1_dynamic_calendar_script, "\n", "\n" );
	}
}

// Function that will return the necessary HTML blocks and queue some static
// JS for the document load event to render an event post page
function ecp1_render_event( &$event ) {
	global $_ecp1_dynamic_event_script, $post, $wp_query, $wpdb;

	// Make sure the event provided is valid
	if ( ! is_array( $event ) )
		return sprintf( '<div id="ecp1_event" class="ecp1_error">%s</div>', __( 'Invalid event cannot display.' ) );
	
	// Get the timezone for the events calendar
	$caltz = new DateTimeZone( $event['_meta']['calendar_tz'] );
	
	// Does the event repeat and if so do we want a repetition?
	$is_cancelled = false;
	$repetition_date = null;
	$repeating_event = 'Y' == $event['ecp1_repeating'][0];
	if ( $repeating_event && isset( $wp_query->query_vars['ecp1_repeat'] ) ) {
		try {
			$repetition_date = new DateTime( $wp_query->query_vars['ecp1_repeat'], $caltz );
		} catch( Exception $rex ) {
			return sprintf( '<div id="ecp1_event" class="ecp1_error">%s</div>', __( 'Invalid repetition date parameter' ) );
		}
	}

	// If this is a repeating event with a given repetition check that exists
	// and apply any changes to the event array as necessary if exception
	if ( $repeating_event && $repetition_date != null ) {
		$cache_table = $wpdb->prefix . 'ecp1_cache';
		$rep = $wpdb->get_results( $wpdb->prepare(
			"SELECT start, changes, is_exception FROM $cache_table WHERE post_id = %s AND start = %s LIMIT 1",
			$post->ID, $repetition_date->format( 'Y-m-d' )
		), OBJECT ); // num index object
		if ( $rep == null ) {
			return sprintf( '<div id="ecp1_evept" class="ecp1_error">%s</div>', __( 'No such repeat for this event' ) );
		} else {
			
			$repeat = $rep[0]; // there should only be one 

			// Change the start DATE (ONLY THE DATE) to $repeat->start
			$oldstart = $event['ecp1_start_ts'][0];
			$esdate = new DateTime( $repeat->start, $caltz );
			$osdate = new DateTime( '@' . $event['ecp1_start_ts'][0] ); $osdate->setTimezone( $caltz );
			$osdate->setDate( $esdate->format( 'Y' ), $esdate->format( 'n' ), $esdate->format( 'j' ) );
			$event['ecp1_start_ts'][0] = $osdate->format( 'U' );
			// Update the end date by the same amount
			if ( $oldstart != $event['ecp1_start_ts'][0] ) {
				$oedate = new DateTime( '@' . $event['ecp1_end_ts'][0] ); $oedate->setTimezone( $caltz );
				$daysdiff = floor( ( $event['ecp1_start_ts'][0] - $oldstart ) / 86400 );
				$oedate->modify( "$daysdiff day" );
				$event['ecp1_end_ts'][0] = $oedate->format( 'U' );
			}

			// Update the event details for the repeat
			if ( $repeat->is_exception ) {
				// Unserialize the exception details
				$exdetail = unserialize( $repeat->changes );
				$is_cancelled = array_key_exists( 'is_cancelled', $exdetail ) && $exdetail['is_cancelled'] ? true : false;
				$updates = array( '_meta' => array( 'calendar_tz' => $event['_meta']['calendar_tz'] ) );
				foreach( EveryCal_Exception::$FIELDS as $key=>$params ) {
					$updates[$params['meta_key']] = $event[$params['meta_key']][0];
					if ( array_key_exists( $key, $exdetail ) )
						EveryCal_Exception::Update( $key, $updates, $exdetail[$key] );
					$event[$params['meta_key']][0] = $updates[$params['meta_key']];
				}
			}

		}
	}
	
	// Register a hook to print the static JS to load FullCalendar on #ecp1_calendar
	add_action( 'wp_print_footer_scripts', 'ecp1_print_event_load' );
	
	// Extract the event fields or go to renderer defaults
	// $p variables are placeholders for the i18n titles
	$pwhen = __( 'When' );
	$pwhere = __( 'Where' );
	$psummary = __( 'Summary' );
	$pdetails = __( 'Details' );
	
	// String placeholder for the time period this event runs over
	$ecp1_time = __( 'Unknown' );
	if ( ! _ecp1_event_meta_is_default( 'ecp1_start_ts' ) || ! _ecp1_event_meta_is_default( 'ecp1_end_ts' ) ) {
		$ecp1_time = ecp1_formatted_date_range( $event['ecp1_start_ts'][0], $event['ecp1_end_ts'][0],
							$event['ecp1_full_day'][0], $event['_meta']['calendar_tz'] );
	}

	// If the event was cancelled then set when to reflect that
	// NOTE: This is necessary here because the event can still be linked
	// where as EveryCal_Scheduler::GetEvents does not include the repeat
	if ( $repeating_event && $is_cancelled )
		$ecp1_time = __( 'Event has been cancelled' );
	
	// String placeholder for the summary text
	$ecp1_summary = $event['ecp1_summary'][1];
	if ( ! _ecp1_event_meta_is_default( 'ecp1_summary' ) )
		$ecp1_summary = esc_html( $event['ecp1_summary'][0] );
	
	// String placeholders for the location and map coords if enabled
	$ecp1_location = $event['ecp1_location'][1];
	if ( ! _ecp1_event_meta_is_default( 'ecp1_location' ) )
		$ecp1_location = esc_html( $event['ecp1_location'][0] );

	// Enable map for the event if required
	$ecp1_map_placeholder = ''; 
	if ( ECP1Mapstraction::MapsEnabled() && 'Y' == _ecp1_event_meta( 'ecp1_showmap' ) ) {
		$provider = ECP1Mapstraction::GetProviderKey();
		if ( ECP1Mapstraction::ValidProvider( $provider ) && 
				( ( ! _ecp1_event_meta_is_default( 'ecp1_coord_lat' ) && ! _ecp1_event_meta_is_default( 'ecp1_coord_lng' ) ) || // has lat/lng
				  ( ! _ecp1_event_meta_is_default( 'ecp1_location' ) && $mapinstance->support_geocoding() ) ) ) { // or have address + geocoding
			$map_provider = ECP1Mapstraction::ProviderData( $provider, 'mxnid' );
			$ecp1_element_id = 'ecp1_event_map';
			$ecp1_map_placeholder = '<div id="' . $ecp1_element_id . '">' . __( 'Loading map...' ) . '</div>';
			// Build options for the map loader function
			$options_hash = array( 'element' => "'$ecp1_element_id'" );
				
			// Decide between location string and lat/lng
			$ecp1_lat = _ecp1_event_meta( 'ecp1_coord_lat' );
			$ecp1_lng = _ecp1_event_meta( 'ecp1_coord_lng' );
			if ( is_numeric( $ecp1_lat ) && is_numeric( $ecp1_lng ) ) {
				$options_hash['lat'] = $ecp1_lat;
				$options_hash['lng'] = $ecp1_lng;
			} else {
				$options_hash['location'] = "'$ecp1_location'";
			}

			// Do we want placemarks? and if so default or a url?
			if ( 'Y' == _ecp1_event_meta( 'ecp1_showmarker' ) ) {
				if ( _ecp1_event_meta_is_default( 'ecp1_map_placemarker' ) ||
					! file_exists( ECP1_DIR . '/img/mapicons/' . _ecp1_event_meta( 'ecp1_map_placemarker' ) ) )
					$options_hash['mark'] = 'true';
				else // file given and exists
					$options_hash['mark'] = sprintf( '"%s"', plugins_url( '/img/mapicons/' . _ecp1_event_meta( 'ecp1_map_placemarker' ), dirname( __FILE__ ) ) );
			} else {
				$options_hash['mark'] = 'false';
			}

			// Map zoom is simple
			$options_hash['zoom'] = _ecp1_event_meta( 'ecp1_map_zoom' );

			$options_hash_str = '{ ';
			foreach( $options_hash as $_k=>$_v )
				$options_hash_str .= sprintf( '%s:%s, ', $_k, $_v );
			$options_hash_str = trim( $options_hash_str, ',' ) . ' }';

			// Dynamic script to run on document ready
			$_ecp1_dynamic_event_script = <<<ENDOFSCRIPT
jQuery(document).ready(function($) {
	// $() will work as an alias for jQuery() inside of this function
	var container = jQuery( '#$ecp1_element_id' );
	if ( container.length > 0 ) {
		var pWidth = container.parent().parent().width() - 150 - 90 - 25; // thumb - title - buffer
		var pHeight = pWidth * 0.65;
		container.css( { width:pWidth, height:pHeight } );
	}
	_mapLoadFunction( $options_hash_str );
} );
ENDOFSCRIPT;
		}
	}

	// String placeholder for the event information (i.e. URL or Internal Description)
	// Because we can do it here we'll support BOTH values but the onclick event for 
	// the calendar uses ONSITE description in preference (which means people will come
	// to this post page and then be able to offsite click).
	$ecp1_info = '';
	$ecp1_desc = _ecp1_event_meta_is_default( 'ecp1_description' ) ? null : ecp1_the_content( $event['ecp1_description'][0] );
	$ecp1_url = _ecp1_event_meta_is_default( 'ecp1_url' ) ? null : urldecode( $event['ecp1_url'][0] );
	if ( ! is_null( $ecp1_desc ) && ! is_null( $ecp1_url ) ) {
		// Both given so render as description<br/>Read more...
		$ecp1_info = sprintf( '<div>%s</div><div><a href="%s" target="_blank">%s</a></div>', $ecp1_desc, $ecp1_url, __( 'Read more ...' ) );
	} elseif ( ! is_null( $ecp1_desc ) ) {
		// Only a description
		$ecp1_info = sprintf( '<div>%s</div>', $ecp1_desc );
	} elseif ( ! is_null( $ecp1_url ) ) {
		// Only a URL
		$ecp1_info = sprintf( '<div><a href="%s" target="_blank">%s</a></div>', $ecp1_url, __( 'Read more...' ) );
	} // else: leave as empty string summary must be enough
	
	// If feature images are enabled by the them (aka Post Thumbnails) then show if there is one
	$feature_image = '';
	if ( function_exists( 'add_theme_support' ) && function_exists( 'get_the_post_thumbnail' ) ) {
		if ( has_post_thumbnail( $post->ID ) )
			$feature_image = get_the_post_thumbnail( $post->ID, 'thumbnail' );
	}

	$outstr = _ecp1_get_option( 'event_template' );
	$outstr = str_replace(
		array( '+FEATURE_IMAGE+', '+TITLE_TIME+', '+TITLE_LOCATION+', '+TITLE_SUMMARY+',
			'+TITLE_DETAILS+', '+EVENT_TIME+', '+EVENT_LOCATION+', '+EVENT_SUMMARY+',
			'+EVENT_DETAILS+', '+MAP_CONTAINER+' ),
		array( $feature_image, $pwhen, $pwhere, $psummary, $pdetails, $ecp1_time, $ecp1_location,
			$ecp1_summary, $ecp1_info, $ecp1_map_placeholder ),
		$outstr );
	return $outstr;
}

// Function to print the dynamic event load script 
function ecp1_print_event_load() {
	global $_ecp1_dynamic_event_script;
	if ( null != $_ecp1_dynamic_event_script ) {
		printf( '%s<!-- Every Calendar +1 Init -->%s<script type="text/javascript">/* <![CDATA[ */%s%s%s/* ]]> */</script>%s', "\n", "\n", "\n", $_ecp1_dynamic_event_script, "\n", "\n" );
	}
}

// Don't close the php interpreter
/*?>*/
