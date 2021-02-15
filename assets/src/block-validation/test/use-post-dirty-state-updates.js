/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { dispatch, select, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { usePostDirtyStateChanges } from '../use-post-dirty-state-changes';
import { BLOCK_VALIDATION_STORE_KEY, createStore } from '../store';

// This allows us to tweak the returned value on each test
jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/compose/build/hooks/use-debounce', () => ( fn ) => fn );

createStore( {
	isPostDirty: false,
} );

describe( 'usePostDirtyStateChanges', () => {
	let container;

	function ComponentContainingHook() {
		usePostDirtyStateChanges();

		return null;
	}

	function renderComponentContainingHook() {
		render( <ComponentContainingHook />, container );
	}

	function setupUseSelect( overrides ) {
		useSelect.mockImplementation( () => ( {
			getEditedPostContent: () => '',
			isSavingOrPreviewingPost: false,

			// We want to use an actual value from the block validation store.
			isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),

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

	it( 'does not clear dirty state if post has not been saved', () => {
		setupUseSelect();

		act( () => {
			dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsPostDirty( false );
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( false );

		act( () => {
			dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsPostDirty( true );
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );
	} );

	it( 'clears dirty state if post has been saved', () => {
		setupUseSelect();

		act( () => {
			dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsPostDirty( true );
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );

		setupUseSelect( {
			isSavingOrPreviewingPost: true,
		} );

		act( () => {
			dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsPostDirty( true );
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( false );
	} );
} );
