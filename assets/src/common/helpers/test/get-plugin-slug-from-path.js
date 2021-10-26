/**
 * Internal dependencies
 */
import { getPluginSlugFromFile } from '../get-plugin-slug-from-file';

describe( 'getPluginSlugFromFile', () => {
	it( 'should return an empty string if no path is provided', () => {
		expect( getPluginSlugFromFile() ).toBe( '' );
	} );

	it( 'should return correct plugin slug', () => {
		expect( getPluginSlugFromFile( 'foo' ) ).toBe( 'foo' );
		expect( getPluginSlugFromFile( 'foo.php' ) ).toBe( 'foo' );
		expect( getPluginSlugFromFile( 'foo/bar' ) ).toBe( 'foo' );
		expect( getPluginSlugFromFile( 'foo/baz.php' ) ).toBe( 'foo' );
	} );
} );
