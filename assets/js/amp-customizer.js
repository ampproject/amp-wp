( function( $ ) {
	'use strict';

	wp.customize( 'amp_navbar_background', function( value ) {
		value.bind( function( to ) {
			var titleBar = $( '.amp-mode-mouse' ).find( 'nav.title-bar' );

			titleBar.css( 'background', to );
		} );
	} );

} )( jQuery );
