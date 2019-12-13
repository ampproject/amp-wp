/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'updateElementById', () => {
	it( 'should work', () => {
		const { updateElementById } = setupReducer();

		const result = updateElementById( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
