/**
 * Internal dependencies
 */
import { validateFeaturedImage } from '../';

describe( 'validateFeaturedImage', () => {
	it( 'returns an error if the `media` is not an object', () => {
		const isValid = validateFeaturedImage( null, { width: 10, height: 10 } );
		expect( isValid ).toStrictEqual( [ 'Selecting a featured image is required.' ] );
	} );

	it( 'returns an error if the featured image is not an acceptable media format', () => {
		const isValid = validateFeaturedImage(
			{ mime_type: 'foo', media_details: { width: 11, height: 11 } },
			{ width: 10, height: 10 },
		);
		expect( isValid ).toStrictEqual( [ 'The featured image must be of either JPEG, PNG, GIF, WebP, or SVG format.' ] );
	} );

	it( 'returns an error if the featured image is too small', () => {
		const isValid = validateFeaturedImage(
			{ mime_type: 'image/png', media_details: { width: 10, height: 10 } },
			{ width: 11, height: 11 },
		);
		expect( isValid ).toStrictEqual( [ 'The featured image should have a size of at least 11 by 11 pixels.' ] );
	} );

	it( 'returns null if the featured image is valid', () => {
		const isValid = validateFeaturedImage(
			{ mime_type: 'image/png', media_details: { width: 11, height: 11 } },
			{ width: 10, height: 10 },
		);
		expect( isValid ).toBeNull();
	} );
} );
