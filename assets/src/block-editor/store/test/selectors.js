/**
 * Internal dependencies
 */
import {
	hasThemeSupport,
	isStandardMode,
	getDefaultStatus,
	getPossibleStatuses,
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

	describe( 'getDefaultStatus', () => {
		it( 'should return the default AMP status', () => {
			const state = { defaultStatus: 'enabled' };

			expect( getDefaultStatus( state ) ).toStrictEqual( 'enabled' );
		} );
	} );

	describe( 'getPossibleStatuses', () => {
		it( 'should return the possible AMP statuses', () => {
			const state = { possibleStatuses: [ 'enabled', 'disabled' ] };

			expect( getPossibleStatuses( state ) ).toStrictEqual( [ 'enabled', 'disabled' ] );
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
