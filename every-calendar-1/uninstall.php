<?php
/**
 * Every Calendar +1 Plugin
 *
 * Uninstall Script:
 *  1) Removes any cached external calendars (for ical export)
 */


// Make sure WordPress is uninstalling the plugin and then do the clean up
if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	// 1 - Remove cached external calendars from calendar meta
	$allcals = get_posts( 'numberposts=-1&post_type=ecp1_calendar&post_status=any' );
	foreach( $allcals as $cal ) {
		$custom = get_post_custom( $cal->ID );
		foreach( $custom as $key=>$value ) {
			if ( strpos( $key, 'ecp1_cache' ) === 0 )
				delete_post_meta( $cal->ID, $key );
		}
	}

}

// Don't close the php interpreter
/*?>*/
