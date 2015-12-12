<?php
/**
 * Registers hooks for the installer and plugin activation mechanisms
 * to make sure we clean up after ourselves if someone doesn't like 
 * the plugin etc...
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Create the rewrite rules needed for plugin
function ecp1_add_rewrite_rules() {
	// Rewrite rules as target => destination
	$rewrites = array(
		'ecp1/([a-zA-Z0-9_\-]+)/_xtest.txt$' => 'index.php?ecp1tpl=test&ecp1_cal=$matches[1]', // Testing script
		'ecp1/([a-zA-Z0-9_\-]+)/events.json$' => 'index.php?ecp1tpl=event-json&ecp1_cal=$matches[1]', // Events as JSON
		'ecp1/([a-zA-Z0-9_\-]+)/events.ics$' => 'index.php?ecp1tpl=ical-feed&ecp1_cal=$matches[1]', // Events iCalendar Feed
		'ecp1/([a-zA-Z0-9_\-]+)/events.rss$' => 'index.php?ecp1tpl=rss-feed&ecp1_cal=$matches[1]', // Events RSS Feed
		'ecp1proxy/([a-zA-Z0-9_\-]+)/([a-z]+)/events.json$' => 
			'index.php?ecp1tpl=proxy-json&ecp1_cal=$matches[1]&ecp1_proxy=$matches[2]', // Proxied calendar events as JSON
		#'event/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/$' => // Events page 
		#	'index.php?ees_year=$matches[1]&ees_month=$matches[2]&ees_day=$matches[3]&ecp1_event=$matches[4]',
	);

	// Loop over the rules and add them to the top then
	// need to go on the top otherwise /pagename/ is used
	foreach( $rewrites as $from=>$to )
		add_rewrite_rule( $from, $to, 'top' );
}

// Function that activates plugin rewrite rules and flushes them to cache
function ecp1_activate_rewrite() {
	ecp1_register_types();     # register the custom cal/event types
	ecp1_add_rewrite_rules();  # setup custom template urls (e.g. json events)
	flush_rewrite_rules();     # flush the rules to the database and .htaccess
}

// Function that deactivates plugin rewrite rules and flushes them to cache
function ecp1_deactivate_rewrite() {
	// They aren't added so flushing will flush all but ours
	flush_rewrite_rules();
}


// Function that creates the event repeat and exception cache table
// the cache allows for "exceptions" to be made for particular repeats
// by over-riding the event details.
function ecp1_add_cache_tables() {
	global $wpdb;
	$tablename = $wpdb->prefix . "ecp1_cache";
	$currentversion = get_option( "ecp1_db_version", -1 );

	// If the the database schema is out-of-date update it
	if ( ECP1_DB_VERSION != $currentversion ) {
		
		// dbDelta requires specific format so read before changing
		// http://codex.wordpress.org/Creating_Tables_with_Plugins
		$sql = "CREATE TABLE $tablename (
			cache_id bigint(20) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL DEFAULT 0,
			start date NULL DEFAULT NULL,
			changes longtext NULL DEFAULT NULL,
			is_exception bool NOT NULL DEFAULT 0,
			PRIMARY KEY  cache_id (cache_id),
			KEY  post_id (post_id),
			KEY  start (start),
			KEY  exception (is_exception)
		);";
		
		// Load the dbDelta functions
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		// Store the current version of the database as an option
		update_option( "ecp1_db_version", ECP1_DB_VERSION );

	}
}

// Don't close the php interpreter
/*?>*/
