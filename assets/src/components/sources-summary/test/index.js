/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * Internal dependencies
 */
import SourcesSummary from '..';

let container;

describe( 'SourcesSummary', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders nothing if no sources array is provided', () => {
		act( () => {
			render(
				<SourcesSummary sources={ [] } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );
} );
