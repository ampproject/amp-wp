/**
 * Internal dependencies
 */
import createSnapList from '../createSnapList';

const getLine = jest.fn( ( offset ) => [ [ offset, 0 ], [ offset, 1000 ] ] );

describe( 'createSnapList', () => {
	it( 'should return an initial list of snap lines', () => {
		const maxValue = 1000;
		const snapLines = createSnapList( getLine, maxValue );

		const expected = {
			0: [ [ [ 0, 0 ], [ 0, 1000 ] ] ],
			500: [ [ [ 500, 0 ], [ 500, 1000 ] ] ],
			1000: [ [ [ 1000, 0 ], [ 1000, 1000 ] ] ],
		};

		expect( snapLines ).toMatchObject( expected );
	} );

	it( 'should allow adding new snap targets', () => {
		const maxValue = 1000;
		const snapLines = createSnapList( getLine, maxValue );

		const expected = {
			0: [ [ [ 0, 0 ], [ 0, 1000 ] ] ],
			100: [ [ [ 100, 0 ], [ 100, 1000 ] ] ],
			500: [ [ [ 500, 0 ], [ 500, 1000 ] ] ],
			750: [ [ [ 750, 0 ], [ 750, 1000 ] ] ],
			1000: [ [ [ 1000, 0 ], [ 1000, 1000 ] ] ],
		};

		snapLines[ 100 ] = [ [ 100, 0 ], [ 100, 1000 ] ];
		snapLines[ 750 ] = [ [ 750, 0 ], [ 750, 1000 ] ];

		expect( snapLines ).toMatchObject( expected );
	} );

	it( 'should append new snap lines to the existing targets', () => {
		const maxValue = 1000;
		const snapLines = createSnapList( getLine, maxValue );

		const expected = {
			0: [ [ [ 0, 0 ], [ 0, 1000 ] ], [ [ 0, 500 ], [ 100, 1000 ] ] ],
			500: [ [ [ 10, 500 ], [ 10, 1000 ] ], [ [ 500, 0 ], [ 500, 1000 ] ] ],
			1000: [ [ [ 1000, 0 ], [ 1000, 1000 ] ] ],
		};

		snapLines[ 0 ] = [ [ 0, 500 ], [ 100, 1000 ] ];
		snapLines[ 500 ] = [ [ 10, 500 ], [ 10, 1000 ] ];

		expect( snapLines ).toMatchObject( expected );
	} );

	it( 'should prevent duplicate snap lines', () => {
		const maxValue = 1000;
		const snapLines = createSnapList( getLine, maxValue );

		const expected = {
			0: [ [ [ 0, 0 ], [ 0, 1000 ] ] ],
			500: [ [ [ 500, 0 ], [ 500, 1000 ] ] ],
			1000: [ [ [ 1000, 0 ], [ 1000, 1000 ] ] ],
		};

		snapLines[ 0 ] = [ [ 0, 0 ], [ 0, 1000 ] ];
		snapLines[ 0 ] = [ [ 0, 0 ], [ 0, 1000 ] ];
		snapLines[ 500 ] = [ [ 500, 0 ], [ 500, 1000 ] ];
		snapLines[ 500 ] = [ [ 500, 0 ], [ 500, 1000 ] ];
		snapLines[ 1000 ] = [ [ 1000, 0 ], [ 1000, 1000 ] ];
		snapLines[ 1000 ] = [ [ 1000, 0 ], [ 1000, 1000 ] ];

		expect( snapLines ).toMatchObject( expected );
	} );

	it( 'should prevent out of bound snap lines', () => {
		const maxValue = 1000;
		const snapLines = createSnapList( getLine, maxValue );

		const expected = {
			0: [ [ [ 0, 0 ], [ 0, 1000 ] ] ],
			500: [ [ [ 500, 0 ], [ 500, 1000 ] ] ],
			1000: [ [ [ 1000, 0 ], [ 1000, 1000 ] ] ],
		};

		snapLines[ -1 ] = [ [ -1, 0 ], [ -1, 1000 ] ];
		snapLines[ 1001 ] = [ [ 1001, 0 ], [ 1001, 1000 ] ];

		expect( snapLines ).toMatchObject( expected );
	} );
} );
