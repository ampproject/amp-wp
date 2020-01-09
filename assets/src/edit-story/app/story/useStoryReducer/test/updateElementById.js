/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'updateElementById', () => {
	it( 'should update the given element', () => {
		const { restore, updateElementById } = setupReducer();

		// Set an initial state with a current page with an element.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
		} );

		const result = updateElementById( { elementId: '123', properties: { a: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123', a: 1 }, { id: '456' } ] },
		] );
	} );

	it( 'should ignore an unknown element (on the current page)', () => {
		const { restore, updateElementById } = setupReducer();

		// Set an initial state with multiple pages with elements.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' } ] },
				{ id: '222', elements: [ { id: '456' } ] },
			],
			current: '111',
		} );

		// 456 does not exist on current page, so nothing happens
		const result = updateElementById( { elementId: '456', properties: { a: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123' } ] },
			{ id: '222', elements: [ { id: '456' } ] },
		] );
	} );
} );
