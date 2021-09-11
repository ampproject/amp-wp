/**
 * Internal dependencies
 */
import Indentation from '..';

const NBSP = String.fromCharCode( 160 );

describe( 'Indentation', () => {
	it( 'returns the specified number of non-breaking spaces', () => {
		expect( Indentation( { size: 4 } ) ).toBe( NBSP + NBSP + NBSP + NBSP );
	} );

	it( 'returns the specified number of non-breaking tabs', () => {
		expect( Indentation( { size: 2, isTab: true } ) ).toBe( '		' );
	} );
} );
