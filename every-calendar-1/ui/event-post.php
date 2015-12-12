<?php
/**
 * Create a filter to render the event in a post
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Load the event post type fields so we can get the meta
require_once( ECP1_DIR . '/includes/data/event-fields.php' );

// Add a filter that checks if this is an event and then re-configures the content if so
add_filter( 'the_content', 'ecp1_post_as_event' );

// Renders an event into the post
function ecp1_post_as_event( $content ) {
	global $post, $ecp1_event_fields;
	
	// Only make the changes if this is a single post display of an ECP1 Event
	if ( is_single() && 'ecp1_event' == $post->post_type ) {
		_ecp1_parse_event_custom(); // load the event meta fields into $ecp1_event_fields
		$content = ecp1_render_event( $ecp1_event_fields ); // Call the render function
	}
	
	// Return the content updated or not
	return $content;
}

// Don't close the php interpreter
/*?>*/
