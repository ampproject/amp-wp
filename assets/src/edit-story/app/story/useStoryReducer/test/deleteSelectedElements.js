/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'deleteSelectedElements', () => {
	it( 'should work', () => {
		const { deleteSelectedElements } = setupReducer();

		const result = deleteSelectedElements( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
