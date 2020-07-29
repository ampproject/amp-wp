/**
 * Internal dependencies
 */
import {
	hasThemeSupport,
	isStandardMode,
	getErrorMessages,
	getAmpSlug,
} from '../selectors';

describe( 'selectors', () => {
	describe( 'hasThemeSupport', () => {
		it( 'should return whether the theme has AMP support', () => {
			const state = { hasThemeSupport: false };

			expect( hasThemeSupport( state ) ).toBe( false );
		} );
	} );

	describe( 'isStandardMode', () => {
		it( 'should return whether standard mode is enabled', () => {
			const state = { isStandardMode: true };

			expect( isStandardMode( state ) ).toBe( true );
		} );
	} );

	describe( 'getErrorMessages', () => {
		it( 'should return the AMP validation messages', () => {
			const expectedMessages = [ 'Disallowed script', 'Disallowed attribute' ];
			const state = { errorMessages: expectedMessages };

			expect( getErrorMessages( state ) ).toStrictEqual( expectedMessages );
		} );
	} );

	describe( 'getAmpSlug', () => {
		it( 'should return the AMP slug', () => {
			const slug = 'amp';
			const state = { ampSlug: slug };

			expect( getAmpSlug( state ) ).toStrictEqual( slug );
		} );
	} );
} );
