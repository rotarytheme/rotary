<?php
/**
 * Every Calendar +1 Query List
 *
 * Write once, use many times.
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Shortcut function to return the query text: template_query
function _ecp1_tq( $name ) {
	global $ECP1_QUERY;
	if ( ! array_key_exists( $name, $ECP1_QUERY ) )
		return null;
	global $$ECP1_QUERY[$name];
	return $$ECP1_QUERY[$name];
}

// Make a lookup array of query variables
$ECP1_QUERY = array(
	'EVENTS'           => '_ecp1_query_events',
	'FEATURED_EVENTS'  => '_ecp1_query_featured_events',
);

// Define a query to get event post ids
$_ecp1_query_events = <<<ENDOFQUERY
SELECT  p.ID
FROM    $wpdb->posts p
	INNER JOIN $wpdb->postmeta c ON c.post_id=p.ID AND (c.meta_key='ecp1_event_calendar' OR c.meta_key='ecp1_extra_calendar')
	INNER JOIN $wpdb->postmeta s ON s.post_id=p.ID AND s.meta_key='ecp1_event_start'
	INNER JOIN $wpdb->postmeta e ON e.post_id=p.ID AND e.meta_key='ecp1_event_end'
WHERE   p.post_status='publish' AND
	c.meta_value=%d AND
	s.meta_value<=%d AND
	e.meta_value>=%d
ORDER BY
	s.meta_value, p.post_name ASC;
ENDOFQUERY;

// Define a query to get feature events
$_ecp1_query_featured_events = <<<ENDOFFEATURE
SELECT  p.ID
FROM    $wpdb->posts p
	INNER JOIN $wpdb->postmeta f ON f.post_id=p.ID AND f.meta_key='ecp1_event_is_featured'
	INNER JOIN $wpdb->postmeta s ON s.post_id=p.ID AND s.meta_key='ecp1_event_start'
	INNER JOIN $wpdb->postmeta e ON e.post_id=p.ID AND e.meta_key='ecp1_event_end'
WHERE   p.post_status='publish' AND
	f.meta_value='Y' AND
	s.meta_value<=%d AND
	e.meta_value>=%d
ORDER BY
	s.meta_value, p.post_name ASC;
ENDOFFEATURE;

// Don't close the php interpreter
/*?>*/
