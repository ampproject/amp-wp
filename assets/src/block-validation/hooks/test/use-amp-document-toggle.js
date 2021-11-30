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
	let returnValue = {};

	const editPost = jest.fn();

	function ComponentContainingHook() {
		returnValue = useAMPDocumentToggle();

		return null;
	}

	function setupAndRender( isAMPEnabled ) {
		useSelect.mockReturnValue( isAMPEnabled || false );

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
		returnValue = {};
	} );

	it( 'returns AMP document enable state', () => {
		act( () => {
			setupAndRender( false );
		} );
		expect( returnValue.isAMPEnabled ).toBe( false );

		act( () => {
			setupAndRender( true );
		} );
		expect( returnValue.isAMPEnabled ).toBe( true );
	} );

	it( 'toggleAMP disables AMP is it was enabled', () => {
		act( () => {
			setupAndRender( true );
		} );

		returnValue.toggleAMP();
		expect( editPost ).toHaveBeenCalledWith( { amp_enabled: false } );
	} );

	it( 'toggleAMP enables AMP is it was disabled', () => {
		act( () => {
			setupAndRender( false );
		} );

		returnValue.toggleAMP();
		expect( editPost ).toHaveBeenCalledWith( { amp_enabled: true } );
	} );
} );
