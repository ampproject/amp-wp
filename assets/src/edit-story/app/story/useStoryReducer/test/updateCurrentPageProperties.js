/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'updateCurrentPageProperties', () => {
	it( 'should update properties of the current page', () => {
		const { restore, updateCurrentPageProperties } = setupReducer();

		// Set an initial state with multiple pages.
		restore( { pages: [ { id: '111' }, { id: '222' } ], current: '222' } );

		// Add x=1 to page 222
		const result = updateCurrentPageProperties( { properties: { x: 1 } } );

		expect( result.pages ).toStrictEqual( [
			{ id: '111' },
			{ id: '222', x: 1 },
		] );
	} );
} );
