( function( $ ) {
	'use strict';

	wp.customize( 'amp_navbar_background', function( value ) {
		value.bind( function( to ) {
			$( '.title-bar' ).css( {
				'background': to
			} );
		} );
	} );

} )( jQuery );
