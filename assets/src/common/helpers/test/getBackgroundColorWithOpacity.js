/**
 * Internal dependencies
 */
import { getBackgroundColorWithOpacity } from '../';

describe( 'getBackgroundColorWithOpacity', () => {
	it( 'should consider maximum opacity if there is no opacity set', () => {
		expect( getBackgroundColorWithOpacity( [], undefined, '#ffffff' ) ).toBe( 'rgba(255, 255, 255, 1)' );
	} );

	it( 'should return correct value with a non-matching background color slug', () => {
		const backgroundColor = {
			color: '#ff6900',
			slug: 'some-non-matching-slug',
		};
		expect( getBackgroundColorWithOpacity( [], backgroundColor, undefined, 65 ) )
			.toBe( 'rgba(255, 105, 0, 0.65)' );
	} );
} );
