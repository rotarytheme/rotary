<?php
function add_theme_caps(){
	$editor = get_role( 'editor' );
	$editor->add_cap( 'edit_home_page' );
	$editor->add_cap( 'create_announcements' );
	$editor->add_cap( 'create_mailchimp_campaigns' );
	$editor->add_cap( 'create_speaker_programs' );
	$editor->add_cap( 'edit_speaker_programs' );
	$editor->add_cap( 'delete_others_announcements' );
	$editor->add_cap( 'edit_others_announcements' );
	

	$admin = get_role( 'administrator' );
	$admin->add_cap( 'edit_home_page' );
	$admin->add_cap( 'create_announcements' );
	$admin->add_cap( 'create_mailchimp_campaigns' );
	$admin->add_cap( 'create_speaker_programs' );
	$admin->add_cap( 'edit_speaker_programs' );
	$admin->add_cap( 'delete_others_announcements' );
	$admin->add_cap( 'edit_others_announcements' );
}
add_action( 'after_setup_theme', 'add_theme_caps' );

