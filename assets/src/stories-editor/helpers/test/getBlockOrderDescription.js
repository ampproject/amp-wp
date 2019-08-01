/**
 * Internal dependencies
 */
import { getBlockOrderDescription } from '../';

describe( 'block order controls', () => {
	const dirUp = -1,
		dirDown = 1;

	describe( 'getBlockOrderDescription', () => {
		const type = 'TestType';

		it( 'should generate a title for the first item moving up', () => {
			expect( getBlockOrderDescription(
				type,
				1,
				1,
				true,
				false,
				dirUp,
			) ).toBe(
				`Block ${ type } is at the beginning of the content and can’t be moved up`
			);
		} );

		it( 'should generate a title for the last item moving down', () => {
			expect( getBlockOrderDescription(
				type,
				3,
				3,
				false,
				true,
				dirDown,
			) ).toBe( `Block ${ type } is at the end of the content and can’t be moved down` );
		} );

		it( 'should generate a title for the second item moving up', () => {
			expect( getBlockOrderDescription(
				type,
				2,
				1,
				false,
				false,
				dirUp,
			) ).toBe( `Move ${ type } block from position 2 up to position 1` );
		} );

		it( 'should generate a title for the second item moving down', () => {
			expect( getBlockOrderDescription(
				type,
				2,
				3,
				false,
				false,
				dirDown,
			) ).toBe( `Move ${ type } block from position 2 down to position 3` );
		} );

		it( 'should generate a title for the only item in the list', () => {
			expect( getBlockOrderDescription(
				type,
				1,
				1,
				true,
				true,
				dirDown,
			) ).toBe( `Block ${ type } is the only block, and cannot be moved` );
		} );
	} );
} );
