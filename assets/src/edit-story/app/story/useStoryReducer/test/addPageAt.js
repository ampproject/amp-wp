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

		const result = addPageAt( { page: { id: '123' }, position: 1 } );
		expect( getPageIds( result ) ).toStrictEqual( [ '111', '123', '222' ] );
	} );

	it( 'should treat illegal positions as "add after current"', () => {
		const { restore, addPageAt } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' } ],
			current: '222',
		} );

		const firstResult = addPageAt( { page: { id: '123' }, position: -50 } );
		expect( getPageIds( firstResult ) ).toStrictEqual( [ '111', '222', '123' ] );

		const secondResult = addPageAt( { page: { id: '321' }, position: 50 } );
		expect( getPageIds( secondResult ) ).toStrictEqual( [ '111', '222', '123', '321' ] );
	} );
} );

function getPageIds( { pages } ) {
	return pages.map( ( { id } ) => id );
}
