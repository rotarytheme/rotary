<?php
/**
 * Rotary theme options
 *
 * @package WordPress
 * @subpackage Rotary
 * @since Rotary 1.0
 */

function rotary_set_home_page() {
	
	if( !get_option( 'rotary_member_page_on_front' ) ) update_option( 'rotary_member_page_on_front', get_option( 'page_on_front' ) );
	if( !get_option( 'rotary_visitor_page_on_front' ) ) update_option( 'rotary_visitor_page_on_front', get_option( 'page_on_front' ) );
	
	if( is_user_logged_in() ) {
		$home = get_option( 'rotary_member_page_on_front' );
	} else {
		$home = get_option( 'rotary_visitor_page_on_front' );
	}
	if( $home ) update_option( 'page_on_front', $home);
}
//add_action('init', 'rotary_set_home_page');


add_action('customize_register', 'rotary_theme_customize');
function rotary_theme_customize( $wp_customize ) {
	class Rotary_Textarea_Control extends WP_Customize_Control {
		public $type = 'textarea';
	
		public function render_content() {
			?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<textarea rows="30" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
				</label>
				<?php
			}
		} 
	$rotary_theme_options = new RotaryThemeOptions();
}



Class RotaryThemeOptions {
	function __construct() {
		global $wp_customize;
		

		
		/**************************************************************************
		 * LOGO SETTINGS
		*/
		
		$isclub = get_theme_mod( 'rotary_club_district', '1' );
		
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
		
		if ( $isclub ) {
				
			$wp_customize->add_setting( 'rotary_club_first', array(
					'default'        => false,
					'capability'     => 'edit_theme_options',
			) );
			$wp_customize->add_control( 'rotary_club_first', array(
					'label'   => __( 'Show Rotary Club Before Club Name', 'Rotary' ),
					'section' => 'rotary_logo_settings',
					'type'    => 'checkbox',
			) );
		}
		$wp_customize->add_setting( 'rotary_club_district', array(
				'default'        => '1',
				'capability'     => 'edit_theme_options',
		) );
		$wp_customize->add_control( 'rotary_club_district', array(
				'label'   => __( 'Is This a Rotary Club or District&#63;', 'Rotary' ),
				'section' => 'rotary_logo_settings',
				'type'    => 'radio',
				'description' => __( 'You will need to refresh the page after changing this option', 'Rotary' ),
				'choices' => array (
							1 => 'Club',
							0 => 'District'
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
		 * This is turned off for Districts
		 */
		
		if ( $isclub ) {
				
				$wp_customize->add_section( 'rotary_main_settings', array(
						'title'          => __( 'Meeting Information', 'Rotary' ),
						'priority'       => 35,
				) );	
					
					
				/*Settings and Controls for the meeting location*/	
					$wp_customize->add_setting( 'rotary_meeting_location', array(
						'default'        => '',
						'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_meeting_location', array(
						'description'    => __( 'If recognized as an address by Google, this will display as a link to a Google Map', 'Rotary' ),
						'label'   => __( 'Meeting Location', 'Rotary' ),
						'section' => 'rotary_main_settings',
						'type'    => 'textarea',
					) );

					$wp_customize->add_setting( 'rotary_country', array(
							'default'        => 'United States',
							'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_country', array(
						'label'   => __( 'Country', 'Rotary' ),
						'section' => 'rotary_main_settings',
						'type'    => 'text',
						'description'    => __( 'Set to "China" to use google.cn API', 'Rotary' ),
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
					
					$wp_customize->add_setting( 'rotary_meeting_frequency', array(
							'default'        => 'every',
							'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_meeting_frequency', array(
							'label'   => __( 'Meeting Frequency', 'Rotary' ),
							'section' => 'rotary_main_settings',
							'type'    => 'select',
							'choices' => array(
									'every' =>  __( 'every', 'Rotary' ),
									'every other' =>  __( 'every other', 'Rotary' ),
									'every first' =>  __( 'every first', 'Rotary' ),
									'every second' =>  __( 'every second', 'Rotary' ),
									'every third' =>  __( 'every third', 'Rotary' ),
									'every fourth' =>  __( 'every fourth', 'Rotary' ),
									'the occasional' =>  __( 'every occasional', 'Rotary' ),
							) ) );
				
					$wp_customize->add_setting( 'rotary_meeting_day', array(
							'default'        => '--select day--',
							'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_meeting_day', array(
							'label'   => __( 'Meeting Day', 'Rotary' ),
							'section' => 'rotary_main_settings',
							'type'    => 'select',
							'choices' => array(
									'--select day--' => __( '--select day--', 'Rotary' ),
									'Monday' =>  __( 'Monday', 'Rotary' ),
									'Tuesday' =>  __( 'Tuesday', 'Rotary' ),
									'Wednesday' =>  __( 'Wednesday', 'Rotary' ),
									'Thursday' =>  __( 'Thursday', 'Rotary' ),
									'Friday' =>  __( 'Friday', 'Rotary' ),
									'Saturday' =>  __( 'Saturday', 'Rotary' ),
									'Sunday' =>  __( 'Sunday', 'Rotary' ),
					) ) );
					$time_array = array(
							'--select time--' => __( '--select time--', 'Rotary' ),
							'6:00 am' =>  __( '6:00 am', 'Rotary' ),
							'6:30 am' =>  __( '6:30 am', 'Rotary' ),
							'7:00 am' =>  __( '7:00 am', 'Rotary' ),
							'7:30 am' =>  __( '7:30 am', 'Rotary' ),
							'8:00 am' =>  __( '8:00 am', 'Rotary' ),
							'8:30 am' =>  __( '8:30 am', 'Rotary' ),
							'9:00 am' =>  __( '9:00 am', 'Rotary' ),
							'9:30 am' =>  __( '9:30 am', 'Rotary' ),
							'10:00 am' =>  __( '10:00 am', 'Rotary' ),
							'10:30 am' =>  __( '10:30 am', 'Rotary' ),
							'11:00 am' =>  __( '11:00 am', 'Rotary' ),
							'11:30 am' =>  __( '11:30 am', 'Rotary' ),
        					    '12:00 pm' =>  __( '12:00 pm', 'Rotary' ),
        					    '12:15 pm' =>  __( '12:15 pm', 'Rotary' ),
        					    '12:30 pm' =>  __( '12:30 pm', 'Rotary' ),
        					    '12:45 pm' =>  __( '12:45 pm', 'Rotary' ),
        					    '1:00 pm' =>  __( '1:00 pm', 'Rotary' ),
        					    '1:15 pm' =>  __( '1:15 pm', 'Rotary' ),
        					    '1:30 pm' =>  __( '1:30 pm', 'Rotary' ),
        					    '1:45 pm' =>  __( '1:45 pm', 'Rotary' ),
							'2:00 pm' =>  __( '2:00 pm', 'Rotary' ),
							'2:30 pm' =>  __( '2:30 pm', 'Rotary' ),
							'3:00 pm' =>  __( '3:00 pm', 'Rotary' ),
							'3:30 pm' =>  __( '3:30 pm', 'Rotary' ),
							'4:00 pm' =>  __( '4:00 pm', 'Rotary' ),
							'4:30 pm' =>  __( '4:30 pm', 'Rotary' ),
							'5:00 pm' =>  __( '5:00 pm', 'Rotary' ),
        					    '5:15 pm' =>  __( '5:15 pm', 'Rotary' ),
        					    '5:30 pm' =>  __( '5:30 pm', 'Rotary' ),
        					    '5:45 pm' =>  __( '5:45 pm', 'Rotary' ),
        					    '6:00 pm' =>  __( '6:00 pm', 'Rotary' ),
        					    '6:15 pm' =>  __( '6:15 pm', 'Rotary' ),
        					    '6:30 pm' =>  __( '6:30 pm', 'Rotary' ),
        					    '6:45 pm' =>  __( '6:45 pm', 'Rotary' ),
        					    '7:00 pm' =>  __( '7:00 pm', 'Rotary' ),
        					    '7:15 pm' =>  __( '7:15 pm', 'Rotary' ),
        					    '7:30 pm' =>  __( '7:30 pm', 'Rotary' ),
        					    '7:45 pm' =>  __( '7:45 pm', 'Rotary' ),
        					    '8:00 pm' =>  __( '8:00 pm', 'Rotary' ),
        					    '8:15 pm' =>  __( '8:15 pm', 'Rotary' ),
        					    '8:30 pm' =>  __( '8:30 pm', 'Rotary' ),
        					    '8:45 pm' =>  __( '8:45 pm', 'Rotary' ),
        					    '9:00 pm' =>  __( '9:00 pm', 'Rotary' ),
        					    '9:15 pm' =>  __( '9:15 pm', 'Rotary' ),
        					    '9:30 pm' =>  __( '9:30 pm', 'Rotary' ),
        					    '9:45 pm' =>  __( '9:45 pm', 'Rotary' ),
        					    '10:00 pm' =>  __( '9:45 pm', 'Rotary' ),
        					    '10:15 pm' =>  __( '10:15 pm', 'Rotary' ),
        					    '10:30 pm' =>  __( '10:30 pm', 'Rotary' ),
        					    '10:45 pm' =>  __( '10:45 pm', 'Rotary' ),
        					    '11:00 pm' =>  __( '11:00 pm', 'Rotary' ), 
					);
					
					$wp_customize->add_setting( 'rotary_doors_open', array(
							'default'        => '--select time--',
							'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_doors_open', array(
							'label'   => __( 'Doors Open', 'Rotary' ),
							'section' => 'rotary_main_settings',
							'type'    => 'select',
							'choices' => $time_array,
					) );
					$wp_customize->add_setting( 'rotary_program_starts', array(
							'default'        => '--select time--',
							'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_program_starts', array(
							'label'   => __( 'Program Starts', 'Rotary' ),
							'section' => 'rotary_main_settings',
							'type'    => 'select',
							'choices' => $time_array,
					) );
					$wp_customize->add_setting( 'rotary_program_ends', array(
							'default'        => '--select time--',
							'capability'     => 'edit_theme_options',
					) );
					$wp_customize->add_control( 'rotary_program_ends', array(
							'label'   => __( 'Program Ends', 'Rotary' ),
							'section' => 'rotary_main_settings',
							'type'    => 'select',
							'choices' => $time_array,
					) );
		} // end of $isclub switch
		
			
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
		
			$wp_customize->add_control( new Rotary_Textarea_Control( $wp_customize, 'rotary_custom_css', 
				array(
					'label'   => __( 'Custom CSS', 'Rotary' ),
					'section' => 'rotary_style_settings',
					'settings'   => 'rotary_custom_css',
				)
			) );

			/**************************************************************************
			 * HOME PAGE OPTIONS
			*/
			if ( 'page' != get_option( 'show_on_front' ) ) {
				update_option( 'show_on_front', 'page');
			}
			
			$wp_customize->add_setting( 'page_on_front', array(
					'type'       => 'option',
					'capability' => 'manage_options',
					//	'theme_supports' => 'static-front-page',
			) );
			
			// $wp_customize->remove_section( 'static_front_page' );
			
			$wp_customize->add_section( 'static_front_page', array(
					'title'          => __( 'Home Page Options' ),
					//	'theme_supports' => 'static-front-page',
					'priority'       => 120,
					'description'    => __( 'This theme can only be used with a static home page.' ),
			) );
			
			$wp_customize->remove_control( 'show_on_front');
			
			//$wp_customize->remove_control( 'page_on_front');
			
			// VISITORS
			/*
			$wp_customize->add_setting( 'rotary_visitor_page_on_front', array(
					'type'       => 'option',
					'capability' => 'manage_options',
					// 'theme_supports' => 'static-front-page',
			) );
			
			$wp_customize->add_control( 'rotary_visitor_page_on_front', array(
					'label'      => __( 'Front page for visitors' ),
					'section'    => 'static_front_page',
					'type'       => 'dropdown-pages',
			) );
			*/
			
			/*Settings and Controls for Slideshow*/
			$wp_customize->add_setting( 'rotary_slideshow', array(
					'default'        => true,
					'capability'     => 'manage_options',
			) );
			$wp_customize->add_control( 'rotary_slideshow', array(
					'label'   => __( 'Show Slideshow', 'Rotary' ),
					'section' => 'static_front_page',
					'type'    => 'checkbox',
			) );
			/*Settings and Controls for Sidebars*/
			$wp_customize->add_setting( 'rotary_home_sidebar', array(
					'default'        => true,
					'capability'     => 'manage_options',
			) );
			$wp_customize->add_control( 'rotary_home_sidebar', array(
					'label'   => __( 'Show Hompage Sidebar', 'Rotary' ),
					'section' => 'static_front_page',
					'type'    => 'checkbox',
			) );
			
			// MEMBERS
			$wp_customize->add_setting( 'rotary_member_page_on_front', array(
					'type'       => 'option',
					'capability' => 'manage_options',
					// 'theme_supports' => 'static-front-page',
			) );
			
			$wp_customize->add_control( 'rotary_member_page_on_front', array(
					'label'      => __( 'Front page for members' ),
					'section'    => 'static_front_page',
					'type'       => 'dropdown-pages',
			) );
			/*Settings and Controls for Slideshow*/
			$wp_customize->add_setting( 'rotary_member_slideshow', array(
					'default'        => true,
					'capability'     => 'manage_options',
			) );
			$wp_customize->add_control( 'rotary_member_slideshow', array(
					'label'   => __( 'Show Slideshow', 'Rotary' ),
					'section' => 'static_front_page',
					'type'    => 'checkbox',
			) );
			/*Settings and Controls for Sidebars*/
			$wp_customize->add_setting( 'rotary_member_sidebar', array(
					'default'        => true,
					'capability'     => 'manage_options',
			) );
			$wp_customize->add_control( 'rotary_member_sidebar', array(
					'label'   => __( 'Show Member Sidebar', 'Rotary' ),
					'section' => 'static_front_page',
					'type'    => 'checkbox',
			) );
			
			$wp_customize->add_setting( 'page_for_posts', array(
					'type'           => 'option',
					'capability'     => 'manage_options',
					// 'theme_supports' => 'static-front-page',
			) );
			
			$wp_customize->add_control( 'page_for_posts', array(
					'label'      => __( 'Posts page' ),
					'section'    => 'static_front_page',
					'type'       => 'dropdown-pages',
			) );

			/*Settings and Controls for the Get More Info button*/
			$wp_customize->add_setting( 'rotary_more_info_button', array(
					'default'        => '',
					'capability'     => 'edit_theme_options',
			) );
			$wp_customize->add_control( 'rotary_more_info_button', array(
					'label'   => __( 'Select page for the Get More Info button' , 'Rotary' ),
					'section' => 'static_front_page',
					'type'    => 'dropdown-pages',
			) );

		}
	}