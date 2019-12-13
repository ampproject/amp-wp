/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'deletePage', () => {
	it( 'should work', () => {
		const { deletePage } = setupReducer();

		const result = deletePage( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
