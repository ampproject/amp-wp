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
import AMPValidationStatusNotification from '../status-notification';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );

describe( 'AMPValidationStatusNotification', () => {
	let container;

	function setupUseSelect( overrides ) {
		useSelect.mockImplementation( () => ( {
			ampCompatibilityBroken: false,
			hasValidationErrors: false,
			isEditedPostNew: false,
			isFetchingErrors: false,
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

	it( 'renders message when there are no AMP validation errors', () => {
		setupUseSelect();

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'All issues reviewed or removed' );
		expect( container.querySelector( '.is-error' ) ).toBeNull();
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

	it( 'renders error message when AMP compatibility is broken', () => {
		setupUseSelect( {
			ampCompatibilityBroken: true,
		} );

		act( () => {
			render( <AMPValidationStatusNotification />, container );
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.innerHTML ).toContain( 'validation issues marked kept' );
		expect( container.querySelector( '.is-error' ) ).not.toBeNull();
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
		expect( container.querySelector( '.is-error' ) ).toBeNull();
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
		expect( container.querySelector( '.is-error' ) ).toBeNull();
	} );
} );

