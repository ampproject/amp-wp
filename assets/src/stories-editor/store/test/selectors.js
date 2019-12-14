/**
 * Internal dependencies
 */
import {
	getAnimatedBlocks,
	getAnimatedBlocksPerPage,
	getAnimationSuccessors,
	isPlayingAnimation,
	isValidAnimationPredecessor,
	getCurrentPage,
	getBlockOrder,
	getBlockIndex,
	isReordering,
} from '../selectors';
import { ANIMATION_STATUS } from '../constants';

describe( 'selectors', () => {
	describe( 'getAnimatedBlocks', () => {
		it( 'should return an empty object if state is empty', () => {
			const state = {};

			expect( getAnimatedBlocks( state ) ).toStrictEqual( {} );
		} );

		it( 'should return the unmodified animation order', () => {
			const page = 'foo';
			const item = 'bar';
			const item2 = 'baz';
			const item3 = 'foobar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined },
							{ id: item2, parent: item },
							{ id: item3, parent: item2 },
						],
					},
				},
			};

			expect( getAnimatedBlocks( state ) ).toStrictEqual( {
				[ page ]: [
					{ id: item, parent: undefined },
					{ id: item2, parent: item },
					{ id: item3, parent: item2 },
				],
			} );
		} );
	} );

	describe( 'getAnimatedBlocksPerPage', () => {
		it( 'should return an empty array if state is empty', () => {
			const state = {};

			expect( getAnimatedBlocksPerPage( state, 'foo' ) ).toStrictEqual( [] );
		} );

		it( 'should return the unmodified animation order', () => {
			const page = 'foo';
			const item = 'bar';
			const item2 = 'baz';
			const item3 = 'foobar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined },
							{ id: item2, parent: item },
							{ id: item3, parent: item2 },
						],
					},
				},
			};

			expect( getAnimatedBlocksPerPage( state, page ) ).toStrictEqual( [
				{ id: item, parent: undefined },
				{ id: item2, parent: item },
				{ id: item3, parent: item2 },
			] );
		} );
	} );

	describe( 'getAnimationSuccessors', () => {
		it( 'should return an empty array if state is empty', () => {
			const state = {};

			expect( getAnimationSuccessors( state, 'foo', undefined ) ).toStrictEqual( [] );
		} );

		it( 'should return the animation successors', () => {
			const page = 'foo';
			const item = 'bar';
			const item2 = 'baz';
			const item3 = 'foobar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined },
							{ id: item2, parent: item },
							{ id: item3, parent: item2 },
						],
					},
				},
			};

			expect( getAnimationSuccessors( state, page, item ) ).toStrictEqual( [ { id: item2, parent: item } ] );
			expect( getAnimationSuccessors( state, page, item2 ) ).toStrictEqual( [ { id: item3, parent: item2 } ] );
		} );
	} );

	describe( 'isPlayingAnimation', () => {
		it( 'should return false if state is empty', () => {
			const state = {};

			expect( isPlayingAnimation( state ) ).toBe( false );
		} );

		it( 'should return false if no item is playing', () => {
			const page = 'foo';
			const item = 'bar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined, status: ANIMATION_STATUS.stopped },
						],
					},
				},
			};

			expect( isPlayingAnimation( state ) ).toBe( false );
		} );

		it( 'should return true if one item is playing', () => {
			const page = 'foo';
			const item = 'bar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined, status: ANIMATION_STATUS.playing },
						],
					},
				},
			};

			expect( isPlayingAnimation( state ) ).toBe( true );
			expect( isPlayingAnimation( state, page ) ).toBe( true );
		} );

		it( 'should return true if one item is prepared', () => {
			const page = 'foo';
			const item = 'bar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined, status: ANIMATION_STATUS.prepared },
						],
					},
				},
			};

			expect( isPlayingAnimation( state ) ).toBe( true );
			expect( isPlayingAnimation( state, page ) ).toBe( true );
		} );

		it( 'should return true if specific item is prepared', () => {
			const page = 'foo';
			const item = 'bar';

			const state = {
				animations: {
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined, status: ANIMATION_STATUS.prepared },
						],
					},
				},
			};

			expect( isPlayingAnimation( state, page ) ).toBe( true );
			expect( isPlayingAnimation( state, page, item ) ).toBe( true );
		} );
	} );

	describe( 'isValidAnimationPredecessor', () => {
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
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined },
						],
					},
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
					animationOrder: {
						[ page ]: [
							{ id: item, parent: undefined },
							{ id: item2, parent: item },
						],
					},
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

			expect( getBlockIndex( state, page ) ).toBeNull( );
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
