<?php
/**
 * Adds hooks and defines functions to the template_redirect action
 * so that the plugin can use custom templates for its rewrite rules.
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Add a hook that parses the custom parameter defined in ECP1_TEMPLATE_TAG
// and calls an appropriate ui/template/ script for that tag.
add_action( 'template_redirect', 'ecp1_rewrite_custom_template', 5 );
function ecp1_rewrite_custom_template() {
	global $wp_query; // WordPress object of this request

	// Only do this if the ECP1_TEMPLATE_TAG query parameter is given
	if ( isset( $wp_query->query_vars[ECP1_TEMPLATE_TAG] ) ) {

		$template = ECP1_DIR . '/ui/templates/' . $wp_query->query_vars[ECP1_TEMPLATE_TAG] . '.php';

		if ( ! is_readable( $template ) ) {
			$http_msg = __( 'Invalid Template' );
			header( sprintf( 'HTTP/1.1 404 %s',$http_msg ), 1 );
			header( sprintf( 'Status: 404 %s', $http_msg ), 1 );
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  dir="ltr" lang="en-US">
<head><title><?php _e( 'Every Calendar +1 Plugin Invalid Template' ); ?></title></head>
<body>
	<h1><?php echo $http_msg; ?></h1>
	<div><?php _e( 'You specified an invalid template tag for Every Calendar +1: ' ); 
			echo htmlspecialchars( $wp_query->query_vars[ECP1_TEMPLATE_TAG] ); ?></div>
</body>
</html>
<?php
		}

		// Otherwise the template exists: include it and then exit
		require( $template );
		exit;

	}
}

// Don't close the php interpreter
/*?>*/
