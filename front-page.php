<?php
/**
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

get_header(); 

// if there is a different page set for logged in users, and we are logged in,
// then use that, otherwise continue 
if( is_user_logged_in() && get_option( 'rotary_member_page_on_front' ) ) {
	//$post_id = get_option( 'rotary_member_page_on_front' );
	//$args = array( 'page_id' => $post_id );
	//$wp_query = new WP_Query( $args );
	get_template_part( 'loop', 'home-members' );
} else {
	get_template_part( 'loop', 'home' );
}

	
get_footer();
