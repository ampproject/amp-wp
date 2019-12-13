/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'deleteCurrentPage', () => {
	it( 'should work', () => {
		const { deleteCurrentPage } = setupReducer();

		const result = deleteCurrentPage( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
