/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'addPage', () => {
	it( 'should add a page and make sure to initialise elements and background element', () => {
		const { addPage } = setupReducer();

		const result = addPage( { properties: { id: '123' } } );

		expect( result.pages ).toStrictEqual( [
			{
				id: '123',
				elements: [],
				backgroundElementId: null,
			},
		] );
	} );

	it( 'should make the new page current', () => {
		const { restore, addPage } = setupReducer();

		// Set an initial state with a different current page.
		restore( {
			pages: [ { id: '111' } ],
			current: '111',
		} );

		const result = addPage( { properties: { id: '123' } } );

		expect( result.current ).toStrictEqual( '123' );
	} );

	it( 'should insert the new page just after current one', () => {
		const { restore, addPage } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' } ],
			current: '111',
		} );

		const result = addPage( { properties: { id: '123' } } );

		const pageIds = result.pages.map( ( { id } ) => id );

		expect( pageIds ).toStrictEqual( [ '111', '123', '222' ] );
	} );
} );
