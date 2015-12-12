<?php
/**
 * Every Calendar +1 Plugin Widget Registration
 */

// Make sure we're included from within the plugin
require( ECP1_DIR . '/includes/check-ecp1-defined.php' );

// Array that will be iterated over to load all the widgets
$ECP1_WIDGETS = array(
	// filename -> class name
	'title-list' => 'ECP1_TitleListWidget'
);

// Loop over the files and load the widget classes
foreach ( $ECP1_WIDGETS as $f=>$c ) {
	include_once( ECP1_DIR . '/widgets/' . $f . '.php' );
}

// Function that will register all of the widgets
function ecp1_register_widgets() {
	global $ECP1_WIDGETS;
	foreach ( $ECP1_WIDGETS as $f=>$c )
		register_widget( $c );
}

// Create an action hook to register the widgets
add_action( 'widgets_init', 'ecp1_register_widgets' );

// Don't close the php interpreter
/*?>*/
