/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useErrorsFetchingStateChanges } from '../use-errors-fetching-state-changes';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );

describe( 'useErrorsFetchingStateChanges', () => {
	let container = null;
	let returnValue = {};

	function ComponentContainingHook() {
		returnValue = useErrorsFetchingStateChanges();

		return null;
	}

	function setupAndRender( overrides ) {
		useSelect.mockImplementation( () => ( {
			isEditedPostNew: false,
			isFetchingErrors: false,
			...overrides,
		} ) );

		render( <ComponentContainingHook />, container );
	}

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

	it( 'returns no loading message when errors are not being fetched', () => {
		act( () => {
			setupAndRender( {
				isFetchingErrors: false,
			} );
		} );

		expect( returnValue ).toMatchObject( {
			isFetchingErrors: false,
			fetchingErrorsMessage: '',
		} );
	} );

	it( 'returns correct status message when a new post is validated', () => {
		act( () => {
			setupAndRender( {
				isEditedPostNew: true,
				isFetchingErrors: false,
			} );
		} );

		expect( returnValue ).toMatchObject( {
			isFetchingErrors: false,
			fetchingErrorsMessage: expect.stringContaining( 'Validating' ),
		} );
	} );

	it( 'returns correct message when fetching errors and re-validating', () => {
		act( () => {
			setupAndRender( {
				isFetchingErrors: true,
			} );
		} );

		expect( returnValue ).toMatchObject( {
			isFetchingErrors: true,
			fetchingErrorsMessage: expect.stringContaining( 'Loading' ),
		} );

		// Simulate state change so that the message is changed.
		act( () => {
			setupAndRender( {
				isFetchingErrors: false,
			} );
		} );

		expect( returnValue ).toMatchObject( {
			isFetchingErrors: false,
			fetchingErrorsMessage: expect.stringContaining( 'Re-validating' ),
		} );
	} );
} );
