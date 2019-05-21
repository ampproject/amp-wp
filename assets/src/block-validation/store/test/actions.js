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
			expect( result ).toEqual( {
				type: 'ADD_VALIDATION_ERROR',
				error,
			} );
		} );

		it( 'should return the ADD_VALIDATION_ERROR action with a clientId', () => {
			const clientId = 'foo';
			const error = { foo: 'bar' };

			const result = addValidationError( error, clientId );
			expect( result ).toEqual( {
				type: 'ADD_VALIDATION_ERROR',
				error,
				clientId,
			} );
		} );
	} );

	describe( 'resetValidationErrors', () => {
		it( 'should return the RESET_VALIDATION_ERRORS action', () => {
			const result = resetValidationErrors();

			expect( result ).toEqual( {
				type: 'RESET_VALIDATION_ERRORS',
			} );
		} );
	} );

	describe( 'updateReviewLink', () => {
		it( 'should return the UPDATE_REVIEW_LINK action', () => {
			const result = updateReviewLink();

			expect( result ).toEqual( {
				type: 'UPDATE_REVIEW_LINK',
			} );
		} );
	} );
} );
