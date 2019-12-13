/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'clearSelection', () => {
	it( 'should work', () => {
		const { clearSelection } = setupReducer();

		const result = clearSelection( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
