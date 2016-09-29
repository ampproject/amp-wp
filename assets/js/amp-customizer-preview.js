( function( $ ) {
	'use strict';

	// Nav bar text color.
	wp.customize( 'amp_customizer[navbar_color]', function( value ) {
		value.bind( function( to ) {
			$( '.amp-wp-header a' ).css( 'color', to );
			$( '.amp-wp-header div' ).css( 'color', to );
			$( '.amp-wp-header .amp-wp-site-icon' ).css( 'border-color', to ).css( 'background-color', to );
		} );
	} );

	// Nav bar background color.
	wp.customize( 'amp_customizer[navbar_background_color]', function( value ) {
		value.bind( function( to ) {
			$( '.amp-wp-header' ).css( 'background-color', to );
			$( '.amp-wp-article a, .amp-wp-article a:visited, .amp-wp-footer a, .amp-wp-footer a:visited' ).css( 'color', to );
			$( 'blockquote, .amp-wp-byline amp-img' ).css( 'border-color', to );
		} );
	} );

	// Add custom-background-image body class when background image is added.
	wp.customize( 'amp_customizer[navbar_background_image]', function( value ) {
		value.bind( function( newVal, oldVal ) {
			if ( newVal ) {
				$( body ).addClass( 'amp-wp-has-header-image' );
				$( '.amp-wp-header' ).addClass( 'header-background-image' ).css( 'background-image', 'url(' + encodeURI( newVal ) + ')' );
			} else {
				$( body ).removeClass( 'amp-wp-has-header-image' );
				$( '.amp-wp-header' ).removeClass( 'header-background-image' ).css( 'background-image', 'none' );
			}
		} );
	} );

	// AMP background color scheme.
	wp.customize( 'amp_customizer[background_color]', function( value ) {
		value.bind( function( to ) {
			var scheme;

			if ( to === 'dark' ) {
				scheme = {
					theme_color: '#111',
					text_color: '#acacac',
					muted_text_color: '#606060',
					border_color: '#2b2b2b'
				};
			} else {
				scheme = {
					theme_color: '#fff',
					text_color: '#535353',
					muted_text_color: '#9f9f9f',
					border_color: '#d4d4d4'
				};
			}

			$( 'body' ).css( 'background-color', scheme.theme_color );
			$( 'body, a:hover, a:active, a:focus, blockquote, .amp-wp-article, .amp-wp-title' ).css( 'color', scheme.text_color );
			$( '.amp-wp-meta, .wp-caption .wp-caption-text, .amp-wp-tax-category, .amp-wp-tax-tag, .amp-wp-footer p' ).css( 'color', scheme.muted_text_color );
			$( '.amp-wp-article-featured-image amp-img, .amp-wp-article- amp-img, .wp-caption .wp-caption-text, .amp-wp-comments-link a, .amp-wp-footer' ).css( 'border-color', scheme.border_color );
			$( '.amp-wp-iframe-placeholder, amp-carousel, amp-iframe, amp-youtube, amp-instagram, amp-vine' ).css( 'background-color', scheme.border_color );
		} );
	} );

} )( jQuery );
