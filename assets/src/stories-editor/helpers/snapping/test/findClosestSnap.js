/**
 * Internal dependencies
 */
import findClosestSnap from '../findClosestSnap';

describe( 'findClosestSnap', () => {
	it( 'should return the first match', () => {
		const number = 15;
		const snap = [ 10, 20, 30 ];
		const snapGap = 6;

		expect( findClosestSnap( number, snap, snapGap ) ).toStrictEqual( 10 );
	} );

	it( 'should return null if there is no match', () => {
		const number = 36;
		const snap = [ 10, 20, 30 ];
		const snapGap = 5;

		expect( findClosestSnap( number, snap, snapGap ) ).toBeNull();
	} );

	it( 'should accept a snap function', () => {
		const number = 15;
		const snap = jest.fn( () => [ 10, 20, 30 ] );
		const snapGap = 6;

		expect( findClosestSnap( number, snap, snapGap ) ).toStrictEqual( 10 );
		expect( snap.mock.calls ).toHaveLength( 1 );
	} );

	it( 'should be memoized', () => {
		const number = 25;
		const snap = jest.fn( () => [ 20, 30, 40 ] );
		const snapGap = 10;

		findClosestSnap( number, snap, snapGap );
		findClosestSnap( number, snap, snapGap );
		expect( findClosestSnap( number, snap, snapGap ) ).toStrictEqual( 20 );
		expect( snap.mock.calls ).toHaveLength( 1 );
	} );
} );
