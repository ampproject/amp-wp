/**
 * Internal dependencies
 */
import getMinimumStoryPosterDimensions from '../getMinimumStoryPosterDimensions';

describe( 'getMinimumStoryPosterDimensions', () => {
	it( 'should return size with correct aspect ration', () => {
		expect( getMinimumStoryPosterDimensions() ).toStrictEqual( { width: 1200, height: 1600 } );
	} );
} );
