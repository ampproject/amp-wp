/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'deleteElementsById', () => {
	it( 'should remove the deleted elements', () => {
		const { restore, deleteElementsById } = setupReducer();

		// Set an initial state with a current page and some elements
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = deleteElementsById( { elementIds: [ '123', '456' ] } );

		expect( result ).toStrictEqual( {
			...initialState,
			pages: [ { id: '111', elements: [ { id: '789' } ] } ],
		} );
	} );

	it( 'should skip unknown elements', () => {
		const { restore, deleteElementsById } = setupReducer();

		// Set an initial state with a current page and some elements
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = deleteElementsById( { elementIds: [ '123', '000' ] } );

		expect( result ).toStrictEqual( {
			...initialState,
			pages: [ { id: '111', elements: [ { id: '456' }, { id: '789' } ] } ],
		} );
	} );

	it( 'should do nothing if no elements', () => {
		const { restore, deleteElementsById } = setupReducer();

		// Set an initial state with a current page and some elements
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = deleteElementsById( { elementIds: [] } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should do nothing if only unknown elements', () => {
		const { restore, deleteElementsById } = setupReducer();

		// Set an initial state with a current page and some elements
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = deleteElementsById( { elementIds: [ '000', '999' ] } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should remove any deleted elements from selection too', () => {
		const { restore, deleteElementsById } = setupReducer();

		// Set an initial state with a current page and some elements
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' }, { id: '789' } ] },
			],
			current: '111',
			selection: [ '123', '789' ],
		} );

		// 123 is selected, 456 is not
		const result = deleteElementsById( { elementIds: [ '123', '456' ] } );

		expect( result ).toStrictEqual( {
			...initialState,
			pages: [ { id: '111', elements: [ { id: '789' } ] } ],
			selection: [ '789' ],
		} );
	} );
} );
