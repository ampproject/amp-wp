/**
 * Internal dependencies
 */
import { getPluginSlugFromPath } from '../index';

describe( 'getPluginSlugFromPath', () => {
	it( 'should return an empty string if no path is provided', () => {
		expect( getPluginSlugFromPath() ).toBe( '' );
	} );

	it( 'should return correct plugin slug', () => {
		expect( getPluginSlugFromPath( 'foo' ) ).toBe( 'foo' );
		expect( getPluginSlugFromPath( 'foo.php' ) ).toBe( 'foo' );
		expect( getPluginSlugFromPath( 'foo/bar' ) ).toBe( 'bar' );
		expect( getPluginSlugFromPath( 'foo/baz.php' ) ).toBe( 'baz' );
	} );
} );
