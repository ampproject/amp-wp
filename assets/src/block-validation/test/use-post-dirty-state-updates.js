/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';
import { createReduxStore, dispatch, register, select, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { usePostDirtyStateChanges } from '../use-post-dirty-state-changes';
import { BLOCK_VALIDATION_STORE_KEY, createStore } from '../store';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/compose/build/hooks/use-debounce', () => ( fn ) => fn );

createStore( {
	isPostDirty: false,
} );

register( createReduxStore( 'test/use-post-dirty-state-updates', {
	reducer: ( state = {} ) => ( { ...state } ),
	actions: {
		change: () => ( { type: 'DUMMY' } ),
	},
} ) );

describe( 'usePostDirtyStateChanges', () => {
	let container = null;
	const getEditedPostContent = jest.fn().mockReturnValue( 'initial' );

	function ComponentContainingHook() {
		usePostDirtyStateChanges();

		return null;
	}

	function renderComponentContainingHook() {
		render( <ComponentContainingHook />, container );
	}

	function setupUseSelect( overrides ) {
		useSelect.mockImplementation( () => ( {
			getEditedPostContent,
			isSavingOrPreviewingPost: false,
			isPostDirty: select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty(),
			...overrides,
		} ) );
	}

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		unmountComponentAtNode( container );
		container.remove();
		container = null;
	} );

	it( 'sets dirty state when content changes and clears it after save', () => {
		// Initial render.
		act( () => {
			setupUseSelect();
			renderComponentContainingHook();
		} );

		// Trigger initial store change.
		act( () => {
			dispatch( 'test/use-post-dirty-state-updates' ).change();
		} );
		expect( getEditedPostContent ).toHaveBeenCalledWith();
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( false );

		// Change content - post should become dirty.
		act( () => {
			getEditedPostContent.mockClear();
			getEditedPostContent.mockReturnValue( 'foo' );
			dispatch( 'test/use-post-dirty-state-updates' ).change();
		} );
		expect( getEditedPostContent ).toHaveBeenCalledWith();
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );

		// Another content change should not trigger getEditedPostContent()
		act( () => {
			getEditedPostContent.mockClear();
			getEditedPostContent.mockReturnValue( 'bar' );
			dispatch( 'test/use-post-dirty-state-updates' ).change();
		} );
		expect( getEditedPostContent ).not.toHaveBeenCalledWith();
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );

		// Save post - dirty state should get cleared.
		act( () => {
			setupUseSelect( {
				isSavingOrPreviewingPost: true,
			} );

			// Component needs to be re-rendered if `useSelect` return value changed.
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( false );

		// Change content - getEditedPostContent() should be called again
		act( () => {
			setupUseSelect( {
				isSavingOrPreviewingPost: false,
			} );
			renderComponentContainingHook();
		} );
		act( () => {
			getEditedPostContent.mockClear();
			getEditedPostContent.mockReturnValue( 'baz' );
			dispatch( 'test/use-post-dirty-state-updates' ).change();
		} );
		expect( getEditedPostContent ).toHaveBeenCalledWith();
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );
	} );
} );
