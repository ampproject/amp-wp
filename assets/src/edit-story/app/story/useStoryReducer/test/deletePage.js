/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'deletePage', () => {
	it( 'should delete the specified page', () => {
		const { restore, deletePage } = setupReducer();

		// Set an initial state with multiple pages.
		restore( { pages: [ { id: '111' }, { id: '222' } ] } );

		const result = deletePage( { pageId: '222' } );

		expect( result.pages ).toStrictEqual( [ { id: '111' } ] );
	} );

	// Disable reason: Awaiting UX decision on what should actually happen
	// eslint-disable-next-line jest/no-disabled-tests
	it.skip( 'should not delete the page if it\'s the only page', () => {
		const { restore, deletePage } = setupReducer();

		// Set an initial state with only one page.
		const initialState = restore( { pages: [ { id: '111' } ] } );

		const result = deletePage( { pageId: '111' } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should ignore unknown page ids', () => {
		const { restore, deletePage } = setupReducer();

		// Set an initial state with multiple pages.
		const initialState = restore( { pages: [ { id: '111' }, { id: '222' } ] } );

		const result = deletePage( { pageId: '333' } );

		expect( result ).toStrictEqual( initialState );
	} );
} );
