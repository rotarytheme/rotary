<?php
if( function_exists( "register_field_group" ))
{
	register_field_group(array (
		'id' => 'acf_committees',
		'title' => 'Committees',
		'fields' => array (
			array (
				'key' => 'field_5351b9ef109fe',
				'label' => 'Committee Number',
				'name' => 'committeenumber',
				'type' => 'number',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
			),
			array (
				'key' => 'field_5351ba0f109ff',
				'label' => 'Committee Mission',
				'name' => 'committeemission',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_5356d453d36ac',
				'label' => 'Committee Chair Email',
				'name' => 'committee_chair_email',
				'type' => 'email',
				'instructions' => 'Enter the email for the committe chair.',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array (
				'key' => 'field_537e8f98e09e1',
				'label' => 'Committee Chair Phone',
				'name' => 'committee_chair_phone',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_5356d48feb36b',
				'label' => 'Committee Co-chair Email 1',
				'name' => 'committee_cochair_email_1',
				'type' => 'email',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
			array (
				'key' => 'field_5356d4dbeecc0',
				'label' => 'Committee Co-chair Email 2',
				'name' => 'committee_co-chair_email_2',
				'type' => 'email',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'rotary-committees',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}
?>