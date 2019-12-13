/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'updateElementsById', () => {
	it( 'should work', () => {
		const { updateElementsById } = setupReducer();

		const result = updateElementsById( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
