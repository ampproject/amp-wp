/**
 * Internal dependencies
 */
import { getMinimumFeaturedImageDimensions } from '../';

describe( 'getMinimumFeaturedImageDimensions', () => {
	it( 'should return size with correct aspect ratio', () => {
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return default values if invalid width and valid height is supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumWidth: 'test',
			featuredImageMinimumHeight: 675,
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return default values if width is not supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumHeight: 675,
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return default values if height is not supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumWidth: 1200,
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return default values if invalid height and valid width is supplied', () => {
		window.ampBlockEditor = {
			featuredImageMinimumHeight: 'test',
			featuredImageMinimumWidth: 1200,
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 675 } );
	} );
	it( 'should return supplied values for valid height and valid width', () => {
		window.ampBlockEditor = {
			featuredImageMinimumWidth: '1200',
			featuredImageMinimumHeight: '1200',
		};
		expect( getMinimumFeaturedImageDimensions() ).toStrictEqual( { width: 1200, height: 1200 } );
	} );
} );

