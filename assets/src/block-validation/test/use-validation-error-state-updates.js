/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { select, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { maybeAddClientIdToValidationError, useValidationErrorStateUpdates } from '../use-validation-error-state-updates';
import { BLOCK_VALIDATION_STORE_KEY, createStore } from '../store';

// This allows us to tweak the returned value on each test
jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );

jest.mock( '@wordpress/api-fetch', () => () => new Promise( ( resolve ) => {
	resolve( { review_link: 'http://site.test/wp-admin', results: require( './__data__/raw-validation-errors' ).rawValidationErrors } );
} ) );

createStore( {
	validationErrors: [],
} );

describe( 'useValidationErrorStateUpdates', () => {
	let container;

	function ComponentContainingHook() {
		useValidationErrorStateUpdates();

		return null;
	}

	function renderComponentContainingHook() {
		render( <ComponentContainingHook />, container );
	}

	function setupUseSelect( overrides ) {
		useSelect.mockImplementation( () => ( {
			currentPost: { id: 1 },
			getClientIdsWithDescendants: () => null,
			getBlock: () => null,
			getBlocks: () => [],
			isAutosavingPost: false,
			isPreviewingPost: false,
			isSavingPost: false,
			validationErrorsFromPost: require( './__data__/raw-validation-errors' ).rawValidationErrors,
			...overrides,
		} ) );
	}

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'does not trigger validation on an autosave', async () => {
		// Initial render should trigger validation.
		setupUseSelect( {
			isAutosavingPost: true,
			isSavingPost: true,
		} );
		act( renderComponentContainingHook );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 0 );

		// Wait for re-render that follows fetching results.
		await ( () => () => new Promise( ( resolve ) => {
			setTimeout( resolve );
		} ) )();

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 0 );
	} );

	it( 'triggers validation on a regular save', async () => {
		setupUseSelect();
		act( renderComponentContainingHook );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 0 );

		// Wait for re-render that follows fetching results.
		await ( () => () => new Promise( ( resolve ) => {
			setTimeout( resolve );
		} ) )();

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 8 );
	} );

	it( 'triggers validation on a preview request', async () => {
		// At this stage, internal flags should have been set.
		setupUseSelect( {
			isPreviewingPost: true,
			isAutosavingPost: true,
			isSavingPost: true,
		} );
		act( renderComponentContainingHook );

		// Wait for re-render that follows fetching results.
		await ( () => () => new Promise( ( resolve ) => {
			setTimeout( resolve );
		} ) )();

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 0 );

		// When the post save is complete but the preview link is invalid, bail.
		setupUseSelect( {
			isPreviewingPost: false,
			isAutosavingPost: false,
			isSavingPost: false,
			previewLink: 'invalid-url',
		} );
		act( renderComponentContainingHook );

		// Wait for re-render that follows fetching results.
		await ( () => () => new Promise( ( resolve ) => {
			setTimeout( resolve );
		} ) )();

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 0 );

		// When the preview link is correct, validation should be triggered.
		setupUseSelect( {
			isPreviewingPost: false,
			isAutosavingPost: false,
			isSavingPost: false,
			previewLink: 'http://site.test/?p=1&preview=1&preview_id=1&preview_nonce=foobar',
		} );
		act( renderComponentContainingHook );

		// Wait for re-render that follows fetching results.
		await ( () => () => new Promise( ( resolve ) => {
			setTimeout( resolve );
		} ) )();

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 8 );
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
