/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'arrangeSelection', () => {
	it( 'should arrange a single selected element to the desired position', () => {
		const { restore, arrangeSelection } = setupReducer();

		restore( getInitialState( [ '123' ] ) );

		const result = arrangeSelection( { position: 2 } );

		expect( getElementIdsFromCurrentPage( result ) ).toStrictEqual( [
			'234',
			'345',
			'123',
			'456',
		] );
	} );

	it( 'should do nothing if there is no selection', () => {
		const { restore, arrangeSelection } = setupReducer();

		const initialState = restore( getInitialState( [] ) );

		const result = arrangeSelection( { position: 2 } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should do nothing if there is multi-selection', () => {
		const { restore, arrangeSelection } = setupReducer();

		const initialState = restore( getInitialState( [ '123', '456' ] ) );

		const result = arrangeSelection( { position: 2 } );

		expect( result ).toStrictEqual( initialState );
	} );
} );

function getElementIdsFromCurrentPage( { pages, current } ) {
	return pages
		.find( ( { id } ) => id === current )
		.elements.map( ( { id } ) => id );
}

function getInitialState( selection ) {
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
		} ],
		current: '111',
		selection,
	};
}

