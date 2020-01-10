/**
 * Internal dependencies
 */
import {
	default as maybeSetInitialPositioning,
	POSITION_TOP_LIMIT,
	POSITION_TOP_GAP,
	POSITION_TOP_DEFAULT,
} from '../maybeSetInitialPositioning';
import { ALLOWED_CHILD_BLOCKS, ALLOWED_MOVABLE_BLOCKS } from '../../constants';

const mockGetBlockRootClientId = jest.fn();
const mockGetBlock = jest.fn();
const mockUpdateBlockAttributes = jest.fn();
const mockCreateBlock = jest.fn();

jest.mock( '@wordpress/data', () => {
	return {
		select: () => ( {
			getBlockRootClientId: ( ...args ) => mockGetBlockRootClientId( ...args ),
			getBlock: ( ...args ) => mockGetBlock( ...args ),
		} ),

		dispatch: () => ( {
			updateBlockAttributes: ( ...args ) => mockUpdateBlockAttributes( ...args ),
		} ),
	};
} );
jest.mock( '@wordpress/blocks', () => ( {
	createBlock: ( ...args ) => mockCreateBlock( ...args ),
} ) );

const setMockImplementation = ( blockData = {} ) => {
	mockGetBlock.mockReset();
	mockGetBlock.mockImplementation( ( clientId ) => ( {
		clientId,
		name: ALLOWED_CHILD_BLOCK,
		innerBlocks: [],
		...blockData,
		attributes: {
			positionTop: 0,
			...( blockData.attributes || {} ),
		},
	} ) );
	mockGetBlockRootClientId.mockReset();
	mockGetBlockRootClientId.mockImplementation( () => PARENT_BLOCK_ID );

	mockUpdateBlockAttributes.mockReset();

	mockCreateBlock.mockReset();
	mockCreateBlock.mockImplementation( ( name ) => ( { name, attributes: {} } ) );
};

const BLOCK_ID = 1;
const PARENT_BLOCK_ID = 2;

const ALLOWED_CHILD_BLOCK = ALLOWED_CHILD_BLOCKS[ 0 ];
const DISALLOWED_CHILD_BLOCK = 'something-disallowed';

const MOVABLE_BLOCK = ALLOWED_MOVABLE_BLOCKS[ 0 ];
const IMMOVABLE_BLOCK = 'something-immovable';

describe( 'maybeSetInitialPositioning', () => {
	beforeEach( () => {
		setMockImplementation();
	} );

	it( 'should invoke getBlock', () => {
		mockGetBlock.mockImplementationOnce( () => null );
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockGetBlock ).toHaveBeenCalledWith( BLOCK_ID );
	} );

	it( 'should do nothing if no block for id', () => {
		mockGetBlock.mockImplementationOnce( () => null );
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockGetBlockRootClientId ).not.toHaveBeenCalled();
		expect( mockUpdateBlockAttributes ).not.toHaveBeenCalled();
	} );

	it( 'should do nothing if block name not in allowed child blocks', () => {
		setMockImplementation( { name: DISALLOWED_CHILD_BLOCK } );
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockGetBlockRootClientId ).not.toHaveBeenCalled();
		expect( mockUpdateBlockAttributes ).not.toHaveBeenCalled();
	} );

	it( 'should invoke getBlock and getBlockRootClientId if block exists', () => {
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockGetBlockRootClientId ).toHaveBeenCalledWith( BLOCK_ID );
		// first call with given black
		expect( mockGetBlock ).toHaveBeenNthCalledWith( 1, BLOCK_ID );
		// second call with parent block
		expect( mockGetBlock ).toHaveBeenNthCalledWith( 2, PARENT_BLOCK_ID );
	} );

	it( 'should do nothing if block is not at 0', () => {
		setMockImplementation( { attributes: { positionTop: 1 } } );
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockUpdateBlockAttributes ).not.toHaveBeenCalled();
	} );

	it( 'should do nothing if no parent block', () => {
		// will return true for given block, but false for parent block
		mockGetBlock.mockImplementation( ( id ) => id === BLOCK_ID );
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockUpdateBlockAttributes ).not.toHaveBeenCalled();
	} );

	it( 'should do nothing if new block would be different', () => {
		// make sure newly created block has some attribute that won't match existing block
		mockCreateBlock.mockImplementationOnce( ( name ) => ( { name, attributes: { x: 1 } } ) );
		maybeSetInitialPositioning( BLOCK_ID );

		expect( mockUpdateBlockAttributes ).not.toHaveBeenCalled();
	} );

	it( 'should set top to gap if no inner blocks', () => {
		maybeSetInitialPositioning( BLOCK_ID );

		const expected = POSITION_TOP_GAP;

		expect( mockUpdateBlockAttributes ).toHaveBeenCalledWith( BLOCK_ID, { positionTop: expected } );
	} );

	it( 'should set top to single child node top + gap if inner blocks', () => {
		const positionTop = 20;
		setMockImplementation( { innerBlocks: [
			{ name: MOVABLE_BLOCK, attributes: { positionTop } },
		] } );

		maybeSetInitialPositioning( BLOCK_ID );

		const expected = positionTop + POSITION_TOP_GAP;

		expect( mockUpdateBlockAttributes ).toHaveBeenCalledWith( BLOCK_ID, { positionTop: expected } );
	} );

	it( 'should set top to max child node top + gap if inner blocks', () => {
		const maxPositionTop = 20;
		setMockImplementation( { innerBlocks: [
			{ name: MOVABLE_BLOCK, attributes: { positionTop: maxPositionTop } },
			{ name: MOVABLE_BLOCK, attributes: { positionTop: maxPositionTop - 1 } },
		] } );

		maybeSetInitialPositioning( BLOCK_ID );

		const expected = maxPositionTop + POSITION_TOP_GAP;

		expect( mockUpdateBlockAttributes ).toHaveBeenCalledWith( BLOCK_ID, { positionTop: expected } );
	} );

	it( 'should ignore immovable child nodes when finding top position', () => {
		const maxPositionTop = 20;
		setMockImplementation( { innerBlocks: [
			{ name: IMMOVABLE_BLOCK, attributes: { positionTop: Number.NaN } },
			{ name: MOVABLE_BLOCK, attributes: { positionTop: maxPositionTop } },
			{ name: MOVABLE_BLOCK, attributes: { positionTop: maxPositionTop - 1 } },
		] } );

		maybeSetInitialPositioning( BLOCK_ID );

		const expected = maxPositionTop + POSITION_TOP_GAP;

		expect( mockUpdateBlockAttributes ).toHaveBeenCalledWith( BLOCK_ID, { positionTop: expected } );
	} );

	it( 'should set top to default if max child node top is above limit', () => {
		const maxPositionTop = POSITION_TOP_LIMIT + 1;
		setMockImplementation( { innerBlocks: [
			{ name: MOVABLE_BLOCK, attributes: { positionTop: maxPositionTop } },
		] } );

		maybeSetInitialPositioning( BLOCK_ID );

		const expected = POSITION_TOP_DEFAULT;

		expect( mockUpdateBlockAttributes ).toHaveBeenCalledWith( BLOCK_ID, { positionTop: expected } );
	} );
} );
