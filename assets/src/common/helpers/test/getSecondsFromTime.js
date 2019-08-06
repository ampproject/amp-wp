/**
 * Internal dependencies
 */
import { getSecondsFromTime } from '../';

describe( 'getSecondsFromTime', () => {
	it( 'should return the proper seconds if the time only has seconds', () => {
		expect( getSecondsFromTime( ':03' ) ).toBe( 3 );
	} );

	it( 'should return the proper seconds if the time has seconds and 0 for minutes', () => {
		expect( getSecondsFromTime( '0:03' ) ).toBe( 3 );
	} );

	it( 'should return the proper seconds if the time has no minutes but double-digit seconds', () => {
		expect( getSecondsFromTime( ':43' ) ).toBe( 43 );
	} );

	it( 'should return the proper seconds if the time has minutes and seconds', () => {
		expect( getSecondsFromTime( '3:43' ) ).toBe( 223 );
	} );

	it( 'should return the proper seconds if the time has double-digit minutes and seconds', () => {
		expect( getSecondsFromTime( '18:43' ) ).toBe( 1123 );
	} );

	it( 'should return the proper seconds if the time has hours, minutes, and seconds', () => {
		expect( getSecondsFromTime( '05:18:43' ) ).toBe( 19123 );
	} );
} );
