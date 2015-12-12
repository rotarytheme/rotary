<?php
/**
 * Adds the Admin panel extra messages and post fields
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Ensure we can look at the event settings and map providers
require_once( ECP1_DIR . '/includes/mapstraction/controller.php' );
require_once( ECP1_DIR . '/includes/data/ecp1-settings.php' );

// Functions that will enqueue CSS / JS based on param
function ecp1_enqueue_admin_css() {
	wp_register_style( 'ecp1_admin_style', plugins_url( '/css/ecp1-admin.css', dirname( __FILE__ ) ) );
	wp_enqueue_style( 'ecp1_admin_style' );
}

// Now for the JS
function ecp1_enqueue_admin_js() {
	wp_enqueue_script( 'jquery' );
}

// Specialised function for the calendar color picker
function ecp1_calendar_edit_libs() {
	wp_register_style( 'ecp1_colorpicker_style', plugins_url( '/colorpicker/css/colorpicker.css', dirname( __FILE__ ) ) );
	wp_register_script( 'ecp1_colorpicker_script', plugins_url( '/colorpicker/js/colorpicker.js', dirname( __FILE__ ) ), array( 'jquery' ) );
	wp_register_script( 'ecp1_colorpicker_init_script', plugins_url( '/js/colorpicker.js', dirname( __FILE__ ) ), array( 'ecp1_colorpicker_script' ) );
	wp_enqueue_style( 'ecp1_colorpicker_style' );
	wp_enqueue_script( 'ecp1_colorpicker_script' );
	wp_enqueue_script( 'ecp1_colorpicker_init_script' );
}

// Specialised function for the event maps, date picker and editor
function ecp1_event_edit_libs() {
	wp_register_style( 'ecp1_jquery-ui-datepicker_style', plugins_url( '/jquery-ui/datepicker.css', dirname( __FILE__ ) ) );
	wp_enqueue_style( 'ecp1_jquery-ui-datepicker_style' );

	wp_register_script( 'ecp1_jquery-ui-datepicker_script', plugins_url( '/jquery-ui/datepicker.min.js', dirname( __FILE__ ) ), array( 'jquery-ui-core' ) );
	wp_register_script( 'ecp1_event_datepicker_script', plugins_url( '/js/datepicker.js', dirname( __FILE__ ) ), array( 'ecp1_jquery-ui-datepicker_script' ) );

	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'ecp1_jquery_ui_datepicker_script' );
	wp_enqueue_script( 'ecp1_event_datepicker_script' );

	wp_register_style( 'ecp1_colorpicker_style', plugins_url( '/colorpicker/css/colorpicker.css', dirname( __FILE__ ) ) );
	wp_register_script( 'ecp1_colorpicker_script', plugins_url( '/colorpicker/js/colorpicker.js', dirname( __FILE__ ) ), array( 'jquery' ) );
	wp_register_script( 'ecp1_colorpicker_init_script', plugins_url( '/js/colorpicker.js', dirname( __FILE__ ) ), array( 'ecp1_colorpicker_script' ) );
	wp_enqueue_style( 'ecp1_colorpicker_style' );
	wp_enqueue_script( 'ecp1_colorpicker_script' );
	wp_enqueue_script( 'ecp1_colorpicker_init_script' );

	// Include the TinyMCE editor - this requires use of the_editor($content, 'element_id')
	// inplace of the <textarea></textarea> tags on the event meta box - and naturally will
	// obey user preferences on richtext editors etc...
	// 19/DEC/2012: not needed for newer versions of WP
	/*if ( user_can_richedit() ) {
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-color' );
		wp_enqueue_scripts( 'editor' );
		if ( function_exists( 'add_thickbox' ) ) add_thickbox();
		wp_enqueue_scripts( 'media-upload' );
		if ( function_exists( 'wp_tiny_mce' ) ) wp_tiny_mce();
		wp_admin_css();
		wp_enqueue_script( 'utils' );
		do_action( 'admin_print_styles-post-php' );
		do_action( 'admin_print_styles' );
		wp_enqueue_script( 'ecp1_event_wysiwyg_script' );
	}*/

	if ( ECP1Mapstraction::MapsEnabled() ) {
		ECP1Mapstraction::EnqueueResources( ECP1Mapstraction::ADMIN );
	}

}

// Add the CSS for either post type
add_action( 'admin_enqueue_scripts', 'ecp1_add_admin_styles', 100 );
function ecp1_add_admin_styles() {
	global $post_type;
	if ( 'ecp1_calendar' == $post_type || 'ecp1_event' == $post_type ) {
		ecp1_enqueue_admin_css();
	}
}

// Add the global JS for either post type
add_action( 'admin_enqueue_scripts', 'ecp1_add_admin_scripts', 100, 1 );
function ecp1_add_admin_scripts( $hook=null ) {
	global $post_type;
	if ( 'ecp1_calendar' == $post_type || 'ecp1_event' == $post_type ) {
		ecp1_enqueue_admin_js();
	}
	if ( 'ecp1_calendar' == $post_type && in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
		ecp1_calendar_edit_libs();
	}
	if ( 'ecp1_event' == $post_type && in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
		ecp1_event_edit_libs();
	}
}

// Add filters to make sure calendar and events display instead of post
add_filter( 'post_updated_messages', 'ecp1_calendar_updated_messages' );
function ecp1_calendar_updated_messages() {
	global $post, $post_ID;
	
	// Custom update messages for the calendar
	$messages['ecp1_calendar'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Calendar updated. <a href="%s">View calendar...</a>' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated (2).' ),
		3 => __( 'Custom field deleted (3).' ),
		4 => __( 'Calendar updated.' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Calendar restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Calendar published. <a href="%s">View calendar...</a>' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Calendar saved.' ),
		8 => sprintf( __( 'Calendar submitted. <a target="_blank" href="%s">Preview calendar...</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Calendar scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview calendar...</a>' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Calendar draft updated. <a target="_blank" href="%s">Preview calendar...</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);
	
	// Custom update messages for the events
	$messages['ecp1_event'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Event updated. <a href="%s">View event...</a>' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated (2).' ),
		3 => __( 'Custom field deleted (3).' ),
		4 => __( 'Event updated.' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Event published. <a href="%s">View event...</a>' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Event saved.' ),
		8 => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event...</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event...</a>' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event...</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);
	
	return $messages;
}

// Display contextual help for calendars and events
add_action( 'contextual_help', 'ecp1_add_help_text', 15, 3 );
function ecp1_add_help_text($contextual_help, $screen_id, $screen) {
	//$contextual_help .= var_dump($screen); // DEBUG code to determine $screen->id
	
	if ( 'edit-ecp1_calendar' == $screen->id ) { // List of Calendar posts

		$contextual_help =
'<p>' . __( 'This is a list of calendars you can edit.' ) . '</p>';

	} else if ( 'edit-ecp1_event' == $screen->id ) {

		$contextual_help = 
'<p>' . __( 'This is a list of your events and events on calendars you can edit.' ) . '</p>';

	} else if ( 'ecp1_calendar' == $screen->id ) { // Edit Calendar Post

		$contextual_help =
'<p>' . __( 'A calendar can contain local events and can load events from external calendars too.' ) . '</p>' .
'<ul>' .
	'<li>' . __( 'Please use a clear and descriptive title' ) . '</li>' .
	'<li>' . __( 'Your description will be displayed on the calendar page above your calendar' ) . '</li>' .
	'<li>' . __( 'Timezone tells readers if there is any time offset for events in this calendar' ) . '</li>' .
	'<li>' . __( 'Month view shows a month grid, week view shows an agenda for the week' ) . '</li>' .
'</ul>';

	} else if ( 'ecp1_event' == $screen->id ) { // Edit Event Post

		$contextual_help =
'<p>' . __( 'An event occurs on a local calendar and optionally on calendars setup to show feature events.' ) . '</p>' .
'<ul>' .
	'<li>' . __( 'The event title should be informative and name your event.' ) . '</li>' .
	'<li>' . __( 'The calendar field nominates which of your calendars the event should appear on.' ) . '</li>' .
	'<li>' . __( 'You should provide a breif summary description of your event.' ) . '</li>' .
	'<li>' . __( 'Optionally you can provide an event website (such as a Facebook page) and/or a full event description: if you provide BOTH a link to the external page will be display at the bottom of the local event page.' ) . '</li>' .
	'<li>' . __( 'Start and end dates / times should be entered. If the event runs all day tick the full day event box.' ) . '</li>' .
	'<li>' . __( 'Location should be an address or commonly known landmark.' ) . '</li>' .
	'<li>' . __( 'You can use the map to give a visual representation of the area: you can move the map and placemarkers.' ) . '</li>' .
'</ul>';

	}
	
	return $contextual_help;
}

// Filter event posts requests to not show events in calendars
// the current user is not allowed to edit (only show events in
// calendars the user is allowed to edit - or is author of).
add_filter( 'posts_request', 'ecp1_filter_event_posts_requests', 1000, 2 );
function ecp1_filter_event_posts_requests( $query, $object ) {
	global $post_type, $wpdb;
	if ( 'ecp1_event' == $post_type && isset( $object->query['post_type'] ) && 'ecp1_event' == $object->query['post_type'] ) {
		//printf( "<pre>INPUT: %s</pre>", print_r($w, true), print_r($o,  true) );
		// Strip out ILLOGICAL OR statements (thanks to Role Scoper)
		// This is so we don't push conditions to the wrong area
		$query_replace = preg_replace( '/OR 1=2/', '', $query );
		$query_replace = preg_replace( '/\( 1=2 \) OR/', '', $query_replace );
		$query_replace = preg_replace( '/\( 1=1 \) AND/', '', $query_replace );
		$query_replace = preg_replace( '/ 1=1  AND/', '', $query_replace );
		$query_replace = preg_replace( '/AND\s+\(\s*\(\s*\(\s*\(\s*1=1\s*\)\s*\)\s*\)\s*\)/', '', $query_replace );
		//printf( "<pre>ROUND 1: %s</pre>", $query_replace );

		// Get a CSV list of calendar ids this user can edit
		$q = '';
		$cals = _ecp1_current_user_calendars();
		foreach( $cals as $cal )
			$q = '' == $q ? $cal->ID : $q.','.$cal->ID;
		
		// If the user cannot edit calendars then no events either
		if ( '' == $q )
			return preg_replace( '/WHERE/', 'WHERE 1=2 AND ', $query_replace, 1 ); // only once

		// We will need to JOIN the post meta table
		$ecp1_join_meta = " LEFT JOIN $wpdb->postmeta AS ecp1_meta ON ($wpdb->posts.ID = ecp1_meta.post_id AND ecp1_meta.meta_key='ecp1_event_calendar') WHERE ";
		$query_replace = preg_replace( '/WHERE/', $ecp1_join_meta, $query_replace, 1 ); // only first WHERE

		// If the user needs elevation grant it in the query
		if ( ! current_user_can( 'edit_others_' . ECP1_EVENT_CAP . 's' ) ) {
			// At this point in time the query should have something like:
			//   $wpdb->posts.post_author = 'ID'
			// Which needs to be replaced with a string that allows if the
			// user has edit on the events calendar
			$match = '/([^ ]+post_author\s*=\s*[\'0-9]+)/';
			$rep = '( ecp1_meta.meta_value IN (' . $q . ') OR \1 )';
			$query_replace = preg_replace( $match, $rep, $query_replace );
			//printf( "<pre>REPLACEMENT: %s</pre>", $query_replace );
			$query = $query_replace;
		} else {
			// The user may need to be restricted so do that by
			// asserting that only list ones in calendars user
			// has edit capability for
			$match = '/WHERE/';
			$rep = 'WHERE ( ecp1_meta.meta_value IN (' . $q . ') OR ecp1_meta.meta_key IS NULL ) AND ';
			$query_replace = preg_replace( $match, $rep, $query_replace, 1 ); // only first WHERE
			//printf( '<pre>REPLACEMENT: %s</pre>', $query_replace );
			$query = $query_replace;
		}
	}
	return $query;
}

// Now that everything is defined add extra fields to the calendar and event types
include_once( ECP1_DIR . '/includes/data/calendar-fields-admin.php' );
include_once( ECP1_DIR . '/includes/data/event-fields-admin.php' );

// Don't close the php interpreter
/*?>*/
