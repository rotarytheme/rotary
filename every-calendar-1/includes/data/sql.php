<?php
/**
 * Class of static queries for use in the plugin
 * Query parameters are described in order for $wpdb->prepare
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );


class EveryCal_Query
{

/**
 * Returns the query string requested.
 *
 * This is necessary because HEREDOCS can't allow $wpdb-> syntax; the new NOWDOCS do
 * but the plugin tries to maintain PHP5.2 compatibility so we can't use them.
 *
 * @param $qs The query string you want to use
 * @return The SQL for the query string requested
 */
public static function Q( $qs ) {
	global $wpdb; $prefix = $wpdb->prefix;
	return str_replace( '{{prefix}}', $prefix, self::$$qs );
}

/**
 * Two columns: Event Post ID | Repeats (Y|NULL)
 * Returns a list of events for the given calendar (not including featured events)
 * that are either repeating events or scheduled in the given timestamp range.
 *
 * Parameters:
 *  Calendar ID
 *  Start DateTime
 *  End DateTime
 */
private static $CALENDAR_EVENTS = "
SELECT  p.ID, r.meta_value
FROM    {{prefix}}posts p
		INNER JOIN {{prefix}}postmeta c ON c.post_id=p.ID AND (c.meta_key='ecp1_event_calendar' OR c.meta_key='ecp1_extra_calendar')
	    INNER JOIN {{prefix}}postmeta s ON s.post_id=p.ID AND s.meta_key='ecp1_event_start'
		INNER JOIN {{prefix}}postmeta e ON e.post_id=p.ID AND e.meta_key='ecp1_event_end'
		LEFT OUTER JOIN {{prefix}}postmeta r ON r.post_id=p.ID AND r.meta_key='ecp1_event_repeats'
WHERE   p.post_status='publish' AND
		c.meta_value=%d AND
		(
			( e.meta_value>=%d AND s.meta_value<=%d ) OR
			r.meta_value='Y'
		)
ORDER BY
		s.meta_value, p.post_name ASC;
";


/**
 * Two columns: Event Post ID | Repeats (Y|NULL)
 * Returns a list of featured events that are either repeating events or in the given time range.
 *
 * Parameters:
 *  Calendar ID
 *  Start DateTime
 *  End DateTime
 */
private static $FEATURED_EVENTS = "
SELECT  p.ID, r.meta_value
FROM    {{prefix}}posts p
		INNER JOIN {{prefix}}postmeta f ON f.post_id=p.ID AND f.meta_key='ecp1_event_is_featured'
		INNER JOIN {{prefix}}postmeta s ON s.post_id=p.ID AND s.meta_key='ecp1_event_start'
		INNER JOIN {{prefix}}postmeta e ON e.post_id=p.ID AND e.meta_key='ecp1_event_end'
		LEFT OUTER JOIN {{prefix}}postmeta r ON r.post_id=p.ID AND r.meta_key='ecp1_event_repeats'
WHERE   p.post_status='publish' AND
		f.meta_value='Y' AND
		(
			( e.meta_value>=%d AND s.meta_value<=%d ) OR
			r.meta_value='Y'
		)
ORDER BY
		s.meta_value, p.post_name ASC;
";

/**
 * One column: Event Post ID
 * Get all events that repeat on the given calendar.
 *
 * Parameters:
 *  Calendar ID
 */
private static $REPEATS_ON_CALENDAR = "
SELECT	DISTINCT p.ID
FROM	{{prefix}}posts p
		INNER JOIN {{prefix}}postmeta m ON m.post_id=p.ID AND m.meta_key = 'ecp1_event_calendar'
		INNER JOIN {{prefix}}postmeta j ON j.post_id=p.ID AND j.meta_key = 'ecp1_event_repeats'
WHERE	p.post_status = 'publish' AND j.meta_value = 'Y' AND m.meta_value = %s;
";

/**
 * Two columns: Calendar Post ID | Event Post ID
 * Get all events (and their source calendar) which are repeating events and
 * have the given calendar as an "extra" calendar for the event to appear on.
 *
 * Parameters:
 *  Extra Calendar ID
 */
private static $REPEATS_ON_EXTRA = "
SELECT	DISTINCT k.meta_value, p.ID
FROM	{{prefix}}posts p
		INNER JOIN {{prefix}}postmeta m ON m.post_id=p.ID AND m.meta_key = 'ecp1_extra_calendar'
		INNER JOIN {{prefix}}postmeta j ON j.post_id=p.ID AND j.meta_key = 'ecp1_event_repeats'
		INNER JOIN {{prefix}}postmeta k ON k.post_id=p.ID AND k.meta_key = 'ecp1_event_calendar'
WHERE	p.post_status = 'publish' AND j.meta_value = 'Y' AND m.meta_value = %s;
";

/**
 * Two columns: Calendar Post ID | Event Post ID
 * Get all featured events that repeat (and their source calendar).
 *
 * Parameters:
 *  NONE
 */
private static $FEATURED_REPEATS = "
SELECT	DISTINCT k.meta_value, p.ID
FROM	{{prefix}}posts p
		INNER JOIN {{prefix}}postmeta m ON m.post_id=p.ID AND m.meta_key = 'ecp1_event_is_featured'
		INNER JOIN {{prefix}}postmeta j ON j.post_id=p.ID AND j.meta_key = 'ecp1_event_repeats'
		INNER JOIN {{prefix}}postmeta k ON k.post_id=p.ID AND k.meta_key = 'ecp1_event_calendar'
WHERE	p.post_status = 'publish' AND j.meta_value = 'Y';
";

}

// Don't close the php interpreter
/*?>*/
