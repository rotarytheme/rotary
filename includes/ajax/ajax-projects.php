<?php


//toggle whether or not a member is participating. Notice that there is no "no priv" ajax as the member
//must be logged in to say that he/she is participating.
add_action( 'wp_ajax_toggleparticipants', 'rotary_toggleparticipants' );
function rotary_toggleparticipants() {
	// By default, let's start with an error message
	$response = array(
			'status' => 'error',
			'message' => 'Invalid nonce',
	);
	$current_user = wp_get_current_user();
	$going = 'no';
	// Next, check to see if the nonce is valid
	if( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'rotary-participant-nonce' ) ) :
	// Update our message / status since our request was successfully processed
	$response['status'] = 'success';
	//toggle value
	if ('' == $_GET['participate']) :
	$going = 'yes';
	p2p_type( 'projects_to_users' )->connect( $_GET['postid'], $current_user->ID, array('date' => current_time('mysql')));
	else :
	p2p_type( 'projects_to_users' )->disconnect( $_GET['postid'], $current_user->ID, array('date' => current_time('mysql')));
	endif;
	$response['message'] = $going;

	endif;

	// Return our response to the script in JSON format
	header( 'Content: application/json' );
	echo json_encode( $response );
	die;
}