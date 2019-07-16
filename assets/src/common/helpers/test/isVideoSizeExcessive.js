/**
 * Internal dependencies
 */
import { isVideoSizeExcessive } from '../';

describe( 'isVideoSizeExcessive', () => {
	it( 'should return true for a video with 3 MB per second', () => {
		const videoSize = 12000000 / 4;
		expect( isVideoSizeExcessive( videoSize ) ).toBe( true );
	} );

	it( 'should return true for a video with slightly over 1 MB per second', () => {
		const videoSize = 4000001 / 4;
		expect( isVideoSizeExcessive( videoSize ) ).toBe( true );
	} );

	it( 'should return false for a video with exactly 1 MB per second', () => {
		const videoSize = 4000000 / 4;
		expect( isVideoSizeExcessive( videoSize ) ).toBe( false );
	} );

	it( 'should return false for a video of much less than 1 MB per second', () => {
		const videoSize = 4000000 / 4;
		expect( isVideoSizeExcessive( videoSize ) ).toBe( false );
	} );
} );
