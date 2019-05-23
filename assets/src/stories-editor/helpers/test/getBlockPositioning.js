/**
 * Internal dependencies
 */
import { getBlockPositioning } from '../';

describe( 'getBlockPositioning', () => {
	it( 'should return correct coordinates', () => {
		expect( getBlockPositioning( 50, 50, 1 ) ).toEqual( { top: 9.54433226690091, left: 32.52921697349392 } );
	} );
} );
