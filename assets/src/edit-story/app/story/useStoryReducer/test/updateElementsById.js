/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'updateElementsById', () => {
	it( 'should update the given elements', () => {
		const { restore, updateElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
		} );

		const result = updateElementsById( { elementIds: [ '123', '456' ], properties: { a: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123', a: 1 }, { id: '456', a: 1 } ] },
		] );
	} );

	it( 'should skip unknown elements', () => {
		const { restore, updateElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
		} );

		const result = updateElementsById( { elementIds: [ '123', '789' ], properties: { a: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123', a: 1 }, { id: '456' } ] },
		] );
	} );

	it( 'should do nothing if no elements given', () => {
		const { restore, updateElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
		} );

		const result = updateElementsById( { elementIds: [], properties: { a: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
		] );
	} );

	it( 'should do nothing if only unknown elements given', () => {
		const { restore, updateElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
		} );

		const result = updateElementsById( { elementIds: [ '789' ], properties: { a: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
		] );
	} );
} );
