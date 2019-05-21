/**
 * Internal dependencies
 */
import { getRgbaFromHex } from '../';

describe( 'getRgbaFromHex', () => {
	it( 'should reject invalid hex values', () => {
		expect( getRgbaFromHex( undefined ) ).toEqual( [] );
		expect( getRgbaFromHex( '' ) ).toEqual( [] );
		expect( getRgbaFromHex( null ) ).toEqual( [] );
	} );

	it( 'should remove leading number sign', () => {
		expect( getRgbaFromHex( 'ffffff' ) ).toEqual( [ 255, 255, 255, 1 ] );
		expect( getRgbaFromHex( '#ffffff' ) ).toEqual( [ 255, 255, 255, 1 ] );
	} );

	it( 'should handle shorthand notation', () => {
		expect( getRgbaFromHex( '#f0f' ) ).toEqual( [ 255, 0, 255, 1 ] );
	} );

	it( 'should apply opacity to  color', () => {
		expect( getRgbaFromHex( '#000000', 0 ) ).toEqual( [ 0, 0, 0, 0 ] );
		expect( getRgbaFromHex( '#000000', 100 ) ).toEqual( [ 0, 0, 0, 1 ] );
		expect( getRgbaFromHex( '#000000', -10 ) ).toEqual( [ 0, 0, 0, 0 ] );
		expect( getRgbaFromHex( '#000000', 10000 ) ).toEqual( [ 0, 0, 0, 1 ] );
	} );
} );
