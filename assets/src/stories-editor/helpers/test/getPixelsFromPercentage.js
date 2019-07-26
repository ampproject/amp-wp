/**
 * Internal dependencies
 */
import { getPixelsFromPercentage } from '../';

describe( 'getPixelsFromPercentage', () => {
	it( 'returns 0 for unknown axis', () => {
		const result = getPixelsFromPercentage( 'z', 77 );

		expect( result ).toBe( 0 );
	} );

	it( 'returns a rounded number', () => {
		const result = getPixelsFromPercentage( 'x', 12.345 );

		expect( result.toString() ).toStrictEqual( expect.not.stringContaining( '.' ) );
	} );
} );
