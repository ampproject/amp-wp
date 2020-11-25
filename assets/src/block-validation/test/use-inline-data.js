/**
 * Internal dependencies
 */
import { useInlineData } from '../use-inline-data';

describe( 'useInlineData', () => {
	it( 'reads global data', () => {
		global.globalVariable = 'test';

		expect( useInlineData( 'globalVariable' ) ).toBe( 'test' );
	} );

	it( 'falls back to a default', () => {
		global.globalVariable = undefined;

		expect( useInlineData( 'globalVariable', 'default-value' ) ).toBe( 'default-value' );
	} );
} );
