/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';
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

			expect( state ).toEqual( {
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

			expect( state ).toEqual( {
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

			expect( state ).toEqual( {
				[ page ]: [
					{ id: item, parent: undefined },
					{ id: item2, parent: item },
				],
			} );
		} );
	} );

	describe( 'currentPage()', () => {
		it( 'should return undefined by default', () => {
			const state = currentPage( undefined, {
				type: 'SET_CURRENT_PAGE',
				page: 'core/invalid-block',
			} );

			expect( state ).toBeUndefined();
		} );

		it.skip( 'should change page if it exists', () => { // eslint-disable-line jest/no-disabled-tests
			select( 'core/block-editor' ).getBlock = jest.fn().mockReturnValue( true );

			const page = 'foo';

			const state = currentPage( undefined, {
				type: 'SET_CURRENT_PAGE',
				page,
			} );

			expect( state ).toEqual( page );

			select( 'core/block-editor' ).getBlock.mockRestore();
		} );

		it.skip( 'should not change state for invalid block', () => { // eslint-disable-line jest/no-disabled-tests
			select( 'core/block-editor' ).getBlock = jest.fn().mockReturnValue( true );

			const page = 'foo';
			const newPage = 'bar';

			const originalState = currentPage( undefined, {
				type: 'SET_CURRENT_PAGE',
				page,
			} );

			select( 'core/block-editor' ).getBlock = jest.fn().mockReturnValue( false );

			const state = currentPage( originalState, {
				type: 'SET_CURRENT_PAGE',
				page: newPage,
			} );

			expect( state ).toEqual( page );

			select( 'core/block-editor' ).getBlock.mockRestore();
		} );
	} );

	describe( 'blocks()', () => {
		it( 'should start reordering', () => {
			const state = blocks( undefined, {
				type: 'START_REORDERING',
			} );

			expect( state ).toEqual( {
				order: [],
				isReordering: true,
			} );
		} );

		it.skip( 'should stop reordering', () => { // eslint-disable-line jest/no-disabled-tests
			const state = blocks( undefined, {
				type: 'STOP_REORDERING',
			} );

			expect( state ).toEqual( {
				order: [],
				isReordering: false,
			} );
		} );

		describe( 'move pages', () => {
			beforeAll( () => {
				select( 'core/block-editor' ).getBlockOrder = jest.fn().mockReturnValue( [ 'page-1', 'page-2' ] );
			} );

			afterAll( () => {
				select( 'core/block-editor' ).getBlockOrder.mockRestore();
			} );

			it.skip( 'should change block order', () => { // eslint-disable-line jest/no-disabled-tests
				const originalState = blocks( undefined, {
					type: 'START_REORDERING',
				} );

				expect( originalState ).toEqual( {
					order: [ 'page-1', 'page-2' ],
					isReordering: true,
				} );

				const state = blocks( originalState, {
					type: 'MOVE_PAGE',
					page: 'page-1',
					index: 1,
				} );

				expect( state ).toEqual( {
					order: [ 'page-2', 'page-1' ],
					isReordering: true,
				} );
			} );
		} );

		describe( 'reset order', () => {
			beforeAll( () => {
				select( 'core/block-editor' ).getBlockOrder = jest.fn().mockReturnValue( [ 'page-1', 'page-2' ] );
			} );

			afterAll( () => {
				select( 'core/block-editor' ).getBlockOrder.mockRestore();
			} );

			it.skip( 'should reset block order', () => { // eslint-disable-line jest/no-disabled-tests
				let originalState = blocks( undefined, {
					type: 'START_REORDERING',
				} );

				originalState = blocks( originalState, {
					type: 'MOVE_PAGE',
					page: 'page-1',
					index: 1,
				} );

				const state = blocks( originalState, {
					type: 'RESET_ORDER',
				} );

				expect( state ).toEqual( {
					order: [ 'page-1', 'page-2' ],
					isReordering: false,
				} );
			} );
		} );
	} );
} );
