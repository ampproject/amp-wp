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
import AMPValidationStatusNotification from '../status-notification';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/data/build/components/use-dispatch/use-dispatch', () => jest.fn() );

describe( 'AMPValidationStatusNotification', () => {
	let container;

	const autosave = jest.fn();
	const savePost = jest.fn();

	useDispatch.mockImplementation( () => ( { autosave, savePost } ) );

	function setupUseSelect( overrides ) {
		useSelect.mockImplementation( () => ( {
			ampCompatibilityBroken: false,
			fetchingErrorsRequestErrorMessage: '',
			hasValidationErrors: false,
			isDraft: false,
			isEditedPostNew: false,
			isFetchingErrors: false,
			reviewLink: 'http://example.com',
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

	it( 'renders message when there are no AMP validation errors', () => {
		setupUseSelect();

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'All issues reviewed or removed' );
	} );

	it( 'does not render when errors are being fetched', () => {
		setupUseSelect( {
			isFetchingErrors: true,
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.children ).toHaveLength( 0 );
	} );

	it( 'renders error message when API request error is present', () => {
		setupUseSelect( {
			fetchingErrorsRequestErrorMessage: 'request error message',
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'request error message' );
		expect( container.querySelector( 'a[href="http://example.com"]' ) ).toBeNull();

		container.querySelector( 'button' ).click();
		expect( autosave ).toHaveBeenCalledWith( { isPreview: true } );

		setupUseSelect( {
			isDraft: true,
			fetchingErrorsRequestErrorMessage: 'request error message',
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );
		container.querySelector( 'button' ).click();
		expect( savePost ).toHaveBeenCalledWith( { isPreview: true } );
	} );

	it( 'renders error message when AMP compatibility is broken', () => {
		setupUseSelect( {
			ampCompatibilityBroken: true,
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'validation issues marked kept' );
		expect( container.querySelector( 'a[href="http://example.com"]' ) ).not.toBeNull();
	} );

	it( 'renders message when there are AMP validation errors', () => {
		setupUseSelect( {
			hasValidationErrors: true,
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'issues needs review' );
		expect( container.querySelector( 'a[href="http://example.com"]' ) ).not.toBeNull();
	} );

	it( 'renders message when there are no errors and post is new', () => {
		setupUseSelect( {
			isEditedPostNew: true,
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'issues will be checked for when the post is saved' );
		expect( container.querySelector( 'a[href="http://example.com"]' ) ).toBeNull();
	} );
} );

