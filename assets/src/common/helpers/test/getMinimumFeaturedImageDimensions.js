/**
 * Internal dependencies
 */
import { getMinimumFeaturedImageDimensions } from '../';

describe( 'getMinimumFeaturedImageDimensions', () => {
	it( 'should return size with correct aspect ratio', () => {
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return default values if invalid width is supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumWidth: 'test',
			featuredImageMinimumHeight: 675,
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return default values if invalid height is supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumWidth: 1200,
			featuredImageMinimumHeight: 'test',
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return supplied values if valid height and width is supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumWidth: '1200',
			featuredImageMinimumHeight: '1200',
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 1200 } );
	} );
} );

