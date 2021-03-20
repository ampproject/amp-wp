/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import Container from './components/container';

domReady( () => {
	const root = document.getElementById( 'amp-qa-tester-build-selector' );
	/* eslint-disable-next-line prettier/prettier */
	const adminBarItem = document.getElementById( 'wp-admin-bar-amp-qa-tester' );

	if ( root ) {
		render( <Container />, root );
	}

	// Allow submenu to be focused so that it will stay open when configuring build to activate.
	adminBarItem.setAttribute( 'tabindex', '0' );
} );
