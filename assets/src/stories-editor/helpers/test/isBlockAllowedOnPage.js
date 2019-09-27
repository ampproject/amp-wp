/**
 * Internal dependencies
 */
import isBlockAllowedOnPage from '../isBlockAllowedOnPage';

const mockCanInsertBlockType = jest.fn();
const mockGetBlockListSettings = jest.fn();

jest.mock( '@wordpress/data', () => {
	return {
		select: () => ( {
			// The callbacks have to be invoked inside the call, otherwise jest can't resolve the variables because of hoisting.
			//
			// I.e. this won't work even though it seems obvious:
			// canInsertBlockType: mockCanInsertBlockType,
			canInsertBlockType: ( ...args ) => mockCanInsertBlockType( ...args ),
			getBlockListSettings: ( ...args ) => mockGetBlockListSettings( ...args ),
		} ),
	};
} );

const BLOCK_NAME = 'any';
const PAGE_ID = 1;

describe( 'isBlockAllowedOnPage', () => {
	it( 'should invoke proper callbacks', () => {
		isBlockAllowedOnPage( BLOCK_NAME, PAGE_ID );

		expect( mockCanInsertBlockType ).toHaveBeenCalledWith( BLOCK_NAME, PAGE_ID );
		expect( mockGetBlockListSettings ).toHaveBeenCalledWith( PAGE_ID );
	} );

	it( 'should return false if element is not allowed by type regardless of block list', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => false );
		mockGetBlockListSettings.mockImplementationOnce( () => ( { allowedBlocks: [ BLOCK_NAME ] } ) );

		const result = isBlockAllowedOnPage( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( false );
	} );

	it( 'should return false if element is no block list exist for page', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => true );
		mockGetBlockListSettings.mockImplementationOnce( () => null );

		const result = isBlockAllowedOnPage( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( false );
	} );

	it( 'should return false if element is block list does not contain element', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => true );
		mockGetBlockListSettings.mockImplementationOnce( () => ( { allowedBlocks: [] } ) );

		const result = isBlockAllowedOnPage( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( false );
	} );

	it( 'should return true only iff element is allowed by both type and block list', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => true );
		mockGetBlockListSettings.mockImplementationOnce( () => ( { allowedBlocks: [ BLOCK_NAME ] } ) );

		const result = isBlockAllowedOnPage( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( true );
	} );
} );
