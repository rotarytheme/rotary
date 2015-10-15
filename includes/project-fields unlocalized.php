<?php

/**
 * Documentation
 * http://www.advancedcustomfields.com/resources/register-fields-via-php/ 
 */

if( function__xists("register_field_group") )
{
	register_field_group(array (
		'id' => 'acf_projects',
		'title' => 'Projects',
		'fields' => array (
			array (
				'key' => 'field_5436e8c7c79e3',
				'label' => 'Project Dates',
				'name' => 'long_term_project',
				'type' => 'true_false',
				'instructions' => 'Enables multiple posts to be associated with this project.	Use this setting if this is a trip or long term project for which several updates will be posted, or for a shorter event or project where there is a start and end time.',
				'message' => 'Multi-day Project','Label to toggle project updates and start/end times',
				'default_value' => 0
			),
			array (
				'key' => 'field_53e29fcd38551',
				'label' => 'Project Start Date',
				'name' => 'rotary_project_date',
				'type' => 'date_picker',
				'instructions' => 'Enter the project date. If left blank, it will default to the current date.',
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1
			),
			array (
				'key' => 'field_rotary_project_start_time',
				'label' => 'Start Time',
				'name' => 'rotary_project_start_time',
				'type' => 'text',
				'default_value' => '6:30 pm',
				'required' => 1,
				'instructions' => 'Enter the start time in H:mm AM/PM format.',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
							array (
									'field' => 'field_5436e8c7c79e3',
									'operator' => '<=',
									'value' => 1
							),
					),
					'allorany' => 'all',
				),
			),
			array (
				'key' => 'field_rotary_project__nd_time',
				'label' => 'End Time',
				'name' => 'rotary_project__nd_time',
				'default_value' => '9:00 pm',
				'required' => 1,
				'type' => 'text',
				'instructions' => 'Enter the end time in H:mm AM/PM format.',
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
							array (
									'field' => 'field_5436e8c7c79e3',
									'operator' => '<=',
									'value' => 1
							),
					),
					'allorany' => 'all',
				),
			),
			array (
				'key' => 'field_550cabe282129',
				'label' => 'Project End Date',
				'name' => 'rotary_project__nd_date',
				'type' => 'date_picker',
				'instructions' => 'Enter the end project date.',
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_5436e8c7c79e3',
							'operator' => '==',
							'value' => '1'
						),
					),
					'allorany' => 'all',
				),
			),
			array (
				'key' => 'field_allow_participation',
				'label' => 'Allow Registration',
				'name' => 'participants_table_flag',
				'type' => 'radio',
				'choices' => array(
								0 => 'None',
								1 => 'Member Sign-Up Table ',
								2 => 'Custom Registration Form'						
							),
				'instructions' => 'Select the type of participation you want to be made availalbe.  Payments require a custom registration form',
				'layout' => 'horizontal',
				'message' => '',
				'default_value' => 1
			),
			array (
				'key' => 'field_gravity_form_id',
				'label' => 'Form ID',
				'name' => 'gravity_form_id',
				'type' => 'number',
				'instructions' => 'Enter a valid form id, and publish/update the project to populate the Column selector dropdown',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => '',
				'maxlength' => '',
				'min' => 1,
				'required' => 1,
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_allow_participation',
							'operator' => '==',
							'value' => '2',
						),
					),
					'allorany' => 'all',
				),
			),
			array (
				'key' => 'field_column_display_repeater',
				'label' => 'Columns to Display',
				'instructions' => 'If no columns are selected, the participation table will not display',
				'name' => 'column_display_repeater',
				'type' => 'repeater',
				'required' => 0,
				'sub_fields' => array (
						array (
								'key' => 'field_56116ab1f39a6',
								'label' => 'Column',
								'name' => 'form_field_column_selector',
								'type' => 'select',
								'required' => 1,
								'column_width' => 45,
								'choices' => array (),
								'default_value' => '',
								'allow_null' => 0,
								'multiple' => 0,
						),
						array (
								'key' => 'field_56116af2f39a7',
								'label' => 'Column Heading',
								'name' => 'form_field_column_header',
								'type' => 'text',
								'column_width' => 40,
								'default_value' => '',
								'placeholder' => 'Column Header',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => 15
						),
						array (
								'key' => 'field_form_field_column_width',
								'label' => 'Width',
								'name' => 'form_field_column_width',
								'type' => 'number',
								'step'=> 10,
								'min' => 10,
								'max' => 100,
								'column_width' => 15,
								'default_value' => 20,
								'prepend' => '',
								'append' => '%',
								'maxlength' => 2
						),
					),
					'row_min' => 0,
					'row_limit' => 5,
					'layout' => 'table',
					'button_label' => 'Add Column',
					'conditional_logic' => array (
						'status' => 1,
						'rules' => array (
							array (
								'field' => 'field_allow_participation',
								'operator' => '==',
								'value' => '2',
							),
						),
						'allorany' => 'all'
					),
			),
			array (
				'key' => 'field_button_label',
				'label' => 'Registration Form button text',
				'name' => 'gravity_button_label',
				'type' => 'select',
				'instructions' => '',
				'default_value' => 'Register',
				'required' => 1,
				'choices' => array(
								'Register' => 'Register', 
								'Signup' => 'Signup', 
								'Volunteer' => 'Volunteer', 
								'Support' => 'Support', 
								'Purchase' => 'Purchase', 
								'Purchase' => 'Advocate', 
								'Purchase' => 'Donate', 	
								'Purchase' => 'Purchase', 					
							),
				'conditional_logic' => array (
					'status' => 1,
					'rules' => array (
						array (
							'field' => 'field_allow_participation',
							'operator' => '==',
							'value' => '2'
						),
					),
					'allorany' => 'all'
				),
			),
			array (
				'key' => 'field_53e2a107e4d92',
				'label' => 'Picture Gallery',
				'name' => 'rotary_project_picture_gallery',
				'type' => 'repeater',
				'instructions' => 'Enter project pictures',
				'sub_fields' => array (
					array (
						'key' => 'field_53e2a169e4d93',
						'label' => 'Project Picture',
						'name' => 'rotary_project_picture',
						'type' => 'image',
						'column_width' => '',
						'save_format' => 'id',
						'preview_size' => 'thumbnail',
						'library' => 'all'
					),
				),
				'row_min' => '',
				'row_limit' => '',
				'layout' => 'table',
				'button_label' => 'Add Row'
			),
			array (
				'key' => 'field_53e2a1f1aefb3',
				'label' => 'Project Location',
				'name' => 'rotary_project_location',
				'type' => 'google_map',
				'instructions' => 'Enter a google-map recognizable address',
				'center_lat' => '47.611066',
				'center_lng' => '-122.201916',
				'zoom' => '12',
				'height' => 220
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'rotary_projects',
					'order_no' => 0,
					'group_no' => 0
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
				0 => 'excerpt',
				1 => 'custom_fields',
				2 => 'discussion',
				3 => 'revisions',
				4 => 'author',
				5 => 'format',
				6 => 'send-trackbacks'
			),
		),
		'menu_order' => 0,
	));
}


function rotary_form_field_column_selector_choices( $acf_field ) {
	global  $id;

	// reset choices
	$acf_field['choices'] = array();
	$gf_form_id = get_field( 'field_gravity_form_id', $id);
	if( $gf_form_id ) :
		$gf_form = GFAPI::get_form( $gf_form_id );
		if ( is_array( $gf_form )) :
			foreach ( $gf_form['fields'] as $gf_field ) {
					$acf_field['choices'][ $gf_field->id ] = $gf_field->label;
			}
		else: 
			$error = '<div><p class="alert" style="font-weight:bold;color:red;text-align:center;">Sorry, I can\'t find form ' . $gf_form_id . '</p></div>';
			echo $error;
		endif;
		// return the field
	endif;
	return $acf_field;
}
add_filter( 'acf/load_field/name=form_field_column_selector', 'rotary_form_field_column_selector_choices' );