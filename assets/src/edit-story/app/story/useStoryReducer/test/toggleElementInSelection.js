/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'toggleElementInSelection', () => {
	it( 'should work', () => {
		const { toggleElementInSelection } = setupReducer();

		const result = toggleElementInSelection( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
