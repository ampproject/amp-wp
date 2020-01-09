/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'setCurrentPage', () => {
	it( 'should set current page if it exists', () => {
		const { restore, setCurrentPage } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' } ],
			current: '111',
		} );

		// Update current page to 222
		const result = setCurrentPage( { pageId: '222' } );

		expect( result.current ).toStrictEqual( '222' );
	} );

	it( 'should ignore unknown pages', () => {
		const { restore, setCurrentPage } = setupReducer();

		// Set an initial state with multiple pages.
		const initialState = restore( {
			pages: [ { id: '111' }, { id: '222' } ],
			current: '111',
		} );

		// Unknown page 333, do nothing
		const result = setCurrentPage( { pageId: '333' } );

		expect( result ).toStrictEqual( initialState );
	} );
} );
