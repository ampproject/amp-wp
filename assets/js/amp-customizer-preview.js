( function( $ ) {
	'use strict';

	// Nav bar text color.
	wp.customize( 'amp_navbar_color', function( value ) {
		value.bind( function( to ) {
			$( '.amp-wp-header a' ).css( 'color', to );
			$( '.amp-wp-header div' ).css( 'color', to );
			$( '.amp-wp-header .amp-wp-site-icon' ).css( 'border-color', to ).css( 'background-color', to );
		} );
	} );

	// Nav bar background color.
	wp.customize( 'amp_navbar_background_color', function( value ) {
		value.bind( function( to ) {
			$( '.amp-wp-header' ).css( 'background-color', to );
			$( '.amp-wp-article a, .amp-wp-article a:visited, .amp-wp-footer a, .amp-wp-footer a:visited' ).css( 'color', to );
			$( 'blockquote, .amp-wp-byline amp-img' ).css( 'border-color', to );
		} );
	} );

	// Add custom-background-image body class when background image is added.
	wp.customize( 'amp_navbar_background_image', function( value ) {
		value.bind( function( newVal, oldVal ) {
			if ( newVal ) {
				newVal = encodeURI( newVal );
				$( '.amp-wp-header' ).addClass( 'header-background-image' ).css( 'background-image', 'url(' + newVal + ')' );
			} else {
				$( '.amp-wp-header' ).removeClass( 'header-background-image' ).css( 'background-image', 'none' );
			}
		} );
	} );

	// AMP background color scheme.
	wp.customize( 'amp_background_color', function( value ) {
		value.bind( function( to ) {

			// Light
			if ( to == 'light' ) {

				/*
					$theme_color      = '#fff';
					$text_color       = '#535353';
					$muted_text_color = '#9f9f9f';
					$border_color     = '#d4d4d4';
				*/

				$( 'body' ).css( 'background-color', '#ffffff' );
				$( 'body, a:hover, a:active, a:focus, blockquote, .amp-wp-article, .amp-wp-title' ).css( 'color', '#535353' );
				$( '.amp-wp-meta, .wp-caption .wp-caption-text, .amp-wp-tax-category, .amp-wp-tax-tag, .amp-wp-footer p' ).css( 'color', '#9f9f9f' );
				$( '.amp-wp-article-featured-image amp-img, .amp-wp-article-content amp-img, .wp-caption .wp-caption-text, .amp-wp-comment-link a, .amp-wp-footer' ).css( 'border-color', '#d4d4d4' );
				$( '.amp-wp-iframe-placeholder, amp-carousel, amp-iframe, amp-youtube, amp-instagram, amp-vine' ).css( 'background-color', '#d4d4d4' );

			// Dark
			} else if ( to == 'dark' ) {

				/*
					$theme_color      = '#111';
					$text_color       = '#acacac';
					$muted_text_color = '#606060';
					$border_color     = '#2b2b2b';
				*/

				$( 'body' ).css( 'background-color', '#111111' );
				$( 'body, a:hover, a:active, a:focus, blockquote, .amp-wp-article, .amp-wp-title' ).css( 'color', '#acacac' );
				$( '.amp-wp-meta, .wp-caption .wp-caption-text, .amp-wp-tax-category, .amp-wp-tax-tag, .amp-wp-footer p' ).css( 'color', '#606060' );
				$( '.amp-wp-article-featured-image amp-img, .amp-wp-article-content amp-img, .wp-caption .wp-caption-text, .amp-wp-comment-link a, .amp-wp-footer' ).css( 'border-color', '#2b2b2b' );
				$( '.amp-wp-iframe-placeholder, amp-carousel, amp-iframe, amp-youtube, amp-instagram, amp-vine' ).css( 'background-color', '#2b2b2b' );

			// Default
			} else {

				/*
					$theme_color      = '#fff';
					$text_color       = '#3d596d';
					$muted_text_color = '#87A6BC';
					$border_color     = '#c8d7e1';
				*/

				$( 'body' ).css( 'background-color', '#ffffff' );
				$( 'body, a:hover, a:active, a:focus, blockquote, .amp-wp-article, .amp-wp-title' ).css( 'color', '#3d596d' );
				$( '.amp-wp-meta, .wp-caption .wp-caption-text, .amp-wp-tax-category, .amp-wp-tax-tag, .amp-wp-footer p' ).css( 'color', '#87A6BC' );
				$( '.amp-wp-article-featured-image amp-img, .amp-wp-article-content amp-img, .wp-caption .wp-caption-text, .amp-wp-comment-link a, .amp-wp-footer' ).css( 'border-color', '#c8d7e1' );
				$( '.amp-wp-iframe-placeholder, amp-carousel, amp-iframe, amp-youtube, amp-instagram, amp-vine' ).css( 'background-color', '#c8d7e1' );

			}
		} );
	} );

} )( jQuery );
