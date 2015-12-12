<?php
/**
 * Registers a shortcode for a full sized (i.e. whole page) calendar
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Load the calendar post type fields so we can get the meta
require_once( ECP1_DIR . '/includes/data/calendar-fields.php' );

// Register the shortcode type and callback
add_shortcode( 'largecalendar', 'ecp1_full_size_calendar' );

// [ largecalendar name="calendar name" ]
function ecp1_full_size_calendar( $atts ) {
	global $ecp1_calendar_fields;
	
	// Extract the attributes or assign default values
	extract( shortcode_atts( array(
		'name' => null, # checked below must not be null
	), $atts ) );
	
	// Make sure a name has been provided
	if ( is_null( $name ) )
		return sprintf( '<span class="ecp1_error">%s</span>', __( 'Unknown calendar: could not display.' ) );
	
	// Lookup the Post ID for the calendar with that name
	// Note: Pages are just Posts with post_type=page so the built in function works
	$cal_post = get_page_by_title( $name, OBJECT, 'ecp1_calendar' );
	
	// We don't want calendar meta for the global page/post but for the one given by title
	_ecp1_parse_calendar_custom( $cal_post->ID ); // Load the calendar meta into global $ecp1_calendar_fields
	$fields = array_merge( $ecp1_calendar_fields, array( 'slug'=>$cal_post->post_name ) );
	return ecp1_render_calendar( $fields ); // Pass the calendar to the render function
}

// Don't close the php interpreter
/*?>*/
