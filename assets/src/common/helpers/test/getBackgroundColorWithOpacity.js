/**
 * Internal dependencies
 */
import { getBackgroundColorWithOpacity } from '../';

describe( 'getBackgroundColorWithOpacity', () => {
	it( 'should return correct value with a matching color value', () => {
		const colors = [
			{
				color: '#ffffff',
				slug: 'white',
			},
			{
				color: '#000000',
				slug: 'black',
			},
		];

		const backgroundColor = {
			color: '#ffffff',
			slug: 'grey',
		};

		expect( getBackgroundColorWithOpacity( colors, backgroundColor, undefined, 65 ) )
			.toBe( 'rgba(255, 255, 255, 0.65)' );
	} );

	it( 'should return correct value with a matching color slug', () => {
		const colors = [
			{
				color: '#ffffff',
				slug: 'white',
			},
			{
				color: '#000000',
				slug: 'black',
			},
		];

		const backgroundColor = {
			color: '#abcdef',
			slug: 'white',
		};

		expect( getBackgroundColorWithOpacity( colors, backgroundColor, undefined, 65 ) )
			.toBe( 'rgba(255, 255, 255, 0.65)' );
	} );

	it( 'should return correct value with a custom color object', () => {
		const backgroundColor = {
			color: '#ffffff',
			slug: undefined,
		};

		expect( getBackgroundColorWithOpacity( [], backgroundColor, undefined, 65 ) )
			.toBe( 'rgba(255, 255, 255, 0.65)' );
	} );

	it( 'should return correct value with custom color', () => {
		expect( getBackgroundColorWithOpacity( [], undefined, '#ffffff', 65 ) )
			.toBe( 'rgba(255, 255, 255, 0.65)' );
	} );

	it( 'should consider maximum opacity if there is no opacity set', () => {
		expect( getBackgroundColorWithOpacity( [], undefined, '#ffffff' ) ).toBe( 'rgba(255, 255, 255, 1)' );
	} );

	it( 'should return nothing if no color is passed at all', () => {
		expect( getBackgroundColorWithOpacity( [], undefined, undefined ) ).toBe( undefined );
	} );
} );
