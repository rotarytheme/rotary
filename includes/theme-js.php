<?php

if (!is_admin()) add_action( 'wp_enqueue_scripts', 'rotary_add_javascript' );

function rotary_add_javascript( ) {
	//style not script but it is the correct spot
	
	global $wp_styles; // call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way
    $protocol = is_ssl() ? 'https' : 'http';
	$query_args = array(
		'family' => 'Open+Sans+Condensed:300,700,300italic');
	wp_enqueue_style( 'rotary-opensanscondensed-font', add_query_arg( $query_args, "$protocol://fonts.googleapis.com/css" ), array(), null 	);	

	wp_enqueue_style( 'rotary-styles', get_bloginfo('template_directory').'/rotary-sass/stylesheets/style.min.css');
	
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