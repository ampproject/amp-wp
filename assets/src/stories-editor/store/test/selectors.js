/**
 * Internal dependencies
 */
import {
	getAnimatedBlocks,
	isValidAnimationPredecessor,
	getCurrentPage,
	getBlockOrder,
	getBlockIndex,
	isReordering,
} from '../selectors';

describe( 'actions', () => {
	describe( 'getAnimatedBlocks', () => {
		it( 'should return an empty object if state is empty', () => {
			const state = {};

			expect( getAnimatedBlocks( state ) ).toEqual( {} );
		} );
	} );

	describe( 'isValidAnimationPredecessor', () => {
		it( 'should return true if there are no animated blocks yet', () => {
			const state = {};
			const page = 'foo';
			const item = 'bar';
			const predecessor = 'baz';

			expect( isValidAnimationPredecessor( state, page, item, predecessor ) ).toBe( true );
		} );

		it( 'should return true if item has no predecessor', () => {
			const state = {};
			const page = 'foo';
			const item = 'bar';

			expect( isValidAnimationPredecessor( state, page, item, undefined ) ).toBe( true );
		} );

		it( 'should return false if predecessor is not animated', () => {
			const page = 'foo';
			const item = 'bar';
			const item2 = 'baz';

			const state = {
				animations: {
					[ page ]: [
						{ id: item, parent: undefined },
					],
				},
			};

			expect( isValidAnimationPredecessor( state, page, item, item2 ) ).toBe( false );
		} );

		it( 'should return false if there is a loop', () => {
			const page = 'foo';
			const item = 'bar';
			const item2 = 'baz';

			const state = {
				animations: {
					[ page ]: [
						{ id: item, parent: undefined },
						{ id: item2, parent: item },
					],
				},
			};

			expect( isValidAnimationPredecessor( state, page, item, item2 ) ).toBe( false );
		} );
	} );

	describe( 'getCurrentPage', () => {
		it( 'should return undefined if there is no current page', () => {
			const state = {};

			expect( getCurrentPage( state ) ).toBeUndefined();
		} );
	} );

	describe( 'getBlockOrder', () => {
		it( 'should return an empty array by default', () => {
			const state = { blocks: { } };

			expect( getBlockOrder( state ) ).toHaveLength( 0 );
		} );
	} );

	describe( 'getBlockIndex', () => {
		it( 'should return null by default', () => {
			const page = 'foo';
			const state = { blocks: { } };

			expect( getBlockIndex( state, page ) ).toBe( null );
		} );

		it( 'should return the page\'s index in the block order list', () => {
			const page = 'foo';
			const page2 = 'bar';
			const page3 = 'baz';
			const state = { blocks: { order: [ page, page2, page3 ] } };

			expect( getBlockIndex( state, page2 ) ).toBe( 1 );
		} );
	} );

	describe( 'isReordering', () => {
		it( 'should return false by default', () => {
			const state = { blocks: { } };

			expect( isReordering( state ) ).toBe( false );
		} );
	} );
} );
