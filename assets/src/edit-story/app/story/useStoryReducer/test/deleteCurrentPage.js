/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'deleteCurrentPage', () => {
	it( 'should update the current page to the next one if possible, otherwise previous', () => {
		const { restore, deleteCurrentPage } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' }, { id: '333' }, { id: '444' } ],
			current: '333',
		} );

		// Delete page 333 (not last) and 444 becomes current
		const firstResult = deleteCurrentPage();
		const firstSetOfPageIds = firstResult.pages.map( ( { id } ) => id );
		expect( firstSetOfPageIds ).toStrictEqual( [ '111', '222', '444' ] );
		expect( firstResult.current ).toStrictEqual( '444' );

		// Now delete page 444 (last) and 222 becomes current
		const secondResult = deleteCurrentPage();
		const secondSetOfPageIds = secondResult.pages.map( ( { id } ) => id );
		expect( secondSetOfPageIds ).toStrictEqual( [ '111', '222' ] );
		expect( secondResult.current ).toStrictEqual( '222' );
	} );
} );
