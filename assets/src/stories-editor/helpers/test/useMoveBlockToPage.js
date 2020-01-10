/**
 * Internal dependencies
 */
import useMoveBlockToPage from '../useMoveBlockToPage';

/*
 * A note on the testing here:
 *
 * `useMoveBlockToPage` is a hook and you might think it needs to be
 * tested as one.
 *
 * But because it's only a hook because `useSelect` and `useDispatch` are
 * hooks, and as we mock those below to not be hooks, we can test this as a
 * regular non-hook function.
 */

const mockGetBlockOrder = jest.fn( () => [] ); // must return Array-like
const mockGetBlock = jest.fn();
const mockGetCurrentPage = jest.fn();
const mockSetCurrentPage = jest.fn();
const mockSelectBlock = jest.fn();
const mockRemoveBlock = jest.fn();
const mockInsertBlock = jest.fn();
const mockUpdateBlockAttributes = jest.fn();
const mockCloneBlock = jest.fn();

jest.mock( '@wordpress/data', () => {
	return {
		useSelect: ( getter ) => getter( () => ( {
			getBlockOrder: mockGetBlockOrder,
			getBlock: mockGetBlock,
			getCurrentPage: mockGetCurrentPage,
		} ) ),
		useDispatch: () => ( {
			setCurrentPage: mockSetCurrentPage,
			selectBlock: mockSelectBlock,
			removeBlock: mockRemoveBlock,
			insertBlock: mockInsertBlock,
			updateBlockAttributes: mockUpdateBlockAttributes,
		} ),
	};
} );

jest.mock( '@wordpress/blocks', () => {
	return {
		cloneBlock: ( ...args ) => mockCloneBlock( ...args ),
	};
} );

const BLOCK_1 = { clientId: 1 };
const BLOCK_2 = { clientId: 2 };
const BLOCK_ID = 1;
const PAGE_1 = 1;
const PAGE_2 = 2;
const PAGE_3 = 3;
const ATTRS = { X: 1 };

describe( 'useMoveBlockToPage', () => {
	it( 'should invoke getters correctly', () => {
		useMoveBlockToPage( BLOCK_ID );

		expect( mockGetBlockOrder ).toHaveBeenCalledWith();
		expect( mockGetBlock ).toHaveBeenCalledWith( BLOCK_ID );
		expect( mockGetCurrentPage ).toHaveBeenCalledWith();
	} );

	it( 'should return two functions', () => {
		const result = useMoveBlockToPage( BLOCK_ID );

		expect( result ).toStrictEqual( {
			getPageByOffset: expect.any( Function ),
			moveBlockToPage: expect.any( Function ),
		} );
	} );

	describe( 'getPageByOffset', () => {
		it( 'should return next page correctly', () => {
			mockGetBlockOrder.mockImplementationOnce( () => [ PAGE_1, PAGE_2 ] );
			mockGetCurrentPage.mockImplementationOnce( () => PAGE_1 );

			const { getPageByOffset } = useMoveBlockToPage( BLOCK_ID );

			const nextPage = getPageByOffset( 1 );

			expect( nextPage ).toStrictEqual( PAGE_2 );
		} );

		it( 'should return page two ahead correctly', () => {
			mockGetBlockOrder.mockImplementationOnce( () => [ PAGE_1, PAGE_2, PAGE_3 ] );
			mockGetCurrentPage.mockImplementationOnce( () => PAGE_1 );

			const { getPageByOffset } = useMoveBlockToPage( BLOCK_ID );

			const nextNextPage = getPageByOffset( 2 );

			expect( nextNextPage ).toStrictEqual( PAGE_3 );
		} );

		it( 'should return null if offset is beyond end of list', () => {
			mockGetBlockOrder.mockImplementationOnce( () => [ PAGE_1 ] );
			mockGetCurrentPage.mockImplementationOnce( () => PAGE_1 );

			const { getPageByOffset } = useMoveBlockToPage( BLOCK_ID );

			const noPage = getPageByOffset( 1 );

			expect( noPage ).toBeNull();
		} );

		it( 'should return previous page correctly', () => {
			mockGetBlockOrder.mockImplementationOnce( () => [ PAGE_1, PAGE_2 ] );
			mockGetCurrentPage.mockImplementationOnce( () => PAGE_2 );

			const { getPageByOffset } = useMoveBlockToPage( BLOCK_ID );

			const previousPage = getPageByOffset( -1 );

			expect( previousPage ).toStrictEqual( PAGE_1 );
		} );

		it( 'should return null if offset is before start of list', () => {
			mockGetBlockOrder.mockImplementationOnce( () => [ PAGE_1 ] );
			mockGetCurrentPage.mockImplementationOnce( () => PAGE_1 );

			const { getPageByOffset } = useMoveBlockToPage( BLOCK_ID );

			const noPage = getPageByOffset( -1 );

			expect( noPage ).toBeNull();
		} );
	} );

	describe( 'moveBlockToPage', () => {
		it( 'should invoke functions correctly when invoked without attributes', () => {
			mockGetBlock.mockImplementationOnce( () => BLOCK_1 );
			mockCloneBlock.mockImplementationOnce( () => BLOCK_2 );

			const { moveBlockToPage } = useMoveBlockToPage( BLOCK_ID );

			moveBlockToPage( PAGE_1 );

			expect( mockRemoveBlock ).toHaveBeenCalledWith( BLOCK_ID );
			expect( mockCloneBlock ).toHaveBeenCalledWith( BLOCK_1 );
			expect( mockInsertBlock ).toHaveBeenCalledWith( BLOCK_2, null, PAGE_1 );
			expect( mockSetCurrentPage ).toHaveBeenCalledWith( PAGE_1 );
			expect( mockSelectBlock ).toHaveBeenCalledWith( BLOCK_2.clientId );
		} );

		it( 'should invoke extra function correctly when invoked with attributes', () => {
			mockCloneBlock.mockImplementationOnce( () => BLOCK_2 );

			const { moveBlockToPage } = useMoveBlockToPage( BLOCK_ID );

			moveBlockToPage( PAGE_1, ATTRS );

			expect( mockUpdateBlockAttributes ).toHaveBeenCalledWith( BLOCK_2.clientId, ATTRS );
		} );
	} );
} );
