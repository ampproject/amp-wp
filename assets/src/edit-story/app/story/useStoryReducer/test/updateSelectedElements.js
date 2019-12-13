/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'updateSelectedElements', () => {
	it( 'should work', () => {
		const { updateSelectedElements } = setupReducer();

		const result = updateSelectedElements( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
