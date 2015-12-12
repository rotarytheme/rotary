<?php
/**
 * Defines the meta fields for the calendar post type
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// An array of meta field names and default values
$ecp1_calendar_fields = array( 
	'ecp1_description' => array( '', '' ), // value, default
	'ecp1_external_url' => array( '', '' ),
	'ecp1_timezone' => array( '', '_' ),
	'ecp1_first_day' => array( '', -1 ),
	'ecp1_default_view' => array( '', 'none' ),
	'ecp1_local_event_color' => array( '', '#3366CC' ),
	'ecp1_local_event_textcolor' => array( '', '#FFFFFF' ),
	'ecp1_external_cals' => array( array(), array() ),
		// array( 'color'=>'#eeffee', 'text'=>'#333333', 'url'=>'url', 'provider'=>'provider array key' ),
	'ecp1_feature_event_color' => array( '', '#CC6633' ),
	'ecp1_feature_event_textcolor' => array( '', '#FFFFFF' ),

	// meta fields about the data
	'_meta' => array(
		// Have the custom fields been loaded yet?
		'_loaded' => false,
		'_id' => null, // the post id of calendar
	)
);

// Function to parse the custom post fields into the fields above
function _ecp1_parse_calendar_custom( $post_id=-1 ) {
	global $post, $ecp1_calendar_fields;
	
	// Determine if we're using the global post or a parameter post
	// Parameter will take precedence over the global post
	if ( $post_id < 0 )
		$post_id = $post->ID;

	// For efficiency only load meta if not already or need different calendar
	if ( $ecp1_calendar_fields['_meta']['_loaded'] && $post_id == $ecp1_calendar_fields['_meta']['_id'] )
		return;
	
	// Load the basic meta for this calendar post
	$custom = get_post_meta( $post_id, 'ecp1_calendar', true );

	// parse the custom meta fields into the value keys
	if ( is_array( $custom ) ) {
		foreach( array_keys( $ecp1_calendar_fields ) as $key ) {
			if ( '_meta' != $key ) {
				if ( isset( $custom[$key] ) )
					$ecp1_calendar_fields[$key][0] = $custom[$key];
				else
					$ecp1_calendar_fields[$key][0] = $ecp1_calendar_fields[$key][1];
			}
		}

		// Flag as loaded
		$ecp1_calendar_fields['_meta']['_loaded'] = true;
		$ecp1_calendar_fields['_meta']['_id'] = $post_id;
	} elseif ( '' == $custom ) { // it does not exist yet (reset to defaults so empty settings don't display previous calendars details)
		foreach( $ecp1_calendar_fields as $key=>$values ) {
			if ( '_meta' != $key )
				$ecp1_calendar_fields[$key][0] = $ecp1_calendar_fields[$key][1];
		}
		// Flag as loaded
		$ecp1_calendar_fields['_meta']['_loaded'] = true;
		$ecp1_calendar_fields['_meta']['_id'] = $post_id;
	} else { // if the setting exists but is something else
		printf( '<pre>%s</pre>', __( 'Every Calendar +1 plugin found non-array meta fields for this calendar.' ) );
	} 
}

// Function that returns true if value is default
function _ecp1_calendar_meta_is_default( $meta ) {
	global $ecp1_calendar_fields;
	if ( ! isset( $ecp1_calendar_fields[$meta] ) )
		return false; // unknown meta is never at default
	if ( ! $ecp1_calendar_fields['_meta']['_loaded'] )
		return true; // if not loaded treat as defaults
	
	return $ecp1_calendar_fields[$meta][1] == $ecp1_calendar_fields[$meta][0];
}

// Function that gets the meta value the get_default parameter
// controls what to do if settings are not yet loaded. If it is
// false and not loaded NULL will be returned, else the default.
function _ecp1_calendar_meta( $meta, $get_default=true ) {
	global $ecp1_calendar_fields;
	if ( ! isset( $ecp1_calendar_fields[$meta] ) )
		return null; // unknown meta is always NULL

	// if loaded then return value
	if ( $ecp1_calendar_fields['_meta']['_loaded'] )
		return $ecp1_calendar_fields[$meta][0];
	elseif ( $get_default ) // not loaded but want defaults
		return $ecp1_calendar_fields[$meta][1];
	else // not loaded and want NULL if so
		return null;
}

// Returns the ID of the calendar the meta is for
function _ecp1_calendar_meta_id() {
	global $ecp1_calendar_fields;
	if ( ! isset( $ecp1_calendar_fields['_meta'] ) || ! $ecp1_calendar_fields['_meta']['_loaded'] )
		return -1; // not loaded
	return $ecp1_calendar_fields['_meta']['_id'];
}

// Don't close the php interpreter
/*?>*/
