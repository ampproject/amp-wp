/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useValidationErrorStateUpdates } from '../use-validation-error-state-updates';
import { BLOCK_VALIDATION_STORE_KEY, createStore } from '../store';

jest.mock( '@wordpress/data/build/components/use-select', () => {
	return () => ( {
		blockOrder: [],
		currentPost: { id: 1 },
		getBlock: () => null,
		validationErrorsFromPost: require( './__data__/raw-validation-errors' ).rawValidationErrors,
	} );
} );

createStore( {
	validationErrors: [],
} );

let container;

describe( 'useValidationErrorStateUpdates', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'updates state', () => {
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 0 );

		function ComponentContainingHook() {
			useValidationErrorStateUpdates();

			return null;
		}

		act( () => {
			render(
				<ComponentContainingHook />,
				container,
			);
		} );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 8 );
	} );
} );
