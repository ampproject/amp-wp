/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'clearBackgroundElement', () => {
	it( 'should work', () => {
		const { clearBackgroundElement } = setupReducer();

		const result = clearBackgroundElement( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
