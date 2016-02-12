( function( $ ) {
	'use strict';

	// Nav bar background color.
	wp.customize( 'amp_navbar_background', function( value ) {
		value.bind( function( to ) {
			$( 'nav.title-bar' ).css( 'background', to );
		} );
	} );

} )( jQuery );
