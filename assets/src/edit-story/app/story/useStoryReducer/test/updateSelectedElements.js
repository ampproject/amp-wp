/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'updateSelectedElements', () => {
	it( 'should update the selected elements', () => {
		const { restore, updateSelectedElements } = setupReducer();

		// Set an initial state with a current page and some elements selected.
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [ '123', '456' ],
		} );

		const result = updateSelectedElements( { properties: { a: 1 } } );

		expect( result ).toStrictEqual( {
			...initialState,
			pages: [ { id: '111', elements: [ { id: '123', a: 1 }, { id: '456', a: 1 }, { id: '789' } ] } ],
			selection: [ '123', '456' ],
		} );
	} );

	it( 'should do nothing if no elements selected', () => {
		const { restore, updateSelectedElements } = setupReducer();

		// Set an initial state with a current page and some elements, none selected.
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = updateSelectedElements( { properties: { a: 1 } } );

		expect( result ).toStrictEqual( initialState );
	} );
} );
