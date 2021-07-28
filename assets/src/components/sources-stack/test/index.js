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
import SourcesStack from '..';

let container;

describe( 'SourcesStack', () => {
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
				<SourcesStack sources={ [] } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );

	it( 'renders DETAILS element with correct title based on the sources count', () => {
		act( () => {
			render(
				<SourcesStack
					sources={ [
						{ name: 'a' },
						{ name: 'b' },
						{ name: 'c' },
					] }
				/>,
				container,
			);
		} );

		expect( container.querySelector( 'details' ) ).not.toBeNull();

		expect( container.querySelector( 'summary' ) ).not.toBeNull();
		expect( container.querySelector( 'summary' ).textContent ).toBe( 'Sources stack (3)' );

		expect( container.querySelector( 'ol' ) ).not.toBeNull();
		expect( container.querySelectorAll( 'ol > li' ) ).toHaveLength( 3 );
	} );
} );
