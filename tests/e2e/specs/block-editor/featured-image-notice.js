/**
 * WordPress dependencies
 */
import { createNewPost } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { clickButton, uploadMedia } from '../../utils';

const noticeSelector = '.media-toolbar-secondary .notice-warning';
const largeImage = 'large-image-36521.jpg';
const smallImage = 'small-image-100-100.jpg';
const selectButton = '.media-modal button.media-button-select';
const featuredImageNoticeText = 'The selected image is too small';
const cropImageText = 'Crop Image';

/**
 * Tests the notices for the featured image.
 */
describe( 'Featured Image Notice', () => {
	beforeEach( async () => {
		await createNewPost( { postType: 'post' } );
		await clickButton( 'Post' );
		await clickButton( 'Featured image' );
		await clickButton( 'Set featured image' );
	} );

	it( 'should not display a notice, nor suggest cropping, when the image is the expected size', async () => {
		await uploadMedia( largeImage );

		// The warning notice text should not appear.
		await expect( page ).not.toMatch( featuredImageNoticeText );
		await expect( page ).toClick( selectButton );

		// This should not suggest cropping.
		await expect( page ).not.toMatch( cropImageText );
	} );

	it( 'should display a notice when the image is too small, but not suggest cropping', async () => {
		await uploadMedia( smallImage );

		// The warning notice for the small image should appear.
		const warningNotice = await page.$( noticeSelector );
		await expect( warningNotice ).toMatch( featuredImageNoticeText );
		await expect( page ).toClick( selectButton );

		// This should not suggest cropping.
		await expect( page ).not.toMatch( cropImageText );
	} );
} );
