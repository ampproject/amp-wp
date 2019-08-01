/**
 * Internal dependencies
 */
import {
	addValidationError,
	resetValidationErrors,
	updateReviewLink,
} from '../actions';

describe( 'actions', () => {
	describe( 'addValidationError', () => {
		it( 'should return the ADD_VALIDATION_ERROR action', () => {
			const error = { foo: 'bar' };

			const result = addValidationError( error );
			expect( result ).toStrictEqual( {
				type: 'ADD_VALIDATION_ERROR',
				error,
				clientId: undefined,
			} );
		} );

		it( 'should return the ADD_VALIDATION_ERROR action with a clientId', () => {
			const clientId = 'foo';
			const error = { foo: 'bar' };

			const result = addValidationError( error, clientId );
			expect( result ).toStrictEqual( {
				type: 'ADD_VALIDATION_ERROR',
				error,
				clientId,
			} );
		} );
	} );

	describe( 'resetValidationErrors', () => {
		it( 'should return the RESET_VALIDATION_ERRORS action', () => {
			const result = resetValidationErrors();

			expect( result ).toStrictEqual( {
				type: 'RESET_VALIDATION_ERRORS',
			} );
		} );
	} );

	describe( 'updateReviewLink', () => {
		it( 'should return the UPDATE_REVIEW_LINK action', () => {
			const url = 'https://example.com/';
			const result = updateReviewLink( url );

			expect( result ).toStrictEqual( {
				type: 'UPDATE_REVIEW_LINK',
				url,
			} );
		} );
	} );
} );
