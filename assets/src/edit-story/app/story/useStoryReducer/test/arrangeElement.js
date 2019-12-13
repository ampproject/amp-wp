/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'arrangeElement', () => {
	it( 'should work', () => {
		const { arrangeElement } = setupReducer();

		const result = arrangeElement( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
