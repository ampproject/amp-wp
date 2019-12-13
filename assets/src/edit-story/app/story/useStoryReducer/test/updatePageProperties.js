/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'updatePageProperties', () => {
	it( 'should work', () => {
		const { updatePageProperties } = setupReducer();

		const result = updatePageProperties( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
