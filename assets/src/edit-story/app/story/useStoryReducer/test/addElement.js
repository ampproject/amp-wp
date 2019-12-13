/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'addElement', () => {
	it( 'should work', () => {
		const { addElement } = setupReducer();

		const result = addElement( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
