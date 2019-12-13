/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'addElementToSelection', () => {
	it( 'should work', () => {
		const { addElementToSelection } = setupReducer();

		const result = addElementToSelection( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
