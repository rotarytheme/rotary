<?php
/**
 * Loads the custom post types, functions and any misc init
 * hooks that the custom templates require. After this file
 * is loaded you can work with the custom types.
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Turn on debugging if requested
if ( ECP1_DEBUG ) {
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );

	if ( is_admin() )
		add_action( 'wp', 'ecp1_debug_query' );
}

function ecp1_debug_query() {
?>
	<div style="border:2px solid red;margin:1em;width:95%;height:200px;overflow:scroll;position:absolute;bottom:0;left:0;z-index:999;background:#fff;">
		<pre>
<?php global $wp_query; print_r( $wp_query ); ?>
		</pre>
	</div>
<?php
}

// Define the Custom Post Type
require_once( ECP1_DIR . '/includes/custom-post-type.php' );

// Load the helper functions
require_once( ECP1_DIR . '/functions.php' );

// Add a hook on init to register the custom rewrite rules
// so they don't get dropped with other permalink updates
// NOTE: this is forward declared (see install-activate.php)
add_action( 'init', 'ecp1_add_rewrite_rules' );

// Add a hook on init to register ECP1_TEMPLATE_TAG and the
// other variables that the custom template scripts need.
add_action( 'init', 'ecp1_register_query_tags' );
function ecp1_register_query_tags() {
	// Tags that should be added to $wp_query->query_vars
	// note that UNLESS you're rewrite rule includes these
	// tags then the regex is NOT validated.
	$tags = array(
		ECP1_TEMPLATE_TAG => '([a-z\-]+)', # which template to use
		ECP1_TEMPLATE_TEST_ARG => '([^&]+)', # for template devs to enable debuging
		'ecp1_start'      => '([0-9])+',   # unix timestamp for event start lookup
		'ecp1_end'        => '([0-9]+)',   #  and the end lookup (neither validated)
		'ecp1_cal'        => '([a-zA-Z0-9_\-]+)',  # the ecp1_calendar slug to lookup in json/ical
		'ecp1_proxy'      => '([a-z]+)', # the array key for external provider that is proxied
		'ecp1_repeat'     => '([0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2})', # date repeat starts

		'ecp1_event'      => '([^/]+)', # event slug to lookup
		'ees_year'        => '([0-9]{4})', # event start year
		'ees_month'       => '([0-9]{1,2})', # event start month
		'ees_day'         => '([0-9]{1,2})', # event start day
	);

	// Loop over the tags and add them to the query vars
	foreach( $tags as $name=>$regex )
		add_rewrite_tag( "%$name%", $regex );
}

// Add a hook to create the event repeat and exception cache
// when the plugin is loaded by automatic upgrade because the
// activation hook doesn't run in those cases (apparently)
// NOTE: this is forward declared (see install-activate.php)
add_action( 'plugins_loaded', 'ecp1_add_cache_tables' );

// Load the plugin widget registry which will load/register widgets
require( ECP1_DIR . '/widgets/register.php' );

// Don't close the php interpreter
/*?>*/
