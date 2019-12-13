/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'arrangeSelection', () => {
	it( 'should work', () => {
		const { arrangeSelection } = setupReducer();

		const result = arrangeSelection( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
