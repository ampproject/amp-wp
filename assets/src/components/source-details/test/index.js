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
import SourceDetails from '..';

let container;

describe( 'SourceDetails', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders nothing if no source is provided', () => {
		act( () => {
			render(
				<SourceDetails source={ {} } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );
} );
