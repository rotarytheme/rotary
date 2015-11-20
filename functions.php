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

$includes_path = TEMPLATEPATH . '/includes/';
$required_path = TEMPLATEPATH . '/required-plugins/';
$shortcodes_path = TEMPLATEPATH . '/shortcodes/';
$ajax_path = TEMPLATEPATH . '/includes/ajax/';
// Theme specific functionality

//
function special_js($hook) {
    wp_enqueue_script( 'special_js',  get_template_directory_uri(). '/includes/js/special_js.js' );
}
add_action( 'admin_enqueue_scripts', 'special_js' );

require_once ($includes_path . 'theme-options.php'); 		// Options panel settings and custom settings

require_once ($includes_path . 'theme-functions.php'); 		// Custom theme functions



require_once ($includes_path . 'committee-project-functions.php'); 		// Custom functions for committees and projects

require_once ($ajax_path . 'ajax-announcements.php'); 		// Ajax functions for announcements

require_once ($ajax_path . 'ajax-projects.php'); 		// Ajax functions for announcements



require_once ($includes_path . 'theme-js.php');				// Load javascript in wp_head

require_once ($includes_path . 'sidebar-init.php');			// Initialize widgetized areas

require_once ($includes_path . 'theme-widgets.php');		// Theme widgets

require_once ($includes_path . 'admin-options.php');		// admin options

require_once ($required_path . 'required-plugins.php');		// required plugins

require_once ($includes_path . 'custom-posts.php');			// custom posts

require_once ($includes_path . 'custom-capabilities.php');		// custom capabilities

require_once ($includes_path . 'rotarythemeupdater.php');	// theme updater

require_once ('nm-mailchimp/admin.php');   // N-Media Mailchimp

require_once('wp-advanced-search/wpas.php');  //advnced search form



include_once('advanced-custom-fields/acf.php' );

include_once('acf-repeater/acf-repeater.php');

include_once( $includes_path . 'speaker-fields.php');

include_once( $includes_path . 'project-fields.php');

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

