/**
 * Internal dependencies
 */
import {
	getValidationErrors,
	getBlockValidationErrors,
	getReviewLink,
	isSanitizationAutoAccepted,
} from '../selectors';

describe( 'selectors', () => {
	describe( 'getValidationErrors', () => {
		it( 'should return a list of validation errors', () => {
			const errors = [ { foo: 'bar' }, { bar: 'baz' } ];

			const state = {
				errors,
			};

			expect( getValidationErrors( state ) ).toEqual( errors );
		} );
	} );

	describe( 'getBlockValidationErrors', () => {
		it( 'should return a list of block validation errors', () => {
			const errors = [ { foo: 'bar' }, { bar: 'baz' }, { baz: 'boo', clientId: 'foo' } ];

			const state = {
				errors,
			};

			expect( getBlockValidationErrors( state, 'foo' ) ).toEqual( [ { baz: 'boo', clientId: 'foo' } ] );
		} );
	} );

	describe( 'getReviewLink', () => {
		it( 'should return the validation errors review link', () => {
			const state = { reviewLink: 'https://example.com' };

			expect( getReviewLink( state ) ).toEqual( 'https://example.com' );
		} );
	} );

	describe( 'isSanitizationAutoAccepted', () => {
		it( 'should return a boolean', () => {
			const state = { isSanitizationAutoAccepted: '1' };

			expect( isSanitizationAutoAccepted( state ) ).toStrictEqual( true );
		} );
	} );
} );
