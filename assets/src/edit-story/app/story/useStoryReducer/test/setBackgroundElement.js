/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'setBackgroundElement', () => {
	it( 'should work', () => {
		const { setBackgroundElement } = setupReducer();

		const result = setBackgroundElement( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
