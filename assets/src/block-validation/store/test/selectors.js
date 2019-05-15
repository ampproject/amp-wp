/**
 * Internal dependencies
 */
import {
	getBlockValidationErrors,
	getValidationErrors,
	getReviewLink,
} from '../selectors';

describe( 'selectors', () => {
	describe( 'getValidationErrors', () => {
		it( 'should return an empty array if state is empty', () => {
			const state = {};

			expect( getValidationErrors( state ) ).toEqual( [] );
		} );

		it( 'should return a list of validation errors', () => {
			const errors = [ { foo: 'bar' }, { bar: 'baz' } ];

			const state = {
				errors,
			};

			expect( getValidationErrors( state ) ).toEqual( errors );
		} );
	} );

	describe( 'getBlockValidationErrors', () => {
		it( 'should return a list of validation errors', () => {
			const errors = [ { foo: 'bar' }, { bar: 'baz' }, { baz: 'boo', clientId: 'foo' } ];

			const state = {
				errors,
			};

			expect( getBlockValidationErrors( state, 'foo' ) ).toEqual( { baz: 'boo', clientId: 'foo' } );
		} );
	} );

	describe( 'getReviewLink', () => {
		it( 'should return the validation errors review link', () => {
			const state = { reviewLink: 'https://example.com' };

			expect( getReviewLink( state ) ).toEqual( 'https://example.com' );
		} );
	} );
} );
