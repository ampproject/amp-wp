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
	render(
		<Container />,
		document.getElementById( 'amp-qa-tester-build-selector' )
	);
} );
