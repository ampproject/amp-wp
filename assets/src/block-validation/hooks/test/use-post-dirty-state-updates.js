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
import { BLOCK_VALIDATION_STORE_KEY, createStore } from '../../store';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/compose/build/hooks/use-debounce', () => ( fn ) => fn );

describe( 'usePostDirtyStateChanges', () => {
	let container = null;
	const getEditedPostContent = jest.fn();

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

	beforeAll( () => {
		createStore( {
			isPostDirty: false,
		} );

		register( createReduxStore( 'test/use-post-dirty-state-updates', {
			reducer: ( state = {} ) => ( { ...state } ),
			actions: {
				change: () => ( { type: 'DUMMY' } ),
			},
		} ) );
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

	it( 'sets dirty state when content changes and clears it after save', () => {
		// Initial render.
		getEditedPostContent.mockReturnValue( 'initial' );
		setupUseSelect();
		act( () => {
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( false );

		// Change content - post should become dirty.
		getEditedPostContent.mockReturnValue( 'foo' );
		act( () => {
			dispatch( 'test/use-post-dirty-state-updates' ).change();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );

		// Save post - dirty state should get cleared.
		setupUseSelect( {
			isSavingOrPreviewingPost: true,
		} );
		act( () => {
			renderComponentContainingHook();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( false );

		// Change content - getEditedPostContent() should be called again
		getEditedPostContent.mockReturnValue( 'baz' );
		setupUseSelect( {
			isSavingOrPreviewingPost: false,
		} );
		act( () => {
			renderComponentContainingHook();
		} );
		act( () => {
			dispatch( 'test/use-post-dirty-state-updates' ).change();
		} );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsPostDirty() ).toBe( true );
	} );
} );
