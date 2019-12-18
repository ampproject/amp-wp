/**
 * Internal dependencies
 */
import { LAYER_DIRECTIONS } from '../../../../constants';
import { setupReducer } from './_utils';

describe( 'arrangeElement', () => {
	it( 'should move element to specified position', () => {
		const { restore, arrangeElement } = setupReducer();

		restore( getInitialState() );

		const result = arrangeElement( { elementId: '123', position: 2 } );

		expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
			'234',
			'345',
			'123',
			'456',
		] );
	} );

	it( 'should move element to front', () => {
		const { restore, arrangeElement } = setupReducer();

		restore( getInitialState() );

		const result = arrangeElement( { elementId: '234', position: LAYER_DIRECTIONS.FRONT } );

		expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
			'123',
			'345',
			'456',
			'234',
		] );
	} );

	it( 'should move element to back', () => {
		const { restore, arrangeElement } = setupReducer();

		restore( getInitialState() );

		const result = arrangeElement( { elementId: '345', position: LAYER_DIRECTIONS.BACK } );

		expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
			'345',
			'123',
			'234',
			'456',
		] );
	} );

	it( 'should move element forward', () => {
		const { restore, arrangeElement } = setupReducer();

		restore( getInitialState() );

		const result = arrangeElement( { elementId: '234', position: LAYER_DIRECTIONS.FORWARD } );

		expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
			'123',
			'345',
			'234',
			'456',
		] );
	} );

	it( 'should move element backward', () => {
		const { restore, arrangeElement } = setupReducer();

		restore( getInitialState() );

		const result = arrangeElement( { elementId: '345', position: LAYER_DIRECTIONS.BACKWARD } );

		expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
			'123',
			'345',
			'234',
			'456',
		] );
	} );

	describe( 'when there is a background element', () => {
		it( 'should not be able to move background element at all', () => {
			const { restore, arrangeElement } = setupReducer();

			restore( getInitialState( { backgroundElementId: '123' } ) );

			// Try to move bg element anywhere
			const result = arrangeElement( { elementId: '123', position: 2 } );

			expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
				'123',
				'234',
				'345',
				'456',
			] );
		} );

		it( 'should not be able to move element below background using position', () => {
			const { restore, arrangeElement } = setupReducer();

			restore( getInitialState( { backgroundElementId: '123' } ) );

			// Try to move any non-bg element to position 0
			const result = arrangeElement( { elementId: '345', position: 0 } );

			expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
				'123',
				'345', // Note that it *does* move, but not below background
				'234',
				'456',
			] );
		} );

		it( 'should not be able to move element below background using "send backwards"', () => {
			const { restore, arrangeElement } = setupReducer();

			restore( getInitialState( { backgroundElementId: '123' } ) );

			// Try to move the element just above the background further backwards.
			const result = arrangeElement( { elementId: '234', position: LAYER_DIRECTIONS.BACKWARD } );

			expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
				'123',
				'234',
				'345',
				'456',
			] );
		} );

		it( 'should not be able to move element below background using "send to back"', () => {
			const { restore, arrangeElement } = setupReducer();

			restore( getInitialState( { backgroundElementId: '123' } ) );

			// Try to move any non-bg element to position BACK
			const result = arrangeElement( { elementId: '345', position: LAYER_DIRECTIONS.BACK } );

			expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
				'123',
				'345', // Note that it *does* move, but not below background
				'234',
				'456',
			] );
		} );
	} );
} );

function getElementIdsFromCurrentPage( { pages, current } ) {
	return pages
		.find( ( { id } ) => id === current )
		.elements.map( ( { id } ) => id );
}

function getInitialState( extraProps ) {
	return {
		pages: [ {
			backgroundElementId: null,
			id: '111',
			elements: [
				{ id: '123' },
				{ id: '234' },
				{ id: '345' },
				{ id: '456' },
			],
			...extraProps,
		} ],
		current: '111',
	};
}
