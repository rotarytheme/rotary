<?php
function add_theme_caps(){
	global $pagenow;

	if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ){ // Test if theme is activated
		// Theme is activated
		// gets the administrator role\
		$editor = get_role( 'editor' );
		$editor->add_cap( 'edit_home_page' );
		$editor->add_cap( 'create_announcements' );
		$editor->add_cap( 'create_mailchimp_campaigns' );
		$editor->add_cap( 'create_speaker_programs' );
		$editor->add_cap( 'edit_speaker_programs' );
		$editor->add_cap( 'delete_others_announcements' );
		$editor->add_cap( 'edit_others_announcements' );
	}
	else {
	// Theme is deactivated
		// Remove the capacity when theme is deactivated
		$editor = get_role( 'editor' );
		$editor->remove_cap( 'edit_home_page' );
		$editor->remove_cap( 'create_announcements' );
		$editor->remove_cap( 'create_mailchimp_campaigns' );
		$editor->remove_cap( 'create_speaker_programs' );
		$editor->remove_cap( 'edit_speaker_programs' );
		$editor->remove_cap( 'delete_others_announcements' );
		$editor->remove_cap( 'edit_others_announcements' );
  }
}
add_action( 'load-themes.php', 'add_theme_caps' );