/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'addPageAt', () => {
	it( 'should add a page at the given position', () => {
		const { restore, addPageAt } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' } ],
			current: '222',
		} );

		const result = addPageAt( { properties: { id: '123' }, position: 1 } );
		const pageIds = result.pages.map( ( { id } ) => id );
		expect( pageIds ).toStrictEqual( [ '111', '123', '222' ] );
	} );

	it( 'should treat illegal positions as "add after current"', () => {
		const { restore, addPageAt } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' } ],
			current: '222',
		} );

		const firstResult = addPageAt( { properties: { id: '123' }, position: -50 } );
		const firstSetOfPageIds = firstResult.pages.map( ( { id } ) => id );
		expect( firstSetOfPageIds ).toStrictEqual( [ '111', '222', '123' ] );

		const secondResult = addPageAt( { properties: { id: '321' }, position: 50 } );
		const secondSetOfPageIds = secondResult.pages.map( ( { id } ) => id );
		expect( secondSetOfPageIds ).toStrictEqual( [ '111', '222', '123', '321' ] );
	} );
} );
