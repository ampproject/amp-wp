/**
 * Internal dependencies
 */
import { getVideoBytesPerSecond } from '../';
import { Mock } from './fixtures/mockClasses';

describe( 'getVideoBytesPerSecond', () => {
	it( 'should return the proper bytes per second for a simple call', () => {
		const attachment = new Mock();
		const filesize = 6000000;
		const length = 3;
		attachment.set( { media_details: { filesize, length } } );
		expect( getVideoBytesPerSecond( attachment ) ).toBe( 2000000 );
	} );

	it( 'should return null if the filesize is not defined', () => {
		const attachment = new Mock();
		const length = 5;
		attachment.set( { media_details: { length } } );
		expect( getVideoBytesPerSecond( attachment ) ).toBe( null );
	} );

	it( 'should return null if the length is not defined', () => {
		const attachment = new Mock();
		const filesize = 8000000;
		attachment.set( { media_details: { filesize } } );
		expect( getVideoBytesPerSecond( attachment ) ).toBe( null );
	} );
} );
