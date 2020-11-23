/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { Error } from '../error';
import { VALIDATION_ERROR_ACK_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_NEW_REJECTED_STATUS } from '../constants';
import '@wordpress/blocks/build/store/';
import '@wordpress/block-editor/build/store';
import { createStore } from '../store';

let container;

jest.mock( '../use-inline-data', () => ( {
	useInlineData: () => ( {
		blockSources: {},
	} ),
} ) );

describe( 'Error', () => {
	beforeAll( () => {
		registerBlockType(
			'amp/test-block',
			{
				title: 'My test block',
			},
		);

		dispatch( 'core/block-editor' ).insertBlock( 'amp/test-block' );

		createStore( {
			reviewLink: 'http://review-link.test',
		} );
	} );

	beforeEach( () => {
		container = document.createElement( 'ul' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it.each( [
		VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
		VALIDATION_ERROR_ACK_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
	].map( ( status ) => [
		status,
		() => (
			<Error
				status={ status }
				term_id={ 12 }
				title="My test block"
				type="js_error"
			/>
		),
	] ) )( 'errors with no associated blocks work correctly', ( status, ErrorComponent ) => {
		act( () => {
			render(
				<ErrorComponent />,
				container,
			);
		} );

		const newReviewed = [ VALIDATION_ERROR_NEW_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS ].includes( status ) ? 'new' : 'reviewed';

		expect( container.querySelector( 'li' ).getAttribute( 'class' ) ).toBe( 'amp-error-container' );
		expect( container.querySelectorAll( `.amp-error--${ newReviewed }` ) ).toHaveLength( 1 );
		expect( container.querySelector( '.amp-error__details-link' ) ).toBeNull();
		expect( container.querySelector( `.amp-error--${ newReviewed } button` ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__block-type-icon' ) ).toBeNull();

		container.querySelector( `.amp-error--${ newReviewed } button` ).click();
		expect( container.querySelector( '.amp-error__details-link' ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__select-block' ) ).toBeNull();

		expect( container.querySelector( 'li' ).innerHTML ).toContain(
			[ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ? 'removed' : 'kept',
		);
	} );

	it.each( [
		VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
		VALIDATION_ERROR_ACK_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
	].map( ( status ) => [
		status,
		() => (
			<Error
				status={ status }
				term_id={ 12 }
				title="My test block"
				type="js_error"
			/>
		),
	] ) )( 'errors with associated blocks work correctly', ( status, ErrorComponent ) => {
		act( () => {
			render(
				<ErrorComponent />,
				container,
			);
		} );

		const newReviewed = [ VALIDATION_ERROR_NEW_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS ].includes( status ) ? 'new' : 'reviewed';

		expect( container.querySelector( 'li' ).getAttribute( 'class' ) ).toBe( 'amp-error-container' );
		expect( container.querySelectorAll( `.amp-error--${ newReviewed }` ) ).toHaveLength( 1 );
		expect( container.querySelector( '.amp-error__details-link' ) ).toBeNull();
		expect( container.querySelector( `.amp-error--${ newReviewed } button` ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__block-type-icon' ) ).toBeNull();

		container.querySelector( `.amp-error--${ newReviewed } button` ).click();
		expect( container.querySelector( '.amp-error__details-link' ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__select-block' ) ).toBeNull();

		expect( container.querySelector( 'li' ).innerHTML ).toContain(
			[ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ? 'removed' : 'kept',
		);
	} );
} );
