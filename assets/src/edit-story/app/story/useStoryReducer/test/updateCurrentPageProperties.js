/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'updateCurrentPageProperties', () => {
	it( 'should work', () => {
		const { updateCurrentPageProperties } = setupReducer();

		const result = updateCurrentPageProperties( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
