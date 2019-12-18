/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'deleteSelectedElements', () => {
	it( 'should remove the selected elements and clear selection', () => {
		const { restore, deleteSelectedElements } = setupReducer();

		// Set an initial state with a current page and some elements selected.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [ '123', '456' ],
		} );

		const result = deleteSelectedElements();

		expect( result ).toStrictEqual( {
			pages: [ { id: '111', elements: [ { id: '789' } ] } ],
			current: '111',
			selection: [],
		} );
	} );

	it( 'should do nothing if no elements selected', () => {
		const { restore, deleteSelectedElements } = setupReducer();

		// Set an initial state with a current page and some elements, none selected.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = deleteSelectedElements();

		expect( result ).toStrictEqual( {
			pages: [ { id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] } ],
			current: '111',
			selection: [],
		} );
	} );
} );
