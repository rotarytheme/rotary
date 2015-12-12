/**
 * Every Calendar +1 WordPress Plugin
 *
 * Color Picker Init
 */
jQuery( document ).ready( function( $ ) {
	var _myColor = '#FFFFFF';
	$( 'div.color_selector > div' ).each( function() {
		_myColor = $( this ).parent().children( 'input' ).first().val();
		var id = $( this ).attr( 'id' );
		if ( typeof id == 'undefined' || '' == id )
			$( this ) .attr( 'id', 'cptarget_' + parseInt(Math.random() * 1000 + 345) );

		$( this ).ColorPicker( {
			color: _myColor,
			onShow: function( cp ) { $( cp ).fadeIn( 250 ); return false; },
			onHide: function( cp ) { $( cp ).fadeOut( 250 ); return false; },
			onChange: function( hsb, hex, rgb ) {
				var e = $( $( this ).data( 'colorpicker' ).el );
				e.children( 'div._eCS' ).css( {backgroundColor:'#' + hex} );
				e.parent().children( 'input' ).val( '#' + hex );
			}
		} );
	} );

	$( 'a.ecp1_ex_rm' ).click( function() {
		var id = $( this ).attr( 'id' );
		id = id.split( '_' );
		id = id[id.length-1];
		id = '#ecp1_ex_' + id;
		$( id ).remove();
	} );
} );
