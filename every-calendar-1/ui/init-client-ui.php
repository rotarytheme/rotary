<?php
/**
 * Loads the UI functions for custom post type display
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// For the custom URLs added to the rewrite we want to overwrite the template
include_once( ECP1_DIR . '/ui/check-custom-template.php' );

// Make sure all the client side libraries get enqueued
include_once( ECP1_DIR . '/ui/client-enqueueing.php' );

// If the event/calendar is requested directly render it
include_once( ECP1_DIR . '/ui/client-render.php' );
include_once( ECP1_DIR . '/ui/calendar-post.php' );
include_once( ECP1_DIR . '/ui/event-post.php' );

// Register the shortcodes
include_once( ECP1_DIR . '/ui/shortcode/full-size-calendar.php' );
include_once( ECP1_DIR . '/ui/shortcode/event-list-calendar.php' );

// Don't close the php interpreter
/*?>*/
