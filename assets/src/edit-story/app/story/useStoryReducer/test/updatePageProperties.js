/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'updatePageProperties', () => {
	it( 'should do nothing if page not found', () => {
		const { restore, updatePageProperties } = setupReducer();

		// Set an initial state with multiple pages.
		const initialState = restore( { pages: [ { id: '111' }, { id: '222' } ] } );

		// There's no page 333
		const result = updatePageProperties( { pageId: '333', properties: { x: 1 } } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should update properties of the given page', () => {
		const { restore, updatePageProperties } = setupReducer();

		// Set an initial state with multiple pages.
		restore( { pages: [ { id: '111' }, { id: '222' } ] } );

		// Add x=1 to page 111
		const result = updatePageProperties( { pageId: '111', properties: { x: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', x: 1 },
			{ id: '222' },
		] );
	} );

	it( 'should disallow updating reserve properties', () => {
		const { restore, updatePageProperties } = setupReducer();

		// Set an initial state with multiple pages.
		restore( { pages: [ { id: '111' }, { id: '222' } ] } );

		// Try modifying illegal props besides x=2
		const result = updatePageProperties( {
			pageId: '111',
			properties: { x: 2, id: '333', elements: [ 'many' ], backgroundElementId: 'nothing' },
		} );

		expect( result.pages ).toStrictEqual( [
			{ id: '111', x: 2 },
			{ id: '222' },
		] );
	} );
} );
