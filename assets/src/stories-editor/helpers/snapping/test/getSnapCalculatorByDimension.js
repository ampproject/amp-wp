/**
 * Internal dependencies
 */
import getSnapCalculatorByDimension from '../getSnapCalculatorByDimension';

const getLine = jest.fn( ( offset ) => [ [ offset, 0 ], [ offset, 1000 ] ] );
const maxValue = 1000;

describe( 'getSnapCalculatorByDimension', () => {
	it( 'should ultimately return a list of snap targets based on block positions', () => {
		const getSnaps = getSnapCalculatorByDimension(
			getLine,
			maxValue,
			[ 'top', 'bottom' ],
			[ 'left', 'right' ],
		);

		const siblingPositions = [
			{
				top: 100,
				right: 200,
				bottom: 200,
				left: 100,
			},
		];

		const getSnapsWithSiblingPositions = getSnaps( siblingPositions );
		const actual = getSnapsWithSiblingPositions( 100, 100 );

		const expected = [
			{
				0: [ [ [ 0, 0 ], [ 0, 1000 ] ] ],
				100: [ [ [ 100, 0 ], [ 100, 1000 ] ] ],
				1000: [ [ [ 1000, 0 ], [ 1000, 1000 ] ] ],
				200: [ [ [ 200, 0 ], [ 200, 1000 ] ] ],
				500: [ [ [ 500, 0 ], [ 500, 1000 ] ] ],
			},
			{
				150: [ [ [ 150, 0 ], [ 150, 1000 ] ] ],
				500: [ [ [ 500, 0 ], [ 500, 1000 ] ] ],
			},
		];

		expect( actual ).toMatchObject( expected );
	} );
} );
