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

	it( 'should return the proper bytes per second for a simple call where the values are stored in the media attributes', () => {
		const attachment = new Mock();
		const filesizeInBytes = 63000000;
		const fileLength = '1:03';
		attachment.set( { attributes: { filesizeInBytes, fileLength } } );
		expect( getVideoBytesPerSecond( attachment ) ).toBe( 1000000 );
	} );

	it( 'should return null if the filesize is not defined', () => {
		const attachment = new Mock();
		const length = 5;
		attachment.set( { media_details: { length } } );
		expect( getVideoBytesPerSecond( attachment ) ).toBeNull( );
	} );

	it( 'should return null if the length is not defined', () => {
		const attachment = new Mock();
		const filesize = 8000000;
		attachment.set( { media_details: { filesize } } );
		expect( getVideoBytesPerSecond( attachment ) ).toBeNull( );
	} );
} );
