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
import { convertErrorSourcesToArray, maybeAddClientIdToValidationError, useValidationErrorStateUpdates } from '../use-validation-error-state-updates';
import { BLOCK_VALIDATION_STORE_KEY, createStore } from '../store';

jest.mock( '@wordpress/data/build/components/use-select', () => {
	return () => ( {
		currentPost: { id: 1 },
		getClientIdsWithDescendants: () => null,
		getBlock: () => null,
		getBlocks: () => [],
		isSavingPost: false,
		validationErrorsFromPost: require( './__data__/raw-validation-errors' ).rawValidationErrors,
	} );
} );

jest.mock( '@wordpress/api-fetch', () => () => new Promise( ( resolve ) => {
	resolve( { review_link: 'http://site.test/wp-admin', results: require( './__data__/raw-validation-errors' ).rawValidationErrors } );
} ) );

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

	it( 'updates state', async () => {
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

		// Wait for re-render that follows fetching results.
		await ( () => () => new Promise( ( resolve ) => {
			setTimeout( resolve );
		} ) )();

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 8 );
	} );
} );

describe( 'convertValidationErrorSourcesToArray', () => {
	it( 'converts an object to an array', () => {
		const validationError = {
			error: {
				sources: {
					0: 'error1',
					1: 'error2',
					3: 'error3',
				},
			},
		};

		convertErrorSourcesToArray( validationError );
		expect( validationError ).toMatchObject(
			{
				error: {
					sources: [ 'error1', 'error2', 'error3' ],
				},
			},
		);
	} );
} );

describe( 'maybeAddClientIdToValidationError', () => {
	it( 'does nothing if the source has no name or block index', () => {
		let testValidationError = {};

		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_name: 'my-block',
					post_id: 88,
				},
				currentPostId: 88,
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => ( { name: 'my-block' } ),
			},
		);

		expect( testValidationError ).toMatchObject( {} );

		testValidationError = {};
		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_content_index: 1,
					post_id: 88,
				},
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => ( { name: 'my-block' } ),
			},
		);
		expect( testValidationError ).toMatchObject( {} );
	} );

	it( 'does nothing if the source post ID doesn\'t match the validation error ID', () => {
		const testValidationError = {};

		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_name: 'my-block',
					block_content_index: 1,
					post_id: 88,
				},
				currentPostId: 77,
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => ( { name: 'my-block' } ),
			},
		);

		expect( testValidationError ).toMatchObject( {} );
	} );

	it( 'does nothing if the block index is not in the block order array', () => {
		const testValidationError = {};

		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_name: 'my-block',
					block_content_index: 3,
					post_id: 88,
				},
				currentPostId: 88,
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => ( { name: 'my-block' } ),
			},
		);

		expect( testValidationError ).toMatchObject( {} );
	} );

	it( 'does nothing if no block is found', () => {
		const testValidationError = {};

		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_name: 'my-block',
					block_content_index: 1,
					post_id: 88,
				},
				currentPostId: 88,
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => null,
			},
		);

		expect( testValidationError ).toMatchObject( {} );
	} );

	it( 'does nothing if the real block name doesn\'t match the source block name', () => {
		const testValidationError = {};

		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_name: 'my-block',
					block_content_index: 1,
					post_id: 88,
				},
				currentPostId: 88,
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => ( { name: 'some-other-block' } ),
			},
		);

		expect( testValidationError ).toMatchObject( {} );
	} );

	it( 'adds the client ID if there is a match', () => {
		const testValidationError = {};

		maybeAddClientIdToValidationError(
			{
				validationError: testValidationError,
				source: {
					block_name: 'my-block',
					block_content_index: 1,
					post_id: 88,
				},
				currentPostId: 88,
				blockOrder: [ 'client-id-1', 'client-id-2' ],
				getBlock: () => ( { name: 'my-block' } ),
			},
		);

		expect( testValidationError ).toMatchObject( { clientId: 'client-id-2' } );
	} );
} );
