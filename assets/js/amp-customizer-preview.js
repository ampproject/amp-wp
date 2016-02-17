( function( wp, $ ) {
	'use strict';

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var api = wp.customize;

	//api.bind( 'ready', function() {
	//	wp.customize.panel.expanded = false;
	//	console.log( this.parentWindow );
	//} );

	// Nav bar text color.
	api( 'amp_navbar_color', function( value ) {
		value.bind( function( to ) {
			$( 'nav.title-bar a' ).css( 'color', to );
			$( 'nav.title-bar div' ).css( 'color', to );
		} );
	} );

	// Nav bar background color.
	api( 'amp_navbar_background', function( value ) {
		value.bind( function( to ) {
			$( 'nav.title-bar' ).css( 'background', to );
		} );
	} );

	// Nav bar site icon.
	api( 'site_icon', function( value ) {
		value.bind( function( to ) {

			var ampSiteIcon = $( '.site-icon' ),
				siteIcon    = $( '.site-icon > img' );

			if ( '' === to ) {
				ampSiteIcon.addClass( 'hidden' );
			} else {
				var request = wp.ajax.post( 'get-attachment', {
					id: to
				} ).done( function( response ) {
					ampSiteIcon.removeClass( 'hidden' );

					ampSiteIcon.attr( 'src', response.url );
					siteIcon.attr( 'src', response.url );
				} );
			}
		} );
	} );
} )( window.wp, jQuery );
