<?php
/**
 * Rotary functions and definitions
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 
// Set path to theme specific functions
 define( 'ACF_LITE' , true );

define( 'ROTARY_THEME_INCLUDES_PATH', TEMPLATEPATH . '/includes/');
define( 'ROTARY_THEME_CLASSES_PATH', TEMPLATEPATH . '/classes/');
define( 'ROTARY_THEME_AJAX_PATH', TEMPLATEPATH . '/includes/ajax/');
define( 'ROTARY_THEME_SHORTCODES_PATH', TEMPLATEPATH . '/shortcodes/');
define( 'ROTARY_THEME_LANGUAGES_PATH', TEMPLATEPATH . '/languages/');
define( 'ROTARY_THEME_CSS_PATH', TEMPLATEPATH . '/css/');
define( 'ROTARY_THEME_CSV_PATH', TEMPLATEPATH . '/import-users-from-csv/');

define( 'ROTARY_THEME_JAVASCRIPT_URL', get_template_directory_uri() .  '/includes/js/');
define( 'ROTARY_THEME_CSS_URL', get_template_directory_uri() . '/css/');
define( 'ROTARY_THEME_CSV_URL', get_template_directory_uri() . '/import-users-from-csv/');

add_action('after_setup_theme', 'my_theme_setup');
function my_theme_setup(){
	load_theme_textdomain('Rotary', TEMPLATEPATH . '/languages');
}

get_locale();

//delete the rotary membership folder if it exists
function rotary_delete_rotarymembership_folder( $dir, $deleteRootToo ) {
	if( !$dh = @opendir( $dir )){
		return;
	}
	while (false !== ($obj = readdir($dh)))	{
		if($obj == '.' || $obj == '..')	{
			continue;
		}
		if (!@unlink($dir . '/' . $obj)) {
			rotary_delete_rotarymembership_folder($dir.'/'.$obj, true);
		}
	}

	closedir($dh);

	if ($deleteRootToo)	{
		@rmdir($dir);
	}
	return;
}

$dir = ABSPATH . '/wp-content/plugins/rotarymembership';
$deleteRootToo = true;
rotary_delete_rotarymembership_folder( $dir, $deleteRootToo );
$dir = ABSPATH . '/wp-content/plugins/rotarymembership-master';
rotary_delete_rotarymembership_folder( $dir, $deleteRootToo );



/*
 * EMBEDDED PLUGINS AND EXTENSIONS
*/
require_once ( 'wp-advanced-search/wpas.php' );  //advnced search form

include_once ( 'advanced-custom-fields/acf.php' );

require_once ( 'nm-mailchimp/admin.php' );   // N-Media Mailchimp

include_once ( 'acf-repeater/acf-repeater.php' );

include_once ( 'import-users-from-csv/import-users-from-csv.php' );

require_once ( 'required-plugins/required-plugins.php' );		// required plugins

require_once ( ROTARY_THEME_INCLUDES_PATH . 'theme-js.php');				// Load javascript in wp_head

require_once ( ROTARY_THEME_INCLUDES_PATH . 'sidebar-init.php');			// Initialize widgetized areas

require_once ( ROTARY_THEME_INCLUDES_PATH . 'theme-widgets.php');		// Theme widgets

require_once ( ROTARY_THEME_INCLUDES_PATH . 'admin-options.php');		// admin options

require_once ( ROTARY_THEME_INCLUDES_PATH . 'custom-capabilities.php');		// custom capabilities

require_once ( ROTARY_THEME_INCLUDES_PATH . 'rotarythemeupdater.php');	// theme updater

require_once ( ROTARY_THEME_INCLUDES_PATH . 'sponsors.php');	// sponsors - custom plugin hacked for the theme

require_once ( ROTARY_THEME_INCLUDES_PATH . 'simple-page-ordering.php');	// simple page odering plugin hacked for the theme

require_once ( ROTARY_THEME_INCLUDES_PATH . 'gravitylist.php');	// to enable scripts on CST's for the gravitylist plugin


/*
 *  CALENDAR 
 */
require_once ( ROTARY_THEME_INCLUDES_PATH . 'functions-calendar.php'); // calendar functions file in included

/*
 * MEMBERS
 */
require_once( ROTARY_THEME_CLASSES_PATH . 'rotaryprofiles.php');

require_once( ROTARY_THEME_CLASSES_PATH . 'rotarymemberdata.php');


// ADMIN
function special_js($hook) {
    wp_enqueue_script( 'special_js',  get_template_directory_uri(). '/includes/js/special_js.js' );
}
add_action( 'admin_enqueue_scripts', 'special_js' );

require_once (ROTARY_THEME_INCLUDES_PATH . 'theme-options.php'); 		// Options panel settings and custom settings

require_once (ROTARY_THEME_INCLUDES_PATH . 'theme-functions.php'); 		// Custom theme functions

/*
 *  CUSTOM POST TYPES
 */

require_once( ROTARY_THEME_CLASSES_PATH . 'custom-post-types.php');

require_once (ROTARY_THEME_INCLUDES_PATH . 'committee-project-functions.php'); 		// Custom functions for committees and projects

include_once( ROTARY_THEME_INCLUDES_PATH . 'speaker-fields.php'); 

include_once( ROTARY_THEME_INCLUDES_PATH . 'committee-fields.php');

include_once( ROTARY_THEME_INCLUDES_PATH . 'project-fields.php');

require_once ( ROTARY_THEME_AJAX_PATH . 'ajax-projects.php'); 		// Ajax functions for projects



/*
 * ANNOUNCEMENTS
 */
require_once ( ROTARY_THEME_AJAX_PATH . 'ajax-announcements.php'); 		// Ajax functions for announcements



/*
 * SHORTCODES
 *
 */


/*
 * EMBEDDED PLUGINS AND EXTENSIONS
 */
require_once ( 'wp-advanced-search/wpas.php' );  //advnced search form

include_once ( 'advanced-custom-fields/acf.php' );

require_once ( 'nm-mailchimp/admin.php' );   // N-Media Mailchimp

include_once ( 'acf-repeater/acf-repeater.php' );

require_once ( 'required-plugins/required-plugins.php' );		// required plugins

//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/*you can put custom code below this line*/



/*---------------------------------------------------------------------------*/
// Custom code which copies all settings from old row and adds to the new row
$theme = wp_get_theme();
$theme_key = $theme['Stylesheet'];
$all_options = wp_load_alloptions();
$op_value = false;
foreach( $all_options as $name => $value ) {
    if (stristr($name, 'theme_mods_rotarytheme-rotary')) {
        if ($name !== 'theme_mods_'.$theme['Stylesheet']) {
            $op_value = get_option($name);
            delete_option($name);
        }
        elseif($op_value){
            update_option($name, $op_value);
        }
    }
}
/*---------------------------------------------------------------------------*/

