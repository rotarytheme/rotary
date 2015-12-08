<?php
function add_theme_caps(){
	if( !get_role('member' )) :
		$capabilities = array(
				'create_announcements' => true,
				'edit_others_announcements' => true,
				'delete_pages' => true,
				'delete_published_pages' => true,
				'edit_pages' => true,
				'edit_others_pages' => true,
				'edit_published_pages' => true,
				'publish_pages' => true,	
				'gravityforms_edit_forms' => true,
				'gravityforms_view_entries' => true,
				'read' => true,
				'manage_categories' => true,
				'delete_posts' => true,
				'delete_published_posts' => true,
				'edit_posts' => true,
				'edit_published_posts' => true,
				'edit_others_posts' => true,
				'publish_posts' => true,
		);
		add_role( 'member', 'Club Member', $capabilities );
	endif;
	
	$editor = get_role( 'editor' );
	if( !$editor->capabilities['create_announcements'] ) {
		$editor->add_cap( 'edit_home_page' );
		$editor->add_cap( 'create_announcements' );
		$editor->add_cap( 'create_mailchimp_campaigns' );
		$editor->add_cap( 'create_speaker_programs' );
		$editor->add_cap( 'edit_speaker_programs' );
		$editor->add_cap( 'delete_others_announcements' );
		$editor->add_cap( 'edit_others_announcements' );
	}
	
	$admin = get_role( 'administrator' );
	if( !$admin->capabilities['create_announcements'] ) {
		$admin->add_cap( 'edit_home_page' );
		$admin->add_cap( 'create_announcements' );
		$admin->add_cap( 'create_mailchimp_campaigns' );
		$admin->add_cap( 'create_speaker_programs' );
		$admin->add_cap( 'edit_speaker_programs' );
		$admin->add_cap( 'delete_others_announcements' );
		$admin->add_cap( 'edit_others_announcements' );
	}
}
add_action( 'after_setup_theme', 'add_theme_caps' );
add_action( 'before_dacdb_update', 'add_theme_caps' );
add_action( 'profile_update', 'add_theme_caps' );
add_action( 'user_register', 'add_theme_caps' );
add_action( 'is_iu_pre_user_import', 'add_theme_caps' );

