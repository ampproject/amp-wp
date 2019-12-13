/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'deleteElementsById', () => {
	it( 'should work', () => {
		const { deleteElementsById } = setupReducer();

		const result = deleteElementsById( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
