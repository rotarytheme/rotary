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
}
add_action( 'after_setup_theme', 'add_theme_caps' );