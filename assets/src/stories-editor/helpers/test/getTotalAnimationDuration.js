/**
 * Internal dependencies
 */
import { getTotalAnimationDuration } from '../';

describe( 'getTotalAnimationDuration', () => {
	it( 'should return 0 if there are no animated blocks', () => {
		const animatedBlocks = [
			{
				id: 'foo',
				animationType: undefined,
				duration: 1000,
			},
		];

		expect( getTotalAnimationDuration( animatedBlocks ) ).toBe( 0 );
	} );

	it( 'should return the total animation duration', () => {
		const animatedBlocks = [
			{
				id: 'foo',
				animationType: 'fade-in',
				duration: 1000,
			},
			{
				id: 'foo-bar',
				parent: 'foo',
				animationType: 'fade-in',
				duration: 1000,
				delay: 1000,
			},
			{
				id: 'foo-baz',
				parent: 'foo',
				animationType: 'fade-in',
				duration: 1000,
				delay: 2000,
			},
			{
				id: 'foo-bar-1',
				parent: 'foo-bar',
				animationType: 'fade-in',
				duration: 1000,
				delay: 1000,
			},
			{
				id: 'foo-bar-2',
				parent: 'foo-bar',
				animationType: 'fade-in',
				duration: 1000,
				delay: 2000,
			},
		];

		expect( getTotalAnimationDuration( animatedBlocks ) ).toBe( 1000 + 3000 + 3000 );
	} );
} );
