<?php
/*
Plugin Name: Every Calendar +1 for WordPress
Plugin URI: http://andrewbevitt.com/code/everycalplus1
Description: A WordPress Calendar plugin with repeating events, widgets and maps built in.
Version: 2.1.1
Author: Andrew Bevitt
Author URI: http://andrewbevitt.com
License: GPL2

Copyright 2011  Andrew Bevitt  (email: mycode@andrewbevitt.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// For development purposes: set this to true and all errors will be printed to screen
// Note this will turn on debugging for all of WordPress only use for testing.
define( 'ECP1_DEBUG', false );

// Allow plugin files to load by defining scope of plugin
define( 'ECP1_PLUGIN', true );

// The plugin directory of Every Calendar +1
define( 'ECP1_DIR', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

// The version of the repeats and exceptions cache tables
define( 'ECP1_DB_VERSION', 1.0 ); // change to plugin version

// The tag that the plugins custom template renders hook on
define( 'ECP1_TEMPLATE_TAG', 'ecp1tpl' );
define( 'ECP1_TEMPLATE_TEST_ARG', '_ecp1test' ); // for template renderer debug

// Some of the functions require PHP 5.3 but can be backported to 5.2
if ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 )
	define( 'ECP1_PHP5', 3 );
else
	define( 'ECP1_PHP5', 2 );

// Initialise the plugin
// Do this first so the rewrite rule flush includes custom types
require_once( ECP1_DIR . '/init-plugin.php' );

// Includes functions for making changes on activation/install/uninstall/deactivation
require_once( ECP1_DIR . '/install-activate.php' );

// Register a function on the activation hook to setup the plugin
register_activation_hook( __FILE__, 'ecp1_plugin_activation' );
function ecp1_plugin_activation() {
	ecp1_activate_rewrite();
	ecp1_add_cache_tables();
}
 
// Register a function on the deactivation hook for locking down data
register_deactivation_hook( __FILE__, 'ecp1_plugin_deactivation' );
function ecp1_plugin_deactivation() {
    ecp1_deactivate_rewrite();
}

// If displaying the administration dashboard load admin UI
// otherwise load the client UI or a custom template
if ( is_admin() )
	include_once( ECP1_DIR . '/includes/init-admin.php' );
else
	include_once( ECP1_DIR . '/ui/init-client-ui.php' );

// Don't close the php interpreter
/*?>*/
