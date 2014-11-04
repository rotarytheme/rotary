<?php
if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_projects',
		'title' => 'Projects',
		'fields' => array (
			array (
				'key' => 'field_5436e8c7c79e3',
				'label' => 'Long Term Project',
				'name' => 'long_term_project',
				'type' => 'true_false',
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_53e29fcd38551',
				'label' => 'Project Date',
				'name' => 'rotary_project_date',
				'type' => 'date_picker',
				'instructions' => 'Enter the project date. If left blank, it will default to the current date.',
				'date_format' => 'yymmdd',
				'display_format' => 'mm/dd/yy',
				'first_day' => 1,
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
						'library' => 'all',
					),
				),
				'row_min' => '',
				'row_limit' => '',
				'layout' => 'table',
				'button_label' => 'Add Row',
			),
			array (
				'key' => 'field_53e2a1f1aefb3',
				'label' => 'Project Location',
				'name' => 'rotary_project_location',
				'type' => 'google_map',
				'instructions' => 'Enter the project location ',
				'center_lat' => '47.611066',
				'center_lng' => '-122.201916',
				'zoom' => '',
				'height' => 220,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'rotary_projects',
					'order_no' => 0,
					'group_no' => 0,
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
				6 => 'send-trackbacks',
			),
		),
		'menu_order' => 0,
	));
}
?>