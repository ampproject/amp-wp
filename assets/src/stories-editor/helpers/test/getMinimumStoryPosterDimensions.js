/**
 * Internal dependencies
 */
import { getMinimumStoryPosterDimensions } from '../';

describe( 'getMinimumStoryPosterDimensions', () => {
	it( 'should return size with correct aspect ration', () => {
		expect( getMinimumStoryPosterDimensions() ).toEqual( { width: 1200, height: 1600 } );
	} );
} );
