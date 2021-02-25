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
import AMPRevalidateNotification from '../revalidate-notification';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/data/build/components/use-dispatch/use-dispatch', () => jest.fn() );

describe( 'AMPRevalidateNotification', () => {
	let container;

	const autosave = jest.fn();
	const savePost = jest.fn();

	useDispatch.mockImplementation( () => ( { autosave, savePost } ) );

	function setupUseSelect( overrides ) {
		useSelect.mockImplementation( () => ( {
			hasActiveMetaboxes: false,
			isDraft: false,
			isFetchingErrors: false,
			isPostDirty: false,
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

	it( 'does not render revalidate message if post is not dirty', () => {
		setupUseSelect();

		act( () => {
			render( <AMPRevalidateNotification />, container );
		} );

		expect( container.children ).toHaveLength( 0 );
	} );

	it( 'does not render when errors are being fetched', () => {
		setupUseSelect( {
			isFetchingErrors: true,
		} );

		act( () => {
			render( <AMPRevalidateNotification />, container );
		} );

		expect( container.children ).toHaveLength( 0 );
	} );

	it( 'renders revalidate message if post is dirty', () => {
		setupUseSelect( {
			isPostDirty: true,
		} );

		act( () => {
			render( <AMPRevalidateNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.children ).toHaveLength( 1 );
		expect( container.innerHTML ).toContain( 'has changed' );
		expect( container.querySelector( 'svg' ) ).not.toBeNull();
		expect( container.querySelector( 'button' ).textContent ).toContain( 'Re-validate' );

		container.querySelector( 'button' ).click();
		expect( autosave ).toHaveBeenCalledWith( { isPreview: true } );
	} );

	it( 'renders revalidate message if draft post is dirty', () => {
		setupUseSelect( {
			isDraft: true,
			isPostDirty: true,
		} );

		act( () => {
			render( <AMPRevalidateNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'has changed' );
		expect( container.querySelector( 'button' ).textContent ).toContain( 'Save draft' );

		container.querySelector( 'button' ).click();
		expect( savePost ).toHaveBeenCalledWith( { isPreview: true } );
	} );

	it( 'always revalidate status message if there are active meta boxes', () => {
		setupUseSelect( {
			isDraft: false,
			isPostDirty: false,
			hasActiveMetaboxes: true,
		} );

		act( () => {
			render( <AMPRevalidateNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.children ).toHaveLength( 1 );
		expect( container.innerHTML ).toContain( 'may have changed' );
		expect( container.querySelector( 'button' ) ).not.toBeNull();
	} );
} );

