/**
 * Internal dependencies
 */
import getRelativeElementPosition from '../getRelativeElementPosition';

describe( 'getRelativeElementPosition', () => {
	it( 'should return the relative element position', () => {
		const blockElement = {
			getBoundingClientRect: jest.fn( () => {
				return {
					top: 400,
					right: 800,
					bottom: 800,
					left: 400,
				};
			} ),
		};

		const parentElement = {
			getBoundingClientRect: jest.fn( () => {
				return {
					top: 250,
					right: 1000,
					bottom: 1000,
					left: 250,
				};
			} ),
		};

		const expected = {
			top: 150,
			right: 550,
			bottom: 550,
			left: 150,
		};

		expect( getRelativeElementPosition( blockElement, parentElement ) ).toStrictEqual( expected );
	} );
} );
