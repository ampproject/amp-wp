/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import DebugOptions from './components/debug-options';

domReady( () => {
	const debuggingSubmenu = document.querySelector( '#wp-admin-bar-amp-debug' );

	if ( debuggingSubmenu ) {
		const subWrapper = document.createElement( 'div' );
		subWrapper.setAttribute( 'class', 'ab-sub-wrapper' );
		debuggingSubmenu.appendChild( subWrapper );

		render(
			<DebugOptions />,
			subWrapper
		);
	}
} );
