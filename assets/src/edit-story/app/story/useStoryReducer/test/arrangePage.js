/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'arrangePage', () => {
	it( 'should work', () => {
		const { arrangePage } = setupReducer();

		const result = arrangePage( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
