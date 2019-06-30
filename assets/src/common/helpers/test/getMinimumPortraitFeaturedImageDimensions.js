/**
 * Internal dependencies
 */
import { getMinimumPortraitFeaturedImageDimensions } from '../';

describe( 'getMinimumPortraitFeaturedImageDimensions', () => {
	it( 'should return size with correct portrait aspect ratio', () => {
		expect( getMinimumPortraitFeaturedImageDimensions() ).toEqual( { width: 1200, height: 1200 * ( 16 / 9 ) } );
	} );
} );
