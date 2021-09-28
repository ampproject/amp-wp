/**
 * Internal dependencies
 */
import { validateFeaturedImage } from '../';

describe( 'validateFeaturedImage', () => {
	it( 'returns an error if `media` is not an object', () => {
		const isValid = validateFeaturedImage( null, {}, false );
		expect( isValid ).toStrictEqual( [ 'Selecting a featured image is recommended for an optimal user experience.' ] );
	} );

	it( 'returns an error if the `media` object is not an object and is required', () => {
		const isValid = validateFeaturedImage( null, null, true );
		expect( isValid ).toStrictEqual( [ 'Selecting a featured image is required.' ] );
	} );

	it( 'returns an error if the featured image is not an acceptable media format', () => {
		const isValid = validateFeaturedImage(
			{ mime_type: 'foo', media_details: { width: 11, height: 11 } },
			{ width: 10, height: 10 },
			false,
		);
		expect( isValid ).toStrictEqual( [ 'The featured image must be in .jpg, .png, or .gif format.' ] );
	} );

	it( 'returns an error if the featured image is too small', () => {
		const isValid = validateFeaturedImage(
			{ mime_type: 'image/png', media_details: { width: 10, height: 10 } },
			{ width: 11, height: 11 },
			false,
		);
		expect( isValid ).toStrictEqual( [ 'The featured image should have a size of at least 11 by 11 pixels.' ] );
	} );

	it( 'returns null if the featured image is valid', () => {
		const isValid = validateFeaturedImage(
			{ mime_type: 'image/png', media_details: { width: 11, height: 11 } },
			{ width: 10, height: 10 },
			false,
		);
		expect( isValid ).toBeNull();
	} );
} );
