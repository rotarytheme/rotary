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
/**************************************************************************
 * LOGO SETTINGS
*/	
	$wp_customize->add_section( 'rotary_logo_settings', array(
			'title'          => __( 'Name and Logo', 'Rotary' ),
			'priority'       => 34,
	) );
	

	/*Settings and controls for Rotary Club Name*/
	$wp_customize->add_setting( 'rotary_club_name', array(
			'default'        => '',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_club_name', array(
			'label'   => __( 'Club/District Name', 'Rotary' ),
			'section' => 'rotary_logo_settings',
			'type'    => 'text',
	) );
	$wp_customize->add_setting( 'rotary_club_first', array(
			'default'        => false,
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_club_first', array(
			'label'   => __( 'Show Rotary Club Before Club Name', 'Rotary' ),
			'section' => 'rotary_logo_settings',
			'type'    => 'checkbox',
	) );
	
$wp_customize->add_setting( 'rotary_club_district', array(
		'default'        => '1',
		'capability'     => 'edit_theme_options',
	) );
$wp_customize->add_control( 'rotary_club_district', array(
		'label'   => __( 'Is This a Rotary Club or District&quot;', 'Rotary' ),
		'section' => 'rotary_logo_settings',
		'type'    => 'radio',
		'choices' => array (
					1 => 'Club',
					2 => 'District'
		)
	) );

/*settings and controls for the logo*/
$wp_customize->add_setting( 'rotary_club_logo', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
) );

$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'rotary_club_logo', array(
		'label'   => __( 'Optional Logo file to use', 'Rotary' ),
		'section' => 'rotary_logo_settings',
		'settings'   => 'rotary_club_logo',
		'description' => __( 'If this is not provided, a responsive text version will be used (required for Districts)', 'Rotary' ),
) ) );


/**************************************************************************
 * MEETING INFORMATION
 */
$wp_customize->add_section( 'rotary_main_settings', array(
		'title'          => __( 'Meeting Information', 'Rotary' ),
		'priority'       => 35,
) );	
	
	
/*Settings and Controls for the meeting location*/	
	$wp_customize->add_setting( 'rotary_meeting_location', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( new Rotary_Textarea_Control( $wp_customize, 'rotary_meeting_location', array(
	'label'   => __( 'Meeting Location', 'Rotary' ),
	'section' => 'rotary_main_settings',
	'settings'   => 'rotary_meeting_location',
	) ) );
	$wp_customize->add_setting( 'rotary_country', array(
			'default'        => 'United States',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_country', array(
		'label'   => __( 'Country', 'Rotary' ),
		'section' => 'rotary_main_settings',
		'type'    => 'text',
	) );
	$wp_customize->add_setting( 'rotary_telephone', array(
			'default'        => '',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_telephone', array(
		'label'   => __( 'Telephone', 'Rotary' ),
		'section' => 'rotary_main_settings',
		'type'    => 'text',
	) );
/* Meeting times */

	$wp_customize->add_setting( 'rotary_meeting_day', array(
			'default'        => '1',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_meeting_day', array(
			'label'   => __( 'Meeting Day', 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'select',
			'choices' => array(
					1 =>  'Monday',
					2 =>  'Tuesday',
					3 =>  'Wednesday',
					4 =>  'Thursday',
					5 =>  'Friday',
					6 =>  'Saturday',
					7 =>  'Sunday',
	) ) );
	$time_array = array(
			'6:00 am' =>  '6:00 am',
			'6:30 am' =>  '6:30 am',
			'7:00 am' =>  '7:00 am',
			'7:30 am' =>  '7:30 am',
			'8:00 am' =>  '8:00 am',
			'8:30 am' =>  '8:30 am',
			'9:00 am' =>  '9:00 am',
			'9:30 am' =>  '9:30 am',
			'10:00 am' =>  '10:00 am',
			'10:30 am' =>  '10:30 am',
			'11:00 am' =>  '11:00 am',
			'11:30 am' =>  '11:30 am',
			'12:00 pm' =>  '12:00 pm',
			'12:30 pm' =>  '12:30 pm',
			'1:00 pm' =>  '1:00 pm',
			'1:30 pm' =>  '1:30 pm',
			'2:00 pm' =>  '2:00 pm',
			'2:30 pm' =>  '2:30 pm',
			'3:00 pm' =>  '3:00 pm',
			'3:30 pm' =>  '3:30 pm',
			'4:00 pm' =>  '4:00 pm',
			'4:30 pm' =>  '4:30 pm',
			'5:00 pm' =>  '5:00 pm',
			'5:30 pm' =>  '5:30 pm',
			'6:00 pm' =>  '6:00 pm',
			'6:30 pm' =>  '6:30 pm',
			'7:00 pm' =>  '7:00 pm',
			'7:30 pm' =>  '7:30 pm',
			'8:00 pm' =>  '8:00 pm',
			'8:30 pm' =>  '8:30 pm',
			'9:00 pm' =>  '9:00 pm',
			'9:30 pm' =>  '9:30 pm',
	);
	
	$wp_customize->add_setting( 'rotary_doors_open', array(
			'default'        => '7:00 am',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_doors_open', array(
			'label'   => __( 'Doors Open', 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'select',
			'choices' => $time_array,
	) );
	$wp_customize->add_setting( 'rotary_program_starts', array(
			'default'        => '7:30 am',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_program_starts', array(
			'label'   => __( 'Program Starts', 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'select',
			'choices' => $time_array,
	) );
	$wp_customize->add_setting( 'rotary_program_ends', array(
			'default'        => '8:30 am',
			'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_program_ends', array(
			'label'   => __( 'Program Ends', 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'select',
			'choices' => $time_array,
	) );
/*Settings and Controls for the Get More Info button*/	
	$wp_customize->add_setting( 'rotary_more_info_button', array(
			'default'        => '',
			'capability'     => 'edit_theme_options',
		) );
		$wp_customize->add_control( 'rotary_more_info_button', array(
			'label'   => __( 'Select page for the Get More Info button' , 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'dropdown-pages',
	) );
	/*Settings and Controls for Slideshow*/	
	$wp_customize->add_setting( 'rotary_slideshow', array(
			'default'        => true,
			'capability'     => 'edit_theme_options',
		) );
	$wp_customize->add_control( 'rotary_slideshow', array(
			'label'   => __( 'Show Slideshow', 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'checkbox',
	) );
	/*Settings and Controls for Sidebars*/	
	$wp_customize->add_setting( 'rotary_home_sidebar', array(
			'default'        => true,
			'capability'     => 'edit_theme_options',
		) );
	$wp_customize->add_control( 'rotary_home_sidebar', array(
			'label'   => __( 'Show Home Page Sidebar', 'Rotary' ),
			'section' => 'rotary_main_settings',
			'type'    => 'checkbox',
	) );

/*Settings and Controls for Social Media*/	
	$wp_customize->add_section( 'rotary_social_media_settings', array(
		'title'          => __( 'Social Media', 'Rotary' ),
		'priority'       => 35,
	) );
	
	$wp_customize->add_setting( 'rotary_twitter', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_twitter', array(
		'label'   => __( 'Twitter', 'Rotary' ),
		'section' => 'rotary_social_media_settings',
		'type'    => 'text',
	) );
	
	
	$wp_customize->add_setting( 'rotary_facebook', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_facebook', array(
		'label'   => __( 'Facebook', 'Rotary' ),
		'section' => 'rotary_social_media_settings',
		'type'    => 'text',
	) );
	
	$wp_customize->add_setting( 'rotary_linkedin', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );
	$wp_customize->add_control( 'rotary_linkedin', array(
		'label'   => __( 'Linkedin', 'Rotary' ),
		'section' => 'rotary_social_media_settings',
		'type'    => 'text',
	) );

	/*Settings and Controls for the custom css*/	
	$wp_customize->add_section( 'rotary_style_settings', array(
		'title'          => __( 'Styles', 'Rotary' ),
		'priority'       => 36,
	) );
	$wp_customize->add_setting( 'rotary_custom_css', array(
		'default'        => '',
		'capability'     => 'edit_theme_options',
	) );


	$wp_customize->add_control( new Rotary_Textarea_Control( $wp_customize, 'rotary_custom_css', array(
	'label'   => __( 'Custom CSS', 'Rotary' ),
	'section' => 'rotary_style_settings',
	'settings'   => 'rotary_custom_css',
) ) );
}