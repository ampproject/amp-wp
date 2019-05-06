/**
 * Internal dependencies
 */
import { getBackgroundColorWithOpacity } from '../';

describe( 'getBackgroundColorWithOpacity', () => {
	it( 'returns unmodified custom background color if there is no opacity', () => {
		expect( getBackgroundColorWithOpacity( [], undefined, '#ffffff' ) ).toBe( '#ffffff' );
	} );
} );
