/**
 * Internal dependencies
 */
import { getMinimumFeaturedImageDimensions } from '../';

describe( 'getMinimumFeaturedImageDimensions', () => {
	it( 'should return size with correct aspect ration', () => {
		expect( getMinimumFeaturedImageDimensions() ).toEqual( { width: 1200, height: 675 } );
	} );
} );
