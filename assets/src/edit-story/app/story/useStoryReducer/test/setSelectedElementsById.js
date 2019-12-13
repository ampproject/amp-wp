/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe.skip( 'setSelectedElementsById', () => {
	it( 'should work', () => {
		const { setSelectedElementsById } = setupReducer();

		const result = setSelectedElementsById( { } );

		expect( result ).not.toStrictEqual( {} );
	} );
} );
