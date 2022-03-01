/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useAMPDocumentToggle } from '../use-amp-document-toggle';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/data/build/components/use-dispatch/use-dispatch', () => jest.fn() );

describe( 'useAMPDocumentToggle', () => {
	let container = null;

	const editPost = jest.fn();

	function ComponentContainingHook() {
		const { isAMPEnabled, toggleAMP } = useAMPDocumentToggle();

		return (
			<button onClick={ toggleAMP }>
				{ isAMPEnabled ? 'enabled' : 'disabled' }
			</button>
		);
	}

	function setupAndRender( isAMPEnabled ) {
		useSelect.mockReturnValue( isAMPEnabled );

		render( <ComponentContainingHook />, container );
	}

	beforeAll( () => {
		useDispatch.mockImplementation( () => ( { editPost } ) );
	} );

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		unmountComponentAtNode( container );
		container.remove();
		container = null;
	} );

	it( 'returns AMP document enable state', () => {
		act( () => {
			setupAndRender( false );
		} );
		expect( container.querySelector( 'button' ).textContent ).toBe( 'disabled' );

		act( () => {
			setupAndRender( true );
		} );
		expect( container.querySelector( 'button' ).textContent ).toBe( 'enabled' );
	} );

	it( 'toggleAMP disables AMP is it was enabled', () => {
		act( () => {
			setupAndRender( true );
			container.querySelector( 'button' ).click();
		} );

		expect( editPost ).toHaveBeenCalledWith( { amp_enabled: false } );
	} );

	it( 'toggleAMP enables AMP is it was disabled', () => {
		act( () => {
			setupAndRender( false );
			container.querySelector( 'button' ).click();
		} );

		expect( editPost ).toHaveBeenCalledWith( { amp_enabled: true } );
	} );
} );
