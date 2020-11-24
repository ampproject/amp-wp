/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { registerBlockType, createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { Error } from '../error';
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from '../constants';
import { createStore } from '../store';

let container, block;
const TEST_BLOCK = 'my-plugin/test-block';

jest.mock( '../use-inline-data', () => ( {
	useInlineData: () => ( {
		blockSources: {
			'my-plugin/test-block': {
				source: 'plugin',
				name: 'My plugin',
			},
		},
	} ),
} ) );

describe( 'Error', () => {
	beforeAll( () => {
		registerBlockType( TEST_BLOCK, {
			attributes: {},
			save: noop,
			category: 'widgets',
			title: 'test block',
		} );

		block = createBlock( TEST_BLOCK, {} );
		dispatch( 'core/block-editor' ).insertBlock( block );

		createStore( {
			reviewLink: 'http://review-link.test',
			validationErrors: [
				{
					clientId: block.clientId,
					code: 'DISALLOWED_TAG',
					status: 3,
					term_id: 12,
					title: 'Invalid script: <code>jquery.js</code>',
					type: 'js_error',
				},
			],
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
			[ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ? 'Removed' : 'Kept',
		);

		expect( container.querySelector( 'li' ).innerHTML ).toContain( 'outside the post content' );
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
				clientId={ block.clientId }
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
		expect( container.querySelector( '.amp-error__block-type-icon' ) ).not.toBeNull();

		container.querySelector( `.amp-error--${ newReviewed } button` ).click();
		expect( container.querySelector( '.amp-error__details-link' ) ).not.toBeNull();
		expect( container.querySelector( '.amp-error__select-block' ) ).not.toBeNull();

		expect( container.querySelector( 'li' ).innerHTML ).toContain(
			[ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ? 'Removed' : 'Kept',
		);
		expect( container.querySelector( 'li' ).innerHTML ).toContain( 'My plugin (plugin)' );
	} );
} );
