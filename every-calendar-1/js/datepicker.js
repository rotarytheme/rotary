/**
 * Every Calendar +1 WordPress Plugin DatePicker init script
 */
jQuery( document ).ready( function( $ ) {
	$( "div.ecp1_meta .ecp1_datepick" ).datepicker( {
		dateFormat: 'yy-mm-dd',
		showOn: 'focus',
		numberOfMonths: 3
	} );
} );
