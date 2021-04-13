/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';

/**
 * Internal dependencies
 */
import AMPToggle from '../index';
import { useAMPDocumentToggle } from '../../../hooks/use-amp-document-toggle';

jest.mock( '../../../hooks/use-amp-document-toggle', () => ( { useAMPDocumentToggle: jest.fn() } ) );

describe( 'AMPToggle', () => {
	let container;

	const toggleAMP = jest.fn();

	function setupHooks( overrides ) {
		useAMPDocumentToggle.mockImplementation( () => ( {
			isAMPEnabled: false,
			toggleAMP,
			...overrides,
		} ) );
	}

	beforeEach( () => {
		jest.clearAllMocks();

		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		unmountComponentAtNode( container );
		container.remove();
		container = null;
	} );

	it( 'renders a toggle that reacts to changes', () => {
		setupHooks( {
			isAMPEnabled: true,
		} );

		act( () => {
			render( <AMPToggle />, container );
		} );

		expect( container.querySelector( 'input[type="checkbox"]' ) ).not.toBeNull();
		expect( container.querySelector( 'input[type="checkbox"]' ).checked ).toBe( true );

		container.querySelector( 'input[type="checkbox"]' ).click();
		expect( toggleAMP ).toHaveBeenCalledTimes( 1 );
	} );
} );

