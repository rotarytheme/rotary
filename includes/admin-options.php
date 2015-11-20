<?php
/**
 * Rotary admin options
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 
 //Required plugins moved to required-plugins/required-plugins.php
 
 //since we require contact form 7 above, lets create a custom template
 add_filter('wpcf7_default_template', 'rotary_wpcf7_default_template', 99, 2);
 function rotary_wpcf7_default_template($template, $prop) {
	if ( 'form' == $prop ) :
		$template =
			'<div id="rotaryemaildialog" class="rotaryemaildialog">
		    	<div class="rotaryemail-sectionheader">
        			<div class="rotaryemail-sectioncontent">
        				<p>
        				<label for="rotaryemailname">Your Name*</label>
        				[text* your-name id:rotaryemailname class:rotaryemailname]
						</p>
		        		<p>
		        		<label for="rotaryemailfrom">Your Email*</label>
		        		[email* your-email id:rotaryemailfrom class:rotaryemailfrom]
		        		</p>
		        		<p>
		        		<label for="rotaryemailsubject">Subject</label>
		        		[text your-subject id:rotaryemailsubject class:rotaryemailsubject]
		        		</p>
		        		<p>
		        		<label for="rotaryemailmessage">Message</label>
		        		[textarea textarea-507 50x10 id:rotaryemailmessage class:rotaryemailmessage]
		        		</p>
		        		<p>
		        		[submit id:rotaryemailsubmit class:rotaryemailsubmit "send"]
		        		</p>
	        		</div>
	        	</div>
        	</div>';
			
	endif;
	return $template;
 } 
 
 function rotary_default_link_cat(){
	 
	 //add custom blogroll category
	 if (!term_exists( 'Rotary', 'link_category')) {
	 	$terms = wp_insert_term( 'Rotary', 'link_category' );
		update_option( 'default_link_category', $terms[0] );
		wp_delete_term( 'Blogroll', 'link_category');
	 }
	//add custom featured category
	if (!term_exists( 'Featured', 'category')) {
		wp_insert_term( 'Featured', 'category' );
	}
	
 }
 add_action( 'after_switch_theme', 'rotary_default_link_cat' ); 
 
 function rotary_notify_theme_activation($oldname, $oldtheme=false) {      
     $current_user = wp_get_current_user();
     $message = 'The theme has been activated by '.$current_user->user_login . ' at email ' . $current_user->user_email . ' at site '. site_url();
     
	 wp_mail( 'rotary@paulosborn.com', 'Theme Activate', $message ); 
 }
 add_action( 'after_switch_theme', 'rotary_notify_theme_activation', 10 ,  2);
 function rotary_set_default_pages(){
	 wp_delete_post(1); //delete sample post
	 wp_delete_comment(1); //delete sample comment
	 wp_delete_post(2); //delete sample page
	 if (!get_page_by_title( 'Member Information' )) {
		 $args = array(
			'post_name' => 'member-information',
			'post_title' => 'Member Information',
			'post_type' => 'page',
			'post_status'    => 'publish',
		);
		wp_insert_post($args);
	 }
	 if (!get_page_by_title( 'About' )) {
		 $args = array(
			'post_name' => 'about',
			'post_title' => 'About',
			'post_type' => 'page',
			'post_status'    => 'publish',
		);
		wp_insert_post($args);
	 }
	 if (!get_page_by_title( 'Home' )) {
		 $args = array(
			'post_name' => 'home',
			'post_title' => 'Home',
			'post_type' => 'page',
			'post_status'    => 'publish',
		);
		wp_insert_post($args);
	 }
	 if (!get_page_by_title( 'Posts' )) {
		 $args = array(
			'post_name' => 'posts',
			'post_title' => 'Posts',
			'post_type' => 'page',
			'post_status'    => 'publish',
		);
		wp_insert_post($args);
	 }
	
 }
 add_action( 'after_switch_theme', 'rotary_set_default_pages' ); 
 
 // Custom WordPress Login Logo
function rotary_login_css() {
	wp_enqueue_style( 'login_css', get_template_directory_uri() . '/css/login.css' );
}
add_action('login_head', 'rotary_login_css');

//add open sans condensed
function rotary_admin_enqueue_scripts() {
	global $wp_styles; // call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way
    $protocol = is_ssl() ? 'https' : 'http';
	$query_args = array(
		'family' => 'Open+Sans+Condensed:300,700,300italic');
	wp_enqueue_style( 'opensanscondensed-css', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null 	);	
}
add_action('admin_enqueue_scripts', 'rotary_admin_enqueue_scripts');
// Custom WordPress Footer d
function rotary_custom_footer_admin () {
	echo '&copy;'. date("Y").' - Rotary WordPress Theme';
}
add_filter('admin_footer_text', 'rotary_custom_footer_admin');
// Custom WordPress Admin Color Scheme
function rotary_admin_css() {
	wp_enqueue_style( 'admin_css', get_template_directory_uri() . '/css/admin.css' );
}
add_action('admin_print_styles', 'rotary_admin_css' );
/*always show kitchen sink*/

function rotary_unhide_kitchensink( $args ) {
$args['wordpress_adv_hidden'] = false;
return $args;
}
add_filter( 'tiny_mce_before_init', 'rotary_unhide_kitchensink' );	

add_action('wp_insert_post_data', 'rotary_check_postdata', 99);
function rotary_check_postdata($data) {
	global $pagenow; 
	if ($pagenow == 'post.php'	) {

		if ( 'rotary-slides' == $data['post_type']  && 'publish' ==$data['post_status'] ) {	
			if (! has_post_thumbnail()) {
				$data['post_status'] = 'draft';
    			add_filter('redirect_post_location', 'rotary_redirect_post_location_filter', 99);
			}
		}
		if ( 'rotary_speakers' == $data['post_type']  && 'publish' ==$data['post_status']) {
			$title = trim($data['post_title']);
			if ($title == null || $title == '' || $title == '(no title)') {
     				$data['post_status'] = 'draft';
    				add_filter('redirect_post_location', 'rotary_redirect_post_location_filter', 99);
    		}		
		}
	}
	
	return $data;
}
function rotary_redirect_post_location_filter($location){
  remove_filter('redirect_post_location', __FUNCTION__, 99);
  $location = add_query_arg('message', 99, $location);
  return $location;
}
add_filter('post_updated_messages', 'rotary_post_updated_messages_filter');
function rotary_post_updated_messages_filter($messages) {

	global $post;
	if ( 'rotary_speakers' == get_post_type( $post->ID )) {
		$messages['post'][99] = 'Title is missing';
	}
	else {
		$messages['post'][99] = 'Featured image is missing';
	}
	
	return $messages;
}
add_action ('after_setup_theme', 'rotary_add_custom_user_roles');
function rotary_add_custom_user_roles() {
	$userRole = get_role( 'Contributor' ); 
	add_role( 'Scribe', 'Scribe', $userRole['capabilities'] );
}
//remove program coordinator from the side bar
add_action ('admin_menu' , 'rotary_remove_progam_coordinator_meta');
function rotary_remove_progam_coordinator_meta() {
	remove_meta_box( 'rotary_program_introducer_catdiv', 'rotary_speakers', 'side' );
}

function rotary_acf_update_project_date($value, $post_id, $field) {
	if ( '' == trim( $value ) ) :
		$value = date('Ymd');
	endif;
	return $value;
}
add_filter('acf/update_value/key=field_53e29fcd38551', 'rotary_acf_update_project_date', 10, 3);