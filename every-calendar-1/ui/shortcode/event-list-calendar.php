<?php
/**
 * Registers a shortcode for a list of events as the calendar
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// We need the Every Calendar settings
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );

// We need to know about the calendar/event post type meta/custom fields
require_once( ECP1_DIR . '/includes/data/calendar-fields.php' );
require_once( ECP1_DIR . '/includes/data/event-fields.php' );

// Load the repeating calendar scheduler
require_once( ECP1_DIR . '/includes/scheduler.php' );

// Load the helper functions
require_once( ECP1_DIR . '/functions.php' );

// THIS IS A SHORTCODE RENDERE: Register the type and callback
add_shortcode( 'eventlist', 'ecp1_event_list_calendar' );

// Placeholder for any dynamic script this calendar will show
$_ecp1_event_list_calendar_script = null;

// [ eventlist name="calendar name" starting="date to start from" until="end date of list" ]
// Defaults:
//   starting = now
//   until    = 1st Jan 2038 - close enough to overflow
function ecp1_event_list_calendar( $atts ) {
	global $_ecp1_event_list_calendar_script;

	// Register a hook to print the static JS to load FullCalendar on #ecp1_calendar
	add_action( 'wp_print_footer_scripts', 'ecp1_print_eventlist_load' );

	// Extract the attributes or assign default values
	extract( shortcode_atts( array(
		'name' => null, # checked below must not be null
		'starting' => time(), # default to now
		'until' => 2145916800, # 1st Jan 2038 at midnight UTC
	), $atts ) );
	
	// Make sure a name has been provided
	if ( is_null( $name ) )
		return sprintf( '<span class="ecp1_error">%s</span>', __( 'Unknown calendar: could not display.' ) );

	// Lookup the Post ID for the calendar with that name
	// Note: Pages are just Posts with post_type=page so the built in function works
	$cal_post = get_page_by_title( $name, OBJECT, 'ecp1_calendar' );
	_ecp1_parse_calendar_custom( $cal_post->ID );
	$raw_timezone = ecp1_get_calendar_timezone();
	$timezone = ecp1_timezone_display( $raw_timezone );


	// Try and parse the starting date time string
	if ( ! is_numeric( $starting ) ) {
		$test = strtotime( $starting );
		if ( false !== $test ) // PHP 5.1.0
			$starting = $test;
		else
			return sprintf( '<span class="ecp1_error">%s</span>', __( 'Could not parse event list start time.' ) );
	}

	// Try and parse the until date time if given
	if ( ! is_numeric( $until ) ){
		$test = strtotime( $until );
		if ( false !== $test ) // PHP 5.1.0
			$until = $test;
		else
			return sprintf( '<span class="ecp1_error">%s</span>', __( 'Could not parse event list end time.' ) );
	}
	

	// Lookup the events for the calendar and then render in a nice template
	$outstring = '<ol>';
	$events = _ecp1_event_list_get( $cal_post->ID, $starting, $until );
	_ecp1_parse_calendar_custom( $cal_post->ID );
	$feature_text = _ecp1_calendar_meta( 'ecp1_feature_event_textcolor' );
	$feature_back = _ecp1_calendar_meta( 'ecp1_feature_event_color' );
	foreach( $events as $event ) {
		// Featured events have their UTC times modified to be local to calendar TZ
		$ewhen = ecp1_formatted_datetime_range( $event['start'], $event['end'], $event['allday'] );
		
		$stylestring = '';
		if ( $event['feature'] )
			$stylestring = ' style="display:block;background:' . $feature_back . ';color:' . $feature_text . ';"';
		if ( $event['custom_colors'] )
			$stylestring = ' style="display:block;background:' . $event['bg_color'] . ';color:' . $event['text_color'] . ';"';

		$outstring .= sprintf('
<li class="ecp1_event">
	<span class="ecp1_feature">%s</span>
	<ul class="ecp1_event-details">
		<li><span style="display:block;"><a %s href="%s"><strong>%s</strong></a></span></li>
		<li><span class="ecp1_event-title"><strong>%s:</strong></span>
				<span class="ecp1_event-text">%s</span></li>
		<li><span class="ecp1_event-title"><strong>%s:</strong></span>
				<span class="ecp1_event-text">
					<span id="ecp1_event_location">%s</span>
				</span></li>
		<li><span class="ecp1_event-title"><strong>%s:</strong></span>
				<span class="ecp1_event-text_wide">%s</span></li>
	</ul>
</li>',
				$event['image'], $stylestring,
				urldecode( $event['url'] ), $event['title'], // get_the_title used in _ecp1_event_list_get
				__( 'When' ), $ewhen,
				__( 'Where' ), esc_html( $event['location'] ),
				__( 'Summary' ), esc_html( $event['summary'] ) );
	}
	$outstring .= '</ol>';

	$feeds = ""; // empty string to be updated if showing icon
	if ( '1' == _ecp1_get_option( 'show_export_icon' ) ) {

		// Now return HTML
		$rss_addr = home_url() . '/ecp1/' . urlencode( $cal_post->post_name ) . '/events.rss';
		$ical_addr = home_url() . '/ecp1/' . urlencode( $cal_post->post_name ) . '/events.ics';
		$icalfeed = sprintf( '<a href="%s" title="%s"><img src="%s" alt="ICAL" /></a>',
					$ical_addr, __( 'Subscribe to Calendar Feed' ),
					plugins_url( '/img/famfamfam/' . _ecp1_get_option( 'export_icon' ), dirname( dirname( __FILE__ ) ) ) );
		$_close_feed_popup = htmlspecialchars( __( 'Back to Event List' ) ); // strings for i18n
		$_feed_addrs = array(
			__( 'iCal / ICS' ) => $ical_addr,
			__( 'Outlook WebCal' ) => preg_replace( '/http[s]?:\/\//', 'webcal://', $ical_addr ),
			__( 'RSS' ) => $rss_addr,
		);
		$_feed_addrs_js = '{';
		foreach( $_feed_addrs as $title=>$link )
			$_feed_addrs_js .= sprintf( "'%s':'%s',", htmlspecialchars( $title ), $link );
		$_feed_addrs_js = trim( $_feed_addrs_js, ',' ) . '}';

		$_ecp1_event_list_calendar_script .= <<<ENDOFSCRIPT
var _feedLinks = $_feed_addrs_js;
jQuery(document).ready(function($) {
	// $() will work as an alias for jQuery() inside of this function
	$('#ecp1_calendar_list div.feeds a').click(function() {
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
		$feeds = '<div class="feeds">' . $icalfeed . '</div>';
	} // end if show export icon

	// Text based description make sure it's escaped
	$description = '';
	if ( ! _ecp1_calendar_meta_is_default( 'ecp1_description' ) )
		$description = wp_filter_post_kses( htmlspecialchars( _ecp1_calendar_meta( 'ecp1_description' ) ) );
	$description = '' != $description ? '<p><strong>' . $description . '</strong></p>' : '';
	$feature_msg = '';
	if ( _ecp1_calendar_show_featured( _ecp1_calendar_meta_id() ) &&
			'1' == _ecp1_get_option( 'base_featured_local_to_event' ) ) {
		// calendar shows feature events and feature events are shown in their
		// location local timezone -> show the note so people know different
		$feature_msg = sprintf( '<div style="padding:0 5px;color:%s;background-color:%s"><em>%s</em></div>',
				_ecp1_calendar_meta( 'ecp1_feature_event_textcolor' ),
				_ecp1_calendar_meta( 'ecp1_feature_event_color' ),
				htmlspecialchars( _ecp1_get_option( 'base_featured_local_note' ) ) );
	}

	$timezone = sprintf( '<div><div style="padding:0 5px;"><em>%s</em></div>%s</div>',
			sprintf( __( 'Events occur at %s local time.' ), $timezone ), $feature_msg );
	return sprintf( '<div id="ecp1_calendar_list">%s%s<div class="fullcal">%s</div>%s</div>', $feeds, $description, $outstring, $timezone );
}



// Looks up the event list and sorts it then returns an array
// note that dates are DateTime objects in the array
function _ecp1_event_list_get( $cal, $starting, $until ) {
	// This is abstracted slightly so we can merge external calendar events
	$local_events = EveryCal_Scheduler::GetEvents( $cal, $starting, $until );
	$event_cache = array(); // the events that get returned

	// Details about this calendar
	$tz = ecp1_get_calendar_timezone();   // the effective timezone
	$dtz = new DateTimeZone( $tz );
	$ex_cals = _ecp1_calendar_meta( 'ecp1_external_cals' ); // before loop
	$my_id = $cal; // because event meta reparses its calendars meta
	$now = new DateTime( NULL, new DateTimeZone( 'UTC' ) );

	// Loop over the events and setup the cache for local events
	foreach( $local_events as $event ) {
		// Check the custom fields make sense
		if ( _ecp1_render_default( $event, 'ecp1_start_ts' ) || _ecp1_render_default( $event, 'ecp1_end_ts' ) )
			continue; // need a start and finish so skip to next post

		try {
			$es = new DateTime( '@' . $event['ecp1_start_ts'] ); // requires PHP 5.2.0
			$ee = new DateTime( '@' . $event['ecp1_end_ts'] );   // 5.2.0 again
			$es->setTimezone( $dtz );
			$ee->setTimezone( $dtz );

			// If this is a feature event (not from this calendar) then change the
			// start/end times to be event local not calendar local if setting = 1
			$localdtz = new DateTimeZone( $event['_meta']['calendar_tz'] );
			if ( $localdtz->getOffset( $now ) != $dtz->getOffset( $now ) ) {
				if ( 'Y' == $event['ecp1_featured'] && '1' == _ecp1_get_option( 'base_featured_local_to_event' ) ) {
					// Base feature events at local calendar timezone or event local timezone?
					// Offset the start and end times by the event calendar offset
					$es->setTimezone( $localdtz );
					$ee->setTimezone( $localdtz );
				}
			}

			// The start and end times
			$estart  = clone $es;
			$eend    = clone $ee;
			$eallday = $event['ecp1_full_day'];

			// Description and permalink/external url
			$efeature = $my_id != $event['ecp1_calendar'] && 'Y' == $event['ecp1_featured'];
			$ecp1_desc = _ecp1_render_default( $event, 'ecp1_description' ) ? '' : strip_tags( _ecp1_event_meta( 'ecp1_description' ) );
			$ecp1_url = _ecp1_render_default( $event, 'ecp1_url' ) ? ecp1_permalink_event( $event ) : urldecode( $event['ecp1_url'] );

			// If feature images are enabled by the them (aka Post Thumbnails) then show if there is one
			$feature_image = false;
			if ( function_exists( 'add_theme_support' ) && function_exists( 'get_the_post_thumbnail' ) ) {
				if ( has_post_thumbnail( $event['post_id'] ) )
					$feature_image = get_the_post_thumbnail( $event['post_id'], 'thumbnail' );
			}
			
			// Are we overwriting the calendar colors with this event?
			$bg_color = '';
			$text_color = '';
			$overwrite_colors = 'Y' == $event['ecp1_overwrite_color'];
			if ( $overwrite_colors ) {
				$bg_color = $event['ecp1_local_color'];
				$text_color = $event['ecp1_local_textcolor'];
			}

			// Setup the event cache
			$event_cache[] = array(
				'start' => $estart, 'end' => $eend, 'allday' => $eallday,
				'feature' => $efeature, 'image' => $feature_image,
				'custom_colors' => $overwrite_colors, 'bg_color' => $bg_color, 'text_color' => $text_color,
				'title' => get_the_title( $event['post_id'] ), 'location' => $event['ecp1_location'],
				'summary' => $event['ecp1_summary'], 'description' => $ecp1_desc, 'url' => $ecp1_url );
		} catch( Exception $e ) {
			continue; // ignore bad timestamps they shouldn't happen
		}
	}

	// Lookup any external calendars and their events
	foreach( $ex_cals as $ex_cal ) {
		$calprov = ecp1_get_calendar_provider_instance( $ex_cal['provider'], $my_id, urldecode( $ex_cal['url'] ) );
		if ( null == $calprov )
			continue; // failed to load
		$continue = true;
		if ( $calprov->cache_expired( _ecp1_get_option( 'ical_export_external_cache_life' ) ) )
			$continue = $calprov->fetch( $start, $end, $dtz );

		if ( $continue ) { // fetched or not but is ok
			$evs = $calprov->get_events( $starting, $until );
			foreach( $evs as $eventid=>$event ) {
				try {
					$e  = $event['start'];
					$estart = new DateTime( "@$e" ); // requires PHP 5.2.0
					$e  = $event['end'];
					$eend = new DateTime( "@$e" ); // 5.2.0 again

					// Create this event in the cache
					$event_cache[] = array(
						'start' => $estart, 'end' => $eend, 'allday' => $event['all_day'],
						'title' => $event['title'], 'location' => $event['location'], 'summary' => $event['summary'],
						'description' => $event['description'], 'url' => $event['url'], 'feature' => false, 'image' => false );
				} catch( Exception $e ) {
					continue; // ignore bad timestamps they shouldn't happen
				}
			}
		}
	}

	// Sort the event_cache array by start date
	usort( $event_cache, '_ecp1_event_list_compare' );

	// Return the finalised array
	return $event_cache;
}

// Comparisson function for event_cache array entries
function _ecp1_event_list_compare( $a, $b ) {
	if ( ! array_key_exists( 'start', $a ) ) return 1;
	if ( ! array_key_exists( 'start', $b ) ) return -1;
	if ( $a['start'] == $b['start'] && $a['end'] == $b['end'] )
		return 0;
	return ( $a['start'] < $b['start'] || ( $a['start'] == $b['start'] && $a['end'] < $b['end'] ) ) ? -1 : 1;
}

// Function to print the dynamic calendar load script
function ecp1_print_eventlist_load() {
	global $_ecp1_event_list_calendar_script;
	if ( null != $_ecp1_event_list_calendar_script ) {
		printf( '%s<!-- Every Calendar +1 Init -->%s<script type="text/javascript">/* <![CDATA[ */%s%s%s/* ]]> */</script>%s', "\n", "\n", "\n", $_ecp1_event_list_calendar_script, "\n", "\n" );
	}
}

// Don't close the php interpreter
/*?>*/
