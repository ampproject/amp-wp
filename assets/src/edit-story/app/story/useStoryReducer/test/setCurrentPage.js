/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'setCurrentPage', () => {
	it( 'should work', () => {
		const { setCurrentPage } = setupReducer();

		const result = setCurrentPage( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
