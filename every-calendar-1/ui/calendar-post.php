<?php
/**
 * Create a filter to render the calendar in a post
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Load the calendar post type fields so we can get the meta
require_once( ECP1_DIR . '/includes/data/calendar-fields.php' );

// Add a filter that checks if this is a calendar and then re-configures the content if so
add_filter( 'the_content', 'ecp1_post_as_calendar' );

// Renders a calendar into the post
function ecp1_post_as_calendar( $content ) {
	global $post, $ecp1_calendar_fields;
	
	// Only make the changes if this is a single post display of an ECP1 Calendar
	if ( is_single() && 'ecp1_calendar' == $post->post_type ) {
		_ecp1_parse_calendar_custom(); // Load the calendar object meta in $ecp1_calendar_fields
		$fields = array_merge( $ecp1_calendar_fields, array( 'slug'=>$post->post_name ) );
		$content = ecp1_render_calendar( $fields ); // Call the render function
	}
	
	// Return the content updated or not
	return $content;
}

// Don't close the php interpreter
/*?>*/
