( function( $ ) {
	'use strict';

	// Nav bar text color.
	wp.customize( 'amp_customizer[header_color]', function( value ) {
		value.bind( function( to ) {
			$( '.amp-wp-header a' ).css( 'color', to );
			$( '.amp-wp-header div' ).css( 'color', to );
			$( '.amp-wp-header .amp-wp-site-icon' ).css( 'border-color', to ).css( 'background-color', to );
		} );
	} );

	// Nav bar background color.
	wp.customize( 'amp_customizer[header_background_color]', function( value ) {
		value.bind( function( to ) {
			$( '.amp-wp-header' ).css( 'background-color', to );
			$( '.amp-wp-article a, .amp-wp-article a:visited, .amp-wp-footer a, .amp-wp-footer a:visited' ).css( 'color', to );
			$( 'blockquote, .amp-wp-byline amp-img' ).css( 'border-color', to );
		} );
	} );

	// AMP background color scheme.
	wp.customize( 'amp_customizer[color_scheme]', function( value ) {
		value.bind( function( to ) {
			var colors;

			// TODO: pull these values from AMP_Customizer_Settings
			if ( to === 'dark' ) {
				colors = {
					theme_color: '#0a0a0a',
					text_color: '#dedede',
					muted_text_color: '#b1b1b1',
					border_color: '#707070'
				};
			} else {
				colors = {
					theme_color: '#fff',
					text_color: '#535353',
					muted_text_color: '#9f9f9f',
					border_color: '#d4d4d4'
				};
			}

			$( 'body' ).css( 'background-color', colors.theme_color );
			$( 'body, a:hover, a:active, a:focus, blockquote, .amp-wp-article, .amp-wp-title' ).css( 'color', colors.text_color );
			$( '.amp-wp-meta, .wp-caption .wp-caption-text, .amp-wp-tax-category, .amp-wp-tax-tag, .amp-wp-footer p' ).css( 'color', colors.muted_text_color );
			$( '.wp-caption .wp-caption-text, .amp-wp-comments-link a, .amp-wp-footer' ).css( 'border-color', colors.border_color );
			$( '.amp-wp-iframe-placeholder, amp-carousel, amp-iframe, amp-youtube, amp-instagram, amp-vine' ).css( 'background-color', colors.border_color );
		} );
	} );

} )( jQuery );
