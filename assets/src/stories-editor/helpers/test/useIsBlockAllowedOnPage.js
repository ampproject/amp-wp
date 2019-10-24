/**
 * Internal dependencies
 */
import useIsBlockAllowedOnPage from '../useIsBlockAllowedOnPage';

/*
 * A note on the testing here:
 *
 * `useIsBlockAllowedOnPage` is a hook and you might think it needs to be
 * tested as one.
 *
 * But because it's only a hook because `useSelect` is a hook, and as we mock
 * it below to not be a hook, we can test this as a regular non-hook function.
 */

const mockCanInsertBlockType = jest.fn();
const mockGetBlockListSettings = jest.fn();

jest.mock( '@wordpress/data', () => {
	return {
		useSelect: () => ( {
			canInsertBlockType: mockCanInsertBlockType,
			getBlockListSettings: mockGetBlockListSettings,
		} ),
	};
} );

const BLOCK_NAME = 'any';
const PAGE_ID = 1;

describe( 'useIsBlockAllowedOnPage', () => {
	it( 'should invoke proper callbacks', () => {
		useIsBlockAllowedOnPage()( BLOCK_NAME, PAGE_ID );

		expect( mockCanInsertBlockType ).toHaveBeenCalledWith( BLOCK_NAME, PAGE_ID );
		expect( mockGetBlockListSettings ).toHaveBeenCalledWith( PAGE_ID );
	} );

	it( 'should return false if element is not allowed by type regardless of block list', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => false );
		mockGetBlockListSettings.mockImplementationOnce( () => ( { allowedBlocks: [ BLOCK_NAME ] } ) );

		const result = useIsBlockAllowedOnPage()( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( false );
	} );

	it( 'should return false if no block list exist for page', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => true );
		mockGetBlockListSettings.mockImplementationOnce( () => null );

		const result = useIsBlockAllowedOnPage()( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( false );
	} );

	it( 'should return false if block list does not contain element', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => true );
		mockGetBlockListSettings.mockImplementationOnce( () => ( { allowedBlocks: [] } ) );

		const result = useIsBlockAllowedOnPage()( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( false );
	} );

	it( 'should return true iff element is allowed by both type and block list', () => {
		mockCanInsertBlockType.mockImplementationOnce( () => true );
		mockGetBlockListSettings.mockImplementationOnce( () => ( { allowedBlocks: [ BLOCK_NAME ] } ) );

		const result = useIsBlockAllowedOnPage()( BLOCK_NAME, PAGE_ID );

		expect( result ).toBe( true );
	} );
} );
