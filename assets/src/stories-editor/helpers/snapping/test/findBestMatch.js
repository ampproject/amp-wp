/**
 * Internal dependencies
 */
import findBestMatch from '../findBestMatch';

describe( 'findBestMatch', () => {
	// Use a matcher that rounds to nearest 10, but returns null if nearest 10 is 50
	const matcher = ( n ) => {
		const tens = Math.round( n / 10 ) * 10;
		return tens === 50 ? null : tens;
	};

	it( 'should return the best match', () => {
		const values = [ 33, 22, 11 ];

		expect( findBestMatch( matcher, ...values ) ).toStrictEqual( 10 );
	} );

	it( 'should return ignore non-matches', () => {
		const values = [ 46, 22, 51 ];

		expect( findBestMatch( matcher, ...values ) ).toStrictEqual( 20 );
	} );

	it( 'should return null if none match', () => {
		const values = [ 46, 51 ];

		expect( findBestMatch( matcher, ...values ) ).toBeNull();
	} );

	it( 'should return the first match if several match equally well', () => {
		const values = [ 33, 22, 12 ];

		expect( findBestMatch( matcher, ...values ) ).toStrictEqual( 20 );
	} );
} );
