/**
 * Internal dependencies
 */
import { getRgbaFromHex } from '../';

describe( 'getRgbaFromHex', () => {
	it( 'should reject invalid hex values', () => {
		expect( getRgbaFromHex( undefined ) ).toStrictEqual( [] );
		expect( getRgbaFromHex( '' ) ).toStrictEqual( [] );
		expect( getRgbaFromHex( null ) ).toStrictEqual( [] );
	} );

	it( 'should remove leading number sign', () => {
		expect( getRgbaFromHex( 'ffffff' ) ).toStrictEqual( [ 255, 255, 255, 1 ] );
		expect( getRgbaFromHex( '#ffffff' ) ).toStrictEqual( [ 255, 255, 255, 1 ] );
	} );

	it( 'should handle shorthand notation', () => {
		expect( getRgbaFromHex( '#f0f' ) ).toStrictEqual( [ 255, 0, 255, 1 ] );
	} );

	it( 'should apply opacity to  color', () => {
		expect( getRgbaFromHex( '#000000', 0 ) ).toStrictEqual( [ 0, 0, 0, 0 ] );
		expect( getRgbaFromHex( '#000000', 100 ) ).toStrictEqual( [ 0, 0, 0, 1 ] );
		expect( getRgbaFromHex( '#000000', -10 ) ).toStrictEqual( [ 0, 0, 0, 0 ] );
		expect( getRgbaFromHex( '#000000', 10000 ) ).toStrictEqual( [ 0, 0, 0, 1 ] );
	} );
} );
