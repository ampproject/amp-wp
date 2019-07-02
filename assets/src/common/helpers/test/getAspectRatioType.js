/**
 * Internal dependencies
 */
import { getAspectRatioType } from '../';

describe( 'getAspectRatioType', () => {
	it( 'should return landscape when the aspect ratio is landscape', () => {
		expect( getAspectRatioType( 1400, 1000 ) ).toEqual( 'landscape' );
	} );
	it( 'should return portrait when the aspect ratio is portrait', () => {
		expect( getAspectRatioType( 1400, 1800 ) ).toEqual( 'portrait' );
	} );
	it( 'should return square when the aspect ratio is square', () => {
		expect( getAspectRatioType( 1200, 1200 ) ).toEqual( 'square' );
	} );
	it( 'should return null when the arguments are null', () => {
		expect( getAspectRatioType( null, null ) ).toEqual( null );
	} );
} );
