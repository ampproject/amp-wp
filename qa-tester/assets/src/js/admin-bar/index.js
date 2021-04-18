/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { css_url as cssUrl } from 'amp-qa-tester-data';
import Container from './components/container';

domReady( () => {
	const root = document.getElementById( 'amp-qa-tester-build-selector' );
	if ( ! root ) {
		return;
	}

	/* eslint-disable-next-line prettier/prettier */
	const adminBarItem = document.getElementById( 'wp-admin-bar-amp-qa-tester' );
	const shadow = root.attachShadow( { mode: 'open' } );

	render( <Container />, shadow );

	// Load the stylesheet for the shadow DOM.
	const linkElement = document.createElement( 'link' );
	linkElement.setAttribute( 'rel', 'stylesheet' );
	linkElement.setAttribute( 'href', cssUrl );
	shadow.appendChild( linkElement );

	// Allow submenu to be focused so that it will stay open when configuring build to activate.
	adminBarItem.setAttribute( 'tabindex', '0' );
} );
