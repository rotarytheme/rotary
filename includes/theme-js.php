<?php

if (!is_admin()) add_action( 'wp_enqueue_scripts', 'rotary_add_javascript' );

function rotary_add_javascript( ) {

	wp_enqueue_script( 'modernizr', get_bloginfo('template_directory').'/includes/js/modernizr.custom.js', array( 'jquery' ) );
	
	wp_enqueue_script( 'cycle', get_bloginfo('template_directory').'/includes/js/jquery.cycle.all.js', array( 'jquery' ) );
	
	wp_enqueue_script( 'touch', get_bloginfo('template_directory').'/includes/js/jquery.touchwipe.min.js', array( 'jquery' ) );

	wp_enqueue_script( 'rotary', get_bloginfo('template_directory').'/includes/js/rotary-theme.js', array( 'jquery' ) );

}