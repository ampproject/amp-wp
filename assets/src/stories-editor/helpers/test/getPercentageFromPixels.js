/**
 * Internal dependencies
 */
import { getPercentageFromPixels } from '../';

describe( 'getPercentageFromPixels', () => {
	it( 'returns 0 for unknown axis', () => {
		const result = getPercentageFromPixels( 'z', 100 );

		expect( result ).toBe( 0 );
	} );

	it( 'returns number with two decimals', () => {
		const result = getPercentageFromPixels( 'x', 123.456 );
		const numberOfDigits = result.toString().split( '.' )[ 1 ].length;

		expect( numberOfDigits ).toBe( 2 );
	} );
} );
