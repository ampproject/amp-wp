/**
 * Internal dependencies
 */
import { hasMinimumDimensions } from '../';

describe( 'hasMinimumDimensions', () => {
	it( 'should reject invalid media object', () => {
		expect( hasMinimumDimensions( undefined, {} ) ).toBe( false );
		expect( hasMinimumDimensions( { width: 100, height: undefined }, {} ) ).toBe( false );
		expect( hasMinimumDimensions( { width: undefined, height: 100 }, {} ) ).toBe( false );
	} );

	it( 'should reject image if width is too small', () => {
		expect( hasMinimumDimensions( { width: 1000, height: 1000 }, { width: 2000, height: 1000 } ) ).toBe( false );
	} );

	it( 'should reject image if height is too small', () => {
		expect( hasMinimumDimensions( { width: 1000, height: 1000 }, { width: 1000, height: 2000 } ) ).toBe( false );
	} );

	it( 'should allow image that exactly matches requirements', () => {
		expect( hasMinimumDimensions( { width: 1000, height: 1000 }, { width: 1000, height: 1000 } ) ).toBe( true );
	} );

	it( 'should allow an image if the minimum width and height are zero', () => {
		expect( hasMinimumDimensions( { width: 1000, height: 1000 }, { width: 0, height: 0 } ) ).toBe( true );
	} );

	it( 'should reject an image if the minimum width is not met while the minimum height is zero', () => {
		expect( hasMinimumDimensions( { width: 1000, height: 1000 }, { width: 2000, height: 0 } ) ).toBe( false );
	} );

	it( 'should reject an image if the minimum height is not met while the minimum width is zero', () => {
		expect( hasMinimumDimensions( { width: 1000, height: 1000 }, { width: 0, height: 2000 } ) ).toBe( false );
	} );
} );
