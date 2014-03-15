<?php
/**
 * Rotary theme options
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */
 add_action('customize_register', 'rotary_theme_customize');
function rotary_theme_customize($wp_customize) {
    class Rotary_Textarea_Control extends WP_Customize_Control {
		public $type = 'textarea';

		public function render_content() {
		?>
		<label>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
		<?php
	}
} 
	$wp_customize->add_section( 'rotary_main_settings', array(
		'title'          => 'Rotary',
		'priority'       => 35,
	) );

/*settings and controls for the logo*/
	/*$wp_customize->add_setting( 'rotary_logo_default_setting', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rotary_logo_default_setting', array(
		'label'   => 'Upload Rotary Logo',
		'section' => 'rotary_main_settings',
		'settings'   => 'rotary_logo_default_setting',
	) ) );*/
	
/*Settings and controls for Rotary Club Name*/	
$wp_customize->add_setting( 'rotary_club_name', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
$wp_customize->add_control( 'rotary_club_name', array(
		'label'   => 'Rotary Club Name',
		'section' => 'rotary_main_settings',
		'type'    => 'text',
	) );
$wp_customize->add_setting( 'rotary_club_first', array(
		'default'        => false,
		'capability'     => 'edit_theme_options',
	) );
$wp_customize->add_control( 'rotary_club_first', array(
		'label'   => 'Show Rotary Club Before Club Name',
		'section' => 'rotary_main_settings',
		'type'    => 'checkbox',
) );	
/*Settings and Controls for the meeting location*/	
	$wp_customize->add_setting( 'rotary_meeting_location', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );


	$wp_customize->add_control( new Rotary_Textarea_Control( $wp_customize, 'rotary_meeting_location', array(
	'label'   => 'Meeting Location',
	'section' => 'rotary_main_settings',
	'settings'   => 'rotary_meeting_location',
) ) );
/*Settings and Controls for the Get More Info button*/	
$wp_customize->add_setting( 'rotary_more_info_button', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_more_info_button', array(
		'label'   => 'Select page for the Get More Info button',
		'section' => 'rotary_main_settings',
		'type'    => 'dropdown-pages',
) );
/*Settings and Controls for Slideshow*/	
$wp_customize->add_setting( 'rotary_slideshow', array(
		'default'        => true,
		'capability'     => 'edit_theme_options',
	) );
$wp_customize->add_control( 'rotary_slideshow', array(
		'label'   => 'Show Slideshow',
		'section' => 'rotary_main_settings',
		'type'    => 'checkbox',
) );
/*Settings and Controls for Sidebars*/	
$wp_customize->add_setting( 'rotary_home_sidebar', array(
		'default'        => true,
		'capability'     => 'edit_theme_options',
	) );
$wp_customize->add_control( 'rotary_home_sidebar', array(
		'label'   => 'Show Home Page Sidebar',
		'section' => 'rotary_main_settings',
		'type'    => 'checkbox',
) );
/*Settings and Controls for background color*/	
$wp_customize->add_setting( 'rotary_background_color', array(
		'default'        => 'gray',
		'capability'     => 'edit_theme_options',
) );
$wp_customize->add_control( 'rotary_background_color', array(
		'label'   => 'Background Color',
		'section' => 'rotary_main_settings',
		'type'    => 'select',
    	'choices'    => array(
        'gray' => 'Gray',
        'white' => 'White',
        ),
) );

/*Settings and Controls for Social Media*/	
	$wp_customize->add_section( 'rotary_social_media_settings', array(
		'title'          => 'Social Media',
		'priority'       => 35,
	) );
	
	$wp_customize->add_setting( 'rotary_twitter', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_twitter', array(
		'label'   => 'Twitter',
		'section' => 'rotary_social_media_settings',
		'type'    => 'text',
	) );
	
	
	$wp_customize->add_setting( 'rotary_facebook', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_facebook', array(
		'label'   => 'Facebook',
		'section' => 'rotary_social_media_settings',
		'type'    => 'text',
	) );
	
	$wp_customize->add_setting( 'rotary_linkedin', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_linkedin', array(
		'label'   => 'Linkedin',
		'section' => 'rotary_social_media_settings',
		'type'    => 'text',
	) );

	
}