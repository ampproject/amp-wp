/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'addElements', () => {
	it( 'should work', () => {
		const { addElements } = setupReducer();

		const result = addElements( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
