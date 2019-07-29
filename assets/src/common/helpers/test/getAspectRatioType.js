/**
 * Internal dependencies
 */
import { getAspectRatioType } from '../';

describe( 'getAspectRatioType', () => {
	it( 'should return landscape when the aspect ratio is landscape', () => {
		expect( getAspectRatioType( 1400, 1000 ) ).toStrictEqual( 'landscape' );
	} );
	it( 'should return portrait when the aspect ratio is portrait', () => {
		expect( getAspectRatioType( 1400, 1800 ) ).toStrictEqual( 'portrait' );
	} );
	it( 'should return square when the aspect ratio is square', () => {
		expect( getAspectRatioType( 1200, 1200 ) ).toStrictEqual( 'square' );
	} );
	it( 'should return null when the arguments are null', () => {
		expect( getAspectRatioType( null, null ) ).toStrictEqual( null );
	} );
} );
