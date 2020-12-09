/**
 * Internal dependencies
 */
import { getMinimumFeaturedImageDimensions } from '../';

describe( 'getMinimumFeaturedImageDimensions', () => {
	it( 'should return size with correct aspect ratio', () => {
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
} );

