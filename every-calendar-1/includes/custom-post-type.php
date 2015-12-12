<?php
/**
 * Defines the custom post types for the Every Calendar +1 plugin
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Define the capability types for the two custom post types
// if you DO NOT want to use a role manager then set these
// to a existing WordPress type: I recommend post for calendar
// and post for events.
// 
// If you want more fine grained access control then change 
// these to something else and setup the capability/roles.
define( 'ECP1_CALENDAR_CAP', 'post' );
define( 'ECP1_EVENT_CAP', 'post' );

// Add action hooks
add_action( 'init', 'ecp1_register_types' );

// Function that creates the ECP1 Custom Post Types
function ecp1_register_types() {
	// Custom labels for the calendar post type
	$ecp1_cal_labels = array(
		'name' => _x( 'Calendars', 'post type general name' ),
		'singular_name' => _x( 'Calendar', 'post type singular name' ),
		'add_new' => _x( 'New Calendar', 'ecp1_event' ),
		'add_new_item' => __( 'New Calendar' ),
		'edit_item' => __( 'Edit Calendar' ),
		'new_item' => __( 'New Calendar' ),
		'view_item' => __( 'View Calendar' ),
		'search_items' => __( 'Search Calendars' ),
		'not_found' => __( 'No calendars found for your criteria!' ),
		'not_found_in_trash' => __( 'No calendars found in trash' ),
		'parent_item_colon' => '',
	);
	
	// Custom labels for the event post type
	$ecp1_evt_labels = array(
		'name' => _x( 'Events', 'post type general name' ),
		'singular_name' => _x( 'Event', 'post type singular name' ),
		'add_new' => _x( 'New Event', 'ecp1_event' ),
		'add_new_item' => __( 'New Event' ),
		'edit_item' => __( 'Edit Event' ),
		'new_item' => __( 'New Event' ),
		'view_item' => __( 'View Event' ),
		'search_items' => __( 'Search Events' ),
		'not_found' => __( 'No events found for your criteria!' ),
		'not_found_in_trash' => __( 'No events found in trash' ),
		'parent_item_colon' => '',
	);
	
	// Custom calendar post type arguments
	$ecp1_cal_args = array(
		'labels' => $ecp1_cal_labels,
		'description' => __( 'EveryCal+1 Events' ),
		'public' => true,
		'exclude_from_search' => true, # don't show events unless the plugin says to
		'show_ui' => true,
		'show_in_menu' => 'edit.php?post_type=ecp1_event',
		# capabilities meta which will need a role manager if not default
		'capability_type' => ECP1_CALENDAR_CAP,
		'map_meta_cap' => true, # make sure all meta capabilities are mapped
		'supports' => array( 'title', 'author' ),
		'rewrite' => array( 'slug' => 'calendar' ),
		'show_in_nav_menus' => true,
		'with_front' => false,
	);
	
	// Custom event post type arguments
	$ecp1_evt_args = array(
		'labels' => $ecp1_evt_labels,
		'description' => __( 'EveryCal+1 Events' ),
		'public' => true,
		'exclude_from_search' => true, # don't show events unless the plugin says to
		'show_ui' => true,
		'menu_position' => 30,
		# capabilities meta which will need a role manage if not default
		'capability_type' => ECP1_EVENT_CAP,
		'map_meta_cap' => true, # make sure all meta capabilities are mapped
		'supports' => array( 'title', 'thumbnail', 'comments', 'custom-fields', 'author' ),
		'query_var' => true,
		'rewrite' => false, // so we can have event/%ey%/%em%/%ed%/name
		'show_in_nav_menus' => false,
		'with_front' => 'false',
	);
	
	// Register the custom post type
	register_post_type( 'ecp1_event', $ecp1_evt_args );
	register_post_type( 'ecp1_calendar', $ecp1_cal_args );

	// Add a permalink structure for events
	//add_rewrite_tag( '%ecp1_event%', '([^/]+)', 'ecp1_event=' );
	//add_rewrite_tag( '%ees_year%', '([0-9]{4})', 'ees_year=' );
	//add_rewrite_tag( '%ees_month%', '([0-9]{1,2})', 'ees_month=' );
	//add_rewrite_tag( '%ees_day%', '([0-9]{1,2})', 'ees_day=' );
	add_permastruct( 'ecp1_event', '/event/%ees_year%/%ees_month%/%ees_day%/%ecp1_event%', false );
}

// Register a filter to replace %ey% %em% %ed% in the events permalink
add_filter( 'post_type_link', 'ecp1_event_permalink_filter', 100, 4 );
function ecp1_event_permalink_filter( $link, $id = 0, $leavename = false, $sample = false ) {
	if ( false === strpos( $link, '%ees_year%/%ees_month%/%ees_day%' ) )
		return $link; // not ours to filter
	$event = get_post( $id );
	if ( ! is_object( $event ) || 'ecp1_event' != $event->post_type )
		return $link; // not ours to filter

	// adapted from get_post_permalink
	$draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );
	if ( ! empty( $link ) && ( !$draft_or_pending || $sample ) ) {

		// Setup formats and a post date timestamp for later
		$yformat = 'Y'; $mformat = 'n'; $dformat = 'j';
		$start = new DateTime( "@" . strtotime( $event->post_date ) );

		// Get the post meta (OUTSIDE OF HELPERS)
		$custom = get_post_meta( $event->ID, 'ecp1_event_start', true );
		if ( '' != $custom && is_numeric( $custom ) ) {
			// Meta has been saved before
			try {
				$d = new DateTime( "@" . $custom );
				$start = $d; // 5.2 doesn't support setTimestamp
			} catch( Exception $e ) { } // do nothing
		}

		// Return the updated link
		$link = str_replace( '%ees_year%', $start->format( $yformat ), $link );
		$link = str_replace( '%ees_month%', $start->format( $mformat ), $link );
		$link = str_replace( '%ees_day%', $start->format( $dformat ), $link );

	} else { } // not using the fancy permalink option

	// Return the updated link
	return $link;
}

// Define a custom join to lookup events by start date year/month/day
add_filter( 'posts_join', 'ecp1_events_ymd_join' );
function ecp1_events_ymd_join( $join ) {
	global $wpdb, $wp_query;
	if ( isset( $wp_query->query_vars['post_type'] ) && 'ecp1_event' == $wp_query->query_vars['post_type'] && is_singular() )
		$join .= sprintf( ' JOIN %s AS ecp1_es ON %s.ID=ecp1_es.post_id AND ecp1_es.meta_key="ecp1_event_start" ', $wpdb->postmeta, $wpdb->posts );
	return $join;
}

// Define a custom where condition on ecp1_events for start year/month/day
add_filter( 'posts_where', 'ecp1_events_ymd_where' );
function ecp1_events_ymd_where( $where ) {
	global $wpdb, $wp_query;
	if ( isset( $wp_query->query_vars['post_type'] ) && 'ecp1_event' == $wp_query->query_vars['post_type'] && is_singular() ) {
		$y = $wp_query->query_vars['ees_year'];
		$m = $wp_query->query_vars['ees_month'];
		$d = $wp_query->query_vars['ees_day'];
		$where .= sprintf( ' AND ( ' .
			' %s = YEAR(CONVERT_TZ(FROM_UNIXTIME(ecp1_es.meta_value), @@session.time_zone, "+00:00")) AND ' .
			' %s = MONTH(CONVERT_TZ(FROM_UNIXTIME(ecp1_es.meta_value), @@session.time_zone, "+00:00")) AND ' .
			' %s = DAYOFMONTH(CONVERT_TZ(FROM_UNIXTIME(ecp1_es.meta_value), @@session.time_zone, "+00:00")) ) ',
			$wpdb->escape( $y ),
			$wpdb->escape( $m ),
			$wpdb->escape( $d ) );
	}
	return $where;
}

// Now define a capbilities filter to allow editors of calendars
// the ability to edit all events in that calendar (hopefully).
//add_filter( 'map_meta_cap', 'ecp1_map_calendar_cap_to_event', 100, 4 );
function ecp1_map_calendar_cap_to_event( $caps, $cap, $user_id, $args ) {
	
	// Only proceed if we have a post argument (i.e. the event)
	// and it actually is a post type of ecp1_event
	$event_id = is_array( $args ) ? $args[0] : $args;
	$event = get_post( $event_id );
	if ( 'ecp1_event' == get_post_type( $post ) ) {
		// NOTE: This is a RoadMap feature at the moment it's faked
		// ROADMAP: look at $cap and $user_id and ecp1_calendar in post meta
	}
	
	// Finally return the caps that are left over
	return $caps;

}

// Don't close the php interpreter
/*?>*/
