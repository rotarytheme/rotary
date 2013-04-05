<?php
/**
 * Rotary functions and definitions
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

// Set path to theme specific functions


$includes_path = TEMPLATEPATH . '/includes/';
// Theme specific functionality

require_once ($includes_path . 'theme-options.php'); 		// Options panel settings and custom settings

require_once ($includes_path . 'theme-functions.php'); 		// Custom theme functions

require_once ($includes_path . 'theme-js.php');				// Load javascript in wp_head

require_once ($includes_path . 'sidebar-init.php');			// Initialize widgetized areas

require_once ($includes_path . 'theme-widgets.php');		// Theme widgets

require_once ($includes_path . 'admin-options.php');		// Theme widgets

/*you can put custom code below this line*/

//always show author
add_action('admin_init', 'rotary_set_user_metaboxes');
function rotary_set_user_metaboxes($user_id=NULL) {
	$meta_key['hidden'] = 'metaboxhidden_post';
	if ( ! $user_id) {
        $user_id = get_current_user_id(); 
	}
	 
    //if ( ! get_user_meta( $user_id, $meta_key['hidden'], true) ) {
        $meta_value = array('postcustom','trackbacksdiv','commentstatusdiv','commentsdiv','slugdiv','revisionsdiv');
        update_user_meta( $user_id, $meta_key['hidden'], $meta_value );
   // }


}