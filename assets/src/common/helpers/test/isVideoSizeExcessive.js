/**
 * Internal dependencies
 */
import { isVideoSizeExcessive } from '../';
import { Mock } from './fixtures/mockClasses';

describe( 'isVideoSizeExcessive', () => {
	it( 'should return true for a video with 3 MB per second', () => {
		const attachment = new Mock();
		const filesize = 12000000;
		const length = 4;
		attachment.set( { media_details: { filesize, length } } );
		expect( isVideoSizeExcessive( attachment ) ).toBe( true );
	} );

	it( 'should return true for a video with slightly over 1 MB per second', () => {
		const attachment = new Mock();
		const filesize = 4000001;
		const length = 4;
		attachment.set( { media_details: { filesize, length } } );
		expect( isVideoSizeExcessive( attachment ) ).toBe( true );
	} );

	it( 'should return false for a video with exactly 1 MB per second', () => {
		const attachment = new Mock();
		const filesize = 4000000;
		const length = 4;
		attachment.set( { media_details: { filesize, length } } );
		expect( isVideoSizeExcessive( attachment ) ).toBe( false );
	} );

	it( 'should return false for a video of much less than 1 MB per second', () => {
		const attachment = new Mock();
		const filesize = 1000000;
		const length = 4;
		attachment.set( { media_details: { filesize, length } } );
		expect( isVideoSizeExcessive( attachment ) ).toBe( false );
	} );
} );
