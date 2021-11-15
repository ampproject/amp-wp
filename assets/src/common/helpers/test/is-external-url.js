/**
 * Internal dependencies
 */
import { isExternalUrl } from '../is-external-url';

describe( 'isExternalUrl', () => {
	it( 'should return true if of a URL is external and false otherwise', () => {
		expect( isExternalUrl( 'https://example.com/' ) ).toBe( true );
		expect( isExternalUrl( 'https://localhost/' ) ).toBe( false );
	} );
} );
