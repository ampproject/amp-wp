/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'deleteElementById', () => {
	it( 'should work', () => {
		const { deleteElementById } = setupReducer();

		const result = deleteElementById( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
