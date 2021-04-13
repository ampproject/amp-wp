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
import AMPDocumentStatusNotification from '../index';
import { useAMPDocumentToggle } from '../../../hooks/use-amp-document-toggle';
import { useErrorsFetchingStateChanges } from '../../../hooks/use-errors-fetching-state-changes';

jest.mock( '@wordpress/data/build/components/use-select', () => jest.fn() );
jest.mock( '@wordpress/data/build/components/use-dispatch/use-dispatch', () => jest.fn() );
jest.mock( '../../../hooks/use-amp-document-toggle', () => ( { useAMPDocumentToggle: jest.fn() } ) );
jest.mock( '../../../hooks/use-errors-fetching-state-changes', () => ( { useErrorsFetchingStateChanges: jest.fn() } ) );

describe( 'AMPDocumentStatusNotification', () => {
	let container;

	const openGeneralSidebar = jest.fn();
	const closePublishSidebar = jest.fn();

	useDispatch.mockImplementation( () => ( {
		openGeneralSidebar,
		closePublishSidebar,
	} ) );

	function setupHooks(
		useSelectOverrides = {},
		useErrorsFetchingStateChangesOverrides = {},
		useAMPDocumentToggleOverrides = {},
	) {
		useSelect.mockImplementation( () => ( {
			isPostDirty: false,
			maybeIsPostDirty: false,
			keptMarkupValidationErrorCount: 0,
			unreviewedValidationErrorCount: 0,
			...useSelectOverrides,
		} ) );

		useErrorsFetchingStateChanges.mockImplementation( () => ( {
			isFetchingErrors: false,
			fetchingErrorsMessage: '',
			...useErrorsFetchingStateChangesOverrides,
		} ) );

		useAMPDocumentToggle.mockImplementation( () => ( {
			isAMPEnabled: true,
			...useAMPDocumentToggleOverrides,
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

	it( 'renders only a toggle if AMP is disabled', () => {
		setupHooks( {}, {}, {
			isAMPEnabled: false,
		} );

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.children ).toHaveLength( 1 );
		expect( container.innerHTML ).toContain( 'Enable AMP' );
	} );

	it( 'renders a loading spinner when errors are being fetched', () => {
		setupHooks( {}, {
			isFetchingErrors: true,
			fetchingErrorsMessage: 'Loading',
		} );

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.innerHTML ).toContain( 'Enable AMP' );
		expect( container.querySelector( '.amp-spinner-container' ) ).not.toBeNull();
		expect( container.innerHTML ).toContain( 'Loading' );
	} );

	it( 'renders a correct message if a post content is or may be dirty', () => {
		setupHooks( {
			isPostDirty: true,
		} );

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.innerHTML ).toContain( 'Enable AMP' );
		expect( container.innerHTML ).toContain( 'Content has changed.' );
		expect( container.querySelector( 'svg' ) ).not.toBeNull();
		expect( container.querySelector( 'button' ).textContent ).toContain( 'Open' );

		// Post may be dirty case.
		setupHooks( {
			maybeIsPostDirty: true,
		} );

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.innerHTML ).toContain( 'Content may have changed.' );

		// Simulate button click.
		container.querySelector( 'button' ).click();
		expect( openGeneralSidebar ).toHaveBeenCalledTimes( 1 );
		expect( closePublishSidebar ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'renders a correct message if there are kept markup errors', () => {
		setupHooks( {
			keptMarkupValidationErrorCount: 3,
		} );

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.innerHTML ).toContain( 'Enable AMP' );
		expect( container.innerHTML ).toContain( 'AMP is blocked due to 3 validation issues marked as kept.' );
		expect( container.querySelector( 'svg' ) ).not.toBeNull();
		expect( container.querySelector( 'button' ).textContent ).toContain( 'Review' );
	} );

	it( 'renders a correct message if there are unreviewed validation errors', () => {
		setupHooks( {
			unreviewedValidationErrorCount: 1,
		} );

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.innerHTML ).toContain( 'Enable AMP' );
		expect( container.innerHTML ).toContain( 'AMP is valid, but 1 issue needs review.' );
		expect( container.querySelector( 'svg' ) ).not.toBeNull();
		expect( container.querySelector( 'button' ).textContent ).toContain( 'Review' );
	} );

	it( 'renders a correct message if there are no errors', () => {
		setupHooks();

		act( () => {
			render( <AMPDocumentStatusNotification />, container );
		} );

		expect( container.innerHTML ).toContain( 'Enable AMP' );
		expect( container.innerHTML ).toContain( 'No AMP validation issues detected.' );
		expect( container.querySelector( 'svg' ) ).not.toBeNull();
		expect( container.querySelector( 'button' ) ).toBeNull();
	} );
} );

