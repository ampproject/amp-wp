/**
 * WordPress dependencies
 */
import '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import {
	animations,
	currentPage,
	blocks,
} from '../reducer';

describe( 'reducers', () => {
	describe( 'animations()', () => {
		it( 'should add new animation entry', () => {
			const page = 'foo';
			const item = 'bar';
			const predecessor = 'baz';

			const state = animations( undefined, {
				type: 'ADD_ANIMATION',
				page,
				item,
				predecessor,
			} );

			expect( state ).toStrictEqual( {
				[ page ]: [
					{ id: item, parent: undefined },
				],
			} );
		} );

		it( 'should prevent adding an item as its own predecessor', () => {
			const page = 'foo';
			const item = 'bar';

			const state = animations( undefined, {
				type: 'ADD_ANIMATION',
				page,
				item,
				predecessor: item,
			} );

			expect( state ).toStrictEqual( {
				[ page ]: [
					{ id: item, parent: undefined },
				],
			} );
		} );

		it( 'should prevent animation order loops', () => {
			const page = 'foo';
			const item = 'bar';
			const item2 = 'baz';

			let originalState = animations( undefined, {
				type: 'ADD_ANIMATION',
				page,
				item,
			} );

			originalState = animations( originalState, {
				type: 'ADD_ANIMATION',
				page,
				item: item2,
				predecessor: item,
			} );

			const state = animations( originalState, {
				type: 'ADD_ANIMATION',
				page,
				item,
				predecessor: item2,
			} );

			expect( state ).toStrictEqual( {
				[ page ]: [
					{ id: item, parent: undefined },
					{ id: item2, parent: item },
				],
			} );
		} );

		it.todo( 'should add an entry when animation type changes' );

		it.todo( 'should update an entry when animation type changes' );

		it.todo( 'should remove an entry when animation type changes' );

		it.todo( 'should update successors when animation type changes' );

		it.todo( 'should update an entry when animation duration changes' );

		it.todo( 'should update an entry when animation delay changes' );
	} );

	describe( 'currentPage()', () => {
		it( 'should change page to whatever was passed', () => {
			const page = 'foo';

			const state = currentPage( undefined, {
				type: 'SET_CURRENT_PAGE',
				page,
			} );

			expect( state ).toStrictEqual( page );
		} );
	} );

	describe( 'blocks()', () => {
		it( 'should start reordering', () => {
			const state = blocks( undefined, {
				type: 'START_REORDERING',
				order: [ 'page-1', 'page-2' ],
			} );

			expect( state ).toStrictEqual( {
				order: [ 'page-1', 'page-2' ],
				isReordering: true,
			} );
		} );

		it( 'should stop reordering', () => {
			const originalState = {
				order: [ 'page-1', 'page-2' ],
				isReordering: true,
			};

			const state = blocks( originalState, {
				type: 'STOP_REORDERING',
			} );

			expect( state ).toStrictEqual( {
				order: [ 'page-1', 'page-2' ],
				isReordering: false,
			} );
		} );

		it( 'should change block order', () => {
			const originalState = blocks( undefined, {
				type: 'START_REORDERING',
				order: [ 'page-1', 'page-2' ],
			} );

			expect( originalState ).toStrictEqual( {
				order: [ 'page-1', 'page-2' ],
				isReordering: true,
			} );

			let state = blocks( originalState, {
				type: 'MOVE_PAGE',
				page: 'page-1',
				index: 1,
			} );

			expect( state ).toStrictEqual( {
				order: [ 'page-2', 'page-1' ],
				isReordering: true,
			} );

			state = blocks( originalState, {
				type: 'RESET_ORDER',
				order: [ 'page-1', 'page-2' ],
			} );

			expect( state ).toStrictEqual( {
				order: [ 'page-1', 'page-2' ],
				isReordering: false,
			} );
		} );
	} );
} );
