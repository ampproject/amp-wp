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
		const values = [
			{ value: 11, matcher },
			{ value: 22, matcher },
			{ value: 33, matcher },
		];

		expect( findBestMatch( ...values ) ).toStrictEqual( {
			value: 10,
			distance: 1,
		} );
	} );

	it( 'should return extra values if present', () => {
		const values = [
			{ value: 11, matcher, a: 1, b: 2 },
			{ value: 22, matcher },
			{ value: 33, matcher },
		];

		expect( findBestMatch( ...values ) ).toStrictEqual( {
			value: 10,
			distance: 1,
			a: 1,
			b: 2,
		} );
	} );

	it( 'should return ignore non-matches', () => {
		const values = [
			{ value: 46, matcher },
			{ value: 22, matcher },
			{ value: 51, matcher },
		];

		expect( findBestMatch( ...values ) ).toStrictEqual( {
			value: 20,
			distance: 2,
		} );
	} );

	it( 'should return null if none match', () => {
		const values = [
			{ value: 46, matcher },
			{ value: 51, matcher },
		];

		expect( findBestMatch( ...values ) ).toStrictEqual( {
			value: null,
			distance: expect.any( Number ),
		} );
	} );

	it( 'should return the first match if several match equally well', () => {
		const values = [
			{ value: 33, matcher },
			{ value: 22, matcher },
			{ value: 12, matcher },
		];

		expect( findBestMatch( ...values ) ).toStrictEqual( {
			value: 20,
			distance: 2,
		} );
	} );
} );
