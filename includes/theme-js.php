<?php

if (!is_admin()) add_action( 'wp_enqueue_scripts', 'rotary_add_javascript' );

function rotary_add_javascript( ) {

	wp_enqueue_script( 'modernizr', get_bloginfo('template_directory').'/includes/js/modernizr.custom.js', array( 'jquery' ) );
	
	wp_enqueue_script( 'cycle', get_bloginfo('template_directory').'/includes/js/jquery.cycle.all.js', array( 'jquery' ) );
	
	wp_enqueue_script( 'touch', get_bloginfo('template_directory').'/includes/js/jquery.touchwipe.min.js', array( 'jquery' ) );
		
	wp_enqueue_script( 'datatables', get_bloginfo('template_directory').'/includes/js/jquery.dataTables.min.js', array( 'jquery' ) );
	
	wp_enqueue_script( 'jquery-ui-tabs' );
	
	wp_enqueue_script( 'jquery-ui-datepicker' );
	
	wp_enqueue_script( 'jquery-masonry' );
	
	wp_enqueue_script( 'fancybox', get_bloginfo('template_directory').'/includes/js/jquery.fancybox.pack.js', array( 'jquery' ) );
	
	wp_enqueue_script('googlemaps', 'http://maps.googleapis.com/maps/api/js?sensor=false', false, '3');
	
	wp_enqueue_script( 'hoverIntent' );

	
	wp_enqueue_script( 'rotary', get_bloginfo('template_directory').'/includes/js/rotary-theme.js', array( 'jquery' ) );
	
	wp_localize_script( 'rotary', 'rotaryparticipants', array('ajaxURL' => admin_url('admin-ajax.php'),'rotaryNonce' => wp_create_nonce( 'rotary-participant-nonce' )) );
	
}