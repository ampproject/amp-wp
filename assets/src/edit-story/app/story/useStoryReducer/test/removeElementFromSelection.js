/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'removeElementFromSelection', () => {
	it( 'should work', () => {
		const { removeElementFromSelection } = setupReducer();

		const result = removeElementFromSelection( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
