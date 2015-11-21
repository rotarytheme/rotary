<?php
/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @see http://tgmpluginactivation.com/configuration/ for detailed documentation.
 *
 * @package    TGM-Plugin-Activation
 * @subpackage Example
 * @version    2.5.2
 * @author     Thomas Griffin, Gary Jones, Juliette Reinders Folmer
 * @copyright  Copyright (c) 2011, Thomas Griffin
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://github.com/TGMPA/TGM-Plugin-Activation
 */

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once get_template_directory() . '/required-plugins/class-tgm-plugin-activation.php';
require_once ABSPATH . '/wp-admin/includes/plugin.php';


add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );
/**
 * Register the required plugins for this theme.
 *
 * In this example, we register five plugins:
 * - one included with the TGMPA library
 * - two from an external source, one from an arbitrary source, one from a GitHub repository
 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function my_theme_register_required_plugins() {
	/*
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	deactivate_plugins( array ( 'rotarymembership/rotarymembership.php' ) );
	
	
	$plugins = array(

		// This is an example of how to include a plugin bundled with a theme.
		/*
		array(
			'name'               => 'TGM Example Plugin', // The plugin name.
			'slug'               => 'tgm-example-plugin', // The plugin slug (typically the folder name).
			'source'             => get_stylesheet_directory() . '/lib/plugins/tgm-example-plugin.zip', // The plugin source.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => '', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
		),
			*/
	array(
		'name' 				=> 'Gravity Forms',
		'slug' 				=> 'gravityforms',
		'source'    		=> 'gravityforms.zip', // The plugin source.
		'version'			=> '1.9.14',
		'required' 			=> true,
		'force_activation' 	=> true	),
	array(
		'name' 				=> 'Gravity Forms PayPal',
		'slug' 				=> 'gravityformspaypal',
		'source'    		=> 'gravityformspaypal.zip', // The plugin source.
		'version'			=> '2.5.1',
		'required' 			=> true,
		'force_activation' 	=> true,		),

	array(
		'name' 				=> 'Max Mega Menu Pro',
		'slug' 				=> 'megamenu-pro',
		'version'			=> '1.3.6',
		'source'    		=> 'megamenu-pro.zip', // The plugin source.
		'required' 			=> false,
		'force_activation' 	=> false
	),

	array(
			'name' 		=> 'MailChimp Campaign',
			'slug' 		=> 'nm-mailchimp-campaign',
			'source'    => 'nm-mailchimp-campaign.zip', // The plugin source.
			'required' 	=> true,
	),


			
		// This is an example of how to include a plugin from an arbitrary external source in your theme.
		/*
		array(
			'name'         => 'TGM New Media Plugin', // The plugin name.
			'slug'         => 'tgm-new-media-plugin', // The plugin slug (typically the folder name).
			'source'       => 'https://s3.amazonaws.com/tgm/tgm-new-media-plugin.zip', // The plugin source.
			'required'     => true, // If false, the plugin is only 'recommended' instead of required.
			'external_url' => 'https://github.com/thomasgriffin/New-Media-Image-Uploader', // If set, overrides default API URL and points to an external URL.
		),
		*/
			
		// This is an example of how to include a plugin from a GitHub repository in your theme.
		// This presumes that the plugin code is based in the root of the GitHub repository
		// and not in a subdirectory ('/src') of the repository.
		array(
			'name'      => 'Rotary Membership',
			'slug'      => 'rotarymembership-master',
			'source'    => 'https://github.com/rotarytheme/rotarymembership/archive/master.zip',
			'required' 	=> true,
			'force_activation' => true,
			'force_deactivation' => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'is_callable'        => 'get_rotary_club_members', // If set, this callable will be be checked for availability to determine if a plugin is active.
				
		),

		// This is an example of how to include a plugin from the WordPress Plugin Repository.
		array(
			'name' 		=> 'Contact Form 7',
			'slug' 		=> 'contact-form-7',
			'required' 	=> true,
			'force_activation' => true
			),
			
		array(
			'name' 		=> 'Posts 2 Posts',
			'slug' 		=> 'posts-to-posts',
			'required' 	=> true,
			'force_activation' => true,
			'force_deactivation' => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
		),
		array(
				'name' 		=> 'Image Widget',
				'slug' 		=> 'image-widget',
				'required' 	=> false,
				'force_activation' => false
		),
		array(
				'name' 		=> 'Add Multiple Users for WordPress',
				'slug' 		=> 'add-multiple-users',
				'required' 	=> false,
				'force_activation' => false
		),
		array(
				'name' 		=> 'AutoChimp',
				'slug' 		=> 'autochimp',
				'required' 			=> false,
				'force_activation' 	=> false
		),
		array(
				'name' 		=> 'Members',
				'slug' 		=> 'members',
				'required' 	=> true,
				'force_activation' => true
		),
		array(
				'name' 		=> 'TinyMCE Advanced',
				'slug' 		=> 'tinymce-advanced',
				'required' 	=> false,
				'force_activation' => false
		),
			/*
		array(
				'name' 		=> 'Download Manager',
				'slug' 		=> 'download-manager',
				'required' 	=> false,
				'force_activation' => false
		),
		*/
		array(
				'name' 		=> 'Max Mega Menu',
				'slug' 		=> 'megamenu',
				'required' 	=> false,
				'force_activation' => false
		),
		array(
				'name' 		=> 'Recent Facebook Posts',
				'slug' 		=> 'recent-facebook-posts',
				'required' 	=> false,
				'force_activation' => false
		),
		array(
				'name' 		=> 'Really Simple Captcha',
				'slug' 		=> 'really-simple-captcha',
				'required' 	=> true,
				'force_activation' => true
		),
		// This is an example of the use of 'is_callable' functionality. A user could - for instance -
		// have WPSEO installed *or* WPSEO Premium. The slug would in that last case be different, i.e.
		// 'wordpress-seo-premium'.
		// By setting 'is_callable' to either a function from that plugin or a class method
		// `array( 'class', 'method' )` similar to how you hook in to actions and filters, TGMPA can still
		// recognize the plugin as being installed.
		/*
		array(
			'name'        => 'WordPress SEO by Yoast',
			'slug'        => 'wordpress-seo',
			'is_callable' => 'wpseo_init',
		),
		*/

	);

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */

	// Change this to your theme text domain, used for internationalising strings
	$theme_text_domain = 'rotary';
	
	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	/*
	$config = array(
			'parent_menu_slug'  => 'plugins.php',         // Default parent menu slug
			'parent_url_slug'   => 'plugins.php',
			'strings'      		=> array(
					),
			);
	}
	*/
	
	
	$config = array(
		'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => get_template_directory() . '/required-plugins/plugins/',               // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.

		/*
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
			'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
			'installing'                      => __( 'Installing Plugin: %s', 'theme-slug' ), // %s = plugin name.
			'oops'                            => __( 'Something went wrong with the plugin API.', 'theme-slug' ),
			'notice_can_install_required'     => _n_noop(
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_can_install_recommended'  => _n_noop(
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_cannot_install'           => _n_noop(
				'Sorry, but you do not have the correct permissions to install the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to install the %1$s plugins.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_ask_to_update_maybe'      => _n_noop(
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop(
				'Sorry, but you do not have the correct permissions to update the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to update the %1$s plugins.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_can_activate_required'    => _n_noop(
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_can_activate_recommended' => _n_noop(
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'notice_cannot_activate'          => _n_noop(
				'Sorry, but you do not have the correct permissions to activate the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to activate the %1$s plugins.',
				'theme-slug'
			), // %1$s = plugin name(s).
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'theme-slug'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'theme-slug'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'theme-slug'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'theme-slug' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'theme-slug' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'theme-slug' ),
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'theme-slug' ),  // %1$s = plugin name(s).
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'theme-slug' ),  // %1$s = plugin name(s).
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'theme-slug' ), // %s = dashboard link.
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'tgmpa' ),

			'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		),
		*/
	);

	tgmpa( $plugins, $config );
}


/**
 * import the megamenu settings
 * @author paulosborn
 *
 */


function import_mega_menu_style(){
	require_once ( ABSPATH . '/wp-content/plugins/megamenu/classes/style-manager.class.php' );
	require_once ( ABSPATH . '/wp-content/plugins/megamenu/classes/settings.class.php' );
	Class Mega_Menu_Settings_Installation extends Mega_Menu_Settings{
	
		public function install_theme() {
	
			$this->init();
	
			$data = '{"title":"Rotary","container_background_from":"rgb(0, 36, 108)","container_background_to":"rgb(0, 36, 108)","container_padding_left":"10px","container_padding_right":"10px","container_padding_top":"8px","container_padding_bottom":"0px","container_border_radius_top_left":"0px","container_border_radius_top_right":"0px","container_border_radius_bottom_left":"0px","container_border_radius_bottom_right":"0px","arrow_up":"disabled","arrow_down":"disabled","arrow_left":"dash-f340","arrow_right":"dash-f344","font_size":"14px","font_color":"#666","font_family":"inherit","menu_item_align":"left","menu_item_background_from":"rgba(251, 251, 255, 0)","menu_item_background_to":"rgba(255, 255, 255, 0)","menu_item_background_hover_from":"rgb(50, 50, 61)","menu_item_background_hover_to":"rgb(50, 50, 61)","menu_item_spacing":"5px","menu_item_link_font":"Open Sans Condensed","menu_item_link_font_size":"17px","menu_item_link_height":"45px","menu_item_link_color":"rgb(255, 255, 255)","menu_item_link_weight":"bold","menu_item_link_text_transform":"capitalize","menu_item_link_text_decoration":"none","menu_item_link_color_hover":"rgb(255, 255, 255)","menu_item_link_weight_hover":"bold","menu_item_link_text_decoration_hover":"none","menu_item_link_padding_left":"10px","menu_item_link_padding_right":"10px","menu_item_link_padding_top":"0px","menu_item_link_padding_bottom":"0px","menu_item_link_border_radius_top_left":"0px","menu_item_link_border_radius_top_right":"0px","menu_item_link_border_radius_bottom_left":"0px","menu_item_link_border_radius_bottom_right":"0px","menu_item_border_color":"#fff","menu_item_border_left":"0px","menu_item_border_right":"0px","menu_item_border_top":"0px","menu_item_border_bottom":"0px","menu_item_border_color_hover":"#fff","menu_item_highlight_current":"on","menu_item_divider":"off","menu_item_divider_color":"rgba(255, 255, 255, 0.1)","menu_item_divider_glow_opacity":"0.1","panel_background_from":"rgb(0, 36, 108)","panel_background_to":"rgb(0, 36, 108)","panel_width":"100%","panel_border_color":"rgb(0, 36, 108)","panel_border_left":"0px","panel_border_right":"0px","panel_border_top":"0px","panel_border_bottom":"0px","panel_border_radius_top_left":"0px","panel_border_radius_top_right":"0px","panel_border_radius_bottom_left":"0px","panel_border_radius_bottom_right":"0px","panel_header_color":"rgb(242, 167, 0)","panel_header_text_transform":"uppercase","panel_header_font":"Open Sans Condensed","panel_header_font_size":"20px","panel_header_font_weight":"bold","panel_header_text_decoration":"none","panel_header_padding_top":"0px","panel_header_padding_right":"0px","panel_header_padding_bottom":"5px","panel_header_padding_left":"0px","panel_header_margin_top":"0px","panel_header_margin_right":"0px","panel_header_margin_bottom":"0px","panel_header_margin_left":"0px","panel_header_border_color":"rgba(85, 85, 85, 0)","panel_header_border_left":"0px","panel_header_border_right":"0px","panel_header_border_top":"0px","panel_header_border_bottom":"0px","panel_padding_left":"10px","panel_padding_right":"10px","panel_padding_top":"0","panel_padding_bottom":"10px","panel_widget_padding_left":"15px","panel_widget_padding_right":"15px","panel_widget_padding_top":"15px","panel_widget_padding_bottom":"15px","panel_font_size":"17px","panel_font_color":"rgb(255, 255, 255)","panel_font_family":"Open Sans Condensed","panel_second_level_font_color":"rgb(242, 167, 0)","panel_second_level_font_color_hover":"rgb(1, 180, 231)","panel_second_level_text_transform":"uppercase","panel_second_level_font":"Open Sans Condensed","panel_second_level_font_size":"20px","panel_second_level_font_weight":"bold","panel_second_level_font_weight_hover":"bold","panel_second_level_text_decoration":"none","panel_second_level_text_decoration_hover":"none","panel_second_level_background_hover_from":"rgba(0,0,0,0)","panel_second_level_background_hover_to":"rgba(0,0,0,0)","panel_second_level_padding_left":"0px","panel_second_level_padding_right":"0px","panel_second_level_padding_top":"0px","panel_second_level_padding_bottom":"0px","panel_second_level_margin_left":"0px","panel_second_level_margin_right":"0px","panel_second_level_margin_top":"0px","panel_second_level_margin_bottom":"0px","panel_second_level_border_color":"rgba(85, 85, 85, 0)","panel_second_level_border_left":"0px","panel_second_level_border_right":"0px","panel_second_level_border_top":"0px","panel_second_level_border_bottom":"0px","panel_third_level_font_color":"rgb(255, 255, 255)","panel_third_level_font_color_hover":"rgb(231, 231, 232)","panel_third_level_text_transform":"none","panel_third_level_font":"Open Sans Condensed","panel_third_level_font_size":"16px","panel_third_level_font_weight":"bold","panel_third_level_font_weight_hover":"bold","panel_third_level_text_decoration":"none","panel_third_level_text_decoration_hover":"none","panel_third_level_background_hover_from":"rgba(0, 0, 0, 0)","panel_third_level_background_hover_to":"rgba(0,0,0,0)","panel_third_level_padding_left":"0px","panel_third_level_padding_right":"0px","panel_third_level_padding_top":"0px","panel_third_level_padding_bottom":"0px","flyout_width":"150px","flyout_menu_background_from":"#f1f1f1","flyout_menu_background_to":"#f1f1f1","flyout_border_color":"#ffffff","flyout_border_left":"0px","flyout_border_right":"0px","flyout_border_top":"0px","flyout_border_bottom":"0px","flyout_border_radius_top_left":"0px","flyout_border_radius_top_right":"0px","flyout_border_radius_bottom_left":"0px","flyout_border_radius_bottom_right":"0px","flyout_menu_item_divider":"off","flyout_menu_item_divider_color":"rgba(222, 36, 36, 0.85)","flyout_padding_top":"0px","flyout_padding_right":"0px","flyout_padding_bottom":"0px","flyout_padding_left":"0px","flyout_link_padding_left":"10px","flyout_link_padding_right":"10px","flyout_link_padding_top":"0px","flyout_link_padding_bottom":"0px","flyout_link_weight":"normal","flyout_link_weight_hover":"normal","flyout_link_height":"35px","flyout_link_text_decoration":"none","flyout_link_text_decoration_hover":"none","flyout_background_from":"#f1f1f1","flyout_background_to":"#f1f1f1","flyout_background_hover_from":"#dddddd","flyout_background_hover_to":"#dddddd","flyout_link_size":"14px","flyout_link_color":"#666","flyout_link_color_hover":"#666","flyout_link_family":"inherit","flyout_link_text_transform":"none","responsive_breakpoint":"600px","responsive_text":"MENU","line_height":"1.7","z_index":"999","shadow":"off","shadow_horizontal":"0px","shadow_vertical":"0px","shadow_blur":"5px","shadow_spread":"0px","shadow_color":"rgba(0, 0, 0, 0.1)","transitions":"on","resets":"on","custom_css":""}';
	
			$import = json_decode( stripslashes( $data ), true );
	
			$saved_themes = get_site_option( "megamenu_themes" );
	
			//Check if this theme has already been imported - if so, replace it
			foreach ( $this->themes as $id => $theme ) {
				if( $import['title'] == $theme['title'] ) {
					$theme_exists =  true;
					$new_theme_id = $id;
				}
			}
			if ( !$theme_exists ) {
				$next_id = $this->get_next_theme_id();
				$new_theme_id = "custom_theme_" . $next_id;
			}
	
			$saved_themes[ $new_theme_id ] = $import; // so this will replace the current definition if the theme_id exists
	
			update_site_option( "megamenu_themes", $saved_themes );
			
			do_action("megamenu_after_theme_import");
	
		}
	
	}
	
	$menu = new Mega_Menu_Settings_Installation();
	$menu->install_theme();
}
register_activation_hook( 'megamenu/megamenu.php', 'import_mega_menu_style' );