<?php
/**
 * Loads the admin functions for custom post type management
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Load the admin meta forms, fields and data management for the types
include_once( ECP1_DIR . '/includes/custom-post-admin.php' );

// Load the plugin settings form
include_once( ECP1_DIR . '/includes/plugin-settings-page.php' );

// Don't close the php interpreter
/*?>*/
