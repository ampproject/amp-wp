/**
 * WordPress dependencies
 */
import '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import reducer from '../reducer';

describe( 'reducer', () => {
	it( 'should add new validation error', () => {
		const clientId = 'foo';
		const error = { bar: 'baz' };

		const state = reducer( undefined, {
			type: 'ADD_VALIDATION_ERROR',
			error,
			clientId,
		} );

		expect( state ).toEqual( {
			errors: [
				{ ...error, clientId },
			],
		} );
	} );

	it( 'should reset validation errors', () => {
		const initialState = { errors: [ { foo: 'bar' }, { bar: 'baz' } ] };

		const state = reducer( initialState, {
			type: 'RESET_VALIDATION_ERRORS',
		} );

		expect( state ).toEqual( {
			errors: [],
		} );
	} );

	it( 'should update the review link', () => {
		const state = reducer( undefined, {
			type: 'UPDATE_REVIEW_LINK',
			url: 'https://example.com',
		} );

		expect( state ).toEqual( {
			reviewLink: 'https://example.com',
		} );
	} );
} );
