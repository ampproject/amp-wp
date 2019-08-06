/**
 * Internal dependencies
 */
import { isVideoSizeExcessive } from '../';

describe( 'isVideoSizeExcessive', () => {
	// @todo Use numeric separator once using Node v12.5.0+, see https://v8.dev/features/numeric-separators
	it.each( [
		[ 3000000, true ],
		[ 1000000.1, true ],
		[ 1000000, false ],
		[ 500000, false ],
	] )( 'should return excessive video size status',
		( bytesPerSecond, expected ) => {
			expect( isVideoSizeExcessive( bytesPerSecond ) ).toBe( expected );
		}
	);
} );
