/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'deleteElementById', () => {
	it( 'should delete the given element', () => {
		const { restore, deleteElementById } = setupReducer();

		// Set an initial state with a current page with an element.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
			selection: [],
		} );

		const result = deleteElementById( { elementId: '123' } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '456' } ] },
		] );
	} );

	it( 'should ignore an unknown element (on the current page)', () => {
		const { restore, deleteElementById } = setupReducer();

		// Set an initial state with multiple pages with elements.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' } ] },
				{ id: '222', elements: [ { id: '456' } ] },
			],
			current: '111',
		} );

		// 456 does not exist on current page, so nothing happens
		const result = deleteElementById( { elementId: '456' } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', elements: [ { id: '123' } ] },
			{ id: '222', elements: [ { id: '456' } ] },
		] );
	} );

	it( 'should remove the deleted element from selection if exists', () => {
		const { restore, deleteElementById } = setupReducer();

		// Set an initial state with a current page and a selected element.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: '123' }, { id: '456' } ] },
			],
			current: '111',
			selection: [ '123', '456' ],
		} );

		const result = deleteElementById( { elementId: '123' } );

		expect( result ).toStrictEqual( {
			pages: [ { id: '111', elements: [ { id: '456' } ] } ],
			current: '111',
			selection: [ '456' ],
		} );
	} );

	it( 'should unset background element id if background element is deleted', () => {
		const { restore, deleteElementById } = setupReducer();

		// Set an initial state with a current page and a selected element.
		restore( {
			pages: [
				{
					backgroundElementId: '123',
					id: '111',
					elements: [ { id: '123' }, { id: '456' } ],
				},
			],
			current: '111',
			selection: [ '123', '456' ],
		} );

		const result = deleteElementById( { elementId: '123' } );

		expect( result ).toStrictEqual( {
			pages: [ { backgroundElementId: null, id: '111', elements: [ { id: '456' } ] } ],
			current: '111',
			selection: [ '456' ],
		} );
	} );
} );
